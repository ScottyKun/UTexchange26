<?php
 
class ApiAvis extends BaseApiController
{
    //save
    public function store(string $convId): void
    {
        $user    = $this->requireAuth();
        $body    = $this->getBody();
        $missing = $this->validateRequired($body, ['note', 'commentaire']);
        if ($missing) ApiResponse::error('Champs requis : ' . implode(', ', $missing), 422);
 
        $note = (int) $body['note'];
        if ($note < 1 || $note > 5) ApiResponse::error('La note doit être entre 1 et 5', 422);
 
        $conv = MongoMessageService::getById($convId);
        if (!$conv) ApiResponse::error('Conversation introuvable', 404);
        if (!$conv->isParticipant($user['user_id'])) ApiResponse::error('Non autorisé', 403);
 
        $r = MongoMessageService::addAvis($convId, $user['user_id'], $note, $body['commentaire']);
 
        if (!$r) {
            if ($conv->getStatus() !== 'terminee') {
                ApiResponse::error('La conversation doit être terminée pour laisser un avis', 400);
            }
            if ($conv->hasAvisFromUser($user['user_id'])) {
                ApiResponse::error('Vous avez déjà laissé un avis pour cette conversation', 409);
            }
            ApiResponse::error('Erreur lors de l\'enregistrement de l\'avis', 500);
        }
 
        ActivityLogService::log('create_avis', $user['user_id'], ['entity' => 'conversation', 'entity_id' => $convId, 'note' => $note]);
        ApiResponse::success(null, 'Avis enregistré', 201);
    }
    //delete
    public function destroy(string $convId, string $avisId): void
    {
        $user = $this->requireAuth();
        $conv = MongoMessageService::getById($convId);
        if (!$conv) ApiResponse::error('Conversation introuvable', 404);
 
        // Vérifier que l'avis appartient à cet utilisateur ou que c'est un admin
        $avis = null;
        foreach ($conv->getAvis() as $a) {
            if ($a->getId() === $avisId) { $avis = $a; break; }
        }
        if (!$avis) ApiResponse::error('Avis introuvable', 404);
 
        if ($avis->getAcheteurId() != $user['user_id'] && $user['role_name'] !== 'Administrateur') {
            ApiResponse::error('Non autorisé', 403);
        }
 
        MongoMessageService::deleteAvis($convId, $avisId);
 
        ActivityLogService::log('delete_avis', $user['user_id'], ['entity' => 'conversation', 'entity_id' => $convId]);
        ApiResponse::success(null, 'Avis supprimé');
    }
    //allForUser
    public function byUser(int $userId): void
    {
        $auth  = $this->tryAuth();
        $avis  = MongoMessageService::getAvisByVendeur($userId);
        $stats = MongoMessageService::getAvisStatsByVendeur($userId);
 
        ActivityLogService::log('list_avis_vendeur', $auth['user_id'] ?? null, ['entity' => 'user', 'entity_id' => $userId]);
        ApiResponse::success([
            'stats' => $stats,
            'avis'  => array_map(fn($a) => $a->toArray(), $avis),
        ]);
    }

    //update
    public function update(string $convId, string $avisId): void
    {
        $user = $this->requireAuth();
        $body  = $this->getBody();
        $missing = $this->validateRequired($body, ['note', 'commentaire']);
        if ($missing) ApiResponse::error('Champs requis : ' . implode(', ', $missing), 422);

        $note = (int) $body['note'];
        if ($note < 1 || $note > 5) ApiResponse::error('La note doit être entre 1 et 5', 422);

        // Vérifier que c'est bien l'auteur
        $conv = MongoMessageService::getById($convId);
        if (!$conv) ApiResponse::error('Conversation introuvable', 404);

        $avis = null;
        foreach ($conv->getAvis() as $a) {
            if ($a->getId() === $avisId) { $avis = $a; break; }
        }
        if (!$avis) ApiResponse::error('Avis introuvable', 404);
        if ($avis->getAcheteurId() != $user['user_id']) ApiResponse::error('Non autorisé', 403);

        $r = MongoMessageService::updateAvis($convId, $avisId, $note, $body['commentaire']);
        if (!$r) ApiResponse::error('Erreur modification', 500);

        ActivityLogService::log('update_avis', $user['user_id'], ['entity' => 'conversation', 'entity_id' => $convId]);
        ApiResponse::success(null, 'Avis modifié');
    }

}