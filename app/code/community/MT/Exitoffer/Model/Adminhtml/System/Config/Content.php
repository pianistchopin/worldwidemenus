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

class MT_Exitoffer_Model_Adminhtml_System_Config_Content
{

    public function toOptionArray()
    {
        return array(
            array(
                'value' => MT_Exitoffer_Model_Popup::CONTENT_TYPE_NEWSLETTER_SUBSCRIPTION_FORM,
                'label' => Mage::helper('adminhtml')->__('Newsletter Subscription Form')
            ),
            array(
                'value' => MT_Exitoffer_Model_Popup::CONTENT_TYPE_STATIC_CMS_BLOCK,
                'label' => Mage::helper('adminhtml')->__('Static CMS Block')
            ),
            array(
                'value' => MT_Exitoffer_Model_Popup::CONTENT_TYPE_YES_NO,
                'label' => Mage::helper('adminhtml')->__('YES/NO buttons')
            ),
            array(
                'value' => MT_Exitoffer_Model_Popup::CONTENT_TYPE_CONTACT_FORM,
                'label'=>Mage::helper('adminhtml')->__('Contact Form')),
        );
    }

    public function toValueArray()
    {
        $data = array();
        $options = $this->toOptionArray();
        foreach ($options as $option) {
            $data[$option['value']] = $option['label'];
        }

        return $data;
    }
}
