<div class="box box-primary">
	<div class="box-header with-border">
		<h3 class="box-title">{{ trans('admin::messages.Latest Users') }}</h3>
		
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
					<th>{{ trans('admin::messages.Name') }}</th>
					<th class="td-nowrap">{{ trans('admin::messages.Country') }}</th>
					<th class="td-nowrap">{{ trans('admin::messages.Status') }}</th>
					<th class="td-nowrap">{{ trans('admin::messages.Date') }}</th>
				</tr>
				</thead>
				<tbody>
				@if ($latestUsers->count() > 0)
					@foreach($latestUsers as $user)
						<tr>
							<td class="td-nowrap"><a href="{{ admin_url('users/' . $user->id . '/edit') }}">{{ $user->id }}</a></td>
							<td>
								<a href="{{ admin_url('users/' . $user->id . '/edit') }}">
									{{ \Illuminate\Support\Str::limit($user->name, 70) }}
								</a>
							</td>
							<td class="td-nowrap">{!! getCountryFlag($user) !!}</td>
							<td class="td-nowrap">
								@if (isVerifiedUser($user))
									<span class="label label-success">{{ trans('admin::messages.Activated') }}</span>
								@else
									<span class="label label-warning">{{ trans('admin::messages.Unactivated') }}</span>
								@endif
							</td>
							<td class="td-nowrap">
								<?php
									try {
										$user->created_at = (new \Date($user->created_at))->timezone(config('app.timezone'));
									} catch (\Exception $e) {}
								?>
								<div class="sparkbar" data-color="#00a65a" data-height="20">
									{{ $user->created_at->formatLocalized(config('settings.app.default_datetime_format')) }}
								</div>
							</td>
						</tr>
					@endforeach
				@else
					<tr>
						<td colspan="5">
							{{ trans('admin::messages.No users found') }}
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
		<a href="{{ admin_url('users') }}" class="btn btn-sm btn-default btn-flat pull-right">{{ trans('admin::messages.View All Users') }}</a>
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
