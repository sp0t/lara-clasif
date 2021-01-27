<div class="header">
	<nav class="navbar fixed-top navbar-site navbar-light bg-light navbar-expand-md" role="navigation">
		<div class="container">
			
			<div class="navbar-identity">
				{{-- Logo --}}
				<a href="{{ lurl('/') }}" class="navbar-brand logo logo-title">
					<?php
					$logoImgStyle = 'width:auto; height:40px; float:left; margin:0 5px 0 0;';
					$logoImg = '<img src="' . url('images/logo.png') . '" style="' . $logoImgStyle . '"/>';
					try {
						if (is_link(public_path('storage'))) {
							if (\Storage::disk('public')->exists(config('larapen.core.logo'))) {
								$logoImg = '<img src="' . \Storage::disk('public')->url(config('larapen.core.logo')) . '" style="' . $logoImgStyle . '"/>';
							}
						}
					} catch (\Exception $e) {}
					
					// Print Logo
					echo $logoImg;
					?>
				</a>
			</div>
			
			<div class="navbar-collapse collapse">
				<ul class="nav navbar-nav navbar-left"></ul>
				<ul class="nav navbar-nav ml-auto navbar-right"></ul>
			</div>
		
		</div>
	</nav>
</div>
