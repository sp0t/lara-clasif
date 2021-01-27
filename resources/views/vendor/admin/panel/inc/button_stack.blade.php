@if ($xPanel->buttons->where('stack', $stack)->count())
	@foreach ($xPanel->buttons->where('stack', $stack) as $button)
	  @if ($button->type == 'model_function')
		  @if ($stack == 'line')
			  {!! $entry->{$button->content}($entry); !!}
		  @else
			  {!! $xPanel->model->{$button->content}($xPanel); !!}
		  @endif
	  @else
		@include($button->content)
	  @endif
	@endforeach
@endif