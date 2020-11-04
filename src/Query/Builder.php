<?php

namespace DanutAvadanei\Scim2\Query;

use Closure;
use DanutAvadanei\Scim2\Concerns\BuildsQueries;
use DanutAvadanei\Scim2\ConnectionInterface;
use Illuminate\Support\Arr;
use Illuminate\Support\LazyCollection;
use Illuminate\Support\Traits\ForwardsCalls;
use Illuminate\Support\Traits\Macroable;
use InvalidArgumentException;

class Builder
{
    use ForwardsCalls, BuildsQueries, Macroable {
        __call as macroCall;
    }

    /**
     * The data provider connection instance.
     *
     * @var \DanutAvadanei\Scim2\ConnectionInterface
     */
    public ConnectionInterface $connection;

    /**
     * The where constraints for the query.
     *
     * @var array
     */
    public array $wheres = [];

    /**
     * All of the available clause operators.
     *
     * @var string[]
     */
    public array $operators = [
        'eq', 'ne', 'co', 'sw', 'ew', 'pr' ,'gt', 'ge', 'lt', 'le',
    ];

    /**
     * The attributes that should be returned.
     *
     * @var array
     */
    public array $attributes = ['*'];

    /**
     * The scim query grammar instance.
     *
     * @var \DanutAvadanei\Scim2\Query\Grammar
     */
    public Grammar $grammar;

    /**
     * The database query post processor instance.
     *
     * @var \DanutAvadanei\Scim2\Query\Processor
     */
    public Processor $processor;

    /**
     * The maximum number of records to return.
     *
     * @var int
     */
    public int $limit = 100;

    /**
     * The number of records to skip.
     *
     * @var int
     */
    public int $offset = 0;

