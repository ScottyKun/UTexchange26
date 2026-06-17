<?php
/**
 * - Validation des fichiers uploadés
 * - Déplacement dans storage/
 * - Serving sécurisé via readfile()
 * - Construction de l'URL publique
 */
class PhotoHelper{
    private const MAX_SIZE        = 5 * 1024 * 1024; // 5 Mo
    private const MAX_PAR_ANNONCE = 8;
    private const EXTENSIONS = ['jpg', 'jpeg', 'png', 'webp'];
    private const MIMES = ['image/jpeg', 'image/png', 'image/webp'];
    private string $storageRoot = __DIR__; //racine du dossier storage

    /** Upload
     * Traite les fichiers uploadés depuis $_FILES.
     * Valide et déplace chaque fichier dans storage/uploads/annonce/{id}/.
     */
    public function processUpload(array $files, int $annonceId, int $existingCount = 0): array
    {
        $result     = ['files' => [], 'errors' => []];
        $normalized = $this->normalizeFiles($files);

        // Filtre les slots vides (champ file soumis sans fichier)
        $normalized = array_values(array_filter(
            $normalized,
            fn($f) => $f['error'] !== UPLOAD_ERR_NO_FILE
        ));

        if (count($normalized) + $existingCount > self::MAX_PAR_ANNONCE) {
            $result['errors'][] = 'Maximum ' . self::MAX_PAR_ANNONCE . ' photos par annonce.';
            return $result;
        }

        foreach ($normalized as $file) {
            $error = $this->validate($file);

            $mime = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);

            if ($error) {
                $result['errors'][] = htmlspecialchars($file['name']) . ' : ' . $error;
                continue;
            }

            $moved = $this->move($file, $annonceId);
            if (!$moved) {
                $result['errors'][] = htmlspecialchars($file['name']) . ' : échec du déplacement.';
                continue;
            }


            $result['files'][] = [
                'chemin_fichier' => $moved,
                'nom_fichier'    => $file['name'],
                'taille_fichier' => $file['size'],
                'type_mime'      => $mime,
            ];
        }

        return $result;
    }
    //Envoie un fichier image au navigateur de manière sécurisée
    public function serve(string $cheminFichier): void
    {
        $fullPath = $this->storageRoot . '/uploads/' . $cheminFichier;

        if (!file_exists($fullPath)) {
            http_response_code(404);
            exit('Fichier introuvable.');
        }

        $mime = (new finfo(FILEINFO_MIME_TYPE))->file($fullPath);

        header('Content-Type: '   . $mime);
        header('Content-Length: ' . filesize($fullPath));
        header('Cache-Control: public, max-age=31536000');
        readfile($fullPath);
        exit;
    }
    //Suppression physique d'une photo
    public function deleteFile(string $cheminFichier): void
    {
        $fullPath = $this->storageRoot . '/uploads/' . $cheminFichier;
        if (file_exists($fullPath)) {
            unlink($fullPath);
        }
    }
    //Suppression du dossier d'une annonce si vide
    public function deleteAnnonceDir(int $annonceId): void
    {
        $dir = $this->storageRoot . '/uploads/annonce/' . $annonceId;
        if (is_dir($dir) && count(scandir($dir)) === 2) { // 2 = . et ..
            rmdir($dir);
        }
    }
    //URL (url publique d'une photo)
    public static function url(string $cheminFichier): string
    {
        return '/uploads/' . $cheminFichier;
    }

    //Helpers
    private function validate(array $file): ?string
    {
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return $this->uploadErrorMessage($file['error']);
        }

        if ($file['size'] > self::MAX_SIZE) {
            return 'Taille maximale dépassée (5 Mo).';
        }

        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, self::EXTENSIONS)) {
            return 'Format non autorisé (JPG, PNG, WEBP).';
        }

        // Vérifie le contenu réel : ex : un .php renommé en .jpg sera détecté
        $mime = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
        if (!in_array($mime, self::MIMES)) {
            return 'Type de fichier non autorisé.';
        }

        return null;
    }
    //Déplace le fichier vers storage et retourne le chemin pour la BD
    private function move(array $file, int $annonceId): ?string
    {
        $dir = $this->storageRoot . '/uploads/annonce/' . $annonceId;

        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $ext      = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $filename = time() . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
        $dest     = $dir . '/' . $filename;

        if (!move_uploaded_file($file['tmp_name'], $dest)) {
            return null;
        }

        return 'annonce/' . $annonceId . '/' . $filename;
    }
    //Pour gérer si on a un fichier unique ou multiple
    private function normalizeFiles(array $files): array
    {
        if (!is_array($files['name'])) {
            return [$files];
        }

        $result = [];
        foreach (array_keys($files['name']) as $i) {
            $result[] = [
                'name'     => $files['name'][$i],
                'tmp_name' => $files['tmp_name'][$i],
                'error'    => $files['error'][$i],
                'size'     => $files['size'][$i],
            ];
        }
        return $result;
    }

    private function uploadErrorMessage(int $code): string
    {
        return match ($code) {
            UPLOAD_ERR_INI_SIZE   => 'Fichier trop lourd (limite serveur).',
            UPLOAD_ERR_PARTIAL    => 'Upload incomplet.',
            UPLOAD_ERR_NO_TMP_DIR => 'Dossier temporaire manquant.',
            UPLOAD_ERR_CANT_WRITE => 'Impossible d\'écrire sur le disque.',
            default               => 'Erreur upload inconnue.',
        };
    }
}
