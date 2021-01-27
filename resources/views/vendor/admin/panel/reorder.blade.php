@extends('admin::layout')

@section('after_styles')
    <link href="{{ asset('vendor/admin/nestedSortable/nestedSortable.css') }}" rel="stylesheet" type="text/css" />
@endsection

@section('header')
    <section class="content-header">
        <h1>
            <span class="text-capitalize">{!! $xPanel->entity_name_plural !!}</span>
            <small>{{ trans('admin::messages.all') }} <span class="text-lowercase">{!! $xPanel->entity_name_plural !!}</span> {{ trans('admin::messages.in_the_database') }}.</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="{{ admin_url() }}">{{ trans('admin::messages.dashboard') }}</a></li>
            <li><a href="{{ url($xPanel->route) }}" class="text-capitalize">{!! $xPanel->entity_name_plural !!}</a></li>
            <li class="active">{{ trans('admin::messages.reorder') }}</li>
        </ol>
    </section>
@endsection

@section('content')
    <?php
    function tree_element($entry, $key, $all_entries, $xPanel)
    {
        if (!isset($entry->tree_element_shown)) {
            // mark the element as shown
            $all_entries[$key]->tree_element_shown = true;
            $entry->tree_element_shown = true;

            // show the tree element
            echo '<li id="list_'.$entry->getKey().'">';
            if (\Illuminate\Support\Str::contains($xPanel->reorder_label, '.')) {
                $tmp = explode('.', $xPanel->reorder_label);
                $relation = head($tmp);
                $reorder_label = last($tmp);
                echo '<div><span class="disclose"><span></span></span>'.$entry->{$relation}->{$reorder_label}.'</div>';
            } else {
                echo '<div><span class="disclose"><span></span></span>'.$entry->{$xPanel->reorder_label}.'</div>';
            }

            // see if this element has any children
            $children = [];
            foreach ($all_entries as $key => $subentry) {
                if ($subentry->parent_id == $entry->getKey()) {
                    $children[] = $subentry;
                }
            }

            $children = collect($children)->sortBy('lft');

            // if it does have children, show them
            if (count($children)) {
                echo '<ol>';
                foreach ($children as $key => $child) {
                    $children[$key] = tree_element($child, $child->getKey(), $all_entries, $xPanel);
                }
                echo '</ol>';
            }
            echo '</li>';
        }

        return $entry;
    }

    ?>
    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            @if ($xPanel->hasAccess('list'))
                <a href="{{ url($xPanel->route) }}"><i class="fa fa-angle-double-left"></i> {{ trans('admin::messages.back_to_all') }} <span class="text-lowercase">{!! $xPanel->entity_name_plural !!}</span></a><br><br>
        @endif

        <!-- Default box -->
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title">{!! trans('admin::messages.reorder').' '.$xPanel->entity_name_plural !!}</h3>
                </div>
                <div class="box-body">

                    <p>{{ trans('admin::messages.reorder_text') }}</p>

                    @if (isset($languages))
                        <ul class="nav nav-tabs">
                            @foreach ($languages as $lang)
                                <li role="presentation" @if ($lang->abbr == $active_language) class="active" @endif >
                                    <a href="{{ url($xPanel->route.'/reorder/'.$lang->abbr) }}">{{ $lang->name }}</a>
                                </li>
                            @endforeach
                        </ul>
                    @endif

                    <ol class="sortable">
                        <?php
                        $all_entries = collect($entries->all())->sortBy('lft')->keyBy($xPanel->getModel()->getKeyName());
                        $root_entries = $all_entries->filter(function($item) use ($parent_id) {
                            return $item->parent_id == $parent_id;
                        });
                        ?>
                        @foreach ($root_entries as $key => $entry)
                            <?php
                            $root_entries[$key] = tree_element($entry, $key, $all_entries, $xPanel);
                            ?>
                        @endforeach
                    </ol>

                    <button id="toArray" class="btn btn-success ladda-button" data-style="zoom-in"><span class="ladda-label"><i class="fa fa-save"></i> {{ trans('admin::messages.save') }}</span></button>

                </div><!-- /.box-body -->
            </div><!-- /.box -->
        </div>
    </div>
@endsection

@section('after_scripts')
    <script src="https://code.jquery.com/ui/1.11.3/jquery-ui.min.js" type="text/javascript"></script>
    <script src="{{ url('vendor/admin/nestedSortable/jquery.mjs.nestedSortable2.js') }}" type="text/javascript"></script>

    <script type="text/javascript">
        jQuery(document).ready(function($) {

            // initialize the nested sortable plugin
            $('.sortable').nestedSortable({
                forcePlaceholderSize: true,
                handle: 'div',
                helper: 'clone',
                items: 'li',
                opacity: .6,
                placeholder: 'placeholder',
                revert: 250,
                tabSize: 25,
                tolerance: 'pointer',
                toleranceElement: '> div',
                maxLevels: {{ $xPanel->reorder_max_level ?? 3 }},

                isTree: true,
                expandOnHover: 700,
                startCollapsed: false
            });

            $('.disclose').on('click', function() {
                $(this).closest('li').toggleClass('mjs-nestedSortable-collapsed').toggleClass('mjs-nestedSortable-expanded');
            });

            $('#toArray').click(function(e){
                // get the current tree order
                arraied = $('ol.sortable').nestedSortable('toArray', {startDepthCount: 0});

                // log it
                console.log(arraied);

                // send it with POST
                $.ajax({
                    url: '{{ Request::url() }}',
                    type: 'POST',
                    data: { tree: arraied },
                })
                    .done(function() {
                        console.log("success");
                        new PNotify({
                            title: "{{ trans('admin::messages.reorder_success_title') }}",
                            text: "{{ trans('admin::messages.reorder_success_message') }}",
                            type: "success"
                        });
                    })
                    .fail(function() {
                        console.log("error");
                        new PNotify({
                            title: "{{ trans('admin::messages.reorder_error_title') }}",
                            text: "{{ trans('admin::messages.reorder_error_message') }}",
                            type: "danger"
                        });
                    })
                    .always(function() {
                        console.log("complete");
                    });

            });

            $.ajaxPrefilter(function(options, originalOptions, xhr) {
                var token = $('meta[name="csrf_token"]').attr('content');

                if (token) {
                    return xhr.setRequestHeader('X-XSRF-TOKEN', token);
                }
            });

        });
    </script>
@endsection