<?php
//##copyright##

interface iaCouponsPackage
{
	const PACKAGE_NAME = 'coupons';

	const COLUMN_ID = 'id';

	const STATUS_AVAILABLE = 'available';
	const STATUS_SUSPENDED = 'suspended';
	const STATUS_USED = 'used';
}

abstract class abstractCouponsPackageAdmin extends abstractPackageAdmin implements iaCouponsPackage
{
	protected $_packageName = 'coupons';
}

abstract class abstractCouponsPackageFront extends abstractPackageFront implements iaCouponsPackage
{
	protected $_packageName = 'coupons';
}