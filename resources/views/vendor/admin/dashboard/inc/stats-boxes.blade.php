<div class="row">
	@if (isset($countUnactivatedPosts))
	<div class="col-lg-3 col-xs-6">
		<div class="small-box bg-yellow">
			<div class="inner">
				<h3>{{ $countUnactivatedPosts }}</h3>
				<p>{{ trans('admin::messages.Unactivated ads') }}</p>
			</div>
			<div class="icon">
				<i class="fa fa-edit"></i>
			</div>
			<a href="{{ admin_url('posts?active=0') }}" class="small-box-footer">
				{{ trans('admin::messages.View more') }} <i class="fa fa-arrow-circle-right"></i>
			</a>
		</div>
	</div>
	@endif
	
	@if (isset($countActivatedPosts))
	<div class="col-lg-3 col-xs-6">
		<div class="small-box bg-green">
			<div class="inner">
				<h3>{{ $countActivatedPosts }}</h3>
				<p>{{ trans('admin::messages.Activated ads') }}</p>
			</div>
			<div class="icon">
				<i class="fa fa-check-circle-o"></i>
			</div>
			<a href="{{ admin_url('posts?active=1') }}" class="small-box-footer">
				{{ trans('admin::messages.View more') }} <i class="fa fa-arrow-circle-right"></i>
			</a>
		</div>
	</div>
	@endif
	
	@if (isset($countUsers))
	<div class="col-lg-3 col-xs-6">
		<div class="small-box bg-aqua">
			<div class="inner">
				<h3>{{ $countUsers }}</h3>
				<p>{{ mb_ucfirst(trans('admin::messages.users')) }}</p>
			</div>
			<div class="icon">
				<i class="ion ion-person-add"></i>
			</div>
			<a href="{{ admin_url('users') }}" class="small-box-footer">
				{{ trans('admin::messages.View more') }} <i class="fa fa-arrow-circle-right"></i>
			</a>
		</div>
	</div>
	@endif
	
	@if (isset($countCountries))
	<div class="col-lg-3 col-xs-6">
		<div class="small-box bg-red">
			<div class="inner">
				<h3>{{ $countCountries }}</h3>
				<p>
					{{ trans('admin::messages.Activated countries') }}
                    <span class="label label-default tooltipHere"
                          title="" data-placement="bottom" data-toggle="tooltip" type="button"
                          data-original-title="{!! trans('admin::messages.To launch your website for several countries you need to activate these countries.') . ' ' . trans('admin::messages.By disabling or removing a country the ads of this country (also) will be deleted.') !!}">
                        {{ trans('admin::messages.Help') }} <i class="fa fa-support"></i>
                    </span>
				</p>
			</div>
			<div class="icon">
				<i class="fa fa-globe"></i>
			</div>
			<a href="{{ admin_url('countries') }}" class="small-box-footer">
				{{ trans('admin::messages.View more') }} <i class="fa fa-arrow-circle-right"></i>
			</a>
		</div>
	</div>
	@endif
</div>

@push('dashboard_styles')
@endpush

@push('dashboard_scripts')
@endpush
