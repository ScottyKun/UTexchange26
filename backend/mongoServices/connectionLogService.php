<?php

use MongoDB\BSON\ObjectId;
use MongoDB\BSON\UTCDateTime;

class ConnectionLogService
{
    private static function col()
    {
        return MongoDatabase::getInstance()->getCollection('connection_logs');
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

    private static function context(): array
    {
        return [
            'ip'         => $_SERVER['HTTP_X_FORWARDED_FOR']
                            ?? $_SERVER['REMOTE_ADDR']
                            ?? null,
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null,
        ];
    }

    public static function logLogin(int $userId, string $email): void
    {
        self::write([
            'user_id' => $userId,
            'email'   => $email,
            'action'  => 'login',
            'success' => true,
        ]);
    }


    public static function logLogout(int $userId, string $email): void
    {
        self::write([
            'user_id' => $userId,
            'email'   => $email,
            'action'  => 'logout',
            'success' => true,
        ]);
    }

    
    public static function logFailedLogin(string $email): void
    {
        self::write([
            'user_id' => null,
            'email'   => $email,
            'action'  => 'failed_login',
            'success' => false,
        ]);
    }

  
    private static function write(array $data): void
    {
        try {
            self::col()->insertOne(array_merge($data, self::context(), [
                'created_at' => new UTCDateTime(),
            ]));
        } catch (\Exception $e) {
            error_log('[ConnectionLogService] ' . $e->getMessage());
        }
    }

  
    public static function getAll(int $limit = 100, int $offset = 0): array
    {
        try {
            $cursor = self::col()->find(
                [],
                [
                    'sort'    => ['created_at' => -1],
                    'limit'   => $limit,
                    'skip'    => $offset,
                    'typeMap' => ['root' => 'array', 'document' => 'array', 'array' => 'array'],
                ]
            );
            return array_map(
                fn($doc) => self::normalize($doc),
                iterator_to_array($cursor)
            );
        } catch (\Exception $e) {
            error_log('[ConnectionLogService] getAll: ' . $e->getMessage());
            return [];
        }
    }

    public static function getByUser(int $userId, int $limit = 20): array
    {
        try {
            $cursor = self::col()->find(
                ['user_id' => $userId],
                [
                    'sort'    => ['created_at' => -1],
                    'limit'   => $limit,
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

    public static function getFailedLogins(int $limit = 50): array
    {
        try {
            $cursor = self::col()->find(
                ['action' => 'failed_login'],
                [
                    'sort'    => ['created_at' => -1],
                    'limit'   => $limit,
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