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

namespace App\Http\Controllers\Install\Traits\Install;

use App\Helpers\DBTool;
use App\Http\Controllers\Install\Traits\Db\MigrationsTrait;
use App\Http\Controllers\Install\Traits\Db\SqlFilesTrait;
use App\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Jenssegers\Date\Date;

trait DbTrait
{
	use SqlFilesTrait, MigrationsTrait;
	
	/**
	 * STEP 4 - Database Import Submission
	 *
	 * @param \Illuminate\Http\Request $request
	 * @param $siteInfo
	 * @param $database
	 */
	private function submitDatabaseImport(Request $request, $siteInfo, $database)
	{
		// Get PDO connexion
		$pdo = DBTool::getPDOConnexion($database);
		
		// Check if database is not empty
		$rules      = [];
		$tableNames = $this->getDatabaseTables($pdo, $database);
		if (is_array($tableNames) && count($tableNames) > 0) {
			// 1. Drop all old tables
			$this->dropExistingTables($pdo, $tableNames);
			
			// 2. Check if all table are dropped (Check if database's tables still exist)
			$tablesExist = false;
			$tableNames  = $this->getDatabaseTables($pdo, $database);
			if (is_array($tableNames) && count($tableNames) > 0) {
				$tablesExist = true;
			}
			
			$rules = [];
			if ($tablesExist) {
				$rules['can_not_empty_database'] = 'required';
			}
			
			// 3. Validation
			$this->validate($request, $rules);
		}
		
		// 4. 1. Import database schema (Migration)
		$this->importSchemaSql($pdo, $database['prefix']);
		
		// 4. 2. Import required data (Seeding)
		$this->importDataSql($pdo, $database['prefix']);
		
		// 4. 3. Import Geonames Default country database
		$this->importGeonamesSql($pdo, $database['prefix'], $siteInfo['default_country']);
		
		// 4. 4. Update seeded data (with customer info)
		$this->updateSeededData($pdo, $database['prefix'], $siteInfo);
		
		// Close PDO connexion
		DBTool::closePDOConnexion($pdo);
	}
	
	/**
	 * Get all the database's tables
	 *
	 * @param \PDO $pdo
	 * @param $database
	 * @return array
	 */
	private function getDatabaseTables(\PDO $pdo, $database)
	{
		$tables = [];
		
		$filter = !empty($database['prefix']) ? ' AND table_name LIKE "' . $database['prefix'] . '%"' : '';
		$sql    = 'SELECT GROUP_CONCAT(table_name) AS table_names
				FROM information_schema.tables
                WHERE table_schema = "' . $database['database'] . '"' . $filter;
		$query  = $pdo->query($sql);
		$obj    = $query->fetch();
		
		if (isset($obj->table_names)) {
			$tables = array_merge($tables, explode(',', $obj->table_names));
		}
		
		return $tables;
	}
	
	/**
	 * Drop All Existing Tables
	 *
	 * @param \PDO $pdo
	 * @param $tableNames
	 */
	private function dropExistingTables(\PDO $pdo, $tableNames)
	{
		if (is_array($tableNames) && count($tableNames) > 0) {
			// Try 4 times
			$try = 5;
			while ($try > 0) {
				try {
					// Extend query max setting
					$pdo->exec('FLUSH TABLES;');
					$pdo->exec('SET group_concat_max_len = 9999999;');
					
					// Drop all tables
					$pdo->exec('SET foreign_key_checks = 0;');
					foreach ($tableNames as $tableName) {
						if ($this->tableExists($pdo, $tableName)) {
							$pdo->exec('DROP TABLE ' . $tableName . ';');
						}
					}
					$pdo->exec('SET foreign_key_checks = 1;');
					
					$pdo->exec('FLUSH TABLES;');
					
					$try--;
				} catch (\Exception $e) {
					dd($e->getMessage());
				}
			}
		}
	}
	
	/**
	 * Import the Default Country Data from the Geonames SQL Files
	 *
	 * @param \PDO $pdo
	 * @param $tablesPrefix
	 * @param $defaultCountryCode
	 * @return bool
	 */
	private function importGeonamesSql(\PDO $pdo, $tablesPrefix, $defaultCountryCode)
	{
		// Default Country SQL file
		$filename = 'database/geonames/countries/' . strtolower($defaultCountryCode) . '.sql';
		$filePath = storage_path($filename);
		
		// Import the SQL file
		$res = DBTool::importSqlFile($pdo, $filePath, $tablesPrefix);
		if ($res === false) {
			dd('ERROR');
		}
		
		return $res;
	}
	
