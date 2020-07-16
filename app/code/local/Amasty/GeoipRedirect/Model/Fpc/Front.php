<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_GeoipRedirect
 */


class Amasty_GeoipRedirect_Model_Fpc_Front extends Varien_Object
{
    protected $redirectAllowed = false;

    public function extractContent($content)
    {
        $ambaseHelper = new Amasty_Base_Helper_Utils();
        $helper = new Amasty_GeoipRedirect_Helper_Data();
        /** @var Zend_Controller_Request_Http $request */
        $request = new Zend_Controller_Request_Http();

        if ($this->isAdmin()) {
            return;
        }

        if (strpos($request->getRequestUri(), '/api/') !== false
            || !$this->isModuleEnabled('Amasty_Geoip')
        ) {
            return;
        }


        $ipRestriction = $this->getDbConfig('amgeoipredirect/restriction/ip_restriction');
        $currentIp = $helper->getIpAddress();
        if (!empty($ipRestriction)) {
            $ipRestriction = array_map("rtrim", explode(PHP_EOL, $ipRestriction));
            foreach ($ipRestriction as $ip) {
                if ($currentIp && $currentIp == $ip) {
                    return;
                }
            }
        }

        $userAgent = $request->getHeader('USER_AGENT');
        $userAgentsIgnore = $this->getDbConfig('amgeoipredirect/restriction/user_agents_ignore');
        if (!empty($userAgentsIgnore)) {
            $userAgentsIgnore = explode(',', $userAgentsIgnore);
            $userAgentsIgnore = array_map("trim", $userAgentsIgnore);
            foreach ($userAgentsIgnore as $agent) {
                if ($userAgent && $agent && stripos($userAgent, $agent) !== false) {
                    return;
                }
            }
        }

        $cookie = Mage::getSingleton('core/cookie');

        $currentStoreId = $this->getStoreId($request);
        if (empty($currentStoreId)) {
            return;
        }

        $cookiePath = $this->getDbConfig('web/cookie/cookie_path', $currentStoreId);
        if (empty($cookiePath)) {
            $cookiePath = Mage::app()->getRequest()->getBasePath();
        }
        $cookieDomain = $this->getDbConfig('web/cookie/cookie_domain', $currentStoreId);
        if (empty($cookieDomain)) {
            $cookieDomain = Mage::app()->getRequest()->getHttpHost();
        }
        $cookieExpire = $this->getDbConfig('web/cookie/cookie_lifetime', $currentStoreId);
        if (!is_numeric($cookieExpire)) {
            $cookieExpire = 3600;
        }
        $cookieExpire = time() + $cookieExpire;
        $cookieHttpOnly = $this->getDbConfig('web/cookie/cookie_httponly', $currentStoreId);
        $cookieIsSecure = Mage::app()->getRequest()->isSecure();

        $currentWebsiteId = $this->getWebsiteId($currentStoreId);
        $addToUrl = $this->applyLogic($request);
        $currentPageUrl = isset($_COOKIE['am_current_store_url']) ? $_COOKIE['am_current_store_url'] : $request->getHttpHost()
            . $request->getBaseUrl()
        ;

        if ($this->getDbConfig('amgeoipredirect/general/enable', $currentStoreId) && $this->redirectAllowed) {
            $ip = $helper->getIpAddress();
            $location = $this->locate($ip);
            $country = $location->getCountry();

            if ($this->getDbConfig('amgeoipredirect/restriction/first_visit_redirect')) {
                $getAmYetRedirectStore = $cookie->get('AmYetRedirectStore');
                $getAmYetRedirectCurrency = $cookie->get('AmYetRedirectCurrency');
                $getAmYetRedirectUrl = $cookie->get('AmYetRedirectUrl');
            } else {
                $getAmYetRedirectStore = 0;
                $getAmYetRedirectCurrency = 0;
                $getAmYetRedirectUrl = 0;
            }

            if (!$getAmYetRedirectUrl && $this->getDbConfig('amgeoipredirect/country_url/enable_url')) {
                $urlMapping = $helper->unserialize($this->getDbConfig('amgeoipredirect/country_url/url_mapping', $currentStoreId));
                $currentUrl = $currentPageUrl . $addToUrl;
                foreach ($urlMapping as $value) {
                    if (!isset($value['country_url'])) {
                        continue;
                    }
                    if (is_array($value['country_url'])) {
                        $checkCountry = in_array($country, $value['country_url']);
                    } else {
                        $checkCountry = $value['country_url'] == $country;
                    }
                    if ($checkCountry && $value['url_mapping'] != $currentUrl) {
                        setcookie('AmYetRedirectUrl', 1, $cookieExpire, $cookiePath, $cookieDomain,
                            $cookieIsSecure, $cookieHttpOnly);
                        Mage::app()->getResponse()->setRedirect($value['url_mapping']);
                        Mage::app()->getResponse()->sendResponse();
                        $ambaseHelper->_exit();
                    }
                }
            }
            if (!$getAmYetRedirectStore && $this->getDbConfig('amgeoipredirect/country_store/enable_store')) {
                $allStores = $this->getAllStores();
                foreach ($allStores as $store) {
                    $storeCode = $store['code'];
                    if (strpos($addToUrl, '/' . $storeCode) !== false) {
                        $addToUrl = substr($addToUrl, strlen($storeCode) + 1);
                    }
                }
                foreach ($allStores as $store) {
                    $storeId = $store['store_id'];
                    $currentStoreUrl = $store['value_store'] ? $store['value_store'] : ($store['value_website'] ? $store['value_website'] : $store['value_default']);

                    $countries = $this->getDbConfig('amgeoipredirect/country_store/affected_countries', $storeId);
                    if (!$this->getDbConfig('amgeoipredirect/restriction/redirect_between_websites')) {
                        $useMultistores = $store['website_id'] == $currentWebsiteId;
                    } else {
                        $useMultistores = true;
                    }
                    if ($country && $countries && strpos($countries, $country) !== false
                        && $useMultistores
                    ) {
                        $storeCode = $store['code'];
                        $urlConfigPath = 'web/unsecure/base_link_url';
                        if ($this->getDbConfig('web/secure/use_in_frontend', $storeId)) {
                            $urlConfigPath = 'web/secure/base_link_url';
                        }

                        $redirectStoreUrl = $this->getDbConfig($urlConfigPath, $storeId);
                        if ($storeId != $currentStoreId) {
                            $redirectStoreUrl .= $this->addStoreCodeToUrl($storeCode, $addToUrl);
                            setcookie(
                                'AmYetRedirectStore', 1, $cookieExpire, $cookiePath,
                                $cookieDomain, $cookieIsSecure, $cookieHttpOnly);
                            setcookie('am_current_store_url', trim($currentStoreUrl, '/'), $cookieExpire,
                                $cookiePath, $cookieDomain, $cookieIsSecure, $cookieHttpOnly);
                            Mage::app()->getResponse()->setRedirect($redirectStoreUrl);
                            Mage::app()->getResponse()->sendResponse();
                            $ambaseHelper->_exit();
                        }
                    }
                }
            }
            if (!$getAmYetRedirectCurrency && $this->getDbConfig('amgeoipredirect/country_currency/enable_currency')) {
                $currencyMapping = $helper->unserialize($this->getDbConfig('amgeoipredirect/country_currency/currency_mapping', $currentStoreId));
                foreach ($currencyMapping as $value) {
                    if (is_array($value['country_currency'])) {
                        $checkCountry = in_array($country, $value['country_currency']);
                    } else {
                        $checkCountry = $value['country_currency'] == $country;
                    }

                    $currentCurrencyCode = $cookie->get('currency') ? $cookie->get('currency') : $this->getDbConfig('currency/options/default', $currentStoreId);
                    if ($checkCountry && $currentCurrencyCode != $value['currency']) {
                        setcookie('AmYetRedirectCurrency', 1, $cookieExpire, $cookiePath,
                            $cookieDomain, $cookieIsSecure, $cookieHttpOnly);
                        setcookie('currency', $value['currency'], $cookieExpire, $cookiePath,
                            $cookieDomain, $cookieIsSecure, $cookieHttpOnly);
                        $code = $this->getStoreCodeById($currentStoreId);

                        $secure = !empty($_SERVER['HTTPS']) && ('off' != $_SERVER['HTTPS']);
                        $http = 'http://';
                        $https = 'https://';
                        $pathInfo = trim(strtok($currentPageUrl, '?'), '/');
                        if (!$secure) {
                            if (strpos($pathInfo, $http) !== 0) {
                                $pathInfo = $http . $pathInfo;
                            }
                        } else {
                            if (strpos($pathInfo, $https) !== 0) {
                                $pathInfo = $https . $pathInfo;
                            }
                        }

                        $pathInfoForUenc = Mage::helper('core/url')->getCurrentUrl();
                        $uenc = strtr(base64_encode($pathInfoForUenc), '+/=', '-_,');
                        $addToUrl = '/directory/currency/switch/currency/' . $value['currency'] . '/uenc/' . $uenc;

                        $redirectCurrency = $pathInfo . $this->addStoreCodeToUrl($code, $addToUrl);

                        Mage::app()->getResponse()->setRedirect($redirectCurrency);
                        Mage::app()->getResponse()->sendResponse();
                        $ambaseHelper->_exit();
                    }
                }
            }
        }
        return $content;
    }

