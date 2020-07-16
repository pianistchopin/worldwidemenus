<?php
class Aitoc_Aitcg_Model_Resource_Template extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct() {
        $this->_init('catalog/product_entity_varchar', 'value_id');
    }
    
    public function getValueTable($entityName, $valueType)
    {
        if (version_compare(Mage::getVersion(), '1.4.0.0', '>=')) {
            return parent::getValueTable($entityName, $valueType);
        }
        
        return $this->getTable($entityName) . '_' . $valueType;
    }

    protected function _getTemplateImageDB($product_id, $template_id)
    {
        $select = $this->_getReadAdapter()->select()
            ->from( $this->getValueTable('catalog/product', 'media_gallery') )
            ->where('entity_id=?', $product_id)
            ->where('value_id=?', $template_id);
        $mediaImage = $this->_getReadAdapter()->fetchRow($select);
        if (!is_array($mediaImage) || sizeof($mediaImage)==0 || $mediaImage['value']=="") {
            return false;
        }

        return $mediaImage;
    }

    /*
     * @param integer $product_id
     * @param integer $template_id
     * @param string $thumbType
     * @return array
     */
    public function getTemplateImage( $product_id, $template_id, $thumbType = Aitoc_Aitcg_Helper_Data::INTEGRATION_DEFAULT )
    {
        $mediaImage = $this->_getTemplateImageDB($product_id, $template_id);
        if (!$mediaImage) {
            return false;
        }

        $img = Mage::helper('aitcg/image')->loadLite( $mediaImage["value"] );
        /** @var $img Aitoc_Aitcg_Helper_Image */

        $mediaImage = $this->_getTemplateImage($thumbType, $mediaImage, $img);
        
        $mediaImage['full_image'] = Mage::getSingleton('catalog/product_media_config')
            ->getMediaUrl($mediaImage['value']);

        return $mediaImage;
    }

    /*
     * @param string $thumbType
     * @param array $mediaImage
     * @param Aitoc_Aitcg_Helper_Image $img
     * @return array
     */
    protected function _getTemplateImage($thumbType, $mediaImage, $img)
    {
        list ($default_size_x, $default_size_y) = Mage::helper('aitcg')->getThumbSize( $thumbType );
       // $default_size_x=250;
        //$default_size_y=250;
        $mediaImage['thumbnail_url'] = $img->keepFrame(false)
            ->constrainOnly(true)
            ->resize($default_size_x, $default_size_y)
            ->__toString();
        $mediaImage['default_size']   = $img->getOriginalSize();
        $mediaImage['thumbnail_file'] = $img->getNewFile();
        $mediaImage['thumbnail_size'] = Mage::helper('aitcg/options')
            ->calculateThumbnailDimension( $default_size_x, $default_size_y, $mediaImage['default_size'][0], $mediaImage['default_size'][1] );

        return $mediaImage;
    }

    /*
     * Compatibility with VYA extension. Used on cart page for example.
     * @param string $thumbType
     * @param string $fullImgUrl
     * @return array
     */
    public function getTemplateImageForVYA($thumbType = Aitoc_Aitcg_Helper_Data::INTEGRATION_DEFAULT, $fullImgUrl )
    {
        $explodedUrl = explode('/', $fullImgUrl);
        $imgName = end($explodedUrl);
        $img = Mage::helper('aitcg/image')->loadLite($imgName,'adjconfigurable');
        // @var $img Aitoc_Aitcg_Helper_Image

        $mediaImage = $this->_getTemplateImage($thumbType, array(), $img);

        $mediaImage['full_image'] = $fullImgUrl;

        return $mediaImage;
    }


    /*
     * Compatibility with VYA extension
     * @param integer $product_id
     * @param integer $template_id
     * @return string
     */
    public function getFullImageUrlForVYA($product_id, $template_id)
    {
        $mediaImage = $this->_getTemplateImageDB($product_id, $template_id);
        if (!$mediaImage) {
            return false;
        }

        $url = Mage::getSingleton('catalog/product_media_config')
            ->getMediaPath($mediaImage['value']);

        return $url;
    }
}