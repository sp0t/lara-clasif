<!-- number input -->
<div @include('admin::panel.inc.field_wrapper_attributes') >
	<label for="{{ $field['name'] }}">{!! $field['label'] !!}</label>
	
	@if(isset($field['prefix']) || isset($field['suffix'])) <div class="input-group"> @endif
		@if(isset($field['prefix'])) <div class="input-group-addon">{!! $field['prefix'] !!}</div> @endif
		<input
				type="number"
				name="{{ $field['name'] }}"
				id="{{ $field['name'] }}"
				value="{{ old($field['name']) ? old($field['name']) : (isset($field['value']) ? $field['value'] : (isset($field['default']) ? $field['default'] : '' )) }}"
				@include('admin::panel.inc.field_attributes')
		>
		@if(isset($field['suffix'])) <div class="input-group-addon">{!! $field['suffix'] !!}</div> @endif
		
		@if(isset($field['prefix']) || isset($field['suffix'])) </div> @endif
	
	{{-- HINT --}}
	@if (isset($field['hint']))
		<p class="help-block">{!! $field['hint'] !!}</p>
	@endif
</div>