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

use App\Helpers\Files\Storage\StorageDisk;
use App\Helpers\UrlGen;
use App\Models\Message;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\NexmoMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Models\Post;
use Illuminate\Support\Str;
use NotificationChannels\Twilio\TwilioChannel;
use NotificationChannels\Twilio\TwilioSmsMessage;

class EmployerContacted extends Notification implements ShouldQueue
{
	use Queueable;
	
	protected $post;
	
	// CAUTION: Conflict between the Model Message $message and the Laravel Mail Message (Mailable) objects.
	// NOTE: No problem with Laravel Notification.
	protected $msg;
	
	public function __construct(Post $post, Message $msg)
	{
		$this->post = $post;
		$this->msg = $msg;
	}
	
	public function via($notifiable)
	{
		if (!empty($this->post->email)) {
			if (config('settings.sms.message_activation') == 1) {
				if (!empty($this->post->phone) && $this->post->phone_hidden != 1) {
					if (config('settings.sms.driver') == 'twilio') {
						return ['mail', TwilioChannel::class];
					}
					
					return ['mail', 'nexmo'];
				}
				
				return ['mail'];
			} else {
				return ['mail'];
			}
		} else {
			if (config('settings.sms.driver') == 'twilio') {
				return [TwilioChannel::class];
			}
			
			return ['nexmo'];
		}
	}
	
	public function toMail($notifiable)
	{
		$postUrl = UrlGen::post($this->post);
		
		$mailMessage = (new MailMessage)
			->replyTo($this->msg->from_email, $this->msg->from_name)
			->subject(trans('mail.post_employer_contacted_title', [
				'title'   => $this->post->title,
				'appName' => config('app.name'),
			]))
			->line(nl2br($this->msg->message))
			->line(trans('mail.post_employer_contacted_content_1', [
				'name'  => $this->msg->from_name,
				'email' => $this->msg->from_email,
				'phone' => $this->msg->from_phone,
			]))
			->line(trans('mail.post_employer_contacted_content_2', [
				'title'   => $this->post->title,
				'postUrl' => $postUrl,
				'appUrl'  => lurl('/'),
				'appName' => config('app.name'),
			]))
			->line('<br>')
			->line(trans('mail.post_employer_contacted_content_3'))
			->line(trans('mail.post_employer_contacted_content_4'))
			->line(trans('mail.post_employer_contacted_content_5'))
			->line(trans('mail.post_employer_contacted_content_6'))
			->line('<br>')
			->line(trans('mail.post_employer_contacted_content_7'));
		
		// Check & get attachment file
		if (!empty($this->msg->filename)) {
			// Get file's content
			$disk = StorageDisk::getDisk();
			if ($disk->exists($this->msg->filename)) {
				$fileData = $disk->get($this->msg->filename);
			}
			
			// Get file's short name
			$filename = last(explode(DIRECTORY_SEPARATOR, $this->msg->filename));
		}
		
		// Attachment
		if (
			isset($fileData, $filename)
			&& !empty($fileData)
			&& !empty($filename)
		) {
			return $mailMessage->attachData($fileData, $filename);
		} else {
			return $mailMessage;
		}
	}
	
	public function toNexmo($notifiable)
	{
		return (new NexmoMessage())->content($this->smsMessage())->unicode();
	}
	
	public function toTwilio($notifiable)
	{
		return (new TwilioSmsMessage())->content($this->smsMessage());
	}
	
	protected function smsMessage()
	{
		return trans('sms.post_employer_contacted_content', [
			'appName' => config('app.name'),
			'postId'  => $this->msg->post_id,
			'message' => Str::limit(strip_tags($this->msg->message), 50),
		]);
	}
}
