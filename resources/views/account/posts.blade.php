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

				@if (Session::has('flash_notification'))
					<div class="col-xl-12">
						<div class="row">
							<div class="col-xl-12">
								@include('flash::message')
							</div>
						</div>
					</div>
				@endif

				<div class="col-md-3 page-sidebar">
					@include('account/inc/sidebar')
				</div>
				<!--/.page-sidebar-->

				<div class="col-md-9 page-content">
					<div class="inner-box">
						@if ($pagePath=='my-posts')
							<h2 class="title-2"><i class="icon-docs"></i> {{ t('My Ads') }} </h2>
						@elseif ($pagePath=='archived')
							<h2 class="title-2"><i class="icon-folder-close"></i> {{ t('Archived ads') }} </h2>
						@elseif ($pagePath=='favourite')
							<h2 class="title-2"><i class="icon-heart-1"></i> {{ t('Favourite jobs') }} </h2>
						@elseif ($pagePath=='pending-approval')
							<h2 class="title-2"><i class="icon-hourglass"></i> {{ t('Pending approval') }} </h2>
						@else
							<h2 class="title-2"><i class="icon-docs"></i> {{ t('Posts') }} </h2>
						@endif

						<div class="table-responsive">
							<form name="listForm" method="POST" action="{{ lurl('account/'.$pagePath.'/delete') }}">
								{!! csrf_field() !!}
								<div class="table-action">
									<label for="checkAll">
										<input type="checkbox" id="checkAll">
										{{ t('Select') }}: {{ t('All') }} |
										<button type="submit" class="btn btn-sm btn-default delete-action">
											<i class="fa fa-trash"></i> {{ t('Delete') }}
                                        </button>
									</label>
									<div class="table-search pull-right col-sm-7">
										<div class="form-group">
											<div class="row">
												<label class="col-sm-5 control-label text-right">{{ t('Search') }} <br>
													<a title="clear filter" class="clear-filter" href="#clear">[{{ t('clear') }}]</a>
												</label>
												<div class="col-sm-7 searchpan">
													<input type="text" class="form-control" id="filter">
												</div>
											</div>
										</div>
									</div>
								</div>
								<table id="addManageTable" class="table table-striped table-bordered add-manage-table table demo"
									   data-filter="#filter" data-filter-text-only="true">
									<thead>
									<tr>
										<th data-type="numeric" data-sort-initial="true"></th>
										<th> {{ t('Photo') }}</th>
										<th data-sort-ignore="true"> {{ t('Ads Details') }} </th>
										<th data-type="numeric"> --</th>
										<th> {{ t('Option') }}</th>
									</tr>
									</thead>
									<tbody>

									<?php
                                    if (isset($posts) && $posts->count() > 0):
									foreach($posts as $key => $post):
										// Fixed 1
										if ($pagePath == 'favourite') {
											if (isset($post->post)) {
												if (!empty($post->post)) {
													$post = $post->post;
												} else {
													continue;
												}
											} else {
												continue;
											}
										}

										// Fixed 2
										if (!$countries->has($post->country_code)) continue;

										// Get Post's URL
										$postUrl = \App\Helpers\UrlGen::post($post);

										// Get country flag
										$countryFlagPath = 'images/flags/16/' . strtolower($post->country_code) . '.png';
									?>
									<tr>
										<td style="width:2%" class="add-img-selector">
											<div class="checkbox">
												<label><input type="checkbox" name="entries[]" value="{{ $post->id }}"></label>
											</div>
										</td>
										<td style="width:14%" class="add-img-td">
											<a href="{{ $postUrl }}">
												<img class="img-thumbnail img-fluid" src="{{ imgUrl(\App\Models\Post::getLogo($post->logo), 'medium') }}" alt="img">
											</a>
										</td>
										<td style="width:58%" class="items-details-td">
											<div>
												<p>
                                                    <strong>
                                                        <a href="{{ $postUrl }}" title="{{ $post->title }}">{{ \Illuminate\Support\Str::limit($post->title, 40) }}</a>
                                                    </strong>
													@if (in_array($pagePath, ['my-posts', 'archived', 'pending-approval']))
														@if (isset($post->latestPayment) and !empty($post->latestPayment))
															@if (isset($post->latestPayment->package) and !empty($post->latestPayment->package))
																<?php
																if ($post->featured == 1) {
																	$color = $post->latestPayment->package->ribbon;
																	$packageInfo = '';
																} else {
																	$color = '#ddd';
																	$packageInfo = ' (' . t('Expired') . ')';
																}
																?>
																<i class="fa fa-check-circle tooltipHere" style="color: {{ $color }};" title="" data-placement="bottom"
																   data-toggle="tooltip" data-original-title="{{ $post->latestPayment->package->short_name . $packageInfo }}"></i>
															@endif
														@endif
													@endif
                                                </p>
												<p>
													<strong><i class="icon-clock" title="{{ t('Posted On') }}"></i></strong>&nbsp;
													{{ $post->created_at->formatLocalized(config('settings.app.default_datetime_format')) }}
												</p>
												<p>
													<strong><i class="icon-eye" title="{{ t('Visitors') }}"></i></strong> {{ $post->visits ?? 0 }}
													<strong><i class="icon-location-2" title="{{ t('Located In') }}"></i></strong> {{ !empty($post->city) ? $post->city->name : '-' }}
													@if (file_exists(public_path($countryFlagPath)))
														<img src="{{ url($countryFlagPath) }}" data-toggle="tooltip" title="{{ $post->country->name }}">
													@endif
												</p>
											</div>
										</td>
										<td style="width:16%" class="price-td">
											<div>
												<strong>
													@if ($post->salary_min > 0)
														{!! \App\Helpers\Number::money($post->salary_min) !!}
													@else
														{!! \App\Helpers\Number::money(' --') !!}
													@endif
												</strong>
											</div>
										</td>
										<td style="width:10%" class="action-td">
											<div>
												@if (in_array($pagePath, ['my-posts']) and $post->user_id==$user->id and $post->archived==0)
													<p>
                                                        <a class="btn btn-primary btn-sm" href="{{ \App\Helpers\UrlGen::editPost($post) }}">
                                                            <i class="fa fa-edit"></i> {{ t('Edit') }}
                                                        </a>
                                                    </p>
												@endif
												@if (in_array($pagePath, ['my-posts']) and isVerifiedPost($post) and $post->archived==0)
													<p>
														<a class="btn btn-warning btn-sm confirm-action" href="{{ lurl('account/'.$pagePath.'/'.$post->id.'/offline') }}">
															<i class="icon-eye-off"></i> {{ t('Offline') }}
														</a>
													</p>
												@endif
												@if (in_array($pagePath, ['archived']) and $post->user_id==$user->id and $post->archived==1)
													<p>
                                                        <a class="btn btn-info btn-sm confirm-action" href="{{ lurl('account/'.$pagePath.'/'.$post->id.'/repost') }}">
                                                            <i class="fa fa-recycle"></i> {{ t('Repost') }}
                                                        </a>
                                                    </p>
												@endif
												<p>
                                                    <a class="btn btn-danger btn-sm delete-action" href="{{ lurl('account/'.$pagePath.'/'.$post->id.'/delete') }}">
                                                        <i class="fa fa-trash"></i> {{ t('Delete') }}
                                                    </a>
                                                </p>
											</div>
										</td>
									</tr>
									<?php endforeach; ?>
                                    <?php endif; ?>
									</tbody>
								</table>
							</form>
						</div>
							
                        <nav>
                            {{ (isset($posts)) ? $posts->links() : '' }}
                        </div>

					</div>
				</div>
			</div>
		</div>
	</div>
