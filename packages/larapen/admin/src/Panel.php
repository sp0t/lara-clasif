<?php

namespace Larapen\Admin;


use Illuminate\Support\Arr;
use Larapen\Admin\PanelTraits\Access;
use Larapen\Admin\PanelTraits\AutoFocus;
use Larapen\Admin\PanelTraits\AutoSet;
use Larapen\Admin\PanelTraits\Buttons;
use Larapen\Admin\PanelTraits\Columns;
use Larapen\Admin\PanelTraits\Create;
use Larapen\Admin\PanelTraits\Delete;
use Larapen\Admin\PanelTraits\FakeColumns;
use Larapen\Admin\PanelTraits\FakeFields;
use Larapen\Admin\PanelTraits\Fields;
use Larapen\Admin\PanelTraits\Filters;
use Larapen\Admin\PanelTraits\Query;
use Larapen\Admin\PanelTraits\Read;
use Larapen\Admin\PanelTraits\Reorder;
use Larapen\Admin\PanelTraits\Search;
use Larapen\Admin\PanelTraits\Update;

class Panel
{
	use Access, Read, Search, Create, Update, Delete, Columns, Fields, Query, Reorder, Buttons, AutoSet, AutoFocus, FakeColumns, FakeFields, Filters;
	
	// ----------------
	// xPanel variables
	// ----------------
	// These variables are passed to the PANEL views, inside the $panel variable.
	// All variables are public, so they can be modified from your EntityController.
	// All functions and methods are also public, so they can be used in your EntityController to modify these variables.
	
	public $model = "\App\Models\Entity"; // what's the namespace for your entity's model
	public $route = ''; // what route have you defined for your entity? used for links.
	public $entity_name = 'entry'; // what name will show up on the buttons, in singural (ex: Add entity)
	public $entity_name_plural = 'entries'; // what name will show up on the buttons, in plural (ex: Delete 5 entities)
	public $request;
	
	public $access = ['list', 'create', 'update', 'delete', /*'show'*/];
	
	public $reorder = false;
	public $reorder_label = false;
	public $reorder_max_level = 3;
	
	public $details_row = false;
	public $export_buttons = false;
	
	public $columns = []; // Define the columns for the table view as an array;
	public $create_fields = []; // Define the fields for the "Add new entry" view as an array;
	public $update_fields = []; // Define the fields for the "Edit entry" view as an array;
	
	public $query;
	public $entry;
	public $buttons;
	public $db_column_types = [];
	public $default_page_length = false;
	
	// TONE FIELDS - TODO: find out what he did with them, replicate or delete
	public $sort = [];
	
	public $parent_entity = false;
	public $parent_route = '';
	public $parent_key_field = null;
	public $parent_entity_name = 'entry';
	public $parent_entity_name_plural = 'entries';
	
	public $disable_sync_pivot = false;
	
	// The following methods are used in CrudController or your EntityController to manipulate the variables above.
	
	// ------------------------------------------------------
	// BASICS - model, route, entity_name, entity_name_plural
	// ------------------------------------------------------
	
	/**
	 * This function binds the CRUD to its corresponding Model (which extends Eloquent).
	 * All Create-Read-Update-Delete operations are done using that Eloquent Collection.
	 *
	 * @param $model_namespace
	 */
	public function setModel($model_namespace)
	{
		if (!class_exists($model_namespace)) {
			abort(404, "The model '{$model_namespace}' does not exist.");
		}
		
		$this->model = new $model_namespace();
		$this->query = $this->model->select('*');
	}
	
	/**
	 * Get the corresponding Eloquent Model for the CrudController, as defined with the setModel() function;.
	 *
	 * @return string
	 */
	public function getModel()
	{
		return $this->model;
	}
	
	/**
	 * Get the database connection, as specified in the .env file or overwritten by the property on the model.
	 */
	private function getSchema()
	{
		return \Schema::setConnection($this->getModel()->getConnection());
	}
	
	/**
	 * Set the route for this CRUD.
	 * Ex: admin/article.
	 *
	 * @param [string] Route name.
	 * @param [array] Parameters.
	 */
	public function setRoute($route)
	{
		$this->route = $route;
		$this->initButtons();
	}
	
	public function setParentRoute($route)
	{
		$this->parent_route = $route;
		$this->initButtons();
	}
	
	/**
	 * @param $field
	 */
	public function setParentKeyField($field)
	{
		$this->parent_key_field = $field;
	}
	
	/**
	 * @return string
	 */
	public function getParentKeyField()
	{
		return $this->parent_key_field;
	}
	
