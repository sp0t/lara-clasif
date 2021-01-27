<?php
$advertising = \App\Models\Advertising::where('slug', 'top')->first();
?>
@if (!empty($advertising))
	@include('home.inc.spacer')
	<div class="container">
		{{-- Desktop --}}
		<div class="container ads-parent-responsive d-none d-xl-block d-lg-block d-md-none d-sm-none">
			<div class="text-center">
				{!! $advertising->tracking_code_large !!}
			</div>
		</div>
		{{-- Tablet --}}
		<div class="container ads-parent-responsive d-none d-xl-none d-lg-none d-md-block d-sm-none">
			<div class="text-center">
				{!! $advertising->tracking_code_medium !!}
			</div>
		</div>
		{{-- Mobile --}}
		<div class="container ads-parent-responsive d-block d-xl-none d-lg-none d-md-none d-sm-block">
			<div class="text-center">
				{!! $advertising->tracking_code_small !!}
			</div>
		</div>
	</div>
@endif