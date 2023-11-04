<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;
use function App\Helpers\getRegionById;
use function App\Helpers\getUsersBySchoolId;

class Mail extends Entity
{
    protected $attributes = [
        'id' => null,
        'recipient' => null,
        'subject' => null,
        'body' => null
    ];

    protected $casts = [
        'id' => 'integer',
        'recipient' => 'string',
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
     * @return string
     */
    public function getRecipient(): string
    {
        return $this->attributes['recipient'];
    }

    public function setRecipient(string $recipient): void
    {
        $this->attributes['recipient'] = $recipient;
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