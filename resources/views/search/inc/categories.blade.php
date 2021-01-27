<?php
if (!isset($cats)) {
    $cats = collect([]);
}

$cats = $cats->groupBy('parent_id');
$subCats = $cats;
if ($cats->has(0)) {
	$cats = $cats->get(0);
}
if ($subCats->has(0)) {
	$subCats = $subCats->forget(0);
}
?>
<?php
	if (
		(isset($subCats) and !empty($subCats) and isset($cat) and !empty($cat) and $subCats->has($cat->tid)) ||
		(isset($cats) and !empty($cats))
	):
?>
@if (isset($subCats) and !empty($subCats) and isset($cat) and !empty($cat))
	@if ($subCats->has($cat->tid))
		<div class="container hide-xs">
			<div class="category-links">
				<ul>
				@foreach ($subCats->get($cat->tid) as $iSubCat)
					<li>
						<a href="{{ \App\Helpers\UrlGen::category($iSubCat, 1) }}">
							{{ $iSubCat->name }}
						</a>
					</li>
				@endforeach
				</ul>
			</div>
		</div>
	@endif
@else
	@if (isset($cats) and !empty($cats))
		<div class="container hide-xs">
			<div class="category-links">
				<ul>
				@foreach ($cats as $iCategory)
					<li>
						<a href="{{ \App\Helpers\UrlGen::category($iCategory) }}">
							{{ $iCategory->name }}
						</a>
					</li>
				@endforeach
				</ul>
			</div>
		</div>
	@endif
@endif
<?php endif; ?>