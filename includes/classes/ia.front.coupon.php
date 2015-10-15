<?php
//##copyright##

class iaCoupon extends abstractCouponsPackageFront
{
	const SORTING_SESSION_KEY = 'coupons_sorting';

	protected static $_table = 'coupons_coupons';

	protected $_itemName = 'coupons';

	private $_foundRows = 0;

	protected $_statuses = array(iaCore::STATUS_ACTIVE, iaCore::STATUS_APPROVAL);

	private $_patterns = array(
		'default' => ':action/:id/',
		'view' => 'coupon/:shop_alias:title_alias/:id.html',
		'add' => 'add/',
		'buy' => 'coupons/buy/:id/'
	);


	/**
	 * Return title
	 * @param array $data
	 * @return string
	 */
	public function title(array $data)
	{
		$title = '';
		if (isset($data['title']))
		{
			$title = $data['title'];
		}
		return $title;
	}

	public function url($action, $data = array())
	{
		$data['action'] = $action;
		$data['shop_alias'] = (!isset($data['shop_alias']) ? '' : $data['shop_alias'] . IA_URL_DELIMITER);
		unset($data['title']);

		if (!isset($this->_patterns[$action]))
		{
			$action = 'default';
		}

		$url = iaDb::printf($this->_patterns[$action], $data);

		return $this->getInfo('url') . $url;
	}

	/**
	 * Return two url for account actions (edit, update)
	 * @param array $params
	 * @return array|bool
	 */
	public function accountActions(array $params)
	{
		if (iaUsers::hasIdentity() && iaUsers::getIdentity()->id == $params['item']['member_id'])
		{
			return array($this->url('edit', $params['item']), null);
		}

		return false;
	}

	public function foundRows()
	{
		return $this->_foundRows;
	}

	private function _getQuery($aWhere = '', $aOrder = '', $limit = 1, $start = 0, $foundRows = false, $ignoreStatus = false, $ignoreIndex = false)
	{
		$iaDb = &$this->iaDb;

		$sql = 'SELECT :found_rows :fields '
			. 'FROM `:coupons` t1 '
			. ($ignoreIndex ? 'IGNORE INDEX (`' . $ignoreIndex . '`) ' : '')
				. 'LEFT JOIN `:categs` t2 ON(t2.`id` = t1.`category_id` AND t2.`status` = \'active\')'
				. 'LEFT JOIN `:members` t3 ON(t3.`id` = t1.`member_id`)'
				. 'LEFT JOIN `:shops` t4 ON(t4.`id` = t1.`shop_id`) '
			. 'WHERE :where '
			. ($aOrder ? 'ORDER BY ' . $aOrder . ' ' : '')
			. 'LIMIT :start, :limit ';

		$where = array(
			//"(t3.`status` = 'active' OR t3.`status` IS NULL) AND `t4`.`status` = 'active' ",
			"(t3.`status` = 'active' OR t3.`status` IS NULL) ",
		);
		empty($aWhere) || $where[] = $aWhere;
		$ignoreStatus || $where[] = "(t1.`status` = 'active') ";

		$data = array(
			'found_rows' => ($foundRows === true ? 'SQL_CALC_FOUND_ROWS' : ''),
			'fields' => 't1.*'
					. ', t2.`title_alias` `category_alias`, t2.`title` `category_title`, t2.`parent_id` `category_parent_id`, t2.`no_follow`, t2.`num_coupons` `num` '
					. ', IF(t3.`fullname` != "", t3.`fullname`, t3.`username`) `account`, t3.`username` `account_username`'
					. ', t4.`title_alias` `shop_alias`, t4.`title` `shop_title`, t4.`shop_image` `shop_image`, t4.`website` `shop_website`, t4.`domain` `shop_domain`, t4.`affiliate_link` `shop_affiliate_link` ',
			'coupons' => self::getTable(true),
			'categs' => $iaDb->prefix . 'coupons_categories',
			'shops' => $iaDb->prefix . 'coupons_shops',
			'members' => iaUsers::getTable(true),
			'where' => implode(' AND ', $where),
			'start' => $start,
			'limit' => $limit,
		);

		$rows = $iaDb->getAll(iaDb::printf($sql, $data));

		if ($foundRows === true)
		{
			$this->_foundRows = $iaDb->foundRows();
		}
		elseif ($foundRows == 'count')
		{
			$data['fields'] = 'COUNT(*) `count`';
			$data['limit'] = 1;

			$this->_foundRows = $iaDb->getOne(iaDb::printf($sql, $data));
		}

		return $this->_processValues($rows);
	}

