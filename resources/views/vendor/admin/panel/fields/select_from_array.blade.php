<!-- select -->
<div @include('admin::panel.inc.field_wrapper_attributes') >
    <label>{!! $field['label'] !!}</label>
    <select
        name="{{ $field['name'] }}"
        @include('admin::panel.inc.field_attributes')
    	>

        @if (isset($field['allows_null']) && $field['allows_null']==true)
            <option value="">-</option>
        @endif
	
		@if (isset($field['options']) && !empty($field['options']))
			@foreach ($field['options'] as $key => $value)
				<option value="{{ $key }}"
					@if ((isset($field['value']) && $key==$field['value']) || ( ! is_null( old($field['name']) ) && old($field['name']) == $key) )
						 selected
					@endif
				>{{ $value }}</option>
			@endforeach
		@endif
	</select>

    {{-- HINT --}}
    @if (isset($field['hint']))
        <p class="help-block">{!! $field['hint'] !!}</p>
    @endif
</div>