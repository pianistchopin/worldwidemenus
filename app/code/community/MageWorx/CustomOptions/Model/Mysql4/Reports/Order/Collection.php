<?php 
class MageWorx_CustomOptions_Model_Mysql4_Reports_Order_Collection extends Mage_Reports_Model_Resource_Order_Collection
{
    /**
     * REWRITE FUNCTION
     * Add revenue
     *
     * @param boolean $convertCurrency
     * @return Mage_Reports_Model_Resource_Order_Collection
     */
    public function addRevenueToSelect($convertCurrency = false)
    {
        if ($convertCurrency) {
            $this->getSelect()->columns(array(
                'revenue' => 'IF(main_table.base_to_global_rate,(main_table.base_grand_total * main_table.base_to_global_rate),main_table.base_grand_total)'
            ));
        } else {
            $this->getSelect()->columns(array(
                'revenue' => 'base_grand_total'
            ));
        }

        return $this;
    }
}