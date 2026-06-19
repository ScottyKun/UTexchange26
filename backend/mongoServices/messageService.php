<?php

use MongoDB\BSON\ObjectId;
use MongoDB\BSON\UTCDateTime;

class MongoMessageService
{
    // Options typeMap : forcer les documents MongoDB en tableaux PHP natifs
    private const TYPE_MAP = [
        'typeMap' => [
            'root'     => 'array',
            'document' => 'array',
            'array'    => 'array',
        ]
    ];

    private static function col()
    {
        return MongoDatabase::getInstance()->getCollection('conversations');
    }

    //Mongodb-php
    private static function normalize(array $doc): array
    {
        if (isset($doc['_id'])) {
            $doc['id'] = (string) $doc['_id'];
            unset($doc['_id']);
        }

        foreach ($doc as $key => &$value) {
            if ($value instanceof UTCDateTime) {
                $value = $value->toDateTime()->format('Y-m-d H:i:s');
            } elseif ($value instanceof ObjectId) {
                $value = (string) $value;
            }
        }
        unset($value);

        if (isset($doc['messages']) && is_array($doc['messages'])) {
            $doc['messages'] = array_values(array_map(
                fn($m) => self::normalizeEmbedded((array) $m),
                $doc['messages']
            ));
        }

        if (isset($doc['avis']) && is_array($doc['avis'])) {
            $doc['avis'] = array_values(array_map(
                fn($a) => self::normalizeEmbedded((array) $a),
                $doc['avis']
            ));
        }

        return $doc;
    }

    // Normalise un sous-document embarqué (message ou avis).
    private static function normalizeEmbedded(array $item): array
    {
        if (isset($item['_id'])) {
            $item['id'] = (string) $item['_id'];
            unset($item['_id']);
        }
        foreach ($item as &$value) {
            if ($value instanceof UTCDateTime) {
                $value = $value->toDateTime()->format('Y-m-d H:i:s');
            } elseif ($value instanceof ObjectId) {
                $value = (string) $value;
            }
        }
        return $item;
    }

    //Conversations

   //create
    public static function createConversation(array $data): string|false
    {
        // Anti-doublon : une seule conversation par annonce + acheteur
        $existing = self::col()->findOne(
            ['annonce_id' => (int) $data['annonce_id'], 'acheteur_id' => (int) $data['acheteur_id']],
            self::TYPE_MAP
        );

        if ($existing) {
            return (string) $existing['_id'] ?? $existing['id'];
        }

        try {
            $result = self::col()->insertOne([
                'annonce_id'      => (int) $data['annonce_id'],
                'annonce_title'   => $data['annonce_title'] ?? '',
                'acheteur_id'     => (int) $data['acheteur_id'],
                'acheteur_nom'    => $data['acheteur_nom']  ?? '',
                'vendeur_id'      => (int) $data['vendeur_id'],
                'vendeur_nom'     => $data['vendeur_nom']   ?? '',
                'status'          => 'active',
                'last_message'    => null,
                'last_message_at' => null,
                'messages'        => [],
                'avis'            => [],
                'created_at'      => new UTCDateTime(),
            ]);

            return $result->getInsertedCount() === 1
                ? (string) $result->getInsertedId()
                : false;
        } catch (\Exception $e) {
            error_log('[MongoMessageService] createConversation: ' . $e->getMessage());
            return false;
        }
    }

    //getById
    public static function getById(string $id): ?Conversation
    {
        try {
            $doc = self::col()->findOne(
                ['_id' => new ObjectId($id)],
                self::TYPE_MAP
            );
            return $doc ? Conversation::fromArray(self::normalize($doc)) : null;
        } catch (\Exception $e) {
            return null;
        }
    }

    //Liste des conversations
    public static function getByUser(int $userId): array
    {
        try {
            $cursor = self::col()->find(
                ['$or' => [['acheteur_id' => $userId], ['vendeur_id' => $userId]]],
                array_merge(self::TYPE_MAP, [
                    'sort'       => ['last_message_at' => -1],
                    'projection' => ['messages' => 0, 'avis' => 0],
                ])
            );

            return array_map(
                fn($doc) => Conversation::fromArray(self::normalize($doc)),
                iterator_to_array($cursor)
            );
        } catch (\Exception $e) {
            return [];
        }
    }

    //terminate conversation
    public static function terminate(string $id): bool
    {
        try {
            $r = self::col()->updateOne(
                ['_id' => new ObjectId($id)],
                ['$set' => ['status' => 'terminee']]
            );
            return $r->getModifiedCount() > 0;
        } catch (\Exception $e) {
            return false;
        }
    }

    //supprime une conversation
    public static function delete(string $id): bool
    {
        try {
            $r = self::col()->deleteOne(['_id' => new ObjectId($id)]);
            return $r->getDeletedCount() > 0;
        } catch (\Exception $e) {
            return false;
        }
    }

