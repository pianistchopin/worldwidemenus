<?php

class Ophirah_Not2Order_Block_Product_View_Options_Type_File
    extends Mage_Catalog_Block_Product_View_Options_Abstract
{
    /**
     * Returns info of file
     *
     * @return string
     */
    public function getFileInfo()
    {
        $info = $this->getProduct()->getPreconfiguredValues()->getData('options/' . $this->getOption()->getId());
        if (empty($info)) {
            $info = new Varien_Object();
        } else if (is_array($info)) {
            $info = new Varien_Object($info);
        }
        return $info;
    }
}