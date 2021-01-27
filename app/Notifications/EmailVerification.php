<?php
/**
 * JobClass - Job Board Web Application
 * Copyright (c) BedigitCom. All Rights Reserved
 *
 * Website: http://www.bedigit.com
 *
 * LICENSE
 * -------
 * This software is furnished under a license and may be used and copied
 * only in accordance with the terms of such license and with the inclusion
 * of the above copyright notice. If you Purchased from Codecanyon,
 * Please read the full License from here - http://codecanyon.net/licenses/standard
 */

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;

class EmailVerification extends Notification implements ShouldQueue
{
	use Queueable;
	
	protected $entity;
	protected $entityRef;
	
	public function __construct($entity, $entityRef)
	{
		$this->entity = $entity;
		$this->entityRef = $entityRef;
	}
	
	public function via($notifiable)
	{
		if (!isset($this->entityRef['name'])) {
			return false;
		}
		
		return ['mail'];
	}
	
	public function toMail($notifiable)
	{
		$verificationUrl = lurl('verify/' . $this->entityRef['slug'] . '/email/' . $this->entity->email_token);
		
		return (new MailMessage)
			->subject(trans('mail.email_verification_title'))
			->greeting(trans('mail.email_verification_content_1', ['userName' => $this->entity->{$this->entityRef['name']},]))
			->line(trans('mail.email_verification_content_2'))
			->action(trans('mail.email_verification_action'), $verificationUrl)
			->line(trans('mail.email_verification_content_3', ['appName' => config('app.name')]));
	}
}
