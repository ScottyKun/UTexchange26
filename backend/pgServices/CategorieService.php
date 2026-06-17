<?php
require_once __DIR__ . '/../model/categorie.php';
require_once __DIR__ . '/../core/database.php';

class CategorieService{
    //all
    public static function getAll(){
        $rq= "SELECT * FROM categories ORDER BY id DESC";
        return Database::query($rq, "Categorie");
    }
    //add
    public static function add($data){
        $rq= "INSERT INTO categories (parent_id, nom, is_active, created_at) 
                VALUES (:parent_id, :nom, true, NOW())";

        $tab = [
            "parent_id" => $data["parent_id"] ?? null,
            "nom" => $data["nom"]
        ];

        return Database::execute($rq, $tab);
    }
    //edit
    public static function edit($id, $data){
        $cat= self::getById($id);
        if(!$cat){
            return false;
        }

        $rq = "UPDATE categories SET 
                nom = :nom,
                parent_id = :parent_id
               WHERE id = :id";

        $tab = [
            "nom" => $data["nom"],
            "parent_id" => $data["parent_id"],
            "id" => $id
        ];

        return Database::execute($rq, $tab);
    }
    //getById
    public static function getById($id){
        $rq= "SELECT * FROM categories WHERE id = :id";
        $tab["id"] = $id;

        return Database::find($rq, "Categorie", $tab);
    }
    //getByParentId
    public static function getByParentId($id){
        $rq= "SELECT * FROM categories WHERE parent_id = :id";
        $tab["id"] = $id;

        return Database::find($rq, "Categorie", $tab);
    }
    //delete
    public static function delete($id){
        $rq= "DELETE FROM categories WHERE id = :id";
        $tab["id"] = $id;

        return Database::execute($rq, $tab);
    }
    //deactivate
    public static function deactivate($id){
        $rq= "UPDATE categories SET is_active = false WHERE id = :id";
        $tab["id"] = $id;

        return Database::execute($rq, $tab);
    }
    //activate
    public static function activate($id){
        $rq= "UPDATE categories SET is_active = true WHERE id = :id";
        $tab["id"] = $id;

        return Database::execute($rq, $tab);
    }
    //getAllParent
    public static function getAllParents(){
        $rq="SELECT * FROM categories WHERE parent_id IS NULL ORDER BY parent_id DESC";
        return Database::query($rq, "Categorie");
    }
    //Parents + enfants
    public static function getParentsWithChildren(): array
    {
        $rq  = "SELECT id, nom, parent_id
            FROM categories
            WHERE is_active = true
            ORDER BY nom ASC";

        $all = Database::query($rq, 'Categorie');

        $parents  = [];
        $children = [];

        foreach ($all as $cat) {
            if ($cat->getParentId() === null) {
                $parents[$cat->getId()] = [
                    'id'      => $cat->getId(),
                    'nom'     => $cat->getNom(),
                    'enfants' => [],
                ];
            } else {
                $children[$cat->getParentId()][] = [
                    'id'  => $cat->getId(),
                    'nom' => $cat->getNom(),
                ];
            }
        }

        foreach ($children as $parentId => $enfants) {
            if (isset($parents[$parentId])) {
                $parents[$parentId]['enfants'] = $enfants;
            }
        }

        return array_values($parents);
    }
}