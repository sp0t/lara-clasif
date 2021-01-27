<?php

namespace Larapen\Admin\app\Http\Controllers\Features;

use Illuminate\Support\Arr;

trait TranslateItem
{
    /**
     * Duplicate an existing item into another language and open it for editing.
     * Support for parent entity added
     *
     * @param $parentId
     * @param null $id
     * @param null $lang
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function translateItem($parentId, $id = null, $lang = null)
    {
        if (! $this->xPanel->hasParentEntity()) {
            $lang = $id;
            $id = $parentId;
        }

        $model = $this->xPanel->model;
        $this->data['entry'] = $model::find($id);
        // check if there isn't a translation already
        $existing_translation = $this->data['entry']->translation($lang);

        if ($existing_translation)
        {
            $new_entry = $existing_translation;
        }
        else
        {
            // get the info for that entry
            $new_entry_attributes = $this->data['entry']->getAttributes();
            $new_entry_attributes['translation_lang'] = $lang;
            $new_entry_attributes['translation_of'] = $id;
            $new_entry_attributes = Arr::except($new_entry_attributes, 'id');

            $new_entry = $model::create($new_entry_attributes);

            if (empty($new_entry)) {
                $this->data['entry'] = $model::find($id);
                $new_entry = $this->data['entry']->translation($lang);
            }
        }

        // redirect to the edit form for that translation
        return redirect(str_replace($id . '/', $new_entry->id . '/', str_replace('translate/' . $lang, 'edit', \Request::url())));
    }
    
    /**
     * Get translated array from entries collection
     *
     * @param $entries
     * @param null $currentEntryId
     * @return array
     */
    public function getTranslatedArray($entries, $currentEntryId = null)
    {
        if ($entries->count() <= 0) {
            return [];
        }
        
        $tab = [];
        foreach ($entries as $entry) {
            if (empty($currentEntryId)) {
                $tab[$entry->tid] = $entry->name;
            } else {
                if ($entry->tid != $currentEntryId) {
                    $tab[$entry->tid] = $entry->name;
                }
            }
        }
        
        return $tab;
    }
}
