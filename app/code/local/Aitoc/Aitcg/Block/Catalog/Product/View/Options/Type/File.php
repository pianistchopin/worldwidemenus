<?php
class Aitoc_Aitcg_Block_Catalog_Product_View_Options_Type_File extends Mage_Catalog_Block_Product_View_Options_Type_File 
{
    protected $total_cpp=0;
    protected function _beforeToHtml()
    {
        $option = $this->getOption();
        $info = $this->getProduct()->getPreconfiguredValues();
        
        if($info) {
            $info = $info->getData('options/' . $option->getId());
        }

        $template_id = 0;
        if(isset($info['aitcg_data']) && $info['aitcg_data']['template_id'] > 0 ) {
            $template_id = $info['aitcg_data']['template_id'];
        } else {
            $sessionData = Mage::getSingleton('aitcg/session')
                ->getData('options_' . $option->getId() . '_file');
            $template_id = intval($sessionData['template_id']);
        }
        
        $data = $this->_getDataTemplate('image', $option, $template_id);
       
        $this->setImage($data["image"]);
        $this->setPreview($data['preview']);
        
        return parent::_beforeToHtml();
    }
    
    protected function _getDataTemplate($model, $option, $template_id)
    {
        $data = array('image' => false, 'preview' => false); 
        $model = Mage::getModel('aitcg/'.$model);
        if($template_id > 0) {
            $model->load( $template_id );
            if($model->hasData()) {
                $data['preview'] = new Varien_Object($model->getFullData( ));
                if($data['preview']['id'] == 0) {
                    $data['preview'] = false;
                }
            }
        }

        $productId  = $option->getProductId();
        $templateId = $option->getImageTemplateId();
        $position   = Mage::getStoreConfig('catalog/aitcg/aitcg_editor_position');
        
        $data["image"] = $model->getMediaImage( $productId, $templateId, $position );
         
        return $data;
        
    }
    
    public function getColorset()
    {
        $id = $this->getOption()->getColorSetId();
        $colorsetModel = Mage::getModel('aitcg/font_color_set');
        $hasId = $colorsetModel->hasId($id);
        if($hasId)
        {
            $colorsetModel  = $colorsetModel->load($id);
            $status = $colorsetModel->getStatus();
            if($status !== '0')
            {
                return $colorsetModel;
            }
        }
        return $colorsetModel->load(Aitoc_Aitcg_Helper_Font_Color_Set::XPATH_CONFIG_AITCG_FONT_COLOR_SET_DFLT);
    }
    
    public function getAllowPredefinedColors()
    {
        $value = $this->getOption()->getAllowPredefinedColors();
        if($value == null)
        {
            return Mage::getStoreConfig('catalog/aitcg/aitcg_font_color_predefine');
        }
        return $value;
    }

    public function getAllowPlaceBehind()
    {
        //TODO: Realize "allow_place_behind" in aitcg product option
        $optValue = $this->getOption()->getAllowPlaceBehind();

        if ($optValue == null) {
            return Mage::getStoreConfig('catalog/aitcg/aitcg_allow_place_behind');
        }

        return $optValue;
    }
    
    public function isObjectDistortionAllowed()
    {
        $optValue = $this->getOption()->getAllowTextDistortion();
        
        if ($optValue == null) {
            $optValue = Mage::getStoreConfig('catalog/aitcg/aitcg_allow_text_distortion'); 
        }
        
        return $optValue;
    }

    public function canEmailToFriend()
    {
        $sendToFriendModel = Mage::registry('send_to_friend_model');
        return $sendToFriendModel && $sendToFriendModel->canEmailToFriend();
    }

    public function getSavePdfUrl($front = 'aitcg')
    {
        if (Mage::app()->getStore()->getConfig('catalog/aitcg/aitcg_enable_svg_to_pdf') == 1)
        {
            return Mage::getUrl($front.'/index/pdf');
        }
        return false;
    }

    public function isMageGtEq19()
    {
        return Mage::helper('aitoc_common')->isMageGtEq19();
    }
    public function setTotalCppOption($total)
    {
        $this->total_cpp=$total;
    }
    public function getTotalCppOption()
    {
        return  $this->total_cpp;
    }
    public function getMaskCatIdsfromOption(){

        $_options = Mage::helper('core')->decorateArray($this->getProduct()->getOptions());
        $ids=array();
        if (count($_options)):
            foreach($_options as $_option) {
                if ($this->getOption()->getId() == $_option->getCppOptionId() && $_option->getOptionForSection()=="shape_mask" )// && $_option->getType() == Mage_Catalog_Model_Product_Option::OPTION_TYPE_DROP_DOWN )
                    //if($this->getOption()->getId() == $_option->getCppOptionId() && empty( $_option->getOptionForMask()) )// && $_option->getType() == Mage_Catalog_Model_Product_Option::OPTION_TYPE_DROP_DOWN )
                {
                    if ($_option->getType() == Mage_Catalog_Model_Product_Option::OPTION_TYPE_DROP_DOWN)
                    {
                        foreach ($_option->getValues() as $_value) {
                            $ids[]=$_value->getMaskCategory();
                        }
                    }

                }
            }
            endif;
        return implode(",",$ids);
    }

