<?php

namespace App\Models\Extensions;

use App\Exceptions\Internal\QueryBuilderException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Builder as BaseBuilder;
use Illuminate\Database\Query\Expression;

/**
 * A query builder which uses the trait {@link FixedQueryBuilderTrait}.
 *
 * This is the "default" query builder for most of our models.
 * This query builder fixes {@link \Illuminate\Database\Eloquent\Builder}
 * such that method used by Lychee throw proper exceptions.
 * See {@link FixedQueryBuilderTrait} for details.
 *
 * @template TModelClass of \Illuminate\Database\Eloquent\Model
 *
 * @extends Builder<TModelClass>
*/
class FixedQueryBuilder extends Builder
{
	/**
	 * Add a basic where clause to the query.
	 *
	 * @param \Closure|string|array<string>|Expression $column
	 * @param mixed                                    $operator
	 * @param mixed                                    $value
	 * @param string                                   $boolean
	 *
	 * @return $this
	 *
	 * @throws QueryBuilderException
	 */
	public function where($column, $operator = null, $value = null, $boolean = 'and'): static
	{
		try {
			parent::where($column, $operator, $value, $boolean);

			return $this;
		} catch (\Throwable $e) {
			throw new QueryBuilderException($e);
		}
	}

	/**
	 * Add a "where in" clause to the query.
	 *
	 * @param string $column
	 * @param mixed  $values
	 * @param string $boolean
	 * @param bool   $not
	 *
	 * @return $this
	 *
	 * @throws QueryBuilderException
	 */
	public function whereIn($column, $values, $boolean = 'and', $not = false): static
	{
		try {
			return parent::whereIn($column, $values, $boolean, $not);
		} catch (\Throwable $e) {
			throw new QueryBuilderException($e);
		}
	}

	/**
	 * Set the columns to be selected.
	 *
	 * @param array|mixed $columns
	 *
	 * @return $this
	 *
	 * @throws QueryBuilderException
	 */
	public function select($columns = ['*']): static
	{
		try {
			return parent::select($columns);
		} catch (\Throwable $e) {
			throw new QueryBuilderException($e);
		}
	}

	/**
	 * Add a join clause to the query.
	 *
	 * @param string          $table
	 * @param \Closure|string $first
	 * @param string|null     $operator
	 * @param string|null     $second
	 * @param string          $type
	 * @param bool            $where
	 *
	 * @return $this
	 *
	 * @throws QueryBuilderException
	 */
	public function join($table, $first, $operator = null, $second = null, $type = 'inner', $where = false): static
	{
		try {
			return parent::join($table, $first, $operator, $second, $type, $where);
		} catch (\Throwable $e) {
			throw new QueryBuilderException($e);
		}
	}

	/**
	 * Add a left join to the query.
	 *
	 * @param string          $table
	 * @param \Closure|string $first
	 * @param string|null     $operator
	 * @param string|null     $second
	 *
	 * @return $this
	 *
	 * @throws QueryBuilderException
	 */
	public function leftJoin($table, $first, $operator = null, $second = null): static
	{
		try {
			return parent::leftJoin($table, $first, $operator, $second);
		} catch (\Throwable $e) {
			throw new QueryBuilderException($e);
		}
	}

	/**
	 * Add an "order by" clause to the query.
	 *
	 * @param \Closure|Builder|BaseBuilder|Expression|string $column
	 * @param string                                         $direction
	 *
	 * @return $this
	 *
	 * @throws QueryBuilderException
	 */
	public function orderBy($column, $direction = 'asc'): static
	{
		try {
			// The parent class is Eloquent\Builder and Eloquent\Builder::orderBy()
			// accepts exactly the types for columns as listed above
			// (see source code of the framework).
			// However, the buggy larastan ruleset lies to PhpStan about the
			// types and hence we must ignore this line.
			//
			// @phpstan-ignore-next-line
			return parent::orderBy($column, $direction);
		} catch (\Throwable $e) {
			throw new QueryBuilderException($e);
		}
	}

	/**
	 * Add a new select column to the query.
	 *
	 * @param array|mixed $column
	 *
	 * @return $this
	 *
	 * @throws QueryBuilderException
	 */
	public function addSelect($column): static
	{
		try {
			return parent::addSelect($column);
		} catch (\Throwable $e) {
			throw new QueryBuilderException($e);
		}
	}

	/**
	 * Add an "or where" clause to the query.
	 *
	 * @param \Closure|array<string>|string|Expression $column
	 * @param mixed                                    $operator
	 * @param mixed                                    $value
	 *
	 * @return $this
	 *
	 * @throws QueryBuilderException
	 */
	public function orWhere($column, $operator = null, $value = null): static
	{
		try {
			parent::orWhere($column, $operator, $value);

			return $this;
		} catch (\Throwable $e) {
			throw new QueryBuilderException($e);
		}
	}
}
