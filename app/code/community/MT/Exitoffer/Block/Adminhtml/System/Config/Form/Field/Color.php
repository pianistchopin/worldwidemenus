<?php
/**
 * MB "Vienas bitas" (Magetrend.com)
 *
 * PHP version 5.2 or later
 *
 * @category MageTrend
 * @package  MT/Exitoffer
 * @author   Edvinas Stulpinas <edwin@magetrend.com>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     https://www.magetrend.com/exit-intent-popup
 */

class MT_Exitoffer_Block_Adminhtml_System_Config_Form_Field_Color extends Mage_Adminhtml_Block_System_Config_Form_Field {

    protected function _getElementHtml( Varien_Data_Form_Element_Abstract $element ) {
        $color = new Varien_Data_Form_Element_Text();
        $data = array(
            'name'      => $element->getName(),
            'html_id'   => $element->getId(),
        );
        $color->setData($data);
        $color->setValue($element->getValue());
        $color->setForm( $element->getForm() );
        $color->addClass($element->getClass() . ' color {required:false, adjust:false, hash:true}' );
        return $color->getElementHtml();
    }

}
