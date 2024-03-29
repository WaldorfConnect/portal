<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;
use DateTime;

class Notification extends Entity
{
    protected $attributes = [
        'id' => null,
        'user_id' => null,
        'subject' => null,
        'body' => null,
        'created_at' => null,
        'read_at' => null,
        'deleted_at' => null
    ];

    protected $casts = [
        'id' => 'integer',
        'user_id' => 'integer',
        'subject' => 'string',
        'body' => 'string',
        'created_at' => 'timestamp',
        'read_at' => 'timestamp',
        'deleted_at' => 'timestamp'
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
    public function getUserId(): int
    {
        return $this->attributes['user_id'];
    }

    public function setUserId(int $userId): void
    {
        $this->attributes['user_id'] = $userId;
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

    public function getCreateDate(): ?DateTime
    {
        $formattedDate = $this->attributes['created_at'];
        if (!$formattedDate) return null;

        return DateTime::createFromFormat('Y-m-d H:i:s', $formattedDate);
    }

    public function setCreateDate(DateTime $time): void
    {
        $this->attributes['created_at'] = $time->format('Y-m-d H:i:s');
    }

    public function getReadDate(): ?DateTime
    {
        $formattedDate = $this->attributes['read_at'];
        if (!$formattedDate) return null;

        return DateTime::createFromFormat('Y-m-d H:i:s', $formattedDate);
    }

    public function setReadDate(DateTime $time): void
    {
        $this->attributes['read_at'] = $time->format('Y-m-d H:i:s');
    }
}