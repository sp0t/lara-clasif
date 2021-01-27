<?php

namespace Larapen\Admin\app\Http\Controllers\Features;

trait ShowDetailsRow
{
    /**
     * Used with AJAX in the list view (datatables) to show extra information about that row that didn't fit in the table.
     * It defaults to showing all connected translations and their CRUD buttons.
     *
     * It's enabled by:
     * - setting the $crud['details_row'] variable to true;
     * - adding the details route for the entity; ex: Route::get('page/{id}/details', 'PageCrudController@showDetailsRow');
     *
     * @param $id (Parent ID)
     * @param null $childId
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function showDetailsRow($id, $childId = null)
    {
        if (!empty($childId)) {
            $id = $childId;
        }

        // Get the info for that entry
        $model = $this->xPanel->model;
        $this->data['entry'] = $model::find($id);
        //$this->data['entry']->addFakes($this->getFakeColumnsAsArray());
        $this->data['original_entry'] = $this->data['entry'];
        $this->data['xPanel'] = $this->xPanel;

        if (property_exists($model, 'translatable')) {
            $this->data['translations'] = $this->data['entry']->translations();

            // create a list of languages the item is not translated in
            $this->data['languages'] = \App\Models\Language::where('active', 1)->get();
            $this->data['languages_already_translated_in'] = $this->data['entry']->translationLanguages();
            $this->data['languages_to_translate_in'] = $this->data['languages']->diff($this->data['languages_already_translated_in']);
            $this->data['languages_to_translate_in'] = $this->data['languages_to_translate_in']->reject(function ($item) {
                return $item->abbr == \Lang::locale();
            });
        }

        return view('admin::panel.details_row', $this->data);
    }
}
