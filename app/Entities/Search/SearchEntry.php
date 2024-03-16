<?php

namespace App\Entities\Search;

class SearchEntry
{
    private string $name;
    private string $type;
    private array $urls;

    public function __construct(string $name, string $type, array $urls)
    {
        $this->name = $name;
        $this->type = $type;
        $this->urls = $urls;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getBadge(): string
    {
        return "<span class=\"badge bg-success\">{$this->type}</span>";
    }

    /**
     * @return array
     */
    public function getUrls(): array
    {
        return $this->urls;
    }
}