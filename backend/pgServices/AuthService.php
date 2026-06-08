<?php
require_once __DIR__ . '/../model/user.php';
require_once __DIR__ . '/../core/JwtService.php';
require_once __DIR__ . '/../core/database.php';
require_once __DIR__ . '/RoleService.php';

class AuthService{

    public static function login($email, $password){
        $rq = "SELECT * FROM utilisateurs WHERE email = :email LIMIT 1";
        $tab["email"] = $email;

        $user = Database::find($rq, "User", $tab);

        $roleName = RoleService::getUserRoleById($user->getId());

        if (!$user) {
            return false;
        }

        if (!$user->getEstActif()) {
            return false;
        }

        if (!password_verify($password, $user->getPassword())) {
            return false;
        }

        //JWT
        $payload = [
            'user_id' => $user->getId(),
            'email'   => $user->getEmail(),
            'role_name' => $roleName
        ];

        $token = JwtService::generate($payload);

        return [
            'user' => [
                'id' => $user->getId(),
                'nom' => $user->getNom(),
                'prenom' => $user->getPrenom(),
                'email' => $user->getEmail(),
                'role_name' => $roleName
            ],
            'token' => $token
        ];
    }

    public static function register($data){
    $check = Database::find(
        "SELECT * FROM utilisateurs WHERE email = :email",
        "User",
        ['email' => $data['email']]
    );

    if ($check) {
        return false;
    }

    $hash = password_hash($data['password'], PASSWORD_BCRYPT);

    $rq = "INSERT INTO utilisateurs 
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

    $result = Database::execute($rq, $tab);

    return $result ? true : false;
    }

}