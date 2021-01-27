<?php

namespace Larapen\Admin\app\Http\Controllers;

use Larapen\Admin\app\Http\Controllers\Features\AjaxTable;
use Larapen\Admin\app\Http\Controllers\Features\BulkDelete;
use Larapen\Admin\app\Http\Controllers\Features\Reorder;
use Larapen\Admin\app\Http\Controllers\Features\SaveActions;
use Larapen\Admin\app\Http\Controllers\Features\ShowDetailsRow;
use Larapen\Admin\app\Http\Controllers\Features\TranslateItem;
use Larapen\Admin\Panel;
use Prologue\Alerts\Facades\Alert;

// VALIDATION
use App\Http\Requests\Admin\Request as StoreRequest;
use App\Http\Requests\Admin\Request as UpdateRequest;

class PanelController extends Controller
{
	use AjaxTable, Reorder, ShowDetailsRow, SaveActions, TranslateItem, BulkDelete;
	
	public $xPanel;
	public $data = [];
	public $request;
	
	public $parentId = 0;
	
	/**
	 * Controller constructor.
	 */
	public function __construct()
	{
		parent::__construct();
		
		if (!$this->xPanel) {
			$this->xPanel = new Panel();
			
			$this->middleware(function ($request, $next) {
				$this->request = $request;
				$this->xPanel->request = $request;
				$this->setup();
				
				return $next($request);
			});
		}
	}
	
	public function setup()
	{
		// ...
	}
	
	/**
	 * Display all rows in the database for this entity.
	 *
	 * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
	 */
	public function index()
	{
		$this->xPanel->hasAccessOrFail('list');
		
		$this->data['xPanel'] = $this->xPanel;
		$this->data['title'] = ucfirst($this->xPanel->entity_name_plural);
		
		// get all entries if AJAX is not enabled
		if (!$this->data['xPanel']->ajax_table) {
			$this->data['entries'] = $this->data['xPanel']->getEntries();
		}
		
		return view('admin::panel.list', $this->data);
	}
	
	/**
	 * Show the form for creating inserting a new row.
	 *
	 * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
	 */
	public function create()
	{
		$this->xPanel->hasAccessOrFail('create');
		
		// prepare the fields you need to show
		$this->data['xPanel'] = $this->xPanel;
		$this->data['saveAction'] = $this->getSaveAction();
		$this->data['fields'] = $this->xPanel->getCreateFields();
		$this->data['title'] = trans('admin::messages.add') . ' ' . $this->xPanel->entity_name;
		
		return view('admin::panel.create', $this->data);
	}
	
	/**
	 * Store a newly created resource in the database.
	 *
	 * @param UpdateRequest|null $request
	 * @return mixed
	 */
	public function storeCrud(StoreRequest $request = null)
	{
		try {
			$this->xPanel->hasAccessOrFail('create');
			
			// fallback to global request instance
			if (is_null($request)) {
				$request = \Request::instance();
			}
			
			// replace empty values with NULL, so that it will work with MySQL strict mode on
			foreach ($request->input() as $key => $value) {
				if (empty($value) && $value !== '0') {
					$request->request->set($key, null);
				}
			}
			
			// insert item in the db
			$item = $this->xPanel->create($request->except(['redirect_after_save', '_token']));
			
			if (empty($item)) {
				\Alert::error(trans('admin::messages.error_saving_entry'))->flash();
				return back();
			}
			
			// show a success message
			\Alert::success(trans('admin::messages.insert_success'))->flash();
			
			// save the redirect choice for next time
			$this->setSaveAction();
			
			return $this->performSaveAction($item->getKey());
		} catch (\Exception $e) {
			// Get error message
			if (isset($e->errorInfo) && isset($e->errorInfo[2]) && !empty($e->errorInfo[2])) {
				$msg = $e->errorInfo[2];
			} else {
				$msg = $e->getMessage();
			}
			
			// Error notification
			Alert::error('Error found - [' . $e->getCode() . '] : ' . $msg . '.')->flash();
			
			// fallback to global request instance
			if (is_null($request)) {
				$request = \Request::instance();
			}
			
			return \Redirect::to($this->xPanel->route);
		}
	}
	
	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param $id
	 * @param null $childId
	 * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
	 */
	public function edit($id, $childId = null)
	{
		$this->xPanel->hasAccessOrFail('update');
		
		$entry = null;
		if (!empty($childId)) {
			$entry = $this->xPanel->getEntryWithParentAndChildKeys($id, $childId);
			$id = $childId;
		}
		
		// get the info for that entry
		$this->data['entry'] = (isset($entry) && !empty($entry)) ? $entry : $this->xPanel->getEntry($id);
		$this->data['xPanel'] = $this->xPanel;
		$this->data['saveAction'] = $this->getSaveAction();
		$this->data['fields'] = $this->xPanel->getUpdateFields($id);
		$this->data['title'] = trans('admin::messages.edit') . ' ' . $this->xPanel->entity_name;
		
		$this->data['id'] = $id;
		
		return view('admin::panel.edit', $this->data);
	}
	
