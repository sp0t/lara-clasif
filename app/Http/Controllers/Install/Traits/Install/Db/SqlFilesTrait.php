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

namespace App\Http\Controllers\Install\Traits\Db;

use App\Helpers\DBTool;

trait SqlFilesTrait
{
	/**
	 * Import Database's Schema (Migration)
	 *
	 * @param \PDO $pdo
	 * @param $tablesPrefix
	 * @return bool
	 */
	protected function importSchemaSql(\PDO $pdo, $tablesPrefix)
	{
		// Default Schema SQL file
		$filename = 'database/schema.sql';
		$filePath = storage_path($filename);
		
		// Import the SQL file
		$res = DBTool::importSqlFile($pdo, $filePath, $tablesPrefix);
		if ($res === false) {
			dd('ERROR');
		}
		
		return $res;
	}
	
	/**
	 * Import Database's Required Data (Seeding)
	 *
	 * @param \PDO $pdo
	 * @param $tablesPrefix
	 * @return bool
	 */
	protected function importDataSql(\PDO $pdo, $tablesPrefix)
	{
		// Default Required Data SQL file
		$filename = 'database/data.sql';
		$filePath = storage_path($filename);
		
		// Import the SQL file
		$res = DBTool::importSqlFile($pdo, $filePath, $tablesPrefix);
		if ($res === false) {
			dd('ERROR');
		}
		
		return $res;
	}
}
