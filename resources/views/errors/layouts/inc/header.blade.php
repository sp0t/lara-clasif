<?php
// Search parameters
$queryString = (request()->getQueryString() ? ('?' . request()->getQueryString()) : '');

// Check if the Multi-Countries selection is enabled
$multiCountriesIsEnabled = false;
$multiCountriesLabel = '';

// Logo Label
$logoLabel = '';
if (getSegment(1) != trans('routes.countries')) {
	$logoLabel = config('settings.app.app_name') . ((!empty(config('country.name'))) ? ' ' . config('country.name') : '');
}
?>
<div class="header">
	<nav class="navbar fixed-top navbar-site navbar-light bg-light navbar-expand-md" role="navigation">
		<div class="container">
			
			<div class="navbar-identity">
				{{-- Logo --}}
				<a href="{{ url(config('app.locale') . '/') }}" class="navbar-brand logo logo-title">
					<img src="{{ imgUrl(config('settings.app.logo', config('larapen.core.logo')), 'logo') }}" class="tooltipHere main-logo" />
				</a>
				{{-- Toggle Nav (Mobile) --}}
				<button data-target=".navbar-collapse" data-toggle="collapse" class="navbar-toggler pull-right" type="button">
					<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 30 30" width="30" height="30" focusable="false">
						<title>{{ t('Menu') }}</title>
						<path stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-miterlimit="10" d="M4 7h22M4 15h22M4 23h22"></path>
					</svg>
				</button>
				{{-- Country Flag (Mobile) --}}
				@if (getSegment(1) != trans('routes.countries'))
					@if (isset($multiCountriesIsEnabled) and $multiCountriesIsEnabled)
						@if (!empty(config('country.icode')))
							@if (file_exists(public_path().'/images/flags/24/' . config('country.icode') . '.png'))
								<button class="flag-menu country-flag d-block d-md-none btn btn-secondary hidden pull-right" href="#selectCountry" data-toggle="modal">
									<img src="{{ url('images/flags/24/' . config('country.icode') . '.png') . getPictureVersion() }}" style="float: left;">
									<span class="caret hidden-xs"></span>
								</button>
							@endif
						@endif
					@endif
				@endif
			</div>
			
			<div class="navbar-collapse collapse">
				<ul class="nav navbar-nav navbar-left">
					{{-- Country Flag --}}
					@if (getSegment(1) != trans('routes.countries'))
						@if (config('settings.geo_location.country_flag_activation'))
							@if (!empty(config('country.icode')))
								@if (file_exists(public_path() . '/images/flags/32/' . config('country.icode') . '.png'))
									<li class="flag-menu country-flag tooltipHere hidden-xs nav-item" data-toggle="tooltip" data-placement="{{ (config('lang.direction') == 'rtl') ? 'bottom' : 'right' }}">
										@if (isset($multiCountriesIsEnabled) and $multiCountriesIsEnabled)
											<a href="#selectCountry" data-toggle="modal" class="nav-link">
												<img class="flag-icon" src="{{ url('images/flags/32/' . config('country.icode') . '.png') . getPictureVersion() }}">
												<span class="caret hidden-sm"></span>
											</a>
										@else
											<a style="cursor: default;">
												<img class="flag-icon no-caret" src="{{ url('images/flags/32/' . config('country.icode') . '.png') . getPictureVersion() }}">
											</a>
										@endif
									</li>
								@endif
							@endif
						@endif
					@endif
				</ul>
				
				<ul class="nav navbar-nav ml-auto navbar-right">
					@if (!auth()->check())
						<li class="nav-item">
							<a href="{{ url(config('app.locale') . '/' . trans('routes.login')) }}" class="nav-link">
								<i class="icon-user fa"></i> {{ t('Log In') }}
							</a>
						</li>
						<li class="nav-item">
							<a href="{{ url(config('app.locale') . '/' . trans('routes.register')) }}" class="nav-link">
								<i class="icon-user-add fa"></i> {{ t('Register') }}
							</a>
						</li>
					@else
						<li class="nav-item">
							@if (app('impersonate')->isImpersonating())
								<a href="{{ route('impersonate.leave') }}" class="nav-link">
									<i class="icon-logout hidden-sm"></i> {{ t('Leave') }}
								</a>
							@else
								<a href="{{ url(config('app.locale') . '/logout') }}" class="nav-link">
									<i class="glyphicon glyphicon-off"></i> {{ t('Log Out') }}
								</a>
							@endif
						</li>
						<li class="nav-item dropdown no-arrow">
							<a href="#" class="dropdown-toggle nav-link" data-toggle="dropdown">
								<i class="icon-user fa hidden-sm"></i>
								<span>{{ auth()->user()->name }}</span>
								<i class="icon-down-open-big fa hidden-sm"></i>
							</a>
							<ul id="userMenuDropdown" class="dropdown-menu user-menu dropdown-menu-right shadow-sm">
								<li class="dropdown-item active">
									<a href="{{ url(config('app.locale') . '/account') }}">
										<i class="icon-home"></i> {{ t('Personal Home') }}
									</a>
								</li>
								@if (in_array(auth()->user()->user_type_id, [1]))
									<li class="dropdown-item">
										<a href="{{ url(config('app.locale') . '/account/companies') }}">
											<i class="icon-town-hall"></i> {{ t('My companies') }}
										</a>
									</li>
									<li class="dropdown-item">
										<a href="{{ url(config('app.locale') . '/account/my-posts') }}">
											<i class="icon-th-thumb"></i> {{ t('My ads') }}
										</a>
									</li>
									<li class="dropdown-item">
										<a href="{{ url(config('app.locale') . '/account/pending-approval') }}">
											<i class="icon-hourglass"></i> {{ t('Pending approval') }}
										</a>
									</li>
									<li class="dropdown-item">
										<a href="{{ url(config('app.locale') . '/account/archived') }}">
											<i class="icon-folder-close"></i> {{ t('Archived ads') }}
										</a>
									</li>
									<li class="dropdown-item">
										<a href="{{ url(config('app.locale') . '/account/conversations') }}">
											<i class="icon-mail-1"></i> {{ t('Conversations') }}
										</a>
									</li>
									<li class="dropdown-item">
										<a href="{{ url(config('app.locale') . '/account/transactions') }}">
											<i class="icon-money"></i> {{ t('Transactions') }}
										</a>
									</li>
								@endif
								@if (in_array(auth()->user()->user_type_id, [2]))
									<li class="dropdown-item">
										<a href="{{ url(config('app.locale') . '/account/resumes') }}">
											<i class="icon-attach"></i> {{ t('My resumes') }}
										</a>
									</li>
									<li class="dropdown-item">
										<a href="{{ url(config('app.locale') . '/account/favourite') }}">
											<i class="icon-heart"></i> {{ t('Favourite jobs') }}
										</a>
									</li>
									<li class="dropdown-item">
										<a href="{{ url(config('app.locale') . '/account/saved-search') }}">
											<i class="icon-star-circled"></i> {{ t('Saved searches') }}
										</a>
									</li>
									<li class="dropdown-item">
										<a href="{{ url(config('app.locale') . '/account/conversations') }}">
											<i class="icon-mail-1"></i> {{ t('Conversations') }}
										</a>
									</li>
								@endif
							</ul>
						</li>
					@endif
					
					@if (!auth()->check())
						<li class="nav-item postadd">
							@if (config('settings.single.guests_can_post_ads') != '1')
								<a class="btn btn-block btn-post btn-add-listing" href="#quickLogin" data-toggle="modal">
									<i class="fa fa-plus-circle"></i> {{ t('Create a Job ad') }}
								</a>
							@else
								<a class="btn btn-block btn-post btn-add-listing" href="{{ \App\Helpers\UrlGen::addPost(true) }}">
									<i class="fa fa-plus-circle"></i> {{ t('Create a Job ad') }}
								</a>
							@endif
						</li>
					@else
						@if (in_array(auth()->user()->user_type_id, [1]))
							<li class="nav-item postadd">
								<a class="btn btn-block btn-post btn-add-listing" href="{{ \App\Helpers\UrlGen::addPost(true) }}">
									<i class="fa fa-plus-circle"></i> {{ t('Create a Job ad') }}
								</a>
							</li>
						@endif
					@endif

					@if (!empty(config('lang.abbr')))
						@if (is_array(LaravelLocalization::getSupportedLocales()) && count(LaravelLocalization::getSupportedLocales()) > 1)
							<!-- Language selector -->
							<li class="dropdown lang-menu nav-item">
								<button class="btn btn-secondary dropdown-toggle" type="button" data-toggle="dropdown">
									<span class="lang-title">{{ strtoupper(config('app.locale')) }}</span>
								</button>
								<ul id="langMenuDropdown" class="dropdown-menu dropdown-menu-right user-menu shadow-sm" role="menu">
									@foreach(LaravelLocalization::getSupportedLocales() as $localeCode => $properties)
										@if (strtolower($localeCode) != strtolower(config('app.locale')))
											<?php
												// Controller Parameters
												$attr = [];
												$attr['countryCode'] = config('country.icode');
												if (isset($uriPathCatSlug)) {
													$attr['catSlug'] = $uriPathCatSlug;
													if (isset($uriPathSubCatSlug)) {
														$attr['subCatSlug'] = $uriPathSubCatSlug;
													}
												}
												if (isset($uriPathCityName) && isset($uriPathCityId)) {
													$attr['city'] = $uriPathCityName;
													$attr['id'] = $uriPathCityId;
												}
												if (isset($uriPathUserId)) {
													$attr['id'] = $uriPathUserId;
													if (isset($uriPathUsername)) {
														$attr['username'] = $uriPathUsername;
													}
												}
												if (isset($uriPathUsername)) {
													if (isset($uriPathUserId)) {
														$attr['id'] = $uriPathUserId;
													}
													$attr['username'] = $uriPathUsername;
												}
												if (isset($uriPathCompanyId)) {
													$attr['id'] = $uriPathCompanyId;
												}
												if (isset($uriPathTag)) {
													$attr['tag'] = $uriPathTag;
												}
												if (isset($uriPathPageSlug)) {
													$attr['slug'] = $uriPathPageSlug;
												}
												
												// Default
												// $link = LaravelLocalization::getLocalizedURL($localeCode, null, $attr);
												$link = lurl(null, $attr, $localeCode);
												$localeCode = strtolower($localeCode);
											?>
											<li class="dropdown-item">
												<a href="{{ $link }}" tabindex="-1" rel="alternate" hreflang="{{ $localeCode }}">
													<span class="lang-name">{{{ $properties['native'] }}}</span>
												</a>
											</li>
										@endif
									@endforeach
								</ul>
							</li>
						@endif
					@endif
				</ul>

			</div>
		</div>
	</nav>
</div>
