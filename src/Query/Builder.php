<?php

namespace DanutAvadanei\Scim2\Query;

use Closure;
use Illuminate\Support\Arr;
use Illuminate\Support\Traits\Macroable;
use InvalidArgumentException;

class Builder
{
    use Macroable;

    /**
     * @var array
     */
    public array $wheres = [];

    /**
     * @var string[]
     */
    public array $operators = [
        'eq', 'ne', 'co', 'sw', 'ew', 'pr' ,'gt', 'ge', 'lt', 'le',
    ];

    public Grammar $grammar;

    public function __construct(Grammar $grammar)
    {
        $this->grammar = $grammar;
    }

    /**
     * Add a basic where clause to the query.
     *
     * @param \Closure|string|array $attribute
     * @param mixed $operator
     * @param mixed $value
     * @param string $logical
     * @param bool $not
     * @return $this
     */
    public function where($attribute, $operator = null, $value = null, string $logical = 'and', bool $not = false)
    {
        if (is_array($attribute)) {
            return $this->addArrayOfWheres($attribute, $logical, $not);
        }

        [$value, $operator] = $this->prepareValueAndOperator(
            $value,
            $operator,
            func_num_args() === 2
        );

        if ($attribute instanceof Closure && is_null($operator)) {
            return $this->whereNested($attribute, $logical, $not);
        }

        if ($this->invalidOperator($operator)) {
            [$value, $operator] = [$operator, 'eq'];
        }

        if (is_null($value)) {
            return $this->whereNotPresent($attribute, $logical);
        }

        $type = 'Basic';

        $this->wheres[] = compact(
            'type',
            'attribute',
            'operator',
            'value',
            'logical',
            'not',
        );

        return $this;
    }

    /**
     * Add an "or where" clause to the query.
     *
     * @param \Closure|string|array $attribute
     * @param mixed $operator
     * @param mixed $value
     * @param bool $not
     * @return $this
     */
    public function orWhere($attribute, $operator = null, $value = null, bool $not = false)
    {
        [$value, $operator] = $this->prepareValueAndOperator(
            $value,
            $operator,
            func_num_args() === 2
        );

        return $this->where($attribute, $operator, $value, 'or', $not);
    }

    /**
     * Add an "where not" clause to the query.
     *
     * @param \Closure|string|array $attribute
     * @param mixed $operator
     * @param mixed $value
     * @param string $logical
     * @return $this
     */
    public function whereNot($attribute, $operator = null, $value = null, string $logical = 'and')
    {
        [$value, $operator] = $this->prepareValueAndOperator(
            $value,
            $operator,
            func_num_args() === 2
        );

        return $this->where($attribute, $operator, $value, $logical, true);
    }

    /**
     * Add an "or where not" clause to the query.
     *
     * @param \Closure|string|array $attribute
     * @param mixed $operator
     * @param mixed $value
     * @return $this
     */
    public function orWhereNot($attribute, $operator = null, $value = null)
    {
        [$value, $operator] = $this->prepareValueAndOperator(
            $value,
            $operator,
            func_num_args() === 2
        );

        return $this->where($attribute, $operator, $value, 'or', true);
    }

    /**
     * Add a "where equals" clause to the query.
     *
     * @param mixed $attribute
     * @param mixed $value
     * @param string $logical
     * @return $this
     */
    public function whereEquals($attribute, $value, string $logical = 'and')
    {
        return $this->where($attribute, 'eq', $value, $logical);
    }

    /**
     * Add a "or where equals" clause to the query.
     *
     * @param mixed $attribute
     * @param mixed $value
     * @return $this
     */
    public function orWhereEquals($attribute, $value)
    {
        return $this->whereEquals($attribute, $value, 'or');
    }

    /**
     * Add a "where not equals" clause to the query.
     *
     * @param mixed $attribute
     * @param mixed $value
     * @param string $logical
     * @return $this
     */
    public function whereNotEquals($attribute, $value, string $logical = 'and')
    {
        return $this->where($attribute, 'ne', $value, $logical);
    }

