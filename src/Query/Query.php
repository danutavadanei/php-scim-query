<?php

namespace DanutAvadanei\Scim2\Query;

class Query
{
    protected string $filter;
    protected array $attributes;
    protected int $limit;

    public function __construct(string $filter, array $attributes = ['*'], int $limit = -1)
    {
        $this->filter = $filter;
        $this->attributes = $attributes;
        $this->limit = $limit;
    }

    /**
     * @return string
     */
    public function getFilter(): string
    {
        return $this->filter;
    }

    /**
     * @return array
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    /**
     * @return int
     */
    public function getLimit(): int
    {
        return $this->limit;
    }
}
