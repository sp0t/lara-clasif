<!DOCTYPE html>
<html lang="{{ config('app.locale') }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>
        {{ isset($title) ? $title.' :: '.config('app.name').' Admin' : config('app.name').' Admin' }}
    </title>
    
    {{-- Encrypted CSRF token for Laravel, in order for Ajax requests to work --}}
    <meta name="csrf-token" content="{{ csrf_token() }}" />

    @yield('before_styles')
    
    <!-- Tell the browser to be responsive to screen width -->
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    <!-- Bootstrap 3.3.6 -->
    <link rel="stylesheet" href="{{ asset('vendor/adminlte/') }}/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.5.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/ionicons/2.0.1/css/ionicons.min.css">
    
    <link rel="stylesheet" href="{{ asset('vendor/adminlte/') }}/dist/css/AdminLTE.min.css">
    <!-- AdminLTE Skins. Choose a skin from the css/skins folder instead of downloading all of them to reduce the load. -->
    <link rel="stylesheet" href="{{ asset('vendor/adminlte/') }}/dist/css/skins/_all-skins.min.css">
    
    <link rel="stylesheet" href="{{ asset('vendor/admin/pnotify/pnotify.custom.min.css') }}">
    
    <!-- iCheck -->
    <link rel="stylesheet" href="{{ asset('vendor/adminlte/') }}/plugins/iCheck/square/blue.css">
    
    <!-- Admin Global CSS -->
    <link rel="stylesheet" href="{{ asset('vendor/admin/style.css') . vTime() }}">

    @yield('after_styles')
    
    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
    <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
    
    @yield('recaptcha_scripts')
</head>
<body class="hold-transition {{ config('larapen.admin.skin') }} login-page">
<div class="login-box">
    
    <div class="login-logo">
        <a href="{{ url('/') }}">
            <img src="{{ imgUrl(config('settings.app.logo'), 'logo') }}" style="width:200px; height:auto;">
        </a>
    </div>
    <!-- /.login-logo -->
    
    @yield('content')
    
</div>
<!-- /.login-box -->

@yield('before_scripts')

<!-- jQuery 2.2.3 -->
<script src="{{ asset('vendor/adminlte/') }}/plugins/jQuery/jquery-2.2.3.min.js"></script>
<!-- Bootstrap 3.3.6 -->
<script src="{{ asset('vendor/adminlte/') }}/bootstrap/js/bootstrap.min.js"></script>
<!-- iCheck -->
<script src="{{ asset('vendor/adminlte/') }}/plugins/iCheck/icheck.min.js"></script>

<script src="{{ asset('vendor/adminlte') }}/dist/js/app.min.js"></script>

<script>
    $(function () {
        $('input').iCheck({
            checkboxClass: 'icheckbox_square-blue',
            radioClass: 'iradio_square-blue',
            increaseArea: '20%' /* optional */
        });
    });
</script>

@include('admin::inc.alerts')

@yield('after_scripts')

</body>
</html>