    protected function addStoreCodeToUrl($code, $url)
    {
        $result = $url;
        if ($this->getDbConfig('web/url/use_store')) {
            $result = '/' . $code . $url;
        } else {
            if (strpos($url, '?') !== false) {
                $result .= '&___store=' . $code;
            } else {
                $result .= '?___store=' . $code;
            }
        }

        return $result;
    }

    protected function isAdmin()
    {
        if (preg_match('|/key/\w{32,}/|', $_SERVER['REQUEST_URI']))
            return true;

        if (FALSE !== strpos($_SERVER['REQUEST_URI'], "/adminhtml_"))
            return true;

        $config = Mage::app()->getConfig();

        $adminKey = (string)$config->getNode('admin/routers/adminhtml/args/frontName');

        if (FALSE !== strpos($_SERVER['REQUEST_URI'], "/$adminKey"))
            return true;
    }

    protected function getAllStores()
    {
        $resource = Mage::getSingleton('core/resource');
        $adapter = $resource->getConnection('core_read');

        $secure = !empty($_SERVER['HTTPS']) && ('off' != $_SERVER['HTTPS']);
        if (!$secure) {
            $path = 'web/unsecure/base_url';
        } else {
            $path = 'web/secure/base_url';
        }
        $configDataTable = $resource->getTableName('core/config_data');

        $select = $adapter->select()
            ->from(array('store' => $resource->getTableName('core/store')), array('store_id', 'website_id', 'code'))
            ->joinLeft(
                array('config_d' => $configDataTable),
                "config_d.scope_id = 0 and config_d.scope = 'default' and config_d.path = '$path'",
                array('value_default' => 'config_d.value')
            )
            ->joinLeft(
                array('config_w' => $configDataTable),
                "config_w.scope_id = store.website_id and config_w.scope = 'websites' and config_w.path = '$path'",
                array('value_website' => 'config_w.value')
            )
            ->joinLeft(
                array('config_s' => $configDataTable),
                "config_s.scope_id = store.store_id and config_s.scope = 'stores' and config_s.path = '$path'",
                array('value_store' => 'config_s.value')
            )
            ->where("store.store_id != 0")
        ;

        $configData = $adapter->fetchAll($select);

        return $configData;

    }

