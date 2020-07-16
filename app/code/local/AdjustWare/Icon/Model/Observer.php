<?php

class AdjustWare_Icon_Model_Observer 
{
    protected $_configurableAttrValues = null;

    public function bindConfigChanges(Varien_Event_Observer $observer)
    {
        $action = $observer->getEvent()->getControllerAction();
        /* @var $action Mage_Core_Controller_Varien_Action */
        
        if ('adminhtml_system_config_save' != $action->getFullActionName() || $action->getRequest()->getParam('section') != 'design') {
            return;
        }
        
        $groups = $action->getRequest()->getPost('groups');
        if (array_key_exists('adjicon', $groups)) {
            if ($this->_isResizeConfigChanged($groups['adjicon'])) {
                Mage::register('adjicon_resize_config_changed', true);
            }
        }
    }
    
    protected function _isResizeConfigChanged($newConfig)
    {
        $newConfig = $this->_convertInputGroupData($newConfig);
        $oldConfig = Mage::getConfig()->getNode('default/design/adjicon')->asArray();
        return $newConfig != $oldConfig;
    }
    
    protected function _convertInputGroupData($newConfig)
    {
        $simpleConfig = array();
        foreach ($newConfig['fields'] as $k => $value) {
            $simpleConfig[$k] = empty($value['value'])? null : $value['value'];
        }
        return $simpleConfig;
    }
    
    public function resizeIcons(Varien_Event_Observer $observer)
    {
        if (!Mage::registry('adjicon_resize_config_changed')) {
            return;
        }
        $iconCollection = Mage::getResourceModel('adjicon/icon_collection');
        foreach ($iconCollection as $icon) {
            $icon->setResizeOptions(Mage::getConfig()->getNode('default/design/adjicon')->asArray())->makeThumb();
        }
    }

    public function setFilterTemplate($observer)
    {
        $block = $observer->getBlock();
        if ($block instanceof Mage_Catalog_Block_Layer_Filter_Attribute && !Mage::helper('adjicon')->isModuleEnabled('AdjustWare_Nav')) {
            $block->setTemplate('adjicon/filter_attribute.phtml');
        }
    }

    public function coreBlockAbstractToHtmlBefore($observer)
    {
        $block = $observer->getBlock();
        $this->setFilterIcons($block);
        $this->setProductConfigurableImage($block);
    }

    public function setFilterIcons($block)
    {
        if ($block instanceof Mage_Catalog_Block_Layer_View && !Mage::helper('adjicon')->isModuleEnabled('AdjustWare_Nav')) {
            Mage::helper('adjicon')->addIconsToFilters($block->getChild());
        }
    }

    public function setProductConfigurableImage($block)
    {
        if ($block instanceof Mage_Checkout_Block_Cart_Item_Renderer_Configurable && Mage::getStoreConfig('design/adjicon/replace_configurable_product_image')) {
            $product = $block->getProduct();
            $optionsArray = unserialize($product->getCustomOption('attributes')->getValue());
            $imageModel = Mage::getModel('adjicon/image');
            foreach($optionsArray as $option)
            {
                $adjAttr = $imageModel->getOptionImageBase($option, $product->getId());
                if(!empty($adjAttr['file']))
                {
                    $imageName = $adjAttr['file'];
                    if($cacheName = Mage::helper('adjicon/image')->setImageAttributeInCache($imageName))
                    {
                        if($product->getData('thumbnail_original') == null)
                        {
                            $product->setData('thumbnail_original', $product->getData('thumbnail'));
                        }
                        $product->setData('thumbnail', $cacheName);
                    }
                    return true;
                }
            }

            if($product->getData('thumbnail_original') != null)
            {
                $product->setData('thumbnail', $product->getData('thumbnail_original'));
            }
        }
    }

    public function setLayeredFilterIcons($observer)
    {
        $block = $observer->getEvent()->getLayerViewBlock();
        Mage::helper('adjicon')->addIconsToFilters($block->getChild());
    }

