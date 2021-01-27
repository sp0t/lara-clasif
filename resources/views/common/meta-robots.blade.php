@if (
		(isset(config('country.lang')['abbr']) and config('app.locale') != config('country.lang')['abbr']) or
		(config('settings.seo.no_index_all') == true) or
		(\Illuminate\Support\Str::contains(\Route::currentRouteAction(), 'Search\CategoryController') and config('settings.seo.no_index_categories') == true) or
		(\Illuminate\Support\Str::contains(\Route::currentRouteAction(), 'Search\TagController') and config('settings.seo.no_index_tags') == true) or
		(\Illuminate\Support\Str::contains(\Route::currentRouteAction(), 'Search\CityController') and config('settings.seo.no_index_cities') == true) or
		(\Illuminate\Support\Str::contains(\Route::currentRouteAction(), 'Search\UserController') and config('settings.seo.no_index_users') == true) or
		(\Illuminate\Support\Str::contains(\Route::currentRouteAction(), 'Search\CompanyController') and config('settings.seo.no_index_companies') == true) or
		(\Illuminate\Support\Str::contains(\Route::currentRouteAction(), 'Post\ReportController') and config('settings.seo.no_index_post_report') == true)
	)
	<meta name="robots" content="noindex,nofollow">
	<meta name="googlebot" content="noindex">
@endif