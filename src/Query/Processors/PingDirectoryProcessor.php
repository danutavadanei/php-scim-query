<?php

namespace DanutAvadanei\Scim2\Query\Processors;

use DanutAvadanei\Scim2\Query\Processor;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

class PingDirectoryProcessor extends Processor
{
    /**
     * Process the results of a "select" query.
     *
     * @param array $results
     * @return \Illuminate\Support\Collection
     */
    public function processSelect(array $results): Collection
    {
        return new Collection(Arr::wrap(Arr::get($results, '_embedded.entries')));
    }
}
