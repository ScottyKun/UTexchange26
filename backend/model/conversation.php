<?php

class Conversation{
    private ?string $id;
    private int $annonce_id;
    private string  $annonce_title;
    private int $acheteur_id;
    private string $acheteur_nom;
    private int $vendeur_id;
    private string $vendeur_nom;
    private string  $status;
    private ?string $last_message;
    private ?string $last_message_at;
    private string  $created_at;

    private int $unread_count = 0;
 
    private array $messages;
 
    private array $avis;

   public function getId(): ?string         { return $this->id; }
    public function getAnnonceId(): int      { return $this->annonce_id; }
    public function getAnnonceTitle(): string { return $this->annonce_title; }
    public function getAcheteurId(): int     { return $this->acheteur_id; }
    public function getAcheteurNom(): string { return $this->acheteur_nom; }
    public function getVendeurId(): int      { return $this->vendeur_id; }
    public function getVendeurNom(): string  { return $this->vendeur_nom; }
    public function getStatus(): string      { return $this->status; }
    public function getLastMessage(): ?string    { return $this->last_message; }
    public function getLastMessageAt(): ?string  { return $this->last_message_at; }
    public function getCreatedAt(): string   { return $this->created_at; }
    public function getMessages(): array   { return $this->messages; }
    public function getAvis(): array   { return $this->avis; }

    public function getUnreadCount(): int { return $this->unread_count; }

    public function __construct(
        ?string $id,
        int  $annonce_id,
        string  $annonce_title,
        int   $acheteur_id,
        string  $acheteur_nom,
        int  $vendeur_id,
        string  $vendeur_nom,
        string  $status          = 'active',
        ?string $last_message    = null,
        ?string $last_message_at = null,
        string  $created_at      = '',
        array   $messages        = [],
        array   $avis            = []
    ) {
        $this->id  = $id;
        $this->annonce_id   = $annonce_id;
        $this->annonce_title  = $annonce_title;
        $this->acheteur_id  = $acheteur_id;
        $this->acheteur_nom  = $acheteur_nom;
        $this->vendeur_id  = $vendeur_id;
        $this->vendeur_nom  = $vendeur_nom;
        $this->status = $status;
        $this->last_message   = $last_message;
        $this->last_message_at  = $last_message_at;
        $this->created_at = $created_at ?: date('Y-m-d H:i:s');
        $this->messages   = $messages;
        $this->avis  = $avis;
    }

    public function toArray(bool $withMessages = true, bool $withAvis = true): array
    {
        $data = [
            'id'              => $this->id,
            'annonce_id'      => $this->annonce_id,
            'annonce_title'   => $this->annonce_title,
            'acheteur_id'     => $this->acheteur_id,
            'acheteur_nom'    => $this->acheteur_nom,
            'vendeur_id'      => $this->vendeur_id,
            'vendeur_nom'     => $this->vendeur_nom,
            'status'          => $this->status,
            'last_message'    => $this->last_message,
            'last_message_at' => $this->last_message_at,
            'created_at'      => $this->created_at,
            'unread_count'    => $this->unread_count,
        ];
 
        if ($withMessages) {
            $data['messages'] = array_map(fn(Message $m) => $m->toArray(), $this->messages);
        }
 
        if ($withAvis) {
            $data['avis'] = array_map(fn(Avis $a) => $a->toArray(), $this->avis);
        }
 
        return $data;
    }

    public static function fromArray(array $data): self
    {
        $messages = array_map(
            fn(array $m) => Message::fromArray($m),
            $data['messages'] ?? []
        );
 
        $avis = array_map(
            fn(array $a) => Avis::fromArray($a),
            $data['avis'] ?? []
        );
 
        $conv= new self(
            $data['id']              ?? null,
            (int) ($data['annonce_id']  ?? 0),
            $data['annonce_title']   ?? '',
            (int) ($data['acheteur_id'] ?? 0),
            $data['acheteur_nom']    ?? '',
            (int) ($data['vendeur_id']  ?? 0),
            $data['vendeur_nom']     ?? '',
            $data['status']          ?? 'active',
            $data['last_message']    ?? null,
            $data['last_message_at'] ?? null,
            $data['created_at']      ?? '',
            $messages,
            $avis
        );
        $conv->unread_count = (int) ($data['unread_count'] ?? 0);
        return $conv;
    }

    public function hasAvisFromUser(int $userId): bool
    {
        foreach ($this->avis as $avis) {
            if ($avis->getAcheteurId() === $userId) {
                return true;
            }
        }
        return false;
    }

    public function isParticipant(int $userId): bool
    {
        return $this->acheteur_id === $userId || $this->vendeur_id === $userId;
    }

    public function isVendeur(int $userId): bool
    {
        return $this->vendeur_id === $userId;
    }
}
