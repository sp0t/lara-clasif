{{-- checkbox --}}
<?php
$disabled = '';
if (
	(isset($xPanel) && !$xPanel->hasAccess('delete'))
	or
	(
		/* Security for Admin Users */
		\Illuminate\Support\Str::contains(\Illuminate\Support\Facades\Route::currentRouteAction(), 'UserController')
		&& (isset($entry) && $entry->can(\App\Models\Permission::getStaffPermissions()))
	)
) {
	$disabled = 'disabled="disabled"';
}
?>
<td class="dt-checkboxes-cell">
	<input name="entryId[]" type="checkbox" value="{{ $entry->{$column['name']} }}" class="dt-checkboxes" {!! $disabled !!}>
</td>
