{{--
 * LaraClassified - Geo Classified Ads CMS
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
    @include('pages.inc.page-intro')
@endsection

@section('content')
	@include('common.spacer')
	<div class="main-container inner-page">
		<div class="container">
			<div class="section-content">
				<div class="row">
                    
                    @if (empty($page->picture))
                        <h1 class="text-center title-1" style="color: {!! $page->name_color !!};"><strong>{{ $page->name }}</strong></h1>
						<hr class="center-block small mt-0" style="background-color: {!! $page->name_color !!};">
                    @endif
                    
					<div class="col-md-12 page-content">
						<div class="inner-box relative">
							<div class="row">
								<div class="col-sm-12 page-content">
                                    @if (empty($page->picture))
										<h3 class="text-center" style="color: {!! $page->title_color !!};">{{ $page->title }}</h3>
                                    @endif
									<div class="text-content text-left from-wysiwyg">
										{!! $page->content !!}
									</div>
								</div>
							</div>
						</div>
					</div>

				</div>

				@include('layouts.inc.social.horizontal')

			</div>
		</div>
	</div>
@endsection

@section('info')
@endsection
