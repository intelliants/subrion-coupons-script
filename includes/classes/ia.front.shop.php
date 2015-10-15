<?php
//##copyright##

class iaShop extends abstractCouponsPackageFront
{
	protected static $_table = 'coupons_shops';

	protected $_itemName = 'shops';

	private $_foundRows = 0;

	protected $_statuses = array(iaCore::STATUS_ACTIVE, iaCore::STATUS_APPROVAL);

	private $_patterns = array(
		'default' => ':action/:id/',
		'view' => 'shop/:title_alias.html',
		'edit' => 'edit/?id=:id',
		'add' => 'add/'
	);

	public function insert(array $entryData)
	{
		$entryData['date_added'] = date(iaDb::DATETIME_FORMAT);
		$entryData['date_modified'] = date(iaDb::DATETIME_FORMAT);
		$entryData['member_id'] = iaUsers::getIdentity()->id;

		return parent::insert($entryData);
	}

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

	/**
	 * Method return url for some pages
	 * @param string $action
	 * @param array $data
	 * @return string
	 */
	public function url($action, $data = array())
	{
		$data['action'] = $action;
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

	private function _getQuery($aWhere = '', $aOrder = '', $limit = 1, $start = 0, $foundRows = false, $ignoreIndex = false)
	{
		$iaDb = &$this->iaDb;

		$sql = 'SELECT :fields '
			. 'FROM :shops as `t1` '
			. ($ignoreIndex ? 'IGNORE INDEX (`' . $ignoreIndex . '`) ' : '')
			. 'LEFT JOIN `:members` t2 ON (t2.`id` = t1.`member_id`) '
			. 'WHERE :where '
			. ($aOrder ? 'ORDER BY ' . $aOrder . ' ' : '')
			. 'LIMIT :start, :limit ';

		$where = array("(t2.`status` = 'active' OR t2.`status` IS NULL)");
		empty($aWhere) || $where[] = $aWhere;
		$where[] = "t1.`status` = 'active' ";

		$data = array(
			'found_rows' => ($foundRows === true ? 'SQL_CALC_FOUND_ROWS' : ''),
			'fields' => 't1.*, t1.`id`, t1.`title`, t1.`title_alias`, t1.`date_added` '
					. ', t2.`fullname` `account`, t2.`username` `account_username` '
					. ', (SELECT COUNT(*) FROM :coupons `coupons` WHERE `coupons`.`shop_id` = t1.`id`) `num_coupons` ',
			'shops' => self::getTable(true),
			'members' => iaUsers::getTable(true),
			'coupons' => $iaDb->prefix . 'coupons_coupons',
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

	/**
	 * Add some modification in listings
	 * @param array $rows
	 * @return array
	 */
	public function _processValues(array $rows)
	{
		// Update favorites
		$iaItem = $this->iaCore->factory('item');
		$rows = $iaItem->updateItemsFavorites($rows, self::getTable());

		// Filter fields
		$iaField = $this->iaCore->factory('field');
		$iaField->filter($rows, self::getTable());

		if ($rows)
		{
			foreach ($rows as &$row)
			{
				if (isset($row['shop_image']) && $row['shop_image'])
				{
					$row['shop_image'] = unserialize($row['shop_image']);
				}
			}
		}

		return $rows;
	}

	public function getById($id)
	{
		$id = intval($id);
		$listing = $this->_getQuery("t1.`id` = '$id'");
		return ($listing ? $listing[0] : false);
	}

	public function getByAlias($alias)
	{
		$listing = $this->_getQuery("t1.`title_alias` = '{$alias}'");

		return ($listing ? $listing[0] : false);
	}

	/**
	 * Get latest listings with limit
	 * @param string $where
	 * @param int $limit
	 * @param int $start
	 * @return array
	 */
	public function getLatest($where = '', $limit = 5, $start = 0)
	{
		return $this->_getQuery($where . ' AND yp.date_added > 0', 'yp.`date_added` DESC', $limit, $start, false, 'loc_category');
	}

	/**
	 * Get popular listings with limit
	 * @param string $where
	 * @param int $limit
	 * @param int $start
	 * @return array
	 */
	public function getPopular($where = '1', $limit = 5, $start = 0)
	{
		return $this->_getQuery($where, 't1.`views_num` DESC', $limit, $start, false);
	}

	public function getShops($where = '', $order = '`title` ASC ', $limit = 5, $start = 0, $found_rows = false)
	{
		return $this->_getQuery($where, $order, $limit, $start, $found_rows);
	}

	public function getFeatured($limit)
	{
		return $this->_getQuery('t1.`featured` = 1 AND t1.`featured_end` > NOW()', '`title` ASC', $limit);
	}

	/**
	 * Get listings by search word
	 * @param string $search - word for search
	 * @param string $where
	 * @param int $limit
	 * @param int $start
	 * @return array
	 */
	public function searchQuery($search, $where, $limit = 0, $start = 0)
	{
		$search = iaSanitize::sql($search);
		$fields = array('description', 'title');

		foreach ($fields as &$field)
		{
			$field = "yp.`{$field}` LIKE '%{$search}%'";
		}

		$where || $where = iaDb::EMPTY_CONDITION;

		$rows = $this->getCompanies($where . ' AND (' . implode(' OR ', $fields) . ') ', $limit, $start);

		return $rows;
	}
}