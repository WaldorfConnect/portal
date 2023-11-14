<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;

class Mail extends Entity
{
    protected $attributes = [
        'id' => null,
        'recipient_id' => null,
        'subject' => null,
        'body' => null
    ];

    protected $casts = [
        'id' => 'integer',
        'recipient_id' => 'integer',
        'subject' => 'string',
        'body' => 'string',
    ];

    /**
     * @return ?int
     */
    public function getId(): ?int
    {
        return $this->attributes['id'];
    }

    /**
     * @return int
     */
    public function getRecipientId(): int
    {
        return $this->attributes['recipient_id'];
    }

    public function setRecipientId(int $recipientId): void
    {
        $this->attributes['recipient_id'] = $recipientId;
    }

    /**
     * @return string
     */
    public function getSubject(): string
    {
        return $this->attributes['subject'];
    }

    public function setSubject(string $subject): void
    {
        $this->attributes['subject'] = $subject;
    }

    /**
     * @return string
     */
    public function getBody(): string
    {
        return $this->attributes['body'];
    }

    public function setBody(string $body): void
    {
        $this->attributes['body'] = $body;
    }
}