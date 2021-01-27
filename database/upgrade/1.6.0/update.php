<?php

\DB::table('settings')->where('key', '=', 'app_logo')->update(array(
	'value' => null,
	'field' => '{"name":"value","label":"Logo","type":"image","upload":"true","disk":"uploads","default":"images/logo@2x.png"}'
));
\DB::table('settings')->where('key', '=', 'app_theme')->update(array(
	'field' => '{"name":"value","label":"Value","type":"select_from_array","options":{"default":"Default","blue":"Blue","yellow":"Yellow","green":"Green","red":"Red"}}'
));
\DB::table('settings')->where('key', '=', 'mail_driver')->update(array(
	'field' => '{"name":"value","label":"Value","type":"select_from_array","options":{"smtp":"SMTP","mailgun":"Mailgun","mandrill":"Mandrill","ses":"Amazon SES","mail":"PHP Mail","sendmail":"Sendmail"}}'
));

\DB::table('settings')->where('key', '=', 'upload_max_file_size')->delete();
\DB::table('settings')->where('key', '=', 'admin_notification')->delete();
\DB::table('settings')->where('key', '=', 'payment_notification')->delete();
