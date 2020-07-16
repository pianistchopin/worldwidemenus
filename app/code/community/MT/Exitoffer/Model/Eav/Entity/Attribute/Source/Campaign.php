<?php

class MT_Exitoffer_Model_Eav_Entity_Attribute_Source_Campaign
    extends Mage_Eav_Model_Entity_Attribute_Source_Abstract
{
    public function getAllOptions($withEmpty = false)
    {
        return $this->toOptionArray();
    }

    public function toOptionArray()
    {
        if (!$this->_options) {
            $this->_options[0] = ' ';
            $collection = Mage::getModel('exitoffer/campaign')->getCollection();
            if ($collection->count() > 0) {
                foreach ($collection as $campaign) {
                    $this->_options[$campaign->getId()] = $campaign->getName();
                }
            }
        }

        return $this->_options;
    }
}