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


class FME_Photogallery_Block_Adminhtml_Photogallery_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
  protected function _prepareForm()
  {
    $form = new Varien_Data_Form();
    $this->setForm($form);
    $fieldset = $form->addFieldset('photogallery_form', array('legend'=>Mage::helper('photogallery')->__('Item information')));

    $mytblGallery = Mage::getModel('photogallery/gallery')->getCollection();
    
    $mytblGallery->addFieldToSelect('gallery_id');       
    $mytblGallery->addFieldToSelect('gallery_title');
    
     
    $galleryCollection[] = array(
      "value"    =>  0,
      "label"    =>  Mage::helper('photogallery')->__('No Parent'),
      "selected" => 'selected');
      
    foreach ($mytblGallery as $item)
    {
      $galleryCollection[] = array(
      "value"    =>  $item->getGalleryId(),
      "label"    =>  $item->getGalleryTitle(),);
    }


     
    $fieldset->addField(
        'gal_name', 'text', array(
        'label'     => Mage::helper('photogallery')->__('Photogallery Name'),
        //'class'     => 'validate-alpha',
        'required'  => true,
        'name'      => 'gal_name',
        )
    );

    
     $fieldset->addField(
         'parent_gallery_id', 'select', array(
         'label'     => Mage::helper('photogallery')->__('Parent Gallery'),
         'name'      => 'parent_gallery_id',
      
         'tabindex' => 4,
         'values'    => $galleryCollection,
         )
     );
    
    $fieldset->addField(
        'show_in', 'select', array(
        'label'     => Mage::helper('photogallery')->__('Display Photogallery At'),
        'name'      => 'show_in',
        'values'    => array(
            array(
                'value'     => 1,
                'label'     => Mage::helper('photogallery')->__('Photogallery Page only'),
            ),
            array(
                'value'     => 2,
                'label'     => Mage::helper('photogallery')->__('Product Page Only'),
            ),
            array(
                'value'     => 3,
                'label'     => Mage::helper('photogallery')->__('Both in Product and Photogallery pages'),
            ),
        ),
        )
    );
            
    //$dateFormatIso = Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT);
    //  
    //$fieldset->addField('gdate', 'date', array(
    //	'name'   => 'gdate',
    //	'label'  => Mage::helper('photogallery')->__('Date'),
    //	'title'  => Mage::helper('photogallery')->__('Date'),
    //	'image'  => $this->getSkinUrl('images/grid-cal.gif'),
    //	'input_format' => Varien_Date::DATE_INTERNAL_FORMAT,
    //	'format'       => $dateFormatIso,
    //	'required'  => true
    //));
    //
    
    $fieldset->addField(
        'store_id', 'multiselect', array(
        'name'      => 'stores[]',
        'label'     => Mage::helper('photogallery')->__('Store View'),
        'title'     => Mage::helper('photogallery')->__('Store View'),
        'required'  => true,
        'values'    => Mage::getSingleton('adminhtml/system_store')->getStoreValuesForForm(false, true)
        )
    );
    
    $fieldset->addField(
        'gorder', 'text', array(
        'label'     => Mage::helper('photogallery')->__('Order'),        
        'required'  => false,
        'name'      => 'gorder',
        )
    );
    
    

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
        'description', 'editor', array(
          'name'      => 'description',
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
    } elseif (Mage::registry('photogallery_data')) {
        $form->setValues(Mage::registry('photogallery_data')->getData());
    }
    
      return parent::_prepareForm();
  }
  
   
}
