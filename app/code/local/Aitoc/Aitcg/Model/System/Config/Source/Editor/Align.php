<?php
class Aitoc_Aitcg_Model_System_Config_Source_Editor_Align
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array('value' => 'center',  'label'=>'Center'),
            array('value' => 'left',    'label'=>'Left'),
            array('value' => 'right',   'label'=>'Right'),
            array('value' => 'justify', 'label'=>'Stretch')
        );
    }
}
