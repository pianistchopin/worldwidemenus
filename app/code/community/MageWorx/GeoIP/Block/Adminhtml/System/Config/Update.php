<?php
/**
 * MageWorx
 * GeoIP Extension
 *
 * @category   MageWorx
 * @package    MageWorx_GeoIP
 * @copyright  Copyright (c) 2015 MageWorx (http://www.mageworx.com/)
 */

class MageWorx_GeoIP_Block_Adminhtml_System_Config_Update extends Mage_Adminhtml_Block_System_Config_Form_Field
{
    /**
     * Adds update button to config field
     *
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        $this->setElement($element);
        return $this->_getAddRowButtonHtml();
    }

    /**
     * Returns update button html
     *
     * @param string $sku
     * @return mixed
     */
    protected function _getAddRowButtonHtml()
    {
        $url = Mage::helper('adminhtml')->getUrl('adminhtml/mageworx_geoip_database/update/');
        $buttonHtml = $this->getLayout()->createBlock('adminhtml/widget_button')
            ->setType('button')
            ->setLabel($this->__('Update Database'))
            ->setOnClick("startUpdate()")
            ->toHtml();

        $backupHtml = 'Create backup <input type="checkbox" id="mageworx_geoip_update_backup" name="backup" value="1" checked="checked">&nbsp;&nbsp;&nbsp;';
        $js = '<script type="text/javascript">
        function startUpdate(){
            url = "' . $url . '";
            backup = $("mageworx_geoip_update_backup");
            if(backup.checked){
                url = url.replace("/update/", "/update/backup/1/");
            }
            window.open(url);
        }
        </script>';

        $lastUpdate = "<br>" . Mage::helper('mageworx_geoip')->__('Last update') . ": ";
        if (Mage::helper('mageworx_geoip')->getLastUpdateTime()) {
            $lastUpdate .= Mage::helper('mageworx_geoip')->getLastUpdateTime();
        } else {
            $lastUpdate .= 'n/a';
        }

        return $backupHtml . $buttonHtml . $js . $lastUpdate;
    }

}