    public function locate($ip)
    {
//        GeoipRedirect Module uses its own "locate" method instead of "locate" method declared in Geoip module
//        because it is being processed before store is defined and check in Geoip requires store
//        $ip = '213.184.225.37';//Minsk
        if ($this->getDbConfig('amgeoip/import/location') && $this->getDbConfig('amgeoip/import/block')) {
            $longIP = ip2long($ip);

            if (!empty($longIP)) {
                if ($longIP < 0) {
                    $longIP = sprintf('%u', $longIP);
                }
                $db = Mage::getSingleton('core/resource')->getConnection('core_read');
                $blockSelect = $db->select()
                    ->from(Mage::getSingleton('core/resource')->getTableName('amgeoip/block'))
                    ->reset(Zend_Db_Select::COLUMNS)
                    ->columns(array('geoip_loc_id'))
                    ->where('start_ip_num < ?', $longIP)
                    ->order('start_ip_num DESC')
                    ->limit(1);

                $select = $db->select()
                    ->from(array('b' => $blockSelect))
                    ->joinInner(
                        array('l' => Mage::getSingleton('core/resource')->getTableName('amgeoip/location')),
                        'l.geoip_loc_id = b.geoip_loc_id',
                        null
                    )
                    ->reset(Zend_Db_Select::COLUMNS)
                    ->columns(array('l.*'));

                if ($res = $db->fetchRow($select))
                    $this->setData($res);
            }
        }

        return $this;
    }

