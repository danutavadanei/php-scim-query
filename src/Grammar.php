<?php

namespace DanutAvadanei\ScimQuery;

use Illuminate\Support\Traits\Macroable;

class Grammar
{
    use Macroable;

    /**
     * Compile the "where" portions of the query.
     *
     * @param  \DanutAvadanei\ScimQuery\Builder $query
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
     * @param  \DanutAvadanei\ScimQuery\Builder $query
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
        return $this->removeLeadingLogical(implode(' ', $scim));
    }

    /**
     * Compile a raw where clause.
     *
     * @param  \DanutAvadanei\ScimQuery\Builder  $query
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
     * @param  \DanutAvadanei\ScimQuery\Builder  $query
     * @param  array  $where
     * @return string
     */
    protected function whereBasic(Builder $query, array $where): string
    {
        $prefix = $where['not'] ? 'not (' : '';
        $suffix = $where['not'] ? ')' : '';

        $attribute = $this->wrap($where['attribute']);

        $value = $this->wrapValue($where['value']);

        $operator = $where['operator'];

        return $prefix . $attribute.' '.$operator.' '.$value . $suffix;
    }

    /**
     * Wrap a value in keyword identifiers.
     *
     * @param  \DanutAvadanei\ScimQuery\Expression|string  $value
     * @return string
     */
    public function wrap($value): string
    {
        if ($this->isExpression($value)) {
            return $this->getValue($value);
        }

        return $value;
    }

    /**
     * Wrap a single string in keyword identifiers.
     *
     * @param  string  $value
     * @return string
     */
    protected function wrapValue(string $value): string
    {
        return '"'.str_replace('"', '\"', $value).'"';
    }

    /**
     * Determine if the given value is a raw expression.
     *
     * @param  mixed  $value
     * @return bool
     */
    public function isExpression($value): bool
    {
        return $value instanceof Expression;
    }

    /**
     * Get the value of a raw expression.
     *
     * @param  \DanutAvadanei\ScimQuery\Expression  $expression
     * @return string
     */
    public function getValue(Expression $expression): string
    {
        return $expression->getValue();
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
}
