$(function()
{
	$('#action-delete').on('click', function()
	{
		return confirm(intelli.lang.delete_coupon_confirmation);
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