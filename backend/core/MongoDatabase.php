<?php

use MongoDB\Client;
use MongoDB\Collection;
use MongoDB\Database;

//connexion et interaction avec la BD MongoDB

class MongoDatabase
{
    private static ?MongoDatabase $instance = null;
    private Client $client;
    private string $dbName;

    private function __construct()
    {
        $uri = $_ENV['MONGO_URI'] ;
        $this->dbName = $_ENV['MONGO_DB'] ;

        try {
            $this->client = new Client($uri);
        } catch (Exception $e) {
            error_log('[MongoDatabase] Connexion échouée : ' . $e->getMessage());
            ApiResponse::error('Erreur de connexion à la base de données NoSQL', 500);
        }
    }

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getCollection(string $name): Collection
    {
        return $this->client
            ->selectDatabase($this->dbName)
            ->selectCollection($name);
    }

    public function getDatabase(): Database
    {
        return $this->client->selectDatabase($this->dbName);
    }

    // Bloquer le clonage et la désérialisation du singleton
    private function __clone() {}
    public function __wakeup(): void {}
}
