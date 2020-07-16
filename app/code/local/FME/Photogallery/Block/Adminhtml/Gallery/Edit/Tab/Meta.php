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
 * @copyright  Copyright 2010 © free-magentoextensions.com All right reserved
 */

/**
 * @category   Photogallery
 * @package    Photogallery
 * @author     Kamran Rafiq Malik <kamran.malik@unitedsol.net>
 */


class FME_Photogallery_Block_Adminhtml_Gallery_Edit_Tab_Meta extends Mage_Adminhtml_Block_Widget_Form
{
   protected function _prepareForm()
   {
    
      $form = new Varien_Data_Form();
      $this->setForm($form);
      $fieldset = $form->addFieldset('gallery_form', array('legend'=>Mage::helper('photogallery')->__('Meta Information')));
     
     
      $fieldset->addField(
          'meta_title', 'text', array(
          'label'     => Mage::helper('photogallery')->__('Meta Title'),
          'style'     => 'width:300px',
          'name'      => 'meta_title',
          'tabindex' => 1,
          )
      );


       $fieldset->addField(
           'meta_keywords', 'editor', array(
           'name'      => 'meta_keywords',
           'label'     => Mage::helper('photogallery')->__('Meta Keywords'),
           'title'     => Mage::helper('photogallery')->__('Meta Keywords'),
           'style'     => 'width:300px; height:150px;',
           'wysiwyg'   => false,
           'tabindex' => 2,
           )
       );
      
      
       $fieldset->addField(
           'meta_description', 'editor', array(
           'name'      => 'meta_description',
           'label'     => Mage::helper('photogallery')->__('Meta Description'),
           'title'     => Mage::helper('photogallery')->__('Meta Description'),
           'style'     => 'width:300px; height:150px;',
           'wysiwyg'   => false,
           'tabindex' => 3,
           )
       );
              
     
      if (Mage::getSingleton('adminhtml/session')->getAdvanceforumData())
      {
          $form->setValues(Mage::getSingleton('adminhtml/session')->getAdvanceforumData());
          Mage::getSingleton('adminhtml/session')->setAdvanceforumData(null);
      } elseif (Mage::registry('gallery_data')) {
          $form->setValues(Mage::registry('gallery_data')->getData());
      }

      return parent::_prepareForm();
   }
   
}
