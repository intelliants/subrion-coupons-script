<?php
//##copyright##

class iaBackendController extends iaAbstractControllerPackageBackend
{
	protected $_name = 'coupons';

	protected $_helperName = 'coupon';

	protected $_gridColumns = '`id`, `title`, `title_alias`, `date_added`, (:sql_category) `category`, (:sql_member) `member`, `status`';
	protected $_gridFilters = array('status' => self::EQUAL, 'title' => self::LIKE);

	protected $_phraseAddSuccess = 'coupon_added';

	protected $_activityLog = array('icon' => 'tag', 'item' => 'coupon');


	protected function _gridRead($params)
	{
		$action = empty($this->_iaCore->requestPath[0]) ? null : $this->_iaCore->requestPath[0];

		switch ($action)
		{
			default: return parent::_gridRead($params);
			case 'alias': return $this->_getJsonAlias($_GET);
			case 'shops': return $this->_getJsonShops($_GET);
		}
	}

	protected function _entryAdd(array $entryData)
	{
		$entryData['date_added'] = date(iaDb::DATETIME_FORMAT);
		$entryData['date_modified'] = date(iaDb::DATETIME_FORMAT);

		return parent::_entryAdd($entryData);
	}

	protected function _entryUpdate(array $entryData, $entryId)
	{
		$entryData['date_modified'] = date(iaDb::DATETIME_FORMAT);

		return parent::_entryUpdate($entryData, $entryId);
	}

	public function updateCounters($entryId, array $entryData, $action, $previousData = null)
	{
		if (in_array($action, iaCore::ACTION_ADD, iaCore::ACTION_EDIT))
		{
			$this->_iaCore->startHook('phpAddItemAfterAll', array(
				'type' => iaCore::ADMIN,
				'listing' => $entryId,
				'item' => $this->getItemName(),
				'data' => $entryData,
				'old' => $previousData
			));
		}
	}

	protected function _unpackGridColumnsArray()
	{
		$prefix = $this->_iaDb->prefix;

		$sqlCategory = 'SELECT `title` FROM `' . $prefix . 'coupons_categories` c WHERE c.`id` = `category_id`';
		$sqlMember = 'SELECT `username` FROM `' . $prefix . iaUsers::getTable() . '` m WHERE m.`id` = `member_id`';

		$columns = str_replace(array(':sql_category', ':sql_member'), array($sqlCategory, $sqlMember), $this->_gridColumns);

		return iaDb::STMT_CALC_FOUND_ROWS . ' ' . $columns . ', 1 `update`, 1 `delete`';
	}

	protected function _modifyGridParams(&$conditions, &$values, array $params)
	{
		if (!empty($params['member']))
		{
			$memberId = $this->_iaDb->one_bind(iaDb::ID_COLUMN_SELECTION,
				'`username` LIKE :member OR `fullname` LIKE :member',
				array('member' => $params['member']), iaUsers::getTable());

			$memberId = $memberId ? (int)$memberId : -1; // -1 or other invalid value

			$conditions[] = '`member_id` = ' . (int)$memberId;
		}
	}


	protected function _setDefaultValues(array &$entry)
	{
		$iaCcat = $this->_iaCore->factoryPackage('ccat', $this->getPackageName(), iaCore::ADMIN);

		$rootCategoryId = $iaCcat->getRootId();

		$entry = array(
			'shop_id' => 0,
			'member_id' => iaUsers::getIdentity()->id,
			'category_id' => $rootCategoryId,
			'sponsored' => false,
			'featured' => false,
			'status' => iaCore::STATUS_ACTIVE,
			'expire_date' => date(iaDb::DATE_FORMAT, strtotime('+1 week'))
		);
	}

	protected function _preSaveEntry(array &$entry, array $data, $action)
	{
		$fields = $this->_iaField->getByItemName($this->getHelper()->getItemName());
		list($entry, , $this->_messages, ) = $this->_iaField->parsePost($fields, $entry);

		$entry['category_id'] = (int)$data['category_id'];

		$entry['title_alias'] = empty($data['title_alias']) ? $data['title'] : $data['title_alias'];
		$entry['title_alias'] = iaSanitize::alias($entry['title_alias']);

		// validate chosen shop
		if (!empty($data['shop']))
		{
			if ($shopData = $this->_iaDb->row_bind(iaDb::ID_COLUMN_SELECTION, '`title` = :name', array('name' => $data['shop']), 'coupons_shops'))
			{
				$entry['shop_id'] = $shopData['id'];
			}
			else
			{
				$this->addMessage('coupon_shop_incorrect');
			}
		}
		else
		{
			$this->addMessage('coupon_shop_empty');
		}

		return !$this->getMessages();
	}

	protected function _assignValues(&$iaView, array &$entryData)
	{
		parent::_assignValues($iaView, $entryData);

		$entryData['shop'] = $this->_iaDb->one('title', iaDb::convertIds($entryData['shop_id']), 'coupons_shops');

		$category = $this->_iaDb->row(array('id', 'title', 'parent_id', 'parents'), iaDb::convertIds($entryData['category_id']), 'coupons_categories');

		$iaView->assign('category', $category);
		$iaView->assign('statuses', $this->getHelper()->getStatuses());
	}

	protected function _getJsonAlias(array $params)
	{
		$title = isset($params['title']) ? $params['title'] : '';
		$title = iaSanitize::alias($title);

		$shop = isset($params['shop']) ? $params['shop'] : null;
		$shopAlias = $this->_iaDb->one_bind('title_alias', '`title` = :title', array('title' => $shop), 'coupons_shops');
		if (empty($shopAlias))
		{
			$shopAlias = iaLanguage::get('shop_incorrect');
		}

		$data = array(
			'id' => (isset($params['id']) && (int)$params['id'] ? (int)$params['id'] : $this->_iaDb->getNextId(iaCoupon::getTable(true))),
			'shop_alias' => $shopAlias,
			'title_alias' => $title
		);

		$url = $this->getHelper()->url('view', $data);

		return array('data' => $url);
	}

	protected function _getJsonShops(array $params)
	{
		$result = array();

		if (isset($params['q']))
		{
			$stmt = "`title` LIKE '" . iaSanitize::sql($params['q']) . "%' ORDER BY `title` ASC";

			$options = $this->_iaDb->onefield('title', $stmt, 0, 15, 'coupons_shops');
			$result['options'] = $options;
		}

		return $result;
	}
}