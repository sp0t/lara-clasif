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
} else {
	// Required var
	$originForm = null;
}
?>
<div id="resumeFields">
	
	@if ($originForm != 'message')
		@if (isset($resume) and !empty($resume))
			<!-- name -->
			<?php $resumeNameError = (isset($errors) and $errors->has('resume.name')) ? ' is-invalid' : ''; ?>
			<div class="form-group row">
				<label class="{{ $classLeftCol }} col-form-label" for="resume.name">{{ t('Name') }}</label>
				<div class="{{ $classRightCol }}">
					<input name="resume[name]"
						   placeholder="{{ t('Name') }}"
						   class="form-control input-md{{ $resumeNameError }}"
						   type="text"
						   value="{{ old('resume.name', (isset($resume->name) ? $resume->name : '')) }}">
				</div>
			</div>
		@endif
		
		<!-- filename -->
		<?php $resumeFilenameError = (isset($errors) and $errors->has('resume.filename')) ? ' is-invalid' : ''; ?>
		<div class="form-group row">
			<label class="{{ $classLeftCol }} col-form-label{{ $resumeFilenameError }}" for="resume.filename"> {{ t('Your resume') }} </label>
			<div class="{{ $classRightCol }}">
				<div class="mb10">
					<input id="resumeFilename" name="resume[filename]" type="file" class="file{{ $resumeFilenameError }}">
				</div>
				<small id="" class="form-text text-muted">{{ t('File types: :file_types', ['file_types' => showValidFileTypes('file')]) }}</small>
				@if (isset($resume))
					@if (!empty($resume) and !empty($resume->filename) and $disk->exists($resume->filename))
					<div>
						<a class="btn btn-default" href="{{ fileUrl($resume->filename) }}" target="_blank">
							<i class="icon-attach-2"></i> {{ t('Download current Resume') }}
						</a>
					</div>
					@endif
				@endif
			</div>
		</div>
	@else
		<!-- filename -->
		<?php $resumeFilenameError = (isset($errors) and $errors->has('resume.filename')) ? ' is-invalid' : ''; ?>
		<div class="form-group required" {!! (config('lang.direction')=='rtl') ? 'dir="rtl"' : '' !!}>
			<label for="resume.filename" class="col-form-label{{ $resumeFilenameError }}">{{ t('Resume File') }} </label>
			<input id="resumeFilename" name="resume[filename]" type="file" class="file{{ $resumeFilenameError }}">
			<small id="" class="form-text text-muted">{{ t('File types: :file_types', ['file_types' => showValidFileTypes('file')]) }}</small>
			@if (!empty($resume) and $disk->exists($resume->filename))
				<div>
					<a class="btn btn-default" href="{{ fileUrl($resume->filename) }}" target="_blank">
						<i class="icon-attach-2"></i> {{ t('Download current Resume') }}
					</a>
				</div>
			@endif
		</div>
	@endif

</div>

@section('after_styles')
	@parent
	<style>
		#resumeFields .select2-container {
			width: 100% !important;
		}
	</style>
@endsection

@section('after_scripts')
	@parent
	<script>
		/* Initialize with defaults (resume) */
		$('#resumeFilename').fileinput(
		{
			theme: "fa",
			language: '{{ config('app.locale') }}',
			@if (config('lang.direction') == 'rtl')
				rtl: true,
			@endif
			showPreview: false,
			allowedFileExtensions: {!! getUploadFileTypes('file', true) !!},
			showUpload: false,
			showRemove: false,
			minFileSize: {{ (int)config('settings.upload.min_file_size', 0) }}, {{-- in KB --}}
			maxFileSize: {{ (int)config('settings.upload.max_file_size', 1000) }} {{-- in KB --}}
		});
	</script>
@endsection