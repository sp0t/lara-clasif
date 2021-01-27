<?php

return [
	
	/*
	|--------------------------------------------------------------------------
	| Validation Language Lines
	|--------------------------------------------------------------------------
	|
	| The following language lines contain the default error messages used by
	| the validator class. Some of these rules have multiple versions such
	| as the size rules. Feel free to tweak each of these messages here.
	|
	*/
	
	'accepted'              => 'The :attribute must be accepted.',
	'active_url'            => 'The :attribute is not a valid URL.',
	'after'                 => 'The :attribute must be a date after :date.',
	'after_or_equal'        => 'The :attribute must be a date after or equal to :date.',
	'alpha'                 => 'The :attribute may only contain letters.',
	'alpha_dash'            => 'The :attribute may only contain letters, numbers, dashes and underscores.',
	'alpha_num'             => 'The :attribute may only contain letters and numbers.',
	'array'                 => 'The :attribute must be an array.',
	'before'                => 'The :attribute must be a date before :date.',
	'before_or_equal'       => 'The :attribute must be a date before or equal to :date.',
	'between'               => [
		'numeric' => 'The :attribute must be between :min and :max.',
		'file'    => 'The :attribute must be between :min and :max kilobytes.',
		'string'  => 'The :attribute must be between :min and :max characters.',
		'array'   => 'The :attribute must have between :min and :max items.',
	],
	'boolean'               => 'The :attribute field must be true or false.',
	'confirmed'             => 'The :attribute confirmation does not match.',
	'date'                  => 'The :attribute is not a valid date.',
	'date_equals'           => 'The :attribute must be a date equal to :date.',
	'date_format'           => 'The :attribute does not match the format :format.',
	'different'             => 'The :attribute and :other must be different.',
	'digits'                => 'The :attribute must be :digits digits.',
	'digits_between'        => 'The :attribute must be between :min and :max digits.',
	'dimensions'            => 'The :attribute has invalid image dimensions.',
	'distinct'              => 'The :attribute field has a duplicate value.',
	'email'                 => 'The :attribute must be a valid email address.',
	'exists'                => 'The selected :attribute is invalid.',
	'file'                  => 'The :attribute must be a file.',
	'filled'                => 'The :attribute field must have a value.',
	'image'                 => 'The :attribute must be an image.',
	'in'                    => 'The selected :attribute is invalid.',
	'in_array'              => 'The :attribute field does not exist in :other.',
	'integer'               => 'The :attribute must be an integer.',
	'ip'                    => 'The :attribute must be a valid IP address.',
	'ipv4'                  => 'The :attribute must be a valid IPv4 address.',
	'ipv6'                  => 'The :attribute must be a valid IPv6 address.',
	'json'                  => 'The :attribute must be a valid JSON string.',
	'max'                   => [
		'numeric' => 'The :attribute may not be greater than :max.',
		'file'    => 'The :attribute may not be greater than :max kilobytes.',
		'string'  => 'The :attribute may not be greater than :max characters.',
		'array'   => 'The :attribute may not have more than :max items.',
	],
	'mimes'                 => 'The :attribute must be a file of type: :values.',
	'mimetypes'             => 'The :attribute must be a file of type: :values.',
	'min'                   => [
		'numeric' => 'The :attribute must be at least :min.',
		'file'    => 'The :attribute must be at least :min kilobytes.',
		'string'  => 'The :attribute must be at least :min characters.',
		'array'   => 'The :attribute must have at least :min items.',
	],
	'not_in'                => 'The selected :attribute is invalid.',
	'not_regex'             => 'The :attribute format is invalid.',
	'numeric'               => 'The :attribute must be a number.',
	'present'               => 'The :attribute field must be present.',
	'regex'                 => 'The :attribute format is invalid.',
	'required'              => 'The :attribute field is required.',
	'required_if'           => 'The :attribute field is required when :other is :value.',
	'required_unless'       => 'The :attribute field is required unless :other is in :values.',
	'required_with'         => 'The :attribute field is required when :values is present.',
	'required_with_all'     => 'The :attribute field is required when :values is present.',
	'required_without'      => 'The :attribute field is required when :values is not present.',
	'required_without_all'  => 'The :attribute field is required when none of :values are present.',
	'same'                  => 'The :attribute and :other must match.',
	'size'                  => [
		'numeric' => 'The :attribute must be :size.',
		'file'    => 'The :attribute must be :size kilobytes.',
		'string'  => 'The :attribute must be :size characters.',
		'array'   => 'The :attribute must contain :size items.',
	],
	'starts_with'           => 'The :attribute must start with one of the following: :values',
	'string'                => 'The :attribute must be a string.',
	'timezone'              => 'The :attribute must be a valid zone.',
	'unique'                => 'The :attribute has already been taken.',
	'uploaded'              => 'The :attribute failed to upload.',
	'url'                   => 'The :attribute format is invalid.',
	
	// Packages Rules
	'recaptcha'             => 'The :attribute field is not correct.',
	'phone'                 => 'The :attribute field contains an invalid number.',
	'dumbpwd'               => 'This password is just too common. Please try another!',
	'phone_number'          => 'Your phone number is not valid.',
	
	
	// Custom Rules
	'required_package_id'             => 'You have to select a premium ad option to continue.',
	'required_payment_method_id'      => 'You have to select a payment method to continue.',
	'blacklist_email_rule'            => 'This email address is blacklisted.',
	'blacklist_domain_rule'           => 'The domain of your email address is blacklisted.',
	'blacklist_ip_rule'               => 'The :attribute must be a valid IP address.',
	'blacklist_word_rule'             => 'The :attribute contains a banned words or phrases.',
	'blacklist_title_rule'            => 'The :attribute contains a banned words or phrases.',
	'between_rule'                    => 'The :attribute must be between :min and :max characters.',
	'username_is_valid_rule'          => 'The :attribute field must be an alphanumeric string.',
	'username_is_allowed_rule'        => 'The :attribute is not allowed.',
	'locale_of_language_rule'         => 'The :attribute field is not valid.',
	'locale_of_country_rule'          => 'The :attribute field is not valid.',
	'currencies_codes_are_valid_rule' => 'The :attribute field is not valid.',
	'mb_alphanumeric_rule'            => 'Please enter a valid content in the :attribute field.',
	
	
	/*
	|--------------------------------------------------------------------------
	| Custom Validation Language Lines
	|--------------------------------------------------------------------------
	|
	| Here you may specify custom validation messages for attributes using the
	| convention "attribute.rule" to name the lines. This makes it quick to
	| specify a specific custom language line for a given attribute rule.
	|
	*/
	
	'custom' => [
		
		'database_connection'      => [
			'required' => 'Can\'t connect to MySQL server',
		],
		'database_not_empty'       => [
			'required' => 'The database is not empty',
		],
		'promo_code_not_valid'     => [
			'required' => 'The promo code is not valid',
		],
		'smtp_valid'               => [
			'required' => 'Can\'t connect to SMTP server',
		],
		'yaml_parse_error'         => [
			'required' => 'Can\'t parse yaml. Please check the syntax',
		],
		'file_not_found'           => [
			'required' => 'File not found.',
		],
		'not_zip_archive'          => [
			'required' => 'The file is not a zip package.',
		],
		'zip_archive_unvalid'      => [
			'required' => 'Cannot read the package.',
		],
		'custom_criteria_empty'    => [
			'required' => 'Custom criteria cannot be empty',
		],
		'php_bin_path_invalid'     => [
			'required' => 'Invalid PHP executable. Please check again.',
		],
		'can_not_empty_database'   => [
			'required' => 'Cannot DROP certain tables, please cleanup your database manually and try again.',
		],
		'recaptcha_invalid'        => [
			'required' => 'Invalid reCAPTCHA check.',
		],
		'payment_method_not_valid' => [
			'required' => 'Something went wrong with payment method setting. Please check again.',
		],
	
	],
	
	/*
	|--------------------------------------------------------------------------
	| Custom Validation Attributes
	|--------------------------------------------------------------------------
	|
	| The following language lines are used to swap attribute place-holders
	| with something more reader friendly such as E-Mail Address instead
	| of "email". This simply helps us make messages a little cleaner.
	|
	*/
	
	'attributes' => [
		
		'gender'                => 'gender',
		'gender_id'             => 'gender',
		'name'                  => 'name',
		'first_name'            => 'first name',
		'last_name'             => 'last name',
		'user_type'             => 'user type',
		'user_type_id'          => 'user type',
		'country'               => 'country',
		'country_code'          => 'country',
		'phone'                 => 'phone',
		'address'               => 'address',
		'mobile'                => 'mobile',
		'sex'                   => 'sex',
		'year'                  => 'year',
		'month'                 => 'month',
		'day'                   => 'day',
		'hour'                  => 'hour',
		'minute'                => 'minute',
		'second'                => 'second',
		'username'              => 'username',
		'email'                 => 'email address',
		'password'              => 'password',
		'password_confirmation' => 'password confirmation',
		'g-recaptcha-response'  => 'captcha',
		'term'                  => 'terms',
		'category'              => 'category',
		'category_id'           => 'category',
		'post_type'             => 'post type',
		'post_type_id'          => 'post type',
		'title'                 => 'title',
		'body'                  => 'body',
		'description'           => 'description',
		'excerpt'               => 'excerpt',
		'date'                  => 'date',
		'time'                  => 'time',
		'available'             => 'available',
		'size'                  => 'size',
		'price'                 => 'price',
		'salary'                => 'salary',
		'contact_name'          => 'name',
		'location'              => 'location',
		'admin_code'            => 'location',
		'city'                  => 'city',
		'city_id'               => 'city',
		'package'               => 'package',
		'package_id'            => 'package',
		'payment_method'        => 'payment method',
		'payment_method_id'     => 'payment method',
		'sender_name'           => 'name',
		'subject'               => 'subject',
		'message'               => 'message',
		'report_type'           => 'report type',
		'report_type_id'        => 'report type',
		'file'                  => 'file',
		'filename'              => 'filename',
		'picture'               => 'picture',
		'resume'                => 'resume',
		'login'                 => 'login',
		'code'                  => 'code',
		'token'                 => 'token',
		'comment'               => 'comment',
		'rating'                => 'rating',
		'logo'                  => 'logo',
		'company_id'            => 'company',
		'resume_id'             => 'resume',
		'company.logo'          => 'logo',
		'company.name'          => 'company name',
		'company.description'   => 'company description',
		'company.country_code'  => 'company country',
		'company.city_id'       => 'company city',
		'company.address'       => 'company address',
		'company.phone'         => 'company phone',
		'company.fax'           => 'company fax',
		'company.email'         => 'company email',
		'company.website'       => 'company website',
		'resume.filename'       => 'resume file',
		'locale'                => 'locale',
		'currencies'            => 'currencies',
		'tags'					=> 'Tags',
		'from_name'             => 'name',
		'from_email'            => 'email',
		'from_phone'            => 'phone',
		
	],

];
