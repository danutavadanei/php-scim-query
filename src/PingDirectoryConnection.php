<?php

namespace DanutAvadanei\Scim2;

use Generator;
use DanutAvadanei\Scim2\Query\Grammars\PingDirectoryGrammar as QueryGrammar;
use DanutAvadanei\Scim2\Query\Processors\PingDirectoryProcessor;
use DanutAvadanei\Scim2\Query\Statements\PingDirectoryStatement;
use Illuminate\Support\Arr;

class PingDirectoryConnection extends Connection
{
    /**
     * @inheritDoc
     */
    protected function getDefaultQueryGrammar(): QueryGrammar
    {
        return new QueryGrammar();
    }

    /**
     * @inheritDoc
     */
    protected function getDefaultPostProcessor(): PingDirectoryProcessor
    {
        return new PingDirectoryProcessor();
    }

    /**
     * @inheritDoc
     */
    public function cursor(array $query): Generator
    {
        $count = 0;
        $limit = max(100, $query['limit']);
        $query['limit'] = 100;

        do {
            $result = $this->run($query, function ($query) {
                if ($this->pretending()) {
                    return [];
                }

                $statement = $this->prepare($query);

                $statement->execute();

                return $statement->fetchAll();
            });

            $query['cursor'] = Arr::get($result, '_links.next.data.cursor');
            $count += Arr::get($result, 'size');

            $entries = $this->postProcessor->processSelect($result);

            foreach ($entries as $entry) {
                yield $entry;
            }
        } while (! is_null($query['cursor']) && $count <= $limit);
    }

    /**
     * Prepare a statement with given query.
     *
     * @param array $query
     * @return \DanutAvadanei\Scim2\Query\Statements\PingDirectoryStatement
     */
    protected function prepare(array $query): PingDirectoryStatement
    {
        return new PingDirectoryStatement($this, $query);
    }
}
