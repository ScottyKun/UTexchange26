<?php
 
class ApiFavoris extends BaseApiController
{
    //all
    public function index(): void
    {
        $user = $this->requireAuth();
        $annonces = FavoriService::getByUser($user['user_id']);
 
        $data = [];
        foreach ($annonces as $a) {
            $row = $a->toArray();
            $cover = PhotoService::getCover($a->getId());
            $row['cover'] = $cover ? $cover->toArray() : null;
            $data[] = $row;
        }
 
        ActivityLogService::log('list_favoris', $user['user_id']);
        ApiResponse::success($data);
    }

    //toggle
    public function toggle(int $id): void
    {
        $user = $this->requireAuth();
        $annonce = AnnonceService::getById($id);
        if (!$annonce) ApiResponse::error('Annonce introuvable', 404);
 
        $action = FavoriService::toggle($user['user_id'], $id);
 
        ActivityLogService::log($action . '_favori', $user['user_id'], ['entity' => 'annonce', 'entity_id' => $id]);
        ApiResponse::success(['action' => $action], $action === 'added' ? 'Ajouté aux favoris' : 'Retiré des favoris');
    }

    //check
    public function check(int $id): void
    {
        $user = $this->requireAuth();
        $exists = FavoriService::exists($user['user_id'], $id);
 
        ActivityLogService::log('check_favori', $user['user_id'], ['entity' => 'annonce', 'entity_id' => $id]);
        ApiResponse::success(['favori' => $exists]);
    }
}