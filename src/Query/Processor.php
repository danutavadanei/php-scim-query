<?php

namespace DanutAvadanei\Scim2\Query;

class Processor
{
    /**
     * Process the results of a "select" query.
     *
     * @param \DanutAvadanei\Scim2\Query\Builder $query
     * @param array $results
     * @return array
     */
    public function processSelect(Builder $query, array $results): array
    {
        return $results;
    }
}
