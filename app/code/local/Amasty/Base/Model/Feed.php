<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Base
 */


class Amasty_Base_Model_Feed extends Mage_AdminNotification_Model_Feed
{
    const HOUR_MIN_SEC_VALUE = 86400;//60 * 60 * 24

    const REMOVE_EXPIRED_FREQUENCY = 21600;//4 times per day 60 * 60 * 6

    const XML_FREQUENCY_PATH = 'ambase/feed/frequency';

    const XML_LAST_REMOVMENT = 'ambase/system_value/remove_date';

    const XML_LAST_UPDATE_PATH = 'ambase/feed/last_update';

    const URL_NEWS = 'amasty.com/feed-news-segments.xml';//don't pass http or https

    /**
     * @var array
     */
    protected $amastyModules = array();

    /**
     * @var array
     */
    protected $subModules = array(
        'Amasty_Base',
        'Magpleasure_Common',
        'Amasty_PromoBannersLite',
        'Amasty_Commonrules',
        'Amasty_PaymentDetect',
        'Amasty_DisableCustomers',
        'Amasty_Fpccrawler',
        'Amasty_Geoip',
        'Magpleasure_Searchcore',
        'Amasty_Regions',
        'Amasty_RuleTimeConditions'
    );

    public function check()
    {
        try {
            // additional check when cache was not cleaned after installation
            if (version_compare($this->getBaseModuleVersion(), '2.3.0') >= 0) {
                $this->checkUpdate();
                $this->removeExpiredItems();
            }
        } catch(Exception $ex) {
            Mage::log($ex->getMessage());
        }
    }

    protected function _isPromoSubscribed()
    {
        return Mage::helper("ambase/promo")->isSubscribed();
    }

    public function checkUpdate()
    {
        if (($this->getFrequency() + $this->getLastUpdate()) > time()) {
            return $this;
        }

        if (!extension_loaded('curl')
            || !Mage::helper('ambase')->isModuleActive('Mage_AdminNotification')
            || !class_exists('Amasty_Base_Model_Source_Type') //cases with wrong permission to file
        ) {
            return $this;
        }

        $allowedNotifications = $this->getAllowedTypes();
        if (empty($allowedNotifications)
            || in_array(Amasty_Base_Model_Source_Type::UNSUBSCRIBE_ALL, $allowedNotifications)
        ) {
            return $this;
        }

        $feedData = array();
        $maxPriority = 0;
        $feedXml = $this->getFeedData();
        $wasInstalled = gmdate('Y-m-d H:i:s', Amasty_Base_Helper_Module::baseModuleInstalled());

        if ($feedXml && $feedXml->channel && $feedXml->channel->item) {
            foreach ($feedXml->channel->item as $item) {
                if (!array_intersect($this->convertToArray($item->type), $allowedNotifications)
                    || (int)$item->version == 2 // for magento two
                    || ((string)$item->edition && (string)$item->edition != $this->getCurrentEdition())
                ) {
                    continue;
                }

                $priority = (int)$item->priority ? (int)$item->priority : 1;
                if ($priority <= $maxPriority) {
                    continue; //add only one with the highest priority
                }

                if (!$this->validateByExtension((string)$item->extension)) {
                    continue;
                }

                if (!$this->validateByAmastyCount($item->amasty_module_qty)) {
                    continue;
                }

                if (!$this->validateByNotInstalled((string)$item->amasty_module_not)) {
                    continue;
                }

                if (!$this->validateByExtension((string)$item->third_party_modules, true)) {
                    continue;
                }

                if (!$this->validateByDomainZone((string)$item->domain_zone)) {
                    continue;
                }

                if ($this->isItemExists($item)) {
                    continue;
                }

                $date = $this->getDate((string)$item->pubDate);
                $expired =(string)$item->expirationDate ? strtotime((string)$item->expirationDate) : null;

                if ($wasInstalled <= $date
                    && (!$expired || $expired > gmdate('U'))
                ) {
                    //add only one with the highest priority
                    $maxPriority = $priority;
                    $expired = $expired ? date('Y-m-d H:i:s', $expired) : null;
                    $feedData = array(
                        'severity' => Mage_AdminNotification_Model_Inbox::SEVERITY_NOTICE,
                        'date_added' => $this->getDate($date),
                        'expiration_date' => $expired,
                        'title' => (string)$item->title,
                        'description' => (string)$item->description,
                        'url' => (string)$item->link,
                        'is_amasty' => 1
                    );
                }
            }

            if ($feedData) {
                $inbox = Mage::getModel('adminnotification/inbox');

                if ($inbox) {
                    $inbox->parse(array($feedData));
                }
            }
        }

        $this->setLastUpdate();

        //load all available extensions in the cache
        Amasty_Base_Helper_Module::reload();

        return $this;
    }

