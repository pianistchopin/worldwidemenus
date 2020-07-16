<?php
class AdjustWare_Icon_Model_Color extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('adjicon/color');
    }

    public function saveColor($attributeOptionInfo)
    {
        $optionId = $attributeOptionInfo['option_id'];
        if(!($color = Mage::app()->getRequest()->getPost('color'.$optionId))) {
			$this->deleteColor($optionId);
		}

		$this->setOptionId($optionId);
        $this->setColor($color);
        $this->save();
    }

	public function deleteColor($optionId) {
		$this->setOptionId($optionId);
		$this->delete();
	}
}