<!-- view field -->

<div @include('admin::panel.inc.field_wrapper_attributes') >
  @include($field['view'], compact('xPanel', 'entry', 'field'))
</div>
