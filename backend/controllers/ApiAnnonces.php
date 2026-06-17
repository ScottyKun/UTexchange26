<?php

class ApiAnnonces extends BaseApiController
{
    //all
    public function index(): void{
        $auth = $this->tryAuth();
        $filters = [
            'cat_id'    => $this->getQuery()['cat_id']    ?? null,
            'search'    => $this->getQuery()['search']    ?? null,
            'min_price' => $this->getQuery()['min_price'] ?? null,
            'max_price' => $this->getQuery()['max_price'] ?? null,
            'type'      => $this->getQuery()['type']      ?? null,
        ];
 
        $annonces = AnnonceService::filter($filters);
        $data = [];
        foreach ($annonces as $a) {
            $row  = $a->toArray();
            $cover = PhotoService::getCover($a->getId());
            $row['cover'] = $cover ? $cover->toArray() : null;
            $data[]  = $row;
        }
 
        ActivityLogService::log('list_annonces', $auth['user_id'] ?? null, ['filters' => array_filter($filters)]);
        ApiResponse::success($data);
       
    }
    //create
    public function store(): void{
        $user  = $this->requireAuth();
        $body  = $this->getBody();
        $missing = $this->validateRequired($body, ['categorie_id', 'title', 'description', 'price', 'type']);
        if ($missing) ApiResponse::error('Champs requis : ' . implode(', ', $missing), 422);
 
        $data = [
            'user_id' => $user['user_id'],
            'categorie_id' => $body['categorie_id'],
            'title' => $body['title'],
            'description'  => $body['description'],
            'price' => $body['price'],
            'type'  => $body['type'],
            'status' => $body['status'] ?? 'active',
            'location' => $body['location'] ?? '',
        ];
 
        $newId = AnnonceService::add($data);
        if (!$newId) ApiResponse::error('Erreur lors de la création', 500);
 
        ActivityLogService::log('create_annonce', $user['user_id'], ['entity' => 'annonce', 'entity_id' => $newId]);
        ApiResponse::success(['id' => $newId], 'Annonce créée', 201);
    }
    //delete
    public function delete(int $id): void{
        $user = $this->requireAuth();
        $annonce = AnnonceService::getById($id);
        if (!$annonce) ApiResponse::error('Annonce introuvable', 404);
        if ($annonce->getUtilisateurId() != $user['user_id'] && $user['role'] !== 'Administrateur') {
            ApiResponse::error('Non autorisé', 403);
        }
 
        PhotoService::deleteByAnnonce($id);
        AnnonceService::delete($id);
 
        ActivityLogService::log('delete_annonce', $user['user_id'], ['entity' => 'annonce', 'entity_id' => $id]);
        ApiResponse::success(null, 'Annonce supprimée');
    }
    //update
    public function update(int $id): void{
        $user = $this->requireAuth();
        $annonce = AnnonceService::getById($id);
        if (!$annonce) ApiResponse::error('Annonce introuvable', 404);
        if ($annonce->getUtilisateurId() != $user['user_id']) ApiResponse::error('Non autorisé', 403);
 
        $body = $this->getBody();
        $data = [
            'categorie_id' => $body['categorie_id'] ?? $annonce->getCategorieId(),
            'title' => $body['title']  ?? $annonce->getTitle(),
            'description'  => $body['description']  ?? $annonce->getDescription(),
            'price'  => $body['price'] ?? $annonce->getPrice(),
            'type'  => $body['type'] ?? $annonce->getType(),
            'status' => $body['status']  ?? $annonce->getStatus(),
            'location' => $body['location']   ?? $annonce->getLocation(),
        ];
 
        $r = AnnonceService::update($id, $data);
        if (!$r) ApiResponse::error('Erreur modification', 500);
 
        ActivityLogService::log('update_annonce', $user['user_id'], ['entity' => 'annonce', 'entity_id' => $id]);
        ApiResponse::success(null, 'Annonce mise à jour');
    }
    //updateType
    public function updateType(int $id): void{
        $user = $this->requireAuth();
        $annonce = AnnonceService::getById($id);
        if (!$annonce) ApiResponse::error('Annonce introuvable', 404);
        if ($annonce->getUtilisateurId() != $user['user_id']) ApiResponse::error('Non autorisé', 403);
 
        $body = $this->getBody();
        if (empty($body['type'])) ApiResponse::error('Champ type requis', 422);
 
        AnnonceService::updateType($id, $body['type']);
        ActivityLogService::log('update_annonce_type', $user['user_id'], ['entity' => 'annonce', 'entity_id' => $id, 'type' => $body['type']]);
        ApiResponse::success(null, 'Type mis à jour');  
    }
    //updateStatus
    public function updateStatus(int $id): void{
        $user = $this->requireAuth();
        $annonce = AnnonceService::getById($id);
        if (!$annonce) ApiResponse::error('Annonce introuvable', 404);
        if ($annonce->getUtilisateurId() != $user['user_id']) ApiResponse::error('Non autorisé', 403);
 
        $body = $this->getBody();
        if (empty($body['status'])) ApiResponse::error('Champ status requis', 422);
 
        AnnonceService::updateStatus($id, $body['status']);
        ActivityLogService::log('update_annonce_status', $user['user_id'], ['entity' => 'annonce', 'entity_id' => $id, 'status' => $body['status']]);
        ApiResponse::success(null, 'Statut mis à jour');
    }
    //getAllByUser
    public function mine(): void{
        $user = $this->requireAuth();
        $annonces = AnnonceService::getAllByUser($user['user_id']);
 
        $data = [];
        foreach ($annonces as $a) {
            $row = $a->toArray();
            $cover  = PhotoService::getCover($a->getId());
            $row['cover'] = $cover ? $cover->toArray() : null;
            $data[] = $row;
        }
 
        ActivityLogService::log('list_my_annonces', $user['user_id']);
        ApiResponse::success($data);
      
    }
    //show
    public function show(int $id): void{
        $auth    = $this->tryAuth();
        $annonce = AnnonceService::getById($id);
        if (!$annonce) ApiResponse::error('Annonce introuvable', 404);
 
        AnnonceService::incrementView($id);
 
        $seller = UserService::getById($annonce->getUtilisateurId());
        $photos  = PhotoService::getByAnnonce($id);
        $avisStats = MongoMessageService::getAvisStatsByVendeur($annonce->getUtilisateurId());
 
        $data  = $annonce->toArray();
        $data['seller']  = $seller ? $seller->toArray() : null;
        $data['photos']  = array_map(fn($p) => $p->toArray(), $photos);
        $data['avis_stats'] = $avisStats;
 
        ActivityLogService::log('view_annonce', $auth['user_id'] ?? null, ['entity' => 'annonce', 'entity_id' => $id]);
        ApiResponse::success($data);
    }