    /*
     * @param Varien_Event_Observer $observer
     */
    public function saveAdjImagesInfo($observer)
    {
        $mediaGallery = $observer->getEvent()->getProduct()->getMediaGallery();
        if(isset($mediaGallery['adjicon_values']) && isset($mediaGallery['adjicon_images'])) {
            foreach($mediaGallery['adjicon_values'] as $id => $value) {
                $baseImages = Mage::helper('core')->jsonDecode($value);
                $base[] = $baseImages['base_image'];
            }
            foreach($mediaGallery['adjicon_images'] as $optionId => $images) {
                $optionValues = Mage::helper('core')->jsonDecode($images);
                foreach($optionValues as $optionValue) {
                    $model = Mage::getModel('adjicon/image')->load($optionValue['image_id']);
                    if(in_array($optionValue['file'], $base)) {
                        $model->setBaseImage(1);
                    }
                    else {
                        $model->setBaseImage(0);
                    }
                    $model->setLabel($optionValue['label']);

                    $model->save();

                    // <<< Aitoc CPP compatibility
                    if(Mage::helper('adjicon')->isCPPEnabled() && isset($optionValue['cpp_option_id'])){
                        $vyaCPPModel = Mage::getModel('adjicon/cpp')->setAdjImagesCPPOptions($model, $optionValue['cpp_option_id']);
                        if($vyaCPPModel->validateCPP()){
                            $vyaCPPModel->save();
                        }
                        else{
                            $error = $this->_getCPPError($observer, $vyaCPPModel, $model);
                            Mage::getSingleton('adminhtml/session')->addError($error);
                        }
                    }
                    // >>>
                }
            }
        }
    }

    /*
     * Aitoc CPP compatibility
     * @param Varien_Event_Observer $observer
     * @param AdjustWare_Icon_Model_Cpp $vyaCPPModel
     * @param AdjustWare_Icon_Model_Image $adjImageModel
     * @return string
     */
    protected function _getCPPError($observer, $vyaCPPModel, $adjImageModel)
    {
        $error = Mage::helper('adjicon')->__('Additional images for attributes and corresponding CPP custom options should have identical dimensions. ');

        $optionTitle = $vyaCPPModel->getCPPOptionTitle();
        $this->_setVYAAttributeValues($observer);
        $vyaAttributeValue = $this->_getVYAAttributeValue($adjImageModel);
        if($optionTitle && $vyaAttributeValue){
            $error .= Mage::helper('adjicon')->__('. Image for \'%s\' value is different from \'%s\' CPP custom option image', $vyaAttributeValue, $optionTitle);
        }

        return $error;
    }

    /*
     * Aitoc CPP compatibility
     * @param Varien_Event_Observer $observer
     */
    protected function _setVYAAttributeValues($observer)
    {
        if($this->_configurableAttrValues === null){
            $product =  $observer->getEvent()->getProduct();
            $this->_configurableAttrValues = Mage::helper('adjicon')->getConfigurableAttributeValues($product);
        }
    }

    /*
     * Aitoc CPP compatibility
     * @param AdjustWare_Icon_Model_Image $adjImageModel
     * @return mixed
     */
    protected function _getVYAAttributeValue($adjImageModel)
    {
        if($this->_configurableAttrValues == null){
            return false;
        }

        $value = $adjImageModel->getOptionId();
        if(isset($this->_configurableAttrValues[$value])){
            return $this->_configurableAttrValues[$value];
        }

        return false;
    }

    public function initProductOnGallery($observer)
    {
        $action = $observer->getControllerAction();
        if($action instanceof AdjustWare_Icon_AdjiconController) {
            $product = $observer->getEvent()->getProduct();
            $params = $action->getRequest()->getParams();
            if(isset($params['option'])) {
                $product->setAdjOption($params['option']);
            }
        }
    }

    /**
     * Disable Configurable swatches if VYA is enabled
     *
     * @param Varien_Event_Observer $observer
     */
    public function disableSwatches(Varien_Event_Observer $observer)
    {
        if($this->_disableSwatchesExecuted)
        {
            return;
        }
        $this->_disableSwatchesExecuted = true;

        $magentoVersion = Mage::getVersion();
        if(Mage::getStoreConfig('configswatches/general/enabled') && version_compare($magentoVersion, '1.9.0', '>=')
        )
        {
            Mage::getModel('core/config')->saveConfig('configswatches/general/enabled', 0);
            Mage::app()->getCacheInstance()->cleanType('config');
        }
    }

    /**
     * Remove configurable swatches js because it's conflicting with our js
     *
     * @param Varien_Event_Observer $observer
     */
    public function removeConfigurableSwatchesJs(Varien_Event_Observer $observer)
    {
        if(version_compare(Mage::getVersion(), '1.9.0', '>='))
        {
            $headBlock = $observer->getLayout()->getBlock('head');
            if(!is_object($headBlock))
            {
                return;
            }
            $items = $headBlock->getData('items');
            if(isset($items['skin_js/js/configurableswatches/product-media.js']))
            {
                $headBlock->removeItem('skin_js', 'js/configurableswatches/product-media.js');
            }
            if(isset($items['skin_js/js/configurableswatches/swatches-product.js']))
            {
                $headBlock->removeItem('skin_js', 'js/configurableswatches/swatches-product.js');
            }
        }
    }

}