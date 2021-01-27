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

namespace App\Helpers;


class DBTool
{
	/**
	 * Get PDO Connexion
	 *
	 * @param array $database
	 * @return \PDO
	 */
	public static function getPDOConnexion($database = [])
	{
		$error = null;
		
		// Retrieve Database Parameters from the /.env file,
		// If they are not set during the function call.
		if (empty($database)) {
			$database = DBTool::getDatabaseConnectionInfo();
		}
		
		// Database Parameters
		$driver = isset($database['driver']) ? $database['driver'] : 'mysql';
		$host = isset($database['host']) ? $database['host'] : '';
		$port = isset($database['port']) ? $database['port'] : '';
		$username = isset($database['username']) ? $database['username'] : '';
		$password = isset($database['password']) ? $database['password'] : '';
		$database = isset($database['database']) ? $database['database'] : '';
		$charset = isset($database['charset']) ? $database['charset'] : 'utf8';
		$socket = isset($database['socket']) ? $database['socket'] : '';
		$options = isset($database['options']) ? $database['options'] : [
			\PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_OBJ,
			\PDO::ATTR_ERRMODE            => \PDO::ERRMODE_EXCEPTION,
			\PDO::ATTR_EMULATE_PREPARES   => true,
			\PDO::ATTR_CURSOR             => \PDO::CURSOR_FWDONLY,
		];
		
		try {
			// Get the Connexion's DSN
			if (empty($socket)) {
				$dsn = $driver . ':host=' . $host . ';port=' . $port . ';dbname=' . $database . ';charset=' . $charset;
			} else {
				$dsn = $driver . ':unix_socket=' . $database['socket'] . ';dbname=' . $database . ';charset=' . $charset;
			}
			// Connect to the Database Server
			return new \PDO($dsn, $username, $password, $options);
			
		} catch (\PDOException $e) {
			$error = "<pre><strong>ERROR:</strong> Can't connect to the database server. " . $e->getMessage() . "</pre>";
		} catch (\Exception $e) {
			$error = "<pre><strong>ERROR:</strong> The database connection failed. " . $e->getMessage() . "</pre>";
		}
		
		die($error);
	}
	
	/**
	 * Database Connection Info
	 *
	 * @return mixed
	 */
	public static function getDatabaseConnectionInfo()
	{
		$config = DBTool::getLaravelDatabaseConfig();
		$defaultDatabase = $config['connections'][$config['default']];
		
		// Database Parameters
		$database['driver'] = $defaultDatabase['driver'];
		$database['host'] = $defaultDatabase['host'];
		$database['port'] = (int)$defaultDatabase['port'];
		$database['socket'] = $defaultDatabase['unix_socket'];
		$database['username'] = $defaultDatabase['username'];
		$database['password'] = $defaultDatabase['password'];
		$database['database'] = $defaultDatabase['database'];
		$database['charset'] = $defaultDatabase['charset'];
		$database['options'] = [
			\PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_OBJ,
			\PDO::ATTR_ERRMODE            => \PDO::ERRMODE_EXCEPTION,
			\PDO::ATTR_EMULATE_PREPARES   => true,
			\PDO::ATTR_CURSOR             => \PDO::CURSOR_FWDONLY,
		];
		
		return $database;
	}
	
	/**
	 * @return array
	 */
	public static function getLaravelDatabaseConfig()
	{
		return (array)include realpath(__DIR__ . '/../../config/database.php');
	}
	
	/**
	 * Get full table name by adding the DB prefix
	 *
	 * @param $name
	 * @return string
	 */
	public static function rawTable($name)
	{
		$config = DBTool::getLaravelDatabaseConfig();
		$defaultDatabase = $config['connections'][$config['default']];
		$databasePrefix = $defaultDatabase['prefix'];
		
		return $databasePrefix . $name;
	}
	
	/**
	 * Close PDO Connexion
	 *
	 * @param $pdo
	 */
	public static function closePDOConnexion(&$pdo)
	{
		$pdo = null;
	}
	
	/**
	 * Get full table name by adding the DB prefix
	 *
	 * @param $name
	 * @return string
	 */
	public static function table($name)
	{
		return \DB::getTablePrefix() . $name;
	}
	
	/**
	 * Quote a value with astrophe to inject to an SQL statement
	 *
	 * @param $value
	 * @return mixed
	 */
	public static function quote($value)
	{
		return \DB::getPdo()->quote($value);
	}
	
