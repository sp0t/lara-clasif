<!-- select2 -->
@php
	$current_value = old($field['name']) ? old($field['name']) : (isset($field['value']) ? $field['value'] : (isset($field['default']) ? $field['default'] : '' ));
@endphp

<div @include('admin::panel.inc.field_wrapper_attributes') >
	<label>{!! $field['label'] !!}</label>
    <?php $entity_model = $xPanel->model; ?>
	<select
			name="{{ $field['name'] }}"
			style="width: 100%"
			@include('admin::panel.inc.field_attributes', ['default_class' =>  'form-control select2_field'])
	>
		
		@if (!(isset($field['fake']) and $field['fake']))
			@if ($entity_model::isColumnNullable($field['name']))
				<option value="">-</option>
			@endif
		@else
			@if (isset($field['allows_null']) && $field['allows_null']==true)
				<option value="">-</option>
			@endif
		@endif
		
		@if (isset($field['model']))
			@foreach ($field['model']::all() as $connected_entity_entry)
				<?php
					$connectedEntityEntryKey = $connected_entity_entry->getKey();
					if (isset($field['key']) && isset($connected_entity_entry->{$field['key']})) {
						$connectedEntityEntryKey = $connected_entity_entry->{$field['key']};
					}
				?>
				@if($current_value == $connectedEntityEntryKey)
					<option value="{{ $connectedEntityEntryKey }}" selected>{{ $connected_entity_entry->{$field['attribute']} }}</option>
				@else
					<option value="{{ $connectedEntityEntryKey }}">{{ $connected_entity_entry->{$field['attribute']} }}</option>
				@endif
			@endforeach
		@endif
	</select>
	
	{{-- HINT --}}
	@if (isset($field['hint']))
		<p class="help-block">{!! $field['hint'] !!}</p>
	@endif
</div>

{{-- ########################################## --}}
{{-- Extra CSS and JS for this particular field --}}
{{-- If a field type is shown multiple times on a form, the CSS and JS will only be loaded once --}}
@if ($xPanel->checkIfFieldIsFirstOfItsType($field, $fields))
	
	{{-- FIELD CSS - will be loaded in the after_styles section --}}
	@push('crud_fields_styles')
		<!-- include select2 css-->
		<link href="{{ asset('vendor/adminlte/plugins/select2/select2.min.css') }}" rel="stylesheet" type="text/css" />
		<link href="https://cdnjs.cloudflare.com/ajax/libs/select2-bootstrap-theme/0.1.0-beta.10/select2-bootstrap.min.css" rel="stylesheet" type="text/css" />
	@endpush
	
	{{-- FIELD JS - will be loaded in the after_scripts section --}}
	@push('crud_fields_scripts')
	<!-- include select2 js-->
	<script src="{{ asset('vendor/adminlte/plugins/select2/select2.min.js') }}"></script>
	<script>
		jQuery(document).ready(function($) {
			// trigger select2 for each untriggered select2 box
			$('.select2_field').each(function (i, obj) {
				if (!$(obj).hasClass("select2-hidden-accessible"))
				{
					$(obj).select2({
						theme: "bootstrap"
					});
				}
			});
		});
	</script>
	@endpush

@endif
{{-- End of Extra CSS and JS --}}
{{-- ########################################## --}}