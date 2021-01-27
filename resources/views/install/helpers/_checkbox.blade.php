
												<input type="hidden" name="{{ $name }}" value="{{ $options[0] }}" />
												<input{{ $value == $options[1] ? " checked" : "" }} {{ isset($disabled) && $disabled == true ? ' disabled="disabled"' : "" }} type="checkbox" id="{{ $name }}" name="{{ $name }}" value="{{ $options[1] }}" class="switchery {{ $classes }}  {{ isset($class) ? $class : "" }}" data-on-text="On" data-off-text="Off" data-on-color="success" data-off-color="default">
												<label>
													@if (!empty($label))
														{!! $label !!}
													@endif
													
													@if (isset($help_class) && Lang::has('messages.' . $help_class . '.' . $name . '.help'))
														<span class="checkbox-description">
															{!! trans('messages.' . $help_class . '.' . $name . '.help') !!}
														</span>
													@endif
													
												</label>

											