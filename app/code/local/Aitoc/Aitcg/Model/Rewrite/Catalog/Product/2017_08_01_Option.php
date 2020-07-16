<?php
class Aitoc_Aitcg_Model_Rewrite_Catalog_Product_Option extends Mage_Catalog_Model_Product_Option
{
    const OPTION_TYPE_AITCUSTOMER_IMAGE      = 'aitcustomer_image';

    /**
     * Get group name of option by given option type
     *
     * @param string $type
     * @return string
     */
    public function getGroupByType($type = null)
    {
        if (is_null($type)) 
        {
            $type = $this->getType();
        }
        switch ($type)
        {
            case self::OPTION_TYPE_AITCUSTOMER_IMAGE: return self::OPTION_GROUP_FILE;
        }
        return parent::getGroupByType($type); 
    }

    /**
     * For 1.6.1.0 compatibility
     * 
     * @return type 
     */
    public function getType()
    {
        if (parent::getType() == self::OPTION_TYPE_AITCUSTOMER_IMAGE && $this->_ignoreCustomType())
        {
            return Mage_Catalog_Model_Product_Option::OPTION_GROUP_FILE;
        }
        return parent::getType();
    }

    /**
     * @return bool
     */
    protected function _ignoreCustomType()
    {
        return Mage::helper('aitcg')->requestIsSuitable(array(
            'checkout/onepage/saveOrder',
            'checkout/multishipping/overviewPost',
            'sales/download/downloadCustomOption'
        ));
    }
    
    /**
     * Save options.
     *
     * @return Mage_Catalog_Model_Product_Option
     */
     
    public function saveOptions()
    {
        /* {#AITOC_COMMENT_END#}
         Mage::app()->removeCache('aitsys_used_product_count_aitcg_count');
         
         if($this->getProduct()->getData('status') == Mage_Catalog_Model_Product_Status::STATUS_ENABLED) {
            
            $bHasAitOption = false;
            $updateOptions = array();
            foreach ($this->getOptions() as $iKey => $aOption)
            {
                if( Mage::helper('aitcg/options')->checkAitOption( $aOption ) ) {
                    if($aOption['option_id'] == 0) {
                        $bHasAitOption = true;
                        break;
                    }
                    $updateOptions[ $aOption['option_id'] ] = $aOption['is_delete'];
                }
            }

            if($bHasAitOption == false) {
                foreach ($this->getProduct()->getOptions() as $iKey => $aOption)
                {
                    if(Mage::helper('aitcg/options')->checkAitOption( $aOption )) {
                        if(!isset($updateOptions[ $aOption->getId() ]) || $updateOptions[ $aOption->getId() ]!=1) {
                            $bHasAitOption = true;
                            break;
                        }
                    }
                }
            }

            if($bHasAitOption) {
                $performer = Aitoc_Aitsys_Abstract_Service::get()->platform()->getModule('Aitoc_Aitcg')->getLicense()->getPerformer();
                $ruler = $performer->getRuler();
                $ruler->checkRuleAdd($this->getProduct(), true);
            }

            
        }
        {#AITOC_COMMENT_START#} */
                
        parent::saveOptions();
    }
}