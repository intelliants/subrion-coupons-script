<?php
//##copyright##

class iaBackendController extends iaAbstractControllerPackageBackend
{
	protected $_name = 'codes';

	protected $_gridColumns = array('code', 'status', 'order', 'multilingual', 'delete' => 'removable');
	protected $_gridFilters = array('status' => self::EQUAL, 'title' => self::LIKE);


	protected function _gridRead($params)
	{
		return $this->_iaDb->all(iaDb::ALL_COLUMNS_SELECTION, '', 0, null, 'coupons_codes');
	}
}