    //total de conversations
    public static function countAll(): int
    {
        try {
            return (int) self::col()->countDocuments([]);
        } catch (\Exception $e) {
            return 0;
        }
    }

    // Messages

   //send message
    public static function sendMessage(string $convId, int $expediteurId, string $contenu): bool
    {
        try {
            $r = self::col()->updateOne(
                ['_id' => new ObjectId($convId)],
                [
                    '$push' => ['messages' => [
                        '_id'           => new ObjectId(),
                        'expediteur_id' => $expediteurId,
                        'contenu'       => $contenu,
                        'is_read'       => false,
                        'created_at'    => new UTCDateTime(),
                    ]],
                    '$set' => [
                        'last_message'    => $contenu,
                        'last_message_at' => new UTCDateTime(),
                    ],
                ]
            );
            return $r->getModifiedCount() > 0;
        } catch (\Exception $e) {
            error_log('[MongoMessageService] sendMessage: ' . $e->getMessage());
            return false;
        }
    }

    //markasRead
    public static function markAsRead(string $convId, int $userId): void
    {
        try {
            self::col()->updateOne(
                ['_id' => new ObjectId($convId)],
                ['$set' => ['messages.$[elem].is_read' => true]],
                ['arrayFilters' => [[
                    'elem.expediteur_id' => ['$ne' => $userId],
                    'elem.is_read'       => false,
                ]]]
            );
        } catch (\Exception $e) {
            error_log('[MongoMessageService] markAsRead: ' . $e->getMessage());
        }
    }

    //nb messages non lus
    public static function getUnreadCount(int $userId): int
    {
        try {
            $pipeline = [
                ['$match'  => ['$or' => [['acheteur_id' => $userId], ['vendeur_id' => $userId]]]],
                ['$unwind' => '$messages'],
                ['$match'  => [
                    'messages.is_read'       => false,
                    'messages.expediteur_id' => ['$ne' => $userId],
                ]],
                ['$count' => 'total'],
            ];

            $rows = iterator_to_array(self::col()->aggregate($pipeline));
            return $rows ? (int) $rows[0]['total'] : 0;
        } catch (\Exception $e) {
            return 0;
        }
    }

    //delete message
    public static function deleteMessage(string $convId, string $messageId): bool
    {
        try {
            $r = self::col()->updateOne(
                ['_id' => new ObjectId($convId)],
                ['$pull' => ['messages' => ['_id' => new ObjectId($messageId)]]]
            );
            if ($r->getModifiedCount() > 0) {
                self::refreshLastMessage($convId);
            }
            return $r->getModifiedCount() > 0;
        } catch (\Exception $e) {
            return false;
        }
    }

    //update message
    public static function updateMessage(string $convId, string $messageId, string $contenu): bool
    {
        try {
            $r = self::col()->updateOne(
                ['_id' => new ObjectId($convId), 'messages._id' => new ObjectId($messageId)],
                ['$set' => ['messages.$.contenu' => $contenu]]
            );
            if ($r->getModifiedCount() > 0) {
                self::refreshLastMessage($convId);
            }
            return $r->getModifiedCount() > 0;
        } catch (\Exception $e) {
            return false;
        }
    }

    //Avis

    //add avis
    public static function addAvis(string $convId, int $auteurId, int $note, string $commentaire): bool
    {
        if ($note < 1 || $note > 5) return false;

        $conv = self::getById($convId);
        if (!$conv)  return false;
        if ($conv->getStatus() !== 'terminee')   return false;
        if ($conv->hasAvisFromUser($auteurId))   return false;
        if ($conv->isVendeur($auteurId)) return false; 

        try {
            $r = self::col()->updateOne(
                ['_id' => new ObjectId($convId)],
                ['$push' => ['avis' => [
                    '_id'         => new ObjectId(),
                    'auteur_id'   => $auteurId,
                    'acheteur_id' => $conv->getAcheteurId(),
                    'vendeur_id'  => $conv->getVendeurId(),
                    'note'        => $note,
                    'commentaire' => trim($commentaire),
                    'is_active'   => true,
                    'created_at'  => new UTCDateTime(),
                ]]]
            );
            return $r->getModifiedCount() > 0;
        } catch (\Exception $e) {
            error_log('[MongoMessageService] addAvis: ' . $e->getMessage());
            return false;
        }
    }

    //delete avis
    public static function deleteAvis(string $convId, string $avisId): bool
    {
        try {
            $r = self::col()->updateOne(
                ['_id' => new ObjectId($convId)],
                ['$pull' => ['avis' => ['_id' => new ObjectId($avisId)]]]
            );
            return $r->getModifiedCount() > 0;
        } catch (\Exception $e) {
            return false;
        }
    }

    //update avis
    public static function updateAvis(string $convId, string $avisId, int $note, string $commentaire): bool
    {
        if ($note < 1 || $note > 5) return false;

        try {
            $r = self::col()->updateOne(
                ['_id' => new ObjectId($convId), 'avis._id' => new ObjectId($avisId)],
                ['$set' => [
                    'avis.$.note'        => $note,
                    'avis.$.commentaire' => trim($commentaire),
                ]]
            );
            return $r->getModifiedCount() > 0;
        } catch (\Exception $e) {
            error_log('[MongoMessageService] updateAvis: ' . $e->getMessage());
            return false;
        }
    }

