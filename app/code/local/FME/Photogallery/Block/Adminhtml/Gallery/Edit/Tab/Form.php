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


class FME_Photogallery_Block_Adminhtml_Gallery_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
  protected function _prepareForm()
  {
    $form = new Varien_Data_Form();
    $this->setForm($form);
    $fieldset = $form->addFieldset('gallery_form', array('legend'=>Mage::helper('photogallery')->__('Album information')));
     
    $fieldset->addField(
        'gallery_title', 'text', array(
        'label'     => Mage::helper('photogallery')->__('Albums Name'),
        'required'  => true,
        'name'      => 'gallery_title',
        )
    );

    $fieldset->addField(
        'gallery_identifier', 'text', array(
        'label'     => Mage::helper('photogallery')->__('URL Identifier'),
        'class'     => 'validate-identifier',
        'required'  => true,
        'name'      => 'gallery_identifier',
        )
    );

    $fieldset->addField(
        'main_images', 'image', array(
        'label'     => Mage::helper('photogallery')->__('Album Title Image'),
        'required'  => false,
        'name'      => 'main_images',
        )
    );

    if (!Mage::app()->isSingleStoreMode()) {
        $fieldset->addField(
            'store_id', 'multiselect', array(
            'name' => 'stores[]',
            'label' => Mage::helper('photogallery')->__('Store View'),
            'title' => Mage::helper('photogallery')->__('Store View'),
            'required' => true,
            'values' => Mage::getSingleton('adminhtml/system_store')
                         ->getStoreValuesForForm(false, true),
            )
        );
    }
    else {
        $fieldset->addField(
            'store_id', 'hidden', array(
            'name' => 'stores[]',
            'value' => Mage::app()->getStore(true)->getId()
            )
        );
    }
    


    
    
    $fieldset->addField(
        'status', 'select', array(
        'label'     => Mage::helper('photogallery')->__('Status'),
        'name'      => 'status',
        'values'    => array(
        array(
                'value'     => 1,
                'label'     => Mage::helper('photogallery')->__('Enabled'),
            ),
            array(
                'value'     => 2,
                'label'     => Mage::helper('photogallery')->__('Disabled'),
            ),
        ),
        )
    );

    $fieldset->addField(
        'gallery_description', 'editor', array(
          'name'      => 'gallery_description',
          'label'     => Mage::helper('photogallery')->__('Description'),
          'title'     => Mage::helper('photogallery')->__('Description'),
          'style'     => 'width:500px; height:500px;',
          'wysiwyg'   => false,
          'required'  => true,
        )
    );
     
     

    if (Mage::getSingleton('adminhtml/session')->getPhotogalleryData())
    {
        $form->setValues(Mage::getSingleton('adminhtml/session')->getPhotogalleryData());
        Mage::getSingleton('adminhtml/session')->setPhotogalleryData(null);
    } elseif (Mage::registry('gallery_data')) {
        $form->setValues(Mage::registry('gallery_data')->getData());
    }
    
      return parent::_prepareForm();
  }
  
   
}