    //Photos
    //show photos
    public function getPhotos(int $annonceId, string $fichier): void{
        $auth = $this->tryAuth();
        $fic  = urldecode($fichier);

        ActivityLogService::log('serve_photo', $auth['user_id'] ?? null, [
            'entity'    => 'annonce',
            'entity_id' => $annonceId,
        ]);

        PhotoService::serve($annonceId, $fic);
    }
    //upload
    public function uploadPhotos(int $id): void
    {
        $user = $this->requireAuth();
        $annonce = AnnonceService::getById($id);
        if (!$annonce) ApiResponse::error('Annonce introuvable', 404);
        if ($annonce->getUtilisateurId() != $user['user_id']) ApiResponse::error('Non autorisé', 403);
        if (empty($_FILES['photos'])) ApiResponse::error('Aucune photo reçue', 400);
 
        $result = PhotoService::upload($_FILES['photos'], $id);
        if (!$result['success']) ApiResponse::error('Erreur upload', 400, $result['errors']);
 
        $photos = PhotoService::getByAnnonce($id);
        ActivityLogService::log('upload_photos', $user['user_id'], ['entity' => 'annonce', 'entity_id' => $id]);
        ApiResponse::success(array_map(fn($p) => $p->toArray(), $photos), 'Photos uploadées', 201);
    }
    //deletePhoto
    public function deletePhoto(int $id): void{
        $user  = $this->requireAuth();
        $photo = PhotoService::getById($id);
        if (!$photo) ApiResponse::error('Photo introuvable', 404);
 
        $annonce = AnnonceService::getById($photo->getAnnonceId());
        if (!$annonce || $annonce->getUtilisateurId() != $user['user_id']) {
            ApiResponse::error('Non autorisé', 403);
        }
 
        PhotoService::delete($id);
        ActivityLogService::log('delete_photo', $user['user_id'], ['entity' => 'photo', 'entity_id' => $id]);
        ApiResponse::success(null, 'Photo supprimée');
    }
    //setcover
    public function setCover(int $id): void
    {
        $user  = $this->requireAuth();
        $photo = PhotoService::getById($id);
        if (!$photo) ApiResponse::error('Photo introuvable', 404);
 
        $annonce = AnnonceService::getById($photo->getAnnonceId());
        if (!$annonce || $annonce->getUtilisateurId() != $user['user_id']) {
            ApiResponse::error('Non autorisé', 403);
        }
 
        PhotoService::setCover($id, $photo->getAnnonceId());
        ActivityLogService::log('set_cover_photo', $user['user_id'], ['entity' => 'photo', 'entity_id' => $id]);
        ApiResponse::success(null, 'Photo de couverture mise à jour');
    }

}