	/**
	 * Update the specified resource in the database.
	 *
	 * @param UpdateRequest|null $request
	 * @return mixed
	 */
	public function updateCrud(UpdateRequest $request = null)
	{
		try {
			$this->xPanel->hasAccessOrFail('update');
			
			// fallback to global request instance
			if (is_null($request)) {
				$request = \Request::instance();
			}
			
			// replace empty values with NULL, so that it will work with MySQL strict mode on
			foreach ($request->input() as $key => $value) {
				if (empty($value) && $value !== '0') {
					$request->request->set($key, null);
				}
			}
			
			// update the row in the db
			$item = $this->xPanel->update($request->get($this->xPanel->model->getKeyName()), $request->except('redirect_after_save', '_token'));
			
			if (empty($item)) {
				\Alert::error(trans('admin::messages.error_saving_entry'))->flash();
				return back();
			}
			
			// show a success message
			\Alert::success(trans('admin::messages.update_success'))->flash();
			
			// save the redirect choice for next time
			$this->setSaveAction();
			
			return $this->performSaveAction($item->getKey());
		} catch (\Exception $e) {
			// Get error message
			if (isset($e->errorInfo) && isset($e->errorInfo[2]) && !empty($e->errorInfo[2])) {
				$msg = $e->errorInfo[2];
			} else {
				$msg = $e->getMessage();
			}
			
			// Error notification
			Alert::error('Error found - [' . $e->getCode() . '] : ' . $msg . '.')->flash();
			
			return \Redirect::to($this->xPanel->route);
		}
	}
	
	/**
	 * Display the specified resource.
	 *
	 * @param $id
	 * @param null $childId
	 * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
	 */
	public function show($id, $childId = null)
	{
		// @todo: Make the entries details by take account all possible fields
		return \Redirect::to($this->xPanel->route);
		
		$this->xPanel->hasAccessOrFail('show');
		
		$entry = null;
		if (!empty($childId)) {
			$entry = $this->xPanel->getEntryWithParentAndChildKeys($id, $childId);
			$id = $childId;
		}
		
		// get the info for that entry
		$this->data['entry'] = (isset($entry) && !empty($entry)) ? $entry : $this->xPanel->getEntry($id);
		$this->data['xPanel'] = $this->xPanel;
		$this->data['title'] = trans('admin::messages.preview') . ' ' . $this->xPanel->entity_name;
		
		return view('admin::panel.show', $this->data);
	}
	
	/**
	 * Remove the specified resource from storage.
	 *
	 * @param $id
	 * @param null $childId
	 * @return mixed
	 */
	public function destroy($id, $childId = null)
	{
		$this->xPanel->hasAccessOrFail('delete');
		
		if (!empty($childId)) {
			$id = $childId;
		}
		
		return $this->xPanel->delete($id);
	}
}
