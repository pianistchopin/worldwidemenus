<?php
class Aitoc_Aitcg_Model_System_Config_Source_Editor_Opacity
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array('value' => '1.0',   'label'=>'100%'),
            array('value' => '0.9',   'label'=>'90%'),
            array('value' => '0.8',   'label'=>'80%'),
            array('value' => '0.7',   'label'=>'70%'),
            array('value' => '0.6',   'label'=>'60%'),
            array('value' => '0.5',   'label'=>'50%'),
            array('value' => '0.4',   'label'=>'40%'),
            array('value' => '0.3',   'label'=>'30%'),
            array('value' => '0.2',   'label'=>'20%'),
            array('value' => '0.1',   'label'=>'10%'),
            array('value' => '0.0',   'label'=>Mage::helper('aitcg')->__('Invisible')),
        );
    }
}