    public function isModuleEnabled($module)
    {
        $fileConfig = new Mage_Core_Model_Config_Base();
        $fileConfig->loadFile(Mage::getBaseDir('etc') . DS . 'modules' . DS . $module . '.xml');

        $isActive = $fileConfig->getNode('modules/' . $module . '/active');

        if (!$isActive || !in_array((string)$isActive, array('true', '1'))) {
            return false;
        }

        return true;
    }

    protected function getWebsiteId($storeId)
    {
        $resource = Mage::getSingleton('core/resource');
        $adapter = $resource->getConnection('core_read');
        $select = $adapter->select()
            ->from(array($resource->getTableName('core/store')), array('website_id'))
            ->where("store_id = $storeId")
        ;
        return $adapter->fetchOne($select);
    }

    public function getStoreId($request)
    {
        if ($this->getDbConfig('amgeoipredirect/restriction/first_visit_redirect')) {
            $store = isset($_COOKIE['store']) ? $_COOKIE['store'] : false;
        } else {
            $store = false;
        }

        $store = $this->getStoreFromUrl($request, $store);

        if ($store == false) {
            $resource = Mage::getSingleton('core/resource');
            $adapter = $resource->getConnection('core_read');

            $path = $request->getHttpHost();
/*            $pathInfo = explode('//', $path);

            if (isset($pathInfo[1])) {
                $path = $pathInfo[1];
            } else if (isset($pathInfo[0])) {
                $path = $pathInfo[0];
            }*/

            $pathUnsecure = $adapter->quote('http://' . $path . $request->getRequestUri());
            $pathSecure = $adapter->quote('https://' . $path . $request->getRequestUri());

            $select = $adapter->select()
                ->from(array('e' => $resource->getTableName('core/config_data')), array('scope', 'scope_id','path', 'value'))
                ->where("path LIKE 'web/unsecure/base_url' OR  path LIKE 'web/secure/base_url' ")
                ->order("CHAR_LENGTH(value) DESC")
            ;

            $urls = $adapter->fetchAll($select);

            foreach ($urls as $url) {
                if (((strpos($pathSecure, $url['value']) !== false) && ($url['path'] === 'web/secure/base_url'))
                    || (strpos($pathUnsecure, $url['value']) !== false) && ($url['path'] === 'web/unsecure/base_url')
                ) {
                    $configData[] = $url;
                }
            }

            if (empty($configData)) {
                $select = $adapter->select()
                    ->from(array('e' => $resource->getTableName('core/config_data')), array('scope', 'scope_id'))
                    ->where("value LIKE '%$path%' and path LIKE 'web/unsecure/base_url'")
                ;
                $configData = $adapter->fetchAll($select);
            }

            foreach ($configData as $data) {
                if (array_key_exists('scope', $data) && array_key_exists('scope_id', $data)) {
                    if ($data['scope'] == 'stores') {
                        $store = $data['scope_id'];
                    } else {
                        $where = 'e.is_default = 1';
                        if ($data['scope'] == 'default') {
                            $where = 'e.is_default = 1';
                        } elseif ($data['scope'] == 'websites') {
                            $where = 'e.website_id =' . $data['scope_id'];
                        }
                        $select = $adapter->select()
                            ->from(array('e' => $resource->getTableName('core/website')), array())
                            ->join(
                                array('group' => $resource->getTableName('core/store_group')),
                                'group.group_id = e.default_group_id',
                                array('default_store_id')
                            )
                            ->where("$where")
                        ;

                        $store = $adapter->fetchOne($select);
                    }
                }

                if ($store) {
                    break;
                }
            }


        }

        return $store;
    }

    protected function getStoreFromUrl($request, $code = false)
    {
        $store = false;

        if (isset(${'_GET'}['___store'])) {
            $code = ${'_GET'}['___store'];
        } else {
            $pathInfo = trim(strtok($request->getRequestUri(), '?'), '/');

            if ($this->getDbConfig('web/url/use_store')) {
                $pathParts = explode('/', $pathInfo);
                $code = array_shift($pathParts);
            }
        }

        if (isset($code) && $code) {
            $resource = Mage::getSingleton('core/resource');
            $adapter = $resource->getConnection('core_read');
            $select = $adapter->select()
                ->from(array('store' => $resource->getTableName('core/store')), array('store.store_id'))
                ->joinLeft(
                    array('group' => $resource->getTableName('core/store_group')),
                    'group.group_id = store.group_id AND group.default_store_id = store.store_id',
                    array()
                )
                ->where('store.code = ?', $code)
            ;

            $store = $adapter->fetchOne($select);
        }

        return $store;
    }

