<div class="box box-primary">
	<div class="box-header with-border">
		<h3 class="box-title">{{ trans('admin::messages.Latest Ads') }}</h3>
		
		<div class="box-tools pull-right">
			<button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
		</div>
	</div>
	<!-- /.box-header -->
	<div class="box-body">
		<div class="table-responsive">
			<table class="table no-margin">
				<thead>
				<tr>
					<th class="td-nowrap">{{ trans('admin::messages.ID') }}</th>
					<th>{{ trans('admin::messages.Title') }}</th>
					<th class="td-nowrap">{{ trans('admin::messages.Country') }}</th>
					<th class="td-nowrap">{{ trans('admin::messages.Status') }}</th>
					<th class="td-nowrap">{{ trans('admin::messages.Date') }}</th>
				</tr>
				</thead>
				<tbody>
				@if ($latestPosts->count() > 0)
					@foreach($latestPosts as $post)
						<tr>
							<td class="td-nowrap">{{ $post->id }}</td>
							<td>{!! getPostUrl($post) !!}</td>
							<td class="td-nowrap">{!! getCountryFlag($post) !!}</td>
							<td class="td-nowrap">
								@if (isVerifiedPost($post))
									<span class="label label-success">{{ trans('admin::messages.Activated') }}</span>
								@else
									<span class="label label-warning">{{ trans('admin::messages.Unactivated') }}</span>
								@endif
							</td>
							<td class="td-nowrap">
								<?php
									try {
										$post->created_at = (new \Date($post->created_at))->timezone(config('app.timezone'));
									} catch (\Exception $e) {}
								?>
								<div class="sparkbar" data-color="#00a65a" data-height="20">
									{{ $post->created_at->formatLocalized(config('settings.app.default_datetime_format')) }}
								</div>
							</td>
						</tr>
					@endforeach
				@else
					<tr>
						<td colspan="5">
							{{ trans('admin::messages.No ads found') }}
						</td>
					</tr>
				@endif
				</tbody>
			</table>
		</div>
		<!-- /.table-responsive -->
	</div>
	<!-- /.box-body -->
	<div class="box-footer clearfix">
		<a href="{{ url(config('app.locale') . '/posts/create') }}" target="_blank" class="btn btn-sm btn-primary btn-flat pull-left">
			{{ trans('admin::messages.Post New Ad') }}
		</a>
		<a href="{{ admin_url('posts') }}" class="btn btn-sm btn-default btn-flat pull-right">{{ trans('admin::messages.View All Ads') }}</a>
	</div>
	<!-- /.box-footer -->
</div>

@push('dashboard_styles')
	<style>
		.td-nowrap {
			width: 10px;
			white-space: nowrap;
		}
	</style>
@endpush

@push('dashboard_scripts')
@endpush
