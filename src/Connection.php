<?php

namespace DanutAvadanei\Scim2;

use Closure;
use Generator;
use GuzzleHttp\Client;
use Illuminate\Support\Arr;
use DanutAvadanei\Scim2\Query\Processor;
use GuzzleHttp\Promise\PromiseInterface;
use DanutAvadanei\Scim2\Query\Builder as QueryBuilder;
use DanutAvadanei\Scim2\Query\Grammar as QueryGrammar;

class Connection implements ConnectionInterface
{
    /**
     * The active http client to the directory.
     *
     * @var \GuzzleHttp\Client
     */
    protected Client $client;

    /**
     * The query grammar implementation.
     */
    protected QueryGrammar $queryGrammar;

    /**
     * The query post processor implementation.
     */
    protected Processor $postProcessor;

    /**
     * The directory connection configuration options.
     *
     * @var array
     */
    protected array $config = [];

    /**
     * All of the queries run against the connection.
     *
     * @var array
     */
    protected array $queryLog = [];

    /**
     * Indicates whether queries are being logged.
     *
     * @var bool
     */
    protected bool $loggingQueries = false;

    /**
     * Indicates if the connection is in a "dry run".
     *
     * @var bool
     */
    protected bool $pretending = false;

    /**
     * Create a new directory connection instance.
     *
     * @param \GuzzleHttp\Client $client
     * @param array $config
     */
    public function __construct(Client $client, array $config = [])
    {
        $this->client = $client;

        $this->config = $config;

        // We need to initialize a query grammar and the query post processors
        // which are both very important parts of the directory abstractions
        // so we initialize these to their default values while starting.
        $this->useDefaultQueryGrammar();

        $this->useDefaultPostProcessor();
    }

    /**
     * @inheritDoc
     */
    public function select(array $query)
    {
        return $this->run($query, function ($query) {
            if ($this->pretending()) {
                return [];
            }

            $promise = $this->execute($query);

            /** @var \GuzzleHttp\Psr7\Response $response */
            $response = $promise->wait();

            return json_decode($response->getBody(), true);
        });
    }

    /**
     * @inheritDoc
     */
    public function selectOne(array $query)
    {
        $records = $this->select($query);

        return array_shift($records);
    }

    /**
     * @inheritDoc
     */
    public function cursor(array $query): Generator
    {
        // TODO: Implement cursor() method.
    }

    /**
     * @inheritDoc
     */
    public function getQueryGrammar(): QueryGrammar
    {
        return $this->queryGrammar;
    }

    /**
     * @inheritDoc
     */
    public function getPostProcessor(): Processor
    {
        return $this->postProcessor;
    }

    /**
     * Set the query grammar to the default implementation.
     *
     * @return void
     */
    public function useDefaultQueryGrammar()
    {
        $this->queryGrammar = $this->getDefaultQueryGrammar();
    }

    /**
     * Get the default query grammar instance.
     *
     * @return \DanutAvadanei\Scim2\Query\Grammar
     */
    protected function getDefaultQueryGrammar(): QueryGrammar
    {
        return new QueryGrammar();
    }

    /**
     * Set the query post processor to the default implementation.
     *
     * @return void
     */
    public function useDefaultPostProcessor()
    {
        $this->postProcessor = $this->getDefaultPostProcessor();
    }

    /**
     * Get the default post processor instance.
     *
     * @return \DanutAvadanei\Scim2\Query\Processor
     */
    protected function getDefaultPostProcessor(): Processor
    {
        return new Processor();
    }

    /**
     * Get a new query builder instance.
     *
     * @return \DanutAvadanei\Scim2\Query\Builder
     */
    public function query()
    {
        return new QueryBuilder(
            $this,
            $this->getQueryGrammar(),
            $this->getPostProcessor()
        );
    }

    /**
     * Run a scim query and log its execution context.
     *
     * @param array $query
     * @param \Closure $callback
     * @return mixed
     *
     * @throws \GuzzleHttp\Exception\ClientException|\GuzzleHttp\Exception\ServerException
     */
    protected function run(array $query, Closure $callback)
    {
        $start = microtime(true);

        $result = $callback($query);

        $this->logQuery(
            $query,
            $this->getElapsedTime($start)
        );

        return $result;
    }

