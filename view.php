<?php
//##copyright##

/*-- START MOD // sasha_gar --*/
$iaCoupon = $iaCore->factoryPackage('coupon', IA_CURRENT_PACKAGE);
$iaCateg = $iaCore->factoryPackage('ccat', IA_CURRENT_PACKAGE);

if (iaView::REQUEST_JSON == $iaView->getRequestType())
{
	if ('report' == $_POST['action'])
	{
		$id = (int)$_POST['id'];
		$comment = '';
		if ((isset($_POST['comments']) && $_POST['comments']))
		{
			$time = date('Y-m-d H:i:s');
			$iaCore->factory('util');
			$ip = iaUtil::getIp(false);
			$comment = <<<COMMENT
Date: {$time}
IP: {$ip}
Comment: {$_POST['comments']}


COMMENT;
		}

		$listing = $iaCoupon ->getById($id);

		$iaMailer = $iaCore->factory('mailer');
		$iaMailer->loadTemplate('reported_as_broken');
		$iaMailer->setReplacements(array(
			'title' => $listing['title'],
			'comments' => $comment,
		));
		$iaMailer->sendToAdministrators();

		$email = (isset($listing['email']) && $listing['email']) ? $listing['email'] : $iaDb->one('email', iaDb::convertIds($listing['member_id']), iaUsers::getTable());

		if ($email)
		{
			$iaMailer->loadTemplate('reported_as_broken');
			$iaMailer->setReplacements(array(
				'title' => $listing['title'],
				'comments' => $comment,
			));
			$iaMailer->addAddress($email);

			$iaMailer->send();
		}
		$fields = array('reported_as_broken' => 1);
		if ($comment)
		{
			if (isset($listing['reported_as_broken_comments']) && $listing['reported_as_broken_comments'])
			{
				$comment = $listing['reported_as_broken_comments'] . $comment;
			}
			$fields['reported_as_broken_comments'] = $comment;
		}
		$iaDb->update($fields, iaDb::convertIds($id), null, iaCoupon::getTable());
	}
}
/*-- END MOD // sasha_gar --*/


if (iaView::REQUEST_HTML == $iaView->getRequestType())
{


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
				$actionUrls = array(
					iaCore::ACTION_EDIT => $iaCoupon->url(iaCore::ACTION_EDIT, $coupon),
					iaCore::ACTION_DELETE => $iaCoupon->url(iaCore::ACTION_DELETE, $coupon)
				);
				$iaView->assign('tools', $actionUrls);

				$iaItem->setItemTools(array(
					'id' => 'action-edit',
					'title' => iaLanguage::get('edit_coupon'),
					'attributes' => array(
						'href' => $actionUrls[iaCore::ACTION_EDIT]
					)
				));
				$iaItem->setItemTools(array(
					'id' => 'action-delete',
					'title' => iaLanguage::get('delete_coupon'),
					'attributes' => array(
						'href' => $actionUrls[iaCore::ACTION_DELETE],
						'class' => 'js-delete-coupon'
					)
				));
			}
		}
	}

	/*-- START MOD // sasha_gar --*/
	$iaItem->setItemTools(array(
		'id' => 'action-report',
		'title' => iaLanguage::get('report_coupon'),
		'attributes' => array(
			'href' => '#',
			'id' => 'js-cmd-report-coupon',
			'data-id' => $coupon['id']
		)
	));
	/*-- END MOD // sasha_gar --*/

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