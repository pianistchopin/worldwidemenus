<?php

class AdjustWare_Icon_Block_Adminhtml_Catalog_Product_Edit_Tabs_Images extends Mage_Adminhtml_Block_Widget
    implements Mage_Adminhtml_Block_Widget_Tab_Interface
{
    const BASE_IMAGE_LABEL_KEY = 'Base_Image';
    const BASE_IMAGE_FIELD_KEY = 'base_image';

    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('adjicon/catalog/product/images.phtml');
    }

    protected function _getProduct()
    {
        return Mage::registry('current_product');
    }

    public function getControllerUrl($optionId, $optionLabel)
    {
        return Mage::getUrl('adminhtml/adjicon/uploadImageFile', array('id' => $optionId, 'product_id' => $this->_getProduct()->getId(), 'label' => $optionLabel));
    }

    public function getJsObjectName()
    {
        return $this->getHtmlId() . 'JsObject';
    }

    public function getImagesJson($optionId)
    {
        $images = Mage::getModel('adjicon/image')->getOptionImagesSet($optionId, $this->_getProduct()->getId());
        if(count($images)>0) {
            foreach($images as &$image) {
                $image['url'] = Mage::helper('adjicon/image')->getAdjConfigurableBaseUrl($image['file']);
            }

            return Mage::helper('core')->jsonEncode($images);
        }
        return '[]';
    }

    public function getImagesValuesJson($optionId)
    {
        $images = Mage::getModel('adjicon/image')->getOptionImageBase($optionId, $this->_getProduct()->getId());
        $values['base_image'] = $images['file'];
        return Mage::helper('core')->jsonEncode($values);
    }

    public function getConfigurableAttributeValues()
    {
        $product = $this->_getProduct();

        return Mage::helper('adjicon')->getConfigurableAttributeValues($product);
    }

    public function getImageTypes()
    {
        return array('base_image' => array(
            'label'  => self::BASE_IMAGE_LABEL_KEY,
            'field'  => self::BASE_IMAGE_FIELD_KEY
        ));
    }

    public function getImageTypesJson()
    {
        return Zend_Json::encode($this->getImageTypes());
    }

    /**
     * Retrieve the label used for the tab relating to this block
     *
     * @return string
     */
    public function getTabLabel()
    {
        return $this->__('Additional Images');
    }

    /**
     * Retrieve the title used by this tab
     *
     * @return string
     */
    public function getTabTitle()
    {
        return $this->__('Additinal Images');
    }

    /**
     * Determines whether to display the tab
     * Add logic here to decide whether you want the tab to display
     *
     * @return bool
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * Stops the tab being hidden
     *
     * @return bool
     */
    public function isHidden()
    {
        return false;
    }

    /*
     * Compatibility with CPP extension
     * @return array
     */
    public function getCPPOptions()
    {
        if(Mage::helper('adjicon')->isCPPEnabled()){
            $product = $this->_getProduct();
            $model = Mage::getModel('aitcg/product');
            return $model->getProductCPPOptions($product->getId());
        }

        return array();
    }
}