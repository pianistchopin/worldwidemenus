<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_GeoipRedirect
 */
class Amasty_GeoipRedirect_Model_Source_ApplyLogic extends Varien_Object
{
    const SPECIFIED_URLS = 1;
    const EXCEPT_URLS = 2;
    const HOMEPAGE_ONLY = 3;

    public function toOptionArray()
    {
        $hlp = Mage::helper('amgeoipredirect');
        return array(
            array('value' => self::EXCEPT_URLS, 'label' => $hlp->__('All Except Specified URLs')),
            array('value' => self::SPECIFIED_URLS, 'label' => $hlp->__('Specified URLs')),
            array('value' => self::HOMEPAGE_ONLY, 'label' => $hlp->__('Redirect From Home Page Only'))
        );
    }
}