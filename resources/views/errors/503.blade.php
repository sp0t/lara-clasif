@extends('errors::layout')

@section('title', 'Service Unavailable')

<?php
$data = [];
if (file_exists(storage_path('framework/down'))) {
	$buffer = file_get_contents(storage_path('framework/down'));
	$data = json_decode($buffer, true);
}
?>
@section('message', (isset($data['message'])) ? $data['message'] : 'Be right back.')
