<?php

use MongoDB\BSON\ObjectId;
use MongoDB\BSON\UTCDateTime;

class ActivityLogService
{
    private static function col()
    {
        return MongoDatabase::getInstance()->getCollection('activity_logs');
    }

    private static function normalize(array $doc): array
    {
        if (isset($doc['_id'])) {
            $doc['id'] = (string) $doc['_id'];
            unset($doc['_id']);
        }
        foreach ($doc as &$value) {
            if ($value instanceof UTCDateTime) {
                $value = $value->toDateTime()->format('Y-m-d H:i:s');
            }
        }
        return $doc;
    }

   public static function log(string $action, ?int $userId = null, array $meta = []): void
    {
        try {
            $doc = [
                'user_id'    => $userId,
                'action'     => $action,
                'ip'         => $_SERVER['HTTP_X_FORWARDED_FOR']
                                ?? $_SERVER['REMOTE_ADDR']
                                ?? null,
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null,
                'created_at' => new UTCDateTime(),
            ];
 
            // entity / entity_id : extraits de $meta si présents
            if (isset($meta['entity'])) {
                $doc['entity'] = $meta['entity'];
                unset($meta['entity']);
            }
            if (isset($meta['entity_id'])) {
                $doc['entity_id'] = $meta['entity_id'];
                unset($meta['entity_id']);
            }
 
            // meta : inclus seulement si non vide
            if (!empty($meta)) {
                $doc['meta'] = $meta;
            }
 
            self::col()->insertOne($doc);
        } catch (\Exception $e) {
            error_log('[ActivityLogService] ' . $e->getMessage());
        }
    }

    public static function getAll(int $limit = 100, int $offset = 0): array
    {
        try {
            $cursor = self::col()->find(
                [],
                [
                    'sort'  => ['created_at' => -1],
                    'limit' => $limit,
                    'skip'  => $offset,
                    'typeMap' => ['root' => 'array', 'document' => 'array', 'array' => 'array'],
                ]
            );

            return array_map(
                fn($doc) => self::normalize($doc),
                iterator_to_array($cursor)
            );

        } catch (\Exception $e) {
            error_log('[ActivityLogService] getAll: ' . $e->getMessage());
            return [];
        }
    }

    public static function getByUser(int $userId, int $limit = 50): array
    {
        try {
            $cursor = self::col()->find(
                ['user_id' => $userId],
                [
                    'sort'   => ['created_at' => -1],
                    'limit'  => $limit,
                    'typeMap' => ['root' => 'array', 'document' => 'array', 'array' => 'array'],
                ]
            );
            return array_map(
                fn($doc) => self::normalize($doc),
                iterator_to_array($cursor)
            );
        } catch (\Exception $e) {
            return [];
        }
    }

    public static function getByAction(string $action, int $limit = 50): array
    {
        try {
            $cursor = self::col()->find(
                ['action' => $action],
                [
                    'sort'   => ['created_at' => -1],
                    'limit'  => $limit,
                    'typeMap' => ['root' => 'array', 'document' => 'array', 'array' => 'array'],
                ]
            );
            return array_map(
                fn($doc) => self::normalize($doc),
                iterator_to_array($cursor)
            );
        } catch (\Exception $e) {
            return [];
        }
    }

  
    public static function countAll(): int
    {
        try {
            return (int) self::col()->countDocuments([]);
        } catch (\Exception $e) {
            return 0;
        }
    }

}