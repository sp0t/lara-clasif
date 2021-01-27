@if ($xPanel->hasAccess('delete'))
	<a href="{{ url($xPanel->route.'/'.$entry->getKey()) }}" class="btn btn-xs btn-danger" data-button-type="delete">
        <i class="fa fa-trash"></i>
		{{ trans('admin::messages.delete') }}
	</a>
@endif