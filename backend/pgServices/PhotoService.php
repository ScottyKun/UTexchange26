<?php
require_once __DIR__ . '/../model/photo.php';
require_once __DIR__ . '/../core/database.php';
require_once __DIR__ . '/../../storage/PhotoHelper.php';

class PhotoService{
    //getByAnnonce
    public static function getByAnnonce($annonceId){
        $rq = "SELECT * FROM photos WHERE annonce_id = :annonce_id";
        $tab = ["annonce_id" => $annonceId];
        return Database::query($rq, "Photo", $tab);
    }
    //getCover
    public static function getCover($annonceId){
        $rq = "SELECT * FROM photos WHERE annonce_id = :annonce_id AND is_cover = TRUE LIMIT 1";
        $tab = ["annonce_id" => $annonceId];
        return Database::find($rq, "Photo", $tab);
    }
    //setCover
    public static function setCover($id, $annonceId){
        //On retire cover à l'annonce
        $rq1="UPDATE photos SET is_cover = FALSE WHERE annonce_id = :annonce_id";
        $tab1["annonce_id"] = $annonceId;
        $r1=Database::execute($rq1, $tab1);

       if (!$r1) return false;
 
        $rq2 = "UPDATE photos SET is_cover = TRUE WHERE id = :id";
        $r2  = Database::execute($rq2, ["id" => $id]);
 
        if ($r2) {
            // Synchroniser is_cover dans MongoDB
            $photo = self::getById($id);
            if ($photo) {
                MetadataService::setCover($photo->getCheminFichier(), $annonceId);
            }
        }
 
        return $r2;

    }
    //deleteByAnnonce
    public static function deleteByAnnonce($annonceId){
        $photos = self::getByAnnonce($annonceId);
        foreach($photos as $photo){
            //suppression de chaque fichier
            (new PhotoHelper)->deleteFile($photo->getCheminFichier());
        }
        //suppression du dossier
        (new PhotoHelper)->deleteAnnonceDir($annonceId);

        $rq="DELETE FROM photos WHERE annonce_id = :annonce_id";
        $tab["annonce_id"] = $annonceId;
        $r= Database::execute($rq, $tab);

        if ($r) {
            MetadataService::deleteByAnnonce($annonceId);
        }
 
        return $r;
    }
    //delete
    public static function delete($id){
        $photo = self::getById($id);
        if(!$photo){
            return false;
        }
        //suppression physique
        (new PhotoHelper)->deleteFile($photo->getCheminFichier());

        $rq= "DELETE FROM photos WHERE id = :id";
        $tab["id"] = $id;
        $r= Database::execute($rq, $tab);

        if ($r) {
            MetadataService::deleteByPath($photo->getCheminFichier());
        }
 
        return $r;
    }
    //getById
    public static function getById($id){
        $rq = "SELECT * FROM photos WHERE id = :id";
        $tab = ["id" => $id];
        return Database::find($rq, "Photo", $tab);
    }
    //countByAnnonce (nombre de photos)
    public static function countByAnnonce($annonceId){
        $rq = "SELECT COUNT(*) FROM photos WHERE annonce_id = :annonce_id";
        $tab = ["annonce_id" => $annonceId];
        return Database::count($rq, $tab);
    }
    //upload
    public static function upload($files, $annonceId){
        $nb= self::countByAnnonce($annonceId);
        $result= (new PhotoHelper)->processUpload($files, $annonceId, $nb);

        if (empty($result['files'])) {
            return ['success' => false, 'errors' => $result['errors']];
        }

        $hasCover=self::getCover($annonceId);
        if($hasCover === false){
            $first=true;
        }else{
            $first=false;
        }

        foreach ($result['files'] as $fileData) {
            $tab=explode('/', $fileData['chemin_fichier']);
            self::save([
                'annonce_id'     => $annonceId,
                'chemin_fichier' => $fileData['chemin_fichier'],
                'nom_fichier'    => end($tab),
                'taille_fichier' => $fileData['taille_fichier'],
                'type_mime'      => $fileData['type_mime'],
                'is_cover'       => $first,
            ]);
            $first = false;
        }

        return ['success' => true, 'errors' => $result['errors']];
    }
    //save
    private static function save($data){
        $rq="INSERT INTO photos (annonce_id, chemin_fichier, nom_fichier, taille_fichier, is_cover)
            VALUES (:annonce_id, :chemin_fichier, :nom_fichier, :taille_fichier, :is_cover)";

        $tab = [
            'annonce_id' => $data['annonce_id'],
            'chemin_fichier' => $data['chemin_fichier'],
            'nom_fichier'=> $data['nom_fichier'],
            'taille_fichier' => $data['taille_fichier'],
            'is_cover' => $data['is_cover'] ? 'true' : 'false',
        ];

        $r= Database::execute($rq, $tab);

        if ($r) {
            MetadataService::save([
                'annonce_id'     => $data['annonce_id'],
                'nom_fichier'    => $data['nom_fichier'],
                'type_mime'      => $data['type_mime'],
                'taille'         => (int) $data['taille_fichier'],
                'chemin_fichier' => $data['chemin_fichier'],
                'is_cover'       => $data['is_cover'],
            ]);
        }
        return $r;
    }
    //URL
    public static function url($path){
        return PhotoHelper::url($path);
    }
    //serve
    public static function serve($annonceId, $nomFichier){
        $path = 'annonce/' . $annonceId . '/' . $nomFichier;

        //vérification du chemin dans la BD
        $rq= "SELECT id FROM photos WHERE chemin_fichier = :path";
        $result= Database::find($rq, "Photo",['path' => $path]);
        if(!$result){
            http_response_code(404);
            exit('Image introuvable.');
        }

        (new PhotoHelper)->serve($path);
    }

}
