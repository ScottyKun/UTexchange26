<?php

class FavoriService{
    //add
    private static function add($userId, $annonceId){
        $rq = "INSERT INTO favoris (utilisateur_id, annonce_id, created_at) 
                    VALUES (:user_id, :annonce_id, NOW())";
        return Database::execute($rq, [
            'user_id' => $userId,
            'annonce_id' => $annonceId
        ]);
    }
    //delete
    private static function remove($userId, $annonceId){
        $rq = "DELETE FROM favoris WHERE utilisateur_id = :user_id AND annonce_id = :annonce_id";
        return Database::execute($rq, [
            'user_id' => $userId,
            'annonce_id' => $annonceId
        ]);
    }
    //exits (check pour savoir si déjà en favoris)
    public static function exists($userId, $annonceId){
        $rq = "SELECT 1 FROM favoris WHERE utilisateur_id = :user_id AND annonce_id = :annonce_id LIMIT 1";
        $result = Database::find($rq, "stdClass", [
            'user_id' => $userId,
            'annonce_id' => $annonceId
        ]);
        return !empty($result);
    }
    //toggle (add/remove)
    public static function toggle($userId, $annonceId){
        if (self::exists($userId, $annonceId)) {
            self::remove($userId, $annonceId);
            return 'removed';
        }

        self::add($userId, $annonceId);
        return 'added';
    }
    //getByUser
    public static function getByUser($userId){
        $rq = "SELECT a.* 
               FROM annonces a
               JOIN favoris f ON a.id = f.annonce_id
               WHERE f.utilisateur_id = :user_id
               ORDER BY f.created_at DESC";

        return Database::query($rq, "Annonce", ['user_id' => $userId]);
    }
}
