<?php

class Aitoc_Aitcg_Model_Sales_Order_Item_Converter extends Mage_Core_Model_Abstract
{
    const CUSTOMER_IMAGE_META_VERSION = '1.0';

    const CUSTOMER_IMAGE_META_VERSION_KEY = 'metadata_version';

    public function getConvertedOrderItem()
    {
        $productOptions = $this->getOrderItem()->getProductOptions();
        if (isset($productOptions['options']))
        {
            foreach ($productOptions['options'] as $optionData)
            {
                $this->getConvertedOptionValue($optionData);
            }
        }

        return $this->getOrderItem();
    }

    public function getConvertedOptionValue($optionValue)
    {
        if (isset($optionValue['option_id']) && Mage::helper('aitcg/options')->checkAitOption($this->_getOption($optionValue['option_id'])))
        {
            if ($this->_checkOptionValue($optionValue))
            {
                $optionVersionKey = $this->_getOptionMetadataVersion($optionValue);
                $currentVersionKey = $this->_getVersionReplacementKey(self::CUSTOMER_IMAGE_META_VERSION);
                $method = '_from' . $optionVersionKey . 'To' . $currentVersionKey;
                return $this->$method($optionValue);
            }
        }

        return $optionValue;
    }

    protected function _fromNonversionedTo10($optionValue)
    {
        $item = $this->getOrderItem();
        $productOptions = $item->getProductOptions();
        foreach ($productOptions['options'] as $key => $optionData)
        {
            if ($optionData['option_id'] == $optionValue['option_id'])
            {
                $option = $this->_getOption($optionData['option_id']);
                $value = unserialize($optionData['option_value']);
                $templateId = $value['template_id'];
                $group = $option->groupFactory($option->getType())->setOption($option);
                $confItemOption = array(
                    'type'          => 'image/png',
                    'title'         => Mage::helper('aitcg')->__('Please enable the Aitoc Custom Product Preview extension, then you will see an image option.'),
                    'quote_path'    => '',
                    'order_path'    => '',
                    'fullpath'      => '',
                    'size'          => '',
                    'width'         => '',
                    'height'        => '',
                    'secret_key'    => '',
                    Aitoc_Aitcg_Helper_Options::OPTION_DATA_KEY => array(
                        'template_id' => $templateId,
                        self::CUSTOMER_IMAGE_META_VERSION_KEY => self::CUSTOMER_IMAGE_META_VERSION
                    )
                );
                $productOptions['info_buyRequest']['options'][$option->getId()] = $confItemOption;
                $userData = array(
                    'label' => $option->getTitle(),
                    'value' => $group->getFormattedOptionValue(serialize($confItemOption)),
                    'print_value' => $group->getPrintableOptionValue(serialize($confItemOption)),
                    'option_id' => $option->getId(),
                    'option_type' => Aitoc_Aitcg_Model_Rewrite_Catalog_Product_Option::OPTION_GROUP_FILE,
                    'option_value' => serialize($confItemOption),
                    'custom_view' => $group->isCustomizedView()
                );
                $productOptions['options'][$key] = $userData;
                $this->_convertImageData($templateId);
            }
        }
        $item->setProductOptions($productOptions)->save();
        $this->setOrderItem($item);
        return $userData;
    }

    protected function _convertImageData($templateId)
    {
        $image = Mage::getModel('aitcg/image')->load($templateId);
        $data = Mage::helper('core')->jsonDecode($image->getImgData());
        if (isset($data['data']))
        {
            return;
        }
        $result = array();
        foreach ($data as $item)
        {
            if (($item['preserveAspectRatio'] == 'xMidYMid') && ($imageSize = $this->_getImageSize($item)))
            {
                $d0 = $item['width']/$item['height'];
                $d1 = $imageSize['width']/$imageSize['height'];
                if ($d1 > $d0)
                {
                    $h = $imageSize['height'] * $item['width'] / $imageSize['width'];
                    $item['y'] = $item['y'] + ($item['height'] - $h) / 2;
                    $item['height'] = $h;
                } else {
                    $w = $imageSize['width'] * $item['height'] / $imageSize['height'];
                    $item['x'] = $item['x'] + ($item['width'] - $w) / 2;
                    $item['width'] = $w;
                }
                $item['preserveAspectRatio'] = 'none';
            }
            $item['creator'] = array(
                'type' => 'UserImage',
                'params' => null,
                'isNew' => false
            );
            $r = isset($item['rotation']) ? $item['rotation'] : 0;
            $item['transform'] =
                'r' . $r . ',' . ($item['x'] + $item['width']/2) . ',' . ($item['y'] + $item['height']/2) .
                't' . $item['x'] . ',' . $item['y'];
            $item['x'] = 0;
            $item['y'] = 0;
            $item['r'] = 0;
            unset($item['rotation']);
            $result[] = $item;
        }

        $image
            ->setImgData(Mage::helper('core')->jsonEncode(array('data' => $result)))
            ->save();
    }

    protected function _getImageSize($image)
    {
        $imageSize = getimagesize($image['src']);
        if ($imageSize)
        {
            return array(
                'width' => $imageSize[0],
                'height' => $imageSize[1]
            );
        }
        return false;
    }

    protected function _checkOptionValue($value)
    {
        $data = unserialize($value['option_value']);
        if (isset($data[Aitoc_Aitcg_Helper_Options::OPTION_DATA_KEY])
            &&
            version_compare(
                $data[Aitoc_Aitcg_Helper_Options::OPTION_DATA_KEY][self::CUSTOMER_IMAGE_META_VERSION_KEY],
                self::CUSTOMER_IMAGE_META_VERSION,
                'ge'
            ))
        {
            return false;
        } else {
            return true;
        }
    }

    protected function _getOptionMetadataVersion($value)
    {
        $data = unserialize($value['option_value']);
        if (isset($data[Aitoc_Aitcg_Helper_Options::OPTION_DATA_KEY]) &&
            isset($data[Aitoc_Aitcg_Helper_Options::OPTION_DATA_KEY][self::CUSTOMER_IMAGE_META_VERSION_KEY]))
        {
            $optionVersion = $data[Aitoc_Aitcg_Helper_Options::OPTION_DATA_KEY][self::CUSTOMER_IMAGE_META_VERSION_KEY];
        } else {
            $optionVersion = 'Nonversioned';
        }
        return $this->_getVersionReplacementKey($optionVersion);
    }

    protected function _getVersionReplacementKey($version)
    {
        return str_replace('.', '', $version);
    }

    protected function _getOption($optionId)
    {
        $productOptions = $this->getOrderItem()->getProductOptions();
        $product = Mage::getModel('catalog/product')->load($productOptions['info_buyRequest']['product']);
        return $product->getOptionById($optionId);
    }

}