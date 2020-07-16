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

class MT_Exitoffer_Model_Subscriber
{
    const XML_PATH_EMAIL_ATTACHMENT = 'exitoffer/email/attachment';

    public function addDbColumn($columnName, $inputType)
    {

        if (empty($columnName) || !ctype_alpha($columnName)) {
            Mage::helper('exitoffer')->log('Bad additional field name '.$columnName.', MT_Exitoffer_Model_Subscriber:addDbColumn');
            return false;
        }

        $columnName = 'subscriber_'.$columnName;
        $resource = Mage::getSingleton('core/resource');
        $tableName = $resource->getTableName('newsletter/subscriber');
        $db = $resource->getConnection('core_write');

        if ($inputType == 'checkbox') {
            $db->addColumn($tableName, $columnName,  array(
                'TYPE'      => Varien_Db_Ddl_Table::TYPE_SMALLINT,
                'LENGTH'    => 1,
                'COMMENT'   => 'Additional field'
            ));
        } else{
            $db->addColumn($tableName, $columnName,  array(
                'TYPE'      => Varien_Db_Ddl_Table::TYPE_TEXT,
                'COMMENT'   => 'Additional field'
            ));
        }

        return true;
    }


    public function isValidAdditional($data)
    {
        if (!isset($data['campaign_id']) || !is_numeric($data['campaign_id'])) {
            Mage::throwException(Mage::helper('exitoffer')->translate('error_with_subscription'));
        }
        $campaignId = $data['campaign_id'];
        $campaign = Mage::getModel('exitoffer/campaign')->load($campaignId);

        if (!$campaign) {
            Mage::throwException(Mage::helper('exitoffer')->translate('error_with_subscription'));
        }

        $additionalFields = $campaign->getPopup()->getFieldCollection();
        if ($additionalFields->count() > 0) {
            foreach ($additionalFields as $field) {
                if ($field->getIsRequired() == 0) {
                    continue;
                }

                if (!isset($data[$field->getName()])) {
                    Mage::throwException(Mage::helper('exitoffer')->translate('field'). ' ' .$field->getTitle(). ' '. Mage::helper('exitoffer')->translate('is_required'));
                }

                $value = $data[$field->getName()];
                // @codingStandardsIgnoreLine
                if ((empty($value) || md5($value) == md5($field->getTitle())) ||
                    ( $field->getType()=='checkbox' && $value == 0 )
                ) {
                    Mage::throwException(Mage::helper('exitoffer')->translate('field'). ' ' .$field->getTitle(). ' '. Mage::helper('exitoffer')->translate('is_required'));
                }
            }
        }
        return true;
    }
}