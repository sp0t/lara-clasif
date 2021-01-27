<?php
/**
 * LaraClassified - Classified Ads Web Application
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

namespace App\Http\Controllers\Install\Traits\Update;

use Jackiedo\DotenvEditor\Facades\DotenvEditor;

trait EnvTrait
{
	/**
	 * Update the current version to last version
	 *
	 * @param $last
	 */
	private function setCurrentVersion($last)
	{
		if (!DotenvEditor::keyExists('APP_VERSION')) {
			DotenvEditor::addEmpty();
		}
		DotenvEditor::setKey('APP_VERSION', $last);
		DotenvEditor::save();
	}
}
