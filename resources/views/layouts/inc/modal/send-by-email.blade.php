<div class="modal fade" id="sendByEmail" tabindex="-1" role="dialog">
	<div class="modal-dialog">
		<div class="modal-content">
			
			<div class="modal-header">
				<h4 class="modal-title">
					<i class="fa icon-info-circled-alt"></i> {{ t('Send by Email') }}
				</h4>
				
				<button type="button" class="close" data-dismiss="modal">
					<span aria-hidden="true">&times;</span>
					<span class="sr-only">{{ t('Close') }}</span>
				</button>
			</div>
			
			<form role="form" method="POST" action="{{ lurl('send-by-email') }}">
				<div class="modal-body">

					@if (isset($errors) and $errors->any() and old('sendByEmailForm')=='1')
						<div class="alert alert-danger">
							<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
							<ul class="list list-check">
								@foreach($errors->all() as $error)
									<li>{{ $error }}</li>
								@endforeach
							</ul>
						</div>
					@endif

					{!! csrf_field() !!}

					<!-- sender_email -->
					@if (auth()->check() and isset(auth()->user()->email))
						<input type="hidden" name="sender_email" value="{{ auth()->user()->email }}">
					@else
						<?php $senderEmailError = (isset($errors) and $errors->has('sender_email')) ? ' is-invalid' : ''; ?>
						<div class="form-group required">
							<label for="sender_email" class="control-label">{{ t('Your Email') }} <sup>*</sup></label>
							<div class="input-group">
								<div class="input-group-prepend">
									<span class="input-group-text"><i class="icon-mail"></i></span>
								</div>
								<input id="sender_email" name="sender_email" type="text" maxlength="60" class="form-control{{ $senderEmailError }}" value="{{ old('sender_email') }}">
							</div>
						</div>
					@endif

					<!-- recipient_email -->
					<?php $recipientEmailError = (isset($errors) and $errors->has('recipient_email')) ? ' is-invalid' : ''; ?>
					<div class="form-group required">
						<label for="recipient_email" class="control-label">{{ t('Recipient Email') }} <sup>*</sup></label>
						<div class="input-group">
							<div class="input-group-prepend">
								<span class="input-group-text"><i class="icon-mail"></i></span>
							</div>
							<input id="recipient_email" name="recipient_email" type="text" maxlength="60" class="form-control{{ $recipientEmailError }}" value="{{ old('recipient_email') }}">
						</div>
					</div>

					<input type="hidden" name="post" value="{{ old('post') }}">
					<input type="hidden" name="sendByEmailForm" value="1">
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-default" data-dismiss="modal">{{ t('Cancel') }}</button>
					<button type="submit" class="btn btn-primary">{{ t('Send') }}</button>
				</div>
			</form>
		</div>
	</div>
</div>