    public function getOptions(){
        $_options = Mage::helper('core')->decorateArray($this->getProduct()->getOptions());
        $cppOptions=array();
        if (count($_options)):
            foreach($_options as $_option){
                if($this->getOption()->getId() == $_option->getCppOptionId() && empty($_option->getOptionForSection()) )// && $_option->getType() == Mage_Catalog_Model_Product_Option::OPTION_TYPE_DROP_DOWN )
                //if($this->getOption()->getId() == $_option->getCppOptionId() && empty( $_option->getOptionForMask()) )// && $_option->getType() == Mage_Catalog_Model_Product_Option::OPTION_TYPE_DROP_DOWN )
                {
                    //var_dump($_option->getData());
                   // exit;
                    $option=array(
                        'id'=>$_option->getId(),
                        'is_require'=>$_option->getIsRequire(),
                        'cls'=>($_option->getIsRequire())?'required-entry':'',
                        'cpp_option_id'=>$_option->getCppOptionId(),
                        'is_one_of_cost'=>$_option->getIsOneOfCost(),
                        'input_type'=>$_option->getType(),
                        'title'=>$this->escapeHtml($_option->getTitle()),
                        'options'=>array(),
                        'note'=>'',
                    );
                    if ($_option->getType() == Mage_Catalog_Model_Product_Option::OPTION_TYPE_DROP_DOWN)
                    {
                        $optionsHtml = '<option value="">' . Mage::helper('aitcg')->__('Select...') . '</option>';
                        foreach ($_option->getValues() as $_value) {
                            $priceStr = $this->_formatPrice(array(
                                'is_percent'    => ($_value->getPriceType() == 'percent'),
                                'pricing_value' => $_value->getPrice(($_value->getPriceType() == 'percent'))
                            ), false);
                            $optionsHtml .= '<option value="' .  $_value->getOptionTypeId() . '">' . $_value->getTitle() . ' ' . $priceStr . ''
                                . '</option>';

                         }
                        foreach ($_option->getValues() as $_value) {
                            $priceStr = $this->_formatPrice(array(
                                'is_percent'    => ($_value->getPriceType() == 'percent'),
                                'pricing_value' => $_value->getPrice(($_value->getPriceType() == 'percent'))
                            ), false);
                            $option['options'][]=array('id'=>  $_value->getOptionTypeId(),'title'=>$_value->getTitle() . ' ' . $priceStr . '');

                        }
                        $option['input_html']=$optionsHtml;//$select->getHtml();

                    }
                    elseif ($_option->getType() == Mage_Catalog_Model_Product_Option::OPTION_TYPE_FIELD)
                    {
                        $option['input_html']=$this->escapeHtml($this->getDefaultValue());
                        $option['cls']=$option['cls'].($_option->getMaxCharacters() ? ' validate-length maximum-length-'.$_option->getMaxCharacters() : '');
                        $option['note']=$_option->getMaxCharacters()?(Mage::helper('catalog')->__('Maximum number of characters:').'<strong>'.$_option->getMaxCharacters().'</strong>'):'';
                        $extra_label=$this->_formatPrice(array(
                            'is_percent'    => ($_option->getPriceType() == 'percent'),
                            'pricing_value' => $_option->getPrice($_option->getPriceType() == 'percent')
                        ));
                        $option['extra_label']= $extra_label;
                    }
                    elseif($_option->getType() == Mage_Catalog_Model_Product_Option::OPTION_TYPE_FILE){
                        $option['cls']=$option['cls'].($_option->getMaxCharacters() ? ' validate-length maximum-length-'.$_option->getMaxCharacters() : '');
                        $extra_label=$this->_formatPrice(array(
                            'is_percent'    => ($_option->getPriceType() == 'percent'),
                            'pricing_value' => $_option->getPrice($_option->getPriceType() == 'percent')
                        ));
                        $option['extra_label']= $extra_label;
                        $option['file_extension']= !empty($_option->getFileExtension())?explode(",",strtolower($_option->getFileExtension())):array();
                        if(in_array('pdf',$option['file_extension'])){
                            $option['note']=Mage::helper('aitcg')->__("PDF format with 300dpi."); //Mage::helper('aitcg')->__("please upload a 300dpi PDF file.");
                        }
                        $option['file_ext']= !empty($_option->getFileExtension())?$_option->getFileExtension():'';


                    }

                    $cppOptions[$_option->getId()]=$option;
                }
            }//exit;
        endif;
        $this->setTotalCppOption(count($cppOptions));
        return Mage::helper('core')->jsonEncode($cppOptions);
    }
    public function getIsEngraving(){
        $product  = $this->getProduct();
        if(!empty( $product) && $product instanceof Mage_Catalog_Model_Product && $product->hasData('engraving')) {

            return $product->getData('engraving');
        }

        return 0;
    }
    public function getSpineHtml()
    {
        $return = '';
        $_product  = $this->getProduct();
        if (!empty( $_product) && $_product->getTypeId()=='configurable') {
            $_attributes = $_product ->getTypeInstance(true)->getConfigurableAttributes($_product);
            foreach ($_attributes as $_attribute) {
                $productAttribute = $_attribute->getProductAttribute();
                if($productAttribute->getAttributeCode() && $productAttribute->getAttributeCode()=="color") {
                    $prices = $_attribute->getPrices();
                    if (is_array($prices)) {
                        foreach ($prices as $value) {
                            $return .= '<option value="' . $value['value_index'] . '">' . $value['label'] . '</option>';
                        }
                    }
                }
            }
        }
        return $return;
    }
    public function getProductSize()
    {
	    $product  = $this->getProduct();
	    if(!empty( $product) && $product instanceof Mage_Catalog_Model_Product) {

		    $size = $product->getAttributeText('size');
	    	return $size;
	    }

	    return 'A3';
    }

