@if (isset($paddingTopExists))
	@if (isset($firstSection) and !$firstSection)
		<div class="h-spacer"></div>
	@else
		@if (!$paddingTopExists)
			<div class="h-spacer"></div>
		@endif
	@endif
@else
	<div class="h-spacer"></div>
@endif