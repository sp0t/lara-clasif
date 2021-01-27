@extends('admin::layout')

@section('content-header')
	<section class="content-header">
	  <h1>
	    {{ trans('admin::messages.preview') }} <span class="text-lowercase">{!! $xPanel->entity_name !!}</span>
	  </h1>
	  <ol class="breadcrumb">
	    <li><a href="{{ admin_url() }}">{{ trans('admin::messages.dashboard') }}</a></li>
	    <li><a href="{{ url($xPanel->route) }}" class="text-capitalize">{!! $xPanel->entity_name_plural !!}</a></li>
	    <li class="active">{{ trans('admin::messages.preview') }}</li>
	  </ol>
	</section>
@endsection

@section('content')
	@if ($xPanel->hasAccess('list'))
		<a href="{{ url($xPanel->route) }}"><i class="fa fa-angle-double-left"></i> {{ trans('admin::messages.back_to_all') }} <span class="text-lowercase">{!! $xPanel->entity_name_plural !!}</span></a><br><br>
	@endif

	<!-- Default box -->
	  <div class="box box-primary">
	    <div class="box-header with-border">
	      <h3 class="box-title">
            {{ trans('admin::messages.preview') }}
            <span class="text-lowercase">{!! $xPanel->entity_name !!}</span>
          </h3>
	    </div>
	    <div class="box-body">
	      {{ dump($entry) }}
	    </div><!-- /.box-body -->
	  </div><!-- /.box -->

@endsection
