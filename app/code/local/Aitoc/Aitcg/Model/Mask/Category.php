<?php
class Aitoc_Aitcg_Model_Mask_Category extends Mage_Core_Model_Abstract
{   
    public function _construct()
    {
        parent::_construct();
        $this->_init('aitcg/mask_category');
    }


    protected $_storeLabels;
    
    public function getName()
    {
        $storeId = Mage::app()->getStore()->getStoreId();
        if($storeId)
        {
            $labels = $this->getStoreLabels();
            if(isset($labels[$storeId]) && $labels[$storeId])
            {
                return $labels[$storeId];
            }
        }
        return parent::getName();
    }
    
    public function getStoreLabels()
    {
        if(!isset($this->_storeLabels))
        {   
            $this->_storeLabels = unserialize(parent::getStoreLabels());
        }

        return $this->_storeLabels;
    }

    public function setStoreLabels($storelables)
    {
        return parent::setStoreLabels(serialize($storelables));
    }    
    
}
