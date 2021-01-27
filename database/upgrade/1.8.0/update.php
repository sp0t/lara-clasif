<?php

\File::delete(app_path('Larapen/Helpers/functions.php'));
\File::delete(app_path('Larapen/Helpers/wordpress.php'));
\File::delete(app_path('Larapen/Models/Pack.php'));
\File::moveDirectory(public_path('vendor/adminlte/plugins/jquery/'), public_path('vendor/adminlte/plugins/jQuery/'));
