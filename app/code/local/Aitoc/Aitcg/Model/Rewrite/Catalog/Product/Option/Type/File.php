<?php
class Aitoc_Aitcg_Model_Rewrite_Catalog_Product_Option_Type_File extends Mage_Catalog_Model_Product_Option_Type_File
{
    private $_is15 = false;
    
    public function __construct() 
    {
        $this->_is15 = (version_compare(Mage::getVersion(), '1.5.0.0') >= 0) ? true : false;
    }

    /**
     * Validate uploaded file
     *
     * @throws Mage_Core_Exception
     * @return Mage_Catalog_Model_Product_Option_Type_File
     */
    protected function _validateUploadedFile()
    {
        $option = $this->getOption();

        if ( Mage::helper('aitcg/options')->checkAitOption( $option ) )
        {
            $values = Mage::app()->getRequest()->getParam('options');
            $optionValue = $values[$option->getId()];
            $runValidation = ($option->getIsRequire() || $this->_validateValue($option)) && !is_null($optionValue);
            if (!$runValidation) {
                $this->setUserValue(null);
                return $this;
            }
            
            $optionValue = Mage::helper('core')->jsonDecode($optionValue);
            $this->_initFilesystem();

            $model = Mage::getModel('aitcg/image');
            $src = Mage::getBaseDir('media') . DS
                . $model->getFullTempPath() . DS
                . $optionValue['config']['rand'] . '.'
                . Aitoc_Aitcg_Model_Image::FORMAT_PNG;

            $fileName = Mage_Core_Model_File_Uploader::getCorrectFileName(pathinfo(strtolower($src), PATHINFO_FILENAME));
            $dispersion = Mage_Core_Model_File_Uploader::getDispretionPath($fileName);

            $filePath = $dispersion;
            $fileHash = md5(file_get_contents($src));
            $filePath .= DS . $fileHash . '.' . Aitoc_Aitcg_Model_Image::FORMAT_PNG;
            $fileFullPath = $this->getQuoteTargetDir() . $filePath;

            $_width = 0;
            $_height = 0;
            $_fileSize  = 0;
            if (is_readable($src)) {
                $_imageSize = getimagesize($src);
                if ($_imageSize) {
                    $_width = $_imageSize[0];
                    $_height = $_imageSize[1];
                }
                $_fileSize = filesize($src);
            }

            $manager = Mage::getModel('aitcg/image_manager');

            $template_id = $manager->addImage($values[$option->getId()], Mage::app()->getRequest()->getParam('product'), $option->getId(), $option) ;

            $this->setUserValue(array(
                'type'          => 'image/png',
                'title'         => 'custom_product_preview.png',
                'quote_path'    => $this->getQuoteTargetDir(true) . $filePath,
                'order_path'    => $this->getOrderTargetDir(true) . $filePath,
                'fullpath'      => $fileFullPath,
                'size'          => $_fileSize,
                'width'         => $_width,
                'height'        => $_height,
                'secret_key'    => substr($fileHash, 0, 20),

                Aitoc_Aitcg_Helper_Options::OPTION_DATA_KEY => array(
                    'template_id' => $template_id,
                    Aitoc_Aitcg_Model_Sales_Order_Item_Converter::CUSTOMER_IMAGE_META_VERSION_KEY =>
                        Aitoc_Aitcg_Model_Sales_Order_Item_Converter::CUSTOMER_IMAGE_META_VERSION)
            ));

            $path = dirname($fileFullPath);
            $io = new Varien_Io_File();
            if (!$io->isWriteable($path) && !$io->mkdir($path, 0777, true)) {
                Mage::throwException(Mage::helper('catalog')->__("Cannot create writeable directory '%s'.", $path));
            }

            @copy($src, $fileFullPath);

            return $this;
        } else {
            return parent::_validateUploadedFile();
        }

    }

    protected function _validateFile($optionValue)
    {
        $option = $this->getOption();
        if (Mage::helper('aitcg/options')->checkAitOption($option))
        {
            return true;
        }
        return parent::_validateFile($optionValue);
    }

    protected function _validateValue($option)
    {
        $options = $this->getRequest()->getOptions();
        if (isset($options[$option->getId()]) && $options[$option->getId()])
        {
            return true;
        }
        return false;
    }

    /**
     * Return option html
     *
     * @param array $optionInfo
     * @return string
     */
    public function getCustomizedView($optionInfo)
    {
        try {
            if (isset($optionInfo['option_value'])) {
                return $this->_getOptionHtml($optionInfo);
            } elseif (isset($optionInfo['value'])) {
                return $optionInfo['value'];
            }
        } catch (Exception $e) {
            return $optionInfo['value'];
        }
    }

