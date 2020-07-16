<?php

/**
 * MageWorx
 * CustomOptions Extension
 * 
 * @category   MageWorx
 * @package    MageWorx_CustomOptions
 * @copyright  Copyright (c) 2015 MageWorx (http://www.mageworx.com/)
 */

class MageWorx_CustomOptions_Block_Catalog_Product_View_Options_Type_Select extends Mage_Catalog_Block_Product_View_Options_Type_Select {

    static $isFirstOption = true;

    public function getValuesHtml() {
        $_option = $this->getOption();        
        $helper = Mage::helper('mageworx_customoptions');
        $displayQty = $helper->canDisplayQtyForOptions();
        $outOfStockOptions = $helper->getOutOfStockOptions();
        $enabledInventory = $helper->isInventoryEnabled();
        $enabledDependent = $helper->isDependentEnabled();
        $enabledSpecialPrice = $helper->isSpecialPriceEnabled();
        $hideDependentOption = $helper->hideDependentOption();
        $displayLowStockMessage = $helper->canDisplayLowStockMessage();
        $lowStockValue = $helper->getLowStockValue();
        
        $buyRequest = false;
        $quoteItemId = 0;
        if ($helper->isQntyInputEnabled() && Mage::app()->getRequest()->getControllerName()!='product') {
            $quoteItemId = (int) $this->getRequest()->getParam('id');
            if ($quoteItemId) {                
                if (Mage::app()->getStore()->isAdmin()) {
                    $item = Mage::getSingleton('adminhtml/session_quote')->getQuote()->getItemById($quoteItemId);
                } else {
                    $item = Mage::getSingleton('checkout/cart')->getQuote()->getItemById($quoteItemId);           
                }
                if ($item) {
                    $buyRequest = $item->getBuyRequest();
                    if ($_option->getType() != Mage_Catalog_Model_Product_Option::OPTION_TYPE_CHECKBOX) {
                        $optionQty = $buyRequest->getData('options_' . $_option->getId() . '_qty');
                        $_option->setOptionQty($optionQty);
                    }
                }
            }
        }
        
        $optionJs = '';
        if (!Mage::app()->getStore()->isAdmin()) $optionJs .= 'opConfig.reloadPrice();';
        if ($_option->getIsXQtyEnabled()) $optionJs .= ' optionSetQtyProduct.setQty();';
        if ($_option->getIsDependentLQty()) $optionJs .= ' optionSetQtyProduct.checkLimitQty('. $_option->getId() .', this);';
        if ($_option->getIsParentLQty()) $optionJs .= ' optionSetQtyProduct.setLimitQty(this);';
        
        $qtyInputType = ($helper->isProductQtyDecimal($this->getProduct())) ? 'type="number" pattern="[0-9]+([\,|\.][0-9]+)?" step="0.01"' : 'type="number" pattern="[0-9]+([\,|\.][0-9]+)?"' ;
        
        if ($_option->getType()==Mage_Catalog_Model_Product_Option::OPTION_TYPE_DROP_DOWN || $_option->getType()==Mage_Catalog_Model_Product_Option::OPTION_TYPE_MULTIPLE
            || $_option->getType()==MageWorx_CustomOptions_Model_Catalog_Product_Option::OPTION_TYPE_SWATCH || $_option->getType()==MageWorx_CustomOptions_Model_Catalog_Product_Option::OPTION_TYPE_MULTISWATCH) {
            
            $require = '';
            if ($_option->getType()==MageWorx_CustomOptions_Model_Catalog_Product_Option::OPTION_TYPE_SWATCH || $_option->getType()==MageWorx_CustomOptions_Model_Catalog_Product_Option::OPTION_TYPE_MULTISWATCH) {
                $require = ' hidden';
            }
            if ($_option->getIsRequire(true)) {                
                if ($enabledDependent && $_option->getIsDependent()) $require .= ' required-dependent'; else $require .= ' required-entry';
            }
            
            $extraParams = ($enabledDependent && $_option->getIsDependent()?' disabled="disabled"':'');
            $select = $this->getLayout()->createBlock('core/html_select')
                    ->setData(array(
                        'id' => 'select_' . $_option->getId(),
                        'class' => $require . ' product-custom-option' . ($_option->getType()==MageWorx_CustomOptions_Model_Catalog_Product_Option::OPTION_TYPE_SWATCH || $_option->getType()==MageWorx_CustomOptions_Model_Catalog_Product_Option::OPTION_TYPE_MULTISWATCH ? ' swatch' : '')
                    ));
            if ($_option->getType()==Mage_Catalog_Model_Product_Option::OPTION_TYPE_DROP_DOWN || $_option->getType()==MageWorx_CustomOptions_Model_Catalog_Product_Option::OPTION_TYPE_SWATCH) {
                $select->setName('options[' . $_option->getid() . ']')->addOption('', $this->__('-- Please Select --'));
            } else {
                $select->setName('options[' . $_option->getid() . '][]');
                $select->setClass('multiselect' . $require . ' product-custom-option');
            }
            
            $imagesHtml = '';
            $showImgFlag = false;
            
            foreach ($_option->getValues() as $_value) {
                $qty = '';
                $customoptionsQty = $helper->getCustomoptionsQty($_value->getCustomoptionsQty(), $_value->getSku(), $this->getProduct()->getId(), $_value->getExtra(), $_value, $quoteItemId);

                if ($enabledInventory && $outOfStockOptions==1 && ((is_numeric($customoptionsQty) && $customoptionsQty==0) || $_value->getIsOutOfStock())) continue;
                
                $selectOptions = array();
                $disabled = '';
                if ($enabledInventory && $customoptionsQty<=0 && $customoptionsQty!=='' && ($outOfStockOptions==0 || $outOfStockOptions==4)) {
                    $selectOptions['disabled'] = $disabled = 'disabled';
                }
                
                $selected = $this->_isValueSelectedByQueryStringConfig($_value->getOptionTypeId());
                if ($_value->getDefault()==1 && !$disabled) {
                    if (!$enabledDependent || !$_option->getIsDependent()) {
                        $selected = true;
                    }
                }
                if ($selected) {
                    $selectOptions['selected'] = 'selected';
                }

                if ($enabledInventory) {
                    if ($displayQty == 1 && $customoptionsQty > 0 && $customoptionsQty >= $lowStockValue) $qty = ' (' . $customoptionsQty . ')';
                    if ($displayQty == 2 && $customoptionsQty > 0) $qty = ' (' . $customoptionsQty . ')';
                    if ($displayQty == 3 && $customoptionsQty > 0 && $customoptionsQty < $lowStockValue) $qty = ' (' . $customoptionsQty . ')';
                    
                    if ($customoptionsQty <= 0 && $outOfStockOptions==4 && $customoptionsQty !== NULL && $customoptionsQty!=='') $qty .= ' (' .$helper->__('Out of stock') . ')';
                    if ($displayLowStockMessage && $lowStockValue > 0 && $customoptionsQty > 0 && $customoptionsQty <= $lowStockValue) $qty .= ' (' . $helper->__('Low stock') . ')';
                }

                $priceStr = $_option->getFormattedOptionPrice($_value);

                // swatch
                if ($_option->getType()==MageWorx_CustomOptions_Model_Catalog_Product_Option::OPTION_TYPE_SWATCH || $_option->getType()==MageWorx_CustomOptions_Model_Catalog_Product_Option::OPTION_TYPE_MULTISWATCH) {
                    $images = $_value->getImages();
                    
                    if (count($images)>0) {
                        $showImgFlag = true;
                        if ($disabled) {
                            $onClick = 'return false;';
                            $className = 'swatch swatch-disabled';
                        } else {
                            $onClick = 'optionSwatch.select('. $_option->getId() .', '.$_value->getOptionTypeId().');';
                            if ($_option->getIsDependentLQty()) $onClick .= ' optionSetQtyProduct.checkLimitQty('. $_option->getId() .', '. $_value->getOptionTypeId() .');';
                            $onClick .= ' return false;';
                            $className = 'swatch';
                            if (!$hideDependentOption && $_option->getIsDependent()) $className .= ' swatch-disabled';
                        }
                        
                        if ($buyRequest) $optionValueQty = $buyRequest->getData('options_'.$_option->getId().'_'.$_value->getOptionTypeId().'_qty'); else $optionValueQty = 0;
                        
                        $image = $images[0];
                        if ($image['source']==1) { // file
                            $arr = $helper->getImgData($image['image_file']);
                            if (isset($arr['big_img_url'])) {
                                $title = $this->htmlEscape($_value->getTitle() . ($priceStr ? ' - ' . strip_tags(str_replace(array('<s>', '</s>'), array('(', ')'), $priceStr)): ''));
                                $imagesHtml .= '<li id="swatch_'. $_value->getOptionTypeId() .'" class="'. $className .'">'.
                                        '<a href="'.$arr['big_img_url'].'" onclick="'. $onClick .'">'.
                                            '<img src="'.$arr['url'].'" title="'. $title .'" alt="'. $title .'" class="swatch small-image-preview v-middle" />'.
                                        '</a>';

                                $imagesHtml .= $this->getSwatchTitleHtml($_option, $_value);
                                $imagesHtml .= $this->getSwatchDescriptionHtml($_option, $_value);
                                $imagesHtml .= (($helper->isQntyInputEnabled() && $_option->getQntyInput() && $_option->getType()==MageWorx_CustomOptions_Model_Catalog_Product_Option::OPTION_TYPE_MULTISWATCH) ? '<div><label><b>'. $helper->getDefaultOptionQtyLabel() .'</b> <input '.$qtyInputType.' class="qty'. ($selected ? ' validate-greater-than-zero' : '') .'" value="'.$optionValueQty.'" maxlength="12" min="0" id="options_'.$_option->getId().'_'.$_value->getOptionTypeId().'_qty" name="options_'.$_option->getId().'_'.$_value->getOptionTypeId().'_qty" onchange="'. $optionJs .'" onKeyPress="if(event.keyCode==13){'. $optionJs .'}" '. ($selected?$disabled:'disabled') .'></label></div>' : '') .
                                    '</li>';
                            }
                        } elseif ($image['source']==2) { // color
                            $imagesHtml .= '<li id="swatch_'. $_value->getOptionTypeId() .'" class="'. $className .'">'.
                                        '<a href="#" onclick="'. $onClick .'">'.
                                            '<div class="swatch container-swatch-color small-image-preview v-middle" title="'. $this->htmlEscape($_value->getTitle() . ($priceStr ? ' - ' . strip_tags(str_replace(array('<s>', '</s>'), array('(', ')'), $priceStr)): '')) .'">'.
                                                '<div class="swatch-color" style="background:'. $image['image_file'] .';">&nbsp;</div>'.
                                            '</div>'.
                                        '</a>';
                            $imagesHtml .= $this->getSwatchTitleHtml($_option, $_value);
                            $imagesHtml .= $this->getSwatchDescriptionHtml($_option, $_value);
                            $imagesHtml .= (($helper->isQntyInputEnabled() && $_option->getQntyInput() && $_option->getType()==MageWorx_CustomOptions_Model_Catalog_Product_Option::OPTION_TYPE_MULTISWATCH) ? '<div><label><b>'. $helper->getDefaultOptionQtyLabel() .'</b> <input '.$qtyInputType.' class="qty'. ($selected ? ' validate-greater-than-zero' : '') .'" value="'.$optionValueQty.'" maxlength="12" min="0" id="options_'.$_option->getId().'_'.$_value->getOptionTypeId().'_qty" name="options_'.$_option->getId().'_'.$_value->getOptionTypeId().'_qty" onchange="'. $optionJs .'" onKeyPress="if(event.keyCode==13){'. $optionJs .'}" '. ($selected?$disabled:'disabled') .'></label></div>' : '') .
                                    '</li>';
                        }
                    }
                } else {
                    if (!$imagesHtml && $_value->getImages()) {
                        $showImgFlag = true;
                            if ($_option->getImageMode()==1 || ($_option->getImageMode()>1 && $_option->getExcludeFirstImage())) {
                            $imagesHtml = '<div id="customoptions_images_'. $_option->getId() .'" class="customoptions-images-div" style="display:none"></div>';
                        }
                    }
                }
                
                $select->addOption($_value->getOptionTypeId(), $_value->getTitle() . ' ' . $priceStr . $qty, $selectOptions);
            }
            if ($_option->getType() == Mage_Catalog_Model_Product_Option::OPTION_TYPE_MULTIPLE || $_option->getType()==MageWorx_CustomOptions_Model_Catalog_Product_Option::OPTION_TYPE_MULTISWATCH) {
                $extraParams .= ' multiple="multiple"';
            }
            
            if ($_option->getType()==MageWorx_CustomOptions_Model_Catalog_Product_Option::OPTION_TYPE_SWATCH || $_option->getType()==MageWorx_CustomOptions_Model_Catalog_Product_Option::OPTION_TYPE_MULTISWATCH) {
                $style = 'height: 1px; min-height: 1px; clear: both;';
                if (Mage::app()->getStore()->isAdmin()) $style .= ' visibility: hidden;';
                $extraParams .= ' style="'. $style .'"';
            }                        
            
            if ($showImgFlag) $showImgFunc = 'optionImages.showImage(this);'; else $showImgFunc = '';
            
            if ($_option->getType()==MageWorx_CustomOptions_Model_Catalog_Product_Option::OPTION_TYPE_SWATCH || $_option->getType()==MageWorx_CustomOptions_Model_Catalog_Product_Option::OPTION_TYPE_MULTISWATCH) $showImgFunc .= ' optionSwatch.change(this);';
            
            $select->setExtraParams('onchange="'.(($enabledDependent)?'dependentOptions.select(this); ':'') . $showImgFunc . $optionJs.'"'.$extraParams);
            
            if ((count($select->getOptions())>1 && ($_option->getType()==Mage_Catalog_Model_Product_Option::OPTION_TYPE_DROP_DOWN || $_option->getType()==MageWorx_CustomOptions_Model_Catalog_Product_Option::OPTION_TYPE_SWATCH)) || (count($select->getOptions())>0 && ($_option->getType()==Mage_Catalog_Model_Product_Option::OPTION_TYPE_MULTIPLE || $_option->getType()==MageWorx_CustomOptions_Model_Catalog_Product_Option::OPTION_TYPE_MULTISWATCH))) {
                $outHTML = $select->getHtml();
                
                if ($_option->getType()==Mage_Catalog_Model_Product_Option::OPTION_TYPE_DROP_DOWN) {
                    $outHTML .= '<div class="tagtip-question" id="select_description_'. $_option->getId() .'" style="display:none;"></div>';
                }
                
                
                if ($_option->getType()==MageWorx_CustomOptions_Model_Catalog_Product_Option::OPTION_TYPE_SWATCH || $_option->getType()==MageWorx_CustomOptions_Model_Catalog_Product_Option::OPTION_TYPE_MULTISWATCH) {
                    $outHTML = '<ul id="ul_swatch_'. $_option->getId() .'">' . $imagesHtml . '</ul>' . $outHTML;
                } else {
                    if ($imagesHtml) {
                        if ($helper->isImagesAboveOptions()) $outHTML = $imagesHtml.$outHTML; else $outHTML .= $imagesHtml;
                    }
                }
                return $outHTML;
            }
            
        } elseif ($_option->getType()==Mage_Catalog_Model_Product_Option::OPTION_TYPE_RADIO || $_option->getType()==Mage_Catalog_Model_Product_Option::OPTION_TYPE_CHECKBOX) {
            $selectHtml = '';
                        
            $require = '';
            if ($_option->getIsRequire(true)) {                
                if ($enabledDependent && $_option->getIsDependent()) $require = ' required-dependent'; else $require = ' validate-one-required-by-name';
            } else if ($_option->getType()==Mage_Catalog_Model_Product_Option::OPTION_TYPE_RADIO && !$_option->getIsRequire()) {
                $selectHtml .= '<li><input type="radio" id="options_' . $_option->getId() . '" class="'
                            . $class . ' radio product-custom-option" name="options[' . $_option->getId() . ']"'
                            . ($this->getSkipJsReloadPrice() ? '' : ' onclick="opConfig.reloadPrice()"')
                            . ' value="" checked="checked" /><span class="label"><label for="options_'
                            . $_option->getId() . '">' . $this->__('None') . '</label></span></li>';
            }
            
            $arraySign = '';
            switch ($_option->getType()) {
                case Mage_Catalog_Model_Product_Option::OPTION_TYPE_RADIO:
                    $type = 'radio';
                    $class = 'radio';
                    break;
                case Mage_Catalog_Model_Product_Option::OPTION_TYPE_CHECKBOX:
                    $type = 'checkbox';
                    $class = 'checkbox';
                    $arraySign = '[]';
                    break;
            }
            $count = 0;
            foreach ($_option->getValues() as $_value) {
                $count++;

                if ($_value->getPriceType() == MageWorx_CustomOptions_Model_Catalog_Product_Option::PRICE_TYPE_OPTIONS_PERCENT) {
                    $priceStr = '<span class="price-notice">'.'+'.'<span class="price">'.(int)$_value->getPrice().'%'.'</span>'.'</span>';
                } else {
                    $priceStr = $helper->getFormatedOptionPrice($this->getProduct(), $_option, $_value);
                }
                
                $qty = '';
                $customoptionsQty = $helper->getCustomoptionsQty($_value->getCustomoptionsQty(), $_value->getSku(), $this->getProduct()->getId(), $_value->getExtra(), $_value, $quoteItemId, null, false, true);
                
                if ($enabledInventory && $outOfStockOptions==1 && ($customoptionsQty===0 || $_value->getIsOutOfStock())) continue;
                
                $inventory = ($enabledInventory && $customoptionsQty<=0 && $customoptionsQty!=='') ? false : true;
                $disabled = (!$inventory && ($outOfStockOptions==0 || $outOfStockOptions==4)) || ($enabledDependent && $_option->getIsDependent()) ? 'disabled="disabled"' : '';
                if ($enabledInventory) {
                    if ($displayQty == 1 && $customoptionsQty > 0 && $customoptionsQty >= $lowStockValue) $qty = ' (' . $customoptionsQty . ')';
                    if ($displayQty == 2 && $customoptionsQty > 0) $qty = ' (' . $customoptionsQty . ')';
                    if ($displayQty == 3 && $customoptionsQty > 0 && $customoptionsQty < $lowStockValue) $qty = ' (' . $customoptionsQty . ')';
                    
                    if ($customoptionsQty <= 0 && $outOfStockOptions==4 && $customoptionsQty !== NULL && $customoptionsQty!=='') $qty .= ' (' .$helper->__('Out of stock') . ')';
                    if ($displayLowStockMessage && $lowStockValue > 0 && $customoptionsQty > 0 && $customoptionsQty <= $lowStockValue) $qty .= ' (' . $helper->__('Low stock') . ')';
                }
                                
                if ($disabled && $enabledDependent && $helper->hideDependentOption() && $_option->getIsDependent()) $selectHtml .= '<li style="display: none;">'; else $selectHtml .= '<li>';
                
                $imagesHtml = '';
                $images = $_value->getImages();
                if ($images) {
                    if ($_option->getImageMode()==1) {
                        foreach($images as $image) {
                            $imgData = $helper->getImgData($image['image_file']);
                            if ($imgData) {
                                $imgData['class'] = 'radio-checkbox-img';
                                $imagesHtml .= $helper->getImgHtml($imgData);
                            }
                        }
                    } elseif ($_option->getExcludeFirstImage()) {
                        $image = $images[0];
                        $imgData = $helper->getImgData($image['image_file']);
                        if ($imgData) {
                            $imgData['class'] = 'radio-checkbox-img';
                            $imagesHtml .= $helper->getImgHtml($imgData);
                        }
                    }
                }
                                
                $checked = $this->_isValueSelectedByQueryStringConfig($_value->getOptionTypeId());
                if ($_value->getDefault()==1 && !$disabled) {
                    $checked = true;
                }
                
                if ($images && $_option->getImageMode()>1) $showImgFunc = 'optionImages.showImage(this);'; else $showImgFunc = '';
                
                $onclick = (($enabledDependent)?'dependentOptions.select(this);':'') . $optionJs . $showImgFunc;

                if ($helper->isQntyInputEnabled() && $_option->getQntyInput() && $_option->getType()==Mage_Catalog_Model_Product_Option::OPTION_TYPE_CHECKBOX) {
                    if ($buyRequest) $optionValueQty = $buyRequest->getData('options_'.$_option->getId().'_'.$_value->getOptionTypeId().'_qty'); else $optionValueQty = 0;
                    if (!$optionValueQty && $checked) $optionValueQty = 1;
                    
                    if ($imagesHtml) $cssVariant = 2; else $cssVariant = 1;
                    $selectHtml .= 
                        '<span class="radio-checkbox-label">'.
                            '<label class="radio-checkbox-label-'. $cssVariant .'" onclick="if ($(Event.element(event)).hasClassName(\'qty\')) return false">' . 
                                '<input ' . $disabled . ' ' . ($checked ? 'checked' : '') . ' type="' . $type . '" class="' . $class . ' ' . $require . ' product-custom-option" onclick=" '.$onclick.'" name="options[' . $_option->getId() . ']' . $arraySign . '" id="options_' . $_option->getId() . '_' . $count . '" value="' . $_value->getOptionTypeId() . '" />'.
                                $imagesHtml .
                                '<div class="radio-checkbox-text">'. $_value->getTitle() . ' ' . $priceStr . $qty .'</div>'.
                                '<div class="label-qty"><b>'.$helper->getDefaultOptionQtyLabel().'</b> <input '.$qtyInputType.' class="qty'. ($checked?' validate-greater-than-zero':'') .'" value="'.$optionValueQty.'" maxlength="12" min="0" id="options_'.$_option->getId().'_'.$_value->getOptionTypeId().'_qty" name="options_'.$_option->getId().'_'.$_value->getOptionTypeId().'_qty" pattern="\d*" onchange="'. $optionJs .'" onKeyPress="if(event.keyCode==13){'. $optionJs .'}" '.($checked?$disabled:'disabled').'></div>'.
                            '</label>';
                } elseif ($imagesHtml) {
                    $selectHtml .=                        
                        '<span class="radio-checkbox-label">'.
                            '<label class="radio-checkbox-label-1" onclick="if ($(Event.element(event)).hasClassName(\'qty\')) return false">' . 
                                '<input ' . $disabled . ' ' . ($checked ? 'checked' : '') . ' type="' . $type . '" class="' . $class . ' ' . $require . ' product-custom-option" onclick=" '.$onclick.'" name="options[' . $_option->getId() . ']' . $arraySign . '" id="options_' . $_option->getId() . '_' . $count . '" value="' . $_value->getOptionTypeId() . '" />'.
                                $imagesHtml .
                                '<div class="radio-checkbox-text">'. $_value->getTitle() . ' ' . $priceStr . $qty .'</div>'.
                            '</label>';
                } else {
                    $selectHtml .=
                        '<input ' . $disabled . ' ' . ($checked ? 'checked' : '') . ' type="' . $type . '" class="' . $class . ' ' . $require . ' product-custom-option" onclick="'.$onclick.'" name="options[' . $_option->getId() . ']' . $arraySign . '" id="options_' . $_option->getId() . '_' . $count . '" value="' . $_value->getOptionTypeId() . '" />'.
                        '<span class="label"><label for="options_' . $_option->getId() . '_' . $count . '">' . $_value->getTitle() . ' ' . $priceStr . $qty . '</label>';
                }

                if ($_value->getDescription() && $helper->isOptionVariationDescriptionEnabled()) {
                    $selectHtml .= '<span class="tagtip-question" id="select_description_'. $_option->getId() .'_'.$_value->getId().'"></span>';
                    $selectHtml .= '<script type="text/javascript">new Tagtip("select_description_'.$_option->getId().'_'.$_value->getId().'", "'.$helper->jsEscape($_value->getDescription()).'", {align: "rightMiddle"});</script>';
                }

                $selectHtml .= '</span>';



                if ($_option->getIsRequire(true)) {
                    $selectHtml .= '<script type="text/javascript">' .
                            '$(\'options_' . $_option->getId() . '_' . $count . '\').advaiceContainer = \'options-' . $_option->getId() . '-container\';' .
                            '$(\'options_' . $_option->getId() . '_' . $count . '\').callbackFunction = \'validateOptionsCallback\';' .
                            '</script>';
                }
                $selectHtml .= '</li>';                                                
            }
            
            if ($selectHtml) $selectHtml = '<ul id="options-' . $_option->getId() . '-list" class="options-list">'.$selectHtml.'</ul>';
            self::$isFirstOption = false;
            return $selectHtml;
        } elseif ($_option->getType()==MageWorx_CustomOptions_Model_Catalog_Product_Option::OPTION_TYPE_HIDDEN) {
            $count = 0;
            $selectHtml = '';
            foreach ($_option->getValues() as $_value) {
                $count++;
                $customoptionsQty = $helper->getCustomoptionsQty($_value->getCustomoptionsQty(), $_value->getSku(), $this->getProduct()->getId(), $_value->getExtra(), $_value, $quoteItemId);
                
                if ($enabledInventory && $outOfStockOptions==1 && ($customoptionsQty===0 || $_value->getIsOutOfStock())) continue;
                
                $inventory = ($enabledInventory && $customoptionsQty===0 && $customoptionsQty!=='') ? false : true;
                $disabled = (!$inventory && ($outOfStockOptions==0 || $outOfStockOptions==4)) || ($enabledDependent && $_option->getIsDependent()) ? 'disabled="disabled"' : '';
                $selectHtml .= '<input ' . $disabled . ' type="hidden" class="product-custom-option" name="options[' . $_option->getId() . '][]" id="options_' . $_option->getId() . '_' . $count . '" value="' . $_value->getOptionTypeId() . '" />';
            }
            return $selectHtml;
        }
    }

