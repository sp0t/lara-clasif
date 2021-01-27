@if (config('settings.app.show_countries_charts'))
@php
	$postsDataArr = json_decode($postsPerCountry->data, true);
	$countPostsLabels = (isset($postsDataArr['labels']) && is_array($postsDataArr['labels']) && count($postsDataArr['labels']) > 1) ? count($postsDataArr['labels']) : 0;
@endphp

@if ($postsPerCountry->countCountries > 1)
<div class="box box-primary">
	<div class="box-header with-border">
		<h3 class="box-title">{{ $postsPerCountry->title }}</h3>
		
		<div class="box-tools pull-right">
			<button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
		</div>
	</div>
	<div class="box-body chart-responsive">
		@if ($countPostsLabels > 0)
			<canvas id="pieChartPosts"></canvas>
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
		@if ($postsPerCountry->countCountries > 1)
		@if ($countPostsLabels > 0)
			@php
				$postsDisplayLegend = ($countPostsLabels <= 15) ? 'true' : 'false';
			@endphp
			
			var config1 = {
				type: 'pie', /* pie, doughnut */
				data: {!! $postsPerCountry->data !!},
				options: {
					responsive: true,
					legend: {
						display: {{ $postsDisplayLegend }},
						position: 'left'
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
				var ctx = document.getElementById('pieChartPosts').getContext('2d');
				window.myPostsDoughnut = new Chart(ctx, config1);
			});
		@endif
		@endif
	</script>
@endpush
@endif
