<?php
/**
 * Media Photogallery & Product Videos extension
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @category   FME
 * @package    Mediaappearance
 * @copyright  Copyright 2010 © free-magentoextensions.com All right reserved
 **/
 
class FME_Photogallery_Block_Adminhtml_Photogalleryblocks extends Mage_Adminhtml_Block_Widget_Grid_Container
{
  public function __construct()
  {
    $this->_controller = 'adminhtml_photogalleryblocks';
    $this->_blockGroup = 'photogallery';
    $this->_headerText = Mage::helper('photogallery')->__('Photogallery Block Manager');
    $this->_addButtonLabel = Mage::helper('photogallery')->__('Add Photogallery Block');
    parent::__construct();

  }
}
