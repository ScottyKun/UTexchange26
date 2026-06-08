<?php
require_once __DIR__ . '/../model/user.php';
require_once __DIR__ . '/../core/database.php';
class UserService{
    //update
    public static function update($id, $data){
        $rq = "UPDATE utilisateurs SET 
                nom = :nom,
                prenom = :prenom,
                email = :email,
                campus = :campus
               WHERE id = :id";
        $tab = [
            "id" => $id,
            "nom" => $data['nom'],
            "prenom" => $data['prenom'],
            "email" => $data['email'],
            "campus" => $data['campus']
        ];
        $result = Database::execute($rq, $tab);
        return $result;
    }
    //add
    public static function add($data){
         $check = Database::find(
            "SELECT * FROM utilisateurs WHERE email = :email",
            "User",
            ['email' => $data['email']]
        );
        if ($check) {
            return false;
        }

        //hash
        $hash = password_hash($data['password'], PASSWORD_BCRYPT);
        $rq="INSERT INTO utilisateurs 
            (nom, prenom, email, mot_de_passe, role_id, campus, est_actif, date_inscription, email_verifie)
            VALUES 
            (:nom, :prenom, :email, :password, :role_id, :campus, true, NOW(), false)";
        $tab = [
            "nom" => $data['nom'] ?? null,
            "prenom" => $data['prenom'] ?? null,
            "email" => $data['email'],
            "password" => $hash,
            "role_id" => $data['role_id'] ?? 2,
            "campus" => $data['campus'] ?? null
        ];
        $result=Database::execute($rq, $tab);

        return $result;
    }
    //delete
    public static function delete($id){
        $rq = "DELETE FROM utilisateurs WHERE id = :id";
        return Database::execute($rq, ['id' => $id]);
    }
    //search
    public static function search($keyword){
        $rq = "SELECT * FROM utilisateurs 
               WHERE nom LIKE :kw 
               OR prenom LIKE :kw 
               OR email LIKE :kw";

        return Database::query($rq, "User", [
            'kw' => "%$keyword%"
        ]);
    }
    //all
    public static function getAll(){
        $rq="SELECT * FROM utilisateurs";
        return Database::query($rq, "User");

    }
    //activate
    public static function activate($id){
        $rq = "UPDATE utilisateurs SET est_actif = true WHERE id = :id";
        return Database::execute($rq, ['id' => $id]);
    }
    //deactivate
    public static function deactivate($id){
        $rq = "UPDATE utilisateurs SET est_actif = false WHERE id = :id";
        return Database::execute($rq, ['id' => $id]);
    }
    //Update password
    public static function updatePassword($id, $old, $new){
        $user = Database::find(
            "SELECT * FROM utilisateurs WHERE id = :id",
            "User",
            ['id' => $id]
        );
        if (!$user) { return false; }

        //check ancien mdp
        if (!password_verify($old, $user->getPassword())) {
            return false;
        }

        $hash = password_hash($new, PASSWORD_BCRYPT);
        $rq = "UPDATE utilisateurs SET mot_de_passe = :password WHERE id = :id";
        return Database::execute($rq, [
            'id' => $id,
            'password' => $hash
        ]);
    }
    //getByID
    public static function getById($id){
        $rq = "SELECT * FROM utilisateurs WHERE id = :id";
        $tab["id"] = $id;
        $user = Database::find($rq, "User", $tab);
        return $user;
    }

}