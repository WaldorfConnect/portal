<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;

class Image extends Entity
{
    protected $attributes = [
        'id' => null,
        'author' => null,
    ];

    protected $casts = [
        'id' => 'string',
        'author' => 'string',
    ];

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->attributes['id'];
    }

    public function setId(string $id): void
    {
        $this->attributes['id'] = $id;
    }

    /**
     * @return string
     */
    public function getAuthor(): string
    {
        return $this->attributes['author'];
    }

    public function setAuthor(string $author): void
    {
        $this->attributes['author'] = $author;
    }
}