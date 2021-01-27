<?php

namespace Larapen\Admin\PanelTraits;

trait Query
{
    // ----------------
    // ADVANCED QUERIES
    // ----------------
	
	/**
	 * Add another clause to the query (for ex, a WHERE clause).
	 *
	 * Examples:
	 * // $this->xPanel->addClause('active');
	 * $this->xPanel->addClause('type', 'car');
	 * $this->xPanel->addClause('where', 'name', '==', 'car');
	 * $this->xPanel->addClause('whereName', 'car');
	 * $this->xPanel->addClause('whereHas', 'posts', function($query) {
	 *     $query->activePosts();
	 *     });
	 *
	 *
	 * @param $function
	 * @return mixed
	 */
	public function addClause($function)
	{
		return call_user_func_array([$this->query, $function], array_slice(func_get_args(), 1, 3));
	}
	
	/**
	 * Use eager loading to reduce the number of queries on the table view.
	 *
	 * @param $entities
	 * @return mixed
	 */
	public function with($entities)
	{
		return $this->query->with($entities);
	}
	
	/**
	 * Order the results of the query in a certain way.
	 *
	 * @param $field
	 * @param string $order
	 * @return mixed
	 */
	public function orderBy($field, $order = 'asc')
	{
		return $this->query->orderBy($field, $order);
	}
	
	/**
	 * Group the results of the query in a certain way.
	 *
	 * @param $field
	 * @return mixed
	 */
	public function groupBy($field)
	{
		return $this->query->groupBy($field);
	}
	
	/**
	 * Limit the number of results in the query.
	 *
	 * @param $number
	 * @return mixed
	 */
	public function limit($number)
	{
		return $this->query->limit($number);
	}
	
	/**
	 * Take a certain number of results from the query.
	 *
	 * @param $number
	 * @return mixed
	 */
	public function take($number)
	{
		return $this->query->take($number);
	}
	
	/**
	 * Start the result set from a certain number.
	 *
	 * @param $number
	 * @return mixed
	 */
	public function skip($number)
	{
		return $this->query->skip($number);
	}
	
	/**
	 * Count the number of results.
	 *
	 * @param null $lang
	 * @return mixed
	 */
	public function count($lang = null)
	{
		// If lang is not set, get the default language
		if (empty($lang)) {
			$lang = \Lang::locale();
		}
		
		if (property_exists($this->model, 'translatable')) {
			$countEntries = $this->query->where('translation_lang', $lang)->count();
		} else {
			$countEntries = $this->query->count();
		}
		
		return $countEntries;
	}
}
