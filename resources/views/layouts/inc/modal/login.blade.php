<div class="modal fade" id="quickLogin" tabindex="-1" role="dialog">
	<div class="modal-dialog  modal-sm">
		<div class="modal-content">
			
			<div class="modal-header">
				<h4 class="modal-title"><i class="icon-login fa"></i> {{ t('Log In') }} </h4>
				
				<button type="button" class="close" data-dismiss="modal">
					<span aria-hidden="true">&times;</span>
					<span class="sr-only">{{ t('Close') }}</span>
				</button>
			</div>
			
			<form role="form" method="POST" action="{{ lurl(trans('routes.login')) }}">
				{!! csrf_field() !!}
				<div class="modal-body">
					
					@if (isset($errors) and $errors->any() and old('quickLoginForm')=='1')
						<div class="alert alert-danger">
							<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
							<ul class="list list-check">
								@foreach($errors->all() as $error)
									<li>{{ $error }}</li>
								@endforeach
							</ul>
						</div>
					@endif
					
					@if (
						config('settings.social_auth.social_login_activation')
						and (
							(config('settings.social_auth.facebook_client_id') and config('settings.social_auth.facebook_client_secret'))
							or (config('settings.social_auth.linkedin_client_id') and config('settings.social_auth.linkedin_client_secret'))
							or (config('settings.social_auth.twitter_client_id') and config('settings.social_auth.twitter_client_secret'))
							or (config('settings.social_auth.google_client_id') and config('settings.social_auth.google_client_secret'))
							)
						)
						<div class="row mb-3 d-flex justify-content-center pl-2 pr-2">
							@if (config('settings.social_auth.facebook_client_id') and config('settings.social_auth.facebook_client_secret'))
							<div class="col-xl-6 col-lg-6 col-md-6 col-sm-12 col-xs-12 mb-1 pl-1 pr-1">
								<div class="col-xl-12 col-md-12 col-sm-12 col-xs-12 btn btn-lg btn-fb">
									<a href="{{ lurl('auth/facebook') }}" class="btn-fb" title="{!! strip_tags(t('Login with Facebook')) !!}">
										<i class="icon-facebook-rect"></i> Facebook
									</a>
								</div>
							</div>
							@endif
							@if (config('settings.social_auth.linkedin_client_id') and config('settings.social_auth.linkedin_client_secret'))
							<div class="col-xl-6 col-lg-6 col-md-6 col-sm-12 col-xs-12 mb-1 pl-1 pr-1">
								<div class="col-xl-12 col-md-12 col-sm-12 col-xs-12 btn btn-lg btn-lkin">
									<a href="{{ lurl('auth/linkedin') }}" class="btn-lkin" title="{!! strip_tags(t('Login with LinkedIn')) !!}">
										<i class="icon-linkedin"></i> LinkedIn
									</a>
								</div>
							</div>
							@endif
							@if (config('settings.social_auth.twitter_client_id') and config('settings.social_auth.twitter_client_secret'))
							<div class="col-xl-6 col-lg-6 col-md-6 col-sm-12 col-xs-12 mb-1 pl-1 pr-1">
								<div class="col-xl-12 col-md-12 col-sm-12 col-xs-12 btn btn-lg btn-tw">
									<a href="{{ lurl('auth/twitter') }}" class="btn-tw" title="{!! strip_tags(t('Login with Twitter')) !!}">
										<i class="icon-twitter-bird"></i> Twitter
									</a>
								</div>
							</div>
							@endif
							@if (config('settings.social_auth.google_client_id') and config('settings.social_auth.google_client_secret'))
							<div class="col-xl-6 col-lg-6 col-md-6 col-sm-12 col-xs-12 mb-1 pl-1 pr-1">
								<div class="col-xl-12 col-md-12 col-sm-12 col-xs-12 btn btn-lg btn-danger">
									<a href="{{ lurl('auth/google') }}" class="btn-danger" title="{!! strip_tags(t('Login with Google')) !!}">
										<i class="icon-googleplus-rect"></i> Google
									</a>
								</div>
							</div>
							@endif
						</div>
					@endif
					
					<?php
					$loginValue = (session()->has('login')) ? session('login') : old('login');
					$loginField = getLoginField($loginValue);
					if ($loginField == 'phone') {
						$loginValue = phoneFormat($loginValue, old('country', config('country.code')));
					}
					?>
				<!-- login -->
					<?php $loginError = (isset($errors) and $errors->has('login')) ? ' is-invalid' : ''; ?>
					<div class="form-group">
						<label for="login" class="control-label">{{ t('Login') . ' (' . getLoginLabel() . ')' }}</label>
						<div class="input-group">
							<div class="input-group-prepend">
								<span class="input-group-text"><i class="icon-user fa"></i></span>
							</div>
							<input id="mLogin" name="login" type="text" placeholder="{{ getLoginLabel() }}" class="form-control{{ $loginError }}" value="{{ $loginValue }}">
						</div>
					</div>
					
					<!-- password -->
					<?php $passwordError = (isset($errors) and $errors->has('password')) ? ' is-invalid' : ''; ?>
					<div class="form-group">
						<label for="password" class="control-label">{{ t('Password') }}</label>
						<div class="input-group show-pwd-group">
							<div class="input-group-prepend">
								<span class="input-group-text"><i class="icon-lock fa"></i></span>
							</div>
							<input id="mPassword" name="password" type="password" class="form-control{{ $passwordError }}" placeholder="{{ t('Password') }}" autocomplete="off">
							<span class="icon-append show-pwd">
								<button type="button" class="eyeOfPwd">
									<i class="far fa-eye-slash"></i>
								</button>
							</span>
						</div>
					</div>
					
					<!-- remember -->
					<?php $rememberError = (isset($errors) and $errors->has('remember')) ? ' is-invalid' : ''; ?>
					<div class="form-group">
						<label class="checkbox form-check-label pull-left mt-2" style="font-weight: normal;">
							<input type="checkbox" value="1" name="remember" id="mRemember" class="{{ $rememberError }}"> {{ t('Keep me logged in') }}
						</label>
						<p class="pull-right mt-2">
							<a href="{{ lurl('password/reset') }}"> {{ t('Lost your password?') }} </a> / <a href="{{ lurl(trans('routes.register')) }}">{{ t('Register') }}</a>
						</p>
						<div style=" clear:both"></div>
					</div>
					
					@include('layouts.inc.tools.recaptcha', ['label' => true])
					
					<input type="hidden" name="quickLoginForm" value="1">
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-default" data-dismiss="modal">{{ t('Cancel') }}</button>
					<button type="submit" class="btn btn-success pull-right">{{ t('Log In') }}</button>
				</div>
			</form>
		
		</div>
	</div>
</div>
