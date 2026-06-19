<?php

class ApiConversations extends BaseApiController
{
    //allByUser
    public function index(): void
    {
        $user = $this->requireAuth();
        $convs = MongoMessageService::getByUser($user['user_id']);
 
        ActivityLogService::log('list_conversations', $user['user_id']);
        ApiResponse::success(array_map(fn($c) => $c->toArray(false, false), $convs));
    }
    //store
    public function store(): void
    {
        $user = $this->requireAuth();
        $body = $this->getBody();
        $missing = $this->validateRequired($body, ['annonce_id']);
        if ($missing) ApiResponse::error('Champ requis : annonce_id', 422);
 
        $annonce = AnnonceService::getById((int) $body['annonce_id']);
        if (!$annonce) ApiResponse::error('Annonce introuvable', 404);
 
        if ($annonce->getUtilisateurId() == $user['user_id']) {
            ApiResponse::error('Impossible de contacter votre propre annonce', 400);
        }
 
        $vendeur = UserService::getById($annonce->getUtilisateurId());
        $acheteur = UserService::getById($user['user_id']);
 
        $convId = MongoMessageService::createConversation([
            'annonce_id'    => $annonce->getId(),
            'annonce_title' => $annonce->getTitle(),
            'acheteur_id'   => $user['user_id'],
            'acheteur_nom'  => $acheteur ? $acheteur->getNom() . ' ' . $acheteur->getPrenom() : '',
            'vendeur_id'    => $annonce->getUtilisateurId(),
            'vendeur_nom'   => $vendeur ? $vendeur->getNom() . ' ' . $vendeur->getPrenom() : '',
        ]);
 
        if (!$convId) ApiResponse::error('Impossible de créer la conversation', 500);
 
        ActivityLogService::log('create_conversation', $user['user_id'], ['entity' => 'annonce', 'entity_id' => $annonce->getId()]);
        ApiResponse::success(['id' => $convId], 'Conversation ouverte', 201);
    }
    //terminate
    public function terminate(string $id): void
    {
        $user = $this->requireAuth();
        $conv = MongoMessageService::getById($id);
        if (!$conv) ApiResponse::error('Conversation introuvable', 404);
        if (!$conv->isVendeur($user['user_id'])) ApiResponse::error('Seul le vendeur peut terminer la conversation', 403);
        if ($conv->getStatus() === 'terminee') ApiResponse::error('Conversation déjà terminée', 400);
 
        MongoMessageService::terminate($id);
 
        ActivityLogService::log('terminate_conversation', $user['user_id'], ['entity' => 'conversation', 'entity_id' => $id]);
        ApiResponse::success(null, 'Conversation terminée. Vous pouvez maintenant laisser un avis.');
    }
    //delete
    public function delete(string $id): void
    {
        $user = $this->requireAuth();
        $conv = MongoMessageService::getById($id);
        if (!$conv) ApiResponse::error('Conversation introuvable', 404);
        if (!$conv->isParticipant($user['user_id']) && $user['role'] !== 'Administrateur') {
            ApiResponse::error('Non autorisé', 403);
        }
 
        MongoMessageService::delete($id);
 
        ActivityLogService::log('delete_conversation', $user['user_id'], ['entity' => 'conversation', 'entity_id' => $id]);
        ApiResponse::success(null, 'Conversation supprimée');
    }
    //show
    public function show(string $id): void
    {
        $user = $this->requireAuth();
        $conv = MongoMessageService::getById($id);
        if (!$conv) ApiResponse::error('Conversation introuvable', 404);
        if (!$conv->isParticipant($user['user_id'])) ApiResponse::error('Non autorisé', 403);
 
        // Marquer les messages reçus comme lus
        MongoMessageService::markAsRead($id, $user['user_id']);
 
        ActivityLogService::log('view_conversation', $user['user_id'], ['entity' => 'conversation', 'entity_id' => $id]);
        ApiResponse::success($conv->toArray());
    }

}