    /**
     * Add a "or where not equals" clause to the query.
     *
     * @param mixed $attribute
     * @param mixed $value
     * @return $this
     */
    public function orWhereNotEquals($attribute, $value)
    {
        return $this->whereNotEquals($attribute, $value, 'or');
    }

    /**
     * Add a "where contains" clause to the query.
     *
     * @param mixed $attribute
     * @param mixed $value
     * @param string $logical
     * @param bool $not
     * @return $this
     */
    public function whereContains($attribute, $value, string $logical = 'and', bool $not = false)
    {
        return $this->where($attribute, 'co', $value, $logical, $not);
    }

    /**
     * Add a "or where contains" clause to the query.
     *
     * @param mixed $attribute
     * @param mixed $value
     * @param bool $not
     * @return $this
     */
    public function orWhereContains($attribute, $value, bool $not = false)
    {
        return $this->whereContains($attribute, $value, 'or', $not);
    }

    /**
     * Add a "where not contains" clause to the query.
     *
     * @param mixed $attribute
     * @param mixed $value
     * @param string $logical
     * @return $this
     */
    public function whereNotContains($attribute, $value, string $logical = 'and')
    {
        return $this->where($attribute, 'co', $value, $logical, true);
    }

    /**
     * Add a "or where not contains" clause to the query.
     *
     * @param mixed $attribute
     * @param mixed $value
     * @return $this
     */
    public function orWhereNotContains($attribute, $value)
    {
        return $this->whereNotContains($attribute, $value, 'or');
    }

    /**
     * Add a "where starts with" clause to the query.
     *
     * @param mixed $attribute
     * @param mixed $value
     * @param string $logical
     * @param bool $not
     * @return $this
     */
    public function whereStartsWith($attribute, $value, string $logical = 'and', bool $not = false)
    {
        return $this->where($attribute, 'sw', $value, $logical, $not);
    }

    /**
     * Add a "or where starts with" clause to the query.
     *
     * @param mixed $attribute
     * @param mixed $value
     * @param bool $not
     * @return $this
     */
    public function orWhereStartsWith($attribute, $value, bool $not = false)
    {
        return $this->whereStartsWith($attribute, $value, 'or', $not);
    }

    /**
     * Add a "where not starts with" clause to the query.
     *
     * @param mixed $attribute
     * @param mixed $value
     * @param string $logical
     * @return $this
     */
    public function whereNotStartsWith($attribute, $value, string $logical = 'and')
    {
        return $this->where($attribute, 'sw', $value, $logical, true);
    }

    /**
     * Add a "or where not starts with" clause to the query.
     *
     * @param mixed $attribute
     * @param mixed $value
     * @return $this
     */
    public function orWhereNotStartsWith($attribute, $value)
    {
        return $this->whereNotStartsWith($attribute, $value, 'or');
    }

    /**
     * Add a "where ends with" clause to the query.
     *
     * @param mixed $attribute
     * @param mixed $value
     * @param string $logical
     * @param bool $not
     * @return $this
     */
    public function whereEndsWith($attribute, $value, string $logical = 'and', bool $not = false)
    {
        return $this->where($attribute, 'ew', $value, $logical, $not);
    }

    /**
     * Add a "or where ends with" clause to the query.
     *
     * @param mixed $attribute
     * @param mixed $value
     * @param bool $not
     * @return $this
     */
    public function orWhereEndsWith($attribute, $value, bool $not = false)
    {
        return $this->whereEndsWith($attribute, $value, 'or', $not);
    }

    /**
     * Add a "where not ends with" clause to the query.
     *
     * @param mixed $attribute
     * @param mixed $value
     * @param string $logical
     * @return $this
     */
    public function whereNotEndsWith($attribute, $value, string $logical = 'and')
    {
        return $this->where($attribute, 'ew', $value, $logical, true);
    }