	/**
	 * Update the seeded data (with the Site Information)
	 *
	 * @param \PDO $pdo
	 * @param $tablesPrefix
	 * @param $siteInfo
	 */
	private function updateSeededData(\PDO $pdo, $tablesPrefix, $siteInfo)
	{
		// Default date
		$date = Date::now();
		
		try {
			
			// USERS - Insert default superuser
			$pdo->exec('DELETE FROM `' . $tablesPrefix . 'users` WHERE 1');
			$sql   = 'INSERT INTO `' . $tablesPrefix . 'users`
				(`id`, `country_code`, `user_type_id`, `gender_id`, `name`, `about`, `email`, `password`, `is_admin`, `verified_email`, `verified_phone`)
				VALUES (1, :countryCode, 1, 1, :name, "Administrator", :email, :password, 1, 1, 1);';
			$query = $pdo->prepare($sql);
			$res   = $query->execute([
				':countryCode' => $siteInfo['default_country'],
				':name'        => $siteInfo['name'],
				':email'       => $siteInfo['email'],
				':password'    => Hash::make($siteInfo['password']),
			]);
			
			// Setup ACL system
			$this->setupAclSystem();
			
			// COUNTRIES - Activate default country
			$sql   = 'UPDATE `' . $tablesPrefix . 'countries` SET `active`=1 WHERE `code`=:countryCode';
			$query = $pdo->prepare($sql);
			$res   = $query->execute([
				':countryCode' => $siteInfo['default_country'],
			]);
			
			// SETTINGS - Update settings
			// App
			$appSettings = [
				'purchase_code' => isset($siteInfo['purchase_code']) ? $siteInfo['purchase_code'] : '',
				'name'          => isset($siteInfo['site_name']) ? $siteInfo['site_name'] : '',
				'slogan'        => isset($siteInfo['site_slogan']) ? $siteInfo['site_slogan'] : '',
				'email'         => isset($siteInfo['email']) ? $siteInfo['email'] : '',
			];
			$sql         = 'UPDATE `' . $tablesPrefix . 'settings` SET `value`=:appSettings WHERE `key`="app"';
			$query       = $pdo->prepare($sql);
			$res         = $query->execute([
				':appSettings' => json_encode($appSettings),
			]);
			
			// Geo Location
			$geoLocationSettings = [
				'default_country_code' => isset($siteInfo['default_country']) ? $siteInfo['default_country'] : '',
			];
			
			$sql   = 'UPDATE `' . $tablesPrefix . 'settings` SET `value`=:geoLocationSettings WHERE `key`="geo_location"';
			$query = $pdo->prepare($sql);
			$res   = $query->execute([
				':geoLocationSettings' => json_encode($geoLocationSettings),
			]);
			
			// Mail
			$mailSettings = [
				'email_sender' => isset($siteInfo['email']) ? $siteInfo['email'] : '',
				'driver'       => isset($siteInfo['mail_driver']) ? $siteInfo['mail_driver'] : '',
			];
			if (isset($siteInfo['mail_driver'])) {
				if ($siteInfo['mail_driver'] == 'sendmail') {
					$mailSettings['sendmail_path'] = isset($siteInfo['sendmail_path']) ? $siteInfo['sendmail_path'] : '';
				}
				if (in_array($siteInfo['mail_driver'], ['smtp', 'mailgun', 'mandrill', 'ses', 'sparkpost'])) {
					$mailSettings['host']       = isset($siteInfo['smtp_hostname']) ? $siteInfo['smtp_hostname'] : '';
					$mailSettings['port']       = isset($siteInfo['smtp_port']) ? $siteInfo['smtp_port'] : '';
					$mailSettings['encryption'] = isset($siteInfo['smtp_encryption']) ? $siteInfo['smtp_encryption'] : '';
					$mailSettings['username']   = isset($siteInfo['smtp_username']) ? $siteInfo['smtp_username'] : '';
					$mailSettings['password']   = isset($siteInfo['smtp_password']) ? $siteInfo['smtp_password'] : '';
				}
				if ($siteInfo['mail_driver'] == 'mailgun') {
					$mailSettings['mailgun_domain'] = isset($siteInfo['mailgun_domain']) ? $siteInfo['mailgun_domain'] : '';
					$mailSettings['mailgun_secret'] = isset($siteInfo['mailgun_secret']) ? $siteInfo['mailgun_secret'] : '';
				}
				if ($siteInfo['mail_driver'] == 'mandrill') {
					$mailSettings['mandrill_secret'] = isset($siteInfo['mandrill_secret']) ? $siteInfo['mandrill_secret'] : '';
				}
				if ($siteInfo['mail_driver'] == 'ses') {
					$mailSettings['ses_key']    = isset($siteInfo['ses_key']) ? $siteInfo['ses_key'] : '';
					$mailSettings['ses_secret'] = isset($siteInfo['ses_secret']) ? $siteInfo['ses_secret'] : '';
					$mailSettings['ses_region'] = isset($siteInfo['ses_region']) ? $siteInfo['ses_region'] : '';
				}
				if ($siteInfo['mail_driver'] == 'sparkpost') {
					$mailSettings['sparkpost_secret'] = isset($siteInfo['sparkpost_secret']) ? $siteInfo['sparkpost_secret'] : '';
				}
			}
			$sql   = 'UPDATE `' . $tablesPrefix . 'settings` SET `value`=:mailSettings WHERE `key`="mail"';
			$query = $pdo->prepare($sql);
			$res   = $query->execute([
				':mailSettings' => json_encode($mailSettings),
			]);
			
		} catch (\PDOException $e) {
			dd($e->getMessage());
		} catch (\Exception $e) {
			dd($e->getMessage());
		}
	}
	
	/**
	 * Check if a table exists in the current database.
	 *
	 * @param \PDO $pdo
	 * @param $table
	 * @return bool
	 */
	private function tableExists(\PDO $pdo, $table)
	{
		// Try a select statement against the table
		// Run it in try/catch in case PDO is in ERRMODE_EXCEPTION.
		try {
			$result = $pdo->query('SELECT 1 FROM ' . $table . ' LIMIT 1');
		} catch (\Exception $e) {
			// We got an exception == table not found
			return false;
		}
		
		// Result is either boolean FALSE (no table found) or PDOStatement Object (table found)
		return $result !== false;
	}
	
	/**
	 * Setup ACL system
	 */
	private function setupAclSystem()
	{
		// Check & Fix the default Permissions
		if (!Permission::checkDefaultPermissions()) {
			Permission::resetDefaultPermissions();
		}
	}
}
