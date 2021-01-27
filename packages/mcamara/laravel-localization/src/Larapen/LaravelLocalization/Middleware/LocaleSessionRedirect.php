<?php namespace Larapen\LaravelLocalization\Middleware;

use Illuminate\Http\RedirectResponse;
use Closure;
use Larapen\LaravelLocalization\LanguageNegotiator;

class LocaleSessionRedirect extends LaravelLocalizationMiddlewareBase
{
	/**
	 * Handle an incoming request.
	 *
	 * @param \Illuminate\Http\Request $request
	 * @param Closure $next
	 *
	 * @return RedirectResponse|mixed
	 */
	public function handle($request, Closure $next)
	{
		// If the URL of the request is in exceptions.
		if ($this->shouldIgnore($request)) {
			return $next($request);
		}
		
		$params = explode('/', $request->path());
		$locale = session('locale', false);
		
		if (\count($params) > 0 && app('laravellocalization')->checkLocaleInSupportedLocales($params[0])) {
			session(['locale' => $params[0]]);
			
			return $next($request);
		}
		elseif (empty($locale) && app('laravellocalization')->hideUrlAndAcceptHeader())
		{
			// When default locale is hidden and accept language header is true,
			// then compute browser language when no session has been set.
			// Once the session has been set, there is no need
			// to negotiate language from browser again.
			$negotiator = new LanguageNegotiator(app('laravellocalization')->getDefaultLocale(), app('laravellocalization')->getSupportedLocales(), $request);
			$locale     = $negotiator->negotiateLanguage();
			session(['locale' => $locale]);
		}
		
		if ($locale === false){
			$locale = app('laravellocalization')->getCurrentLocale();
		}
		
		if (
			$locale
			&& app('laravellocalization')->checkLocaleInSupportedLocales($locale)
			&& !(
				app('laravellocalization')->getDefaultLocale() === $locale
				&& app('laravellocalization')->hideDefaultLocaleInURL()
			)
		) {
			app('session')->reflash();
			$redirection = app('laravellocalization')->getLocalizedURL($locale);
			
			return new RedirectResponse($redirection, 302, ['Vary' => 'Accept-Language']);
		}
		
		return $next($request);
	}
}
