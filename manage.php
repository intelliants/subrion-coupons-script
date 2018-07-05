<?php
/******************************************************************************
 *
 * Subrion Coupons & Deals Software
 * Copyright (C) 2018 Intelliants, LLC <https://intelliants.com>
 *
 * This file is part of Subrion Coupons & Deals Software.
 *
 * This program is a commercial software and any kind of using it must agree
 * to the license, see <https://subrion.pro/license.html>.
 *
 * This copyright notice may not be removed from the software source without
 * the permission of Subrion respective owners.
 *
 *
 * https://subrion.pro/product/coupons-script.html
 *
 ******************************************************************************/

// process ajax actions
if (iaView::REQUEST_JSON == $iaView->getRequestType()) {
    if (isset($_GET['q'])) {
        $fieldName = 'title_' . $iaView->language;

        $where = "`{$fieldName}` LIKE '%" . iaSanitize::sql($_GET['q']) . "%' ";
        $order = "ORDER BY `{$fieldName}` ASC ";

        $shops = $iaDb->onefield($fieldName, $where . $order, 0, 15, 'coupons_shops');

        $iaView->assign(['options' => $shops]);
    }

    if (isset($_GET['action']) && 'validate' == $_GET['action'] && !empty($_GET['shop'])) {
        $shop = iaSanitize::sql($_GET['shop']);
        $where = "`title` LIKE '%{$shop}%' ";

        $out['data'] = $iaDb->one("`website`", $where, 'coupons_shops');

        $iaView->assign($out);
    }
}