    public function getSwatchTitleHtml($_option, $_value)
    {
        if (!$_option->getShowSwatchTitle()) {
            return '';
        }
        $html = '<div>'.$_value->getTitle().'</div>';
        if ($_value->getPriceType() == 'percent' || $_value->getPriceType() == MageWorx_CustomOptions_Model_Catalog_Product_Option::PRICE_TYPE_OPTIONS_PERCENT) {
            $html .= '<div>'.number_format((float)$_value->getPrice(), 2, '.', '').'%'.'</div>';
        } else {
            $html .= '<div>'.Mage::helper('core')->currency($this->getSwatchTitleTax($_value), true, false).'</div>';
        }
        return $html;
    }
    
    public function getSwatchDescriptionHtml($_option, $_value){
        $html = '';
        if (Mage::helper('mageworx_customoptions')->isOptionVariationDescriptionEnabled() && $_value->getDescription() != '') {
            $html .= '<span class="tagtip-question" id="select_description_'. $_option->getId() .'_'.$_value->getId().'"></span>';
            $html .= '<script type="text/javascript">new Tagtip("select_description_'.$_option->getId().'_'.$_value->getId().'", "'.Mage::helper('mageworx_customoptions')->jsEscape($_value->getDescription()).'", {align: "rightMiddle"});</script>';
        }
        return $html;
    }

    public function getSwatchTitleTax($_value) {
        return Mage::helper('tax')->getPrice($this->getProduct(), $_value->getPrice());
    }

    /**
     * Check if option value should be selected based on the query string param "config"
     *
     * @param int $optionTypeId
     * @return bool
     */
    protected function _isValueSelectedByQueryStringConfig($optionTypeId) 
    {
        $config = Mage::helper('mageworx_customoptions')
            ->getPreconfiguredValues($this->getProduct(), $this->getOption()->getId());

        return in_array($optionTypeId, $config);
    }
}