@if ($xPanel->hasAccess('revisions') && count($entry->revisionHistory))
    <a href="{{ url($xPanel->route.'/'.$entry->getKey().'/revisions') }}" class="btn btn-xs btn-default"><i class="fa fa-history"></i> {{ trans('admin::messages.revisions') }}</a>
@endif
