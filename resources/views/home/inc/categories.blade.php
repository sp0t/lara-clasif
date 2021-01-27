@if (isset($categoriesOptions) and isset($categoriesOptions['type_of_display']))
	@include('home.inc.spacer')
	<div class="container">
		<div class="col-xl-12 content-box layout-section">
			<div class="row row-featured row-featured-category">
				<div class="col-xl-12 box-title no-border">
					<div class="inner">
						<h2>
							<span class="title-3">{{ t('Browse by') }} <span style="font-weight: bold;">{{ t('Category') }}</span></span>
							<a href="{{ \App\Helpers\UrlGen::sitemap() }}" class="sell-your-item">
								{{ t('View more') }} <i class="icon-th-list"></i>
							</a>
						</h2>
					</div>
				</div>
				
				@if ($categoriesOptions['type_of_display'] == 'c_picture_icon')
					
					@if (isset($categories) and $categories->count() > 0)
						@foreach($categories as $key => $cat)
							<div class="col-lg-2 col-md-3 col-sm-3 col-xs-4 f-category">
								<a href="{{ \App\Helpers\UrlGen::category($cat) }}">
									<img src="{{ imgUrl($cat->picture, 'cat') }}" class="img-fluid" alt="{{ $cat->name }}">
									<h6>
										{{ $cat->name }}
										@if (isset($categoriesOptions['count_categories_posts']) and $categoriesOptions['count_categories_posts'])
											@if ($cat->children->count() > 0)
												&nbsp;({{ $cat->posts->count() + $cat->childrenPosts->count() }})
											@else
												&nbsp;({{ $cat->childrenPosts->count() }})
											@endif
										@endif
									</h6>
								</a>
							</div>
						@endforeach
					@endif
				
				@elseif (in_array($categoriesOptions['type_of_display'], ['cc_normal_list', 'cc_normal_list_s']))
					
					<div style="clear: both;"></div>
					<?php $styled = ($categoriesOptions['type_of_display'] == 'cc_normal_list_s') ? ' styled' : ''; ?>
					
					@if (isset($categories) and $categories->count() > 0)
						<div class="col-xl-12">
							<div class="list-categories-children{{ $styled }}">
								<div class="row">
									@foreach ($categories as $key => $cols)
										<div class="col-md-4 col-sm-4 {{ (count($categories) == $key+1) ? 'last-column' : '' }}">
											@foreach ($cols as $iCat)
												
												<?php
													$randomId = '-' . substr(uniqid(rand(), true), 5, 5);
												?>
											
												<div class="cat-list">
													<h3 class="cat-title rounded">
														@if (isset($categoriesOptions['show_icon']) and $categoriesOptions['show_icon'] == 1)
															<i class="{{ $iCat->icon_class ?? 'icon-ok' }}"></i>&nbsp;
														@endif
														<a href="{{ \App\Helpers\UrlGen::category($iCat) }}">
															{{ $iCat->name }}
															@if (isset($categoriesOptions['count_categories_posts']) and $categoriesOptions['count_categories_posts'])
																@if ($iCat->children->count() > 0)
																	&nbsp;({{ $iCat->posts->count() + $iCat->childrenPosts->count() }})
																@else
																	&nbsp;({{ $iCat->childrenPosts->count() }})
																@endif
															@endif
														</a>
														<span class="btn-cat-collapsed collapsed"
															  data-toggle="collapse"
															  data-target=".cat-id-{{ $iCat->id . $randomId }}"
															  aria-expanded="false"
														>
															<span class="icon-down-open-big"></span>
														</span>
													</h3>
													<ul class="cat-collapse collapse show cat-id-{{ $iCat->id . $randomId }} long-list-home">
														@if (isset($subCategories) and $subCategories->has($iCat->tid))
															@foreach ($subCategories->get($iCat->tid) as $iSubCat)
																<li>
																	<a href="{{ \App\Helpers\UrlGen::category($iSubCat, 1) }}">
																		{{ $iSubCat->name }}
																	</a>
																	@if (isset($categoriesOptions['count_categories_posts']) and $categoriesOptions['count_categories_posts'])
																		&nbsp;({{ $iSubCat->childrenPosts->count() }})
																	@endif
																</li>
															@endforeach
														@endif
													</ul>
												</div>
											@endforeach
										</div>
									@endforeach
								</div>
							</div>
							<div style="clear: both;"></div>
						</div>
					@endif
				
				@else
					
					<?php
					$listTab = [
						'c_circle_list' => 'list-circle',
						'c_check_list'  => 'list-check',
						'c_border_list' => 'list-border',
					];
					$catListClass = (isset($listTab[$categoriesOptions['type_of_display']])) ? 'list ' . $listTab[$categoriesOptions['type_of_display']] : 'list';
					?>
					@if (isset($categories) and $categories->count() > 0)
						<div class="col-xl-12">
							<div class="list-categories">
								<div class="row">
									@foreach ($categories as $key => $items)
										<ul class="cat-list {{ $catListClass }} col-md-4 {{ (count($categories) == $key+1) ? 'cat-list-border' : '' }}">
											@foreach ($items as $k => $cat)
												<li>
													@if (isset($categoriesOptions['show_icon']) and $categoriesOptions['show_icon'] == 1)
														<i class="{{ $cat->icon_class ?? 'icon-ok' }}"></i>&nbsp;
													@endif
													<a href="{{ \App\Helpers\UrlGen::category($cat) }}">
														{{ $cat->name }}
													</a>
													@if (isset($categoriesOptions['count_categories_posts']) and $categoriesOptions['count_categories_posts'])
														@if ($cat->children->count() > 0)
															&nbsp;({{ $cat->posts->count() + $cat->childrenPosts->count() }})
														@else
															&nbsp;({{ $cat->childrenPosts->count() }})
														@endif
													@endif
												</li>
											@endforeach
										</ul>
									@endforeach
								</div>
							</div>
						</div>
					@endif
				
				@endif
		
			</div>
		</div>
	</div>
@endif

@section('before_scripts')
	@parent
	@if (isset($categoriesOptions) and isset($categoriesOptions['max_sub_cats']) and $categoriesOptions['max_sub_cats'] >= 0)
		<script>
			var maxSubCats = {{ (int)$categoriesOptions['max_sub_cats'] }};
		</script>
	@endif
@endsection
