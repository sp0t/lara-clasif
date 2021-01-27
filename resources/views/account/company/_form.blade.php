<?php
// From Company's Form
$classLeftCol = 'col-md-3';
$classRightCol = 'col-md-9';

if (isset($originForm)) {
	// From User's Form
	if ($originForm == 'user') {
		$classLeftCol = 'col-md-3';
		$classRightCol = 'col-md-7';
	}
	
	// From Post's Form
	if ($originForm == 'post') {
		$classLeftCol = 'col-md-3';
		$classRightCol = 'col-md-8';
	}
}
?>
<div id="companyFields">
	<!-- name -->
	<?php $companyNameError = (isset($errors) and $errors->has('company.name')) ? ' is-invalid' : ''; ?>
	<div class="form-group row required">
		<label class="{{ $classLeftCol }} control-label" for="company.name">{{ t('Company Name') }} <sup>*</sup></label>
		<div class="{{ $classRightCol }}">
			<input name="company[name]"
				   placeholder="{{ t('Company Name') }}"
				   class="form-control input-md{{ $companyNameError }}"
				   type="text"
				   value="{{ old('company.name', (isset($company->name) ? $company->name : '')) }}">
		</div>
	</div>

	<!-- logo -->
	<?php $companyLogoError = (isset($errors) and $errors->has('company.logo')) ? ' is-invalid' : ''; ?>
	<div class="form-group row">
		<label class="{{ $classLeftCol }} control-label{{ $companyLogoError }}" for="company.logo"> {{ t('Logo') }} </label>
		<div class="{{ $classRightCol }}">
			<div {!! (config('lang.direction')=='rtl') ? 'dir="rtl"' : '' !!} class="file-loading mb10">
				<input id="logo" name="company[logo]" type="file" class="file{{ $companyLogoError }}">
			</div>
			<small id="" class="form-text text-muted">
				{{ t('File types: :file_types', ['file_types' => showValidFileTypes('image')]) }}
			</small>
		</div>
	</div>

	<!-- description -->
	<?php $companyDescriptionError = (isset($errors) and $errors->has('company.description')) ? ' is-invalid' : ''; ?>
	<div class="form-group row required">
		<label class="{{ $classLeftCol }} control-label" for="company.description">{{ t('Company Description') }} <sup>*</sup></label>
		<div class="{{ $classRightCol }}">
			<textarea class="form-control{{ $companyDescriptionError }}"
					  name="company[description]"
					  rows="10"
			>{{ old('company.description', (isset($company->description) ? $company->description : '')) }}</textarea>
			<small id="" class="form-text text-muted">
				{{ t('Describe the company') }} - ({{ t(':number characters maximum', ['number' => 1000]) }})
			</small>
		</div>
	</div>

	@if (isset($company) and !empty($company))
		<!-- country_code -->
		<?php $companyCountryCodeError = (isset($errors) and $errors->has('company.country_code')) ? ' is-invalid' : ''; ?>
		<div class="form-group row required">
			<label class="{{ $classLeftCol }} control-label{{ $companyCountryCodeError }}" for="company.country_code">{{ t('Country') }}</label>
			<div class="{{ $classRightCol }}">
				<select id="countryCode" name="company[country_code]" class="form-control sselecter{{ $companyCountryCodeError }}">
					<option value="0" {{ (!old('company.country_code') or old('company.country_code')==0) ? 'selected="selected"' : '' }}> {{ t('Select a country') }} </option>
					@foreach ($countries as $item)
						<option value="{{ $item->get('code') }}"
								{{ (old('company.country_code',
								(isset($company->country_code) ? $company->country_code : ((!empty(config('country.code'))) ? config('country.code') : 0)))==$item->get('code')) ? 'selected="selected"' : '' }}>
							{{ $item->get('name') }}
						</option>
					@endforeach
				</select>
			</div>
		</div>
	
		<!-- city_id -->
		<?php $companyCityIdError = (isset($errors) and $errors->has('company.city_id')) ? ' is-invalid' : ''; ?>
		<div class="form-group row">
			<label class="{{ $classLeftCol }} control-label{{ $companyCityIdError }}" for="company.city_id">{{ t('City') }}</label>
			<div class="{{ $classRightCol }}">
				<select id="cityId" name="company[city_id]" class="form-control sselecter{{ $companyCityIdError }}">
					<option value="0" {{ (!old('company.city_id') or old('company.city_id')==0) ? 'selected="selected"' : '' }}>
						{{ t('Select a city') }}
					</option>
				</select>
			</div>
		</div>
	
		<!-- address -->
		<?php $companyAddressError = (isset($errors) and $errors->has('company.address')) ? ' is-invalid' : ''; ?>
		<div class="form-group row">
			<label class="{{ $classLeftCol }} control-label" for="company.address">{{ t('Address') }}</label>
			<div class="input-group {{ $classRightCol }}">
				<div class="input-group-prepend">
					<span class="input-group-text"><i class="icon-location"></i></span>
				</div>
				<input name="company[address]"
					   type="text"
					   class="form-control{{ $companyAddressError }}"
					   placeholder=""
					   value="{{ old('company.address', (isset($company->address) ? $company->address : '')) }}"
				>
			</div>
		</div>
	
		<!-- phone -->
		<?php $companyPhoneError = (isset($errors) and $errors->has('company.phone')) ? ' is-invalid' : ''; ?>
		<div class="form-group row">
			<label class="{{ $classLeftCol }} control-label" for="company.phone">{{ t('Phone') }}</label>
			<div class="input-group {{ $classRightCol }}">
				<div class="input-group-prepend">
					<span class="input-group-text"><i class="icon-phone-1"></i></span>
				</div>
				<input name="company[phone]" type="text"
					   class="form-control{{ $companyPhoneError }}" placeholder=""
					   value="{{ old('company.phone', (isset($company->phone) ? $company->phone : '')) }}">
			</div>
		</div>
	
		<!-- fax -->
		<?php echo (isset($errors) and $errors->has('company.fax')) ? ' is-invalid' : ''; ?>
		<div class="form-group row">
			<label class="{{ $classLeftCol }} control-label" for="company.fax">{{ t('Fax') }}</label>
			<div class="{{ $classRightCol }}">
				<div class="input-group">
					<div class="input-group-prepend">
						<span class="input-group-text"><i class="icon-print"></i></span>
					</div>
					<input name="company[fax]" type="text"
						   class="form-control" placeholder=""
						   value="{{ old('company.fax', (isset($company->fax) ? $company->fax : '')) }}">
				</div>
			</div>
		</div>
	
		<!-- email -->
		<?php $companyEmailError = (isset($errors) and $errors->has('company.email')) ? ' is-invalid' : ''; ?>
		<div class="form-group row">
			<label class="{{ $classLeftCol }} control-label" for="company.email">{{ t('Email') }}</label>
			<div class="input-group {{ $classRightCol }}">
				<div class="input-group-prepend">
					<span class="input-group-text"><i class="icon-mail"></i></span>
				</div>
				<input name="company[email]" type="text"
					   class="form-control{{ $companyEmailError }}" placeholder=""
					   value="{{ old('company.email', (isset($company->email) ? $company->email : '')) }}">
			</div>
		</div>
	
		<!-- website -->
		<?php $companyWebsiteError = (isset($errors) and $errors->has('company.website')) ? ' is-invalid' : ''; ?>
		<div class="form-group row">
			<label class="{{ $classLeftCol }} control-label" for="company.website">{{ t('Website') }}</label>
			<div class="input-group {{ $classRightCol }}">
				<div class="input-group-prepend">
					<span class="input-group-text"><i class="icon-globe"></i></span>
				</div>
				<input name="company[website]" type="text"
					   class="form-control{{ $companyWebsiteError }}" placeholder=""
					   value="{{ old('company.website', (isset($company->website) ? $company->website : '')) }}">
			</div>
		</div>
	
		<!-- facebook -->
		<?php $companyFacebookError = (isset($errors) and $errors->has('company.facebook')) ? ' is-invalid' : ''; ?>
		<div class="form-group row">
			<label class="{{ $classLeftCol }} control-label" for="company.facebook">Facebook</label>
			<div class="input-group {{ $classRightCol }}">
				<div class="input-group-prepend">
					<span class="input-group-text"><i class="icon-facebook"></i></span>
				</div>
				<input name="company[facebook]" type="text"
					   class="form-control{{ $companyFacebookError }}" placeholder=""
					   value="{{ old('company.facebook', (isset($company->facebook) ? $company->facebook : '')) }}">
			</div>
		</div>
	
		<!-- twitter -->
		<?php $companyTwitterError = (isset($errors) and $errors->has('company.twitter')) ? ' is-invalid' : ''; ?>
		<div class="form-group row">
			<label class="{{ $classLeftCol }} control-label" for="company.twitter">Twitter</label>
			<div class="input-group {{ $classRightCol }}">
				<div class="input-group-prepend">
					<span class="input-group-text"><i class="icon-twitter"></i></span>
				</div>
				<input name="company[twitter]" type="text"
					   class="form-control{{ $companyTwitterError }}" placeholder=""
					   value="{{ old('company.twitter', (isset($company->twitter) ? $company->twitter : '')) }}">
			</div>
		</div>
	
		<!-- linkedin -->
		<?php $companyLinkedinError = (isset($errors) and $errors->has('company.linkedin')) ? ' is-invalid' : ''; ?>
		<div class="form-group row">
			<label class="{{ $classLeftCol }} control-label" for="company.linkedin">Linkedin</label>
			<div class="input-group {{ $classRightCol }}">
				<div class="input-group-prepend">
					<span class="input-group-text"><i class="icon-linkedin"></i></span>
				</div>
				<input name="company[linkedin]" type="text"
					   class="form-control{{ $companyLinkedinError }}" placeholder=""
					   value="{{ old('company.linkedin', (isset($company->linkedin) ? $company->linkedin : '')) }}">
			</div>
		</div>
	
		<!-- googleplus -->
		<?php $companyGoogleplusError = (isset($errors) and $errors->has('company.googleplus')) ? ' is-invalid' : ''; ?>
		<div class="form-group row">
			<label class="{{ $classLeftCol }} control-label" for="company.googleplus">Google+</label>
			<div class="input-group {{ $classRightCol }}">
				<div class="input-group-prepend">
					<span class="input-group-text"><i class="icon-googleplus-rect"></i></span>
				</div>
				<input name="company[googleplus]" type="text"
					   class="form-control{{ $companyGoogleplusError }}" placeholder=""
					   value="{{ old('company.googleplus', (isset($company->googleplus) ? $company->googleplus : '')) }}">
			</div>
		</div>
	
		<!-- pinterest -->
		<?php $companyPinterestError = (isset($errors) and $errors->has('company.pinterest')) ? ' is-invalid' : ''; ?>
		<div class="form-group row">
			<label class="{{ $classLeftCol }} control-label" for="company.pinterest">Pinterest</label>
			<div class="input-group {{ $classRightCol }}">
				<div class="input-group-prepend">
					<span class="input-group-text"><i class="icon-docs"></i></span>
				</div>
				<input name="company[pinterest]" type="text"
					   class="form-control{{ $companyPinterestError }}" placeholder=""
					   value="{{ old('company.pinterest', (isset($company->pinterest) ? $company->pinterest : '')) }}">
			</div>
		</div>
	@endif
