<!-- read_images -->
<div @include('admin::panel.inc.field_wrapper_attributes') >

	<input type="hidden" name="edit_url" value="{{ request()->url() }}">
	<label>{{ $field['label'] }}</label>
	<?php
        $entity_model = (isset($field['value'])) ? $field['value'] : null;
        $posts_pictures_number = (int)config('settings.single.pictures_limit');
	?>

	<div style="display: block; text-align: center;">
	@if (!empty($entity_model) && !$entity_model->isEmpty())
		@foreach ($entity_model as $connected_entity_entry)
			<div style="margin: 10px 5px; display: inline-block;">
				<img src="{{ \Storage::disk($field['disk'])->url($connected_entity_entry->{$field['attribute']}) }}" style="width:320px; height:auto;">
				<div style="text-align: center; margin-top: 10px;">
					<a href="{{ admin_url('pictures/' . $connected_entity_entry->id . '/edit') }}" class="btn btn-xs btn-default"><i class="fa fa-edit"></i> {{ trans('admin::messages.Edit') }}</a>&nbsp;
					<a href="{{ admin_url('pictures/' . $connected_entity_entry->id) }}" class="btn btn-xs btn-default" data-button-type="delete"><i class="fa fa-trash"></i> {{ trans('admin::messages.Delete') }}</a>
				</div>
			</div>
		@endforeach
        @if ($entity_model->count() < $posts_pictures_number)
            <hr><br>
            <a href="{{ admin_url('pictures/create?post_id=' . request()->segment(3)) }}" class="btn btn-xs btn-default"><i class="fa fa-edit"></i> {{ trans('admin::messages.add') }} {{ trans('admin::messages.picture') }}</a><br><br>
        @endif
	@else
		<br>{{ trans('admin::messages.No pictures found.') }}<br><br>
        <a href="{{ admin_url('pictures/create?post_id=' . request()->segment(3)) }}" class="btn btn-xs btn-default"><i class="fa fa-edit"></i> {{ trans('admin::messages.add') }} {{ trans('admin::messages.picture') }}</a><br><br>
	@endif
	</div>
	<div style="clear: both;"></div>

</div>

@if ($xPanel->checkIfFieldIsFirstOfItsType($field, $fields))
    @push('crud_fields_scripts')
    <script>
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $("[data-button-type=delete]").click(function (e) {
            e.preventDefault(); // does not go through with the link.

            var $this = $(this);

            if (confirm("{{ trans('admin::messages.delete_confirm') }}") == true) {
                $.post({
                    type: 'DELETE',
                    url: $this.attr('href'),
                    success: function (result) {
                        alert("{{ trans('admin::messages.delete_confirmation_message') }}");
                        window.location.replace("{{ request()->url() }}");
                        window.location.href = "{{ request()->url() }}";
                    }
                });
            }
        });
    </script>
    @endpush
@endif