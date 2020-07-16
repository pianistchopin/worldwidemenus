<?php
class AdjustWare_Icon_Model_Attribute extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('adjicon/attribute');
    }

	// return array of icons with store specific titles by given option ids
    public function getIconsByOptions($ids, $storeId=null)
    {    
        return $this->getResource()->getIconsByOptions($ids, $storeId);        
    }

    // return array of all options with default titles and icon information
    public function getOptions()
    {    
        return $this->getResource()->getOptions($this->getAttributeId());
    }

    public function getAttributeOptions()
    {
        return $this->getResource()->getAttributeOptions($this->getAttributeId());
    }

	public function getOptionById($optionId, $attributeId)
	{
		return $this->getResource()->getOptionById($optionId, $attributeId);
	}

	public function getColorOptions()
	{
		return $this->getResource()->getColorOptions($this->getAttributeId());
	}

    // return array of all drop-down magento attributes which haven't linked to icons yet
    public function getAvailableAttributesAsOptions()
    {    
        $attributes =  $this->getResource()->getAvailableAttributes();        
        $options    = array();
        $options[] = array('value'=>0, 'label'=>'Please select');
        foreach ($attributes as $a){
            $options[] = array('value'=>$a['attribute_id'], 'label'=>$a['frontend_label']);
        }
        return $options;
    }

    protected function _beforeSave() {
        $attrModel = Mage::getModel('eav/entity_attribute')->load($this->getAttributeId());
        $this->setAttributeCode($attrModel->getAttributeCode());
    }

    protected function _afterSave() {
        parent::_afterSave();

        if ($this->getId()) {
            $options = $this->getOptions();
            foreach ($options as $info){
                $color = Mage::getModel('adjicon/color');
                /* @var $icon AdjustWare_Icon_Model_Icon */
                $color->load($info['color_id'])
                    ->saveColor($info);
            }
        }
        
        return $this;
    }
}