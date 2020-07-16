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

class MT_Exitoffer_Model_Adminhtml_System_Config_Source_Campaign
{
    protected $_options;

    public function toOptionArray()
    {
        if (!$this->_options) {
            $popupTableName = Mage::getSingleton('core/resource')->getTableName('exitoffer/popup');
            $collection = Mage::getModel('exitoffer/campaign')->getCollection();
            $collection->getSelect()
                ->join(
                    array('pp' => $popupTableName),
                    'main_table.popup_id = pp.entity_id',
                    array('content_type')
                )
                ->where("pp.coupon_status = 1");

            if ($collection->count() > 0) {
                foreach ($collection as $campaign) {
                    $this->_options[$campaign->getId()] = $campaign->getName();
                }
            }
        }
        return $this->_options;
    }
}