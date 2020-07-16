<?php
/**
 * Photogallery Photogallery & Product Videos extension
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @category   FME
 * @package    Photogallery
 * @copyright  Copyright 2010 © free-magentoextensions.com All right reserved
 **/ 
 
class FME_Photogallery_Block_Adminhtml_Photogalleryblocks_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{

  public function __construct()
  {
      parent::__construct();
      $this->setId('photogalleryblocks_tabs');
      $this->setDestElementId('edit_form');
      $this->setTitle(Mage::helper('photogallery')->__('Photogallery Block Management'));
  }

  protected function _beforeToHtml()
  {
      $this->addTab(
          'photogallery_form_section', array(
          'label'     => Mage::helper('photogallery')->__('Photogallery Block Information'),
          'title'     => Mage::helper('photogallery')->__('Photogallery Block Information'),
          'content'   => $this->getLayout()->createBlock('photogallery/adminhtml_photogalleryblocks_edit_tab_form')->toHtml(),
          )
      );
      
       $this->addTab(
           'images_section', array(
              'label'     => Mage::helper('photogallery')->__('Gallery Images'),
              'title'     => Mage::helper('photogallery')->__('Gallery Images'),
              'content'   => $this->getLayout()->createBlock('photogallery/adminhtml_photogalleryblocks_edit_tab_images')->toHtml(),
           )
       );
     
      
      return parent::_beforeToHtml();
  }
}
