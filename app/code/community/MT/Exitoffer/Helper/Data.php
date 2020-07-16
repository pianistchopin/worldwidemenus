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

class MT_Exitoffer_Helper_Data extends Mage_Core_Helper_Abstract
{

    public $checkoutPageMap = array(
        'onestepcheckout_index_index',
        'onepagecheckout_index_index'
    );

    public function isAjax()
    {
        $request = Mage::app()->getRequest();
        if ($request->isXmlHttpRequest()) {
            return true;
        }
        if ($request->getParam('ajax') || $request->getParam('isAjax')) {
            return true;
        }
        return false;
    }



    public function getTemplateAttachment()
    {
        $fileName = Mage::getStoreConfig(MT_Exitoffer_Model_Subscriber::XML_PATH_EMAIL_ATTACHMENT);
        if ($fileName == '') {
            return false;
        }

        $file = Mage::getBaseDir('media') . '/exitoffer/files/' . $fileName;
        if (!file_exists($file)) {
            return false;
        }

        return $file;
    }

    public function isMobile()
    {
        $isMobile = '0';
        $request = Mage::app()->getRequest();
        $userAgent = $request->getServer('HTTP_USER_AGENT', false);
        $httpAccept = $request->getServer('HTTP_ACCEPT', false);
        $httpXWapProfile = $request->getServer('HTTP_X_WAP_PROFILE', false);
        $httpProfile = $request->getServer('HTTP_PROFILE', false);
        $allHttp = $request->getServer('ALL_HTTP', false);

        if ($userAgent
            && preg_match(
                '/(android|up.browser|up.link|mmp|symbian|smartphone|midp|wap|phone)/i',
                strtolower($userAgent)
            )
        ) {
            $isMobile = 1;
        }

        if ($httpAccept) {
            if ((strpos(strtolower($httpAccept), 'application/vnd.wap.xhtml+xml') !== false)
                || (($httpXWapProfile || $httpProfile))
            ) {
                $isMobile = 1;
            }
        }

        if ($userAgent) {
            $mobileUa = strtolower(substr($userAgent, 0, 4));
            $mobileAgents = array(
                'w3c ','acs-','alav','alca','amoi','andr','audi','avan','benq','bird','blac','blaz','brew',
                'cell','cldc','cmd-','dang','doco','eric','hipt','inno','ipaq','java','jigs','kddi','keji',
                'leno','lg-c','lg-d','lg-g','lge-','maui','maxo','midp','mits','mmef','mobi','mot-','moto',
                'mwbp','nec-','newt','noki','oper','palm','pana','pant','phil','play','port','prox','qwap',
                'sage','sams','sany','sch-','sec-','send','seri','sgh-','shar','sie-','siem','smal','smar',
                'sony','sph-','symb','t-mo','teli','tim-','tosh','tsm-','upg1','upsi','vk-v','voda','wap-',
                'wapa','wapi','wapp','wapr','webc','winw','winw','xda','xda-'
            );

            if (in_array($mobileUa, $mobileAgents)) {
                $isMobile = 1;
            }
        }

        if ($allHttp) {
            if (strpos(strtolower($allHttp), 'OperaMini') !== false) {
                $isMobile = 1;
            }
        }

        if ($userAgent) {
            if (strpos(strtolower($userAgent), 'windows') !== false) {
                $isMobile = 0;
            }
        }

        return $isMobile;
    }

    public function translate($key)
    {
        return Mage::getStoreConfig('exitoffer/translate/'.$key);
    }

    public function log($msg)
    {

    }

    public function getPageId()
    {
        $pageId = '';
        $app = Mage::app();

        if ($app->getFrontController()->getRequest()->getRouteName() == 'cms') {
            $pageId = 'cms_page_' . Mage::getSingleton('cms/page')->getId();
        } else {
            $product = Mage::registry('current_product');
            if ($product && $product->getId()) {
                $pageId = 'product_page';
            } else {
                $category = Mage::registry('current_category');
                if ($category && $category->getId()) {
                    $pageId = 'category_page';
                } else {
                    $request = $app->getRequest();
                    $module = $request->getModuleName();
                    $controller = $request->getControllerName();
                    $action = $request->getActionName();

                    if (($module == 'checkout' && $controller == 'cart' && $action == 'index')
                        || ($controller == 'checkout_cart' && $module = 'ajaxcart')
                    ) {
                        $pageId = 'cart_page';
                    } elseif ((Mage::getURL('checkout/onepage') == Mage::helper('core/url')->getCurrentUrl())
                        || ($module == 'onepage' && $action == 'index')) {
                        $pageId = 'checkout_page';
                    } elseif (in_array($module.'_'.$controller.'_'.$action, $this->checkoutPageMap)) {
                        $pageId = 'checkout_page';
                    }
                }
            }
        }

        return $pageId;
    }

    public function getCurrentPopup()
    {

        $postData = Mage::app()->getRequest()->getPost();

        if (!isset($postData['campaign_id']) || !is_numeric($postData['campaign_id'])) {
            $defaultCampaignId = Mage::getStoreConfig(MT_Exitoffer_Model_Campaign::XML_PATH_DEFAULT_CAMPAIGN_ID);
            if (
                Mage::getStoreConfig(MT_Exitoffer_Model_Campaign::XML_PATH_DEFAULT_SUBSCRIPTION_IS_ACTIVE)
                && !empty($defaultCampaignId)
                && is_numeric($defaultCampaignId)
            ) {
                $campaignId = $defaultCampaignId;
            } else {
                Mage::helper('exitoffer')->log('Missing campaign ID, MT_Exitoffer_Model_Observer:beforeSaveSubscriber');
                return;
            }
        } else {
            $campaignId = $postData['campaign_id'];
        }

        $campaign = Mage::getModel('exitoffer/campaign')->load($campaignId);
        if (!$campaign) {
            Mage::helper('exitoffer')->log('Campaign not exist, MT_Exitoffer_Model_Observer:beforeSaveSubscriber');
            return;
        }

        $popup = $campaign->getPopup();
        if (!$popup) {
            Mage::helper('exitoffer')->log('Popup not assigned for campaign # '.$campaign->getId().', MT_Exitoffer_Model_Observer:beforeSaveSubscriber');
            return;
        }

        return $popup;
    }

}