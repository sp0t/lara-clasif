@if ($xPanel->hasAccess('show'))
	<a href="{{ url($xPanel->route.'/'.$entry->getKey()) }}" class="btn btn-xs btn-default"><i class="fa fa-eye"></i> {{ trans('admin::messages.preview') }}</a>
@endif