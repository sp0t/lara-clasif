@if (config('settings.app.show_countries_charts'))
@php
	$usersDataArr = json_decode($usersPerCountry->data, true);
	$countUsersLabels = (isset($usersDataArr['labels']) && is_array($usersDataArr['labels']) && count($usersDataArr['labels']) > 1) ? count($usersDataArr['labels']) : 0;
@endphp

@if ($usersPerCountry->countCountries > 1)
<div class="box box-primary">
	<div class="box-header with-border">
		<h3 class="box-title">{{ $usersPerCountry->title }}</h3>
		
		<div class="box-tools pull-right">
			<button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
		</div>
	</div>
	<div class="box-body chart-responsive">
		@if ($countUsersLabels > 0)
			<canvas id="pieChartUsers"></canvas>
		@else
			{!! trans('admin::messages.No data found.') !!}
		@endif
	</div>
</div>
@endif

@push('dashboard_styles')
	<style>
		canvas {
			-moz-user-select: none;
			-webkit-user-select: none;
			-ms-user-select: none;
		}
	</style>
@endpush

@push('dashboard_scripts')
    <script>
		@if ($usersPerCountry->countCountries > 1)
		@if ($countUsersLabels > 0)
			@php
				$usersDisplayLegend = ($countUsersLabels <= 15) ? 'true' : 'false';
			@endphp
			
			var config = {
				type: 'pie', /* pie, doughnut */
				data: {!! $usersPerCountry->data !!},
				options: {
					responsive: true,
					legend: {
						display: {{ $usersDisplayLegend }},
						position: 'right'
					},
					title: {
						display: false
					},
					animation: {
						animateScale: true,
						animateRotate: true
					}
				}
			};
			
			$(function () {
				var ctx = document.getElementById('pieChartUsers').getContext('2d');
				window.myUsersDoughnut = new Chart(ctx, config);
			});
		@endif
		@endif
    </script>
@endpush
@endif
