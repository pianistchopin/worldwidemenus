<?php
class Aitoc_Aitcg_Model_Rewrite_Catalog_Product_Attribute_Backend_Media extends Mage_Catalog_Model_Product_Attribute_Backend_Media
{
    protected $_renamedImages = array();

    /**
     * Load attribute data after product loaded
     *
     * @param Mage_Catalog_Model_Product $object
     */
    public function afterLoad($object)
    {
        $attrCode = $this->getAttribute()->getAttributeCode();
        $value = array();
        $value['images'] = array();
        $value['values'] = array();
        $localAttributes = array('label', 'position', 'disabled', 'cgimage');

        foreach ($this->_getResource()->loadGallery($object, $this) as $image) {
            foreach ($localAttributes as $localAttribute) {
                if (is_null($image[$localAttribute])) {
                    $image[$localAttribute] = $this->_getDefaultValue($localAttribute, $image);
                }
            }
            $value['images'][] = $image;
        }

        $object->setData($attrCode, $value);
    }

    public function afterSave($object)
    {
        if ($object->getIsDuplicate() == true) {
            $this->duplicate($object);
            return;
        }

        $attrCode = $this->getAttribute()->getAttributeCode();
        $value = $object->getData($attrCode);
                
        if(version_compare(Mage::getVersion(), '1.4.0.0', '>='))
        {
            if (!is_array($value) || !isset($value['images']) || $object->isLockedAttribute($attrCode)) 
            {
                return;
            }
        }
        else
        {
            if (!is_array($value) || !isset($value['images'])) 
            {
                return;
            }
        }
        $this->_imagesGalleryRewrite($value['images'], $object);
        
    }

    protected function _imagesGalleryRewrite($images, $object)
    {

        $toDelete = array();
        $filesToValueIds = array();
        foreach ($images as &$image) {
            if(!empty($image['removed'])) {
                if(isset($image['value_id'])) {
                    $toDelete[] = $image['value_id'];
                }
                continue;
            }            
            $this->_imageUpdateGallery($image,$object);
        }
        $this->_getResource()->deleteGallery($toDelete);
            return true;
    }
    protected function _imageUpdateGallery($image, $object)
    {
        if(!isset($image['value_id'])) {
            $data = array();
            $data['entity_id']      = $object->getId();
            $data['attribute_id']   = $this->getAttribute()->getId();
            $data['value']          = $image['file'];
            $image['value_id']      = $this->_getResource()->insertGallery($data);
        }

        $this->_getResource()->deleteGalleryValueInStore($image['value_id'], $object->getStoreId());

            // Add per store labels, position, disabled
            $data = array();
            $data['value_id'] = $image['value_id'];
            $data['label']    = $image['label'];
            $data['position'] = (int) $image['position'];
            $data['disabled'] = (int) $image['disabled'];
            $data['cgimage'] = (int) empty($image['cgimage'])?0:$image['cgimage']; //if update img after duplicate, this string shows error
            $data['store_id'] = (int) $object->getStoreId();

        $this->_getResource()->insertGalleryValueInStore($data);
        return true;
    }
}
