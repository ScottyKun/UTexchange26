<?php

use MongoDB\BSON\ObjectId;
use MongoDB\BSON\UTCDateTime;

class MetadataService
{
    private static function col()
    {
        return MongoDatabase::getInstance()->getCollection('photos_metadata');
    }

    private static function normalize(array $doc): array
    {
        if (isset($doc['_id'])) {
            $doc['id'] = (string) $doc['_id'];
            unset($doc['_id']);
        }
        foreach ($doc as &$value) {
            if ($value instanceof UTCDateTime) {
                $value = $value->toDateTime()->format('Y-m-d H:i:s');
            }
        }
        return $doc;
    }

    public static function save(array $data): bool
    {
        try {
            $result = self::col()->insertOne([
                'annonce_id'    => (int) ($data['annonce_id']    ?? 0),
                'user_id'       => (int) ($data['user_id']       ?? 0),
                'nom_fichier'   => $data['nom_fichier']   ?? '',
                'type_mime'     => $data['type_mime']     ?? 'application/octet-stream',
                'taille'        => (int) ($data['taille'] ?? 0),
                'chemin_fichier'=> $data['chemin_fichier'] ?? '',
                'is_cover'      => (bool) ($data['is_cover'] ?? false),
                'created_at'    => new UTCDateTime(),
            ]);

            return $result->getInsertedCount() === 1;
        } catch (\Exception $e) {
            error_log('[MetadataService] save: ' . $e->getMessage());
            return false;
        }
    }

   //Appelé en miroir de PhotoService::setCover()
    public static function setCover(string $cheminFichier, int $annonceId): bool
    {
        try {
            // Retirer l'ancien cover
            self::col()->updateMany(
                ['annonce_id' => $annonceId],
                ['$set' => ['is_cover' => false]]
            );

            // Définir le nouveau cover
            $r = self::col()->updateOne(
                ['annonce_id' => $annonceId, 'chemin_fichier' => $cheminFichier],
                ['$set' => ['is_cover' => true]]
            );

            return $r->getModifiedCount() > 0;
        } catch (\Exception $e) {
            error_log('[MetadataService] setCover: ' . $e->getMessage());
            return false;
        }
    }

    //Appelé en miroir de PhotoService::delete().
    public static function deleteByPath(string $cheminFichier): bool
    {
        try {
            $r = self::col()->deleteOne(['chemin_fichier' => $cheminFichier]);
            return $r->getDeletedCount() > 0;
        } catch (\Exception $e) {
            error_log('[MetadataService] deleteByPath: ' . $e->getMessage());
            return false;
        }
    }

   //Appelé en miroir de PhotoService::deleteByAnnonce().
    public static function deleteByAnnonce(int $annonceId): bool
    {
        try {
            $r = self::col()->deleteMany(['annonce_id' => $annonceId]);
            return $r->getDeletedCount() >= 0;
        } catch (\Exception $e) {
            error_log('[MetadataService] deleteByAnnonce: ' . $e->getMessage());
            return false;
        }
    }

    //Métadonnées de toutes les photos d'une annonce.
    public static function getByAnnonce(int $annonceId): array
    {
        try {
            $cursor = self::col()->find(
                ['annonce_id' => $annonceId],
                [
                    'sort'    => ['is_cover' => -1, 'created_at' => 1],
                    'typeMap' => ['root' => 'array', 'document' => 'array', 'array' => 'array'],
                ]
            );
            return array_map(
                fn($doc) => self::normalize($doc),
                iterator_to_array($cursor)
            );
        } catch (\Exception $e) {
            return [];
        }
    }

    //Toutes les métadonnées (admin — audit).
    public static function getAll(int $limit = 100, int $offset = 0): array
    {
        try {
            $cursor = self::col()->find(
                [],
                [
                    'sort'    => ['created_at' => -1],
                    'limit'   => $limit,
                    'skip'    => $offset,
                    'typeMap' => ['root' => 'array', 'document' => 'array', 'array' => 'array'],
                ]
            );
            return array_map(
                fn($doc) => self::normalize($doc),
                iterator_to_array($cursor)
            );
        } catch (\Exception $e) {
            return [];
        }
    }

    //Taille totale occupée par les fichiers d'une annonce
    public static function getTotalSizeByAnnonce(int $annonceId): int
    {
        try {
            $pipeline = [
                ['$match' => ['annonce_id' => $annonceId]],
                ['$group' => ['_id' => null, 'total' => ['$sum' => '$taille']]],
            ];
            $rows = iterator_to_array(self::col()->aggregate($pipeline));
            return $rows ? (int) $rows[0]['total'] : 0;
        } catch (\Exception $e) {
            return 0;
        }
    }
}