    protected function getStoreCodeById($storeId)
    {
        $resource = Mage::getSingleton('core/resource');
        $adapter = $resource->getConnection('core_read');
        $select = $adapter->select()
            ->from(array('store' => $resource->getTableName('core/store')), array('store.code'))
            ->where('store.store_id = ?', $storeId)
        ;

        return $adapter->fetchOne($select);
    }


    public function getDbConfig($path, $scopeId = 0, $default = null)
    {
        $resource = Mage::getSingleton('core/resource');
        $adapter = $resource->getConnection('core_read');

        $scopeId = (int)$scopeId;
        $websiteId = (int)$this->getWebsiteId($scopeId);
        $select = $adapter->select()
            ->from(array('e' => $resource->getTableName('core/config_data')), array('value'))
            ->joinLeft(
                array('store' => $resource->getTableName('core/store')),
                'store.store_id = e.scope_id',
                array()
            )
            ->where('path = ?', $path)
            ->where("(scope_id = $websiteId AND scope = 'websites') 
                OR (scope_id = $scopeId AND scope = 'stores') OR scope = 'default'"
            )
            ->order("FIELD(scope,'stores','websites','default')");

        $value = $adapter->fetchOne($select);

        if (is_string($value) && strpos($value, '{{') !== false) {
            if (strpos($value, '{{unsecure_base_url}}') !== false) {
                $unsecureBaseUrl = $this->getDbConfig(Mage_Core_Model_Store::XML_PATH_UNSECURE_BASE_URL);
                $value = str_replace('{{unsecure_base_url}}', $unsecureBaseUrl, $value);
            } elseif (strpos($value, '{{secure_base_url}}') !== false) {
                $secureBaseUrl = $this->getDbConfig(Mage_Core_Model_Store::XML_PATH_SECURE_BASE_URL);
                $value = str_replace('{{secure_base_url}}', $secureBaseUrl, $value);
            } elseif (strpos($value, '{{base_url}}') !== false) {
                $value = Mage::getConfig()->substDistroServerVars($value);
            }
        }

        return $value;
    }

    /**
     * @param $request
     * @return string
     */
    protected function applyLogic($request)
    {
        $applyLogic = $this->getDbConfig('amgeoipredirect/restriction/apply_logic');
        $requestUri = $request->getPathInfo();
        $result = '';
        switch ($applyLogic) {
            case Amasty_GeoipRedirect_Model_Source_ApplyLogic::SPECIFIED_URLS :
                $acceptedUrls = explode(PHP_EOL, $this->getDbConfig('amgeoipredirect/restriction/accepted_urls'));
                foreach ($acceptedUrls as $url) {
                    $url = trim($url);
                    if ($url && $requestUri && strpos($requestUri, $url) !== false) {
                        $this->redirectAllowed = true;
                        $result = $url;
                        break;
                    }
                }
                break;
            case Amasty_GeoipRedirect_Model_Source_ApplyLogic::HOMEPAGE_ONLY :
                $requestUri = strtok($requestUri, '?');
                if ($requestUri == '/') {
                    $this->redirectAllowed = true;
                    break;
                }
                $pathInfo = trim(strtok($requestUri, '?'), '/');
                $pathParts = explode('/', $pathInfo);

                if ($this->getStoreFromUrl($request) && count($pathParts) == 1) {
                    $this->redirectAllowed = true;
                }
                break;
            default:
                $exceptedUrls = $this->getDbConfig('amgeoipredirect/restriction/excepted_urls');
                $exceptedUrls = explode(PHP_EOL, $exceptedUrls);
                if ($requestUri) {
                    $this->redirectAllowed = true;
                    foreach ($exceptedUrls as $url) {
                        $url = trim($url);
                        if ($url == '' || stripos($requestUri, $url) === false) {
                            continue;
                        }
                        $this->redirectAllowed = false;
                        $result = $url;
                        break;
                    }
                }
        }

        return $result;
    }
}
