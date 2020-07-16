<?php

class Ophirah_Not2Order_Block_Product_View_Options_Type_Text
    extends Ophirah_Not2Order_Block_Product_View_Options_Abstract
{

    public function getDefaultValue()
    {
        return $this->getProduct()->getPreconfiguredValues()->getData('options/' . $this->getOption()->getId());
    }
}