if (iaView::REQUEST_HTML == $iaView->getRequestType()) {
    if (!$iaCore->get('coupon_add_guest', true) && !iaUsers::hasIdentity()) {
        return iaView::accessDenied(iaLanguage::getf('coupon_add_no_auth', ['base_url' => IA_URL]));
    }

    $iaCoupon = $iaCore->factoryItem('coupon');

    switch ($pageAction) {
        case iaCore::ACTION_ADD:
        case iaCore::ACTION_EDIT:
            iaBreadcrumb::remove(-2);

            $iaField = $iaCore->factory('field');

            if (iaCore::ACTION_ADD == $pageAction) {
                // set default coupon values
                $couponEntry = [];
            } elseif (iaCore::ACTION_EDIT == $pageAction) {
                $couponId = (int)end($iaCore->requestPath);

                if (empty($couponId)) {
                    return iaView::errorPage(iaView::ERROR_NOT_FOUND);
                } else {
                    $couponEntry = $iaCoupon->getById($couponId);

                    if (empty($couponEntry)) {
                        return iaView::errorPage(iaView::ERROR_NOT_FOUND);
                    }
                    if (!iaUsers::hasIdentity() || $couponEntry['member_id'] != iaUsers::getIdentity()->id) {
                        return iaView::accessDenied();
                    }
                }
            }

            $iaPlan = $iaCore->factory('plan');
            $iaView->assign('plans', $iaPlan->getPlans($iaCoupon->getItemName()));

            $couponEntry['item'] = $iaCoupon->getItemName();

            // get coupon fields
            $sections = $iaField->getTabs($iaCoupon->getItemName(), $couponEntry);
            $iaView->assign('sections', $sections);

            // get categories
            $iaCateg = $iaCore->factoryItem('ccat');

            $categories = [];

            $iaCateg->getAllCategories($iaCateg->getRootId(), $categories);
            $iaView->assign('coupon_categories', $categories);

            if (isset($_POST['data-coupon'])) {
                $error = false;
                $messages = [];
                $item = [];

                $iaUtil = $iaCore->factory('util');

                list($item, $error, $messages) = $iaField->parsePost($iaCoupon->getItemName(), $couponEntry);

                if (!iaUsers::hasIdentity() && !iaValidate::isCaptchaValid()) {
                    $error = true;
                    $messages[] = iaLanguage::get('confirmation_code_incorrect');
                }

                $item['member_id'] = 0;
                $item['shop_id'] = 0;

                if (iaUsers::hasIdentity()) {
                    $item['member_id'] = iaUsers::getIdentity()->id;
                } elseif ($iaCore->get('listing_tie_to_member') && !empty($item['email'])) {
                    $iaUsers = $iaCore->factory('users');
                    if ($member = $iaUsers->getInfo($item['email'], 'email')) {
                        $item['member_id'] = $member['id'];
                    }
                }

                // assign category value
                if (isset($_POST['category_id']) && $_POST['category_id']) {
                    $category = $iaCateg->getById((int)$_POST['category_id']);
                    if ($category && empty($category['locked'])) {
                        $item['category_id'] = $category['id'];
                    } elseif ($category && !empty($category['locked'])) {
                        $error = true;
                        $messages[] = iaLanguage::get('coupon_category_locked');
                    } else {
                        $error = true;
                        $messages[] = iaLanguage::get('coupon_category_empty');
                    }
                } else {
                    $error = true;
                    $messages[] = iaLanguage::get('coupon_category_empty');
                }

                if ($_POST['expire_date'] && time() >= strtotime($_POST['expire_date'])) {
                    $error = true;
                    $messages[] = iaLanguage::get('error_expire_date_in_past');
                }

                // assign title alias
                $item['title_alias'] = iaSanitize::alias($item['title_' . $iaView->language]);

                // assign item status
                $item['status'] = $iaCore->get('coupons_auto_approval') ? iaCore::STATUS_ACTIVE : iaCore::STATUS_APPROVAL;

                if (!$iaCoupon->isSubmissionAllowed($item['member_id'])) {
                    $error = true;
                    $messages[] = iaLanguage::get('limit_is_exceeded');
                }

                // assign shop value
                if (!$error) {
                    if (!empty($_POST['shop'])) {
                        $iaShop = $iaCore->factoryItem('shop');

                        if ($shopData = $iaShop->getByTitle($_POST['shop'])) {
                            $item['shop_id'] = $shopData['id'];
                        } else {
                            if ($iaCore->get('shop_submission')) {
                                // add shop here
                                $newShop = [
                                    'domain' => '',
                                    'website' => iaUtil::checkPostParam('website')
                                ];

                                if ($newShop['website']) {
                                    $domain = parse_url($newShop['website'], PHP_URL_HOST);
                                    $domain = str_ireplace('www.', '', $domain);

                                    $newShop['domain'] = $domain;
                                }

                                foreach ($iaCore->languages as $iso => $language) {
                                    $newShop['title_' . $iso] = $_POST['shop'];
                                }

                                $shopTitle = $_POST['shop'];
                                if (empty($newShop['website']) || 'http://' == $newShop['website']) {
                                    $newShop['title_alias'] = $shopData['title_alias'] = iaSanitize::alias($shopTitle);
                                    unset($newShop['website']);
                                } else {
                                    $newShop['title_alias'] = $shopData['title_alias'] = iaSanitize::alias($newShop['domain'] ? $newShop['domain'] : $shopTitle);
                                }

                                $item['shop_id'] = $iaShop->insert($newShop);

                                $messages[] = iaLanguage::get('shop_added');
                            } else {
                                $error = true;
                                $messages[] = iaLanguage::get('error_shop_incorrect');
                            }
                        }
                    } else {
                        $error = true;
                        $messages[] = iaLanguage::get('error_shop_incorrect');
                    }
                }

                if (!$error) {
                    if (iaCore::ACTION_ADD == $pageAction) {
                        // insert coupon
                        $item['id'] = $iaCoupon->insert($item);
                    } elseif (iaCore::ACTION_EDIT == $pageAction) {
                        $iaCoupon->update($item, $couponId);
                        $item['id'] = $couponId;
                    }

                    // implement common hook
                    $iaCore->startHook('phpAddItemAfterAll', [
                        'type' => 'front',
                        'listing' => $item['id'],
                        'item' => $iaCoupon->getItemName(),
                        'data' => $item,
                        'old' => $couponEntry
                    ]);

                    // redirect
                    $couponEntry['id'] = $item['id'];
                    $item['shop_alias'] = $shopData['title_alias'];

                    $url = $iaCoupon->url(('simple' === $item['type'] ? 'view-code' : 'view'), $item);
                    if (!iaUsers::hasIdentity() && 'simple' === $item['type']) {
                        $url = IA_URL;
                    }

                    if (isset($_POST['plan_id']) && $_POST['plan_id']) {
                        $plan = $iaPlan->getById($_POST['plan_id']);
                        if ($plan['cost'] > 0) {
                            $redirectUrl = $iaPlan->prePayment($iaCoupon->getItemName(), $couponEntry, $plan['id'], $url);

                            iaUtil::redirect(iaLanguage::get('redirect'), iaLanguage::get('coupon_added_active'), $redirectUrl);
                        }
                    } else {
                        iaUtil::go_to($url);
                    }
                } else {
                    $iaView->setMessages($messages, iaView::ERROR);
                }

                $couponEntry = $item;
            }

            $iaView->assign('item', $couponEntry);

            $iaView->display('coupon-add');

            break;

        case iaCore::ACTION_DELETE:
            $id = (int)(isset($_GET['id']) ? $_GET['id'] : end($iaCore->requestPath));
            if (!isset($id) || empty($id)) {
                return iaView::errorPage(iaView::ERROR_NOT_FOUND);
            }

            // get coupon info
            $coupon = $iaCoupon->getById((int)$id);

            if (empty($coupon)) {
                return iaView::errorPage(iaView::ERROR_NOT_FOUND);
            }

            if ($coupon['member_id'] != $iaCore->factory('users')->getIdentity()->id) {
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
