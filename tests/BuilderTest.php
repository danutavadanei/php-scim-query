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
        $builder->whereEquals('name', 'John');
        $this->assertSame('name eq "John"', $builder->toScim());

        $builder = $this->getBuilder();
        $builder->whereEquals('active', true)->whereEquals('external', false);
        $this->assertSame('active eq true and external eq false', $builder->toScim());

        $builder = $this->getBuilder();
        $builder->whereEquals('age', 25);
        $this->assertSame('age eq 25', $builder->toScim());

        $builder = $this->getBuilder();
        $builder->whereEquals('name', 'John')->whereEquals('name', 'Jane');
        $this->assertSame('name eq "John" and name eq "Jane"', $builder->toScim());
    }

    public function testWhereNotEquals()
    {
        $builder = $this->getBuilder();
        $builder->whereNotEquals('name', 'John');
        $this->assertSame('not (name eq "John")', $builder->toScim());

        $builder = $this->getBuilder();
        $builder->whereNotEquals('name', 'John')->whereNotEquals('name', 'Jane');
        $this->assertSame('not (name eq "John") and not (name eq "Jane")', $builder->toScim());
    }

    public function testOrWhereEquals()
    {
        $builder = $this->getBuilder();
        $builder->whereEquals('name', 'John')->orWhereEquals('name', 'Jane');
        $this->assertSame('name eq "John" or name eq "Jane"', $builder->toScim());
    }

    public function testOrWhereNotEquals()
    {
        $builder = $this->getBuilder();
        $builder->whereNotEquals('name', 'John')->orWhereNotEquals('name', 'Jane');
        $this->assertSame('not (name eq "John") or not (name eq "Jane")', $builder->toScim());
    }

    public function testWhereContains()
    {
        $builder = $this->getBuilder();
        $builder->whereContains('name', 'John');
        $this->assertSame('name co "John"', $builder->toScim());

        $builder = $this->getBuilder();
        $builder->whereContains('name', 'John')->whereContains('name', 'Jane');
        $this->assertSame('name co "John" and name co "Jane"', $builder->toScim());
    }

    public function testWhereNotContains()
    {
        $builder = $this->getBuilder();
        $builder->whereNotContains('name', 'John');
        $this->assertSame('not (name co "John")', $builder->toScim());

        $builder = $this->getBuilder();
        $builder->whereNotContains('name', 'John')->whereNotContains('name', 'Jane');
        $this->assertSame('not (name co "John") and not (name co "Jane")', $builder->toScim());
    }

    public function testOrWhereContains()
    {
        $builder = $this->getBuilder();
        $builder->whereContains('name', 'John')->orWhereContains('name', 'Jane');
        $this->assertSame('name co "John" or name co "Jane"', $builder->toScim());
    }

    public function testOrWhereNotContains()
    {
        $builder = $this->getBuilder();
        $builder->whereNotContains('name', 'John')->orWhereNotContains('name', 'Jane');
        $this->assertSame('not (name co "John") or not (name co "Jane")', $builder->toScim());
    }

    public function testWhereStartsWith()
    {
        $builder = $this->getBuilder();
        $builder->whereStartsWith('name', 'John');
        $this->assertSame('name sw "John"', $builder->toScim());

        $builder = $this->getBuilder();
        $builder->whereStartsWith('name', 'John')->whereStartsWith('name', 'Jane');
        $this->assertSame('name sw "John" and name sw "Jane"', $builder->toScim());
    }

    public function testWhereNotStartsWith()
    {
        $builder = $this->getBuilder();
        $builder->whereNotStartsWith('name', 'John');
        $this->assertSame('not (name sw "John")', $builder->toScim());

        $builder = $this->getBuilder();
        $builder->whereNotStartsWith('name', 'John')->whereNotStartsWith('name', 'Jane');
        $this->assertSame('not (name sw "John") and not (name sw "Jane")', $builder->toScim());
    }

    public function testOrWhereStartsWith()
    {
        $builder = $this->getBuilder();
        $builder->whereStartsWith('name', 'John')->orWhereStartsWith('name', 'Jane');
        $this->assertSame('name sw "John" or name sw "Jane"', $builder->toScim());
    }

    public function testOrWhereNotStartsWith()
    {
        $builder = $this->getBuilder();
        $builder->whereNotStartsWith('name', 'John')->orWhereNotStartsWith('name', 'Jane');
        $this->assertSame('not (name sw "John") or not (name sw "Jane")', $builder->toScim());
    }

    public function testWhereEndsWith()
    {
        $builder = $this->getBuilder();
        $builder->whereEndsWith('name', 'John');
        $this->assertSame('name ew "John"', $builder->toScim());

        $builder = $this->getBuilder();
        $builder->whereEndsWith('name', 'John')->whereEndsWith('name', 'Jane');
        $this->assertSame('name ew "John" and name ew "Jane"', $builder->toScim());
    }

    public function testWhereNotEndsWith()
    {
        $builder = $this->getBuilder();
        $builder->whereNotEndsWith('name', 'John');
        $this->assertSame('not (name ew "John")', $builder->toScim());

        $builder = $this->getBuilder();
        $builder->whereNotEndsWith('name', 'John')->whereNotEndsWith('name', 'Jane');
        $this->assertSame('not (name ew "John") and not (name ew "Jane")', $builder->toScim());
    }

    public function testOrWhereEndsWith()
    {
        $builder = $this->getBuilder();
        $builder->whereEndsWith('name', 'John')->orWhereEndsWith('name', 'Jane');
        $this->assertSame('name ew "John" or name ew "Jane"', $builder->toScim());
    }

    public function testOrWhereNotEndsWith()
    {
        $builder = $this->getBuilder();
        $builder->whereNotEndsWith('name', 'John')->orWhereNotEndsWith('name', 'Jane');
        $this->assertSame('not (name ew "John") or not (name ew "Jane")', $builder->toScim());
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

    public function testWhereIn()
    {
        $builder = $this->getBuilder();
        $builder->whereIn('name', ['Joe', 'Jane']);
        $this->assertSame('name eq "Joe" or name eq "Jane"', $builder->toScim());

        $builder = $this->getBuilder();
        $builder->whereEquals('name', 'John')->whereIn('name', ['Joe', 'Jane']);
        $this->assertSame('name eq "John" and (name eq "Joe" or name eq "Jane")', $builder->toScim());
    }

    public function testWhereNotIn()
    {
        $builder = $this->getBuilder();
        $builder->whereNotIn('name', ['Joe', 'Jane']);
        $this->assertSame('not (name eq "Joe" and name eq "Jane")', $builder->toScim());

        $builder = $this->getBuilder();
        $builder->whereEquals('name', 'John')->whereNotIn('name', ['Joe', 'Jane']);
        $this->assertSame('name eq "John" and not (name eq "Joe" and name eq "Jane")', $builder->toScim());
    }

    public function testOrWhereIn()
    {
        $builder = $this->getBuilder();
        $builder->whereEquals('active', 'true')->orWhereIn('name', ['Joe', 'Jane']);
        $this->assertSame('active eq "true" or (name eq "Joe" or name eq "Jane")', $builder->toScim());

        $builder = $this->getBuilder();
        $builder->whereEquals('name', 'John')->orWhereIn('name', ['Joe', 'Jane']);
        $this->assertSame('name eq "John" or (name eq "Joe" or name eq "Jane")', $builder->toScim());
    }

    public function testOrWhereNotIn()
    {
        $builder = $this->getBuilder();
        $builder->whereEquals('active', true)->orWhereNotIn('name', ['Joe', 'Jane']);
        $this->assertSame('active eq true or not (name eq "Joe" and name eq "Jane")', $builder->toScim());

        $builder = $this->getBuilder();
        $builder->whereEquals('name', 'John')->orWhereNotIn('name', ['Joe', 'Jane']);
        $this->assertSame('name eq "John" or not (name eq "Joe" and name eq "Jane")', $builder->toScim());
    }

    /**
     * @return \DanutAvadanei\ScimQuery\Builder
     */
    protected function getBuilder(): Builder
    {
        return new Builder(new Grammar());
    }
}
