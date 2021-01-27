@if ($xPanel->reorder)
	@if ($xPanel->hasAccess('reorder'))
	  <a href="{{ url($xPanel->route.'/reorder') }}" class="btn btn-default ladda-button" data-style="zoom-in">
		  <span class="ladda-label">
              <i class="fa fa-arrows"></i> {{ trans('admin::messages.reorder') }} {!! $xPanel->entity_name_plural !!}
          </span>
      </a>
	@endif
@endif