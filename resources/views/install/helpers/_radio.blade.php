    @foreach($options as $option)
        <div class="radio">
            <label>
                <input{{ $option['value'] == $value  ? " checked" : "" }} type="radio" id="{{ $name }}" name="{{ $name }}" value="{{ $option['value'] }}" class="styled">
                {{ $option['text'] }}
            </label>
        </div>
	@endforeach