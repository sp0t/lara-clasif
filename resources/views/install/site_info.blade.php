@extends('install.layouts.master')

@section('title', trans('messages.configuration'))

@section('content')
	
	<form action="{{ $installUrl . '/site_info' }}" method="POST">
		{!! csrf_field() !!}
		
		<h3 class="title-3"><i class="icon-globe"></i> {{ trans('messages.general') }}</h3>
		<div class="row">
			<div class="col-md-6">
				@include('install.helpers.form_control', [
					'type' => 'text',
					'name' => 'site_name',
					'value' => (isset($site_info["site_name"]) ? $site_info["site_name"] : ""),
					'help_class' => 'install',
					'rules' => ["site_name" => "required"]
				])
			</div>
			<div class="col-md-6">
				@include('install.helpers.form_control', [
					'type' => 'text',
					'name' => 'site_slogan',
					'value' => (isset($site_info["site_slogan"]) ? $site_info["site_slogan"] : ""),
					'help_class' => 'install',
					'rules' => ["site_slogan" => "required"]
				])
			</div>
		</div>
		<hr/>
		<h3 class="title-3"><i class="icon-user"></i> {{ trans('messages.admin_info') }}</h3>
		<div class="row">
			<div class="col-md-6">
				@include('install.helpers.form_control', [
					'type' => 'text',
					'name' => 'name',
					'value' => (isset($site_info["name"]) ? $site_info["name"] : ""),
					'help_class' => 'install',
					'rules' => $rules
				])
			</div>
			<div class="col-md-6">
				@include('install.helpers.form_control', [
					'type' => 'text',
					'name' => 'purchase_code',
					'value' => (isset($site_info["purchase_code"]) ? $site_info["purchase_code"] : ""),
					'help_class' => 'install',
					'rules' => $rules
				])
			</div>
		</div>
		
		<div class="row">
			<div class="col-md-6">
				@include('install.helpers.form_control', [
					'type' => 'text',
					'name' => 'email',
					'value' => (isset($site_info["email"]) ? $site_info["email"] : ""),
					'help_class' => 'install',
					'rules' => $rules
				])
			</div>
			<div class="col-md-6">
				@include('install.helpers.form_control', [
					'type' => 'text',
					'name' => 'password',
					'value' => (isset($site_info["password"]) ? $site_info["password"] : ""),
					'help_class' => 'install',
					'rules' => $rules
				])
			</div>
		</div>
		
		<div class="row">
			<div class="col-md-6">
				@include('install.helpers.form_control', [
				'type' => 'select',
				'name' => 'default_country',
				'value' => (isset($site_info["default_country"]) ? $site_info["default_country"] : ((isset($_COOKIE['ip_country_code'])) ? $_COOKIE['ip_country_code'] : "")),
				'options' => getCountriesFromArray(),
				'include_blank' => trans('messages.choose'),
				'rules' => $rules
				])
			</div>
		</div>
		
		<hr/>
		<h3 class="title-3"><i class="icon-mail"></i> {{ trans('messages.system_email_configuration') }}</h3>
		<div class="row">
			<div class="col-md-6">
				@include('install.helpers.form_control', [
					'type' => 'select',
					'name' => 'mail_driver',
					'label' => trans('messages.mail_driver'),
					'value' => (isset($site_info["mail_driver"]) ? $site_info["mail_driver"] : ""),
					'options' => [
						["value" => "sendmail", "text" => trans('messages.sendmail')],
						["value" => "smtp", "text" => trans('messages.smtp')],
						["value" => "mailgun", "text" => trans('messages.mailgun')],
						["value" => "postmark", "text" => trans('messages.postmark')],
						["value" => "ses", "text" => trans('messages.ses')],
						["value" => "mandrill", "text" => trans('messages.mandrill')],
						["value" => "sparkpost", "text" => trans('messages.sparkpost')],
					],
					'help_class' => 'install',
					'rules' => $rules
				])
			</div>
		</div>
		
		<div class="row sendmail_box">
			<div class="col-md-6">
				@php($sendmailPath = '/usr/sbin/sendmail -bs')
				@include('install.helpers.form_control', [
					'type' => 'text',
					'name' => 'sendmail_path',
					'label' => trans('messages.sendmail_path'),
					'value' => (isset($site_info["sendmail_path"]) ? $site_info["sendmail_path"] : $sendmailPath),
					'help_class' => 'install',
					'rules' => $sendmail_rules
				])
			</div>
		</div>
		
		<div class="row smtp_box">
			<div class="col-md-6">
				@include('install.helpers.form_control', [
					'type' => 'text',
					'name' => 'smtp_hostname',
					'label' => trans('messages.hostname'),
					'value' => (isset($site_info["smtp_hostname"]) ? $site_info["smtp_hostname"] : ""),
					'help_class' => 'install',
					'rules' => $smtp_rules
				])
				
				@include('install.helpers.form_control', [
					'type' => 'text',
					'name' => 'smtp_username',
					'label' => trans('messages.username'),
					'value' => (isset($site_info["smtp_username"]) ? $site_info["smtp_username"] : ""),
					'help_class' => 'install',
					'rules' => $smtp_rules
				])
				
				@include('install.helpers.form_control', [
					'type' => 'text',
					'name' => 'smtp_encryption',
					'label' => trans('messages.encryption'),
					'value' => (isset($site_info["smtp_encryption"]) ? $site_info["smtp_encryption"] : ""),
					'help_class' => 'install',
					'rules' => $smtp_rules
				])
			</div>
			<div class="col-md-6">
				@include('install.helpers.form_control', [
					'type' => 'text',
					'name' => 'smtp_port',
					'label' => trans('messages.port'),
					'value' => (isset($site_info["smtp_port"]) ? $site_info["smtp_port"] : ""),
					'help_class' => 'install',
					'rules' => $smtp_rules
				])
				
				@include('install.helpers.form_control', [
					'type' => 'text',
					'name' => 'smtp_password',
					'label' => trans('messages.password'),
					'value' => (isset($site_info["smtp_password"]) ? $site_info["smtp_password"] : ""),
					'help_class' => 'install',
					'rules' => $smtp_rules
				])
			</div>
		</div>
		
		<div class="row mailgun_box">
			<div class="col-md-6">
				@include('install.helpers.form_control', [
					'type' => 'text',
					'name' => 'mailgun_domain',
					'label' => trans('messages.mailgun_domain'),
					'value' => (isset($site_info["mailgun_domain"]) ? $site_info["mailgun_domain"] : ""),
					'help_class' => 'install',
					'rules' => $mailgun_rules
				])
			
			</div>
			<div class="col-md-6">
				@include('install.helpers.form_control', [
					'type' => 'text',
					'name' => 'mailgun_secret',
					'label' => trans('messages.mailgun_secret'),
					'value' => (isset($site_info["mailgun_secret"]) ? $site_info["mailgun_secret"] : ""),
					'help_class' => 'install',
					'rules' => $mailgun_rules
				])
			</div>
		</div>
		
		<div class="row postmark_box">
			<div class="col-md-6">
				@include('install.helpers.form_control', [
					'type' => 'text',
					'name' => 'postmark_token',
					'label' => trans('messages.postmark_token'),
					'value' => (isset($site_info["postmark_token"]) ? $site_info["postmark_token"] : ""),
					'help_class' => 'install',
					'rules' => $postmark_rules
				])
			</div>
		</div>
		
		<div class="row ses_box">
			<div class="col-md-6">
				@include('install.helpers.form_control', [
					'type' => 'text',
					'name' => 'ses_key',
					'label' => trans('messages.ses_key'),
					'value' => (isset($site_info["ses_key"]) ? $site_info["ses_key"] : ""),
					'help_class' => 'install',
					'rules' => $ses_rules
				])
				
				@include('install.helpers.form_control', [
					'type' => 'text',
					'name' => 'ses_secret',
					'label' => trans('messages.ses_secret'),
					'value' => (isset($site_info["ses_secret"]) ? $site_info["ses_secret"] : ""),
					'help_class' => 'install',
					'rules' => $ses_rules
				])
			</div>
			<div class="col-md-6">
				@include('install.helpers.form_control', [
					'type' => 'text',
					'name' => 'ses_region',
					'label' => trans('messages.ses_region'),
					'value' => (isset($site_info["ses_region"]) ? $site_info["ses_region"] : ""),
					'help_class' => 'install',
					'rules' => $ses_rules
				])
			</div>
		</div>
		
		<div class="row mandrill_box">
			<div class="col-md-6">
				@include('install.helpers.form_control', [
					'type' => 'text',
					'name' => 'mandrill_secret',
					'label' => trans('messages.mandrill_secret'),
					'value' => (isset($site_info["mandrill_secret"]) ? $site_info["mandrill_secret"] : ""),
					'help_class' => 'install',
					'rules' => $mandrill_rules
				])
			</div>
		</div>
		
		<div class="row sparkpost_box">
			<div class="col-md-6">
				@include('install.helpers.form_control', [
					'type' => 'text',
					'name' => 'sparkpost_secret',
					'label' => trans('messages.sparkpost_secret'),
					'value' => (isset($site_info["sparkpost_secret"]) ? $site_info["sparkpost_secret"] : ""),
					'help_class' => 'install',
					'rules' => $sparkpost_rules
				])
			</div>
		</div>
		
		<hr/>
		<div class="text-right">
			<button type="submit" class="btn btn-primary" data-wait="{{ trans('messages.button_processing') }}">
				{!! trans('messages.next') !!} <i class="icon-right-open-big position-right"></i>
			</button>
		</div>
	
	</form>