    /**
     * Execute the given callback in "dry run" mode.
     *
     * @param  \Closure  $callback
     * @return array
     */
    public function pretend(Closure $callback): array
    {
        return $this->withFreshQueryLog(function () use ($callback) {
            $this->pretending = true;

            // Basically to make the database connection "pretend", we will just return
            // the default values for all the query methods, then we will return an
            // array of queries that were "executed" within the Closure callback.
            $callback($this);

            $this->pretending = false;

            return $this->queryLog;
        });
    }

    /**
     * Get the elapsed time since a given starting point.
     *
     * @param  int  $start
     * @return float
     */
    protected function getElapsedTime(int $start): float
    {
        return round((microtime(true) - $start) * 1000, 2);
    }

    /**
     * Log a query in the connection's query log.
     *
     * @param  array $query
     * @param  float|null  $time
     * @return void
     */
    public function logQuery(array $query, float $time = null): void
    {
        if ($this->loggingQueries) {
            $this->queryLog[] = compact('query', 'time');
        }
    }

    /**
     * Execute the given callback in "dry run" mode.
     *
     * @param  \Closure  $callback
     * @return array
     */
    protected function withFreshQueryLog(Closure $callback): array
    {
        $loggingQueries = $this->loggingQueries;

        // First we will back up the value of the logging queries property and then
        // we'll be ready to run callbacks. This query log will also get cleared
        // so we will have a new log of all the queries that are executed now.
        $this->enableQueryLog();

        $this->queryLog = [];

        // Now we'll execute this callback and capture the result. Once it has been
        // executed we will restore the value of query logging and give back the
        // value of the callback so the original callers can have the results.
        $result = $callback();

        $this->loggingQueries = $loggingQueries;

        return $result;
    }

    /**
     * Enable the query log on the connection.
     *
     * @return void
     */
    public function enableQueryLog(): void
    {
        $this->loggingQueries = true;
    }

    /**
     * Disable the query log on the connection.
     *
     * @return void
     */
    public function disableQueryLog(): void
    {
        $this->loggingQueries = false;
    }

    /**
     * Get the connection query log.
     *
     * @return array
     */
    public function getQueryLog(): array
    {
        return $this->queryLog;
    }

    /**
     * Clear the query log.
     *
     * @return void
     */
    public function flushQueryLog(): void
    {
        $this->queryLog = [];
    }

    /**
     * Determine if the connection is in a "dry run".
     *
     * @return bool
     */
    public function pretending(): bool
    {
        return $this->pretending === true;
    }

    /**
     * Get an option from the configuration options.
     *
     * @param  string|null  $option
     * @return mixed
     */
    public function getConfig($option = null)
    {
        return Arr::get($this->config, $option);
    }

    /**
     * Get the directory connection name.
     *
     * @return string|null
     */
    public function getName()
    {
        return $this->getConfig('name');
    }

    /**
     * Get the directory driver search url.
     *
     * @return string
     */
    public function getDriverSearchUrl()
    {
        return $this->getConfig('driver.url');
    }

    /**
     * Get the directory driver http method.
     *
     * @return string
     */
    public function getDriverHttpMethod()
    {
        return $this->getConfig('driver.method');
    }

    /**
     * Execute a http query to the directory.
     *
     * @param array $query
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    protected function execute(array $query): PromiseInterface
    {
        return $this->{'execute' . ucfirst($this->getDriverHttpMethod())}($query);
    }

    /**
     * Execute a get http query to the directory.
     *
     * @param array $query
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    protected function executeGet(array $query): PromiseInterface
    {
        return $this->client->getAsync(
            $this->getDriverSearchUrl(),
            compact('query')
        );
    }

    /**
     * Execute a post http query to the directory.
     *
     * @param array $query
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    protected function executePost(array $query): PromiseInterface
    {
        return $this->client->postAsync(
            $this->getDriverSearchUrl(),
            ['json' => $query]
        );
    }
}
