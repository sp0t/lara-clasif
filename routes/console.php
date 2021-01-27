<?php

use Illuminate\Foundation\Inspiring;

/*
|--------------------------------------------------------------------------
| Console Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of your Closure based console
| commands. Each Closure is bound to a command instance allowing a
| simple approach to interacting with each command's IO methods.
|
*/

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->describe('Display an inspiring quote');


Artisan::command('ads:clean', function () {
    $this->info('Remove and archive old ads');
})->describe('Remove and archive old ads');


Artisan::command('makeBackup:exec {type?}', function () {
	$this->info('Make backup of the website');
})->describe('Make backup of the website');


Artisan::command('clearBackup:exec', function () {
	$this->info('Clear backups of the website');
})->describe('Clear backups of the website');
