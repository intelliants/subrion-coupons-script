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

$iaCoupon = $iaCore->factoryModule('coupon', IA_CURRENT_MODULE);
$iaCateg = $iaCore->factoryModule('ccat', IA_CURRENT_MODULE);

if (iaView::REQUEST_JSON == $iaView->getRequestType() && isset($_POST['action'])) {
    switch ($_POST['action']) {
        case 'report':
            $id = (int)$_POST['id'];
            $comment = '';

            if ((isset($_POST['comments']) && $_POST['comments'])) {
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
            $iaMailer->loadTemplate('reported_as_problem');
            $iaMailer->setReplacements([
                'title' => $listing['title'],
                'comments' => $comment,
            ]);
            $iaMailer->sendToAdministrators();

            $email = (isset($listing['email']) && $listing['email']) ? $listing['email'] : $iaDb->one('email', iaDb::convertIds($listing['member_id']), iaUsers::getTable());

            if ($email) {
                $iaMailer->loadTemplate('reported_as_problem');
                $iaMailer->setReplacements([
                    'title' => $listing['title'],
                    'comments' => $comment
                ]);
                $iaMailer->addAddress($email);

                $iaMailer->send();
            }
            $fields = ['reported_as_problem' => 1];
            if ($comment) {
                if (isset($listing['reported_as_problem_comments']) && $listing['reported_as_problem_comments']) {
                    $comment = $listing['reported_as_problem_comments'] . $comment;
                }
                $fields['reported_as_problem_comments'] = $comment;
            }
            $iaDb->update($fields, iaDb::convertIds($id), null, iaCoupon::getTable());

            break;

        case 'status':
            $output = ['result' => false, 'message' => iaLanguage::get('invalid_parameters')];

            if (iaUsers::hasIdentity() && isset($_POST['id'])) {
                $id = (int)$_POST['id'];

                $couponCode = $iaCoupon->getCode($id);
                $coupon = $iaCoupon->getById($couponCode['coupon_id']);

                if ($coupon && $coupon['member_id'] == iaUsers::getIdentity()->id) {
                    $iaDb->update(['status' => $_POST['status']], iaDb::convertIds($id), null, iaCoupon::getTableCodes());

                    if (0 === $iaDb->getErrorNumber()) {
                        $output['result'] = true;
                        $output['message'] = iaLanguage::get('saved');
                    } else {
                        $output['message'] = iaLanguage::get('db_error');
                    }
                }
            }

            $iaView->assign($output);
    }
}

if (iaView::REQUEST_HTML == $iaView->getRequestType()) {
    if (!isset($iaCore->requestPath[2]) || empty($iaCore->requestPath[2])) {
        return iaView::errorPage(iaView::ERROR_NOT_FOUND);
    }

    // get coupon info
    $couponId = (int)$iaCore->requestPath[2];
    $coupon = $iaCoupon->get("t1.`id` = '{$couponId}'", '', 1, 0, false, true);

    if (empty($coupon)) {
        return iaView::errorPage(iaView::ERROR_NOT_FOUND);
    }

    $coupon = array_shift($coupon);
    $coupon['item'] = $iaCoupon->getItemName();

    if ($coupon['status'] != iaCore::STATUS_ACTIVE
        && $coupon['member_id'] != iaUsers::getIdentity()->id) {
        return iaView::errorPage(iaView::ERROR_NOT_FOUND);
    }

    $iaView->assign('item', $coupon);

    // increment views counter
    $iaCoupon->incrementViewsCounter($coupon['id']);

    // get shop info
    $iaItem = $iaCore->factory('item');
    $iaShop = $iaCore->factoryModule('shop', IA_CURRENT_MODULE);

    $shop = $coupon['shop_id'] ? $iaShop->getById($coupon['shop_id']) : [];
    $iaView->assign('shop', $shop);

    // get coupon category
    $couponCategory = $iaCateg->getById($coupon['category_id']);
    $iaView->assign('coupon_category', $couponCategory);

    // get account information
    if ($coupon['member_id']) {
        $account = $iaCore->factory('users')->getInfo($coupon['member_id']);
        $iaView->assign('coupon_account', $account);
        if ($account) {
            if (iaUsers::hasIdentity() && $coupon['member_id'] == iaUsers::getIdentity()->id) {
                $actionUrls = [
                    iaCore::ACTION_EDIT => $iaCoupon->url(iaCore::ACTION_EDIT, $coupon),
                    iaCore::ACTION_DELETE => $iaCoupon->url(iaCore::ACTION_DELETE, $coupon)
                ];
                $iaView->assign('tools', $actionUrls);

                $iaItem->setItemTools([
                    'id' => 'action-edit',
                    'title' => iaLanguage::get('edit_coupon'),
                    'attributes' => [
                        'href' => $actionUrls[iaCore::ACTION_EDIT]
                    ]
                ]);
                $iaItem->setItemTools([
                    'id' => 'action-delete',
                    'title' => iaLanguage::get('delete_coupon'),
                    'attributes' => [
                        'href' => $actionUrls[iaCore::ACTION_DELETE],
                        'class' => 'js-delete-coupon'
                    ]
                ]);
//                // Reserved for future use
//                $iaItem->setItemTools([
//                    'id' => 'bar-chart',
//                    'title' => iaLanguage::get('coupon_statistics'),
//                    'attributes' => [
//                        'href' => '#',
//                        'id' => 'js-cmd-statistics-coupon',
//                        'data-id' => $coupon['id']
//                    ]
//                ]);
            }
        }
    }

    $iaItem->setItemTools([
        'id' => 'action-report',
        'title' => iaLanguage::get('report_coupon'),
        'attributes' => [
            'href' => '#',
            'id' => 'js-cmd-report-coupon',
            'data-id' => $coupon['id']
        ]
    ]);

    $iaCore->startHook('phpViewListingBeforeStart', [
        'listing' => $coupon['id'],
        'item' => $iaCoupon->getItemName(),
        'title' => $coupon['title'],
        'desc' => $coupon['short_description']
    ]);

    // breadcrumb formation
    iaBreadcrumb::add(iaLanguage::get('shops'), IA_MODULE_URL . 'shops/');
    iaBreadcrumb::add($shop['title'], $iaShop->url('view', $shop));

    // get purchased codes
    if ('deal' == $coupon['type'] && iaUsers::hasIdentity() && $coupon['member_id'] == iaUsers::getIdentity()->id) {
        $iaView->assign('codes', $iaCoupon->getCodes($couponId));
        $iaView->assign('codeStatuses', $iaCoupon->getCodeStatuses());
    }

    // set coupon meta values
    $iaView->set('title', $coupon['title']);
    $iaView->set('description', $coupon['meta_description']);
    $iaView->set('keywords', $coupon['meta_keywords']);

    if (isset($_GET['print']) && '' == $_GET['print']) {
        $iaView->disableLayout();
        $iaView->display('coupon-view-printing');
    } else {
        $iaView->display('coupon-view');
    }
}
