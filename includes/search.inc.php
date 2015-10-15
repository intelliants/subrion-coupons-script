<?php
//##copyright##

function coupons_search($aQuery, $aFields, $aStart, $aLimit, &$aNumAll, $aWhere = '')
{
	$iaCore = &iaCore::instance();

	$ret = array();
	$match = '';

	// additional fields
	if ($aFields && is_array($aFields))
	{
		foreach ($aFields as $data)
		{
/*			if ('LIKE' == $data['cond'])
			{
				$data['val'] = "%{$data['val']}%";
			}

			// for multiple values, like combo or checkboxes
			if (is_array($data['val']))
			{
				if ('!=' == $data['cond'])
				{
					$data['cond'] = count($data['val']) > 1 ? 'NOT IN' : '!=';
				}
				else
				{
					$data['cond'] = count($data['val']) > 1 ? 'IN' : '=';
				}
				$data['val'] = count($data['val']) > 1 ? '(' . implode(',', $data['val']) . ')' : array_shift($data['val']);
			}
			elseif (preg_match('/^(\d+)\s*-\s*(\d+)$/', $data['val'], $range))
			{
				// search in range
				$data['cond'] = sprintf('BETWEEN %d AND %d', $range[1], $range[2]);
				$data['val'] = '';
			}
			else
			{
				$data['val'] = "'" . iaSanitize::sql($data['val']) . "'";
			}
*/

			$data['column'] = str_replace(':column', 't1.`' . $data['field'] . '`', $data['column']);

			$match .= " {$data['column']} {$data['cond']} {$data['val']} ";
		}
	}

	$iaCoupon = $iaCore->factoryPackage('coupon', 'coupons');

	$coupons = $match ? $iaCoupon->getCoupons(iaDb::EMPTY_CONDITION . $match, $aStart, $aLimit) : array();
	$aNumAll += $iaCore->iaDb->foundRows();

	$resourceName = 'coupon';
	$resourceName = is_file(IA_FRONT_TEMPLATES . $iaCore->get('tmpl') . IA_DS . 'packages' . IA_DS . 'coupons' . IA_DS . $resourceName. '-list-simple.tpl')
		? IA_FRONT_TEMPLATES . $iaCore->get('tmpl') . IA_DS . 'packages' . IA_DS . 'coupons' . IA_DS . $resourceName
		: IA_PACKAGES . 'coupons/templates/common/' . $resourceName;

	$iaSmarty = &$iaCore->iaView->iaSmarty;

	$iaSmarty->assign('config', $iaCore->getConfig());
	$iaSmarty->assign('member', iaUsers::getIdentity(true));
	$iaSmarty->assign('packages', $iaCore->packagesData);

	foreach ($coupons as $coupon)
	{
		$iaSmarty->assign('coupon', $coupon);
		$ret[] = $iaSmarty->fetch($resourceName . '-list-' . $coupon['coupon_type'] . '.tpl');
	}

	return $ret;
}

function shops_search($aQuery, $aFields, $aStart, $aLimit, &$aNumAll, $aWhere = '')
{
	$iaCore = &iaCore::instance();
	$ret = array();
	$match = '';

	// additional fields
	if ($aFields && is_array($aFields))
	{
		$i = 0;
		foreach ($aFields as $fname => $data)
		{
			if ('LIKE' == $data['cond'])
			{
				$data['val'] = "%{$data['val']}%";
			}

			// for multiple values, like combo or checkboxes
			if (is_array($data['val']))
			{
				if ('!=' == $data['cond'])
				{
					$data['cond'] = count($data['val']) > 1 ? 'NOT IN' : '!=';
				}
				else
				{
					$data['cond'] = count($data['val']) > 1 ? 'IN' : '=';
				}
				$data['val'] = count($data['val']) > 1 ? '(' . implode(',', $data['val']) . ')' : array_shift($data['val']);
			}
			elseif (preg_match('/^(\d+)\s*-\s*(\d+)$/', $data['val'], $range))
			{
				// search in range
				$data['cond'] = sprintf('BETWEEN %d AND %d', $range[1], $range[2]);
				$data['val'] = '';
			}
			else
			{
				$data['val'] = "'" . iaSanitize::sql($data['val']) . "'";
			}

			$match .= $i == 0 ? " AND (" : "OR ";
			$i++;
			$match .= " t1.`{$fname}` {$data['cond']} {$data['val']} ";
		}
		$match .= ')';
	}

	$iaSmarty = &$iaCore->iaView->iaSmarty;
	$iaShop = $iaCore->factoryPackage('shop', 'coupons');

	$shops = $match ? $iaShop->getShops(iaDb::EMPTY_CONDITION . $match, $aStart, $aLimit) : array();
	$aNumAll += $iaCore->iaDb->foundRows();

	$iaSmarty->assign('config', $iaCore->getConfig());

	foreach ($shops as $shop)
	{
		$iaSmarty->assign('shop', $shop);
		$ret[] = $iaSmarty->fetch(IA_PACKAGES . 'coupons/templates/common/shop.tpl');
	}

	return $ret;
}