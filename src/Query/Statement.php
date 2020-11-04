<?php

namespace DanutAvadanei\Scim2\Query;

use DanutAvadanei\Scim2\ConnectionInterface;
use GuzzleHttp\Promise\Promise;

class Statement
{
    /**
     * @var \DanutAvadanei\Scim2\ConnectionInterface
     */
    protected ConnectionInterface $connection;

    /**
     * @var array
     */
    protected array $query;

    /**
     * @var string
     */
    protected string $url;

    /**
     * @var string
     */
    protected string $method;

    /**
     * @var \GuzzleHttp\Promise\Promise
     */
    private Promise $promise;

    /**
     * Create a new statement instance.
     *
     * @param \DanutAvadanei\Scim2\ConnectionInterface $connection
     * @param array $query
     */
    public function __construct(ConnectionInterface $connection, array $query)
    {
        $this->query = $query;
        $this->connection = $connection;
        $this->url = $connection->getDriverSearchUrl();
        $this->method = $connection->getDriverHttpMethod();
    }

    /**
     * @return mixed
     */
    public function fetchAll()
    {
        return json_decode($this->promise->wait()->getBody(), true);
    }

    /**
     * Execute a http query to the directory.
     */
    public function execute(): void
    {
        $this->promise = $this->{$this->method}($this->query);
    }

    /**
     * Execute a get http query to the directory.
     *
     * @param array $query
     * @return \GuzzleHttp\Promise\Promise
     */
    protected function get(array $query): Promise
    {
        return $this->connection->client->getAsync(
            $this->connection->getDriverSearchUrl(),
            compact('query')
        );
    }

    /**
     * Execute a post http query to the directory.
     *
     * @param array $query
     * @return \GuzzleHttp\Promise\Promise
     */
    protected function post(array $query): Promise
    {
        return $this->connection->client->postAsync(
            $this->connection->getDriverSearchUrl(),
            ['json' => $query]
        );
    }
}
