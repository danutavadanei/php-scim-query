<?php

namespace DanutAvadanei\Scim2\Query;

use Illuminate\Support\Traits\Macroable;

class Grammar
{
    use Macroable;

    /**
     * Compile a query into a scim 2.0 filter object.
     *
     * @param \DanutAvadanei\Scim2\Query\Builder $query
     * @return array
     */
    public function compile(Builder $query): array
    {
        return [
            'filter' => $this->compileWheres($query),
            'includeAttributes' => $this->compileAttributes($query),
            'limit' => $query->limit,
            'offset' => $query->offset,
        ];
    }

    /**
     * Compile the "select attributes" portion of the query.
     *
     * @param \DanutAvadanei\Scim2\Query\Builder $query
     * @return string
     */
    public function compileAttributes(Builder $query): string
    {
        return implode(',', $query->attributes);
    }

    /**
     * Compile the "where" portions of the query.
     *
     * @param  \DanutAvadanei\Scim2\Query\Builder $query
     * @return string
     */
    public function compileWheres(Builder $query): string
    {
        // Each type of where clauses has its own compiler function which is responsible
        // for actually creating the where clauses SQL. This helps keep the code nice
        // and maintainable since each clause has a very small method that it uses.
        if (is_null($query->wheres)) {
            return '';
        }

        // If we actually have some where clauses, we will strip off the first boolean
        // operator, which is added by the query builders for convenience so we can
        // avoid checking for the first clauses in each of the compilers methods.
        if (count($scim = $this->compileWheresToArray($query)) > 0) {
            return $this->concatenateWhereClauses($scim);
        }

        return '';
    }

    /**
     * Get an array of all the where clauses for the query.
     *
     * @param  \DanutAvadanei\Scim2\Query\Builder $query
     * @return array
     */
    protected function compileWheresToArray(Builder $query): array
    {
        return collect($query->wheres)->map(function ($where) use ($query) {
            return $where['logical'].' '.$this->{"where{$where['type']}"}($query, $where);
        })->all();
    }

    /**
     * Format the where clause statements into one string.
     *
     * @param  array  $scim
     * @return string
     */
    protected function concatenateWhereClauses(array $scim): string
    {
        return $this->trimRedundantParentheses($this->removeLeadingLogical(implode(' ', $scim)));
    }

    /**
     * Compile a raw where clause.
     *
     * @param  \DanutAvadanei\Scim2\Query\Builder  $query
     * @param  array  $where
     * @return string
     */
    protected function whereRaw(Builder $query, array $where): string
    {
        return $where['scim'];
    }

    /**
     * Compile a basic where clause.
     *
     * @param  \DanutAvadanei\Scim2\Query\Builder  $query
     * @param  array  $where
     * @return string
     */
    protected function whereBasic(Builder $query, array $where): string
    {
        $attribute = $where['attribute'];

        $value = $this->wrapValue($where['value']);

        $operator = $where['operator'];

        return $this->wrapExpression(
            $attribute.' '.$operator.' '.$value,
            $where['not']
        );
    }

    /**
     * Compile a present where clause.
     *
     * @param  \DanutAvadanei\Scim2\Query\Builder  $query
     * @param  array  $where
     * @return string
     */
    protected function whereIn(Builder $query, array $where): string
    {
        $logical = $where['not'] ? 'and' : 'or';

        $builder = $query->newQuery();

        foreach ($where['values'] as $value) {
            $builder->where($where['attribute'], 'eq', $value, $logical);
        }

        return $this->wrapExpression($builder->toScimFilter(), $where['not'], true);
    }

    /**
     * Compile a present where clause.
     *
     * @param  \DanutAvadanei\Scim2\Query\Builder  $query
     * @param  array  $where
     * @return string
     */
    protected function wherePresent(Builder $query, array $where): string
    {
        return $this->wrapExpression(
            $where['attribute'].' pr',
            $where['not']
        );
    }

    /**
     * Compile a nested where clause.
     *
     * @param  \DanutAvadanei\Scim2\Query\Builder $query
     * @param  array  $where
     * @return string
     */
    protected function whereNested(Builder $query, array $where): string
    {
        return $this->wrapExpression(
            $this->compileWheres($where['query']),
            $where['not'],
            true
        );
    }

    /**
     * Wrap a single string in keyword identifiers.
     *
     * @param  mixed $value
     * @return string
     */
    protected function wrapValue($value): string
    {
        if (is_bool($value) || is_numeric($value)) {
            return var_export($value, true);
        }

        return '"'.str_replace('"', '\"', $value).'"';
    }

    /**
     * Remove the leading logical from a statement.
     *
     * @param  string  $value
     * @return string
     */
    protected function removeLeadingLogical(string $value): string
    {
        return preg_replace('/and |or /i', '', $value, 1);
    }

    /**
     * Trim the redundant parentheses from a statement.
     *
     * @param  string  $value
     * @return string
     */
    protected function trimRedundantParentheses(string $value): string
    {
        return preg_replace('/^\((.*)\)$/i', '$1', $value);
    }

    private function wrapExpression(string $string, bool $negation, bool $shouldWrap = false): string
    {
        if ($negation) {
            return 'not ('.$string.')';
        }

        if (! $shouldWrap) {
            return $string;
        }

        return '('.$string.')';
    }
}