   //activate/deactivate avis
    public static function setAvisActif(string $convId, string $avisId, bool $actif): bool
    {
        try {
            $r = self::col()->updateOne(
                ['_id' => new ObjectId($convId), 'avis._id' => new ObjectId($avisId)],
                ['$set' => ['avis.$.is_active' => $actif]]
            );
            return $r->getModifiedCount() > 0;
        } catch (\Exception $e) {
            return false;
        }
    }

    //all avis
    public static function getAvisByVendeur(int $vendeurId): array
    {
        try {
            $pipeline = [
                ['$match'   => ['vendeur_id' => $vendeurId, 'avis' => ['$ne' => []]]],
                ['$unwind'  => '$avis'],
                ['$match'   => ['avis.is_active' => true]],
                ['$project' => [
                    'avis'          => 1,
                    'annonce_title' => 1,
                    'acheteur_nom'  => 1,
                ]],
                ['$sort' => ['avis.created_at' => -1]],
            ];

            
            $rows = array_map(
                fn($doc) => $doc->getArrayCopy(),
                iterator_to_array(self::col()->aggregate($pipeline))
            );

            return array_map(function (array $row) {
                $data = self::normalizeEmbedded((array) $row['avis']);
                $data['annonce_title'] = $row['annonce_title'] ?? '';
                $data['acheteur_nom']  = $row['acheteur_nom']  ?? '';
                return Avis::fromArray($data);
            }, $rows);
        } catch (\Exception $e) {
            return [];
        }
    }

    //all avis (admin)
    public static function getAllAvis(): array
    {
        try {
            $pipeline = [
                ['$match'   => ['avis' => ['$ne' => []]]],
                ['$unwind'  => '$avis'],
                ['$project' => [
                    '_id'  => 1,
                    'avis'=> 1,
                    'annonce_title' => 1,
                    'acheteur_nom'  => 1,
                    'vendeur_nom' => 1,
                    'vendeur_id' => 1,
                ]],
                ['$sort' => ['avis.created_at' => -1]],
            ];

             $rows = array_map(
                fn($doc) => $doc->getArrayCopy(),
                iterator_to_array(self::col()->aggregate($pipeline))
            );

            return array_map(function (array $row) {
                $data = self::normalizeEmbedded((array) $row['avis']);
                $data['annonce_title'] = $row['annonce_title'] ?? '';
                $data['acheteur_nom']  = $row['acheteur_nom']  ?? '';
                $data['vendeur_nom']   = $row['vendeur_nom']   ?? '';
                $data['vendeur_id']    = $row['vendeur_id']    ?? null;
                $data['conversation_id']  = isset($row['_id']) ? (string) $row['_id'] : null;
                return Avis::fromArray($data);
            }, $rows);
        } catch (\Exception $e) {
            return [];
        }
    }

    //stats avis
    public static function getAvisStatsByVendeur(int $vendeurId): array
    {
        try {
            $pipeline = [
                ['$match'   => ['vendeur_id' => $vendeurId]],
                ['$unwind'  => '$avis'],
                ['$match'   => ['avis.is_active' => true]],
                ['$group'   => [
                    '_id'    => null,
                    'total'  => ['$sum' => 1],
                    'moyenne' => ['$avg' => '$avis.note'],
                ]],
            ];

            $rows = iterator_to_array(self::col()->aggregate($pipeline));

            if (empty($rows)) return ['total' => 0, 'moyenne' => 0.0];

            return [
                'total'   => (int)   $rows[0]['total'],
                'moyenne' => round((float) $rows[0]['moyenne'], 1),
            ];
        } catch (\Exception $e) {
            return ['total' => 0, 'moyenne' => 0.0];
        }
    }

    //helpers
    private static function refreshLastMessage(string $convId): void
    {
        try {
            $doc = self::col()->findOne(
                ['_id' => new ObjectId($convId)],
                array_merge(self::TYPE_MAP, ['projection' => ['messages' => 1]])
            );
            if (!$doc) return;

            $messages = $doc['messages'] ?? [];

            if (empty($messages)) {
                $set = ['last_message' => null, 'last_message_at' => null];
            } else {
                $last = end($messages);
                $set  = [
                    'last_message'    => $last['contenu'],
                    'last_message_at' => $last['created_at'] instanceof UTCDateTime
                        ? $last['created_at']
                        : new UTCDateTime(strtotime($last['created_at']) * 1000),
                ];
            }

            self::col()->updateOne(
                ['_id' => new ObjectId($convId)],
                ['$set' => $set]
            );
        } catch (\Exception $e) {
            error_log('[MongoMessageService] refreshLastMessage: ' . $e->getMessage());
        }
    }
}