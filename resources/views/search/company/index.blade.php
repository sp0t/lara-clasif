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

@section('search')
	@parent
	@include('search.company.inc.search')
@endsection

@section('content')
	@include('common.spacer')
	<div class="main-container">
		<div class="container">
			
			<div class="col-lg-12 content-box">
				<div class="row row-featured row-featured-category row-featured-company">
					<div class="col-lg-12 box-title no-border">
						<div class="inner">
							<h2>
								<span class="title-3">{{ t('Companies List') }}</span>
								<?php $attr = ['countryCode' => config('country.icode')]; ?>
								<a class="sell-your-item" href="{{ lurl(trans('routes.v-search', $attr), $attr) }}">
									{{ t('Browse Jobs') }}
									<i class="icon-th-list"></i>
								</a>
							</h2>
						</div>
					</div>
					
					@if (isset($companies) and $companies->count() > 0)
						@foreach($companies as $key => $iCompany)
							<?php
								// Get companies URL
								$attr = ['countryCode' => config('country.icode'), 'id' => $iCompany->id];
								$companyUrl = lurl(trans('routes.v-search-company', $attr), $attr);
							?>
							<div class="col-lg-2 col-md-3 col-sm-3 col-xs-4 f-category">
								<a href="{{ $companyUrl }}">
									<img alt="{{ $iCompany->name }}" class="img-fluid" src="{{ imgUrl(\App\Models\Company::getLogo($iCompany->logo), 'medium') }}">
									<h6> {{ t('Jobs at') }}
										<span class="company-name">{{ $iCompany->name }}</span>
										<span class="jobs-count text-muted">({{ $iCompany->posts_count }})</span>
									</h6>
								</a>
							</div>
						@endforeach
					@else
						<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 f-category" style="width: 100%;">
							{{ t('No result. Refine your search using other criteria.') }}
						</div>
					@endif
			
				</div>
			</div>
			
			<div style="clear: both"></div>
			
			<div class="pagination-bar text-center">
				{{ (isset($companies)) ? $companies->links() : '' }}
			</div>
			
		</div>
	</div>
@endsection
