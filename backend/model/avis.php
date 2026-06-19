<?php

class Avis{
    private $id;
    private ?string $conversation_id;
    private $acheteur_id;
    private $vendeur_id;
    private $note;
    private $commentaire;
    private $created_at;
    private $is_active;

    // Champs enrichis optionnels (remplis via agrégation)
    private ?string $annonce_title = null;
    private ?string $acheteur_nom = null;
    private ?string $vendeur_nom = null;


    public function getId()
    {
        return $this->id;
    }

    public function getConversationId()
    {
        return $this->conversation_id;
    }

    public function getAcheteurId()
    {
        return $this->acheteur_id;
    }

    public function getVendeurId()
    {
        return $this->vendeur_id;
    }

    public function getNote()
    {
        return $this->note;
    }

    public function getCommentaire()
    {
        return $this->commentaire;
    }

    public function getCreatedAt()
    {
        return $this->created_at;
    }

    public function getIsActive()
    {
        return $this->is_active;
    }

    public function __construct(
        ?string $id,
        int $acheteur_id,
        int $vendeur_id,
        int $note,
        string  $commentaire,
        bool $is_active  = true,
        string  $created_at = '',
        ?string $conversation_id =null
    ) {
        $this->id = $id;
        $this->acheteur_id = $acheteur_id;
        $this->vendeur_id = $vendeur_id;
        $this->note = $note;
        $this->commentaire  = $commentaire;
        $this->is_active = $is_active;
        $this->created_at = $created_at ?: date('Y-m-d H:i:s');
        $this->conversation_id = $conversation_id;
    }

    public function toArray(): array
    {
        $data = [
            'id'          => $this->id,
            'acheteur_id' => $this->acheteur_id,
            'vendeur_id'  => $this->vendeur_id,
            'note'        => $this->note,
            'commentaire' => $this->commentaire,
            'is_active'   => $this->is_active,
            'created_at'  => $this->created_at,
            'conversation_id' => $this->conversation_id,
        ];
 
        // Inclure les champs enrichis seulement s'ils sont définis
        if ($this->annonce_title !== null) $data['annonce_title'] = $this->annonce_title;
        if ($this->acheteur_nom  !== null) $data['acheteur_nom']  = $this->acheteur_nom;
        if ($this->vendeur_nom   !== null) $data['vendeur_nom']   = $this->vendeur_nom;
        
        return $data;
    }

    public static function fromArray(array $data): self
    {
        $obj = new self(
            $data['id']   ?? null,
            (int)  ($data['acheteur_id'] ?? 0),
            (int)  ($data['vendeur_id'] ?? 0),
            (int)  ($data['note']  ?? 1),
            $data['commentaire'] ?? '',
            (bool) ($data['is_active'] ?? true),
            $data['created_at']  ?? '',
            (string) ($data['conversation_id'] ?? '')
        );
 
        // Champs enrichis (agrégation admin / profil vendeur)
        $obj->annonce_title = $data['annonce_title'] ?? null;
        $obj->acheteur_nom  = $data['acheteur_nom']  ?? null;
        $obj->vendeur_nom   = $data['vendeur_nom']   ?? null;
 
        return $obj;
    }
}