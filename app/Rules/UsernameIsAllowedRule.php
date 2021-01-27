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

namespace App\Rules;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Routing\Router;
use Illuminate\Config\Repository;
use Illuminate\Contracts\Validation\Rule;

class UsernameIsAllowedRule implements Rule
{
	/**
	 * The router instance used to check the username against application routes.
	 *
	 * @var \Illuminate\Routing\Router
	 */
	private $router;
	
	/**
	 * The filesystem class used to retrieve public files and directories.
	 *
	 * @var \Illuminate\Filesystem\Filesystem
	 */
	private $files;
	
	/**
	 * The config repository used to retrieve reserved usernames.
	 *
	 * @var \Illuminate\Config\Repository
	 */
	private $config;
	
	/**
	 * Create a new allowed username validator instance.
	 *
	 * @param \Illuminate\Routing\Router $router
	 * @param \Illuminate\Filesystem\Filesystem $files
	 * @param \Illuminate\Config\Repository $config
	 */
	public function __construct(Router $router, Filesystem $files, Repository $config)
	{
		$this->config = $config;
		$this->router = $router;
		$this->files = $files;
	}
	
	/**
	 * Determine if the validation rule passes.
	 *
	 * @param string $attribute
	 * @param mixed $value
	 * @return bool
	 */
	public function passes($attribute, $value)
	{
		$value = trim(strtolower($value));
		
		if ($this->isReservedUsername($value)) {
			return false;
		}
		
		if ($this->matchesRoute($value)) {
			return false;
		}
		
		if ($this->matchesPublicFileOrDirectory($value)) {
			return false;
		}
		
		return true;
	}
	
	/**
	 * Get the validation error message.
	 *
	 * @return string
	 */
	public function message()
	{
		return trans('validation.username_is_allowed_rule');
	}
	
	/* PRIVATES */
	
	/**
	 * Determine whether the given username is in the reserved usernames list.
	 *
	 * @param string $value
	 * @return bool
	 */
	private function isReservedUsername($value)
	{
		$reservedUsernames = $this->config->get('larapen.core.reservedUsernames');
		
		if (is_string($reservedUsernames)) {
			$reservedUsernames = preg_split('/[,;\s]+/ui', $reservedUsernames);
			$reservedUsernames = array_map('trim', $reservedUsernames);
		}
		
		// Pass if unable to check
		if (!is_array($reservedUsernames)) {
			return false;
		}
		
		return in_array($value, $reservedUsernames);
	}
	
	/**
	 * Determine whether the given username matches an application route.
	 *
	 * @param string $value
	 * @return bool
	 */
	private function matchesRoute($value)
	{
		foreach ($this->router->getRoutes() as $route) {
			if (strtolower($route->uri) === $value) {
				return true;
			}
		}
		
		return false;
	}
	
	/**
	 * Determine whether the given username matches a public file or directory.
	 *
	 * @param string $value
	 * @return bool
	 */
	private function matchesPublicFileOrDirectory($value)
	{
		foreach ($this->files->glob(public_path() . DIRECTORY_SEPARATOR . '*') as $path) {
			if (strtolower(basename($path)) === $value) {
				return true;
			}
		}
		
		return false;
	}
}
