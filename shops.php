<?php
//##copyright##

if (iaView::REQUEST_HTML == $iaView->getRequestType())
{
	$iaShop = $iaCore->factoryPackage('shop', IA_CURRENT_PACKAGE);

	switch ($iaView->name())
	{
		case 'shop_view':
			$iaCoupon = $iaCore->factoryPackage('coupon', IA_CURRENT_PACKAGE);

			// get shop by id
			if (isset($iaCore->requestPath[0]) && !empty($iaCore->requestPath[0]))
			{
				$shop = $iaShop->getByAlias($iaCore->requestPath[0]);
				$iaView->assign('shop', $shop);

				if (empty($shop))
				{
					return iaView::errorPage(iaView::ERROR_NOT_FOUND);
				}

				// increment views counter
				$iaShop->incrementViewsCounter($shop['id']);

				$iaCore->startHook('phpViewListingBeforeStart', array(
					'listing' => $shop['id'],
					'item' => $iaShop->getItemName(),
					'title' => $shop['title'],
					'desc' => $shop['description']
				));

				// breadcrumb formation
				iaBreadcrumb::add(iaLanguage::get('shops'), IA_PACKAGE_URL . 'shops/');
				iaBreadcrumb::replaceEnd($shop['title']);

				$coupons = $couponsExpired = array();
				switch(true)
				{
					case !isset($_GET['sorting']) || 'all' == $_GET['sorting']:
						$coupons = $iaCoupon->getCoupons("`shop_id` = '{$shop['id']}' AND (`expire_date` >= NOW() OR `expire_date` = 1)", '`expire_date` ASC', 100);
						$couponsExpired = $iaCore->get('show_expired_coupons') ? $iaCoupon->getCoupons("`shop_id` = '{$shop['id']}' AND (`expire_date` < NOW() AND `expire_date` != 0)", '`expire_date` ASC', 100) : array();
						break;

					case ('active' == $_GET['sorting']):
						$coupons = $iaCoupon->getCoupons("`shop_id` = '{$shop['id']}' AND (`expire_date` >= NOW() OR `expire_date` = 1)", '`expire_date` ASC', 100);
						break;

					case ('expired' == $_GET['sorting']):
						$couponsExpired = $iaCore->get('show_expired_coupons') ? $iaCoupon->getCoupons("`shop_id` = '{$shop['id']}' AND (`expire_date` < NOW() AND `expire_date` != 0)", '`expire_date` ASC', 100) : array();
				}

				$iaView->assign('coupons', $coupons);
				$iaView->assign('coupons_expired', $couponsExpired);

				// set shop meta values
				$iaView->set('description', $shop['meta_description']);
				$iaView->set('keywords', $shop['meta_keywords']);

				$iaView->set('title', $shop['title']);
				$iaView->display('shop-view');
			}
			else
			{
				return iaView::errorPage(iaView::ERROR_NOT_FOUND);
			}

			break;

		case 'shop_add':



		case 'shops':
			$letters['all'] = iaUtil::getLetters();
			$letters['active'] = (isset($iaCore->requestPath[0]) && in_array($iaCore->requestPath[0], $letters['all'])) ? $iaCore->requestPath[0] : false;

			$cause = '';
			if ($letters['active'])
			{
				$cause = ('0-9' == $letters['active']) ?  "(`title` REGEXP '^[0-9]') " : "(`title` LIKE '{$letters['active']}%') ";

				// breadcrumb formation
				iaBreadcrumb::add(iaLanguage::get('shops'), IA_PACKAGE_URL . 'shops/');
				iaBreadcrumb::replaceEnd($letters['active']);
			}

			// check for letters that have shops
			$letters['existing'] = array();
			$array = $iaDb->all('DISTINCT UPPER(SUBSTR(`title`, 1, 1)) `letter`', "`status` = 'active'", null, null, iaShop::getTable());
			if ($array)
			{
				foreach ($array as $item)
				{
					$letters['existing'][] = $item['letter'];
				}
			}
			$iaView->assign('letters', $letters);

			// get shops
			$shops = $iaShop->getShops($cause);
			$iaView->assign('shops', $shops);

			$iaView->display('shops');

			break;

		default:
			return iaView::errorPage(iaView::ERROR_NOT_FOUND);
	}
}