	public function getMaskCustomOption($cppId = 0)
	{
		$product  = $this->getProduct();
		if(!empty( $product) && $product instanceof Mage_Catalog_Model_Product) {
			foreach($product->getOptions() as $option) {
				//if( $option->getData('cpp_option_id') == $cppId && $option->getData('option_for_mask') == 1) {
                if( $option->getData('cpp_option_id') == $cppId && $option->getData('option_for_section') == "shape_mask") {

                    return $option->getId();
					break;
				}
			}
		}

		return 0;
	}
    public function getFoilTextCustomOption($cppId = 0)
    {
        $product  = $this->getProduct();
        if(!empty( $product) && $product instanceof Mage_Catalog_Model_Product) {
            foreach($product->getOptions() as $option) {
                if( $option->getData('cpp_option_id') == $cppId && $option->getData('option_for_section')=="foil_text") {
                    return $option->getId();
                    break;
                }
            }
        }
        return 0;
    }

    public function getFoilLogoCustomOption($cppId = 0)
    {
        $product  = $this->getProduct();
        if(!empty( $product) && $product instanceof Mage_Catalog_Model_Product) {
            foreach($product->getOptions() as $option) {
                if( $option->getData('cpp_option_id') == $cppId  && $option->getData('option_for_section')=="foil_logo") {
                    return $option->getId();
                    break;
                }
            }
        }
        return 0;
    }
    public function getPredefinedCustomOptionOpts($cppId = 0)
    {
        $opts=array();
        $product  = $this->getProduct();
        if(!empty( $product) && $product instanceof Mage_Catalog_Model_Product) {
            foreach($product->getOptions() as $option) {
                if( $option->getData('cpp_option_id') == $cppId  && $option->getData('option_for_section')=="digital_image" && $option->getType() == Mage_Catalog_Model_Product_Option::OPTION_TYPE_FILE) {
                    $opts['digital_image']=$option->getId();
                }
                if( $option->getData('cpp_option_id') == $cppId  && $option->getData('option_for_section')=="cover_image" && $option->getType() == Mage_Catalog_Model_Product_Option::OPTION_TYPE_DROP_DOWN) {
                    $opts['cover_image']=$option->getId();
                }
            }
        }

        return $opts;

    }
    public function getCoverImageCustomOptionOpts($cppId = 0)
    {
        $opts=array();
        $product  = $this->getProduct();
        if(!empty( $product) && $product instanceof Mage_Catalog_Model_Product) {
            foreach($product->getOptions() as $option) {
                if( $option->getData('cpp_option_id') == $cppId  && $option->getData('option_for_section')=="cover_image" && $option->getType() == Mage_Catalog_Model_Product_Option::OPTION_TYPE_DROP_DOWN) {
                    $opts['cover_image']=$option->getId();
                    break;
                }
            }
        }

        return $opts;
    }
}
