<?php

\File::deleteDirectory(app_path('Http/Controllers/Ad/'));
\File::deleteDirectory(app_path('Jobs/'));
\File::deleteDirectory(base_path('database/umpirsky/country/icu/'));
\File::deleteDirectory(public_path('assets/css/style/'));
\File::deleteDirectory(public_path('uploads/app/categories/blue/'));
\File::deleteDirectory(public_path('uploads/app/categories/default/'));
\File::deleteDirectory(public_path('uploads/app/categories/green/'));
\File::deleteDirectory(public_path('uploads/app/categories/yellow/'));
\File::deleteDirectory(base_path('resources/views/ad/'));
\File::deleteDirectory(base_path('resources/views/emails/ad/'));
\File::deleteDirectory(base_path('resources/views/layouts/inc/tools/svgmap/'));

\File::delete(\File::glob(app_path('Mail') . '/Ad*.php'));
\File::delete(\File::glob(base_path('database/migrations') . '/*.php'));
\File::delete(\File::glob(public_path('assets/css') . '/fileinput*.css'));
\File::delete(\File::glob(public_path('assets/js') . '/fileinput*.js'));
\File::delete(\File::glob(base_path('resources/views/search/inc') . '/ads*.php'));

\File::delete(app_path('Events/AdWasVisited.php'));
\File::delete(app_path('Helpers/Rules.php'));
\File::delete(app_path('Helpers/Validator.php'));
\File::delete(app_path('Http/Controllers/Account/AdsController.php'));
\File::delete(app_path('Http/Controllers/Admin/AdController.php'));
\File::delete(app_path('Http/Controllers/Admin/AdTypeController.php'));
\File::delete(app_path('Http/Controllers/Ajax/AdController.php'));
\File::delete(app_path('Http/Controllers/Ajax/AutocompleteController.php'));
\File::delete(app_path('Http/Controllers/Ajax/JsonController.php'));
\File::delete(app_path('Http/Controllers/Ajax/PlacesController.php'));
\File::delete(app_path('Http/Controllers/Ajax/StateCitiesController.php'));
\File::delete(app_path('Http/Requests/Admin/AdRequest.php'));
\File::delete(app_path('Http/Requests/Admin/AdTypeRequest.php'));
\File::delete(app_path('Listeners/UpdateTheAdCounter.php'));
\File::delete(app_path('Models/Ad.php'));
\File::delete(app_path('Models/AdType.php'));
\File::delete(app_path('Models/Permission.php'));
\File::delete(app_path('Models/Role.php'));
\File::delete(app_path('Models/SavedAd.php'));
\File::delete(public_path('uploads/app/default/categories/fa-folder-blue.png'));
\File::delete(public_path('uploads/app/default/categories/fa-folder-default.png'));
\File::delete(public_path('uploads/app/default/categories/fa-folder-green.png'));
\File::delete(public_path('uploads/app/default/categories/fa-folder-red.png'));
\File::delete(public_path('uploads/app/default/categories/fa-folder-yellow.png'));
\File::delete(base_path('resources/views/account/ads.blade.php'));
\File::delete(base_path('resources/views/account/inc/sidebar-left.blade.php'));
\File::delete(base_path('resources/views/auth/signup/activation.blade.php'));
\File::delete(base_path('resources/views/auth/signup/success.blade.php'));
\File::delete(base_path('resources/views/home/inc/bottom-info.blade.php'));
\File::delete(base_path('resources/views/layouts/inc/carousel.blade.php'));

if (\File::exists(public_path('assets/css/style/custom.css'))) {
    \File::delete(public_path('css/custom.css'));
    \File::move(public_path('assets/css/style/custom.css'), public_path('css/custom.css'));
}
