@if (
		config('settings.security.recaptcha_activation')
		and config('recaptcha.site_key')
		and config('recaptcha.secret_key')
	)
	@if (config('recaptcha.version') == 'v2')
		<!-- recaptcha -->
		@if (isFromAdminPanel())
			
			<?php $recaptchaError = (isset($errors) and $errors->has('g-recaptcha-response')) ? ' has-error' : ''; ?>
			<div class="form-group required{{ $recaptchaError }}">
				<div class="no-label">
					{!! recaptchaHtmlFormSnippet() !!}
				</div>
				
				@if ($errors->has('g-recaptcha-response'))
					<span class="help-block{{ $recaptchaError }}">
						<strong>{{ $errors->first('g-recaptcha-response') }}</strong>
					</span>
				@endif
			</div>
		
		@else
			
			<?php $recaptchaError = (isset($errors) and $errors->has('g-recaptcha-response')) ? ' is-invalid' : ''; ?>
			@if (isset($colLeft) and isset($colRight))
				<div class="form-group row required{{ $recaptchaError }}">
					<label class="{{ $colLeft }} col-form-label" for="g-recaptcha-response">
						@if (isset($label) and $label == true)
							{{ t('We do not like robots') }}
						@endif
					</label>
					<div class="{{ $colRight }}">
						{!! recaptchaHtmlFormSnippet() !!}
					</div>
				</div>
			@else
				@if (isset($label) and $label == true)
					<div class="form-group required{{ $recaptchaError }}">
						<label class="control-label" for="g-recaptcha-response">{{ t('We do not like robots') }}</label>
						<div>
							{!! recaptchaHtmlFormSnippet() !!}
						</div>
					</div>
				@elseif (isset($noLabel) and $noLabel == true)
					<div class="form-group required{{ $recaptchaError }}">
						<div class="no-label">
							{!! recaptchaHtmlFormSnippet() !!}
						</div>
					</div>
				@else
					<div class="form-group required{{ $recaptchaError }}">
						<div>
							{!! recaptchaHtmlFormSnippet() !!}
						</div>
					</div>
				@endif
			@endif
		
		@endif
	
	@else
		<input type="hidden" name="g-recaptcha-response" id="gRecaptchaResponse">
	@endif
@endif

@section('recaptcha_scripts')
	@if (
		config('settings.security.recaptcha_activation')
		and config('recaptcha.site_key')
		and config('recaptcha.secret_key')
	)
		<style>
			.is-invalid .g-recaptcha iframe,
			.has-error .g-recaptcha iframe {
				border: 1px solid #f85359;
			}
		</style>
		@if (config('recaptcha.version') == 'v3')
			<script type="text/javascript">
				function myCustomValidation(token){
					/* read HTTP status */
					/* console.log(token); */
					
					if ($('#gRecaptchaResponse').length) {
						$('#gRecaptchaResponse').val(token);
					}
				}
			</script>
			{!! recaptchaApiV3JsScriptTag([
				'action' 		    => request()->path(),
				'custom_validation' => 'myCustomValidation'
			]) !!}
		@else
			{!! recaptchaApiJsScriptTag() !!}
		@endif
	@endif
@endsection