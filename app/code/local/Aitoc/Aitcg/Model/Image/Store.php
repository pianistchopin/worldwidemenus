<?php
class Aitoc_Aitcg_Model_Image_Store extends Mage_Core_Model_Abstract
{

    public function _construct()
    {
        parent::_construct();
        $this->_init('aitcg/image_store');
    }

    public function loadByImageId( $image_id ) {
        $collection = $this->getCollection();
        $collection->addFieldToFilter('image_id', $image_id);
        $collection->load();
        return $collection->count();        
    }
    
    public function getOrdersByImageId( $image_id ) {
        $collection = $this->getCollection();
        $collection->addFieldToFilter('image_id', $image_id);
        $collection->getSelect()
            ->joinInner(
                array('o'=> $collection->getTable('sales/order') ), 
                'o.entity_id=main_table.order_id', 
                array('entity_id')
            );
            
        $collection->load();  

 
        return $collection;
    }
    
    public function getOrderCollection( $day_count ) {
        $collection = $this->getCollection();
        $select = $collection->getSelect();
        $select
            ->joinInner(
                array('ai'=> $collection->getTable('aitcg/image') ), 
                'ai.id=main_table.image_id',
                array('temp_id','create_time','is_order')
            )->where('ai.is_order!=?',0)
            ->joinInner(
                array('o'=> $collection->getTable('sales/order') ), 
                'o.entity_id=main_table.order_id', 
                array('status')
            );
        if($day_count >= 30) {
            $day_count = floor($day_count/30);
            $select
                ->where('ai.create_time < DATE_SUB( NOW(), INTERVAL ? MONTH)', $day_count);
        } else {
            $select
                ->where('ai.create_time < DATE_SUB( NOW(), INTERVAL ? DAY)', $day_count);
        }
        $collection->load();
        return $collection;
    }
    

}