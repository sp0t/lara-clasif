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

namespace App\Helpers\Lang\Traits;

use App\Helpers\ArrayHelper;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Str;

trait LangLinesTrait
{
	/**
	 * Fill (or Add) the missing lines in the Language files
	 *
	 * @param null $defaultLangCode
	 * @param $langCode
	 * @return bool
	 */
	public function syncLines($defaultLangCode, $langCode)
	{
		if ($this->masterLangExists()) {
			$defaultLangCode = $this->masterLangCode;
		}
		
		// Get missing lines in the Language files
		$missingEntries = $this->getMissingLines($defaultLangCode, $langCode);
		$missingEntriesFormatted = $this->getAddLinesArrayFormat($missingEntries);
		
		if (!empty($missingEntriesFormatted)) {
			
			// Add the vendor languages missing lines (If exist)
			if (isset($missingEntriesFormatted['vendor'])) {
				$packagesMissingEntries = $missingEntriesFormatted['vendor'];
				unset($missingEntriesFormatted['vendor']);
				
				foreach ($packagesMissingEntries as $namespace => $packageMissingEntries) {
					$packageMissingEntries = Arr::dot($packageMissingEntries);
					Lang::addLines($packageMissingEntries, $langCode, $namespace);
				}
			}
			
			// Add the main languages missing lines
			$mainMissingEntries = Arr::dot($missingEntriesFormatted);
			Lang::addLines($mainMissingEntries, $langCode);
			
			// Get language files grouped by file name
			$files = $this->files();
			$groups = array_keys($files);
			
			foreach ($groups as $group) {
				if (!isset($missingEntries[$group])) {
					continue;
				}
				
				// Get the new content of the file
				$newContent = Lang::get($group, [], $langCode, false);
				
				if (isset($files[$group]) && isset($files[$group][$langCode])) {
					$filePath = $files[$group][$langCode];
					
					// Save
					$this->writeFile($filePath, $newContent);
				}
			}
		}
		
		return true;
	}
	
	/**
	 * @param array $except
	 * @return array
	 */
	public function getLocales($except = [])
	{
		$except = array_merge((array)$except, ['..', '.', 'vendor']);
		$localesScanned = array_diff(scandir($this->path), $except);
		
		$locales = [];
		if (!empty($localesScanned)) {
			foreach ($localesScanned as $folder) {
				if (is_dir($this->path . '/' . $folder)) {
					$locales[] = $folder;
				}
			}
		}
		
		return $locales;
	}
	
	// PRIVATE METHODS
	
	/**
	 * Extract the vendor missing entries
	 *
	 * @param $array
	 * @return array
	 */
	private function getAddLinesArrayFormat($array)
	{
		$newArray = [];
		foreach ($array as $group => $lines) {
			if (Str::contains($group, '::')) {
				list($namespace, $groupName) = explode('::', $group, 2);
				$newArray['vendor'][$namespace][$groupName] = $lines;
			} else {
				$newArray[$group] = $lines;
			}
		}
		
		return $newArray;
	}
	
	/**
	 * Get missing lines in the Language files
	 *
	 * @param $defaultLangCode
	 * @param $langCode
	 * @return array
	 */
	private function getMissingLines($defaultLangCode, $langCode)
	{
		// Get language files grouped by file name
		$files = $this->files();
		
		// Create language files if does not exist
		$files = $this->fillMissingFilePaths($defaultLangCode, $files);
		
		// Get language groups
		$groups = array_keys($files);
		
		// Get the master language entries
		$masterLangEntries = [];
		foreach ($groups as $group) {
			$masterLangEntries[$group] = Lang::get($group, [], $defaultLangCode, false);
		}
		
		// Get the Current Language entries
		$langEntries = [];
		foreach ($groups as $group) {
			$langEntries[$group] = Lang::get($group, [], $langCode, false);
		}
		
		// Get the Current Language missing entries
		$missingEntries = ArrayHelper::diffAssoc($masterLangEntries, $langEntries);
		
		return $missingEntries;
	}
	
	/**
	 * Array of language files grouped by file name.
	 *
	 * ex: ['user' => ['en' => 'user.php', 'nl' => 'user.php']]
	 *
	 * @return mixed
	 */
	private function files()
	{
		$files = File::allFiles($this->path);
		
		$files = collect($files)->filter(function ($file) {
			return File::extension($file) == 'php';
		});
		
		// Grouped by file name
		$filesByFile = $files->groupBy(function ($file) {
			$fileName = $file->getBasename('.' . $file->getExtension());
			
			if (Str::contains($file->getPath(), 'vendor')) {
				$fileName = str_replace('.php', '', $file->getFileName());
				
				$packageName = basename(dirname($file->getPath()));
				
				return "{$packageName}::{$fileName}";
			} else {
				return $fileName;
			}
		})->map(function ($files) {
			return $files->keyBy(function ($file) {
				return basename($file->getPath());
			})->map(function ($file) {
				return $file->getRealPath();
			});
		});
		
		return $filesByFile->toArray();
	}
	
	/**
	 * Fill missing file paths
	 *
	 * @param $defaultLangCode
	 * @param $filesByFile
	 * @return mixed
	 */
	private function fillMissingFilePaths($defaultLangCode, $filesByFile)
	{
		$locales = $this->getLocales();
		
		foreach ($filesByFile as $group => $item) {
			foreach ($locales as $locale) {
				if (!isset($item[$locale]) && isset($item[$defaultLangCode])) {
					$missingFilePath = str_replace('/'.$defaultLangCode.'/', '/' . $locale . '/', $item[$defaultLangCode]);
					$filesByFile[$group][$locale] = $missingFilePath;
					
					$this->writeFile($missingFilePath, []);
				}
			}
		}
		
		return $filesByFile;
	}
	
	/**
	 * Write a language file from array.
	 *
	 * @param $filePath
	 * @param array $translations
	 */
	public function writeFile($filePath, array $translations)
	{
		if (!File::exists($directory = dirname($filePath))) {
			mkdir($directory, 0777, true);
		}
		
		$content = "<?php \n\nreturn [";
		
		if (!empty($translations)) {
			$content .= $this->stringLineMaker($translations);
			$content .= "\n";
		}
		
		$content .= "];\n";
		
		File::put($filePath, $content);
	}
	
	/**
	 * Write the lines of the inner array of the language file.
	 *
	 * @param $array
	 * @param string $prepend
	 * @return string
	 */
	private function stringLineMaker($array, $prepend = '')
	{
		$output = '';
		
		foreach ($array as $key => $value) {
			$key = str_replace('\"', '"', addslashes($key));
			
			if (is_array($value)) {
				$value = $this->stringLineMaker($value, $prepend . '    ');
				
				$output .= "\n{$prepend}    '{$key}' => [{$value}\n{$prepend}    ],";
			} else {
				$value = str_replace('\"', '"', addslashes($value));
				
				$output .= "\n{$prepend}    '{$key}' => '{$value}',";
			}
		}
		
		return $output;
	}
	
	/**
	 * Get the content in the given file path.
	 *
	 * @param $filePath
	 * @param bool $createIfNotExists
	 * @return array
	 */
	public function getFileContent($filePath, $createIfNotExists = false)
	{
		if ($createIfNotExists && !File::exists($filePath)) {
			$this->writeFile($filePath, []);
			return [];
		}
		
		try {
			return (array)include $filePath;
		} catch (\ErrorException $e) {
			dd('File not found: ' . $filePath);
		}
	}
}
