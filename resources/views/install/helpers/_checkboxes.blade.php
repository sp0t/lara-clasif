    @foreach($options as $option)
        <div class="checkbox">
            <label>
                <input{{ in_array($option['value'], explode(",", $value))  ? " checked" : "" }} type="checkbox" id="{{ $name }}" name="{{ $name }}" value="{{ $option['value'] }}" class="styled">
                {{ $option['text'] }}
            </label>
        </div>
	@endforeach