    /**
     * Add a "or where not ends with" clause to the query.
     *
     * @param mixed $attribute
     * @param mixed $value
     * @return $this
     */
    public function orWhereNotEndsWith($attribute, $value)
    {
        return $this->whereNotEndsWith($attribute, $value, 'or');
    }

    /**
     * Add a "where greater than" clause to the query.
     *
     * @param mixed $attribute
     * @param mixed $value
     * @param string $logical
     * @param bool $not
     * @return $this
     */
    public function whereGreaterThan($attribute, $value, string $logical = 'and', bool $not = false)
    {
        return $this->where($attribute, 'gt', $value, $logical, $not);
    }

    /**
     * Add a "or where greater than" clause to the query.
     *
     * @param mixed $attribute
     * @param mixed $value
     * @param bool $not
     * @return $this
     */
    public function orWhereGreaterThan($attribute, $value, bool $not = false)
    {
        return $this->whereGreaterThan($attribute, $value, 'or', $not);
    }

    /**
     * Add a "where not greater than" clause to the query.
     *
     * @param mixed $attribute
     * @param mixed $value
     * @param string $logical
     * @return $this
     */
    public function whereNotGreaterThan($attribute, $value, string $logical = 'and')
    {
        return $this->where($attribute, 'gt', $value, $logical, true);
    }

    /**
     * Add a "or where not greater than" clause to the query.
     *
     * @param mixed $attribute
     * @param mixed $value
     * @return $this
     */
    public function orWhereNotGreaterThan($attribute, $value)
    {
        return $this->whereNotGreaterThan($attribute, $value, 'or');
    }

    /**
     * Add a "where greater than or equal to" clause to the query.
     *
     * @param mixed $attribute
     * @param mixed $value
     * @param string $logical
     * @param bool $not
     * @return $this
     */
    public function whereGreaterThanOrEqualTo($attribute, $value, string $logical = 'and', bool $not = false)
    {
        return $this->where($attribute, 'ge', $value, $logical, $not);
    }

    /**
     * Add a "or where greater than or equal to" clause to the query.
     *
     * @param mixed $attribute
     * @param mixed $value
     * @param bool $not
     * @return $this
     */
    public function orWhereGreaterThanOrEqualTo($attribute, $value, bool $not = false)
    {
        return $this->whereGreaterThanOrEqualTo($attribute, $value, 'or', $not);
    }

    /**
     * Add a "where not greater than or equal to" clause to the query.
     *
     * @param mixed $attribute
     * @param mixed $value
     * @param string $logical
     * @return $this
     */
    public function whereNotGreaterThanOrEqualTo($attribute, $value, string $logical = 'and')
    {
        return $this->where($attribute, 'ge', $value, $logical, true);
    }

    /**
     * Add a "or where not greater than or equal to" clause to the query.
     *
     * @param mixed $attribute
     * @param mixed $value
     * @return $this
     */
    public function orWhereNotGreaterThanOrEqualTo($attribute, $value)
    {
        return $this->whereNotGreaterThanOrEqualTo($attribute, $value, 'or');
    }

    /**
     * Add a "where less than" clause to the query.
     *
     * @param mixed $attribute
     * @param mixed $value
     * @param string $logical
     * @param bool $not
     * @return $this
     */
    public function whereLessThen($attribute, $value, string $logical = 'and', bool $not = false)
    {
        return $this->where($attribute, 'lt', $value, $logical, $not);
    }

    /**
     * Add a "or where less than" clause to the query.
     *
     * @param mixed $attribute
     * @param mixed $value
     * @param bool $not
     * @return $this
     */
    public function orWhereLessThen($attribute, $value, bool $not = false)
    {
        return $this->whereLessThen($attribute, $value, 'or', $not);
    }

    /**
     * Add a "where not less than" clause to the query.
     *
     * @param mixed $attribute
     * @param mixed $value
     * @param string $logical
     * @return $this
     */
    public function whereNotLessThen($attribute, $value, string $logical = 'and')
    {
        return $this->where($attribute, 'lt', $value, $logical, true);
    }

