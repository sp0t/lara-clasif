<div class="modal fade" id="maintenanceMode">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
				<h4 class="modal-title">{{ trans('admin::messages.Maintenance Mode') }}</h4>
			</div>
			
			<form role="form" method="POST" action="{{ admin_url('actions/maintenance_down') }}">
				{!! csrf_field() !!}
				
				<div class="modal-body">
					
					@if (isset($errors) and $errors->any() and old('maintenanceForm')=='1')
						<div class="alert alert-danger">
							<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
							<ul class="list list-check">
								@foreach($errors->all() as $error)
									<li>{{ $error }}</li>
								@endforeach
							</ul>
						</div>
					@endif
				
					<!-- message -->
					<div class="form-group required <?php echo (isset($errors) and $errors->has('message')) ? ' is-invalid' : ''; ?>">
						<label for="message" class="control-label">{{ t('Message') }} <span class="text-count">(200 max)</span></label>
						<textarea id="message" name="message" class="form-control required" placeholder="Be right back." rows="3">{{ old('message') }}</textarea>
					</div>
					
					<input type="hidden" name="maintenanceForm" value="1">
				</div>
				
				<div class="modal-footer">
					<button type="button" class="btn btn-default pull-left" data-dismiss="modal">{{ t('Close') }}</button>
					<button type="submit" class="btn btn-primary">{{ trans('admin::messages.Put in Maintenance Mode') }}</button>
				</div>
			</form>
		</div>
		<!-- /.modal-content -->
	</div>
	<!-- /.modal-dialog -->
</div>
<!-- /.modal -->