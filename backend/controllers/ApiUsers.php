<?php

class ApiUsers extends BaseApiController
{
    //all
    public function index(): void
    {
        $user  = $this->requireAdmin();
        $users = UserService::getAll();
 
        ActivityLogService::log('list_users', $user['user_id']);
        ApiResponse::success(array_map(fn($u) => $u->toArray(), $users));
    }

    public function indexBI(): void
    {
        $user  = $this->requireBI();
        $users = UserService::getAll();
 
        ActivityLogService::log('list_users', $user['user_id']);
        ApiResponse::success(array_map(fn($u) => $u->toArray(), $users));
    }
    //show
    public function show(int $id): void
    {
        $auth = $this->tryAuth();
        $user = UserService::getById($id);
        if (!$user) ApiResponse::error('Utilisateur introuvable', 404);
 
        $data  = $user->toArray();
        $data['avis_stats'] = MongoMessageService::getAvisStatsByVendeur($id);
        $data['avis'] = array_map(
            fn($a) => $a->toArray(),
            MongoMessageService::getAvisByVendeur($id)
        );
 
        ActivityLogService::log('view_user', $auth['user_id'] ?? null, ['entity' => 'user', 'entity_id' => $id]);
        ApiResponse::success($data);
    }
    //add
    public function add(): void
    {
        $user = $this->requireAdmin();
        
        $body = $this->getBody();

        $missing = $this->validateRequired($body, ['nom', 'prenom', 'email', 'password', 'campus']);
        if ($missing) {
            ApiResponse::error('Champs requis manquants : ' . implode(', ', $missing), 422);
        }

        $checkdomain = $this->isAllowedEmailDomain($body['email']);
        if (!$checkdomain) {
            ApiResponse::error('Email doit être dans un domaine autorisé', 422);
        }

        if (!filter_var($body['email'], FILTER_VALIDATE_EMAIL)) {
            ApiResponse::error('Format d\'email invalide', 422);
        }

        if (strlen($body['password']) < 8) {
            ApiResponse::error('Le mot de passe doit contenir au moins 8 caractères', 422);
        }

        $checkdomain = $this->isAllowedEmailDomain($body['email']);
        if (!$checkdomain) {
            ApiResponse::error('Email doit être dans un domaine autorisé', 422);
        }
 
        $r = UserService::add($body);
        if (!$r) ApiResponse::error('Erreur d\'ajout', 500);
 
        ActivityLogService::log('add_user', $user['user_id']);
        ApiResponse::success(null, 'Utilisateur ajouté');
    }
    //update
    public function update(int $id): void
    {
        $user = $this->requireAuth();
        if ($user['user_id'] != $id && $user['role_name'] !== 'Administrateur') {
            ApiResponse::error('Non autorisé', 403);
        }
 
        $body = $this->getBody();
        $data = [
            'nom' => $body['nom']    ?? null,
            'prenom' => $body['prenom'] ?? null,
            'email'  => $body['email']  ?? null,
            'campus' => $body['campus'] ?? null,
        ];
        $data = array_filter($data, fn($v) => $v !== null);
 
        if (empty($data)) ApiResponse::error('Aucun champ à modifier', 422);

        $checkdomain = $this->isAllowedEmailDomain($body['email']);
        if (!$checkdomain) {
            ApiResponse::error('Email doit être dans un domaine autorisé', 422);
        }
 
        $r = UserService::update($id, $data);
        if (!$r) ApiResponse::error('Erreur modification', 500);
 
        ActivityLogService::log('update_user', $user['user_id'], ['entity' => 'user', 'entity_id' => $id]);
        ApiResponse::success(null, 'Profil mis à jour');
    }
    //delete
    public function destroy(int $id): void
    {
        $user = $this->requireAdmin();
 
        $r = UserService::delete($id);
        if (!$r) ApiResponse::error('Erreur suppression', 500);
 
        ActivityLogService::log('delete_user', $user['user_id'], ['entity' => 'user', 'entity_id' => $id]);
        ApiResponse::success(null, 'Utilisateur supprimé');
    }
    //activate
    public function activate(int $id): void
    {
        $user = $this->requireAdmin();
        UserService::activate($id);
 
        ActivityLogService::log('activate_user', $user['user_id'], ['entity' => 'user', 'entity_id' => $id]);
        ApiResponse::success(null, 'Utilisateur activé');
    }
    //deactivate
    public function deactivate(int $id): void
    {
        $user = $this->requireAdmin();
        UserService::deactivate($id);
 
        ActivityLogService::log('deactivate_user', $user['user_id'], ['entity' => 'user', 'entity_id' => $id]);
        ApiResponse::success(null, 'Utilisateur désactivé');
    }
    //password
    public function updatePassword(int $id): void
    {
        $user = $this->requireAuth();
        if ($user['user_id'] != $id) ApiResponse::error('Non autorisé', 403);
 
        $body = $this->getBody();
        $missing = $this->validateRequired($body, ['old', 'new']);
        if ($missing) ApiResponse::error('Champs requis : old, new', 422);
 
        if (strlen($body['new']) < 8) ApiResponse::error('Mot de passe trop court (min 8 caractères)', 422);
 
        $r = UserService::updatePassword($id, $body['old'], $body['new']);
        if ($r !== true) ApiResponse::error($r, 400);
 
        ActivityLogService::log('update_password', $user['user_id'], ['entity' => 'user', 'entity_id' => $id]);
        ApiResponse::success(null, 'Mot de passe modifié');
    }


    private function isAllowedEmailDomain($email){
        $allowedDomains = [
            'utbm.fr',
            'utt.fr',
            'utc.fr',
            'uttop.fr',
            'utexchange.fr'
        ];
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return false;
        }
        $domain = substr(strrchr($email, "@"), 1);
        return in_array(strtolower($domain), $allowedDomains);
    }

}