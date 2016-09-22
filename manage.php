<?php
//##copyright##

// process ajax actions
if (iaView::REQUEST_JSON == $iaView->getRequestType())
{
	if (isset($_GET['q']))
	{
		$where = "`title` LIKE '%{$_GET['q']}%' ";
		$order = "ORDER BY `title` ASC ";

		$shops['options'] = $iaDb->onefield('title', $where . $order, 0, 15, 'coupons_shops');
		$iaView->assign($shops);
	}

	if (isset($_GET['action']) && 'validate' == $_GET['action'] && !empty($_GET['shop']))
	{
		$shop = iaSanitize::sql($_GET['shop']);
		$where = "`title` LIKE '%{$shop}%' ";

		$out['data'] = $iaDb->one("`website`", $where, 'coupons_shops');
		$iaView->assign($out);
	}
}

if (iaView::REQUEST_HTML == $iaView->getRequestType())
{
	if (!$iaCore->get('coupon_add_guest', true) && !iaUsers::hasIdentity())
	{
		return iaView::accessDenied(iaLanguage::getf('coupon_add_no_auth', array('base_url' => IA_URL)));
	}

	$iaCoupon = $iaCore->factoryPackage('coupon', IA_CURRENT_PACKAGE);

	switch ($pageAction)
	{
		case iaCore::ACTION_ADD:
		case iaCore::ACTION_EDIT:
			iaBreadcrumb::remove(-2);

			$iaField = $iaCore->factory('field');

			if (iaCore::ACTION_ADD == $pageAction)
			{
				// set default coupon values
				$couponEntry = array(
					'expire_date' => false
				);
			}
			elseif (iaCore::ACTION_EDIT == $pageAction)
			{
				$couponId = (int)end($iaCore->requestPath);

				if (empty($couponId))
				{
					return iaView::errorPage(iaView::ERROR_NOT_FOUND);
				}
				else
				{
					$couponEntry = $iaCoupon->getById($couponId);
					if (empty($couponEntry))
					{
						return iaView::errorPage(iaView::ERROR_NOT_FOUND);
					}
					else
					{
						if (!iaUsers::hasIdentity() || $couponEntry['member_id'] != iaUsers::getIdentity()->id)
						{
							return iaView::accessDenied();
						}
					}
				}
			}

			$iaPlan = $iaCore->factory('plan');
			$iaView->assign('plans', $iaPlan->getPlans($iaCoupon->getItemName()));

			$couponEntry['item'] = $iaCoupon->getItemName();

			// get coupon fields
			$sections = array(
				'common' => $iaField->filterByGroup($couponEntry),
			);
			$iaView->assign('sections', $sections);

			// get categories
			$iaCateg = $iaCore->factoryPackage('ccat', IA_CURRENT_PACKAGE);

			$categories = array();

			$iaCateg->getAllCategories($iaCateg->getRoot(), $categories);
			$iaView->assign('coupon_categories', $categories);

		if (isset($_POST['data-coupon']))
		{
			$error = false;
			$messages = array();
			$item = false;
			$data = array();

			$iaUtil = $iaCore->factory('util');

			$fields = $iaField->filter($couponEntry, $iaCoupon->getItemName());
			list($data, $error, $messages) = $iaField->parsePost($fields, $couponEntry);

			if (!iaUsers::hasIdentity() && !iaValidate::isCaptchaValid())
			{
				$error = true;
				$messages[] = iaLanguage::get('confirmation_code_incorrect');
			}

			$item['ip'] = $iaUtil->getIp();
			$item['member_id'] = 0;
			if (iaUsers::hasIdentity())
			{
				$item['member_id'] = iaUsers::getIdentity()->id;
			}
			elseif($iaCore->get('listing_tie_to_member'))
			{
				$iaUsers = $iaCore->factory('users');
				$member = $iaUsers->getInfo($data['email'], 'email');

				$item['member_id'] = ($member) ? $member['id'] : 0;
			}

			// assign category value
			if (isset($_POST['category_id']) && $_POST['category_id'])
			{
				$category = $iaCateg->getById((int)$_POST['category_id']);
				if ($category && empty($category['locked']))
				{
					$data['category_id'] = $category['id'];
				}
				elseif ($category && !empty($category['locked']))
				{
					$error = true;
					$messages[] = iaLanguage::get('coupon_category_locked');
				}
				else
				{
					$error = true;
					$messages[] = iaLanguage::get('coupon_category_empty');
				}
			}
			else
			{
				$error = true;
				$messages[] = iaLanguage::get('coupon_category_empty');
			}

			// assign title alias
			$data['title_alias'] = iaSanitize::alias($data['title']);

			// assign item status
			$data['status'] = $iaCore->get('coupons_auto_approval') ? iaCore::STATUS_ACTIVE : iaCore::STATUS_APPROVAL;

			// assign expire date
			$data['expire_date'] = ($data['expire_date'] ? date(iaDb::DATE_FORMAT, strtotime($_POST['expire_date'])): '');

			// assign shop value
			if (!empty($_POST['shop']) && !$error)
			{
				$shopTitle = $_POST['shop'];

				$shopData = $iaDb->row(iaDB::ALL_COLUMNS_SELECTION, "`title` = '{$shopTitle}'", 'coupons_shops');
				if (empty($shopData))
				{
					if ($iaCore->get('shop_submission'))
					{
						// add shop here
						$iaShop = $iaCore->factoryPackage('shop', IA_CURRENT_PACKAGE);

						$newShop = array(
							'member_id' => iaUsers::hasIdentity() ? iaUsers::getIdentity()->id : 0,
							'title' => $shopTitle,
							'website' => iaUtil::checkPostParam('website'),
						);

						$newShop['domain'] = $newShop['website'] ? str_ireplace('www.', '', parse_url($newShop['website'], PHP_URL_HOST)) : '';

						if (empty($newShop['website']) || 'http://' == $newShop['website'])
						{
							$newShop['title_alias'] = $shopData['title_alias'] = iaSanitize::alias($newShop['title'] ? $newShop['title'] : $shopTitle);
							unset($newShop['website']);
						}
						else
						{
							$newShop['title_alias'] = $shopData['title_alias'] = iaSanitize::alias($newShop['domain'] ? $newShop['domain'] : $shopTitle);
						}

						$data['shop_id'] = $iaShop->insert($newShop);

						$messages[] = iaLanguage::get('shop_added');
					}
					else
					{
						$error = true;
						$messages[] = iaLanguage::get('error_shop_incorrect');
					}
				}
				else
				{
					$data['shop_id'] = $shopData['id'];
				}
			}
			else
			{
				$error = true;
				$messages[] = iaLanguage::get('error_shop_incorrect');
			}

			if (!$iaCoupon->isSubmissionAllowed($item['member_id']))
			{
				$error = true;
				$messages[] = iaLanguage::get('limit_is_exceeded');
			}

			if (!$error)
			{
				if (iaCore::ACTION_ADD == $pageAction)
				{
					// insert coupon
					$data['id'] = $iaCoupon->insert($data);
				}
				elseif (iaCore::ACTION_EDIT == $pageAction)
				{
					$iaCoupon->update($data, $couponId);
					$data['id'] = $couponId;
				}

				// implement common hook
				$iaCore->startHook('phpAddItemAfterAll', array(
					'type' => 'front',
					'listing' => $data['id'],
					'item' => $iaCoupon->getItemName(),
					'data' => $data,
					'old' => $couponEntry
				));

				// redirect
				$couponEntry['id'] = $data['id'];
				$data['shop_alias'] = $shopData['title_alias'];

				if (isset($_POST['plan_id']) && $_POST['plan_id'])
				{
					$plan = $iaPlan->getById($_POST['plan_id']);
					if ($plan['cost'] > 0)
					{
						$url = $iaPlan->prePayment($iaCoupon->getItemName(), $couponEntry, $plan['id'], $iaCoupon->url('view', $data));

						iaUtil::redirect(iaLanguage::get('redirect'), $messages, $url);
					}
				}
				else
				{
					iaUtil::go_to($iaCoupon->url('view', $data));
				}

			}
			else
			{
				iaField::keepValues($data, $fields, $couponEntry);

				$iaView->setMessages($messages, iaView::ERROR);
			}

			$couponEntry = $data;
		}

			$iaView->assign('item', $couponEntry);

			$iaView->display('coupon-add');

			break;

		case iaCore::ACTION_DELETE:
			$id = (int)(isset($_GET['id']) ? $_GET['id'] : end($iaCore->requestPath));
			if (!isset($id) || empty($id))
			{
				return iaView::errorPage(iaView::ERROR_NOT_FOUND);
			}

			// get coupon info
			$coupon = $iaCoupon->getById((int)$id);

			if (empty($coupon))
			{
				return iaView::errorPage(iaView::ERROR_NOT_FOUND);
			}

			if ($coupon['member_id'] != $iaCore->factory('users')->getIdentity()->id)
			{
				return iaView::errorPage(iaView::ERROR_UNAUTHORIZED);
			}

			$result = $iaCoupon->delete($coupon['id']);

			iaUtil::redirect(
				iaLanguage::get($result ? 'thanks' : 'error'),
				iaLanguage::get($result ? 'coupon_deleted' : 'db_error'),
				$iaCoupon->url($result ? 'my' : 'view', $coupon)
			);

			break;

		default:
			return iaView::errorPage(iaView::ERROR_NOT_FOUND);
	}
}