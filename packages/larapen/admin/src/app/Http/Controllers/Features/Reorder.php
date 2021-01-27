<?php

namespace Larapen\Admin\app\Http\Controllers\Features;

trait Reorder
{
    /**
     *  Reorder the items in the database using the Nested Set pattern.
     *
     *	Database columns needed: id, parent_id, lft, rgt, depth, name/title
     *
     * @param null $lang
     * @param null $parentId
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function reorder($parentId = null, $lang = null)
    {
        $this->xPanel->hasAccessOrFail('reorder');

        // Get given lang if 'parent_entity' doesn't exists
        if (! $this->xPanel->hasParentEntity()) {
            $lang = $parentId;
        }

        // If lang is not set, get the default language
        if (empty($lang)) {
            $lang = \Lang::locale();
        }

        // Get all languages (for order onglets)
        if (property_exists($this->xPanel->model, 'translatable'))
        {
            $this->data['languages'] = \App\Models\Language::where('active', 1)->get();
            $this->data['active_language'] = $lang;
        }

        // Get all results for that entity
        $this->data['entries'] = $this->xPanel->getEntries($lang);
        $this->data['parent_id'] = $this->parentId;

        $this->data['xPanel'] = $this->xPanel;
        $this->data['title'] = trans('admin::messages.reorder') . ' ' . $this->xPanel->entity_name;

        return view('admin::panel.reorder', $this->data);
    }

    /**
     * Save the new order, using the Nested Set pattern.
     *
     * Database columns needed: id, parent_id, lft, rgt, depth, name/title
     *
     * @return bool|string
     */
    public function saveReorder()
    {
        // if reorder_table_permission is false, abort
        $this->xPanel->hasAccessOrFail('reorder');

        $model = $this->xPanel->model;
        $count = 0;
        $all_entries = \Request::input('tree');

        if (count($all_entries)) {
            foreach ($all_entries as $key => $entry) {
                if ($entry['item_id'] != '' && $entry['item_id'] != null) {
                    $entry['parent_id'] = $this->parentId;
                    $item               = $model::find($entry['item_id']);
                    if ($item->getConnection()->getSchemaBuilder()->hasColumn($item->getTable(), 'parent_id')) {
						$item->parent_id = $entry['parent_id'];
					}
                    $item->depth        = $entry['depth'];
                    $item->lft          = $entry['left'];
                    $item->rgt          = $entry['right'];
                    $item->save();

                    $count++;
                }
            }
        }
        else
        {
            return false;
        }

        return 'success for ' . $count . " items";
    }
}
