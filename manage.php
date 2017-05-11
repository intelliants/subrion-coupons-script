<?php
/******************************************************************************
 *
 * Subrion Coupons & Deals Software
 * Copyright (C) 2017 Intelliants, LLC <https://intelliants.com>
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

        $iaView->assign(array('options' => $shops));
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

    $iaCoupon = $iaCore->factoryModule('coupon', IA_CURRENT_MODULE);

    switch ($pageAction) {
        case iaCore::ACTION_ADD:
        case iaCore::ACTION_EDIT:
            iaBreadcrumb::remove(-2);

            $iaField = $iaCore->factory('field');

            if (iaCore::ACTION_ADD == $pageAction) {
                // set default coupon values
                $couponEntry = [
                    'expire_date' => false
                ];
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
            $iaCateg = $iaCore->factoryModule('ccat', IA_CURRENT_MODULE);

            $categories = [];

            $iaCateg->getAllCategories($iaCateg->getRootId(), $categories);
            $iaView->assign('coupon_categories', $categories);

            if (isset($_POST['data-coupon'])) {
                $error = false;
                $messages = [];
                $item = false;
                $data = [];

                $iaUtil = $iaCore->factory('util');

                list($data, $error, $messages) = $iaField->parsePost($iaCoupon->getItemName(), $couponEntry);

                if (!iaUsers::hasIdentity() && !iaValidate::isCaptchaValid()) {
                    $error = true;
                    $messages[] = iaLanguage::get('confirmation_code_incorrect');
                }

                $item['ip'] = $iaUtil->getIp();
                $item['member_id'] = 0;

                if (iaUsers::hasIdentity()) {
                    $item['member_id'] = iaUsers::getIdentity()->id;
                } elseif ($iaCore->get('listing_tie_to_member')) {
                    $iaUsers = $iaCore->factory('users');
                    if ($member = $iaUsers->getInfo($data['email'], 'email')) {
                        $item['member_id'] = $member['id'];
                    }
                }

                // assign category value
                if (isset($_POST['category_id']) && $_POST['category_id']) {
                    $category = $iaCateg->getById((int)$_POST['category_id']);
                    if ($category && empty($category['locked'])) {
                        $data['category_id'] = $category['id'];
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

                // assign title alias
                $data['title_alias'] = iaSanitize::alias($data['title_' . $iaView->language]);

                // assign item status
                $data['status'] = $iaCore->get('coupons_auto_approval') ? iaCore::STATUS_ACTIVE : iaCore::STATUS_APPROVAL;

                // correctly handle NULL value
                // TODO: remove in 4.1.4 version of the core script
                if (isset($data['expire_date']) && (empty($data['expire_date']) || '0000-00-00 00:00:00' == $data['expire_date'])) {
                    $data['expire_date'] = null;
                }

                // assign shop value
                if (!$error) {
                    if (!empty($_POST['shop'])) {
                        $iaShop = $iaCore->factoryModule('shop', IA_CURRENT_MODULE);

                        if ($shopData = $iaShop->getByTitle($_POST['shop'])) {
                            $data['shop_id'] = $shopData['id'];
                        } else {
                            if ($iaCore->get('shop_submission')) {
                                // add shop here
                                $newShop = [
                                    'member_id' => iaUsers::hasIdentity() ? iaUsers::getIdentity()->id : 0,
                                    'website' => iaUtil::checkPostParam('website')
                                ];

                                foreach ($iaCore->languages as $iso => $language) {
                                    $newShop['title_' . $iso] = $_POST['shop'];
                                }

                                $newShop['domain'] = $newShop['website'] ? str_ireplace('www.', '', parse_url($newShop['website'], PHP_URL_HOST)) : '';

                                if (empty($newShop['website']) || 'http://' == $newShop['website']) {
                                    $newShop['title_alias'] = $shopData['title_alias'] = iaSanitize::alias($newShop['title_' . $iaView->language] ? $newShop['title_' . $iaView->language] : $shopTitle);
                                    unset($newShop['website']);
                                } else {
                                    $newShop['title_alias'] = $shopData['title_alias'] = iaSanitize::alias($newShop['domain'] ? $newShop['domain'] : $shopTitle);
                                }

                                $data['shop_id'] = $iaShop->insert($newShop);

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

                if (!$iaCoupon->isSubmissionAllowed($item['member_id'])) {
                    $error = true;
                    $messages[] = iaLanguage::get('limit_is_exceeded');
                }

                if (!$error) {
                    if (iaCore::ACTION_ADD == $pageAction) {
                        // insert coupon
                        $data['id'] = $iaCoupon->insert($data);
                    } elseif (iaCore::ACTION_EDIT == $pageAction) {
                        $iaCoupon->update($data, $couponId);
                        $data['id'] = $couponId;
                    }

                    // implement common hook
                    $iaCore->startHook('phpAddItemAfterAll', [
                        'type' => 'front',
                        'listing' => $data['id'],
                        'item' => $iaCoupon->getItemName(),
                        'data' => $data,
                        'old' => $couponEntry
                    ]);

                    // redirect
                    $couponEntry['id'] = $data['id'];
                    $data['shop_alias'] = $shopData['title_alias'];

                    if (isset($_POST['plan_id']) && $_POST['plan_id']) {
                        $plan = $iaPlan->getById($_POST['plan_id']);
                        if ($plan['cost'] > 0) {
                            $url = $iaPlan->prePayment($iaCoupon->getItemName(), $couponEntry, $plan['id'], $iaCoupon->url('view', $data));

                            iaUtil::redirect(iaLanguage::get('redirect'), $messages, $url);
                        }
                    } else {
                        iaUtil::go_to($iaCoupon->url('view', $data));
                    }
                } else {
                    $iaView->setMessages($messages, iaView::ERROR);
                }

                $couponEntry = $data;
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