	public function getById($id)
	{
		$coupons = $this->_getQuery("t1.`id` = '{$id}'");

		return $coupons ? $coupons[0] : array();
	}

	/**
	 * Add some modification in listings
	 *
	 * @param array $rows
	 * @return array
	 */
	public function _processValues(array &$rows)
	{
		// Update favorites
		$iaItem = $this->iaCore->factory('item');
		$rows = $iaItem->updateItemsFavorites($rows, self::getItemName());

		// Filter fields
		$iaField = $this->iaCore->factory('field');
		$iaField->filter($rows, self::getTable());

		if ($rows)
		{
			foreach ($rows as &$row)
			{
				if (!is_array($row))
				{
					break;
				}
				if (empty($row['shop_image']))
				{
					continue;
				}
				$row['shop_image'] = (':' == $row['shop_image'][1]) ? unserialize($row['shop_image']) : $row['shop_image'];
			}
		}

		return $rows;
	}

	/**
	 * Get listings by custom condition
	 * @param string $where
	 * @param string $order
	 * @param int $limit
	 * @param int $start
	 * @param bool $foundRows
	 * @return array
	 */
	/*---*/public function getCoupons($where = '', $order = '', $limit = 5, $start = 0, $foundRows = false, $ignoreStatus = false)
	{
		return $this->_getQuery($where, $order, $limit, $start, $foundRows, $ignoreStatus);
	}/*---*/

	/**
	 * Get user`s listings
	 *
	 * @param int $memberId
	 * @param int $limit
	 * @param int $start
	 * @return array
	 */
	public function getByUser($memberId, $limit = 5, $start = 0)
	{
		return $this->_getQuery('t1.`member_id` = ' . (int)$memberId, 't1.`member_id` DESC', $limit, $start, true);
	}

	public function getFavorites($ids)
	{
		$stmt = iaDb::printf("`id` IN (:ids) AND `status` IN (':active', 'available')", array('ids' => implode(',', $ids), 'active' => iaCore::STATUS_ACTIVE));

		return $this->iaDb->all(iaDb::ALL_COLUMNS_SELECTION . ', (SELECT `title_alias` FROM `' . $this->iaDb->prefix . 'coupons_shops` `shops` WHERE `shops`.`id` = `shop_id`) `shop_alias`, 1 `favorite`', $stmt, null, null, self::getTable());
	}

	// called at the Member Details page
	public function fetchMemberListings($memberId, $start, $limit)
	{
		return array(
			'items' => $this->getByUser($memberId, $limit, $start),
			'total_number' => $this->foundRows()
		);
	}

	/**
	 * Get listings by Category ID
	 * @param string $aWhere
	 * @param int $catId
	 * @param int $aStart
	 * @param int $aLimit
	 * @param bool $aOrder
     * @return array
	 */
	public function getByCategory($aWhere, $catId, $aStart = 0, $aLimit = 10, $aOrder = false)
	{
		empty($aWhere) || $aWhere .= ' AND ';
		$aWhere .= is_array($catId)
			? 't1.`category_id` IN(' . implode(',', $catId) . ')'
			: 't1.`category_id` = ' . (int)$catId;

		return $this->_getQuery($aWhere, $aOrder, $aLimit, $aStart, true);
	}

