<?php

namespace LarapenIlluminate\Translation;

use App\Helpers\ArrayHelper;
use Illuminate\Contracts\Translation\Loader;

class Translator extends \Illuminate\Translation\Translator
{
	/**
	 * Create a new translator instance.
	 *
	 * @param  \Illuminate\Contracts\Translation\Loader $loader
	 * @param  string $locale
	 * @return void
	 */
	public function __construct(Loader $loader, $locale)
	{
		parent::__construct($loader, $locale);
	}
	
	/**
	 * Add translation lines to the given locale.
	 *
	 * @param  array $lines
	 * @param  string $locale
	 * @param  string $namespace
	 * @return void
	 */
	public function addLines(array $lines, $locale, $namespace = '*')
	{
		foreach ($lines as $key => $value) {
			list($group, $item) = explode('.', $key, 2);
			
			/*
			 * To accept strings with dots (like: 'Foo. Bar' or 'Foo.') as language files lines keys,
			 * 1. Limit the explode('.', KEY) line in Array::set() to 4.
			 * 2. Or replace the explode('.', KEY) line in Array::set() by a regex to split the KEY by dot.
			 *    NOTE: The dot will require character in left & right: (eg. 'foo.bar' instead of 'foo. bar' or 'foo.')
			 */
			ArrayHelper::setWithLimit($this->loaded, "$namespace.$group.$locale.$item", $value, 4);
		}
	}
}
