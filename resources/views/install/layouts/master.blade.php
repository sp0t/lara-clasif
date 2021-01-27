{{--
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
--}}
<!DOCTYPE html>
<html lang="{{ config('app.locale', 'en') }}">
<head>
	<meta charset="utf-8">
	<meta name="csrf-token" content="{{ csrf_token() }}">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta name="robots" content="noindex,nofollow"/>
	<meta name="googlebot" content="noindex">
	<title>@yield('title')</title>
	
	@yield('before_styles')
	
	<link href="{{ url(mix('css/app.css')) }}" rel="stylesheet">
	
	@yield('after_styles')

    <!--[if lt IE 9]>
	<script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
	<script src="https://oss.maxcdn.com/libs/respond.js/1.3.0/respond.min.js"></script>
	<![endif]-->

	<script>
		paceOptions = {
			elements: true
		};
	</script>
	<script src="{{ URL::asset('assets/js/pace.min.js') }}"></script>
</head>
<body>
<div id="wrapper">

	@section('header')
		@include('install.layouts.inc.header')
	@show

	<div class="container">
		<div class="row">
			<div class="col-sm-12 col-md-12 col-lg-12">
				<h1 class="text-center title-1 font-weight-bold mt-5 mb-3" style="text-transform: none;">
					{{ trans('messages.installation') }}
				</h1>
	
				@include('install._steps')
				
				@if (isset($errors) and $errors->any())
					<div class="alert alert-danger mt-4">
						<ul class="list list-check">
							@foreach ($errors->all() as $error)
								<li>{!! $error !!}</li>
							@endforeach
						</ul>
					</div>
					<?php $paddingTopExists = true; ?>
				@endif
			</div>
		</div>
	</div>
	
	@include('common.spacer')
	<div class="main-container">
		<div class="container">
			<div class="row">
				<div class="col-sm-12 col-md-12 col-xl-12">
					<div class="inner-box">
						@yield('content')
					</div>
				</div>
			</div>
		</div>
	</div>
	
	@section('footer')
		@include('install.layouts.inc.footer')
	@show

</div>

@yield('before_scripts')

<script>
	/* Init. vars */
	var siteUrl = '{{ url('/') }}';
	var languageCode = '{{ config('app.locale') }}';
	var countryCode = '{{ config('country.code', 0) }}';
	
	/* Init. Translation vars */
	var langLayout = {
		'hideMaxListItems': {
			'moreText': "{{ t('View More') }}",
			'lessText': "{{ t('View Less') }}"
		}
	};
</script>

<script src="{{ url(mix('js/app.js')) }}"></script>
@if (file_exists(public_path() . '/assets/plugins/select2/js/i18n/'.config('app.locale').'.js'))
	<script src="{{ url('assets/plugins/select2/js/i18n/'.config('app.locale').'.js') }}"></script>
@endif

<script>
	$(document).ready(function () {
		/* Select Boxes */
		$(".selecter").select2({
			language: '{{ config('app.locale', 'en') }}',
			dropdownAutoWidth: 'true',
			/*minimumResultsForSearch: Infinity*/
		});
	});
</script>

@yield('after_scripts')

</body>
</html>