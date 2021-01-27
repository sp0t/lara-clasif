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
@extends('errors.layouts.master')

@section('search')
	@parent
	@include('errors.layouts.inc.search')
@endsection

@section('content')
	@include('common.spacer')
	<div class="main-container inner-page">
		<div class="container">
			<div class="section-content">
				<div class="row">

					<div class="col-md-12 page-content">

						<div class="error-page" style="margin: 100px 0;">
							<h2 class="headline text-center" style="font-size: 180px; float: none;"> 500</h2>
							<div class="text-center m-l-0" style="margin-top: 60px;">
								<h3 class="m-t-0"><i class="fa fa-warning"></i> 500 Internal Server Error.</h3>
								<p>
									<?php
									$defaultErrorMessage = "An internal server error has occurred. If the error persists please contact the development team.";
									?>
									{!! isset($exception) ? ($exception->getMessage() ? $exception->getMessage() : $defaultErrorMessage) : $defaultErrorMessage !!}
								</p>
							</div>
						</div>

					</div>

				</div>
			</div>

            <?php
				$requirements = [];
				if (!version_compare(PHP_VERSION, '7.1.3', '>=')) {
					$requirements[] = 'PHP 7.1.3 or higher is required.';
				}
				if (!extension_loaded('openssl')) {
					$requirements[] = 'OpenSSL PHP Extension is required.';
				}
				if (!extension_loaded('mbstring')) {
					$requirements[] = 'Mbstring PHP Extension is required.';
				}
				if (!extension_loaded('pdo')) {
					$requirements[] = 'PDO PHP Extension is required.';
				}
				if (!extension_loaded('tokenizer')) {
					$requirements[] = 'Tokenizer PHP Extension is required.';
				}
				if (!extension_loaded('xml')) {
					$requirements[] = 'XML PHP Extension is required.';
				}
				if (!extension_loaded('fileinfo')) {
					$requirements[] = 'PHP Fileinfo Extension is required.';
				}
				if (!(extension_loaded('gd') && function_exists('gd_info'))) {
					$requirements[] = 'PHP GD Library is required.';
				}
            ?>
			@if (isset($requirements))
			<div class="row">
				<div class="col-md-12">
					<ul class="installation">
						@foreach ($requirements as $key => $item)
							<li>
								<i class="icon-cancel text-danger"></i>
								<h5 class="title-5">
									Error #{{ $key }}
								</h5>
								<p>
									{{ $item }}
								</p>
							</li>
						@endforeach
					</ul>
				</div>
			</div>
			@endif
			
		</div>
	</div>
	<!-- /.main-container -->
@endsection
