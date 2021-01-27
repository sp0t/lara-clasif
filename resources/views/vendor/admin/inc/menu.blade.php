<div class="navbar-custom-menu pull-left">
    <ul class="nav navbar-nav"></ul>
</div>

<div class="navbar-custom-menu">
    <ul class="nav navbar-nav">
        
        <li><a href="{{ url('/') }}" target="_blank"><i class="fa fa-home"></i> <span>{{ trans('admin::messages.Home') }}</span></a></li>
        
        @if (auth()->guest())
            <li><a href="{{ admin_url('login') }}">{{ trans('admin::messages.login') }}</a></li>
        @else
            <li><a href="{{ admin_url('logout') }}"><i class="fa fa-btn fa-sign-out"></i> {{ trans('admin::messages.logout') }}</a></li>
    	@endif
     
    </ul>
</div>
