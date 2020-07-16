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

class MT_Exitoffer_Block_Popup extends Mage_Core_Block_Template
{
    private $__popup = null;

    private $__campaign = null;

    public function getPopup()
    {
        if ($this->__popup === null) {
            $this->__popup = $this->getCampaign()->getPopup();
        }
        return $this->__popup;
    }

    public function getCampaign()
    {
        if ($this->__campaign  === null) {
            $campaignModel = Mage::getSingleton('exitoffer/campaign');
            $this->__campaign = $campaignModel->getCurrent();
        }
        return $this->__campaign ;
    }


    public function getCookieLifeTime()
    {
        if (Mage::getStoreConfig('exitoffer/general/dev'))
            return 0.00001;
        $lifeTime = $this->getCampaign()->getCookieLifetime();
        if ($lifeTime == 0) {
            $lifeTime = 100*365;
        }

        return $lifeTime;
    }

    public function isActivePopUp()
    {
        if (Mage::getStoreConfig('exitoffer/general/is_active') != 1) {
           return false;
        }

        $campaign = Mage::getSingleton('exitoffer/campaign');
        if (!$campaign->getCurrent()) {
            return false;
        }

        return true;
    }

    public function getTheme()
    {
        $theme = $this->getPopup()->getTheme();
        return $theme;
    }

    public function getText()
    {
        return Mage::getStoreConfig('exitoffer/popup/message');
    }

    public function getPopupContentHtml()
    {
        return $this->getChildHtml('popup_theme_'.$this->getTheme());
    }

    public function getConfig()
    {
        $campaign = $this->getCampaign();
        $config = array(
            'actionUrl' => $this->getActionUrl(),
            'translate' => Mage::getStoreConfig('exitoffer/translate'),
            'layerClose' => $campaign->getLayerClose(),
            'showInLast' => $campaign->getShowInLastTab(),
            'cookieName' => $campaign->getCookieName(),
            'cookieLifeTime' => $this->getCookieLifeTime(),
            'campaignId' => $campaign->getId(),
            'isMobile' => $this->getIsMobile(),
            'mobileTrigger' => $campaign->getMobileTrigger(),
            'showOnLoadDelay' => $campaign->getShowOnLoadDelay(),
            'showOnMobile' => $campaign->getShowOnMobile(),
            'showOnLoad' => $campaign->getShowOnLoad(),
            'showOnLoadCookieName' => $campaign->getCookieName().'_sol',
        );

        return $config;
    }

    public function getActionUrl()
    {
        $url = '';
        $popup = $this->getPopup();
        if ($popup->getContentType() == MT_Exitoffer_Model_Popup::CONTENT_TYPE_NEWSLETTER_SUBSCRIPTION_FORM) {
            $url = Mage::getUrl('exitoffer/subscriber/new/');
        }

        if ($popup->getContentType() == MT_Exitoffer_Model_Popup::CONTENT_TYPE_YES_NO) {
            $url = Mage::getUrl('exitoffer/index/coupon/');
        }

        if ($popup->getContentType() == MT_Exitoffer_Model_Popup::CONTENT_TYPE_CONTACT_FORM) {
            $url = Mage::getUrl('exitoffer/index/contact/');
        }

        return str_replace(array('http:', 'https:'), '', $url);
    }

    public function getConfigJs()
    {
        return json_encode($this->getConfig());
    }

    public function getContentType()
    {
        return $this->getPopup()->getContentType();
    }

    public function getShowInLast()
    {
        return Mage::getStoreConfig('exitoffer/popup/showinlast');
    }

    public function getStaticBlockHtml()
    {
        $id = $this->getPopup()->getStaticBlockId();
        if (!is_numeric($id)) {
            Mage::helper('exitoffer')->log('Static block is not assigned to popup. MT_Exitoffer_Block_Popup::getStaticBlockHtml');
            return '';
        }

        return Mage::app()->getLayout()->createBlock('cms/block')->setBlockId($id)->toHtml();
    }


    public function getIsMobile()
    {
        return Mage::helper('exitoffer')->isMobile();
    }
}

