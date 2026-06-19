<?php

class Message{
    private $id;
    private $expediteur_id;
    private $contenu;
    private $is_read;
    private $created_at;

    public function getId()
    {
        return $this->id;
    }
    public function getExpediteurId()
    {
        return $this->expediteur_id;
    }

    public function getContenu()
    {
        return $this->contenu;
    }

    public function getIsRead()
    {
        return $this->is_read;
    }

    public function getCreatedAt()
    {
        return $this->created_at;
    }

    public function __construct(
        ?string $id,
        int     $expediteur_id,
        string  $contenu,
        bool    $is_read    = false,
        string  $created_at = ''
    ) {
        $this->id             = $id;
        $this->expediteur_id  = $expediteur_id;
        $this->contenu        = $contenu;
        $this->is_read        = $is_read;
        $this->created_at     = $created_at ?: date('Y-m-d H:i:s');
    }

    public function toArray()
    {
        return [
            'id' => $this->id,
            'expediteur_id' => $this->expediteur_id,
            'contenu' => $this->contenu,
            'is_read' => $this->is_read,
            'created_at' => $this->created_at
        ];
    }

     public static function fromArray(array $data): self
    {
        return new self(
            $data['id'] ?? null,
            (int)  ($data['expediteur_id'] ?? 0),
            $data['contenu']  ?? '',
            (bool) ($data['is_read']  ?? false),
            $data['created_at'] ?? ''
        );
    }
}