    /**
     * @param $value
     *
     * @return array
     */
    protected function convertToArray($value)
    {
        return explode(',', (string)$value);
    }

    /**
     * @return $this
     */
    public function removeExpiredItems()
    {
        if ($this->getLastRemovement() + self::REMOVE_EXPIRED_FREQUENCY > time()) {
            return $this;
        }

        $collection = Mage::getResourceModel('ambase/inbox_expired_collection');
        foreach ($collection as $model) {
            $model->setIsRemove(1)->save();
        }

        $this->setLastRemovement();

        return $this;
    }

    protected function isItemExists($item)
    {
        return Mage::getResourceModel('ambase/inbox_collection')->execute($item);
    }

    /**
     * @return int
     */
    public function getFrequency()
    {
        return Mage::getStoreConfig(self::XML_FREQUENCY_PATH) * self::HOUR_MIN_SEC_VALUE;
    }

    public function getLastUpdate()
    {
        return Mage::getStoreConfig(self::XML_LAST_UPDATE_PATH);
    }

    public function setLastUpdate()
    {
        $config = Mage::getModel('core/config');
        /* @var $config Mage_Core_Model_Config */
        $config->saveConfig(self::XML_LAST_UPDATE_PATH, time());
        Mage::getConfig()->cleanCache();
        return $this;
    }

    /**
     * @return int
     */
    protected function getLastRemovement()
    {
        return Mage::getStoreConfig(self::XML_LAST_REMOVMENT);
    }

    /**
     * @return $this
     */
    protected function setLastRemovement()
    {
        $config = Mage::getModel('core/config');
        /* @var $config Mage_Core_Model_Config */
        $config->saveConfig(self::XML_LAST_REMOVMENT, time());
        Mage::getConfig()->cleanCache();
        return $this;
    }

    public function getFeedUrl()
    {
        if (is_null($this->_feedUrl)) {
            $this->_feedUrl = (Mage::getStoreConfigFlag(self::XML_USE_HTTPS_PATH) ? 'https://' : 'http://')
                . self::URL_NEWS;
        }
        return $this->_feedUrl;
    }

