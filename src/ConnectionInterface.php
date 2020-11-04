<?php


namespace DanutAvadanei\Scim2;

use DanutAvadanei\Scim2\Query\Grammar;
use DanutAvadanei\Scim2\Query\Processor;
use Generator;

interface ConnectionInterface
{
    /**
     * Run a select statement against the scim2 provider.
     *
     * @param array $query
     * @return mixed
     */
    public function select(array $query);

    /**
     * Run a select statement and return a single result.
     *
     * @param array $query
     * @return mixed
     */
    public function selectOne(array $query);

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