@endsection

@section('after_styles')
	<style>
		.action-td p {
			margin-bottom: 5px;
		}
	</style>
@endsection

@section('after_scripts')
	<script src="{{ url('assets/js/footable.js?v=2-0-1') }}" type="text/javascript"></script>
	<script src="{{ url('assets/js/footable.filter.js?v=2-0-1') }}" type="text/javascript"></script>
	<script type="text/javascript">
		$(function () {
			$('#addManageTable').footable().bind('footable_filtering', function (e) {
				var selected = $('.filter-status').find(':selected').text();
				if (selected && selected.length > 0) {
					e.filter += (e.filter && e.filter.length > 0) ? ' ' + selected : selected;
					e.clear = !e.filter;
				}
			});

			$('.clear-filter').click(function (e) {
				e.preventDefault();
				$('.filter-status').val('');
				$('table.demo').trigger('footable_clear_filter');
			});

			$('#checkAll').click(function () {
				checkAll(this);
			});
			
			$('a.delete-action, button.delete-action, a.confirm-action').click(function(e)
			{
				e.preventDefault(); /* prevents the submit or reload */
				var confirmation = confirm("{{ t('confirm_this_action') }}");
				
				if (confirmation) {
					if( $(this).is('a') ){
						var url = $(this).attr('href');
						if (url !== 'undefined') {
							redirect(url);
						}
					} else {
						$('form[name=listForm]').submit();
					}
					
				}
				
				return false;
			});
		});
	</script>
	<!-- include custom script for ads table [select all checkbox]  -->
	<script>
		function checkAll(bx) {
			var chkinput = document.getElementsByTagName('input');
			for (var i = 0; i < chkinput.length; i++) {
				if (chkinput[i].type == 'checkbox') {
					chkinput[i].checked = bx.checked;
				}
			}
		}
	</script>
@endsection
