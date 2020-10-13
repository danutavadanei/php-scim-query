<?php

namespace DanutAvadanei\Scim2\Tests\Query;

use DanutAvadanei\Scim2\Query\Builder;
use DanutAvadanei\Scim2\Query\Grammar;
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

    public function testOrWhereEquals()
    {
        $builder = $this->getBuilder();
        $builder->whereEquals('name', 'John')->orWhereEquals('name', 'Jane');
        $this->assertSame('name eq "John" or name eq "Jane"', $builder->toScim());
    }

    public function testWhereNotEquals()
    {
        $builder = $this->getBuilder();
        $builder->whereNotEquals('name', 'John');
        $this->assertSame('name ne "John"', $builder->toScim());

        $builder = $this->getBuilder();
        $builder->whereNotEquals('name', 'John')->whereNotEquals('name', 'Jane');
        $this->assertSame('name ne "John" and name ne "Jane"', $builder->toScim());
    }

    public function testOrWhereNotEquals()
    {
        $builder = $this->getBuilder();
        $builder->whereNotEquals('name', 'John')->orWhereNotEquals('name', 'Jane');
        $this->assertSame('name ne "John" or name ne "Jane"', $builder->toScim());
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

    public function testOrWhereContains()
    {
        $builder = $this->getBuilder();
        $builder->whereContains('name', 'John')->orWhereContains('name', 'Jane');
        $this->assertSame('name co "John" or name co "Jane"', $builder->toScim());
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

    public function testOrWhereStartsWith()
    {
        $builder = $this->getBuilder();
        $builder->whereStartsWith('name', 'John')->orWhereStartsWith('name', 'Jane');
        $this->assertSame('name sw "John" or name sw "Jane"', $builder->toScim());
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

    public function testOrWhereEndsWith()
    {
        $builder = $this->getBuilder();
        $builder->whereEndsWith('name', 'John')->orWhereEndsWith('name', 'Jane');
        $this->assertSame('name ew "John" or name ew "Jane"', $builder->toScim());
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

    public function testOrWherePresent()
    {
        $builder = $this->getBuilder();
        $builder->wherePresent('type')->orWherePresent('activity');
        $this->assertSame('type pr or activity pr', $builder->toScim());

        $builder = $this->getBuilder();
        $builder->orWherePresent(['type', 'activity']);
        $this->assertSame('type pr or activity pr', $builder->toScim());
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

    public function testOrWhereNotPresent()
    {
        $builder = $this->getBuilder();
        $builder->whereNotPresent('type')->orWhereNotPresent('activity');
        $this->assertSame('not (type pr) or not (activity pr)', $builder->toScim());

        $builder = $this->getBuilder();
        $builder->orWhereNotPresent(['type', 'activity']);
        $this->assertSame('not (type pr) or not (activity pr)', $builder->toScim());
    }

    public function testWhereGreaterThan()
    {
        $builder = $this->getBuilder();
        $builder->whereGreaterThan('name', 'John');
        $this->assertSame('name gt "John"', $builder->toScim());

        $builder = $this->getBuilder();
        $builder->whereGreaterThan('name', 'John')->whereGreaterThan('name', 'Jane');
        $this->assertSame('name gt "John" and name gt "Jane"', $builder->toScim());
    }

    public function testOrWhereGreaterThan()
    {
        $builder = $this->getBuilder();
        $builder->whereGreaterThan('name', 'John')->orWhereGreaterThan('name', 'Jane');
        $this->assertSame('name gt "John" or name gt "Jane"', $builder->toScim());
    }

    public function testWhereNotGreaterThan()
    {
        $builder = $this->getBuilder();
        $builder->whereNotGreaterThan('name', 'John');
        $this->assertSame('not (name gt "John")', $builder->toScim());

        $builder = $this->getBuilder();
        $builder->whereNotGreaterThan('name', 'John')->whereNotGreaterThan('name', 'Jane');
        $this->assertSame('not (name gt "John") and not (name gt "Jane")', $builder->toScim());
    }

    public function testOrWhereNotGreaterThan()
    {
        $builder = $this->getBuilder();
        $builder->whereNotGreaterThan('name', 'John')->orWhereNotGreaterThan('name', 'Jane');
        $this->assertSame('not (name gt "John") or not (name gt "Jane")', $builder->toScim());
    }

    public function testWhereGreaterThanOrEqualTo()
    {
        $builder = $this->getBuilder();
        $builder->whereGreaterThanOrEqualTo('name', 'John');
        $this->assertSame('name ge "John"', $builder->toScim());

        $builder = $this->getBuilder();
        $builder->whereGreaterThanOrEqualTo('name', 'John')->whereGreaterThanOrEqualTo('name', 'Jane');
        $this->assertSame('name ge "John" and name ge "Jane"', $builder->toScim());
    }

    public function testOrWhereGreaterThanOrEqualTo()
    {
        $builder = $this->getBuilder();
        $builder->whereGreaterThanOrEqualTo('name', 'John')->orWhereGreaterThanOrEqualTo('name', 'Jane');
        $this->assertSame('name ge "John" or name ge "Jane"', $builder->toScim());
    }

    public function testWhereNotGreaterThanOrEqualTo()
    {
        $builder = $this->getBuilder();
        $builder->whereNotGreaterThanOrEqualTo('name', 'John');
        $this->assertSame('not (name ge "John")', $builder->toScim());

        $builder = $this->getBuilder();
        $builder->whereNotGreaterThanOrEqualTo('name', 'John')->whereNotGreaterThanOrEqualTo('name', 'Jane');
        $this->assertSame('not (name ge "John") and not (name ge "Jane")', $builder->toScim());
    }

    public function testOrWhereNotGreaterThanOrEqualTo()
    {
        $builder = $this->getBuilder();
        $builder->whereNotGreaterThanOrEqualTo('name', 'John')->orWhereNotGreaterThanOrEqualTo('name', 'Jane');
        $this->assertSame('not (name ge "John") or not (name ge "Jane")', $builder->toScim());
    }

    public function testWhereLessThen()
    {
        $builder = $this->getBuilder();
        $builder->whereLessThen('name', 'John');
        $this->assertSame('name lt "John"', $builder->toScim());

        $builder = $this->getBuilder();
        $builder->whereLessThen('name', 'John')->whereLessThen('name', 'Jane');
        $this->assertSame('name lt "John" and name lt "Jane"', $builder->toScim());
    }

    public function testOrWhereLessThen()
    {
        $builder = $this->getBuilder();
        $builder->whereLessThen('name', 'John')->orWhereLessThen('name', 'Jane');
        $this->assertSame('name lt "John" or name lt "Jane"', $builder->toScim());
    }

    public function testWhereNotLessThen()
    {
        $builder = $this->getBuilder();
        $builder->whereNotLessThen('name', 'John');
        $this->assertSame('not (name lt "John")', $builder->toScim());

        $builder = $this->getBuilder();
        $builder->whereNotLessThen('name', 'John')->whereNotLessThen('name', 'Jane');
        $this->assertSame('not (name lt "John") and not (name lt "Jane")', $builder->toScim());
    }

    public function testOrWhereNotLessThen()
    {
        $builder = $this->getBuilder();
        $builder->whereNotLessThen('name', 'John')->orWhereNotLessThen('name', 'Jane');
        $this->assertSame('not (name lt "John") or not (name lt "Jane")', $builder->toScim());
    }

    public function testWhereLessThenOrEqualTo()
    {
        $builder = $this->getBuilder();
        $builder->whereLessThenOrEqualTo('name', 'John');
        $this->assertSame('name le "John"', $builder->toScim());

        $builder = $this->getBuilder();
        $builder->whereLessThenOrEqualTo('name', 'John')->whereLessThenOrEqualTo('name', 'Jane');
        $this->assertSame('name le "John" and name le "Jane"', $builder->toScim());
    }

    public function testOrWhereLessThenOrEqualTo()
    {
        $builder = $this->getBuilder();
        $builder->whereLessThenOrEqualTo('name', 'John')->orWhereLessThenOrEqualTo('name', 'Jane');
        $this->assertSame('name le "John" or name le "Jane"', $builder->toScim());
    }

    public function testWhereNotLessThenOrEqualTo()
    {
        $builder = $this->getBuilder();
        $builder->whereNotLessThenOrEqualTo('name', 'John');
        $this->assertSame('not (name le "John")', $builder->toScim());

        $builder = $this->getBuilder();
        $builder->whereNotLessThenOrEqualTo('name', 'John')->whereNotLessThenOrEqualTo('name', 'Jane');
        $this->assertSame('not (name le "John") and not (name le "Jane")', $builder->toScim());
    }

    public function testOrWhereNotLessThenOrEqualTo()
    {
        $builder = $this->getBuilder();
        $builder->whereNotLessThenOrEqualTo('name', 'John')->orWhereNotLessThenOrEqualTo('name', 'Jane');
        $this->assertSame('not (name le "John") or not (name le "Jane")', $builder->toScim());
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

    public function testOrWhereIn()
    {
        $builder = $this->getBuilder();
        $builder->whereEquals('active', 'true')->orWhereIn('name', ['Joe', 'Jane']);
        $this->assertSame('active eq "true" or (name eq "Joe" or name eq "Jane")', $builder->toScim());

        $builder = $this->getBuilder();
        $builder->whereEquals('name', 'John')->orWhereIn('name', ['Joe', 'Jane']);
        $this->assertSame('name eq "John" or (name eq "Joe" or name eq "Jane")', $builder->toScim());
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

    public function testOrWhereNotIn()
    {
        $builder = $this->getBuilder();
        $builder->whereEquals('active', true)->orWhereNotIn('name', ['Joe', 'Jane']);
        $this->assertSame('active eq true or not (name eq "Joe" and name eq "Jane")', $builder->toScim());

        $builder = $this->getBuilder();
        $builder->whereEquals('name', 'John')->orWhereNotIn('name', ['Joe', 'Jane']);
        $this->assertSame('name eq "John" or not (name eq "Joe" and name eq "Jane")', $builder->toScim());
    }

    public function testWhereNested()
    {
        $builder = $this->getBuilder();
        $builder->whereNested(function (Builder $query) {
            $query->whereEquals('name', 'Joe');
        });
        $this->assertSame('name eq "Joe"', $builder->toScim());

        $builder = $this->getBuilder();
        $builder
            ->whereEquals('active', true)
            ->whereNested(function (Builder $query) {
                $query->whereEquals('name', 'Joe');
            });
        $this->assertSame('active eq true and (name eq "Joe")', $builder->toScim());

        $builder = $this->getBuilder();
        $builder->whereNested(function (Builder $query) {
            $query->whereEquals('name', 'Joe')
                ->whereEquals('name', 'Jane');
        });
        $this->assertSame('name eq "Joe" and name eq "Jane"', $builder->toScim());

        $builder = $this->getBuilder();
        $builder
            ->whereEquals('active', true)
            ->whereNested(function (Builder $query) {
                $query->whereEquals('name', 'Joe')
                    ->whereEquals('name', 'Jane');
            });
        $this->assertSame('active eq true and (name eq "Joe" and name eq "Jane")', $builder->toScim());
    }

    public function testOrWhereNested()
    {
        $builder = $this->getBuilder();
        $builder
            ->whereEquals('active', true)
            ->orWhereNested(function (Builder $query) {
                $query->whereEquals('name', 'Joe');
            });
        $this->assertSame('active eq true or (name eq "Joe")', $builder->toScim());

        $builder = $this->getBuilder();
        $builder
            ->whereEquals('active', true)
            ->orWhereNested(function (Builder $query) {
                $query->whereEquals('name', 'Joe')
                    ->whereEquals('name', 'Jane');
            });
        $this->assertSame('active eq true or (name eq "Joe" and name eq "Jane")', $builder->toScim());
    }

    public function testWhereNotNested()
    {
        $builder = $this->getBuilder();
        $builder->whereNotNested(function (Builder $query) {
            $query->whereEquals('name', 'Joe');
        });
        $this->assertSame('not (name eq "Joe")', $builder->toScim());

        $builder = $this->getBuilder();
        $builder
            ->whereEquals('active', true)
            ->whereNotNested(function (Builder $query) {
                $query->whereEquals('name', 'Joe');
            });
        $this->assertSame('active eq true and not (name eq "Joe")', $builder->toScim());

        $builder = $this->getBuilder();
        $builder->whereNotNested(function (Builder $query) {
            $query->whereEquals('name', 'Joe')
                ->whereEquals('name', 'Jane');
        });
        $this->assertSame('not (name eq "Joe" and name eq "Jane")', $builder->toScim());

        $builder = $this->getBuilder();
        $builder
            ->whereEquals('active', true)
            ->whereNotNested(function (Builder $query) {
                $query->whereEquals('name', 'Joe')
                    ->whereEquals('name', 'Jane');
            });
        $this->assertSame('active eq true and not (name eq "Joe" and name eq "Jane")', $builder->toScim());
    }

    public function testOrWhereNotNested()
    {
        $builder = $this->getBuilder();
        $builder
            ->whereEquals('active', true)
            ->orWhereNotNested(function (Builder $query) {
                $query->whereEquals('name', 'Joe');
            });
        $this->assertSame('active eq true or not (name eq "Joe")', $builder->toScim());

        $builder = $this->getBuilder();
        $builder
            ->whereEquals('active', true)
            ->orWhereNotNested(function (Builder $query) {
                $query->whereEquals('name', 'Joe')
                    ->whereEquals('name', 'Jane');
            });
        $this->assertSame('active eq true or not (name eq "Joe" and name eq "Jane")', $builder->toScim());
    }

    public function testWhereRaw()
    {
        $builder = $this->getBuilder();
        $builder->whereRaw('name eq "Joe"');
        $this->assertSame('name eq "Joe"', $builder->toScim());
    }

    public function testOrWhereRaw()
    {
        $builder = $this->getBuilder();
        $builder->wherePresent('title')->orWhereRaw('name eq "Joe"');
        $this->assertSame('title pr or name eq "Joe"', $builder->toScim());
    }

    public function testWhereWithArrayConditions()
    {
        $builder = $this->getBuilder();
        $builder->where([['active', true], ['name', 'Joe']]);
        $this->assertSame('active eq true and name eq "Joe"', $builder->toScim());

        $builder = $this->getBuilder();
        $builder->where(['active' => true, 'name' => 'Joe']);
        $this->assertSame('active eq true and name eq "Joe"', $builder->toScim());

        $builder = $this->getBuilder();
        $builder->where([['active', true], ['name', 'Joe'], ['age', 'lt', 25]]);
        $this->assertSame('active eq true and name eq "Joe" and age lt 25', $builder->toScim());
    }

    /**
     * @return \DanutAvadanei\Scim2\Query\Builder
     */
    protected function getBuilder(): Builder
    {
        return new Builder(new Grammar());
    }
}
