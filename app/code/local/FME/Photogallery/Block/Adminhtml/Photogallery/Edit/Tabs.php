<?php
/**
 * Photo Photogallery & Products Photogallery extension
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
 * @copyright  Copyright 2010 � free-magentoextensions.com All right reserved
 */

/**
 * @category   Photogallery
 * @package    Photogallery
 * @author     Kamran Rafiq Malik <kamran.malik@unitedsol.net>
 */

class FME_Photogallery_Block_Adminhtml_Photogallery_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{

  public function __construct()
  {
      parent::__construct();
      $this->setId('photogallery_tabs');
      $this->setDestElementId('edit_form');
      $this->setTitle(Mage::helper('photogallery')->__('Photogallery Information'));
  }

  protected function _beforeToHtml()
  {
      $this->addTab(
          'form_section', array(
          'label'     => Mage::helper('photogallery')->__('Photogallery Information'),
          'title'     => Mage::helper('photogallery')->__('Photogallery Information'),
          'content'   => $this->getLayout()->createBlock('photogallery/adminhtml_photogallery_edit_tab_form')->toHtml(),
          )
      );
      
      $this->addTab(
          'images_section', array(
              'label'     => Mage::helper('photogallery')->__('Gallery Images'),
              'title'     => Mage::helper('photogallery')->__('Gallery Images'),
              'content'   => $this->getLayout()->createBlock('photogallery/adminhtml_photogallery_edit_tab_images')->toHtml(),
          )
      );
      
      
      $this->addTab(
          'products_section', array(
            'label'     => Mage::helper('photogallery')->__('Attach With Products'),
            'url'       => $this->getUrl('*/*/products', array('_current' => true)),
            'class'     => 'ajax',
          )
      );
      
      return parent::_beforeToHtml();
  }
}
