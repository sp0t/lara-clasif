@extends('admin::layout')

@section('header')
	<section class="content-header">
		<h1>
			{{ trans('admin::messages.dashboard') }}
			<small>{{ trans('admin::messages.first_page_you_see', ['app_name' => config('app.name'), 'app_version' => config('app.version')]) }}</small>
		</h1>
		<ol class="breadcrumb">
			<li><a href="{{ admin_url() }}">{{ config('app.name') }}</a></li>
			<li class="active">{{ trans('admin::messages.dashboard') }}</li>
		</ol>
	</section>
@endsection

@section('content')
    <div class="row">
        <div class="col-md-12">
        
			<!-- Small Boxes (Stats Boxes) -->
			@include('admin::dashboard.inc.stats-boxes')
	
			<div class="row">
				<!-- Left Col -->
				<section class="col-lg-6 connectedSortable">
					{{-- Posts Chart (Morris) --}}
					@include('admin::dashboard.inc.charts.morris.' . config('settings.app.dashboard_latest_entries_chart', 'bar') . '.latest-posts')
					
					{{-- Latest Posts --}}
					@include('admin::dashboard.inc.latest-posts')
					
					{{-- Posts Chart (ChartJs) --}}
					@include('admin::dashboard.inc.charts.chartjs.pie.posts-per-country')
				</section>
				
				<!-- Right Col -->
				<section class="col-lg-6 connectedSortable">
					{{-- Users Chart (Morris) --}}
					@include('admin::dashboard.inc.charts.morris.' . config('settings.app.dashboard_latest_entries_chart', 'bar') . '.latest-users')
					
					{{-- Latest Users --}}
					@include('admin::dashboard.inc.latest-users')
					
					{{-- Posts Chart (ChartJs) --}}
					@include('admin::dashboard.inc.charts.chartjs.pie.users-per-country')
				</section>
			</div>
			
        </div>
    </div>
@endsection

{{-- Define blade stacks so css and js can be pushed from the fields to these sections. --}}
@section('after_styles')
	<?php /*<link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/morris.js/0.5.1/morris.css">*/ ?>
	<link rel="stylesheet" href="{{ asset('vendor/adminlte/') }}/plugins/morris/0.5.1/morris.css">
	
	<!-- DASHBOARD LIST CONTENT - dashboard_styles stack -->
	@stack('dashboard_styles')
	
	<style>
		/* Bootstrap tooltip need to be in single line */
		.tooltip-inner {
			white-space: nowrap;
			max-width: none;
		}
	</style>
@endsection

@section('after_scripts')
	<script src="//cdnjs.cloudflare.com/ajax/libs/raphael/2.1.0/raphael-min.js"></script>
	<script src="{{ asset('vendor/adminlte') }}/plugins/morris/morris.min.js"></script>
	<script src="{{ asset('vendor/adminlte') }}/plugins/chartjs/2.7.2/Chart.js"></script>
	
	<!-- DASHBOARD LIST CONTENT - dashboard_scripts stack -->
	@stack('dashboard_scripts')
@endsection
