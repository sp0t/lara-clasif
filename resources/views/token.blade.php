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
			<div class="row">

				@if (isset($errors) and $errors->any())
					<div class="col-xl-12">
						<div class="alert alert-danger">
							<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
							<ul class="list list-check">
								@foreach ($errors->all() as $error)
									<li>{{ $error }}</li>
								@endforeach
							</ul>
						</div>
					</div>
				@endif

				@if (session('code'))
					<div class="col-xl-12">
						<div class="alert alert-danger">
							<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
							<p>{{ session('code') }}</p>
						</div>
					</div>
				@endif

				@if (Session::has('flash_notification'))
					<div class="col-xl-12">
						<div class="row">
							<div class="col-xl-12">
								@include('flash::message')
							</div>
						</div>
					</div>
				@endif
					
				<div class="col-xl-12">
					<div class="alert alert-info">
						{{ getTokenMessage() }}:
					</div>
				</div>

				<div class="col-lg-5 col-md-8 col-sm-10 col-xs-12 login-box">
					<div class="card card-default">
						<div class="panel-intro text-center">
							<h2 class="logo-title"><strong>{{ t('Code') }}</strong></h2>
						</div>
						
						<div class="card-body">
							<form id="tokenForm" role="form" method="POST" action="{{ lurl(getRequestPath('verify/.*')) }}">
								{!! csrf_field() !!}
								
								<!-- code -->
								<?php $codeError = (isset($errors) and $errors->has('code')) ? ' is-invalid' : ''; ?>
								<div class="form-group">
									<label for="code" class="col-form-label">{{ getTokenLabel() }}:</label>
									<div class="input-icon">
										<i class="fa icon-lock-2"></i>
										<input id="code"
											   name="code"
											   type="text"
											   placeholder="{{ t('Enter the validation code') }}"
											   class="form-control{{ $codeError }}"
											   value="{{ old('code') }}"
										>
									</div>
								</div>
								
								<div class="form-group">
									<button id="tokenBtn" type="submit" class="btn btn-primary btn-lg btn-block">{{ t('Submit') }}</button>
								</div>
							</form>
						</div>
						
						<div class="card-footer text-center">
							&nbsp;
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
@endsection

@section('after_scripts')
	<script>
		$(document).ready(function () {
			$("#tokenBtn").click(function () {
				$("#tokenForm").submit();
				return false;
			});
		});
	</script>
@endsection