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

class FME_Photogallery_Block_Adminhtml_Photogalleryblocks_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{

 
  protected function _prepareForm()
  {
    $form = new Varien_Data_Form();
    $this->setForm($form);
    $fieldset = $form->addFieldset('photogalleryblocks_form', array('legend'=>Mage::helper('photogallery')->__('Photogallery Block information')));
    
    $fieldset->addField(
        'block_title', 'text', array(
        'label'     => Mage::helper('photogallery')->__('Title'),
        'class'     => 'required-entry',
        'required'  => true,
        'name'      => 'block_title',
        )
    );
    
    $fieldset->addField(
        'block_identifier', 'text', array(
        'name'      => 'block_identifier',
        'label'     => Mage::helper('photogallery')->__('Identifier'),
        'title'     => Mage::helper('photogallery')->__('Identifier'),
        'required'  => true,
        'class'     => 'validate-identifier',
        //'after_element_html' => '<p class="nm"><small>' . Mage::helper('faqs')->__('(eg: domain.com/faqs/identifier)') . '</small></p>',
        )
    );
    
    $fieldset->addField(
        'block_status', 'select', array(
        'label'     => Mage::helper('photogallery')->__('Status'),
        'name'      => 'block_status',
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
    
    
    //try{
    //$config = Mage::getSingleton('cms/wysiwyg_config')->getConfig(
    //	array(
    //			'add_widgets' => false,
    //			'add_variables' => false,
    //		)
    //	);
    //$config->setData(Mage::helper('photogallery')->recursiveReplace(
    //			'/photogallery/',
    //			'/'.(string)Mage::app()->getConfig()->getNode('admin/routers/adminhtml/args/frontName').'/',
    //			$config->getData()
    //		)
    //	);
    //}
    //catch (Exception $ex){
    //$config = null;
    //}
    
    //$fieldset->addField('block_content', 'editor', array(
    //  'name'      => 'block_content',
    //  'label'     => Mage::helper('photogallery')->__('Content'),
    //  'title'     => Mage::helper('photogallery')->__('Content'),
    //  'style'     => 'width:760px; height:500px;',
    //  'config'    => $config	  
    //));
    //make Wysiwyg Editor integrate in the form
    $wysiwygConfig = Mage::getSingleton('cms/wysiwyg_config')->getConfig(
        array('tab_id' => 'form_section')
    );
        $wysiwygConfig["files_browser_window_url"] = Mage::getSingleton('adminhtml/url')->getUrl('adminhtml/cms_wysiwyg_images/index');
        $wysiwygConfig["directives_url"] = Mage::getSingleton('adminhtml/url')->getUrl('adminhtml/cms_wysiwyg/directive');
        $wysiwygConfig["directives_url_quoted"] = Mage::getSingleton('adminhtml/url')->getUrl('adminhtml/cms_wysiwyg/directive');
        $wysiwygConfig["widget_window_url"] = Mage::getSingleton('adminhtml/url')->getUrl('adminhtml/widget/index');
    $wysiwygConfig["files_browser_window_width"] = (int) Mage::getConfig()->getNode('adminhtml/cms/browser/window_width');
    $wysiwygConfig["files_browser_window_height"] = (int) Mage::getConfig()->getNode('adminhtml/cms/browser/window_height');
        $plugins = $wysiwygConfig->getData("plugins");
        $plugins[0]["options"]["url"] = Mage::getSingleton('adminhtml/url')->getUrl('adminhtml/system_variable/wysiwygPlugin');
        $plugins[0]["options"]["onclick"]["subject"] = "MagentovariablePlugin.loadChooser('".Mage::getSingleton('adminhtml/url')->getUrl('adminhtml/system_variable/wysiwygPlugin')."', '{{html_id}}');";
        $plugins = $wysiwygConfig->setData("plugins", $plugins);
     
       $contentField = $fieldset->addField(
           'block_content', 'editor', array(
           'name'      => 'block_content',
           'label'     => Mage::helper('photogallery')->__('Content'),
           'title'     => Mage::helper('photogallery')->__('Content'),
           'style'     => 'height:20em; width:50em;',
           'required'  => true,
           'config'    => $wysiwygConfig
           )
       );
      
       // Setting custom renderer for content field to remove label column
        $renderer = $this->getLayout()->createBlock('adminhtml/widget_form_renderer_fieldset_element')
                    ->setTemplate('cms/page/edit/form/renderer/content.phtml');
        $contentField->setRenderer($renderer);

      
      
      if (Mage::getSingleton('adminhtml/session')->getPhotogalleryblocksData())
      {
          $form->setValues(Mage::getSingleton('adminhtml/session')->getPhotogalleryblocksData());
          Mage::getSingleton('adminhtml/session')->setPhotogalleryblocksData(null);
      } elseif (Mage::registry('photogallery_block_data')) {
          $form->setValues(Mage::registry('photogallery_block_data')->getData());
      }

      return parent::_prepareForm();
  }
}