</div>

@section('after_styles')
	@parent
	<style>
		#companyFields .select2-container {
			width: 100% !important;
		}
		.file-loading:before {
			content: " {{ t('Loading') }}...";
		}
		.krajee-default.file-preview-frame .kv-file-content {
			height: auto;
		}
		.krajee-default.file-preview-frame .file-thumbnail-footer {
			height: 30px;
		}
	</style>
@endsection

@section('after_scripts')
	@parent
	<script>
		/* Initialize with defaults (logo) */
		$('#logo').fileinput(
		{
			theme: "fa",
			language: '{{ config('app.locale') }}',
			@if (config('lang.direction') == 'rtl')
				rtl: true,
			@endif
			dropZoneEnabled: false,
			showPreview: true,
			previewFileType: 'image',
			allowedFileExtensions: {!! getUploadFileTypes('image', true) !!},
			browseLabel: '{!! t("Browse") !!}',
			showUpload: false,
			showRemove: false,
			minFileSize: {{ (int)config('settings.upload.min_image_size', 0) }}, {{-- in KB --}}
			maxFileSize: {{ (int)config('settings.upload.max_image_size', 1000) }}, {{-- in KB --}}
			@if (isset($company) and !empty($company->logo) and isset($disk) and $disk->exists($company->logo))
				/* Retrieve Existing Logo */
				initialPreview: [
					'<img src="{{ imgUrl($company->logo, 'medium') }}" class="file-preview-image">',
				],
			@endif
			/* Remove Drag-Drop Icon (in footer) */
			fileActionSettings: {dragIcon: '', dragTitle: ''},
			layoutTemplates: {
				/* Show Only Actions (in footer) */
				footer: '<div class="file-thumbnail-footer pt-2">{actions}</div>',
				/* Remove Delete Icon (in footer) */
				actionDelete: ''
			}
		});
	</script>
	@if (isset($company) and !empty($company))
	<script>
		/* Translation */
		var lang = {
			'select': {
				'country': "{{ t('Select a country') }}",
				'admin': "{{ t('Select a location') }}",
				'city': "{{ t('Select a city') }}"
			}
		};

		/* Locations */
		var countryCode = '{{ old('company.country_code', (isset($company) ? $company->country_code : 0)) }}';
		var adminType = 0;
		var selectedAdminCode = 0;
		var cityId = '{{ old('company.city_id', (isset($company) ? $company->city_id : 0)) }}';
	</script>
	<script src="{{ url('assets/js/app/d.select.location.js') . vTime() }}"></script>
	@endif
@endsection