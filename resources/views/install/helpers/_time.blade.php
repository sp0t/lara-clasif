<div class="input-icon-right">											
    <input {{ isset($disabled) && $disabled == true ? ' disabled="disabled"' : "" }} id="{{ $name }}" placeholder="{{ isset($placeholder) ? $placeholder : "" }}" value="{{ isset($value) ? $value : "" }}" type="text" name="{{ $name }}" class="form-control{{ $classes }} pickatime{{ isset($class) ? $class : "" }}">
    <span class=""><i class="icon-alarm-check"></i></span>
</div>
