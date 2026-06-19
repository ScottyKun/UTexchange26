<?php

class User{
    private $id;
    private $nom;
    private $prenom;
    private $email;
    private $mot_de_passe;
    private $role_id;
    private $campus;
    private $est_actif;
    private $date_inscription;
    private $email_verifie;
    private $modif_inscription;

    public function getModifInscription()
    {
        return $this->modif_inscription;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getNom()
    {
        return $this->nom;
    }

    public function getPrenom()
    {
        return $this->prenom;
    }

    public function getEmail()
    {
        return $this->email;
    }

    public function getCampus()
    {
        return $this->campus;
    }

    public function getEstActif()
    {
        return $this->est_actif;
    }

    public function getDateIns()
    {
        return $this->date_inscription;
    }

    public function getEmailVerified()
    {
        return $this->email_verifie;
    }

    public function getPassword()
    {
        return $this->mot_de_passe;
    }

    public function getRoleId()
    {
        return $this->role_id;
    }

    public function toArray()
    {
        return [
            'id' => $this->id,
            'nom' => $this->nom,
            'prenom' => $this->prenom,
            'email' => $this->email,
            'campus' => $this->campus,
            'est_actif' => $this->est_actif,
            'date_inscription' => $this->date_inscription,
            'email_verifie' => $this->email_verifie,
            'role_id' => $this->role_id,
            'modif_inscription' => $this->modif_inscription
        ];
    }

}