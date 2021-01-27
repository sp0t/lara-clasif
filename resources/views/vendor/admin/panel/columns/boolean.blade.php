{{-- converts 1/true or 0/false to yes/no/lang --}}
<td data-order="{{ $entry->{$column['name']} }}">
	@if ($entry->{$column['name']} === true || $entry->{$column['name']} === 1 || $entry->{$column['name']} === '1')
        @if ( isset( $column['options'][1] ) )
            {{ $column['options'][1] }}
        @else
            {{ Lang::has('admin::messages.yes')?trans('admin::messages.yes'):'Yes' }}
        @endif
    @else
        @if ( isset( $column['options'][0] ) )
            {{ $column['options'][0] }}
        @else
            {{ Lang::has('admin::messages.no')?trans('admin::messages.no'):'No' }}
        @endif
    @endif
</td>
