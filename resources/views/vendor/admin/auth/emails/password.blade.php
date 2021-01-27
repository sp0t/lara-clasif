{{ trans('admin::messages.click_here_to_reset') }}: <a href="{{ $link = admin_url('password/reset', $token).'?email='.urlencode($user->getEmailForPasswordReset()) }}"> {{ $link }} </a>
