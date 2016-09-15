{if (isset($item.item) && 'coupons' == $item.item)}
	<div class="modal fade" id="report-coupon-modal" tabindex="-1" role="dialog" aria-labelledby="report-coupon">
		<div class="modal-dialog" role="document">
			<div class="modal-content">
				<form id="report-coupon-form" class="ia-form">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
						<h4 class="modal-title" id="myModalLabel">{lang key='do_you_want_report_broken'}</h4>
					</div>
					<div class="modal-body">
						<div class="form-group">
							<label for="report-coupon-comment">{lang key='comment'}:</label>
							<textarea name="report-coupon-comment" id="report-coupon-comment" class="form-control" rows="5" required></textarea>
						</div>
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-default" data-dismiss="modal">{lang key='cancel'}</button>
						<button type="submit" class="btn btn-primary">{lang key='submit'}</button>
					</div>
				</form>
			</div>
		</div>
	</div>
{/if}