    /**
     * Create a new query builder instance.
     *
     * @param \DanutAvadanei\Scim2\ConnectionInterface $connection
     * @param \DanutAvadanei\Scim2\Query\Grammar|null $grammar
     * @param \DanutAvadanei\Scim2\Query\Processor|null $processor
     */
    public function __construct(ConnectionInterface $connection, Grammar $grammar = null, Processor $processor = null)
    {
        $this->connection = $connection;
        $this->grammar = $grammar ?: $connection->getQueryGrammar();
        $this->processor = $processor ?: $connection->getPostProcessor();
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
     * Add a raw where clause to the query.
     *
     * @param string $scim
     * @param string $logical
     * @return \DanutAvadanei\Scim2\Query\Builder
     */
    public function whereRaw(string $scim, string $logical = 'and')
    {
        $this->wheres[] = ['type' => 'raw', 'scim' => $scim, 'logical' => $logical];

        return $this;
    }

    /**
     * Add a raw "or where" clause to the query.
     *
     * @param string $scim
     * @return \DanutAvadanei\Scim2\Query\Builder
     */
    public function orWhereRaw(string $scim)
    {
        return $this->whereRaw($scim, 'or');
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
     * Add an array of where clauses to the query.
     *
     * @param array $attribute
     * @param string logical$
     * @param bool $not
     * @param string $method
     * @return $this
     */
    private function addArrayOfWheres(array $attribute, string $logical, bool $not, string $method = 'where')
    {
        return $this->whereNested(function ($query) use ($attribute, $method, $logical) {
            foreach ($attribute as $key => $value) {
                if (is_numeric($key) && is_array($value)) {
                    $query->{$method}(...array_values($value));
                } else {
                    $query->$method($key, 'eq', $value, $logical);
                }
            }
        }, $logical, $not);
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
     * Determine if the given operator and value combination is legal.
     *
     * Prevents using Null values with invalid operators.
     *
     * @param  string  $operator
     * @param  mixed  $value
     * @return bool
     */
    protected function invalidOperatorAndValue(string $operator, $value): bool
    {
        return is_null($value) && in_array($operator, $this->operators) &&
            ! in_array($operator, ['pr']);
    }

    /**
     * Prepare the value and operator for a where clause.
     *
     * @param  mixed $value
     * @param  mixed $operator
     * @param  bool  $useDefault
     * @return array
     *
     * @throws \InvalidArgumentException
     */
    public function prepareValueAndOperator($value, $operator, $useDefault = false): array
    {
        if ($useDefault) {
            return [$operator, 'eq'];
        } elseif ($this->invalidOperatorAndValue($operator, $value)) {
            throw new InvalidArgumentException('Illegal operator and value combination.');
        }

        return [$value, $operator];
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
        return new static($this->connection, $this->grammar);
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

    /**
     * Alias to set the "limit" value of the query.
     *
     * @param  int  $value
     * @return $this
     */
    public function take(int $value): self
    {
        return $this->limit($value);
    }

    /**
     * Set the "limit" value of the query.
     *
     * @param  int  $value
     * @return $this
     */
    public function limit(int $value): self
    {
        if ($value >= 0) {
            $this->limit = $value;
        }

        return $this;
    }

    /**
     * Alias to set the "offset" value of the query.
     *
     * @param  int  $value
     * @return $this
     */
    public function skip(int $value): self
    {
        return $this->offset($value);
    }

    /**
     * Set the "offset" value of the query.
     *
     * @param  int  $value
     * @return $this
     */
    public function offset(int $value): self
    {
        $this->offset = max(0, $value);

        return $this;
    }

    /**
     * Execute a query for a single record by uid.
     *
     * @param  string $uid
     * @param  array $columns
     * @return mixed|null
     */
    public function find(string $uid, array $columns = ['*'])
    {
        return $this->where('uid', 'eq', $uid)->first($columns);
    }

    /**
     * Execute the query and get the first result.
     *
     * @param  array|string  attributes$
     * @return mixed|null
     */
    public function first($attributes = ['*'])
    {
        return $this->take(1)->get($attributes)->first();
    }

    /**
     * Get a lazy collection for the given query.
     *
     * @return \Illuminate\Support\LazyCollection
     */
    public function cursor()
    {
        return new LazyCollection(function () {
            yield from $this->connection->cursor($this->toScim());
        });
    }

    /**
     * Execute the query as a "select" statement.
     *
     * @param  array|string  $attributes
     * @return \Illuminate\Support\Collection
     */
    public function get($attributes = ['*'])
    {
        return collect($this->onceWithAttributes(Arr::wrap($attributes), function () {
            return $this->processor->processSelect($this, $this->runSelect());
        }));
    }

    /**
     * Run the query as a "select" statement against the connection.
     *
     * @return array
     */
    protected function runSelect()
    {
        return $this->connection->select($this->toScim());
    }

    /**
     * Execute the given callback while selecting the given attributes.
     *
     * After running the callback, the attributes are reset to the original value.
     *
     * @param  array  $attributes
     * @param  callable  $callback
     * @return mixed
     */
    protected function onceWithAttributes(array $attributes, callable $callback)
    {
        $original = $this->attributes;

        if ($original === ['*']) {
            $this->attributes = $attributes;
        }

        $result = $callback();

        $this->attributes = $original;

        return $result;
    }

    /**
     * Get the scim2 filter representation of the query.
     *
     * @return string
     */
    public function toScimFilter()
    {
        return $this->grammar->compileWheres($this);
    }

    /**
     * Get the scim2 query.
     *
     * @return array
     */
    public function toScim(): array
    {
        return $this->grammar->compile($this);
    }

    /**
     * Handle dynamic method calls into the method.
     *
     * @param string $method
     * @param array $parameters
     * @return mixed
     *
     * @throws \BadMethodCallException
     */
    public function __call(string $method, array $parameters)
    {
        if (static::hasMacro($method)) {
            return $this->macroCall($method, $parameters);
        }

        static::throwBadMethodCallException($method);
    }
}
