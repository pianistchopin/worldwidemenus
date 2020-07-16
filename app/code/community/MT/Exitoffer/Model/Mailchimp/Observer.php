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

class MT_Exitoffer_Model_Mailchimp_Observer
    extends Ebizmarts_MailChimp_Model_Observer
{
    /**
     * Process NewsletterPopup observer before MageMonkey
     * @param Varien_Event_Observer $observer
     * @return Varien_Event_Observer|void
     */
    public function handleSubscriber(Varien_Event_Observer $observer)
    {
        Mage::getModel('exitoffer/observer')
            ->beforeSaveSubscriber($observer);
        Mage::unregister('exit_offer_disable_beforeSaveSubscriber');
        Mage::register('exit_offer_disable_beforeSaveSubscriber', 1);
        return parent::handleSubscriber($observer);
    }
}