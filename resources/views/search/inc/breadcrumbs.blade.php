<div class="container">
	<nav aria-label="breadcrumb" role="navigation" class="search-breadcrumb">
		<ol class="breadcrumb">
			<li class="breadcrumb-item"><a href="{{ lurl('/') }}"><i class="icon-home fa"></i></a></li>
			<li class="breadcrumb-item">
				<?php $attr = ['countryCode' => config('country.icode')]; ?>
				<a href="{{ lurl(trans('routes.v-search', $attr), $attr) }}">
					{{ config('country.name') }}
				</a>
			</li>
			@if (isset($bcTab) and count($bcTab) > 0)
				@foreach($bcTab as $key => $value)
                    <?php $value = collect($value); ?>
					@if ($value->has('position') and $value->get('position') > count($bcTab)+1)
						<li class="breadcrumb-item active">
							{!! $value->get('name') !!}
							&nbsp;
							@if (isset($city) or isset($admin))
								<a href="#browseAdminCities" id="dropdownMenu1" data-toggle="modal"> <span class="caret"></span></a>
							@endif
						</li>
					@else
						<li class="breadcrumb-item"><a href="{{ $value->get('url') }}">{!! $value->get('name') !!}</a></li>
					@endif
				@endforeach
			@endif
		</ol>
	</nav>
</div>
