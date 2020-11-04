<?php

namespace DanutAvadanei\Scim2\Query\Grammars;

use DanutAvadanei\Scim2\Query\Builder;
use DanutAvadanei\Scim2\Query\Grammar;

class PingDirectoryGrammar extends Grammar
{
    /**
     * Compile a query into a scim 2.0 filter object.
     *
     * @param \DanutAvadanei\Scim2\Query\Builder $query
     * @return array
     */
    public function compile(Builder $query): array
    {
        return [
            'filter' => $this->compileWheres($query),
            'includeAttributes' => $this->compileAttributes($query),
            'limit' => $query->limit,
            'searchScope' => 'wholeSubtree',
        ];
    }
}
