<?php

return [
	
	/*
	|--------------------------------------------------------------------------
	| Default Filesystem Disk
	|--------------------------------------------------------------------------
	|
	| Here you may specify the default filesystem disk that should be used
	| by the framework. The "local" disk, as well as a variety of cloud
	| based disks are available to your application. Just store away!
	|
	*/
	
	'default' => env('FILESYSTEM_DRIVER', 'public'),
	
	/*
	|--------------------------------------------------------------------------
	| Default Cloud Filesystem Disk
	|--------------------------------------------------------------------------
	|
	| Many applications store files both locally and in the cloud. For this
	| reason, you may specify a default "cloud" driver here. This driver
	| will be bound as the Cloud disk implementation in the container.
	|
	*/
	
	'cloud' => env('FILESYSTEM_CLOUD', 's3'),
	
	/*
	|--------------------------------------------------------------------------
	| Filesystem Disks
	|--------------------------------------------------------------------------
	|
	| Here you may configure as many filesystem "disks" as you wish, and you
	| may even configure multiple disks of the same driver. Defaults have
	| been setup for each driver as an example of the required options.
	|
	| Supported Drivers: "local", "ftp", "sftp", "s3", "dropbox"
	|
	*/
	
    'disks' => [
		
		'local' => [
			'driver' => 'local',
			'root'   => storage_path('app'),
		],
		
		'public' => [
			'driver' 	 => 'local',
			'root' 		 => storage_path('app/public'),
			'url' 		 => env('APP_URL').'/storage',
			'visibility' => 'public',
		],
		
		//---
		
		// Used for Admin -> Log
        'storage' => [
            'driver' => 'local',
            'root'   => storage_path(),
        ],
		
		// Used for Admin -> Backup
        'backups' => [
            'driver' => 'local',
            'root'   => storage_path('backups'), // that's where your backups are stored by default: storage/backups
        ],
		
		//---
		
		'ftp' => [
			'driver'   => 'ftp',
			'host'     => env('FTP_HOST'),
			'username' => env('FTP_USERNAME'),
			'password' => env('FTP_PASSWORD'),
			'port'     => env('FTP_PORT', 21),
			'root'     => env('FTP_ROOT', ''),
			'passive'  => env('FTP_PASSIVE', true),
			'ssl'      => env('FTP_SSL', true),
			'timeout'  => env('FTP_TIMEOUT', 30),
		],
		
		'sftp' => [
			'driver'     => 'sftp',
			'host' 	     => env('SFTP_HOST'),
			'username'   => env('SFTP_USERNAME'),
			'password'   => env('SFTP_PASSWORD'), // Or SSH Encryption Password
			'privateKey' => env('SFTP_SSH_PRIVATE_KEY'), // '/path/to/privateKey'
			'port'       => env('SFTP_PORT', 22),
			'root'       => env('SFTP_ROOT', ''),
			'timeout'    => env('SFTP_TIMEOUT', 30),
		],
		
		'minio' => [
			'driver'   => 's3',
			'endpoint' => env('MINIO_ENDPOINT', 'http://127.0.0.1:9000'),
			'key'      => env('MINIO_KEY'),
			'secret'   => env('MINIO_SECRET'),
			'region'   => env('MINIO_REGION'),
			'bucket'   => env('MINIO_BUCKET'),
			'use_path_style_endpoint' => true,
		],
		
		's3' => [
			'driver' => 's3',
			'key' 	 => env('AWS_ACCESS_KEY_ID'),
			'secret' => env('AWS_SECRET_ACCESS_KEY'),
			'region' => env('AWS_DEFAULT_REGION'),
			'bucket' => env('AWS_BUCKET'),
			'url'	 => env('AWS_URL', ''),
		],
		
		's3-cached' => [
			'driver' => 's3',
			'key' 	 => env('AWS_ACCESS_KEY_ID'),
			'secret' => env('AWS_SECRET_ACCESS_KEY'),
			'region' => env('AWS_DEFAULT_REGION'),
			'bucket' => env('AWS_BUCKET'),
			'url'	 => env('AWS_URL', ''),
		],
		
		'dropbox' => [
			'driver'              => 'dropbox',
			'root'                => env('DROPBOX_ROOT', '/'),
			'authorization_token' => env('DROPBOX_AUTHORIZATION_TOKEN', ''),
		],
		
		'backblaze' => [
			'driver'          => 'backblaze',
			'account_id'      => env('BACKBLAZE_ACCOUNT_ID'),
			'application_key' => env('BACKBLAZE_APPLICATION_KEY'),
			'bucket'          => env('BACKBLAZE_BUCKET'),
		],
		
		'digitalocean' => [
			'driver' => 'digitalocean',
			'key'    => env('DIGITALOCEAN_KEY'),
			'secret' => env('DIGITALOCEAN_SECRET'),
			'region' => env('DIGITALOCEAN_REGION'),
			'bucket' => env('DIGITALOCEAN_BUCKET'),
		],
		
    ],
	
];
