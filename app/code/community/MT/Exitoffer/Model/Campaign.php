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

class MT_Exitoffer_Model_Campaign
    extends Mage_Rule_Model_Rule

{
    const STATUS_ACTIVE = 1;

    private $__currentCampaign = null;

    private $__currentPopup = null;

    const XML_PATH_DEFAULT_CAMPAIGN_ID = 'exitoffer/subscription/campaign_id';

    const XML_PATH_DEFAULT_SUBSCRIPTION_IS_ACTIVE = 'exitoffer/subscription/is_active';

    protected function _construct()
    {
        parent::_construct();
        $this->_init('exitoffer/campaign');
    }

    public function save()
    {
        parent::save();


        if (is_array($this->getData('store_ids'))) {
            $storeIds = $this->getData('store_ids');
            $this->unsetData('store_ids');
            $db = Mage::getSingleton('core/resource')->getConnection( 'core_write' );
            $table = Mage::getSingleton( 'core/resource' )->getTableName('exitoffer/campaign_store');
            $db->delete($table,  'campaign_id = '.$this->getId());
            foreach ($storeIds as $storeId) {
                $db->insert($table, array(
                    'campaign_id' => $this->getId(),
                    'store_id' => $storeId
                ));
            }
        }

        if (is_array($this->getData('pages'))) {
            $pages = $this->getData('pages');
            $this->unsetData('pages');

            $db = Mage::getSingleton('core/resource')->getConnection('core_write');
            $table = Mage::getSingleton( 'core/resource' )->getTableName('exitoffer/campaign_page');
            $db->delete($table, 'campaign_id = '.$this->getId());
            foreach ($pages as $page) {
                $db->insert($table, array(
                    'campaign_id' => $this->getId(),
                    'page_id' => $page
                ));
            }
        }
    }

    public function getStoreIds()
    {
        $storeIds = array();
        $db = Mage::getSingleton('core/resource')->getConnection('core_read');
        $table = Mage::getSingleton( 'core/resource' )->getTableName('exitoffer/campaign_store');
        $select = $db->select()
            ->from($table, array('store_id'))
            ->where('campaign_id =?', $this->getId());
        $data = $db->fetchAll($select);

        if (count($data) > 0) {
            foreach ($data as $row) {
                $storeIds[] = $row['store_id'];
            }
        }

        return $storeIds;
    }

    public function getPages()
    {
        $pages = array();
        $db = Mage::getSingleton('core/resource')->getConnection('core_read');
        $table = Mage::getSingleton( 'core/resource' )->getTableName('exitoffer/campaign_page');
        $select = $db->select()
            ->from($table, array('page_id'))
            ->where('campaign_id =?', $this->getId());
        $data = $db->fetchAll($select);

        if (count($data) > 0) {
            foreach ($data as $row) {
                $pages[] = $row['page_id'];
            }
        }

        return $pages;
    }


    public function getCurrent()
    {


        if ($this->__currentCampaign === null) {
            $storeId = Mage::app()->getStore()->getId();
            $dateNow = date('Y-m-d H:i:s');
            $pageId = Mage::helper('exitoffer')->getPageId();

            $campaignCollection  = Mage::getModel('exitoffer/campaign')->getCollection()
                ->addFieldToFilter('status', MT_Exitoffer_Model_Campaign::STATUS_ACTIVE);
            $campaignCollection->getSelect()->join(
                array('s' => $campaignCollection->getTable('exitoffer/campaign_store')),
                'main_table.entity_id=s.campaign_id',
                array('store_id' => 's.store_id')
            )->join(
                array('t' => $campaignCollection->getTable('exitoffer/campaign_page')),
                'main_table.entity_id=t.campaign_id',
                array('page_id' => 't.page_id')
            )

                ->where('store_id  = '.$storeId.' OR store_id = 0')
                ->where("(start_date <= '{$dateNow}' AND end_date >= '{$dateNow}') OR (start_date is null AND end_date is null) OR (start_date is null AND end_date >= '{$dateNow}') OR (end_date is null AND start_date <= '{$dateNow}')")
                ->where("page_id = '{$pageId}' OR page_id = 'all' OR page_id = 's_{$pageId}'")
                ->group('main_table.entity_id');

            if ($campaignCollection->count() == 0) {
                $this->__currentCampaign = false;
            } else {
                $request = Mage::app()->getRequest();
                $cart = Mage::getModel('checkout/cart');
                foreach ($campaignCollection as $campaign) {
                    if (!$campaign->isAvailable()) {
                        continue;
                    }


                    //TODO check $cart here and continue if sku will be found


                    $params = $campaign->getParamsArray();
                    if (count($params) == 0 && $campaign->validate($cart)) {

                        $this->__currentCampaign = $campaign;
                        break;
                    } else {
                        $match = true;
                        foreach ($params as $key => $value) {
                            if ($request->getParam($key) != $value) {
                                $match = false;
                                break;
                            }
                        }

                        if (!$match) {
                            continue;
                        }

                        if (!$campaign->validate($cart)) {
                            continue;
                        }

                        $this->__currentCampaign = $campaign;
                        break;
                    }
                }
            }

            //no campaign for current page
            if ($this->__currentCampaign === null) {
                $this->__currentCampaign = false;
            }

        }

        return $this->__currentCampaign ;
    }

    public function isAvailable()
    {
        $storeId = Mage::app()->getStore()->getId();
        $defaultCookie = Mage::getStoreConfig('exitoffer/general/cookiename', $storeId);
        if (Mage::app()->getFrontController()->getAction()->getFullActionName()=='checkout_onepage_success'
            || (
                Mage::getSingleton('customer/session')->isLoggedIn()
                && !Mage::getModel('core/cookie')->get($defaultCookie.'_customer')
            )
        ) {
            Mage::getModel('core/cookie')->set($defaultCookie.'_customer', 1);
            return false;
        }

        if ($this->getShowTo() == 1) {
            if (Mage::getModel('core/cookie')->get($defaultCookie.'_customer') == 1) {
                return false;
            }
        }
        if ($this->getPageId() == 's_category_page') {
            if (!$this->isAvailableOnCategory()) {
                return false;
            }
        }

        if ($this->getPageId() == 's_product_page') {
            if (!$this->isAvailableOnProduct()) {
                return false;
            }
        }
        return true;
    }

    public function isAvailableOnCategory()
    {
        $currentCategory = Mage::registry('current_category');
        if ($currentCategory && $currentCategory->getData('mt_eop_show_popup') == $this->getId()) {
            return true;
        }

        return false;
    }

    public function isAvailableOnProduct()
    {
        $currentProduct = Mage::registry('current_product');
        if ($currentProduct && $currentProduct->getData('mt_eop_show_popup') == $this->getId() ) {
            return true;
        }

        return false;
    }

    public function getParamsArray()
    {
        $return = array();
        $paramsString = $this->getParams();
        if (empty($paramsString)) {
            return $return;
        }

        $params = explode('&', $paramsString);
        foreach ( $params as $param) {
            $tmpParam = explode('=', $param);
            if (count($tmpParam) == 2) {
                $return[$tmpParam[0]] = $tmpParam[1];
            }
        }

        return $return;
    }

    public function getPopup()
    {
        if ($this->__currentPopup === null) {
            $this->__currentPopup = Mage::getModel('exitoffer/popup')->load($this->getPopupId());
        }
        return $this->__currentPopup;
    }

    public function getCookieName()
    {
        $prefix = Mage::getStoreConfig('exitoffer/general/cookiename');
        $storeId = Mage::app()->getStore()->getId();
        // @codingStandardsIgnoreLine
        return $prefix.'_'.md5($storeId.'_'.$this->getId());
    }

    public function delete()
    {
        $campaignId = $this->getId();
        $resource = Mage::getSingleton('core/resource');
        $tablePages = $resource->getTableName('exitoffer/campaign_page');
        $tableStore = $resource->getTableName('exitoffer/campaign_store');
        $db = $resource->getConnection('core_write');
        $db->delete($tablePages, 'campaign_id = '.$campaignId);
        $db->delete($tableStore, 'campaign_id = '.$campaignId);

        return parent::delete();
    }

    public function getConditionsInstance()
    {
        return Mage::getModel('salesrule/rule_condition_combine');
    }

    public function getActionsInstance()
    {
        return Mage::getModel('salesrule/rule_condition_product_combine');
    }

    /**
     * load Rule posted from web
     *
     * @param array $rule
     * @return Magestore_RewardPointsRule_Model_Earning_Sales
     */
    public function loadPost(array $rule)
    {
        $arr = $this->_convertFlatToRecursive($rule);

        if (isset($arr['conditions'])) {
            $this->getConditions()->setConditions(array())->loadArray($arr['conditions'][1]);
        }
        if (isset($arr['actions'])) {
            $this->getActions()->setActions(array())->loadArray($arr['actions'][1], 'actions');
        }

        return $this;
    }

    protected function _beforeSave()
    {
        parent::_beforeSave();
        if ($this->hasWebsiteIds()) {
            $websiteIds = $this->getWebsiteIds();
            if (is_array($websiteIds) && !empty($websiteIds)) {
                $this->setWebsiteIds(implode(',', $websiteIds));
            }
        }
        if ($this->hasCustomerGroupIds()) {
            $groupIds = $this->getCustomerGroupIds();
            if (is_array($groupIds) && !empty($groupIds)) {
                $this->setCustomerGroupIds(implode(',', $groupIds));
            }
        }
        return $this;
    }

}