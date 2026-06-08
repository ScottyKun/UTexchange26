<?php
//connexion et interaction avec la BD PostgreSQL

class Database
{
    private static $cnx;

    private static function init()
    {
        try {
        $host = $_ENV['DB_HOST'];
        $dbname = $_ENV['DB_NAME'];
        $port = $_ENV['DB_PORT'];
        $username = $_ENV['DB_USER'];
        $password = $_ENV['DB_PASS'];

        $dsn = "pgsql:host=$host;port=$port;dbname=$dbname";

        self::$cnx = new PDO($dsn, $username, $password, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]);
        } catch (PDOException $e) {
            error_log($e->getMessage());
            header("Location: /500");
            exit;
        }
    }

    private static function getConx()
    {
        if (self::$cnx === null) {
            self::init();
        }
        return self::$cnx;
    }

    public static function execute($rq, $tab)
    {
        try {
            $stm = self::getConx()->prepare($rq);
            $r = $stm->execute($tab);
            return $r;
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return false;
        }
    }

    public static function query($rq, $class, $tab = array())
    {
        try {
            $stm = self::getConx()->prepare($rq);
            $stm->execute($tab);
            $stm->setFetchMode(PDO::FETCH_CLASS, $class);
            return $stm->fetchAll();
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return [];
        }
    }

    public static function find($rq, $class, $tab = array())
    {
        try {
            $stm = self::getConx()->prepare($rq);
            $stm->execute($tab);
            $stm->setFetchMode(PDO::FETCH_CLASS, $class);
            return $stm->fetch();
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return null;
        }
    }

    public static function count($rq, $tab){
        try {
            $stm = self::getConx()->prepare($rq);
            $stm->execute($tab);
            return (int) $stm->fetchColumn();
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return 0;
        }
    }

    public static function insertAndGetId($rq, $tab = [])
    {
        try {
            $stm = self::getConx()->prepare($rq);
            $stm->execute($tab);
            return $stm->fetchColumn(); // récupère le RETURNING id
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return false;
        }
    }

    //recuperer tes donnees sans classe, utile pour nos statistiques
    public static function fetch($rq, $tab = [])
    {
        try {
            $stm = self::getConx()->prepare($rq);
            $stm->execute($tab);

            return $stm->fetch(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {
            error_log($e->getMessage());
            return null;
        }
    }
    public static function fetchAllAssoc($rq, $tab = [])
    {
        try {
            $stm = self::getConx()->prepare($rq);
            $stm->execute($tab);

            return $stm->fetchAll(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {
            error_log($e->getMessage());
            return [];
        }
    }
}
