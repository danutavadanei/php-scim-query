<?php

namespace DanutAvadanei\Scim2\Tests\Mocks;

use DanutAvadanei\Scim2\ConnectionInterface;
use DanutAvadanei\Scim2\Query\Grammar;
use DanutAvadanei\Scim2\Query\Processor;
use Generator;

class ConnectionMock implements ConnectionInterface
{
    public function raw($filter, array $attributes = ['*'], int $limit = -1, int $offset = 0)
    {
        //
    }

    public function selectOne(array $query)
    {
        //
    }

    public function select(array $query)
    {
        //
    }

    public function cursor(array $query): Generator
    {
        //
    }

    public function getQueryGrammar(): Grammar
    {
        return new Grammar();
    }

    public function getPostProcessor(): Processor
    {
        return new Processor();
    }
}
