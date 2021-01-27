{{-- Simditor --}}
@if (config('settings.single.simditor_wysiwyg'))
    <script src="{{ asset('assets/plugins/simditor/scripts/mobilecheck.js') }}"></script>
    <script src="{{ asset('assets/plugins/simditor/scripts/module.js') }}"></script>
    <script src="{{ asset('assets/plugins/simditor/scripts/uploader.js') }}"></script>
    <script src="{{ asset('assets/plugins/simditor/scripts/hotkeys.js') }}"></script>
    <script src="{{ asset('assets/plugins/simditor/scripts/simditor.js') }}"></script>
    <script type="text/javascript">
        Simditor.i18n = {
            '{{ config('app.locale') }}': {
                'blockquote': '{!! t('simditor.blockquote') !!}',
                'bold': '{!! t('simditor.bold') !!}',
                'code': '{!! t('simditor.code') !!}',
                'color': '{!! t('simditor.color') !!}',
                'coloredText': '{!! t('simditor.coloredText') !!}',
                'hr': '{!! t('simditor.hr') !!}',
                'image': '{!! t('simditor.image') !!}',
                'externalImage': '{!! t('simditor.externalImage') !!}',
                'uploadImage': '{!! t('simditor.uploadImage') !!}',
                'uploadFailed': '{!! t('simditor.uploadFailed') !!}',
                'uploadError': '{!! t('simditor.uploadError') !!}',
                'imageUrl': '{!! t('simditor.imageUrl') !!}',
                'imageSize': '{!! t('simditor.imageSize') !!}',
                'imageAlt': '{!! t('simditor.imageAlt') !!}',
                'restoreImageSize': '{!! t('simditor.restoreImageSize') !!}',
                'uploading': '{!! t('simditor.uploading') !!}',
                'indent': '{!! t('simditor.indent') !!}',
                'outdent': '{!! t('simditor.outdent') !!}',
                'italic': '{!! t('simditor.italic') !!}',
                'link': '{!! t('simditor.link') !!}',
                'linkText': '{!! t('simditor.linkText') !!}',
                'linkUrl': '{!! t('simditor.linkUrl') !!}',
                'linkTarget': '{!! t('simditor.linkTarget') !!}',
                'openLinkInCurrentWindow': '{!! t('simditor.openLinkInCurrentWindow') !!}',
                'openLinkInNewWindow': '{!! t('simditor.openLinkInNewWindow') !!}',
                'removeLink': '{!! t('simditor.removeLink') !!}',
                'ol': '{!! t('simditor.ol') !!}',
                'ul': '{!! t('simditor.ul') !!}',
                'strikethrough': '{!! t('simditor.strikethrough') !!}',
                'table': '{!! t('simditor.table') !!}',
                'deleteRow': '{!! t('simditor.deleteRow') !!}',
                'insertRowAbove': '{!! t('simditor.insertRowAbove') !!}',
                'insertRowBelow': '{!! t('simditor.insertRowBelow') !!}',
                'deleteColumn': '{!! t('simditor.deleteColumn') !!}',
                'insertColumnLeft': '{!! t('simditor.insertColumnLeft') !!}',
                'insertColumnRight': '{!! t('simditor.insertColumnRight') !!}',
                'deleteTable': '{!! t('simditor.deleteTable') !!}',
                'title': '{!! t('simditor.title') !!}',
                'normalText': '{!! t('simditor.normalText') !!}',
                'underline': '{!! t('simditor.underline') !!}',
                'alignment': '{!! t('simditor.alignment') !!}',
                'alignCenter': '{!! t('simditor.alignCenter') !!}',
                'alignLeft': '{!! t('simditor.alignLeft') !!}',
                'alignRight': '{!! t('simditor.alignRight') !!}',
                'selectLanguage': '{!! t('simditor.selectLanguage') !!}',
                'fontScale': '{!! t('simditor.fontScale') !!}',
                'fontScaleXLarge': '{!! t('simditor.fontScaleXLarge') !!}',
                'fontScaleLarge': '{!! t('simditor.fontScaleLarge') !!}',
                'fontScaleNormal': '{!! t('simditor.fontScaleNormal') !!}',
                'fontScaleSmall': '{!! t('simditor.fontScaleSmall') !!}',
                'fontScaleXSmall': '{!! t('simditor.fontScaleXSmall') !!}'
            }
        };
        
        (function() {
            $(function() {
                var $preview, editor, mobileToolbar, toolbar, allowedTags;
                Simditor.locale = '{{ config('app.locale') }}';
                toolbar = ['bold','italic','underline','fontScale','color','|','ol','ul','blockquote','table','link'];
                mobileToolbar = ["bold", "italic", "underline", "ul", "ol"];
                if (mobilecheck()) {
                    toolbar = mobileToolbar;
                }
                allowedTags = ['br','span','a','img','b','strong','i','strike','u','font','p','ul','ol','li','blockquote','pre','h1','h2','h3','h4','hr','table'];
                editor = new Simditor({
                    textarea: $('#description'),
                    placeholder: '{{ t('Describe what makes your ad unique') }}...',
                    toolbar: toolbar,
                    pasteImage: false,
                    defaultImage: '{{ asset('assets/plugins/simditor/images/image.png') }}',
                    upload: false,
                    allowedTags: allowedTags
                });
                $preview = $('#preview');
                if ($preview.length > 0) {
                    return editor.on('valuechanged', function(e) {
                        return $preview.html(editor.getValue());
                    });
                }
            });
        }).call(this);
    </script>
@endif

<?php
/*
{{-- CKEditor --}}
{{-- Use this plugin by deactiving the "Simditor WYSIWYG Editor" --}}
@if (!config('settings.single.simditor_wysiwyg') && config('settings.single.ckeditor_wysiwyg'))
    <script src="{{ asset('vendor/admin/ckeditor/ckeditor.js') }}"></script>
    <script src="{{ asset('vendor/admin/ckeditor/adapters/jquery.js') }}"></script>
    <script type="text/javascript">
        jQuery(document).ready(function($) {
            CKEDITOR.config.toolbar = [
                ['Bold','Italic','Underline','Strike','-','RemoveFormat','-','NumberedList','BulletedList','-','Undo','Redo','-','Table','-','Link','Unlink','Smiley','Source']
            ];
            $('textarea[name="description"].ckeditor').ckeditor({
                language: '{{ strtolower(ietfLangTag(config('app.locale'))) }}'
            });
        });
    </script>
@endif
*/
?>
