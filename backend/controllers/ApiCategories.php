<?php

class ApiCategories extends BaseApiController
{
    //all
    public function index(): void
    {
        $auth = $this->tryAuth();
        $categories = CategorieService::getAll();
 
        ActivityLogService::log('list_categories', $auth['user_id'] ?? null);
        ApiResponse::success(array_map(fn($c) => $c->toArray(), $categories));
    }
    //add
     public function store(): void
    {
        $user = $this->requireAdmin();
        $body = $this->getBody();
        $missing = $this->validateRequired($body, ['nom']);
        if ($missing) ApiResponse::error('Champ requis : nom', 422);
 
        $data = [
            'nom' => $body['nom'],
            'parent_id' => $body['parent_id'] ?? null,
        ];
 
        $r = CategorieService::add($data);
        if (!$r) ApiResponse::error('Erreur lors de la création', 500);
 
        ActivityLogService::log('create_categorie', $user['user_id'], ['nom' => $body['nom']]);
        ApiResponse::success(null, 'Catégorie créée', 201);
    }
    //update
    public function update(int $id): void
    {
        $user = $this->requireAdmin();
        $cat  = CategorieService::getById($id);
        if (!$cat) ApiResponse::error('Catégorie introuvable', 404);
 
        $body = $this->getBody();
        $data = [
            'nom' => $body['nom']       ?? $cat->getNom(),
            'parent_id' => $body['parent_id'] ?? $cat->getParentId(),
        ];
 
        $r = CategorieService::edit($id, $data);
        if (!$r) ApiResponse::error('Erreur modification', 500);
 
        ActivityLogService::log('update_categorie', $user['user_id'], ['entity' => 'categorie', 'entity_id' => $id]);
        ApiResponse::success(null, 'Catégorie mise à jour');
    }
    //delete
    public function delete(int $id): void
    {
        $user = $this->requireAdmin();
        $cat  = CategorieService::getById($id);
        if (!$cat) ApiResponse::error('Catégorie introuvable', 404);
 
        $r = CategorieService::delete($id);
        if (!$r) ApiResponse::error('Erreur suppression', 500);
 
        ActivityLogService::log('delete_categorie', $user['user_id'], ['entity' => 'categorie', 'entity_id' => $id]);
        ApiResponse::success(null, 'Catégorie supprimée');
    }
    //activate
    public function activate(int $id): void
    {
        $user = $this->requireAdmin();
        $cat  = CategorieService::getById($id);
        if (!$cat) ApiResponse::error('Catégorie introuvable', 404);
 
        CategorieService::activate($id);
        ActivityLogService::log('activate_categorie', $user['user_id'], ['entity' => 'categorie', 'entity_id' => $id]);
        ApiResponse::success(null, 'Catégorie activée');
    }
    //deactivate
    public function deactivate(int $id): void
    {
        $user = $this->requireAdmin();
        $cat  = CategorieService::getById($id);
        if (!$cat) ApiResponse::error('Catégorie introuvable', 404);
 
        CategorieService::deactivate($id);
        ActivityLogService::log('deactivate_categorie', $user['user_id'], ['entity' => 'categorie', 'entity_id' => $id]);
        ApiResponse::success(null, 'Catégorie désactivée');
    }

    public function show(int $id): void
    {
        $auth = $this->requireAdmin();
        $cat  = CategorieService::getById($id);
        if (!$cat) ApiResponse::error('Catégorie introuvable', 404);
 
        ActivityLogService::log('view_categorie', $auth['user_id'] ?? null, ['entity' => 'categorie', 'entity_id' => $id]);
        ApiResponse::success($cat->toArray());
    }

}