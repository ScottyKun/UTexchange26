<?php
require_once __DIR__ . '/../model/annonce.php';
require_once __DIR__ . '/../core/database.php';

class StatistiqueService{
    //Les annonces (stats selon type et staut, total, lastest)
    public static function getAnnonceStats(){
        $rq="SELECT
            COUNT(*) FILTER (WHERE type = 'don') AS dons,
            COUNT(*) FILTER (WHERE type = 'troc') AS trocs,
            COUNT(*) FILTER (WHERE type = 'vente') AS ventes,
            COUNT(*) FILTER (WHERE type = 'location') AS locations,
            COUNT(*) FILTER (WHERE status = 'vendu')  AS vendues,
            COUNT(*) FILTER (WHERE status = 'active') AS actives,
            COUNT(*) FILTER (WHERE status = 'signale') AS signales
        FROM annonces";

        return Database::fetch($rq);
    }
    public static function getLatestAnnonces($limit = 10){
        $rq="SELECT *
        FROM annonces
        WHERE status = 'active'
        ORDER BY created_at DESC
        LIMIT $limit";

        return Database::query($rq, 'Annonce',[]);
    }
    public static function getNbAnnonces(){
        $rq="SELECT COUNT(*) FROM annonces";
        return Database::count($rq,[]);
    }
   
    //Les users (top 10 vendeurs, total)
    public static function getTopVendeurs($limit = 5){
       
    }
    public static function getNbUsers(){
        $rq="SELECT COUNT(*) FROM utilisateurs";
        return Database::count($rq,[]);
    }
    //Les categories
    public static function getTopCategories($limit = 5){
        $rq="SELECT
            c.id,
            c.nom,
            COUNT(a.id) AS total_annonces
        FROM categories c
        JOIN annonces a ON a.categorie_id = c.id
        GROUP BY c.id
        ORDER BY total_annonces DESC
        LIMIT $limit";

        return Database::fetchAllAssoc($rq);
    }

}
