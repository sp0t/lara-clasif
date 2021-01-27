@if ($xPanel->hasAccess('parent'))
	<a href="{{ url($xPanel->parent_route) }}" class="btn btn-success ladda-button" data-style="zoom-in">
		<span class="ladda-label">
            <i class="fa fa-reply"></i> {{ trans('admin::messages.go_to') }} {!! $xPanel->parent_entity_name_plural !!}
        </span>
    </a>
@endif