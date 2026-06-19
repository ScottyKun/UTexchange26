<?php

class Role{
    private $id;
    private $nom;
    private $is_active;
    private $description;
    private $created_at;

    public function getCreatedAt()
    {
        return $this->created_at;
    }

    public function getUpdatedAt()
    {
        return $this->updated_at;
    }
    private $updated_at;

    public function getId()
    {
        return $this->id;
    }

    public function getIsActive()
    {
        return $this->is_active;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function getNom()
    {
        return $this->nom;
    }

    public function toArray()
    {
        return [
            'id' => $this->id,
            'nom' => $this->nom,
            'is_active' => $this->is_active,
            'description' => $this->description,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at
        ];
    }
}