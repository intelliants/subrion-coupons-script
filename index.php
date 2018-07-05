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

$iaCoupon = $iaCore->factoryItem('coupon');

// process ajax actions
if (iaView::REQUEST_JSON == $iaView->getRequestType()) {
    if (!in_array($_GET['trigger'], ['up', 'down'])) {
        return iaView::errorPage(iaView::ERROR_INTERNAL);
    }

    if ($iaCoupon->incrementThumbsCounter((int)$_GET['id'], $_GET['trigger'])) {
        $output = [
            'message' => iaLanguage::get('thumbs_vote_accepted'),
            'error' => false,
            'rating' => (int)$iaCoupon->getThumbsNum((int)$_GET['id'])
        ];
    } else {
        $output = ['message' => iaLanguage::get('thumbs_already_voted'), 'error' => true];
    }

    $iaView->assign($output);
}

if (iaView::REQUEST_HTML == $iaView->getRequestType()) {
    $iaCcat = $iaCore->factoryItem('ccat');

    iaLanguage::set('no_my_coupons', str_replace('{%URL%}', IA_MODULE_URL . 'coupons/add/', iaLanguage::get('no_my_coupons')));

    $pagination = [
        'total' => 0,
        'limit' => $iaCore->get('coupons_per_page', 2),
        'start' => 0,
        'url' => IA_SELF . '?page={page}'
    ];

    $page = isset($_GET['page']) && is_numeric($_GET['page']) ? max((int)$_GET['page'], 1) : 1;
    $pagination['start'] = ($page - 1) * $pagination['limit'];

    $template = iaView::DEFAULT_ACTION;

    $stmt = $iaCore->get('show_expired_coupons') ? '' : 't1.`expire_date` >= NOW()';

    switch ($iaView->name()) {
        case 'new_coupons':
            $coupons = $iaCoupon->get($stmt, 't1.`date_added` DESC', $pagination['limit'], $pagination['start'], true);
            $iaView->assign('coupons', $coupons);

            $pagination['total'] = $iaCoupon->foundRows();

            break;

        case 'popular_coupons':
            // get popular coupons
            $coupons = $iaCoupon->get($stmt, 't1.`views_num` DESC', $pagination['limit'], $pagination['start'], true);
            $iaView->assign('coupons', $coupons);

            $pagination['total'] = $iaCoupon->foundRows();

            break;

        case 'printable_coupons':
        case 'deals':
        case 'codes':
            $typesMap = ['printable_coupons' => 'printable', 'deals' => 'deal', 'codes' => 'simple'];
            $where = ($stmt ? $stmt . ' AND ' : '') . iaDb::convertIds($typesMap[$iaView->name()], 'type');
            // get coupons
            $coupons = $iaCoupon->get($where, 't1.`date_added` DESC', $pagination['limit'], $pagination['start'], true);
            $iaView->assign('coupons', $coupons);

            $pagination['total'] = $iaCoupon->foundRows();

            break;

        case 'my_coupons':
            if (!iaUsers::hasIdentity()) {
                return iaView::accessDenied();
            }

            // get member coupons
            $coupons = $iaCoupon->get("t1.`member_id` = '" . iaUsers::getIdentity()->id . "'", 't1.`date_added` DESC', 20, 0, false, true);

            $iaView->assign('coupons', $coupons);
            $pagination['total'] = $iaCoupon->foundRows();

            break;

        case 'purchased_coupons':
            if (!iaUsers::hasIdentity()) {
                return iaView::accessDenied();
            }

            $template = 'list-coupons-purchased';
            /**
             * @var iaCoupon $iaCoupon
             */
            $where = iaDb::printf("`member_id` = :member_id AND `type` LIKE 'deal'", [
                'member_id' => iaUsers::getIdentity()->id,
            ]);
            $purchasedCouponsIds = $iaDb->onefield('id', $where, 0, null, iaCoupon::getTable());
            $codes = $iaCoupon->getCodes($purchasedCouponsIds);

            $iaView->assign('codes', $codes);
            $pagination['total'] = $iaCoupon->foundRows();

            break;

        case 'coupons_home':
            // get category by alias
            $categoryAlias = '';
            if ($iaCore->requestPath) {
                $categoryAlias = implode(IA_URL_DELIMITER, $iaCore->requestPath);
            }

            $category = $iaCcat->getCategory(iaDb::convertIds($categoryAlias, 'title_alias'));
            $iaView->assign('category', $category);

            // requested category not found
            if (!$category) {
                return iaView::errorPage(iaView::ERROR_NOT_FOUND);
            }
            $iaView->assign('current_category', $category);

            // increment views counter
            $iaCcat->incrementViewsCounter($category['id']);

            // get categories
            $categories = $iaCcat->getCategories("`parent_id` = '" . $category['id'] . "' AND `status` = 'active' ");
            $iaView->assign('categories', $categories);

            // get neighbour categories & update page title
            $neighbours = $iaCcat->getCategories("`parent_id` = '" . $category[iaCcat::COL_PARENT_ID] . "'AND `id` != '" . $category['id'] . "' AND `status` = 'active' ");
            $iaView->assign('neighbours', $neighbours);

            if ($category['id'] != $iaCcat->getRootId()) {
                $iaView->title($category['title'] . ' ' . iaLanguage::get('coupons'));

                // set shop meta values
                $iaView->set('description', $category['meta_description']);
                $iaView->set('keywords', $category['meta_keywords']);

                // generate breadcrumb
                $parents = $iaCcat->getParents($category['id']);
                foreach ($parents as $i => $p) {
                    isset($parents[++$i])
                        ? iaBreadcrumb::add($p['title'], $iaCcat->url('view', $p))
                        : iaBreadcrumb::replaceEnd($p['title'], $iaCcat->url('view', $p));
                }
            }

            $sorting = $iaCoupon->getSorting($_SESSION, $_GET);
            $sortingStmt = 't1.`' . $sorting[0] . '` ' . $sorting[1];

            // get coupons
            $coupons = $iaCoupon->getByCategory($stmt, $category['id'], $pagination['start'], $pagination['limit'], $sortingStmt);
            $iaView->assign('coupons', $coupons);

            $pagination['total'] = $iaCoupon->foundRows();

            $iaView->assign('sorting', $sorting);

            break;

        case 'coupon_buy':
            if (empty($iaCore->requestPath[0]) || count($iaCore->requestPath) != 1) {
                return iaView::errorPage(iaView::ERROR_NOT_FOUND);
            }

            $coupon = $iaCoupon->getById((int)$iaCore->requestPath[0]);

            if (empty($coupon) || iaCore::STATUS_ACTIVE != $coupon['status']) {
                return iaView::errorPage(iaView::ERROR_NOT_FOUND);
            }

            $title = iaDb::printf('Coupon ":title"', $coupon);
            $coupon['member_id'] = iaUsers::getIdentity()->id;

            $iaCore->factory('transaction')->create($title, $coupon['cost'], $iaCoupon->getItemName(), $coupon, $iaCoupon->url('view', $coupon));

            break;

        default:
            return iaView::errorPage(iaView::ERROR_NOT_FOUND);
    }

    if ($iaAcl->isAccessible('coupon_add', iaCore::ACTION_ADD)) {
        $iaPage = $iaCore->factory('page', iaCore::FRONT);

        $pageActions[] = [
            'icon' => 'plus-square',
            'title' => iaLanguage::get('page_title_coupon_add'),
            'url' => $iaPage->getUrlByName('coupon_add'),
            'classes' => 'btn-success'
        ];
        $iaView->set('actions', $pageActions);
    }

    $iaView->assign('pagination', $pagination);

    $iaView->display($template);
}
