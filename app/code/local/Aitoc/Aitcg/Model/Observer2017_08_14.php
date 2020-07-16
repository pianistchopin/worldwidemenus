<?php
class Aitoc_Aitcg_Model_Observer extends Mage_Core_Model_Abstract
{
    
    public function deleteOrderImages( $day_count ) 
    {
        $day_count = intval($day_count);

        $model = Mage::getModel('aitcg/image_store');
        $collection = $model->getOrderCollection( $day_count );
        if($collection->count() == 0) {
            return true;
        }
        $status_allow = array('canceled','complete','closed');
        $delete_ids = array();
        foreach($collection as $data) {
            if(!isset($delete_ids[$data['image_id']]) && in_array($data['status'], $status_allow))
                $delete_ids[$data['image_id']] = 1;
            else
                    $delete_ids[$data['image_id']] = 0;
           /* if(!in_array($data['status'], $status_allow)) {
                $delete_ids[$data['image_id']] = 0;
            } */       
        }
        /*
        if(sizeof($delete_ids) == 0) {
            return true;
        } 
        */  
        foreach($delete_ids as $id => $status) {
            if($status == 0) {
                continue;
            }
            $this->_deleteOrderImage($id);
        }
    }
    
    protected function _deleteOrderImage($id)
    {
        $model = Mage::getModel('aitcg/image');
        $model->load($id);
        $model->deleteData();
    }
    
    public function cronDeleteTempImages() 
    {
        $days = Mage::getStoreConfig('catalog/aitcg/aitcg_cron_order');
        $this->deleteOrderImages($days);
    }
    
    public function salesQuoteRemoveItem( $observer ) {
        $request = Mage::app()->getRequest();
        if ($request->getModuleName() == 'checkout' && $request->getControllerName() == 'cart' && $request->getActionName()=='updateItemOptions') {
            //don't delete data when removing products while updating it's options
            return true;
        }
        $item = $observer->getData('quote_item');        
        /** @var $item Mage_Sales_Model_Quote_Item */
        $cart = $request->getPost('cart');
        if($cart[$item->getId()]['qty'] > 0) {
            //don't delete items that are not deleted from cart
            return true;
        }
        $optionIds = $item->getOptionByCode('option_ids');
        if(!$optionIds) {
            return true;
        }
        foreach (explode(',', $optionIds->getValue()) as $optionId)
        {
            if ($option = $item->getOptionByCode('option_'.$optionId))
            {
                $this->_deleteImageIfIsOrder( $option );
            }
        }
    }
    
    
    protected function _deleteImageIfIsOrder( $option ) 
    {
        /** 
        * @TODO Needs more accurate fix to prevent unserialization except aitoc module options
        * @author ksenevich@aitoc.com
        */
        if ('a:' != substr($option->getData('value'), 0, 2)) 
        {
            return false;
        }
        
        $data = unserialize($option->getData('value'));
        if( isset($data['template_id']) && $data['template_id'] > 0 ) 
        {
                $model = Mage::getModel( 'aitcg/image' );
                $model->load( $data['template_id'] );
                if(!$model->isOrder()) {
                        $model->deleteData();
                }
        }

    }

    public function salesOrderSaveBefore( $observer ) {
        $items = $observer->getData('order')->getItemsCollection();
        foreach($items as $item) 
        {
            $options = $item->getProductOptions();
            if(!isset($options['options']) || sizeof($options['options']) == 0) {
                continue;
            }
            foreach($options['options'] as $option) {
                if ('aitcustomer_image' == $option['option_type'])
                {
                    $this->_generateImageException($option);
                }
            }
        }
    }
    
    protected function _generateImageException( $option ) 
    {
        $data = unserialize($option['option_value']);

        if( isset($data['template_id']) && $data['template_id'] > 0 ) 
        {
                $model = Mage::getModel( 'aitcg/image' );
                $model->load( $data['template_id'] );

                $data = $model->getFullData();
                if(!isset($data['id']) || $data['id'] == 0) {
                        throw new Mage_Core_Exception(print_r($data,1));
                        #throw new Mage_Core_Exception(Mage::helper('aitcg')->__('The preview image was not found, please get back to the shopping cart and check all the required product options'));
                }
                return true;
        }
        return false;
    }
    
    public function salesOrderItemSaveAfter( $observer ) {
        $item = $observer->getData('item');
        $order_id = $item->getData('order_id');
        $options = $item->getProductOptions();
        if(empty($options['options'])) {
            return true;
        }
        foreach($options['options'] as $option) {
            if ('aitcustomer_image' == $option['option_type'])
            {
                $data = unserialize($option['option_value']);
    
                if( isset($data['template_id']) && $data['template_id'] > 0 ) 
                {
                    $model = Mage::getModel( 'aitcg/image' );
                    $model->load( $data['template_id'] );
                    if($model->isOrder()) {                
                        $model->setOrderId($order_id);
                    }
                    $model->save();
                }
            }
        }
    }
    
    public function aitsysBlockAbstractToHtmlAfter( $observer ) {
        $block = $observer->getData('block');
        $module = false;
        if($block->hasData('module_name')) {
            $module = $block->getData('module_name');
        } else {
            $module = $block->getData('rendered_block')->getData('module_name');
        }
        if(!$module || ($module != 'Mage_Sales')) {
            return true;
        }
        $out = Mage::helper('aitcg/options')->replaceAitTemplate( $observer->getTransport()->getHtml() );
        $observer->getTransport()->setHtml($out);
        return 1;
    }

    public function productGridPrepareMassaction($observer)
    {
        $block = $observer->getData('block');
        $block->getMassactionBlock()->addItem('copy_options', array(
            'label'=> Mage::helper('aitcg')->__('Copy CPP options'),
            'url'  => Mage::getUrl('adminhtml/aitcg_options/massCopy'),
            'additional' => array(
                'visibility' => array(
                    'name' => 'copy_from_product_id',
                    'type' => 'text',
                    'class' => 'required-entry validate-greater-than-zero',
                    'label' => Mage::helper('aitcg')->__('From (Product ID):')
                )
            )
        ));
    }

}
