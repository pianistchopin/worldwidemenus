<?php
class Aitoc_Aitcg_Block_Rewrite_Sales_Order_Email_Items_Order_Default extends Mage_Sales_Block_Order_Email_Items_Order_Default {
    
    public function getItemOptions()
    {
        $result = array();
        if ($options = $this->getItem()->getProductOptions()) {
            if (isset($options['options'])) {
                $result = array_merge($result, $options['options']);
            }
            if (isset($options['additional_options'])) {
                $result = array_merge($result, $options['additional_options']);
            }
            if (isset($options['attributes_info'])) {
                $result = array_merge($result, $options['attributes_info']);
            }
        }
        foreach ($result as & $option) {
            if (isset($option['option_type']) && Mage::helper('aitcg/options')->checkAitOption($option)) {
                if($image = Mage::helper('aitcg/options')->getImageHtml($option))
                {
                    $option['print_value'] = $image;
                    $option['value'] = $option['print_value'];
                }
            }
        }

        return $result;
    }

}