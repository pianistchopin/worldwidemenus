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
 * @author     Shahzad Hussain <shehzad.cs@gmail.com>
 */
class FME_Photogallery_Block_Adminhtml_Photogallery_Edit_Tab_Images extends Mage_Adminhtml_Block_Widget
{
    protected $_uploaderType = 'uploader/multiple';
    protected function _prepareForm()
    {
    $data = $this->getRequest()->getPost();
        $form = new Varien_Data_Form();
        $form->setValues($data);
        $this->setForm($form);
        
        return parent::_prepareForm();
    }
    
    public function images()
    {
               $test = Mage::registry('photogallery_img');
               $img_data = $test->getData();
               return $img_data; 
    }
    
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('photogallery/blank.phtml');
        $this->setId('photogallery_content');
        $this->setHtmlId('photogallery_content');
    }
    
    protected function _prepareLayout()
    {
         if(Mage::getVersion() >= '1.9.3'): 
         $this->setChild(
             'uploader',
             $this->getLayout()->createBlock($this->_uploaderType)
         );

        $this->getUploader()->getUploaderConfig()
            ->setFileParameterName('image')
            ->setTarget(Mage::getModel('adminhtml/url')->addSessionParam()->getUrl('*/*/image'));

        $browseConfig = $this->getUploader()->getButtonConfig();
        $browseConfig
            ->setAttributes(
                array(
                'accept' => $browseConfig->getMimeTypesByExtensions('gif, png, jpeg, jpg')
                )
            );
        else:
            $this->setChild(
                'uploader',
                $this->getLayout()->createBlock('adminhtml/media_uploader')
            );

        $this->getUploader()->getConfig()
            ->setUrl(Mage::getModel('adminhtml/url')->addSessionParam()->getUrl('*/*/image'))
            ->setFileField('image')
            ->setFilters(
                array(
                'images' => array(
                    'label' => Mage::helper('adminhtml')->__('Images (.gif, .jpg, .png)'),
                    'files' => array('*.gif', '*.jpg','*.jpeg', '*.png')
                )
                )
            );
        
         $this->setChild(
             'delete_button',
             $this->getLayout()->createBlock('adminhtml/widget_button')
                ->addData(
                    array(
                    'id'      => '{{id}}-delete',
                    'class'   => 'delete',
                    'type'    => 'button',
                    'label'   => Mage::helper('adminhtml')->__('Remove'),
                    'onclick' => $this->getJsObjectName() . '.removeFile(\'{{fileId}}\')'
                    )
                )
         ); 
        endif;    
            
        return parent::_prepareLayout();
    }

    /**
     * Retrive uploader block
     *
     * @return Mage_Adminhtml_Block_Media_Uploader
     */
    public function getUploader()
    {
        return $this->getChild('uploader');
    }

    /**
     * Retrive uploader block html
     *
     * @return string
     */
    public function getUploaderHtml()
    {
        return $this->getChildHtml('uploader');
    }

    public function getJsObjectName()
    {
        return $this->getHtmlId() . 'JsObject';
    }

    public function getAddImagesButton()
    {
        return $this->getButtonHtml(
            Mage::helper('catalog')->__('Add New Images'),
            $this->getJsObjectName() . '.showUploader()',
            'add',
            $this->getHtmlId() . '_add_images_button'
        );
    }

    public function getImagesJson()
    {
         //echo "<pre>"; 
        $polje = $this->images();
        if(is_array($polje)) {
            $value['images'] = $polje;
           // print_r($value['images']);
            if(count($value['images'])>0) {
                    foreach ($value['images'] as &$image) {
                        $image['url'] = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA).'gallery/galleryimages'.$image['img_name']; 
                        $image['value_id'] = $image['img_id'];
                        unset($image['img_id']);
                        $image['file'] = $image['img_name'];
                        unset($image['img_name']);
                        $image['label'] = $image['img_label'];
                        unset($image['img_label']);
                        $image['alt_text'] = $image['alt_text'];
                       // unset($image['alt_text']);
                        $image['description'] = $image['img_description'];
                        unset($image['img_description']);
                        $image['position'] = $image['img_order'];
                        unset($image['img_order']);
                    }

                     //print_r(Zend_Json::encode($value['images']));
                     //exit();
                    return Zend_Json::encode($value['images']);
            }

            return '[]';
        }
    }
    public function getImagesValuesJson()
    {
        $values = array();

        return Zend_Json::encode($values);
    }


    /**
     * Enter description here...
     *
     * @return array
     */
    public function getMediaAttributes()
    {

    }
    
    public function getImageTypes()
    {
        $type = array();
        $type['gallery']['label'] = "gallery";
        $type['gallery']['field'] = "gallery";
        
        $imageTypes = array();

        return $type;
    }
    
    public function getImageTypesJson()
    {
        return Zend_Json::encode($this->getImageTypes());
    }
    
    public function getCustomRemove()
    {
        return $this->setChild(
            'delete_button',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->addData(
                    array(
                    'id'      => '{{id}}-delete',
                    'class'   => 'delete',
                    'type'    => 'button',
                    'label'   => Mage::helper('adminhtml')->__('Remove'),
                    'onclick' => $this->getJsObjectName() . '.removeFile(\'{{fileId}}\')'
                    )
                )
        );
    }
    
    public function getDeleteButtonHtml()
    {
        return $this->getChildHtml('delete_button');
    }

    public function getCustomValueId()
    {
        return $this->setChild(
            'value_id',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->addData(
                    array(
                    'id'      => '{{id}}-value',
                    'class'   => 'value_id',
                    'type'    => 'text',
                    'label'   => Mage::helper('adminhtml')->__('ValueId'),
                    )
                )
        );
    }
    
    public function getValueIdHtml()
    {
        return $this->getChildHtml('value_id');
    }
    
}
