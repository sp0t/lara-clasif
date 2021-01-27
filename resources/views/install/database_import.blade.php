@extends('install.layouts.master')

@section('title', trans('messages.database'))

@section('content')

    <h3 class="title-3"><i class="icon-database"></i> {{ trans('messages.database_configuration') }}</h3>

    <h5 class="">
        The settings was successfully configured!
        Click <strong>{!! trans('messages.setup_database') !!}</strong> button to start importing data to database '{{ $database["database"] }}'.
    </h5>

    <div class="text-right">
        <a href="{{ $installUrl . '/database_import?action=import' }}" class="btn btn-primary">
            {!! trans('messages.setup_database') !!} <i class="icon-right-open-big position-right"></i>
        </a>
        <a href="{{ $installUrl . '/database' }}" class="btn btn-default">
            <i class="icon-left-open-big"></i> {!! trans('messages.back') !!}
        </a>
    </div>

@endsection

@section('after_scripts')
    <script type="text/javascript" src="{{ URL::asset('assets/js/plugins/forms/styling/uniform.min.js') }}"></script>
@endsection
