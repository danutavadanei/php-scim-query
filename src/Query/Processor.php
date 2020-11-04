<?php

namespace DanutAvadanei\Scim2\Query;

use Illuminate\Support\Collection;

class Processor
{
    /**
     * Process the results of a "select" query.
     *
     * @param array $results
     * @return \Illuminate\Support\Collection
     */
    public function processSelect(array $results): Collection
    {
        return new Collection($results);
    }
}
