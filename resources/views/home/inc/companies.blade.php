@if (isset($featuredCompanies) and !empty($featuredCompanies))
	@if (isset($featuredCompanies->companies) and $featuredCompanies->companies->count() > 0)
		@include('home.inc.spacer')
		<div class="container">
			<div class="col-lg-12 content-box layout-section">
				<div class="row row-featured row-featured-category row-featured-company">
					<div class="col-lg-12  box-title no-border">
						<div class="inner">
							<h2>
								<span class="title-3">{!! $featuredCompanies->title !!}</span>
								<a class="sell-your-item" href="{{ $featuredCompanies->link }}">
									{{ t('View more') }}
									<i class="icon-th-list"></i>
								</a>
							</h2>
						</div>
					</div>
			
					@foreach($featuredCompanies->companies as $key => $iCompany)
						<?php
							// Company URL setting
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
			
				</div>
			</div>
		</div>
	@endif
@endif
