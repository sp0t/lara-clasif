<?php
/**
 * JobClass - Job Board Web Application
 * Copyright (c) BedigitCom. All Rights Reserved
 *
 * Website: http://www.bedigit.com
 *
 * LICENSE
 * -------
 * This software is furnished under a license and may be used and copied
 * only in accordance with the terms of such license and with the inclusion
 * of the above copyright notice. If you Purchased from Codecanyon,
 * Please read the full License from here - http://codecanyon.net/licenses/standard
 */

namespace App\Http\Requests;

use App\Rules\BetweenRule;
use App\Rules\EmailRule;

class SendPostByEmailRequest extends Request
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rules = [
            'sender_email'    => ['required', 'email', new EmailRule(), 'max:100'],
            'recipient_email' => ['required', 'email', new EmailRule(), 'max:100'],
            //'message' 	  => ['required', new BetweenRule(20, 500)],
            'post'            => ['required', 'numeric'],
        ];
        
        return $rules;
    }
}
