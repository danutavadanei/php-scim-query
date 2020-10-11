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
        $builder->whereEquals('name', 'Dan');
        $this->assertSame('name eq "Dan"', $builder->toScim());

        $builder = $this->getBuilder();
        $builder->whereEquals('name', 'Dan')->whereEquals('name', 'Joe');
        $this->assertSame('name eq "Dan" and name eq "Joe"', $builder->toScim());
    }

    public function testWhereNotEquals()
    {
        $builder = $this->getBuilder();
        $builder->whereNotEquals('name', 'Dan');
        $this->assertSame('not (name eq "Dan")', $builder->toScim());

        $builder = $this->getBuilder();
        $builder->whereNotEquals('name', 'Dan')->whereNotEquals('name', 'Joe');
        $this->assertSame('not (name eq "Dan") and not (name eq "Joe")', $builder->toScim());
    }

    public function testOrWhereEquals()
    {
        $builder = $this->getBuilder();
        $builder->whereEquals('name', 'Dan')->orWhereEquals('name', 'Joe');
        $this->assertSame('name eq "Dan" or name eq "Joe"', $builder->toScim());
    }

    public function testOrWhereNotEquals()
    {
        $builder = $this->getBuilder();
        $builder->whereNotEquals('name', 'Dan')->orWhereNotEquals('name', 'Joe');
        $this->assertSame('not (name eq "Dan") or not (name eq "Joe")', $builder->toScim());
    }

    public function testWherePresent()
    {
        $builder = $this->getBuilder();
        $builder->wherePresent('type');
        $this->assertSame('type pr', $builder->toScim());

        $builder = $this->getBuilder();
        $builder->wherePresent(['type', 'activity']);
        $this->assertSame('type pr and activity pr', $builder->toScim());
    }

    public function testWhereNotPresent()
    {
        $builder = $this->getBuilder();
        $builder->whereNotPresent('type');
        $this->assertSame('not (type pr)', $builder->toScim());

        $builder = $this->getBuilder();
        $builder->whereNotPresent(['type', 'activity']);
        $this->assertSame('not (type pr) and not (activity pr)', $builder->toScim());
    }

    public function testOrWherePresent()
    {
        $builder = $this->getBuilder();
        $builder->wherePresent('type')->orWherePresent('activity');
        $this->assertSame('type pr or activity pr', $builder->toScim());

        $builder = $this->getBuilder();
        $builder->orWherePresent(['type', 'activity']);
        $this->assertSame('type pr or activity pr', $builder->toScim());
    }

    public function testOrWhereNotPresent()
    {
        $builder = $this->getBuilder();
        $builder->whereNotPresent('type')->orWhereNotPresent('activity');
        $this->assertSame('not (type pr) or not (activity pr)', $builder->toScim());

        $builder = $this->getBuilder();
        $builder->orWhereNotPresent(['type', 'activity']);
        $this->assertSame('not (type pr) or not (activity pr)', $builder->toScim());
    }

    /**
     * @return \DanutAvadanei\ScimQuery\Builder
     */
    protected function getBuilder(): Builder
    {
        return new Builder(new Grammar());
    }
}
