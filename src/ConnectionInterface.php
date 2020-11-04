<?php


namespace DanutAvadanei\Scim2;

use DanutAvadanei\Scim2\Query\Grammar;
use DanutAvadanei\Scim2\Query\Processor;
use Generator;

interface ConnectionInterface
{
    /**
     * Get a new raw query expression.
     *
     * @param string $filter
     * @param array $attributes
     * @param int $limit
     * @param int $offset
     * @return mixed
     */
    public function raw(string $filter, array $attributes = ['*'], int $limit = 100, int $offset = 0);

    /**
     * Run a select statement against the scim2 provider.
     *
     * @param array $query
     * @return mixed
     */
    public function select(array $query);

    /**
     * Run a select statement against the scim2 provider and returns a generator.
     *
     * @param array $query
     * @return \Generator
     */
    public function cursor(array $query): Generator;

    /**
     * Get the query grammar used by the connection.
     *
     * @return \DanutAvadanei\Scim2\Query\Grammar
     */
    public function getQueryGrammar(): Grammar;

    /**
     * Get the query post processor used by the connection.
     *
     * @return \DanutAvadanei\Scim2\Query\Processor
     */
    public function getPostProcessor(): Processor;
}
