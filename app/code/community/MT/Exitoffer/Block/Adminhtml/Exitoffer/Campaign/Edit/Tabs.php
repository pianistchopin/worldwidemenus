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

class MT_Exitoffer_Block_Adminhtml_Exitoffer_Campaign_Edit_Tabs
    extends Mage_Adminhtml_Block_Widget_Tabs {

    public function __construct() {
        parent::__construct();
        $this->setId('exitoffer_series_tab');
        $this->setDestElementId('edit_form');
        $this->setTitle(Mage::helper('exitoffer')->__('Exit Intent Campaign'));
    }

    protected function _beforeToHtml()
    {
        $campaign = Mage::helper('exitoffer/adminhtml')->getCurrentCampaign();

        $this->addTab('general', array(
            'label' => Mage::helper('exitoffer')->__('Campaign Information'),
            'title' => Mage::helper('exitoffer')->__('Campaign Information'),
            'content' => $this->getLayout()->createBlock('exitoffer/adminhtml_exitoffer_campaign_edit_tabs_general')->toHtml(),
        ));

   //     if ($campaign->getId()) {
            $this->addTab('popup', array(
                'label' => Mage::helper('exitoffer')->__('Popup Settings'),
                'title' => Mage::helper('exitoffer')->__('Popup Settings'),
                'content' => $this->getLayout()->createBlock('exitoffer/adminhtml_exitoffer_campaign_edit_tabs_popup')->toHtml(),
            ));

        $this->addTab('condition', array(
                'label' => Mage::helper('exitoffer')->__('Conditions'),
                'title' => Mage::helper('exitoffer')->__('Conditions'),
                'content' => $this->getLayout()->createBlock('exitoffer/adminhtml_exitoffer_campaign_edit_tabs_condition')->toHtml(),
            ));
   //     }

/*
                if ($campaign->getId()) {
                    $this->addTab('additional_fields', array(
                        'label' => Mage::helper('exitoffer')->__('Additional Fields'),
                        'title' => Mage::helper('exitoffer')->__('Additional Fields'),
                        'active' => (Mage::app()->getRequest()->getParam('tab') == 'additional_fields')?true:false,
                        'content' => $this->getLayout()->createBlock('exitoffer/adminhtml_exitoffer_campaign_edit_tabs_fields_grid')->toHtml().'<p><br/></p>'.
                            $this->getLayout()->createBlock('exitoffer/adminhtml_exitoffer_campaign_edit_tabs_fields_form')->toHtml()

                    ));
                }

*/
        return parent::_beforeToHtml();
    }
}