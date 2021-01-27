<!-- Tiny MCE -->
<div @include('admin::panel.inc.field_wrapper_attributes') >
    <label>{!! $field['label'] !!}</label>
    <textarea
    	id="tinymce-{{ $field['name'] }}"
        name="{{ $field['name'] }}"
        @include('admin::panel.inc.field_attributes', ['default_class' =>  'form-control tinymce'])
        >{{ old($field['name']) ? old($field['name']) : (isset($field['value']) ? $field['value'] : (isset($field['default']) ? $field['default'] : '' )) }}</textarea>

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
    @endpush

    {{-- FIELD JS - will be loaded in the after_scripts section --}}
    @push('crud_fields_scripts')
    <!-- include tinymce js-->
    <script src="{{ asset('vendor/admin/tinymce/tinymce.min.js') }}"></script>
    {{-- <script src="{{ asset('vendor/admin/tinymce/jquery.tinymce.min.js') }}"></script> --}}

    <script type="text/javascript">
    tinymce.init({
        selector: "textarea.tinymce",
        skin: "dick-light",
        plugins: "link,anchor"
     });
    </script>
    @endpush

@endif
{{-- End of Extra CSS and JS --}}
{{-- ########################################## --}}