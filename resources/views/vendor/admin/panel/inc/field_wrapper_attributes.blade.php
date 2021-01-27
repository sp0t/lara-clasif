<?php
$var_name = str_replace('[]', '', $field['name']);
$var_name = str_replace('][', '.', $var_name);
$var_name = str_replace('[', '.', $var_name);
$var_name = str_replace(']', '', $var_name);
$required = (isset($field['rules']) && isset($field['rules'][$var_name]) && in_array('required', explode('|', $field['rules'][$var_name]))) ? true : '';
?>
@if (isset($field['wrapperAttributes']))
    @foreach ($field['wrapperAttributes'] as $attribute => $value)
    	@if (is_string($attribute))
			@if ($attribute == 'class' and isset($field['type']) and $field['type'] == 'image')
				{{ $attribute }}="{{ $value }} image"
			@else
				{{ $attribute }}="{{ $value }}"
			@endif
        @endif
    @endforeach

    @if (!isset($field['wrapperAttributes']['class']))
		@if (isset($field['type']) and $field['type'] == 'image')
			class="form-group col-md-12 image{{ $errors->has($var_name) ? ' has-error' : '' }}"
		@else
			class="form-group col-md-12{{ $errors->has($var_name) ? ' has-error' : '' }}"
		@endif
    @endif
@else
	@if (isset($field['type']) and $field['type'] == 'image')
		class="form-group col-md-12 image{{ $errors->has($var_name) ? ' has-error' : '' }}"
	@else
		class="form-group col-md-12{{ $errors->has($var_name) ? ' has-error' : '' }}"
	@endif
@endif