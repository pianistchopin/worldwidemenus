<?php

class FME_Photogallery_Model_Observer_Product extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {    
        // Note that the photogallery_id refers to the key field in your database table.
        $this->_init('photogallery/photogallery', 'photogallery_id');
    }
    
    /**
     * Inject one tab into the product edit page in the Magento admin
     *
     * @param Varien_Event_Observer $observer
     */
    public function injectTabs(Varien_Event_Observer $observer)
    {
        $block = $observer->getEvent()->getBlock();
        
        if ($block instanceof Mage_Adminhtml_Block_Catalog_Product_Edit_Tabs) {
            if ($this->_getRequest()->getActionName() == 'edit' || $this->_getRequest()->getParam('type')) {
                $block->addTab(
                    'custom-product-tab-01', array(
                    'label'     => 'Photogallery',
                    'content'   => $block->getLayout()->createBlock('adminhtml/template', 'custom-tab-content', array('template' => 'photogallery/content.phtml'))->toHtml(),
                    )
                );
            }
        }
    }

    /**
     * This method will run when the product is saved
     * Use this function to update the product model and save
     *
     * @param Varien_Event_Observer $observer
     */
    public function saveTabData(Varien_Event_Observer $observer)
    {
        if ($post = $this->_getRequest()->getPost()) {
            if(isset($post["photogallery_id"]) && $post["photogallery_id"] != 0) {
                try {
                    // Load the current product model	
                    $product = Mage::registry('product');
                    $condition = $this->_getWriteAdapter()->quoteInto('product_id = ?', $product["entity_id"]);
                    $this->_getWriteAdapter()->delete($this->getTable('photogallery_products'), $condition);    
                    $productsArray = array();
                    $productsArray['photogallery_id'] = $post["photogallery_id"];
                    $productsArray['product_id'] = $product["entity_id"];
                    $this->_getWriteAdapter()->insert($this->getTable('photogallery_products'), $productsArray);                
                }
                 catch (Exception $e) {
                    Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                 }
            }
        }
    }
    
    /**
     * Shortcut to getRequest
     */
    protected function _getRequest()
    {
        return Mage::app()->getRequest();
    }
}