    /**
     * Format File option html
     *
     * @param string|array $optionValue Serialized string of option data or its data array
     * @return string
     * @see Mage_Catalog_Model_Product_Option_Type_File::_getOptionHtml()
     */
    protected function _getOptionHtml($optionValue)
    {
        if ($this->_isCreateOrderRequest())
        {
            return parent::_getOptionHtml($optionValue);
        }

        try {
            $option = $this->getOption();
        } catch (Exception $e) {
            $option = $this->_getOptionByValue($optionValue);
        }

        $value = $this->_getValueOption($optionValue);

        $isTempData = false;
        $data = array();
        if( Mage::helper('aitcg/options')->checkAitOption( $option ) &&  $option->getData("image_template_id") > 0)
        {

            $model = Mage::getModel('aitcg/image');

            $data = $this->_getImageData($model, $value);
            $data = $this->_getAreaData($option, $model, $data);

            $isTempData = true;

        }
        return $this->_sprintOption($value, $optionValue, $option, $data, $isTempData);
    }

    protected function _getOptionByValue($optionInfo)
    {
        if (isset($optionInfo['option_value']))
        {
            $option = Mage::getModel('catalog/product_option')->load($optionInfo['option_id']);
            $product = Mage::getModel('catalog/product')->load($option->getProductId());

            return $product->getOptionById($option->getId());
        }
    }
    
    protected function _sprintOption($value, $optionValue, $option, $data, $isTempData)
    {
    
        $js = "";
        if($isTempData)
        {
            $sBlockName = $this->_getBlockName();
            $js =  Mage::app()->getLayout()
                ->createBlock($sBlockName, null, $data)
                ->setProduct( $option->getProduct() )
                ->setOption( $option )
                ->toHtml();
                
            return $js;
        }
        else
        {
            return $js . sprintf('<a href="%s" target="_blank">%s</a> %s',
                $this->_getOptionDownloadUrl($value['url']['route'], $value['url']['params']),
                Mage::helper('core')->htmlEscape($value['title']),
                $this->_getSizes($optionValue)
            );
        }
    }
    
    protected function _getBlockName()
    {
        $request = Mage::app()->getRequest();
        if( $request->getModuleName() == 'checkout' && $request->getControllerName() == 'cart') {
            //allowing moving/uploading images only at cart
            return 'aitcg/checkout_cart_item_option_cgfile';
        }
        return 'aitcg/checkout_cart_item_option_cgfile_lite';
    }
    
    protected function _getImageData($model, $value)
    {
        $request = Mage::app()->getRequest();
        $data = array();
        if( isset($value['aitcg_data']['template_id']) && $value['aitcg_data']['template_id'] > 0 )
        {                    
            $model->load($value['aitcg_data']['template_id']);
            if(!$model->isEmpty())
            {
                $data = $model->getFullData();
            }
        }
        return $data;
    }
    
    protected function _getAreaData($option, $model, $data = array())
    {
        $request = Mage::app()->getRequest();
        if( $request->getModuleName() == 'checkout' && $request->getControllerName() == 'cart') {
            //allowing moving/uploading images only at cart
           if($this->hasData('quote_item')) {
                $options = $this->getQuoteItem()->getOptions();
            } else {
                $options = $this->getQuoteItemOption()->getItem()->getOptions();
            }
            foreach($options as $buyRequest ) {
                if($buyRequest['code'] == 'info_buyRequest') {
                    break;
                }
            }                    
        }    
        
        if(!isset($data['area_size_x'])) {
                $data['area_size_x'] = $option->getAreaSizeX();
                $data['area_size_y'] = $option->getAreaSizeY();
                $data['area_offset_x']= $option->getAreaOffsetX();
                $data['area_offset_y']= $option->getAreaOffsetY();
        }

        $data["image"] = $model->getMediaImage( $option->getProductId(), $option->getImageTemplateId(), Aitoc_Aitcg_Helper_Data::INTEGRATION_POPUP );

        $this->setData('aitcg_model', $data);

        if(isset($buyRequest))
                $data['buy_request'] = $buyRequest;
        if($model->hasDataChanges()) {
                $model->save();
        }
        return $data;
    }
    
    protected function _getSizes($optionValue)
    {
        $value = $this->_getValueOption($optionValue);
        if (isset($value['width'],$value['height']) && $value['width'] > 0 && $value['height'] > 0) 
        {
                $sizes = $value['width'] . ' x ' . $value['height'] . ' ' . Mage::helper('catalog')->__('px.');
        } else 
        {
                $sizes = '';
        }
        return $sizes;
    }
    
    protected function _getValueOption($optionValue)
    {
        if (isset($optionValue['option_value']))
        {
            $optionValue = $optionValue['option_value'];
        }
        if(is_array($optionValue)) 
        {
            return $optionValue;
        } else 
        {
            try 
            {
                return unserialize($optionValue);
            } catch (Exception $e) {
                return $optionValue;
            }
        }
    }

    protected function _isCreateOrderRequest()
    {
        return Mage::helper('aitcg')->requestIsSuitable(array(
            'checkout/onepage/saveOrder',
            'checkout/multishipping/overviewPost'
        ));
    }
}