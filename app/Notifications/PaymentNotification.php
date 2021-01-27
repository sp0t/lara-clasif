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

use App\Helpers\UrlGen;
use App\Models\Package;
use App\Models\PaymentMethod;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;

class PaymentNotification extends Notification implements ShouldQueue
{
	use Queueable;
	
	protected $payment;
	protected $post;
	protected $package;
	protected $paymentMethod;
	
	public function __construct($payment, $post)
	{
		$this->payment = $payment;
		$this->post = $post;
		$this->package = Package::findTrans($payment->package_id);
		$this->paymentMethod = PaymentMethod::find($payment->payment_method_id);
	}
	
	public function via($notifiable)
	{
		return ['mail'];
	}
	
	public function toMail($notifiable)
	{
		$postUrl = UrlGen::post($this->post);
		
		return (new MailMessage)
			->subject(trans('mail.payment_notification_title'))
			->greeting(trans('mail.payment_notification_content_1'))
			->line(trans('mail.payment_notification_content_2', [
				'advertiserName' => $this->post->contact_name,
				'postUrl'        => $postUrl,
				'title'          => $this->post->title
			]))
			->line('<br>')
			->line(trans('mail.payment_notification_content_3', [
				'adId'              => $this->post->id,
				'packageName'       => (!empty($this->package->short_name)) ? $this->package->short_name : $this->package->name,
				'amount'            => $this->package->price,
				'currency'          => $this->package->currency_code,
				'paymentMethodName' => $this->paymentMethod->display_name
			]));
	}
}