    /**
     * Add a "or where not less than" clause to the query.
     *
     * @param mixed $attribute
     * @param mixed $value
     * @return $this
     */
    public function orWhereNotLessThen($attribute, $value)
    {
        return $this->whereNotLessThen($attribute, $value, 'or');
    }

    /**
     * Add a "where less than or equal to" clause to the query.
     *
     * @param mixed $attribute
     * @param mixed $value
     * @param string $logical
     * @param bool $not
     * @return $this
     */
    public function whereLessThenOrEqualTo($attribute, $value, string $logical = 'and', bool $not = false)
    {
        return $this->where($attribute, 'le', $value, $logical, $not);
    }

    /**
     * Add a "or where less than or equal to" clause to the query.
     *
     * @param mixed $attribute
     * @param mixed $value
     * @param bool $not
     * @return $this
     */
    public function orWhereLessThenOrEqualTo($attribute, $value, bool $not = false)
    {
        return $this->whereLessThenOrEqualTo($attribute, $value, 'or', $not);
    }

    /**
     * Add a "where not less than or equal to" clause to the query.
     *
     * @param mixed $attribute
     * @param mixed $value
     * @param string $logical
     * @return $this
     */
    public function whereNotLessThenOrEqualTo($attribute, $value, string $logical = 'and')
    {
        return $this->where($attribute, 'le', $value, $logical, true);
    }

    /**
     * Add a "or where not less than or equal to" clause to the query.
     *
     * @param mixed $attribute
     * @param mixed $value
     * @return $this
     */
    public function orWhereNotLessThenOrEqualTo($attribute, $value)
    {
        return $this->whereNotLessThenOrEqualTo($attribute, $value, 'or');
    }

    /**
     * Add a "where in" clause to the query.
     *
     * @param string $attribute
     * @param array $values
     * @param string $logical
     * @param bool $not
     * @return $this
     */
    public function whereIn(string $attribute, array $values, string $logical = 'and', bool $not = false)
    {
        $type = 'In';

        $this->wheres[] = compact('type', 'attribute', 'values', 'logical', 'not');

        return $this;
    }

    /**
     * Add a "or where in" clause to the query.
     *
     * @param string $attribute
     * @param array $values
     * @param bool $not
     * @return $this
     */
    public function orWhereIn(string $attribute, array $values, bool $not = false)
    {
        return $this->whereIn($attribute, $values, 'or', $not);
    }

    /**
     * Add a "where not in" clause to the query.
     *
     * @param string $attribute
     * @param array $values
     * @param string $logical
     * @return $this
     */
    public function whereNotIn(string $attribute, array $values, string $logical = 'and')
    {
        return $this->whereIn($attribute, $values, $logical, true);
    }

    /**
     * Add a "or where not in" clause to the query.
     *
     * @param string $attribute
     * @param array $values
     * @return $this
     */
    public function orWhereNotIn(string $attribute, array $values)
    {
        return $this->whereIn($attribute, $values, 'or', true);
    }

    /**
     * Add a "where present" clause to the query.
     *
     * @param string|array $attributes
     * @param string $logical
     * @param bool $not
     * @return $this
     */
    public function wherePresent($attributes, string $logical = 'and', bool $not = false)
    {
        $type = 'Present';

        foreach (Arr::wrap($attributes) as $attribute) {
            $this->wheres[] = compact('type', 'attribute', 'logical', 'not');
        }

        return $this;
    }

    /**
     * Add a "or where present" clause to the query.
     *
     * @param string|array $attributes
     * @param bool $not
     * @return $this
     */
    public function orWherePresent($attributes, bool $not = false)
    {
        return $this->wherePresent($attributes, 'or', $not);
    }

    /**
     * Add a "not where present" clause to the query.
     *
     * @param string|array $attributes
     * @param string $logical
     * @return $this
     */
    public function whereNotPresent($attributes, string $logical = 'and')
    {
        return $this->wherePresent($attributes, $logical, true);
    }

