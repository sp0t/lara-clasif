<?php

namespace App\Http\Requests\Admin;

class SettingRequest extends Request
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            // 'key'    => ['required', 'min:3', 'max:255'],
            // 'name'   => ['required', 'min:3', 'max:255'],
            // 'field'  => ['required'],
        ];
    }
}