	/**
	 * @param $id
	 * @param $childId
	 * @return null
	 */
	public function getEntryWithParentAndChildKeys($id, $childId)
	{
		$entry = null;
		
		$parentKeyField = $this->getParentKeyField();
		if (!empty($parentKeyField)) {
			try {
				$entry = $this->model->query()
					->where($parentKeyField, $id)
					->where($this->model->getKeyName(), $childId)
					->first();
			} catch (\Exception $e) {
				abort(400, $e->getMessage());
			}
			
			if (empty($entry)) {
				abort(404, 'Entry not found!');
			}
		}
		
		return $entry;
	}
	
	/**
	 * Set the route for this CRUD using the route name.
	 * Ex: admin.article.
	 *
	 * @param $route
	 * @param array $parameters
	 * @throws \Exception
	 */
	public function setRouteName($route, $parameters = [])
	{
		$complete_route = $route . '.index';
		
		if (!\Route::has($complete_route)) {
			throw new \Exception('There are no routes for this route name.', 404);
		}
		
		$this->route = route($complete_route, $parameters);
		$this->initButtons();
	}
	
	/**
	 * Get the current CrudController route.
	 *
	 * Can be defined in the CrudController with:
	 * - $this->crud->setRoute(admin_uri('article'))
	 * - $this->crud->setRouteName(admin_uri().'.article')
	 * - $this->crud->route = admin_uri("article")
	 *
	 * @return string
	 */
	public function getRoute()
	{
		return $this->route;
	}
	
	/**
	 * Set the entity name in singular and plural.
	 * Used all over the CRUD interface (header, add button, reorder button, breadcrumbs).
	 *
	 * @param $singular
	 * @param $plural
	 */
	public function setEntityNameStrings($singular, $plural)
	{
		$this->entity_name = $singular;
		$this->entity_name_plural = $plural;
	}
	
	public function setParentEntityNameStrings($singular, $plural)
	{
		$this->parent_entity_name = $singular;
		$this->parent_entity_name_plural = $plural;
	}
	
	/**
	 * Disable syncPivot() feature in the update() method
	 */
	public function disableSyncPivot()
	{
		$this->disable_sync_pivot = true;
	}
	
	/**
	 * @return bool
	 */
	public function isEnabledSyncPivot()
	{
		return !($this->disable_sync_pivot == true);
	}
	
	// ----------------------------------
	// Miscellaneous functions or methods
	// ----------------------------------
	
	/**
	 * Return the first element in an array that has the given 'type' attribute.
	 *
	 * @param $type
	 * @param $array
	 * @return mixed
	 */
	public function getFirstOfItsTypeInArray($type, $array)
	{
		return Arr::first($array, function ($item) use ($type) {
			return $item['type'] == $type;
		});
	}
	
	// ------------
	// TONE FUNCTIONS - UNDOCUMENTED, UNTESTED, SOME MAY BE USED IN THIS FILE
	// ------------
	//
	// TODO:
	// - figure out if they are really needed
	// - comments inside the function to explain how they work
	// - write docblock for them
	// - place in the correct section above (CREATE, READ, UPDATE, DELETE, ACCESS, MANIPULATION)
	
	public function sync($type, $fields, $attributes)
	{
		if (!empty($this->{$type})) {
			$this->{$type} = array_map(function ($field) use ($fields, $attributes) {
				if (in_array($field['name'], (array)$fields)) {
					$field = array_merge($field, $attributes);
				}
				
				return $field;
			}, $this->{$type});
		}
	}
	
	public function setSort($items, $order)
	{
		$this->sort[$items] = $order;
	}
	
	public function sort($items)
	{
		if (array_key_exists($items, $this->sort)) {
			$elements = [];
			
			foreach ($this->sort[$items] as $item) {
				if (is_numeric($key = array_search($item, array_column($this->{$items}, 'name')))) {
					$elements[] = $this->{$items}[$key];
				}
			}
			
			return $this->{$items} = array_merge($elements, array_filter($this->{$items}, function ($item) use ($items) {
				return !in_array($item['name'], $this->sort[$items]);
			}));
		}
		
		return $this->{$items};
	}
	
	/**
	 * Get the Eloquent Model name from the given relation definition string.
	 *
	 * @example For a given string 'company' and a relation between App/Models/User and App/Models/Company, defined by a
	 *          company() method on the user model, the 'App/Models/Company' string will be returned.
	 *
	 * @example For a given string 'company.address' and a relation between App/Models/User, App/Models/Company and
	 *          App/Models/Address defined by a company() method on the user model and an address() method on the
	 *          company model, the 'App/Models/Address' string will be returned.
	 *
	 * @param $relationString String Relation string. A dot notation can be used to chain multiple relations.
	 *
	 * @return string relation model name
	 */
	private function getRelationModel($relationString)
	{
		$result = array_reduce(explode('.', $relationString), function ($obj, $method) {
			return $obj->$method()->getRelated();
		}, $this->model);
		
		return get_class($result);
	}
}