    /**
     * Add a "or where not present" clause to the query.
     *
     * @param string|array $attributes
     * @return $this
     */
    public function orWhereNotPresent($attributes)
    {
        return $this->orWherePresent($attributes, true);
    }

    public function whereComplex($attribute, $operator, Closure $value, string $boolean)
    {
        return $this;
    }

    private function addArrayOfWheres($attribute, string $boolean, bool $not)
    {
        return $this;
    }

    /**
     * Prepare the value and operator for a where clause.
     *
     * @param  string  $value
     * @param  string  $operator
     * @param  bool  $useDefault
     * @return array
     *
     * @throws \InvalidArgumentException
     */
    public function prepareValueAndOperator($value, $operator, $useDefault = false)
    {
        if ($useDefault) {
            return [$operator, '='];
        } elseif ($this->invalidOperatorAndValue($operator, $value)) {
            throw new InvalidArgumentException('Illegal operator and value combination.');
        }

        return [$value, $operator];
    }

    /**
     * Determine if the given operator and value combination is legal.
     *
     * Prevents using Null values with invalid operators.
     *
     * @param  string  $operator
     * @param  mixed  $value
     * @return bool
     */
    protected function invalidOperatorAndValue($operator, $value)
    {
        return is_null($value) && in_array($operator, $this->operators) &&
            ! in_array($operator, ['pr']);
    }

    /**
     * @param mixed $operator
     * @return bool
     */
    public function invalidOperator($operator): bool
    {
        return ! in_array(strtolower($operator), $this->operators);
    }

    /**
     * Get the SCIM representation of the query.
     *
     * @return string
     */
    public function toScim()
    {
        return $this->grammar->compileWheres($this);
    }

    /**
     * Add a nested where statement to the query.
     *
     * @param \Closure $callback
     * @param string $boolean
     * @param bool $not
     * @return \DanutAvadanei\Scim2\Query\Builder
     */
    public function whereNested(Closure $callback, string $boolean = 'and', bool $not = false)
    {
        call_user_func($callback, $query = $this->forNestedWhere());

        return $this->addNestedWhereQuery($query, $boolean, $not);
    }

    /**
     * Add a nested "or where" statement to the query.
     *
     * @param \Closure $callback
     * @param bool $not
     * @return \DanutAvadanei\Scim2\Query\Builder
     */
    public function orWhereNested(Closure $callback, bool $not = false)
    {
        return $this->whereNested($callback, 'or', $not);
    }

    /**
     * Add a nested "where not" statement to the query.
     *
     * @param \Closure $callback
     * @param string $logical
     * @return \DanutAvadanei\Scim2\Query\Builder
     */
    public function whereNotNested(Closure $callback, string $logical = 'and')
    {
        return $this->whereNested($callback, $logical, true);
    }

    /**
     * Add a nested "or where" statement to the query.
     *
     * @param \Closure $callback
     * @return \DanutAvadanei\Scim2\Query\Builder
     */
    public function orWhereNotNested(Closure $callback)
    {
        return $this->whereNested($callback, 'or', true);
    }

    /**
     * Create a new query instance for nested where condition.
     *
     * @return \DanutAvadanei\Scim2\Query\Builder
     */
    public function forNestedWhere()
    {
        return $this->newQuery();
    }

    /**
     *
     * @return \DanutAvadanei\Scim2\Query\Builder
     */
    public function newQuery()
    {
        return new static($this->grammar);
    }

    /**
     * Add another query builder as a nested where to the query builder.
     *
     * @param \DanutAvadanei\Scim2\Query\Builder $query
     * @param string $logical
     * @param bool $not
     * @return $this
     */
    public function addNestedWhereQuery(Builder $query, string $logical = 'and', bool $not = false)
    {
        if (count($query->wheres)) {
            $type = 'Nested';

            $this->wheres[] = compact('type', 'query', 'logical', 'not');
        }

        return $this;
    }
}
