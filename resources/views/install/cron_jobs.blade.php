@extends('install.layouts.master')

@section('title', trans('messages.cron_jobs'))

@section('content')

	@include('elements._cron_jobs')

	<br />
	<div class="text-right">
		<a href="{{ $installUrl . '/finish' }}" class="btn btn-primary bg-teal">
			{!! trans('messages.finish') !!} <i class="icon-right-open-big position-right"></i>
		</a>
	</div>

@endsection

@section('after_scripts')
	<script type="text/javascript" src="{{ URL::asset('assets/js/plugins/forms/styling/uniform.min.js') }}"></script>
@endsection
