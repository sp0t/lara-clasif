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
use Illuminate\Notifications\Messages\NexmoMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Jenssegers\Date\Date;
use NotificationChannels\Twilio\TwilioChannel;
use NotificationChannels\Twilio\TwilioSmsMessage;

class PostDeleted extends Notification implements ShouldQueue
{
    use Queueable;

    protected $post;

    public function __construct($post)
    {
        $this->post = $post;
    }

    public function via($notifiable)
    {
        if (!empty($this->post->email)) {
            return ['mail'];
        } else {
            if (config('settings.sms.driver') == 'twilio') {
                return [TwilioChannel::class];
            }

            return ['nexmo'];
        }
    }

    public function toMail($notifiable)
    {
		return (new MailMessage)
			->subject(trans('mail.post_deleted_title', ['title' => $this->post->title]))
			->greeting(trans('mail.post_deleted_content_1'))
			->line(trans('mail.post_deleted_content_2', [
				'title'   => $this->post->title,
				'now'     => Date::now(config('timezone.id'))->formatLocalized(config('settings.app.default_date_format')),
				'appUrl'  => lurl('/'),
				'appName' => config('app.name')
			]))
			->line(trans('mail.post_deleted_content_3'))
			->line('<br>')
			->line(trans('mail.post_deleted_content_4'));
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
        return trans('sms.post_deleted_content', ['appName' => config('app.name'), 'title' => $this->post->title]);
    }
}
