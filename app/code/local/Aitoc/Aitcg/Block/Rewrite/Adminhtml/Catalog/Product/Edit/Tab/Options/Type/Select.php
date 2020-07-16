<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magento.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magento.com for more information.
 *
 * @category    Mage
 * @package     Mage_Adminhtml
 * @copyright  Copyright (c) 2006-2017 X.commerce, Inc. and affiliates (http://www.magento.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * customers defined options
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author     Magento Core Team <core@magentocommerce.com>
 */

class Aitoc_Aitcg_Block_Rewrite_Adminhtml_Catalog_Product_Edit_Tab_Options_Type_Select extends
	Mage_Adminhtml_Block_Catalog_Product_Edit_Tab_Options_Type_Select
{
	/**
	 * Class constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->setTemplate('aitcommonfiles/design--adminhtml--default--default--template--catalog--product--edit--options--type--select.phtml');
		$this->setCanEditPrice(true);
		$this->setCanReadPrice(true);
	}
    public function getCoverCategorySelectHtml(){
        $collection = Mage::getModel('aitcg/category')->getCollection();

       // if ($ids !== null) {
           // $collection->addFieldToFilter('category_id', array('in' => explode(',' ,$ids)) );
       // }
        $category = array('' => 'Select cover');
        foreach($collection as $row) {
            $category[$row->getCategoryId()] = htmlentities($row->getName(), ENT_QUOTES);
        }

        $select = $this->getLayout()->createBlock('adminhtml/html_select')
            ->setData(array(
                'id' => 'product_option_{{id}}_select_{{select_id}}_cover_category',
                'class' => 'select'
            ))
            ->setName('product[options][{{id}}][values][{{select_id}}][cover_category]')
            ->setOptions($category);

        return $select->getHtml();

    }
	public function getMaskCategorySelectHtml()
	{
		$collection = Mage::getModel('aitcg/mask_category')->getCollection();
		$category = array('' => 'Select mask category');
		foreach($collection as $row) {
            $category[$row->getId()] = $row->getName().((!empty($row->getNameSuffix()))?' – '.$row->getNameSuffix():'');
        }

		$select = $this->getLayout()->createBlock('adminhtml/html_select')
		               ->setData(array(
			               'id' => 'product_option_{{id}}_select_{{select_id}}_mask_category',
			               'class' => 'select'
		               ))
		               ->setName('product[options][{{id}}][values][{{select_id}}][mask_category]')
		               ->setOptions($category);

		return $select->getHtml();
	}
}
