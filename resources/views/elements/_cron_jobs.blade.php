<h3 class="title-3"><i class="icon-clock"></i> {{ trans('messages.setting_up_cron_jobs') }}</h3>
    
<div class="">
    {!! trans('messages.cron_jobs_guide') !!}    
</div>
<br/>

@if (!isset(explode(" ", exec("whereis php"))[1]))
    <div class="alert alert-info">
        Cannot find PHP_BIN_PATH in your server. Please find it and replace all {PHP_BIN_PATH} text below with that one.<br /> Ex: /usr/bin/php7.0, /usr/bin/php, /usr/lib/php.
    </div>
@endif

<pre style="font-size: 16px;background:#f5f5f5"># {{ trans('messages.cron_jobs_comment') }}
* * * * * {!! (isset(explode(" ", exec("whereis php"))[1]) ? explode(" ", exec("whereis php"))[1] : "<span class='text-danger'>{PHP_BIN_PATH}</span>") !!} -q {{ base_path() }}/artisan schedule:run >> /dev/null 2>&gt;&amp;1
</pre>