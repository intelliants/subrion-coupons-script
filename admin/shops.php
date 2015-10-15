<?php
//##copyright##

class iaBackendController extends iaAbstractControllerPackageBackend
{
	protected $_name = 'shops';

	protected $_helperName = 'shop';

	protected $_gridColumns = '`id`, `title`, `title_alias`, (:inner_sql) `coupons_num`, `date_added`, `status`';
	protected $_gridFilters = array('status' => self::EQUAL, 'title' => self::LIKE);

	protected $_activityLog = array('icon' => 'cart', 'item' => 'shop');

	private $_fields;


	public function __construct()
	{
		parent::__construct();

		$this->_fields = $this->_iaField->getByItemName($this->getItemName());
	}

	protected function _gridRead($params)
	{
		return (isset($this->_iaCore->requestPath[0]) && 'alias' == $this->_iaCore->requestPath[0])
			? $this->_getAlias($_GET)
			: parent::_gridRead($params);
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

	protected function _unpackGridColumnsArray()
	{
		$this->_iaCore->factoryPackage('coupon', $this->getPackageName(), iaCore::ADMIN);

		$innerSql = 'SELECT COUNT(*) FROM `:prefix:table_coupons` c WHERE c.`shop_id` = `:prefix:table_shops`.`id`';
		$innerSql = iaDb::printf($innerSql, array(
			'prefix' => $this->_iaDb->prefix,
			'table_coupons' => iaCoupon::getTable(),
			'table_shops' => $this->getTable()
		));

		$columns = str_replace(':inner_sql', $innerSql, $this->_gridColumns);

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
		$entry = array(
			'member_id' => iaUsers::getIdentity()->id,
			'featured' => false,
			'status' => iaCore::STATUS_ACTIVE
		);
	}

	protected function _assignValues(&$iaView, array &$entryData)
	{
		parent::_assignValues($iaView, $entryData);

		$websiteFieldExists = false;

		foreach ($this->_fields as $field)
		{
			if ('website' == $field['name'])
			{
				$websiteFieldExists = true;
				break;
			}
		}

		$iaView->assign('statuses', $this->getHelper()->getStatuses());
		$iaView->assign('website_field_exists', $websiteFieldExists);
	}

	protected function _preSaveEntry(array &$entry, array $data, $action)
	{
		list($entry, , $this->_messages, ) = $this->_iaField->parsePost($this->_fields, $entry);

		if (!empty($data['title_alias']))
		{
			$entry['title_alias'] = $data['title_alias'];
		}
		elseif (!empty($data['website']) && 'http://' != $data['website'])
		{
			$entry['title_alias'] = $data['website'];
			$entry['title_alias'] = $entry['title_alias'] ? str_ireplace('www.', '', parse_url($entry['title_alias'], PHP_URL_HOST)) : '';
		}
		else
		{
			$entry['title_alias'] = $entry['title'];
		}

		$entry['title_alias'] = iaSanitize::alias($entry['title_alias']);

		return !$this->getMessages();
	}

	protected function _getAlias(array $params)
	{
		$result = array('alias' => '');

		$title = isset($params['title']) ? $params['title'] : '';
		if (!isset($params['alias']))
		{
			if ('website' == $params['type'] && $title)
			{
				$title = str_ireplace('www.', '', parse_url($title, PHP_URL_HOST));
			}

			$title = iaSanitize::alias($title);
		}

		$data = array(
			'id' => (isset($params['id']) && (int)$params['id'] > 0 ? (int)$params['id'] : '{id}'),
			'title_alias' => $title
		);

		if (!isset($params['alias'])
			&& $this->_iaDb->exists('`title_alias` = :alias AND `id` != :id', array('alias' => $data['title_alias'], 'id' => (int)$params['id']), $this->getTable()))
		{
			$result['exists'] = iaLanguage::get('coupon_shop_already_exists');
		}

		$result['data'] = $this->getHelper()->url('view', $data);

		return $result;
	}
}