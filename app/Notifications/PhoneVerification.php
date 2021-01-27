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
use Illuminate\Notifications\Messages\NexmoMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use NotificationChannels\Twilio\TwilioChannel;
use NotificationChannels\Twilio\TwilioSmsMessage;

class PhoneVerification extends Notification implements ShouldQueue
{
    use Queueable;
    
    protected $entity;
    protected $entityRef;
    protected $tokenUrl;
    
    public function __construct($entity, $entityRef)
    {
        $this->entity = $entity;
        $this->entityRef = $entityRef;
    
        // Get the Token verification URL
        $this->tokenUrl = (isset($entityRef['slug'])) ? url(config('app.locale') . '/verify/' . $entityRef['slug'] . '/phone') : '';
    }
    
    public function via($notifiable)
    {
        if (!isset($this->entityRef['name'])) {
            return false;
        }
        
        if (config('settings.sms.driver') == 'twilio') {
            return [TwilioChannel::class];
        }
        
        return ['nexmo'];
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
        return trans('sms.phone_verification_content', [
            'appName'  => config('app.name'),
            'token'    => $this->entity->phone_token,
            'tokenUrl' => $this->tokenUrl,
        ]);
    }
}