	public function insert(array $entryData)
	{
		$entryData['date_added'] = date(iaDb::DATETIME_FORMAT);
		$entryData['date_modified'] = date(iaDb::DATETIME_FORMAT);
		$entryData['member_id'] = iaUsers::hasIdentity() ? iaUsers::getIdentity()->id : 0;

		return parent::insert($entryData);
	}
/*
	public function update(array $aData, $aOldData = array())
	{
		// If status changed
		if ('active' == $aData['status'] && $status != 'active' && $categ == $aData['category_id'])
		{
			$this->updateCategoryAmount($categ, -1);
		}
		elseif ('active' != $aData['status'] && 'active' == $status && $categ == $aData['category_id'])
		{
			$this->updateCategoryAmount($categ, 1);
		}

		// If category changed
		if ($categ != $aData['category_id'])
		{
			if ('active' == $aData['status'] && 'active' == $status)
			{
				$this->updateCategoryAmount($aData['category_id'], -1);
				$this->updateCategoryAmount($categ, 1);
			}
			elseif ('active' != $aData['status'] && 'active' == $status)
			{
				$this->updateCategoryAmount($categ, 1);
			}
			elseif ('active' == $aData['status'] && 'active' != $status)
			{
				$this->updateCategoryAmount($aData['category_id'], -1);
			}
		}

		return (bool)$this->iaDb->update($aData, "`id` = {$aData['id']}", 0, self::getTable());
	}
*/
	public function updateCounters()
	{
		$this->iaDb->update(array('num_coupons' => 0, 'num_all_coupons' => 0), '', null, 'coupons_categories');

		$sql =
			'UPDATE `:prefixcoupons_categories` c SET ' .
			'`num_all_coupons` = (' .
				'SELECT COUNT(*) FROM `:table_coupons` l ' .
				'LEFT JOIN `:prefixcoupons_categories_flat` fs ' .
				'ON fs.`category_id` = l.`category_id` ' .
				'WHERE fs.`parent_id` = c.`id` ' .
				"AND l.`status` = ':status'" .
			'),' .
			'`num_coupons` = (' .
				'SELECT COUNT(*) FROM `:table_coupons` ' .
				'WHERE `category_id` = c.`id` ' .
				"AND `status` = ':status'" .
			') ' .
			"WHERE c.`status` = ':status'";

		$sql = iaDb::printf($sql, array(
			'prefix' => $this->iaDb->prefix,
			'table_coupons' => self::getTable(true),
			'status' => iaCore::STATUS_ACTIVE
		));

		return $this->iaDb->query($sql);
	}

	public function incrementThumbsCounter($itemId, $trigger, $columnName = 'thumbs_num')
	{
		$viewsTable = 'thumbs_log';
		$sign = ('up' == $trigger) ? '+' : '-';

		$ipAddress = $this->iaCore->factory('util')->getIp(true);
		$date = date(iaDb::DATE_FORMAT);

		if ($this->iaDb->exists('`item_id` = :id AND `ip` = :ip AND `date` = :date', array('id' => $itemId, 'ip' => $ipAddress, 'date' => $date), $viewsTable))
		{
			return false;
		}

		$this->iaDb->insert(array('item_id' => $itemId, 'ip' => $ipAddress, 'date' => $date), null, $viewsTable);
		$result = $this->iaDb->update(null, iaDb::convertIds($itemId), array($columnName => '`' . $columnName . '` ' . $sign . ' 1'), self::getTable());

		return (bool)$result;
	}

	public function getThumbsNum($id)
	{
		return $this->iaDb->one('thumbs_num', iaDb::convertIds($id), self::getTable());
	}

	public function getSorting(&$storage, &$params)
	{
		$field = 'date_added';
		$direction = iaDb::ORDER_DESC;

		$validFields = array(
			'date' => 'date_added',
			'likes' => 'thumbs_num',
			'popularity' => 'views_num'
		);
		$validDirections = array(
			'up' => iaDb::ORDER_ASC,
			'down' => iaDb::ORDER_DESC
		);

		empty($storage[self::SORTING_SESSION_KEY][0]) || $field = $storage[self::SORTING_SESSION_KEY][0];
		empty($storage[self::SORTING_SESSION_KEY][1]) || $direction = $storage[self::SORTING_SESSION_KEY][1];

		if (isset($params['sort']) && in_array($params['sort'], array_keys($validFields)))
		{
			$field = $validFields[$params['sort']];

			isset($storage[self::SORTING_SESSION_KEY]) || $storage[self::SORTING_SESSION_KEY] = array();
			$storage[self::SORTING_SESSION_KEY][0] = $field;
		}
		if (isset($params['order']) && in_array($params['order'], array_keys($validDirections)))
		{
			$direction = $validDirections[$params['order']];

			isset($storage[self::SORTING_SESSION_KEY]) || $storage[self::SORTING_SESSION_KEY] = array();
			$storage[self::SORTING_SESSION_KEY][1] = $direction;
		}

		return array($field, $direction);
	}
}