<?php

namespace DanutAvadanei\ScimQuery\Tests;

use DanutAvadanei\ScimQuery\Builder;
use DanutAvadanei\ScimQuery\Grammar;
use PHPUnit\Framework\TestCase;

class BuilderTest extends TestCase
{
    public function testWhereEquals()
    {
        $builder = $this->getBuilder();

        $builder->whereEquals('name', 'Dan')
            ->whereEquals('name', 'Joe');

        $this->assertSame('name eq "Dan" and name eq "Joe"', $builder->toScim());
    }

    public function testOrWhereEquals()
    {
        $builder = $this->getBuilder();

        $builder->whereEquals('name', 'Dan')
            ->orWhereEquals('name', 'Joe');

        $this->assertSame('name eq "Dan" or name eq "Joe"', $builder->toScim());
    }

    public function testWhereNotEquals()
    {
        $builder = $this->getBuilder();

        $builder->whereNotEquals('name', 'Dan')
            ->whereNotEquals('name', 'Joe');

        $this->assertSame('not (name eq "Dan") and not (name eq "Joe")', $builder->toScim());
    }

    public function testOrWhereNotEquals()
    {
        $builder = $this->getBuilder();

        $builder->whereNotEquals('name', 'Dan')
            ->orWhereNotEquals('name', 'Joe');

        $this->assertSame('not (name eq "Dan") or not (name eq "Joe")', $builder->toScim());
    }

    /**
     * @return \DanutAvadanei\ScimQuery\Builder
     */
    protected function getBuilder()
    {
        return new Builder(new Grammar());
    }
}
