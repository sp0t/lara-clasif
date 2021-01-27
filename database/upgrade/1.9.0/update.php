<?php

\File::deleteDirectory(app_path('Larapen/'));
\File::delete(base_path('gulpfile.js'));
\File::delete(base_path('package.json'));
\File::delete(base_path('phpspec.yml'));
\File::delete(base_path('phpunit.xml'));
