<?php

class ApiMessages extends BaseApiController
{
    //send
    public function sendMessage(string $id): void
    {
        $user = $this->requireAuth();
        $conv = MongoMessageService::getById($id);
        if (!$conv) ApiResponse::error('Conversation introuvable', 404);
        if (!$conv->isParticipant($user['user_id'])) ApiResponse::error('Non autorisé', 403);
        if ($conv->getStatus() === 'terminee') ApiResponse::error('Conversation terminée', 400);
 
        $body    = $this->getBody();
        $contenu = trim($body['contenu'] ?? '');
        if (empty($contenu)) ApiResponse::error('Le message ne peut pas être vide', 422);
 
        $r = MongoMessageService::sendMessage($id, $user['user_id'], $contenu);
        if (!$r) ApiResponse::error('Erreur lors de l\'envoi', 500);
 
        ActivityLogService::log('send_message', $user['user_id'], ['entity' => 'conversation', 'entity_id' => $id]);
        ApiResponse::success(null, 'Message envoyé', 201);
    }
    //update
    public function updateMessage(string $messageId): void
    {
        $user = $this->requireAuth();
        $body = $this->getBody();
        $convId  = $body['conv_id'] ?? null;
        $contenu = trim($body['contenu'] ?? '');
 
        if (!$convId) ApiResponse::error('conv_id requis', 422);
        if (empty($contenu)) ApiResponse::error('Contenu requis', 422);
 
        $conv = MongoMessageService::getById($convId);
        if (!$conv) ApiResponse::error('Conversation introuvable', 404);
 
        $msg = null;
        foreach ($conv->getMessages() as $m) {
            if ($m->getId() === $messageId) { $msg = $m; break; }
        }
        if (!$msg) ApiResponse::error('Message introuvable', 404);
        if ($msg->getExpediteurId() != $user['user_id']) ApiResponse::error('Non autorisé', 403);
 
        MongoMessageService::updateMessage($convId, $messageId, $contenu);
 
        ActivityLogService::log('update_message', $user['user_id'], ['entity' => 'conversation', 'entity_id' => $convId]);
        ApiResponse::success(null, 'Message modifié');
    }
    //delete
    public function deleteMessage(string $messageId): void
    {
        $user = $this->requireAuth();
        $body = $this->getBody();
        $convId = $body['conv_id'] ?? null;
        if (!$convId) ApiResponse::error('conv_id requis dans le body', 422);
 
        $conv = MongoMessageService::getById($convId);
        if (!$conv) ApiResponse::error('Conversation introuvable', 404);
        if (!$conv->isParticipant($user['user_id'])) ApiResponse::error('Non autorisé', 403);
 
        // Vérifier que c'est bien le message de cet utilisateur
        $msg = null;
        foreach ($conv->getMessages() as $m) {
            if ($m->getId() === $messageId) { $msg = $m; break; }
        }
        if (!$msg) ApiResponse::error('Message introuvable', 404);
        if ($msg->getExpediteurId() != $user['user_id']) ApiResponse::error('Non autorisé', 403);
 
        MongoMessageService::deleteMessage($convId, $messageId);
 
        ActivityLogService::log('delete_message', $user['user_id'], ['entity' => 'conversation', 'entity_id' => $convId]);
        ApiResponse::success(null, 'Message supprimé');
    }
    //getMessages
    public function getMessages(string $id): void
    {
        $user = $this->requireAuth();
        $conv = MongoMessageService::getById($id);
        if (!$conv) ApiResponse::error('Conversation introuvable', 404);
        if (!$conv->isParticipant($user['user_id'])) ApiResponse::error('Non autorisé', 403);
 
        MongoMessageService::markAsRead($id, $user['user_id']);
        $messages = array_map(fn($m) => $m->toArray(), $conv->getMessages());
 
        ActivityLogService::log('list_messages', $user['user_id'], ['entity' => 'conversation', 'entity_id' => $id]);
        ApiResponse::success($messages);
    }
    //count unread
    public function unreadCount(): void
    {
        $user  = $this->requireAuth();
        $count = MongoMessageService::getUnreadCount($user['user_id']);
 
        ActivityLogService::log('get_unread_count', $user['user_id']);
        ApiResponse::success(['count' => $count]);
    }
    //markAsRead
    public function markAsRead(string $id): void
    {
        $user = $this->requireAuth();
        $conv = MongoMessageService::getById($id);
        if (!$conv) ApiResponse::error('Conversation introuvable', 404);
        if (!$conv->isParticipant($user['user_id'])) ApiResponse::error('Non autorisé', 403);
 
        MongoMessageService::markAsRead($id, $user['user_id']);
 
        ActivityLogService::log('mark_messages_read', $user['user_id'], ['entity' => 'conversation', 'entity_id' => $id]);
        ApiResponse::success(null, 'Messages marqués comme lus');
    }
    
}