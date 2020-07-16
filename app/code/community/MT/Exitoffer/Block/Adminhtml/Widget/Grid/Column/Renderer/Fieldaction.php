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

class MT_Exitoffer_Block_Adminhtml_Widget_Grid_Column_Renderer_Fieldaction
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Text
{
    /**
     * Render a grid cell as options
     *
     * @param Varien_Object $row
     * @return string
     */
    public function render(Varien_Object $row)
    {
        $helper = Mage::helper('exitoffer');
        $deleteUrl = Mage::helper('adminhtml')->getUrl('adminhtml/exitoffer_popup/deleteFieldAjax/');
        return '<a href="javascript:ExitOfferPopup.editField('.$value = $row->getId().', \''.base64_encode(json_encode($row->getData())).'\')">'.$helper->__('Edit').'</a>'. ' | ' .'<a href="javascript:ExitOfferPopup.deleteField('.$value = $row->getId().', '."'".$deleteUrl."'".', '."'exitofferpopup_list_gridJsObject'".')">'.$helper->__('Delete').'</a>';
    }


}
