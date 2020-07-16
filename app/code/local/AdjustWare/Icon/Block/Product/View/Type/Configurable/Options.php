<?php

class AdjustWare_Icon_Block_Product_View_Type_Configurable_Options extends Mage_Catalog_Block_Product_View_Type_Configurable
{
    public function getAdjAttributesConfig()
    {
        $adjAttributes = array();

        foreach ($this->getAllowAttributes() as $attribute) {
            $productAttribute = $attribute->getProductAttribute();
            $attributeId = $productAttribute->getId();
            $adjAttribute = Mage::getModel('adjicon/attribute')->load($attributeId, 'attribute_id');
            $info = array(
                'icon_id'                  => $adjAttribute->getId(),
                'color_icon_type'          => $adjAttribute->getColorIconType(),
                'icon_type'                => $adjAttribute->getIconType(),
                'config_option_type'       => $adjAttribute->getConfigOptionType(),
                'show_images_configurable' => $adjAttribute->getShowImagesConfigurable(),
                'visualization_type'       => $adjAttribute->getVisualizationType(),
                'options'                  => $adjAttribute->getAttributeOptions()
            );

            $adjAttributes[$attributeId] = $info;
        }
        $config = array(
            'media_url'   => Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA),
            'attributes'  => $adjAttributes
        );
        return Mage::helper('core')->jsonEncode($config);
    }

    public function getImagesSet()
    {
        $attributes = $this->getAllowAttributes()->getItems();
        $mainAttribute = Mage::helper('adjicon')->getFirstAttribute($attributes);
        $options = array();
        $images = array();
        foreach($mainAttribute->getPrices() as $option) {
            $options[] = $option['value_index'];
        }

        $adjImageModel = Mage::getModel('adjicon/image');
        $size = Mage::helper('adjicon')->getThumbsSizes('small');
        foreach($options as $id) {
            $set = Mage::getModel('adjicon/image')->getOptionImagesSet($id, $mainAttribute->getProductId());
            foreach($set as &$row) {
                $row['gallery'] = $this->getCustomGalleryUrl($row['image_id'], $id);
                $row['width'] = $size;
                $row['src_full'] = $row['file'];
                $row['src'] = $adjImageModel->resize($row['file'], $size);
            }
            $images[$id]['files'] = $set;
            $baseImage = $adjImageModel->getOptionImageBase($id, $mainAttribute->getProductId());
            $images[$id]['base'] = $baseImage;
        }

        $adjAttributes = array(
            'baseMediaUrl' => Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA).'adjconfigurable/',
            'options'      => $images
        );
        return Mage::helper('core')->jsonEncode($adjAttributes);
    }

    public function getCustomGalleryUrl($imageId, $optionId)
    {
        $params = array('id' => $this->getProduct()->getId());
        $params['option'] = $optionId;
        $params['image'] = $imageId;

        return $this->getUrl('adjicon/adjicon/gallery', $params);
    }
}