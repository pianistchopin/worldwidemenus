<?php

class MT_Exitoffer_Model_Mailchimp_Api_Subscriber
    extends Ebizmarts_MailChimp_Model_Api_Subscribers
{
    /**
     * Add data from additional fields and coupon code
     * @param $subscriber
     * @return array|null
     */
    public function getMergeVars($subscriber)
    {
        $mergedVars = parent::getMergeVars($subscriber);
        $popup = Mage::helper('exitoffer')->getCurrentPopup();
        if (!$popup || !$popup->getId() || $popup->getCouponStatus() == 0) {
            return $mergedVars;
        }

        if (Mage::getStoreConfig('exitoffer/general/is_active')) {
            $mergedVars['COUPON'] = $subscriber->getData('exit_offer_coupon_code');
        }
        if ($additionalFields = $popup->getFieldCollection()) {
            foreach ($additionalFields as $field) {
                $field['name'] = str_replace('subscriber_', '', $field['name']);
                $value = $subscriber->getData('subscriber_' . $field['name']);
                if (!empty($value)) {
                    $mergedVars[strtoupper($field['name'])] = $value;
                }
            }
        }
        return $mergedVars;
    }
}