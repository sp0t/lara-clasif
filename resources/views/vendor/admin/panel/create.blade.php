@extends('admin::layout')

@section('header')
    <section class="content-header">
        <h1>
            {{ trans('admin::messages.add') }} <span class="text-lowercase">{!! $xPanel->entity_name !!}</span>
        </h1>
        <ol class="breadcrumb">
            <li><a href="{{ admin_url() }}">{{ trans('admin::messages.dashboard') }}</a></li>
            <li><a href="{{ url($xPanel->route) }}" class="text-capitalize">{!! $xPanel->entity_name_plural !!}</a></li>
            <li class="active">{{ trans('admin::messages.add') }}</li>
        </ol>
    </section>
@endsection

@section('content')
    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            <!-- Default box -->
            @if ($xPanel->hasAccess('list'))
                <a href="{{ url($xPanel->route) }}"><i class="fa fa-angle-double-left"></i> {{ trans('admin::messages.back_to_all') }} <span class="text-lowercase">{!! $xPanel->entity_name_plural !!}</span></a><br><br>
            @endif

            {!! Form::open(array('url' => $xPanel->route, 'method' => 'post', 'files' => $xPanel->hasUploadFields('create'))) !!}
            <div class="box box-primary">

                <div class="box-header with-border">
                    <h3 class="box-title">{{ trans('admin::messages.add_a_new') }} {!! $xPanel->entity_name !!}</h3>
                </div>
                <div class="box-body row">
                    <!-- load the view from the application if it exists, otherwise load the one in the package -->
                    @if(view()->exists('vendor.admin.panel.' . $xPanel->entity_name . '.form_content'))
                        @include('vendor.admin.panel.' . $xPanel->entity_name . '.form_content', ['fields' => $xPanel->getFields('create')])
                    @elseif(view()->exists('vendor.admin.panel.form_content'))
                        @include('vendor.admin.panel.form_content', ['fields' => $xPanel->getFields('create')])
                    @else
                        @include('admin::panel.form_content', ['fields' => $xPanel->getFields('create')])
                    @endif
                </div><!-- /.box-body -->
                <div class="box-footer">
    
                    @include('admin::panel.inc.form_save_buttons')
                    
                </div><!-- /.box-footer-->

            </div><!-- /.box -->
            {!! Form::close() !!}
        </div>
    </div>

@endsection
