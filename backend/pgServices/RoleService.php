<?php
require_once __DIR__ . '/../model/role.php';
require_once __DIR__ . '/../core/database.php';
class RoleService{
    //all
    public static function getAll(){
        $rq = "SELECT * FROM roles";
        return Database::query($rq, "Role");
    }
    //update
    public static function update($id, $data){
        $rq = "UPDATE roles 
               SET nom = :nom,
                   description = :description
               WHERE id = :id";

        return Database::execute($rq, [
            'id' => $id,
            'nom' => $data['nom'],
            'description' => $data['description']
        ]);
    }
    //add
    public static function add($data){
        $rq = "INSERT INTO roles (nom, description, is_active)
               VALUES (:nom, :description, 1)";

        return Database::execute($rq, [
            'nom' => $data['nom'],
            'description' => $data['description'] ?? null
        ]);
    }
    //deactivate
    public static function deactivate($id){
        $rq = "UPDATE roles SET is_active = 0 WHERE id = :id";
        return Database::execute($rq, ['id' => $id]);
    }
    //activate
    public static function activate($id){
        $rq = "UPDATE roles SET is_active = 1 WHERE id = :id";
        return Database::execute($rq, ['id' => $id]);
    }
    //getUserRole (name)
    public static function getUserRoleById($userId){
        $rq = "SELECT r.nom 
               FROM roles r
               JOIN utilisateurs u ON u.role_id = r.id
               WHERE u.id = :id
               LIMIT 1";

        $result = Database::find($rq, "Role", ['id' => $userId]);
        return $result ? $result->getNom() : null;
    }
}