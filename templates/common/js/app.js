$(function()
{
	$('#timer-{$randId}-{$listing.id}').countdown(
		{
			date: '{$listing.expire_date|date_format:$core.config.date_format}',
			htmlTemplate: "%dd %hh %im %ss left"
		});

	var clip_{$listing.id} = new ZeroClipboard($('.clip_{$listing.id}'),
	{
		moviePath: '{$smarty.const.IA_CLEAR_URL}js/utils/zeroclipboard/ZeroClipboard.swf',
		hoverClass: 'hover',
		activeClass: 'active'
	});

	clip_{$listing.id}.on('complete', function(client, args)
	{
		var affiliateLink = $(this).data('affiliate-link');
		var couponLink    = $(this).data('coupon-link');

		if ('undefined' != typeof affiliateLink && '' != affiliateLink)
		{
			window.location.href = couponLink;
			window.open(affiliateLink, '_blank');
		}
		else
		{
			window.location.href = couponLink;
		}
	});


	$('#coupon-list-{$listing.id} h4.media-heading a:first').on('click', function(e)
	{
		e.preventDefault();

		var affiliateLink = $('#coupon-list-{$listing.id}').data('affiliate-link');
		var couponLink    = '{ia_url type='url' item='coupons' data=$listing}';

		if ('undefined' != typeof affiliateLink && '' != affiliateLink)
		{
			window.location.href = couponLink;
			window.open(affiliateLink, '_blank');
		}
		else
		{
			window.location.href = couponLink;
		}
	});
});