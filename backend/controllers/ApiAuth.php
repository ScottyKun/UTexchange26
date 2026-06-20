<?php

class ApiAuth extends BaseApiController
{
    public function login(): void
    {
        $body = $this->getBody();

        $missing = $this->validateRequired($body, ['email', 'password']);
        if ($missing) {
            ApiResponse::error('Champs requis manquants : ' . implode(', ', $missing), 422);
        }

        $checkdomain = $this->isAllowedEmailDomain($body['email']);
        if (!$checkdomain) {
            ApiResponse::error('Email doit être dans un domaine autorisé', 422);
        }

        $result = AuthService::login($body['email'], $body['password']);

        if (!$result) {
            ConnectionLogService::logFailedLogin($body['email']);
            ApiResponse::error('Email ou mot de passe incorrect', 401);
        }

        ConnectionLogService::logLogin($result['user']['id'], $result['user']['email']);
        ActivityLogService::log('login', $result['user']['id']);

        ApiResponse::success($result, 'Connexion réussie');
    }

    public function register(): void
    {
        $body = $this->getBody();

        $missing = $this->validateRequired($body, ['nom', 'prenom', 'email', 'password']);
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

        $result = AuthService::register($body);

        if (!$result) {
            ApiResponse::error('Cet email est déjà utilisé', 409);
        }

        ActivityLogService::log('register', null, ['email' => $body['email']]);

        ApiResponse::success(null, 'Compte créé avec succès', 201);
    }

    public function logout(): void
    {
        $payload = $this->requireAuth();

        ConnectionLogService::logLogout($payload['user_id'], $payload['email']);
        ActivityLogService::log('logout', $payload['user_id']);

        // Stateless : le token est invalidé côté Angular (suppression localStorage)
        ApiResponse::success(null, 'Déconnexion enregistrée');
    }

    public function me(): void
    {
        $payload = $this->requireAuth();

        $user = UserService::getById($payload['user_id']);

        if (!$user) {
            ApiResponse::error('Utilisateur introuvable', 404);
        }

        ApiResponse::success($user->toArray());
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

    public function generateBiToken()
    {
        $this->requireAdmin();
        
        $token = JwtService::generateBi([
            'user_id' => 999,
            'email' => 'powerbi@utexchange.fr',
            'role_name' => 'BI',
            'type' => 'bi'
        ]);
        
        return ApiResponse::success([
            'token' => $token
        ]);
    }
}