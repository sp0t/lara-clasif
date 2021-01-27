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
							<div class="col-lg-12">
								@include('flash::message')
							</div>
						</div>
					</div>
				@endif
				
				<div class="col-sm-3 page-sidebar">
					@include('account.inc.sidebar')
				</div>
				<!--/.page-sidebar-->
				
				<div class="col-sm-9 page-content">
					<nav class="inner-box">
						<h2 class="title-2"><i class="icon-mail"></i> {{ t('Messages') }} </h2>
						
						<div style="clear:both"></div>
						
						<?php
						if (isset($conversation) && !empty($conversation) > 0):
						
							// Conversation URLs
							$consUrl = lurl('account/conversations');
							$conDelAllUrl = lurl('account/conversations/' . $conversation->id . '/messages/delete');
						?>
						<div class="table-responsive">
							<form name="listForm" method="POST" action="{{ $conDelAllUrl }}">
								{!! csrf_field() !!}
								<div class="table-action">
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
								
								<table id="addManageTable" class="table table-striped table-bordered add-manage-table table demo" data-filter="#filter" data-filter-text-only="true">
									<thead>
									<tr>
										<th data-sort-ignore="true" colspan="3">
											<a href="{{ $consUrl }}"><i class="icon-level-up"></i> {{ t('Back') }}</a>&nbsp;|&nbsp;
											{{ t("Conversation") }} #{{ $conversation->id }}&nbsp;|&nbsp;
											{{ $conversation->subject }}
										</th>
									</tr>
									</thead>
									<tbody>
									<!-- Main Conversation -->
									<tr>
										<td colspan="3">
											<strong>{{ t("Sender's Name") }}:</strong> {{ $conversation->from_name ?? '--' }}<br>
											<strong>{{ t("Sender's Email") }}:</strong> {{ $conversation->from_email ?? '--' }}<br>
											<strong>{{ t("Sender's Phone") }}:</strong> {{ $conversation->from_phone ?? '--' }}<br>
											<hr>
											{!! nl2br($conversation->message) !!}
											@if (!empty($conversation->filename) and $disk->exists($conversation->filename))
												<br><br><a class="btn btn-info" href="{{ fileUrl($conversation->filename) }}">{{ t('Download') }}</a>
											@endif
											<hr>
											<a class="btn btn-primary" href="#" data-toggle="modal" data-target="#replyTo{{ $conversation->id }}">
												<i class="icon-reply"></i> {{ t('Reply') }}
											</a>
										</td>
									</tr>
									<!-- All Conversation's Messages -->
									<?php
									if (isset($messages) && $messages->count() > 0):
									foreach($messages as $key => $message):
									?>
									<tr>
										@if ($message->from_user_id == auth()->user()->id)
											<td class="add-img-selector">
												<div class="checkbox" style="width:2%">
													<label><input type="checkbox" name="entries[]" value="{{ $message->id }}"></label>
												</div>
											</td>
											<td style="width:88%;">
												<div style="word-break:break-all;">
													<strong>
														<i class="icon-reply"></i> {{ $message->from_name }}:
													</strong><br>
													{!! nl2br($message->message) !!}
													@if (!empty($message->filename) and $disk->exists($message->filename))
														<br><br><a class="btn btn-info" href="{{ fileUrl($message->filename) }}">{{ t('Download') }}</a>
													@endif
												</div>
											</td>
											<td class="action-td" style="width:10%">
												<div>
													<p>
														<?php $conDelUrl = lurl('account/conversations/' . $conversation->id . '/messages/' . $message->id . '/delete'); ?>
														<a class="btn btn-danger btn-sm delete-action" href="{{ $conDelUrl }}">
															<i class="fa fa-trash"></i> {{ t('Delete') }}
														</a>
													</p>
												</div>
											</td>
										@else
											<td colspan="3">
												<div style="word-break:break-all;">
													<strong>{{ $message->from_name }}:</strong><br>
													{!! nl2br($message->message) !!}
													@if (!empty($message->filename) and $disk->exists($message->filename))
														<br><br><a class="btn btn-info" href="{{ fileUrl($message->filename) }}">{{ t('Download') }}</a>
													@endif
												</div>
											</td>
										@endif
									</tr>
									<?php endforeach; ?>
									<?php endif; ?>
									</tbody>
								</table>
								
								@if (isset($messages) && $messages->count() > 0)
								<div class="table-action">
									<label for="checkAll">
										<input type="checkbox" id="checkAll">
										{{ t('Select') }}: {{ t('All') }} |
										<button type="submit" class="btn btn-sm btn-default delete-action">
											<i class="fa fa-trash"></i> {{ t('Delete') }}
										</button>
									</label>
								</div>
								@endif
								
							</form>
						</div>
						
						<nav>
							{{ (isset($messages)) ? $messages->links() : '' }}
						</nav>
						<?php endif; ?>
						
						<div style="clear:both"></div>
					
					</div>
				</div>
				<!--/.page-content-->
				
			</div>
			<!--/.row-->
		</div>
		<!--/.container-->
	</div>
	<!-- /.main-container -->
	
	@if (isset($conversation) && $conversation->count() > 0)
		@include('account.inc.reply-message')
	@endif

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