@if (config('services.googlemaps.key'))
<div class="intro-inner">
	<div class="contact-intro">
		<div class="w100 map">
			<iframe id="googleMaps" src="" width="100%" height="350" frameborder="0" style="border:0; pointer-events:none;"></iframe>
		</div>
	</div>
</div>
@endif

@section('after_scripts')
	@parent
	@if (config('services.googlemaps.key'))
	<script src="https://maps.googleapis.com/maps/api/js?key={{ config('services.googlemaps.key') }}" type="text/javascript"></script>
	<script>
		$(document).ready(function () {
			getGoogleMaps(
				'<?php echo config('services.googlemaps.key'); ?>',
				'<?php echo (!is_null($city)) ? $city->name . ', ' . config('country.name') : config('country.name') ?>',
				'<?php echo config('app.locale'); ?>'
			);
		})
	</script>
	@endif
@endsection