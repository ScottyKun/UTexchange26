<?php

class ApiAdmin extends BaseApiController
{
    public function allAnnonces(): void
    {
        $user     = $this->requireAdmin();
        $annonces = AnnonceService::getByAdmin();

        $data = [];
        foreach ($annonces as $a) {
            $row  = $a->toArray();
            $cover  = PhotoService::getCover($a->getId());
            $seller  = UserService::getById($a->getUtilisateurId());
            $row['cover']  = $cover  ? $cover->toArray()  : null;
            $row['seller'] = $seller ? $seller->toArray() : null;
            $data[]  = $row;
        }

        ActivityLogService::log('admin_list_annonces', $user['user_id']);
        ApiResponse::success($data);
    }

    public function allAnnoncesBI(): void
    {
        $user = $this->requireBI();
        $annonces = AnnonceService::getByAdmin();

        $data = [];
        foreach ($annonces as $a) {
            $row  = $a->toArray();
            $cover  = PhotoService::getCover($a->getId());
            $seller  = UserService::getById($a->getUtilisateurId());
            $row['cover']  = $cover  ? $cover->toArray()  : null;
            $row['seller'] = $seller ? $seller->toArray() : null;
            $data[]  = $row;
        }

        ActivityLogService::log('admin_list_annonces', $user['user_id']);
        ApiResponse::success($data);
    }

    public function allAvis(): void
    {
        $user = $this->requireAdmin();
        $avis = MongoMessageService::getAllAvis();

        ActivityLogService::log('admin_list_avis', $user['user_id']);
        ApiResponse::success(array_map(fn($a) => $a->toArray(), $avis));
    }

    public function allAvisBI(): void
    {
        $user = $this->requireBI();
        $avis = MongoMessageService::getAllAvis();

        ActivityLogService::log('admin_list_avis', $user['user_id']);
        ApiResponse::success(array_map(fn($a) => $a->toArray(), $avis));
    }

    public function activateAvis(string $convId, string $avisId): void
    {
        $user = $this->requireAdmin();
        $r    = MongoMessageService::setAvisActif($convId, $avisId, true);
        if (!$r) ApiResponse::error('Avis introuvable ou déjà actif', 404);

        ActivityLogService::log('admin_activate_avis', $user['user_id'], ['entity' => 'conversation', 'entity_id' => $convId]);
        ApiResponse::success(null, 'Avis activé');
    }

    public function deactivateAvis(string $convId, string $avisId): void
    {
        $user = $this->requireAdmin();
        $r    = MongoMessageService::setAvisActif($convId, $avisId, false);
        if (!$r) ApiResponse::error('Avis introuvable ou déjà inactif', 404);

        ActivityLogService::log('admin_deactivate_avis', $user['user_id'], ['entity' => 'conversation', 'entity_id' => $convId]);
        ApiResponse::success(null, 'Avis désactivé');
    }

    public function reportAnnonce(int $id): void
    {
        $user    = $this->requireAdmin();
        $annonce = AnnonceService::getById($id);
        if (!$annonce) ApiResponse::error('Annonce introuvable', 404);

        $blockedStatuses = ['vendu', 'signalé', 'draft'];
        if (in_array($annonce->getStatus(), $blockedStatuses)) {
            ApiResponse::error('Impossible de signaler une annonce avec le statut : ' . $annonce->getStatus(), 400);
        }

        AnnonceService::reportAnnonce($id);

        ActivityLogService::log('admin_report_annonce', $user['user_id'], ['entity' => 'annonce', 'entity_id' => $id]);
        ApiResponse::success(null, 'Annonce signalée');
    }

    public function stats(): void
    {
        $user = $this->requireBI();

        $data = [
            // Stats PostgreSQL
            'nb_users'          => (int) StatistiqueService::getNbUsers(),
            'nb_annonces'       => (int) StatistiqueService::getNbAnnonces(),
            'annonce_stats'     => StatistiqueService::getAnnonceStats(),
            'latest_annonces'   => array_map(fn($a) => $a->toArray(), StatistiqueService::getLatestAnnonces()),
            'top_categories'    => StatistiqueService::getTopCategories(),

            // Stats MongoDB
            'nb_conversations'  => MongoMessageService::countAll(),
            'activity_logs'     => ActivityLogService::getAll(20),
            'connection_logs'   => ConnectionLogService::getAll(20),
        ];

        ActivityLogService::log('admin_view_stats', $user['user_id']);
        ApiResponse::success($data);
    }

    public function activityLogs(): void
    {
        $user = $this->requireBI();
        $query  = $this->getQuery();
        $limit  = (int) ($query['limit']  ?? 100);
        $offset = (int) ($query['offset'] ?? 0);

        $logs = ActivityLogService::getAll($limit, $offset);

        ActivityLogService::log('admin_view_activity_logs', $user['user_id']);
        ApiResponse::success([
            'logs'  => $logs,
            'total' => ActivityLogService::countAll(),
        ]);
    }

    public function connectionLogs(): void
    {
        $user = $this->requireBI();
        $query  = $this->getQuery();
        $limit  = (int) ($query['limit']  ?? 100);
        $offset = (int) ($query['offset'] ?? 0);

        $logs = ConnectionLogService::getAll($limit, $offset);

        ActivityLogService::log('admin_view_connection_logs', $user['user_id']);
        ApiResponse::success([
            'logs'  => $logs,
            'total' => ConnectionLogService::countAll(),
        ]);
    }
}