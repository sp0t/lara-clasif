@if (config('settings.single.activation_facebook_comments') and config('services.facebook.client_id') and !$commentsAreDisabledByUser)
	<div class="container">
		<div id="fb-root"></div>
		<script>
			(function (d, s, id) {
				var js, fjs = d.getElementsByTagName(s)[0];
				if (d.getElementById(id)) return;
				js = d.createElement(s);
				js.id = id;
				js.src = "//connect.facebook.net/{{ config('lang.locale', 'en_US') }}/sdk.js#xfbml=1&version=v2.5&appId={{ config('services.facebook.client_id') }}";
				fjs.parentNode.insertBefore(js, fjs);
			}(document, 'script', 'facebook-jssdk'));
		</script>
		<div class="fb-comments" data-href="{{ request()->url() }}" data-width="100%" data-numposts="5"></div>
	</div>
@endif