{if (isset($item.item) && 'coupons' == $item.item)}
	<div class="modal fade" id="report-coupon-modal" tabindex="-1" role="dialog" aria-labelledby="report-coupon">
		<div class="modal-dialog" role="document">
			<div class="modal-content">
				<form id="report-coupon-form" class="ia-form">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
						<h4 class="modal-title" id="myModalLabel">{lang key='do_you_want_report_problem'}</h4>
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

	<div class="modal fade" id="statistic-coupon-modal" tabindex="-1" role="dialog" aria-labelledby="statistics-coupon">
		<div class="modal-dialog" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
					<h4>{$item.title}</h4>
				</div>
				<div class="modal-body">
					<div class="number-views">
						<legend>Number views</legend>
						{$item.views_num}
					</div>
					{if $item.reported_as_problem}
						<legend>{lang key="reported_problem"}</legend>
						<div class="reported-problem">
							{$item.reported_as_problem_comments|nl2br}
						</div>

					{/if}
				</div>
			</div>
		</div>
	</div>
{/if}
