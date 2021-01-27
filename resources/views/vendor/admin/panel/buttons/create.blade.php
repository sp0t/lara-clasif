@if ($xPanel->hasAccess('create'))
	<a href="{{ url($xPanel->route.'/create') }}" class="btn btn-primary ladda-button" data-style="zoom-in">
		<span class="ladda-label">
            <i class="fa fa-plus"></i> {{ trans('admin::messages.add') }} {!! $xPanel->entity_name !!}
        </span>
    </a>
@endif