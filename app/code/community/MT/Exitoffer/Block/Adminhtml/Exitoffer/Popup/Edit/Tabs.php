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

class MT_Exitoffer_Block_Adminhtml_Exitoffer_Popup_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs {

    public function __construct() {
        parent::__construct();
        $this->setId('exitoffer_series_tab');
        $this->setDestElementId('edit_form');
        $this->setTitle(Mage::helper('exitoffer')->__('Exit Intent Popup'));
    }

    protected function _beforeToHtml()
    {



        $popup = Mage::registry('exitoffer_popup_data');

        if (!$popup->getId() && !Mage::app()->getRequest()->getParam('content_type')) {
            $this->addTab('settings', array(
                'label' => Mage::helper('exitoffer')->__('Popup Information'),
                'title' => Mage::helper('exitoffer')->__('Popup Information'),
                'content' => $this->getLayout()->createBlock('exitoffer/adminhtml_exitoffer_popup_edit_tabs_settings')->toHtml(),
            ));
        } else {
            $contentType = '';
            if ($popup && $popup->getContentType() != '') {
                $contentType = $popup->getContentType();
            } elseif (Mage::app()->getRequest()->getParam('content_type') != '') {
                $contentType = Mage::app()->getRequest()->getParam('content_type');
            }

            $this->addTab('general', array(
                'label' => Mage::helper('exitoffer')->__('Popup Information'),
                'title' => Mage::helper('exitoffer')->__('Popup Information'),
                'content' => $this->getLayout()->createBlock('exitoffer/adminhtml_exitoffer_popup_edit_tabs_edit')->toHtml(),
            ));

            if ($contentType == MT_Exitoffer_Model_Popup::CONTENT_TYPE_NEWSLETTER_SUBSCRIPTION_FORM) {


                $this->addTab(MT_Exitoffer_Model_Popup::CONTENT_TYPE_NEWSLETTER_SUBSCRIPTION_FORM, array(
                    'label' => Mage::helper('exitoffer')->__('Newsletter Subscription Form Settings'),
                    'title' => Mage::helper('exitoffer')->__('Newsletter Subscription Form Settings'),
                    'active' => (Mage::app()->getRequest()->getParam('tab') == MT_Exitoffer_Model_Popup::CONTENT_TYPE_NEWSLETTER_SUBSCRIPTION_FORM) ? true : false,
                    'content' => $this->getLayout()->createBlock('exitoffer/adminhtml_exitoffer_popup_edit_tabs_nsf')->toHtml()
                ));

                $this->addTab('coupon', array(
                    'label' => Mage::helper('exitoffer')->__('Discount Coupon'),
                    'title' => Mage::helper('exitoffer')->__('Discount Coupon'),
                    'active' => (Mage::app()->getRequest()->getParam('tab') == 'coupon') ? true : false,
                    'content' => $this->getLayout()->createBlock('exitoffer/adminhtml_exitoffer_popup_edit_tabs_coupon')->toHtml()
                ));

                $this->addTab('theme', array(
                    'label' => Mage::helper('exitoffer')->__('Theme'),
                    'title' => Mage::helper('exitoffer')->__('Theme'),
                    'active' => (Mage::app()->getRequest()->getParam('tab') == 'theme') ? true : false,
                    'content' => $this->getLayout()->createBlock('exitoffer/adminhtml_exitoffer_popup_edit_tabs_theme')->toHtml()
                ));

                if ($popup->getId()) {
                    $this->addTab('additional_fields', array(
                        'label' => Mage::helper('exitoffer')->__('Additional Fields'),
                        'title' => Mage::helper('exitoffer')->__('Additional Fields'),
                        'active' => (Mage::app()->getRequest()->getParam('tab') == 'additional_fields') ? true : false,
                        'content' => $this->getLayout()->createBlock('exitoffer/adminhtml_exitoffer_popup_edit_tabs_fields_grid')->toHtml() . '<p><br/></p>' .
                            $this->getLayout()->createBlock('exitoffer/adminhtml_exitoffer_popup_edit_tabs_fields_form')->toHtml()

                    ));
                }

            }

            if ($contentType == MT_Exitoffer_Model_Popup::CONTENT_TYPE_STATIC_CMS_BLOCK) {
                $this->addTab('theme', array(
                    'label' => Mage::helper('exitoffer')->__('Static block settings'),
                    'title' => Mage::helper('exitoffer')->__('Static block settings'),
                    'active' => (Mage::app()->getRequest()->getParam('tab') == 'cms') ? true : false,
                    'content' => $this->getLayout()->createBlock('exitoffer/adminhtml_exitoffer_popup_edit_tabs_cms')->toHtml()
                ));
            }

            if ($contentType == MT_Exitoffer_Model_Popup::CONTENT_TYPE_YES_NO) {
                $this->addTab('yesno', array(
                    'label' => Mage::helper('exitoffer')->__('Buttons settings'),
                    'title' => Mage::helper('exitoffer')->__('Buttons settings'),
                    'active' => (Mage::app()->getRequest()->getParam('tab') == 'yesno') ? true : false,
                    'content' => $this->getLayout()->createBlock('exitoffer/adminhtml_exitoffer_popup_edit_tabs_yesno')->toHtml()
                ));

                $this->addTab('coupon', array(
                    'label' => Mage::helper('exitoffer')->__('Discount Coupon'),
                    'title' => Mage::helper('exitoffer')->__('Discount Coupon'),
                    'active' => (Mage::app()->getRequest()->getParam('tab') == 'coupon') ? true : false,
                    'content' => $this->getLayout()->createBlock('exitoffer/adminhtml_exitoffer_popup_edit_tabs_coupon')->toHtml()
                ));

                $this->addTab('theme', array(
                    'label' => Mage::helper('exitoffer')->__('Theme'),
                    'title' => Mage::helper('exitoffer')->__('Theme'),
                    'active' => (Mage::app()->getRequest()->getParam('tab') == 'theme') ? true : false,
                    'content' => $this->getLayout()->createBlock('exitoffer/adminhtml_exitoffer_popup_edit_tabs_theme')->toHtml()
                ));
            }


            if ($contentType == MT_Exitoffer_Model_Popup::CONTENT_TYPE_CONTACT_FORM) {
                $this->addTab('contact', array(
                    'label' => Mage::helper('exitoffer')->__('Contact Popup Settings'),
                    'title' => Mage::helper('exitoffer')->__('Contact Popup Settings'),
                    'active' => (Mage::app()->getRequest()->getParam('tab') == 'contact') ? true : false,
                    'content' => $this->getLayout()->createBlock('exitoffer/adminhtml_exitoffer_popup_edit_tabs_contact')->toHtml()
                ));

                $this->addTab('theme', array(
                    'label' => Mage::helper('exitoffer')->__('Theme'),
                    'title' => Mage::helper('exitoffer')->__('Theme'),
                    'active' => (Mage::app()->getRequest()->getParam('tab') == 'theme') ? true : false,
                    'content' => $this->getLayout()->createBlock('exitoffer/adminhtml_exitoffer_popup_edit_tabs_theme')->toHtml()
                ));

                if ($popup->getId()) {
                    $this->addTab('additional_fields', array(
                        'label' => Mage::helper('exitoffer')->__('Form Fields'),
                        'title' => Mage::helper('exitoffer')->__('Form Fields'),
                        'active' => (Mage::app()->getRequest()->getParam('tab') == 'additional_fields') ? true : false,
                        'content' => $this->getLayout()->createBlock('exitoffer/adminhtml_exitoffer_popup_edit_tabs_fields_grid')->toHtml() . '<p><br/></p>' .
                            $this->getLayout()->createBlock('exitoffer/adminhtml_exitoffer_popup_edit_tabs_fields_form')->toHtml()

                    ));
                }
            }
        }
        return parent::_beforeToHtml();
    }
}