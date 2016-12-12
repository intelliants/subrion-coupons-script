$(function()
{
	$('.js-countdown').each(function() {
		var $this = $(this),
			finalDate = $this.data('countdown');

		$this.countdown(finalDate, function(event) {
			$this.html(event.strftime('%D days %H:%M:%S'));
		});
	})

	$('#action-delete').on('click', function(e)
	{
		e.preventDefault

		intelli.confirm(_t('delete_coupon_confirmation'), { url: $(this).attr('href') });
	});

	// thumbs actions
	$('a[class^="thumbs-"]').on('click', function(e)
	{
		e.preventDefault();

		var params = $(this).data();

		$.ajax(
		{
			url: intelli.config.packages.coupons.url + 'coupons/rate.json',
			type: 'get',
			dataType: 'json',
			data: params,
			success: function (response)
			{
				intelli.notifFloatBox({msg: response.message, type: response.error ? 'error' : 'success', autohide: true});
				response.error || $('#thumb_result_' + params.id).text(response.rating);
			}
		});
	});
});