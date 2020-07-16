<?php
/**
 * Photo Gallery & Products Gallery extension
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @category   FME
 * @package    Gallery
 * @copyright  Copyright 2010 © free-magentoextensions.com All right reserved
 */

/**
 * @category   Gallery
 * @package    Gallery
 * @author     Kamran Rafiq Malik <kamran.malik@unitedsol.net>
 */

class FME_Photogallery_Model_Img extends Mage_Core_Model_Abstract
{
    protected function _construct()
    {
    parent::_construct();
        $this->_init('photogallery/img');
    }
    
}
