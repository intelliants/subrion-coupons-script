<?php
//##copyright##

function smarty_function_coupon_code($params)
{
	$html = '';

	is_array($params) || $params = array();

	if (empty($params['coupon']))
	{
		return '';
	}

	$coupon = $params['coupon'];
	$iaCore = iaCore::instance();

	if (iaUsers::hasIdentity())
	{
		if ($coupon['member_id'] == iaUsers::getIdentity()->id)
		{
			$html = '<span>You own this coupon.</span>';
		}

		$iaCoupon = $iaCore->factoryPackage('coupon', 'coupons');

		$iaCore->factory('transaction');

		$transaction = $iaCore->iaDb->row_bind(iaDb::ALL_COLUMNS_SELECTION, 'member_id = :member && `item` = :item && `item_id` = :id AND `amount` >= :price',
			array('member' => iaUsers::getIdentity()->id, 'item' => 'coupons', 'id' => $coupon['id'], 'price' => $coupon['cost']), iaTransaction::getTable());
		if (isset($transaction['status']) && iaTransaction::PASSED == $transaction['status'])
		{
			$html = '<span>You bought this coupon.</span>';
		}
	}

	$url = isset($transaction) && $transaction
		? IA_URL . 'pay/' . $transaction['sec_key']
		: $iaCore->packagesData['coupons']['url'] . 'coupons/buy/' . $coupon['id'] . IA_URL_DELIMITER;

	return $html . '<a class="btn btn-plain btn-info" href="' . $url . '">' .
		iaLanguage::getf('purchase_coupon_code', array('cost' => $coupon['cost'], 'currency' => $iaCore->get('currency'))) . '</a>';
}

$iaSmarty->registerPlugin(iaSmarty::PLUGIN_FUNCTION, 'coupon_code', 'smarty_function_coupon_code');