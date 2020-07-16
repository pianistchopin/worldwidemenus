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

class MT_Exitoffer_Model_Popup
    extends Mage_Core_Model_Abstract
{

    const CONTENT_TYPE_NEWSLETTER_SUBSCRIPTION_FORM = 'newsletter_subscription_form';

    const CONTENT_TYPE_SOCIAL_BUTTONS = 'social_buttons';

    const CONTENT_TYPE_STATIC_CMS_BLOCK = 'static_cms_block';

    const CONTENT_TYPE_YES_NO = 'yes_no';

    const CONTENT_TYPE_CONTACT_FORM = 'contact_form';

    protected function _construct()
    {
        $this->_init('exitoffer/popup');
    }

    public function delete()
    {
        $additionalFields = $this->getFieldCollection();
        if ($additionalFields->count() > 0) {
            foreach ($additionalFields as $field) {
                $field->delete();
            }
        }

        return parent::delete();
    }

    public function getCollectionAsOptionArray()
    {
        $optionArray = array();
        $collection = $this->getCollection();
        if ($collection->count() > 0) {
            foreach ($collection as $popup) {
                $optionArray[] = array(
                    'label' => $popup->getName(),
                    'value' => $popup->getId(),
                );
            }
        }

        return $optionArray;
    }

    public function getFieldCollection()
    {
        $collection = Mage::getModel('exitoffer/field')->getCollection()
            ->addFieldToFilter('popup_id', $this->getId())
            ->setOrder('position','ASC');
        return $collection;
    }

    public function getNewCouponCode()
    {
        $ruleId = $this->getCouponRuleId();
        if (!is_numeric($ruleId)) {
            Mage::helper('exitoffer')->log(
                'Shopping cart price rule is not assigned to the popup # '.$this->getId()
                .', MT_Exitoffer_Model_Subscriber:getCouponCode'
            );
            return '';
        }

        $rule = Mage::getModel('salesrule/rule')->load($ruleId);
        if (!$rule->getId()) {
            return false;
        }

        if ($rule->getUseAutoGeneration() == 1) {
            $massGenerator = $rule->getCouponMassGenerator();
            $massGenerator->setData(array(
                'rule_id' => $ruleId,
                'qty' => 1,
                'length' => $this->getCouponLength(),
                'format' => $this->getCouponFormat(),
                'prefix' => $this->getCouponPrefix(),
                'suffix' => $this->getCouponSuffix(),
                'dash' => $this->getCouponDash(),
                'uses_per_coupon' => 1,
                'uses_per_customer' => 1
            ));
            $massGenerator->generatePool();
            $latestCoupon = max($rule->getCoupons());
            $code =  $latestCoupon->getCode();
        } else {
            $code = $rule->getCouponCode();
        }

        return $code;
    }

    public function isValidFormData($data)
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