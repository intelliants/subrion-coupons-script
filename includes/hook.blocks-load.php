<?php
//##copyright##

if (iaView::REQUEST_HTML == $iaView->getRequestType() && iaCore::ACCESS_FRONT == $iaCore->getAccessType())
{
	$limit = 5;
	$couponBlocks = [];

	$iaCoupon = $iaCore->factoryModule('coupon', 'coupons');
	$iaCcat = $iaCore->factoryModule('ccat', $iaCoupon->getModuleName());
	$iaShop = $iaCore->factoryModule('shop', $iaCcat->getModuleName());

	// include coupons css
	$iaView->add_css('_IA_URL_modules/coupons/templates/front/css/coupons');
	$iaView->add_js('_IA_URL_js/utils/zeroclipboard/ZeroClipboard.min, _IA_URL_modules/coupons/js/front/jquery.countdown.min, _IA_URL_modules/coupons/js/front/app');

	$stmt = $iaCore->get('show_expired_coupons') ? iaDb::EMPTY_CONDITION : 't1.`expire_date` >= NOW()';

	$couponBlocks['top_categories'] = $iaCcat->getCategories("`level` = 1 && `status` = 'active' ");

	if ($iaView->blockExists('browse_coupons'))
	{
		if ('coupons_home' == $iaView->name())
		{
			// get category by alias
			$categoryAlias = $iaCore->requestPath ? (string)implode(IA_URL_DELIMITER, $iaCore->requestPath) : '';
			$currentCategory = $iaCcat->getCategory(iaDb::convertIds($categoryAlias, 'title_alias'));
			$iaView->assign('current_category', $currentCategory);

			$where = iaDb::convertIds($currentCategory['id'], iaCcat::COL_PARENT_ID);
		}
		else
		{
			$where = '`level` = 1';
		}
		$where .= " AND `status` = 'active' ";

		$categories = $iaCcat->getCategories($where);
		$iaView->assign('coupons_categories', $categories);
	}

	if ($iaView->blockExists('top_coupons'))
	{
		$couponBlocks['top'] = $iaCoupon->get($stmt, '`thumbs_num` DESC', $iaCore->get('top_coupons_block_num'));
	}

	if ($iaView->blockExists('popular_shops'))
	{
		$couponBlocks['popular_shops'] = $iaShop->getPopular(null, $iaCore->get('popular_shops_max_items', $limit));
	}

	if ($iaView->blockExists('featured_coupons'))
	{
		$couponBlocks['featured'] = $iaCoupon->get($stmt . ' AND t1.`featured` = 1 AND t1.`featured_end` > NOW()', 'RAND()', $iaCore->get('featured_coupons_block_num'));
	}

	if ($iaView->blockExists('sponsored_coupons'))
	{
		$couponBlocks['sponsored'] = $iaCoupon->get($stmt . ' AND t1.`sponsored` = 1 AND t1.`sponsored_end` > NOW()', 'RAND()', $iaCore->get('sponsored_coupons_block_num'));
	}

	if ($iaView->blockExists('new_coupons'))
	{
		$couponBlocks['new'] = $iaCoupon->get($stmt, '`date_added` DESC', $iaCore->get('new_coupons_block_num'));
	}

	if ($iaView->blockExists('deal_of_the_day'))
	{
		$couponBlocks['oftheday'] = $iaCoupon->getDealOfTheDay();
	}

	if ($iaView->blockExists('featured_shops'))
	{
		$couponBlocks['featured_shops'] = $iaShop->getFeatured($iaCore->get('featured_shops_block_num'));
	}

	$iaView->assign('coupon_blocks', $couponBlocks);
}