<?php

class Ophirah_Not2Order_Helper_Data extends Mage_Core_Helper_Data
{

    public function getShowPrice($_product)
    {

        $enabled = Mage::getStoreConfig('qquoteadv_general/orderability_and_prices/enabled');
        if ($enabled != '1') {
            return true;
        }

        // Auto hide price when
        // when Cart2Quote conditions are set
        // 'quotemode_conditions' > is valid for ALL conditions
        $_product = Mage::getModel('catalog/product')->load($_product->getId());
        $loggedIn = Mage::getModel('customer/session')->isLoggedIn();
        $hideCartButton = false;

        if (Mage::getConfig()->getModuleConfig('Ophirah_Qquoteadv')->is('active', 'true') &&
            $_product->getData('quotemode_conditions') > 0) {
            $hideQuoteButton = Mage::helper('qquoteadv')->hideQuoteButton($_product);
            if ($this->autoHideCartButton($hideQuoteButton)) {
                return false;
            }
        }

        if (!$loggedIn && ($_product->getHidePrice() == '2' || $_product->getHidePrice() == '1')
            || $loggedIn && $_product->getHidePrice() == '1'
        ) {
            return false;
        }

        return true;
    }

    public function useTemplates($name = '', $path = '', $force = null)
    {

        $enabled = Mage::getStoreConfig('qquoteadv_general/orderability_and_prices/enabled');

        $layout = Mage::app()->getLayout();
        $currentTemplate = $layout->getBlock($name)->getTemplate();

        $useTemplates = Mage::getStoreConfig('qquoteadv_general/orderability_and_prices/usetemplates');

        if (($useTemplates == '1' && $enabled == '1') || ($force == 'true' && $enabled == '1')) {
            return $path;
        }

        return $currentTemplate;
    }

    /**
     * Hide Cart Button when Cart2Quote
     * module is set for showing
     * add to quote button with conditions
     *
     * @param boolean $hideQuoteButton
     * @return boolean
     */
    public function autoHideCartButton($hideQuoteButton = false)
    {
        if (Mage::getStoreConfig('qquoteadv_general/orderability_and_prices/enabled') &&
            Mage::getStoreConfig('qquoteadv_general/orderability_and_prices/autohide') &&
            $hideQuoteButton === false
        ) {
            return true;
        }
        return false;
    }
}