    protected function isExtensionInstalled($code)
    {
        $modules = array_keys((array)Mage::getConfig()->getNode('modules')->children());
        foreach ($modules as $moduleName) {
            if ($moduleName == $code) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return array
     */
    protected function getAllowedTypes()
    {
        $allowedNotifications = Mage::getStoreConfig('ambase/feed/type');
        $allowedNotifications = explode(',', $allowedNotifications);

        return $allowedNotifications;
    }

    /**
     * @return string
     */
    protected function getCurrentEdition()
    {
        return Mage::getConfig()->getNode('modules/Enterprise_Enterprise') ? 'ee' : 'ce';
    }

    /**
     * @return array
     */
    protected function getInstalledAmastyExtensions()
    {
        if (!$this->amastyModules) {
            $modules = Mage::getConfig()->getNode('modules')->children();
            $modules = (array)$modules;
            $result = array();

            foreach ($modules as $module => $config) {
                if (strpos($module, 'Amasty_') !== false
                    || strpos($module, 'Magpleasure_') !== false
                ) {
                    $result[] = $module;
                }
            }

            $this->amastyModules = $result;
        }

        return $this->amastyModules;
    }

    /**
     * @return array
     */
    protected function getAllExtensions()
    {
        $modules = Mage::getConfig()->getNode('modules')->children();
        $modules = (array)$modules;
        $result = array_keys($modules);

        return $result;
    }

    /**
     * @param string $extensions
     * @return bool
     */
    protected function validateByExtension($extensions, $allModules = false)
    {
        if ($extensions) {
            $result = false;
            $mOneExtensions = $this->validateExtensionValue($extensions);

            if ($mOneExtensions) {
                $installedModules = $allModules ? $this->getAllExtensions() : $this->getInstalledAmastyExtensions();
                $intersect = array_intersect($mOneExtensions, $installedModules);
                if ($intersect) {
                    $result = true;
                }
            }
        } else {
            $result = true;
        }

        return $result;
    }

    /**
     * @param string $extensions
     * @return bool
     */
    protected function validateByNotInstalled($extensions)
    {
        if ($extensions) {
            $result = false;
            $mOneExtensions = $this->validateExtensionValue($extensions);

            if ($mOneExtensions) {
                $installedModules = $this->getInstalledAmastyExtensions();
                $diff = array_diff($mOneExtensions, $installedModules);
                if ($diff) {
                    $result = true;
                }
            }
        } else {
            $result = true;
        }

        return $result;
    }

    /**
     * @param string $extensions
     *
     * @return array
     */
    protected function validateExtensionValue($extensions)
    {
        $extensions = explode(',', $extensions);

        $mOneExtensions = array();
        foreach ($extensions as $extension) {
            if (strpos($extension, '_2') === false) {
                $mOneExtensions[] = str_replace('_1', '', $extension);
            }
        }

        return $mOneExtensions;
    }


    /**
     * @return string
     */
    protected function getBaseModuleVersion()
    {
        return (string) Mage::getConfig()->getNode()->modules->Amasty_Base->version;
    }

    /**
     * @param $counts
     * @return bool
     */
    protected function validateByAmastyCount($counts)
    {
        $result = true;
        $countString = (string)$counts;

        if ($countString) {
            $counts = $this->convertToArray($counts);
            $moreThan = null;

            $position = strpos($countString, '>');
            if ($position !== false) {
                $moreThan = substr($countString, $position + 1);
                $moreThan = explode(',', $moreThan);
                $moreThan = array_shift($moreThan);
            }

            $result = false;
            $amastyModules = $this->getInstalledAmastyExtensions();
            $amastyModules = $this->removeSubModules($amastyModules);
            $amastyModules = array_flip($amastyModules);
            $amastyModules = $this->removeToolKitModules($amastyModules);

            $amastyCount = count($amastyModules);

            if ($amastyCount
                && (in_array($amastyCount, $counts)
                    || ($moreThan && $amastyCount >= $moreThan)
                )
            ) {
                $result = true;
            }
        }

        return $result;
    }

    /**
     * @param $zones
     *
     * @return bool
     */
    protected function validateByDomainZone($zones)
    {
        $result = true;
        if ($zones) {
            $zones = $this->convertToArray($zones);
            $currentZone = $this->getDomainZone();

            if (!in_array($currentZone, $zones)) {
                $result = false;
            }
        }

        return $result;
    }

    /**
     * @return string
     */
    protected function getDomainZone()
    {
        $domain = '';
        $url = Mage::getBaseUrl();
        $components = parse_url($url);
        if (isset($components['host'])) {
            $host = explode('.', $components['host']);
            $domain = end($host);
        }

        return $domain;
    }

    protected function removeSubModules($amastyModules)
    {
        $amastyModules = array_diff($amastyModules, $this->subModules);
        return $amastyModules;
    }

    protected function removeToolKitModules($amastyModules)
    {
        if (isset($amastyModules['Amasty_Followup'])) {
            unset($amastyModules['Amasty_Segments']);
        }

        if (isset($amastyModules['Amasty_Loyalty'])) {
            unset($amastyModules['Amasty_RulesPro']);
            unset($amastyModules['Amasty_Rules']);
            unset($amastyModules['Amasty_Promocopy']);
        }

        if (isset($amastyModules['Amasty_Rules'])) {
            unset($amastyModules['Amasty_Promocopy']);
        }

        if (isset($amastyModules['Amasty_RulesPro'])) {
            unset($amastyModules['Amasty_Rules']);
            unset($amastyModules['Amasty_Promocopy']);
        }

        if (isset($amastyModules['Amasty_Scheckout'])) {
            unset($amastyModules['Amasty_Autoshipping']);
        }

        if (isset($amastyModules['Amasty_Acart'])) {
            unset($amastyModules['Amasty_Geoip']);
        }

        if (isset($amastyModules['Amasty_Productmanagerbundle'])) {
            unset($amastyModules['Amasty_Imgupload']);
            unset($amastyModules['Amasty_Pgrid']);
            unset($amastyModules['Amasty_Paction']);
            unset($amastyModules['Amasty_File']);
        }
        
        if (isset($amastyModules['Amasty_Ordermanagertoolkit'])) {
            unset($amastyModules['Amasty_Oaction']);
            unset($amastyModules['Amasty_Ogrid']);
            unset($amastyModules['Amasty_Flags']);
            unset($amastyModules['Amasty_Orderattach']);
        }

        if (isset($amastyModules['Amasty_SeoToolKit'])) {
            unset($amastyModules['Amasty_SeoSingleUrl']);
            unset($amastyModules['Amasty_SeoShortUrl']);
            unset($amastyModules['Amasty_Meta']);
            unset($amastyModules['Amasty_SeoGoogleSitemap']);
            unset($amastyModules['Amasty_SeoHtmlSitemap']);
            unset($amastyModules['Amasty_SeoRichData']);
            unset($amastyModules['Amasty_SeoReviews']);
            unset($amastyModules['Amasty_SeoTags']);
        }

        if (isset($amastyModules['Amasty_Groupcat'])
            || isset($amastyModules['Magpleasure_Blog'])
            || isset($amastyModules['Amasty_Faq'])
            || isset($amastyModules['Amasty_Customform'])
        ) {
            unset($amastyModules['Amasty_InvisibleCaptcha']);
        }

        return $amastyModules;
    }
}
