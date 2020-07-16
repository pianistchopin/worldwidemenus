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
 
class FME_Photogallery_Block_Photogallery_Toolbar extends Mage_Page_Block_Html_Pager
{
    
    protected function _construct()
    {
        parent::_construct();
        
        $this->setTemplate('photogallery/photogallery/toolbar.phtml');
    }
    
    

    /**
     * Retrieve current limit per page
     *
     * @return string
     */
    public function getLimit()
    {
    
    
        if (Mage::getStoreConfig('photogallery/photogallery/limit'))
            $limit = Mage::getStoreConfig('photogallery/photogallery/limit');
        else        
            $limit = 100;
        
        return $limit; 
    }

    /**
     * Retrieve Limit Pager URL
     *
     * @param int $limit
     * @return string
     */
    public function getLimitUrl($limit)
    {
        return $this->getPagerUrl(
            array(
            $this->getLimitVarName() => $limit,
            $this->getPageVarName() => null
            )
        );
    }
    
    
}