	/**
	 * Get the MySQL version
	 *
	 * @param null $pdo
	 * @return int|mixed
	 */
	public static function getMySqlVersion($pdo = null)
	{
		$version = 0;
		
		try {
			if (empty($pdo)) {
				$pdo = \DB::connection()->getPdo();
			}
			
			if ($pdo instanceof \PDO) {
				$version = $pdo->query('select version()')->fetchColumn();
				
				$tmp = [];
				preg_match('/^[0-9\.]+/', $version, $tmp);
				if (isset($tmp[0])) {
					$version = $tmp[0];
				}
			}
		} catch (\Exception $e) {}
		
		return $version;
	}
	
	/**
	 * Check if the entered value is the MySQL minimal version
	 *
	 * @param $min
	 * @return bool
	 */
	public static function isMySqlMinVersion($min)
	{
		// Get the MySQL version
		$version = DBTool::getMySqlVersion();
		
		return (version_compare($version, $min) >= 0);
	}
	
	/**
	 * Import SQL File
	 *
	 * @param $pdo
	 * @param $sqlFile
	 * @param null $tablePrefix
	 * @param null $InFilePath
	 * @return bool
	 */
	public static function importSqlFile($pdo, $sqlFile, $tablePrefix = null, $InFilePath = null)
	{
		try {
			
			if (!$pdo instanceof \PDO) {
				return false;
			}
			
			// Enable LOAD LOCAL INFILE
			$pdo->setAttribute(\PDO::MYSQL_ATTR_LOCAL_INFILE, true);
			
			$errorDetect = false;
			
			// Temporary variable, used to store current query
			$tmpLine = '';
			
			// Read in entire file
			$lines = file($sqlFile);
			
			// Loop through each line
			foreach ($lines as $line) {
				// Skip it if it's a comment
				if (substr($line, 0, 2) == '--' || trim($line) == '') {
					continue;
				}
				
				// Read & replace prefix
				$line = str_replace(['<<prefix>>', '<<InFilePath>>'], [$tablePrefix, $InFilePath], $line);
				
				// Add this line to the current segment
				$tmpLine .= $line;
				
				// If it has a semicolon at the end, it's the end of the query
				if (substr(trim($line), -1, 1) == ';') {
					try {
						// Perform the Query
						$pdo->exec($tmpLine);
					} catch (\PDOException $e) {
						echo "<br><pre>Error performing Query: '<strong>" . $tmpLine . "</strong>': " . $e->getMessage() . "</pre>\n";
						$errorDetect = true;
					}
					
					// Reset temp variable to empty
					$tmpLine = '';
				}
			}
			
			// Check if error is detected
			if ($errorDetect) {
				return false;
			}
			
		} catch (\Exception $e) {
			echo "<br><pre>Exception => " . $e->getMessage() . "</pre>\n";
			return false;
		}
		
		return true;
	}
	
	/**
	 * Perform MySQL Database Backup
	 *
	 * @param $pdo
	 * @param string $tables
	 * @param string $filePath
	 * @return bool
	 */
	public static function backupDatabaseTables($pdo, $tables = '*', $filePath = '/')
	{
		try {
			
			if (!$pdo instanceof \PDO) {
				return false;
			}
			
			// Get all of the tables
			if ($tables == '*') {
				$tables = [];
				$query = $pdo->query('SHOW TABLES');
				while ($row = $query->fetch_row()) {
					$tables[] = $row[0];
				}
			} else {
				$tables = is_array($tables) ? $tables : explode(',', $tables);
			}
			
			if (empty($tables)) {
				return false;
			}
			
			$out = '';
			
			// Loop through the tables
			foreach ($tables as $table) {
				$query = $pdo->query('SELECT * FROM ' . $table);
				$numColumns = $query->field_count;
				
				// Add DROP TABLE statement
				$out .= 'DROP TABLE ' . $table . ';' . "\n\n";
				
				// Add CREATE TABLE statement
				$query2 = $pdo->query('SHOW CREATE TABLE ' . $table);
				$row2 = $query2->fetch_row();
				$out .= $row2[1] . ';' . "\n\n";
				
				// Add INSERT INTO statements
				for ($i = 0; $i < $numColumns; $i++) {
					while ($row = $query->fetch_row()) {
						$out .= "INSERT INTO $table VALUES(";
						for ($j = 0; $j < $numColumns; $j++) {
							$row[$j] = addslashes($row[$j]);
							$row[$j] = preg_replace("/\n/us", "\\n", $row[$j]);
							if (isset($row[$j])) {
								$out .= '"' . $row[$j] . '"';
							} else {
								$out .= '""';
							}
							if ($j < ($numColumns - 1)) {
								$out .= ',';
							}
						}
						$out .= ');' . "\n";
					}
				}
				$out .= "\n\n\n";
			}
			
			// Save file
			\File::put($filePath, $out);
			
		} catch (\Exception $e) {
			echo "<br><pre>Exception => " . $e->getMessage() . "</pre>\n";
			return false;
		}
		
		return true;
	}
}
