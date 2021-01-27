{{-- Simditor --}}
@if (config('settings.single.simditor_wysiwyg'))
    <link media="all" rel="stylesheet" type="text/css" href="{{ asset('assets/plugins/simditor/styles/simditor.css') }}" />
    @if (config('lang.direction') == 'rtl')
        <link media="all" rel="stylesheet" type="text/css" href="{{ asset('assets/plugins/simditor/styles/simditor-rtl.css') }}" />
    @endif
@endif

{{-- CKEditor --}}
{{-- Use this plugin by deactiving the "Simditor WYSIWYG Editor" --}}
@if (!config('settings.single.simditor_wysiwyg') && config('settings.single.ckeditor_wysiwyg'))
    {{-- ... --}}
@endif