@endsection

@section('after_scripts')
	<script type="text/javascript" src="{{ URL::asset('assets/plugins/forms/styling/uniform.min.js') }}"></script>
	<script>
		function toogleMailer() {
			var value = $("select[name='mail_driver']").val();
			if (value == 'sendmail') {
				$('.sendmail_box').show();
				$('.smtp_box').hide();
				$('.mailgun_box').hide();
				$('.postmark_box').hide();
				$('.ses_box').hide();
				$('.mandrill_box').hide();
				$('.sparkpost_box').hide();
			} else if (value == 'mailgun') {
				$('.sendmail_box').hide();
				$('.smtp_box').show();
				$('.mailgun_box').show();
				$('.postmark_box').hide();
				$('.ses_box').hide();
				$('.mandrill_box').hide();
				$('.sparkpost_box').hide();
			} else if (value == 'postmark') {
				$('.sendmail_box').hide();
				$('.smtp_box').show();
				$('.mailgun_box').hide();
				$('.postmark_box').show();
				$('.ses_box').hide();
				$('.mandrill_box').hide();
				$('.sparkpost_box').hide();
			} else if (value == 'ses') {
				$('.sendmail_box').hide();
				$('.smtp_box').show();
				$('.mailgun_box').hide();
				$('.postmark_box').hide();
				$('.ses_box').show();
				$('.mandrill_box').hide();
				$('.sparkpost_box').hide();
			} else if (value == 'mandrill') {
				$('.sendmail_box').hide();
				$('.smtp_box').show();
				$('.mailgun_box').hide();
				$('.postmark_box').hide();
				$('.ses_box').hide();
				$('.mandrill_box').show();
				$('.sparkpost_box').hide();
			} else if (value == 'sparkpost') {
				$('.sendmail_box').hide();
				$('.smtp_box').show();
				$('.mailgun_box').hide();
				$('.postmark_box').hide();
				$('.ses_box').hide();
				$('.mandrill_box').hide();
				$('.sparkpost_box').show();
			} else {
				$('.sendmail_box').hide();
				$('.smtp_box').show();
				$('.mailgun_box').hide();
				$('.postmark_box').hide();
				$('.ses_box').hide();
				$('.mandrill_box').hide();
				$('.sparkpost_box').hide();
			}
		}
		
		$(document).ready(function () {
			toogleMailer();
			$("select[name='mail_driver']").change(function () {
				toogleMailer();
			});
		});
	</script>
@endsection
