<?php
//##copyright##

if (iaView::REQUEST_HTML == $iaView->getRequestType())
{
	$iaCoupon = $iaCore->factoryPackage('coupon', IA_CURRENT_PACKAGE);
	$iaCateg = $iaCore->factoryPackage('ccat', IA_CURRENT_PACKAGE);

	if (!isset($iaCore->requestPath[2]) || empty($iaCore->requestPath[2]))
	{
		return iaView::errorPage(iaView::ERROR_NOT_FOUND);
	}

	// get coupon info 
	$couponId = (int)$iaCore->requestPath[2];
	$coupon = $iaCoupon->getCoupons("t1.`id` = '{$couponId}'", '', 1, 0, false, true);

	if (empty($coupon))
	{
		return iaView::errorPage(iaView::ERROR_NOT_FOUND);
	}

	$coupon = array_shift($coupon);

	if ($coupon['status'] != iaCore::STATUS_ACTIVE
		&& $coupon['member_id'] != iaUsers::getIdentity()->id)
	{
		return iaView::errorPage(iaView::ERROR_NOT_FOUND);
	}

	$iaItem = $iaCore->factory('item');
	// update favorites state
	$coupon = array_shift($iaItem->updateItemsFavorites(array($coupon), $iaCoupon->getItemName()));
	$coupon['item'] = $iaCoupon->getItemName();
	$iaView->assign('item', $coupon);

	// increment views counter
	$iaCoupon->incrementViewsCounter($coupon['id']);

	// get shop info
	$iaShop = $iaCore->factoryPackage('shop', IA_CURRENT_PACKAGE);

	$shop = $coupon['shop_id'] ? $iaShop->getById($coupon['shop_id']) : array();
	$iaView->assign('shop', $shop);

	// get coupon category
	$couponCategory = $iaCateg->getCategory("`id` = '{$coupon['category_id']}'");
	$iaView->assign('coupon_category', $couponCategory);

	// get account information
	if ($coupon['member_id'])
	{
		$account = $iaCore->factory('users')->getInfo($coupon['member_id']);
		$iaView->assign('coupon_account', $account);
		if ($account)
		{
			if (iaUsers::hasIdentity() && $coupon['member_id'] == iaUsers::getIdentity()->id)
			{
				$iaItem->setItemTools(array(
					'id' => 'action-edit',
					'title' => iaLanguage::get('edit_coupon'),
					'attributes' => array(
						'href' => IA_PACKAGE_URL . 'coupons/edit/' . $coupon['id'] . '/'
					)
				));
				$iaItem->setItemTools(array(
					'id' => 'action-delete',
					'title' => iaLanguage::get('delete_coupon'),
					'attributes' => array(
						'href' => IA_PACKAGE_URL . 'coupons/delete/?id=' . $coupon['id']
					)
				));
			}
		}
	}

	$iaCore->startHook('phpViewListingBeforeStart', array(
		'listing' => $coupon['id'],
		'item' => $iaCoupon->getItemName(),
		'title' => $coupon['title'],
		'desc' => $coupon['short_description']
	));

	// breadcrumb formation
	iaBreadcrumb::add(iaLanguage::get('shops'), IA_PACKAGE_URL . 'shops/');
	iaBreadcrumb::add($shop['title'], $iaShop->url('view', $shop));

	// set coupon meta values
	$iaView->set('title', $coupon['title']);
	$iaView->set('description', $coupon['meta_description']);
	$iaView->set('keywords', $coupon['meta_keywords']);

	$iaView->display('coupon-view');
}