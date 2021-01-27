{{--
 * JobClass - Job Board Web Application
 * Copyright (c) BedigitCom. All Rights Reserved
 *
 * Website: http://www.bedigit.com
 *
 * LICENSE
 * -------
 * This software is furnished under a license and may be used and copied
 * only in accordance with the terms of such license and with the inclusion
 * of the above copyright notice. If you Purchased from Codecanyon,
 * Please read the full License from here - http://codecanyon.net/licenses/standard
--}}
@extends('layouts.master')

@section('content')
	@include('common.spacer')
	<div class="main-container">
		<div class="container">
			<div class="row clearfix">
				
				@if (isset($errors) and $errors->any())
					<div class="col-md-12">
						<div class="alert alert-danger">
							<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
							<h5><strong>{{ t('Oops ! An error has occurred. Please correct the red fields in the form') }}</strong></h5>
							<ul class="list list-check">
								@foreach ($errors->all() as $error)
									<li>{{ $error }}</li>
								@endforeach
							</ul>
						</div>
					</div>
				@endif
					
				<div class="col-md-12">
					<div class="contact-form">
						
						<h3 class="gray mt-0">
							<strong><a href="{{ \App\Helpers\UrlGen::post($post) }}">{{ $title }}</a></strong>
						</h3>
						<hr class="mt-1">
						<h4>{{ t('There\'s something wrong with this ad?') }}</h4>
						
						<form role="form" method="POST" action="{{ lurl('posts/' . $post->id . '/report') }}">
							{!! csrf_field() !!}
							<fieldset>
								<!-- report_type_id -->
								<?php $reportTypeIdError = (isset($errors) and $errors->has('report_type_id')) ? ' is-invalid' : ''; ?>
								<div class="form-group required">
									<label for="report_type_id" class="control-label{{ $reportTypeIdError }}">{{ t('Reason') }} <sup>*</sup></label>
									<select id="reportTypeId" name="report_type_id" class="form-control selecter{{ $reportTypeIdError }}">
										<option value="">{{ t('Select a reason') }}</option>
										@foreach($reportTypes as $reportType)
											<option value="{{ $reportType->id }}" {{ (old('report_type_id', 0)==$reportType->id) ? 'selected="selected"' : '' }}>
												{{ $reportType->name }}
											</option>
										@endforeach
									</select>
								</div>
								
								<!-- email -->
								@if (auth()->check() and isset(auth()->user()->email))
									<input type="hidden" name="email" value="{{ auth()->user()->email }}">
								@else
									<?php $emailError = (isset($errors) and $errors->has('email')) ? ' is-invalid' : ''; ?>
									<div class="form-group required">
										<label for="email" class="control-label">{{ t('Your E-mail') }} <sup>*</sup></label>
										<div class="input-group">
											<div class="input-group-prepend">
												<span class="input-group-text"><i class="icon-mail"></i></span>
											</div>
											<input id="email" name="email" type="text" maxlength="60" class="form-control{{ $emailError }}" value="{{ old('email') }}">
										</div>
									</div>
								@endif
							
								<!-- message -->
								<?php $messageError = (isset($errors) and $errors->has('message')) ? ' is-invalid' : ''; ?>
								<div class="form-group required">
									<label for="message" class="control-label">{{ t('Message') }} <sup>*</sup> <span class="text-count"></span></label>
									<textarea id="message" name="message" class="form-control{{ $messageError }}" rows="10">{{ old('message') }}</textarea>
								</div>
								
								@include('layouts.inc.tools.recaptcha', ['label' => true])
			
								<input type="hidden" name="post_id" value="{{ $post->id }}">
								<input type="hidden" name="abuseForm" value="1">
								
								<div class="form-group">
									<a href="{{ rawurldecode(URL::previous()) }}" class="btn btn-default btn-lg">{{ t('Back') }}</a>
									<button type="submit" class="btn btn-primary btn-lg">{{ t('Send Report') }}</button>
								</div>
							</fieldset>
						</form>
					</div>
				</div>
				
			</div>
		</div>
	</div>
@endsection

@section('after_scripts')
	<script src="{{ url('assets/js/form-validation.js') }}"></script>
@endsection