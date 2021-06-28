<?php

namespace Raank\Processors;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Making a query builder from request body.
 *
 * @category Processors
 * @package Raank\Http\Middleware
 * @subpackage BodyBuilder
 * @version 1.0.0
 */
class BodyBuilder
{
    /**
     * The constructor method.
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     *
     * @param array   $body The body to Processing.
     * @param integer $perPage Per Page Items.
     */
    public function __construct(
        protected array $body,
        protected int $perPage = 10
    ) {}

    /**
     * Mounting the builder.
     *
     * @param string $model
     *
     * @return LengthAwarePaginator
     */
    public function builder(string $model): LengthAwarePaginator
    {
        $body = $this->body;

        $builder = $model::whereNotNull(
            (new $model)->getKeyName()
        );

        if (array_key_exists('where', $body)) {
            $this->queryWhere(
                $builder,
                $this->treatWhere(
                    $body['where']
                )
            );
        }

        if (array_key_exists('whereNull', $body)) {
            $this->queryWhereNull($builder, $body['whereNull']);
        }

        if (array_key_exists('whereNotNull', $body)) {
            $this->queryWhereNotNull($builder, $body['whereNotNull']);
        }

        if (array_key_exists('whereDate', $body)) {
            $this->queryWhereDate(
                $builder,
                $this->treatWhereDate(
                    $body['whereDate']
                )
            );
        }

        if (array_key_exists('whereBetween', $body)) {
            $this->queryWhereBetween($builder, $body['whereBetween']);
        }

        if (array_key_exists('orderBy', $body)) {
            $this->queryOrderBy($builder, $body['orderBy']);
        }

        return $builder->paginate(1);
    }

    /**
     * If where in body.
     *
     * @param array $values
     *
     * @return array
     */
    private function treatWhere(array $values): array
    {
        $query = [];

        foreach ($values as $idx => $where) {
            if (count($where) === 3) {
                [$field, $operator, $value] = $where;

                if (strtoupper($operator) === 'LIKE' && strpos($value, '%') === false) {
                    $value = '%' . $value . '%';
                }

                $query[$idx] = [$field, $operator, $value];
            } elseif (count($where) === 2) {
                [$field, $value] = $where;

                $query[$idx] = [$field, '=', $value];
            }
        }

        return $query;
    }

    /**
     * If where in body.
     *
     * @param array $values
     *
     * @return array
     */
    private function treatWhereDate(array $values): array
    {
        $query = [];

        foreach ($values as $idx => $where) {
            if (count($where) === 3) {
                [$field, $operator, $value] = $where;

                if (strtoupper($operator) === 'LIKE' && strpos($value, '%') === false) {
                    $value = '%' . $value . '%';
                }

                $query[$idx] = [$field, $operator, $value];
            } elseif (count($where) === 2) {
                [$field, $value] = $where;

                $query[$idx] = [$field, '=', $value];
            }
        }

        return $query;
    }

    /**
     * Builder to where.
     *
     * @param Builder $builder
     * @param array $values
     *
     * @return void
     */
    private function queryWhere(Builder &$builder, array $values): void
    {
        foreach ($values as $where) {
            [$field, $operator, $value] = $where;

            $builder->where(
                $field,
                $operator,
                $value
            );
        }
    }

    /**
     * Builder to where not null.
     *
     * @param Builder $builder
     * @param array $values
     *
     * @return void
     */
    private function queryWhereNotNull(Builder &$builder, array $values): void
    {
        foreach ($values as $value) {
            $builder->whereNotNull($value);
        }
    }

    /**
     * Builder to where null.
     *
     * @param Builder $builder
     * @param array $values
     *
     * @return void
     */
    private function queryWhereNull(Builder &$builder, array $values): void
    {
        foreach ($values as $value) {
            $builder->whereNull($value);
        }
    }

    /**
     * Builder to where betweeen.
     *
     * @param Builder $builder
     * @param array $values
     *
     * @return void
     */
    private function queryWhereBetween(Builder &$builder, array $values): void
    {
        foreach ($values as $row) {
            foreach ($row as $field => $array) {
                if (count($array) === 2) {
                    $builder->orWhereBetween($field, $array);
                }
            }
        }
    }

    /**
     * Builder to order by.
     *
     * @param Builder $builder
     * @param array $values
     *
     * @return void
     */
    private function queryOrderBy(Builder &$builder, array $values): void
    {
        foreach ($values as $item) {
            $builder->orderBy($item['field'], $item['order'] ?? 'DESC');
        }
    }

    /**
     * Builder to where date.
     *
     * @param Builder $builder
     * @param array $values
     *
     * @return void
     */
    private function queryWhereDate(Builder &$builder, array $values): void
    {
        foreach ($values as $where) {
            [$field, $operator, $value] = $where;

            $builder->whereDate(
                $field,
                $operator,
                $value
            );
        }
    }
}