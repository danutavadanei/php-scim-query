<?php

namespace DanutAvadanei\Scim2;

use DanutAvadanei\Scim2\Query\Grammars\PingDirectoryGrammar as QueryGrammar;
use DanutAvadanei\Scim2\Query\Processors\PingDirectoryProcessor;

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
}
