<?php

namespace DanutAvadanei\ScimQuery;

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

        if ($this->invalidOperator($operator)) {
            [$value, $operator] = [$operator, 'eq'];
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
     * Add a "where equals" clause to the query.
     *
     * @param mixed $attribute
     * @param mixed $value
     * @param string $logical
     * @param bool $not
     * @return $this
     */
    public function whereEquals($attribute, $value, string $logical = 'and', bool $not = false)
    {
        return $this->where($attribute, 'eq', $value, $logical, $not);
    }

    /**
     * Add a "or where equals" clause to the query.
     *
     * @param mixed $attribute
     * @param mixed $value
     * @param bool $not
     * @return $this
     */
    public function orWhereEquals($attribute, $value, bool $not = false)
    {
        return $this->whereEquals($attribute, $value, 'or', $not);
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
        return $this->where($attribute, 'eq', $value, $logical, true);
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

    public function whereNested(Closure $attribute, string $boolean)
    {
        return $this;
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
}
