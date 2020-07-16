<?php
/**
 * @copyright  Copyright (c) 2009 AITOC, Inc.
 */
class Aitoc_Common_Helper_Data extends Mage_Core_Helper_Abstract
{
    public function isMageGtEq19()
    {
        return version_compare(Mage::getVersion(), '1.9.0.0', '>=');
    }
}