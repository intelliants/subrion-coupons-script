<?php
//##copyright##

class iaBackendController extends iaAbstractControllerPackageBackend
{
	protected $_name = 'coupons_codes';

	protected $_gridColumns = array('title', 'contents', 'position', 'extras', 'type', 'status', 'order', 'multilingual', 'delete' => 'removable');
	protected $_gridFilters = array('status' => self::EQUAL, 'title' => self::LIKE, 'type' => self::EQUAL, 'position' => self::EQUAL, 'extras' => self::EQUAL);


	protected function _entryUpdate(array $entryData, $entryId)
	{
		$entryData['date_modified'] = date(iaDb::DATETIME_FORMAT);

		return parent::_entryUpdate($entryData, $entryId);
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
}