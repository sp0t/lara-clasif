@if (isset($errors) and $errors->any())
    <div class="col-xl-12">
        <div class="alert alert-danger">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
            <h5><strong>{{ t('Oops ! An error has occurred. Please correct the red fields in the form') }}</strong></h5>
            <ul class="list list-check">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    </div>
@endif

@if (Session::has('flash_notification'))
    <div class="col-xl-12">
        <div class="row">
            <div class="col-xl-12">
                @include('flash::message')
            </div>
        </div>
    </div>
@endif