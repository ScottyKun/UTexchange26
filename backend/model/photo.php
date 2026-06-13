<?php

class Photo{
    private $id;
    private $annonce_id;
    private $chemin_fichier;
    private $nom_fichier;
    private $is_cover;
    private $taille_fichier;
    private $created_at;

    public function getId()
    {
        return $this->id;
    }

    public function getAnnonceId()
    {
        return $this->annonce_id;
    }

    public function getCheminFichier()
    {
        return $this->chemin_fichier;
    }

    public function getNomFichier()
    {
        return $this->nom_fichier;
    }

    public function getIsCover()
    {
        return $this->is_cover;
    }

    public function getTailleFichier()
    {
        return $this->taille_fichier;
    }

    public function getCreatedAt()
    {
        return $this->created_at;
    }

    public function toArray()
    {
        return [
            'id' => $this->id,
            'annonce_id' => $this->annonce_id,
            'nom_fichier' => $this->nom_fichier,
            'chemin_fichier' => $this->chemin_fichier,
            'is_cover' => $this->is_cover,
            'created_at' => $this->created_at
        ];
    }

}
