<?php
/**
 * @category	Solide Webservices
 * @package		Flexslider
 */

if (Mage::getModel('admin/block')) {

	$installer = $this;
	$installer->startSetup();
	$connection = $installer->getConnection();

	$installer->getConnection()->insertMultiple(
		$installer->getTable('admin/permission_block'),
		array(
			array('block_name' => 'flexslider/view', 'is_allowed' => 1),
		)
	);

	$installer->endSetup();

}

?>
