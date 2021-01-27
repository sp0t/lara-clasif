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
		<script>console.log("path:index")</script>
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
					@include('account.inc.sidebar')
				</div>
				<!--/.page-sidebar-->

				<div class="col-md-9 page-content">
					<div class="inner-box">
						<h2 class="title-2"><i class="icon-town-hall"></i> {{ t('My companies') }} </h2>
						<div class="mb30">
							<a href="{{ lurl('account/companies/create') }}" class="btn btn-default"><i class="icon-plus"></i> {{ t('Create a new company') }}</a>
						</div>
						<br>
						
						<div class="table-responsive">
							<form name="listForm" method="POST" action="{{ lurl('account/companies/delete') }}">
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
										<th> {{ t('Logo') }}</th>
										<th data-sort-ignore="true"> {{ t('Company Name') . ' / ' . t('Description') }} </th>
										<th data-type="numeric">{{ t('Ads') }}</th>
										<th> {{ t('Option') }}</th>
									</tr>
									</thead>
									<tbody>

									<?php
                                    if (isset($companies) && $companies->count() > 0):
									foreach($companies as $key => $company):
									?>
									<tr>
										<td style="width:2%" class="add-img-selector">
											<div class="checkbox">
												<label><input type="checkbox" name="entries[]" value="{{ $company->id }}"></label>
											</div>
										</td>
										<td style="width:14%" class="add-img-td">
											<a href="{{ lurl('account/companies/' . $company->id . '/edit') }}">
												<img class="img-thumbnail img-fluid" src="{{ imgUrl(\App\Models\Company::getLogo($company->logo), 'medium') }}" alt="img">
											</a>
										</td>
										<td style="width:58%" class="items-details-td">
											<div>
												<p>
                                                    <strong>
                                                        <a href="{{ lurl('account/companies/' . $company->id . '/edit') }}" title="{{ $company->title }}">
															{{ \Illuminate\Support\Str::limit($company->name, 40) }}
														</a>
                                                    </strong>
                                                </p>
												<p>
													<strong><i class="icon-info-circled-alt" title="{{ t('Description') }}"></i></strong>
													{{ \Illuminate\Support\Str::limit($company->description, 100) }}
												</p>
											</div>
										</td>
										<td style="width:16%" class="price-td">
											<div>
												<strong>
													<?php $attr = ['countryCode' => config('country.icode'), 'id' => $company->id]; ?>
													<a href="{{ lurl(trans('routes.v-search-company', $attr), $attr) }}">
														{{ $company->posts()->count() }}
													</a>
												</strong>
											</div>
										</td>
										<td style="width:10%" class="action-td">
											<div>
												@if ($company->user_id==$user->id)
													<p>
                                                        <a class="btn btn-primary btn-sm" href="{{ lurl('account/companies/' . $company->id . '/edit') }}">
                                                            <i class="fa fa-edit"></i> {{ t('Edit') }}
                                                        </a>
                                                    </p>
													<p>
														<a class="btn btn-danger btn-sm delete-action" href="{{ lurl('account/companies/' . $company->id . '/delete') }}">
															<i class="fa fa-trash"></i> {{ t('Delete') }}
														</a>
													</p>
												@endif
											</div>
										</td>
									</tr>
									<?php endforeach; ?>
                                    <?php endif; ?>
									</tbody>
								</table>
							</form>
						</div>
							
                        <div class="pagination-bar text-center">
                            {{ (isset($companies)) ? $companies->links() : '' }}
                        </div>

					</div>
				</div>
			</div>
		</div>
	</div>
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
			
			$('a.delete-action, button.delete-action').click(function(e)
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
