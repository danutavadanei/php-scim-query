<?php


namespace DanutAvadanei\Scim2;


use DanutAvadanei\Scim2\Query\Query;
use Generator;

interface ConnectionInterface
{
    /**
     * Get a new raw query expression.
     *
     * @param $scim
     * @param array $attributes
     * @param int $limit
     * @return \DanutAvadanei\Scim2\Query\Expression
     */
    public function raw($scim, array $attributes = ['*'], int $limit = -1);

    /**
     * Run a select statement and return a single result.
     *
     * @param \DanutAvadanei\Scim2\Query\Query $query
     * @return mixed
     */
    public function selectOne(Query $query);

    /**
     * Run a select statement against the scim2 provider.
     *
     * @param \DanutAvadanei\Scim2\Query\Query $query
     * @return mixed
     */
    public function select(Query $query);

    /**
     * Run a select statement against the scim2 provider and returns a generator.
     *
     * @param \DanutAvadanei\Scim2\Query\Query $query
     * @return \Generator
     */
    public function cursor(Query $query): Generator;
}
