<?php

class Aitoc_Aitcg_Block_Rewrite_Adminhtml_Catalog_Product_Edit_Tab_Options_Option
    extends Mage_Adminhtml_Block_Catalog_Product_Edit_Tab_Options_Option
{
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('aitcommonfiles/design--adminhtml--default--default--template--catalog--product--edit--options--option.phtml');
    }

    protected function _prepareLayout()
    {
        if (!(Mage::helper('aitcg')->isModuleEnabled('Aitoc_Aitoptionstemplate')
            && $this->getRequest()
                ->getParam('aitflag') == 1)
        ) {
            $this->setChild(
                'aitcg_option_type',
                $this->getLayout()->createBlock(
                    'aitcg/adminhtml_catalog_product_edit_tab_options_type_cgfile'
                )->setProduct($this->getProduct())
            );
        }

        return parent::_prepareLayout();
    }

    public function getTemplatesHtml()
    {
        $templates = parent::getTemplatesHtml();

        return $templates . "\n" . $this->getChildHtml('aitcg_option_type');
    }

	public function getOptionValues()
	{
		$optionsArr = array_reverse($this->getProduct()->getOptions(), true);
//        $optionsArr = $this->getProduct()->getOptions();

		if (!$this->_values) {
			$showPrice = $this->getCanReadPrice();
			$values = array();
			$scope = (int) Mage::app()->getStore()->getConfig(Mage_Core_Model_Store::XML_PATH_PRICE_SCOPE);
			foreach ($optionsArr as $option) {
				/* @var $option Mage_Catalog_Model_Product_Option */

				$this->setItemCount($option->getOptionId());

				$value = array();

				$value['id'] = $option->getOptionId();
				$value['item_count'] = $this->getItemCount();
				$value['option_id'] = $option->getOptionId();
				$value['title'] = $this->escapeHtml($option->getTitle());
				$value['type'] = $option->getType();
				$value['is_require'] = $option->getIsRequire();
				$value['sort_order'] = $option->getSortOrder();
				$value['can_edit_price'] = $this->getCanEditPrice();

				if ($option && Mage::helper('aitcg/options')->checkAitOption($option)) {
					$value['image_template_id']       = $option->getImageTemplateId();
					$value['area_size_x']             = $option->getAreaSizeX();
					$value['area_size_y']             = $option->getAreaSizeY();
					$value['area_offset_x']           = $option->getAreaOffsetX();
					$value['area_offset_y']           = $option->getAreaOffsetY();
					$value['use_text']                = $option->getUseText();
					$value['use_user_image']          = $option->getUseUserImage();
					$value['use_digital_image']       = $option->getUseDigitalImage();
					$value['use_predefined_image']    = $option->getUsePredefinedImage();
					$value['predefined_cats']         = $option->getPredefinedCats();
					$value['use_masks']               = $option->getUseMasks();
					$value['use_instagram']           = $option->getUseInstagram();
					$value['use_pinterest']           = $option->getUsePinterest();
					$value['use_black_white']         = $option->getUseBlackWhite();
					$value['masks_cat_id']            = $option->getMasksCatId();
					$value['mask_location']           = $option->getMaskLocation();
					$value['allow_colorpick']         = $option->getAllowColorpick();
					$value['text_length']             = $option->getTextLength();
					$value['allow_text_distortion']   = $option->getAllowTextDistortion();
					$value['input_box_type']          = $option->getInputBoxType();
					$value['allow_predefined_colors'] = $option->getAllowPredefinedColors();
					$value['curve_text']              = $option->getCurveText();
					$value['color_set_id']            = $this->_getColorSetId($option);
					$value['def_img_behind_text']     = $option->getDefImgBehindText();
					$value['def_img_behind_image']    = $option->getDefImgBehindImage();
					$value['def_img_behind_clip']     = $option->getDefImgBehindClip();
					$value['allow_save_graphics']     = $option->getAllowSaveGraphics();
					$value['scale_image']             = $option->getScaleImage();
				}

				$value['cpp_option_id']     = $option->getCppOptionId();
                $value['is_one_of_cost']       = $option->getIsOneOfCost();
                $value['option_for_section']     = $option->getOptionForSection();
				//$value['option_for_mask']   = $option->getOptionForMask();
				$value['is_inside_page']    = $option->getIsInsidePage();

				if ($this->getProduct()->getStoreId() != '0') {
					$value['checkboxScopeTitle'] = $this->getCheckboxScopeHtml($option->getOptionId(), 'title',
						is_null($option->getStoreTitle()));
					$value['scopeTitleDisabled'] = is_null($option->getStoreTitle())?'disabled':null;
				}

				if ($option->getGroupByType() == Mage_Catalog_Model_Product_Option::OPTION_GROUP_SELECT) {

//                    $valuesArr = array_reverse($option->getValues(), true);

					$i = 0;
					$itemCount = 0;
					foreach ($option->getValues() as $_value) {
						/* @var $_value Mage_Catalog_Model_Product_Option_Value */
						$value['optionValues'][$i] = array(
							'item_count' => max($itemCount, $_value->getOptionTypeId()),
							'option_id' => $_value->getOptionId(),
							'option_type_id' => $_value->getOptionTypeId(),
							'title' => $this->escapeHtml($_value->getTitle()),
							'price' => ($showPrice)
								? $this->getPriceValue($_value->getPrice(), $_value->getPriceType()) : '',
							'price_type' => ($showPrice) ? $_value->getPriceType() : 0,
							'sku' => $this->escapeHtml($_value->getSku()),
							'mask_category' => $_value->getMaskCategory(),
                            'cover_category' => $_value->getCoverCategory(),
							'sort_order' => $_value->getSortOrder(),
						);

						if ($this->getProduct()->getStoreId() != '0') {
							$value['optionValues'][$i]['checkboxScopeTitle'] = $this->getCheckboxScopeHtml(
								$_value->getOptionId(), 'title', is_null($_value->getStoreTitle()),
								$_value->getOptionTypeId());
							$value['optionValues'][$i]['scopeTitleDisabled'] = is_null($_value->getStoreTitle())
								? 'disabled' : null;
							if ($scope == Mage_Core_Model_Store::PRICE_SCOPE_WEBSITE) {
								$value['optionValues'][$i]['checkboxScopePrice'] = $this->getCheckboxScopeHtml(
									$_value->getOptionId(), 'price', is_null($_value->getstorePrice()),
									$_value->getOptionTypeId());
								$value['optionValues'][$i]['scopePriceDisabled'] = is_null($_value->getStorePrice())
									? 'disabled' : null;
							}
						}
						$i++;
					}
				} else {
					$value['price'] = ($showPrice)
						? $this->getPriceValue($option->getPrice(), $option->getPriceType()) : '';
					$value['price_type'] = $option->getPriceType();
					$value['sku'] = $this->escapeHtml($option->getSku());
					$value['mask_category'] = $option->getMaskCategory();
                    $value['cover_category'] = $option->getCoverCategory();
					$value['max_characters'] = $option->getMaxCharacters();
					$value['file_extension'] = $option->getFileExtension();
					$value['image_size_x'] = $option->getImageSizeX();
					$value['image_size_y'] = $option->getImageSizeY();
					if ($this->getProduct()->getStoreId() != '0' &&
					    $scope == Mage_Core_Model_Store::PRICE_SCOPE_WEBSITE) {
						$value['checkboxScopePrice'] = $this->getCheckboxScopeHtml($option->getOptionId(),
							'price', is_null($option->getStorePrice()));
						$value['scopePriceDisabled'] = is_null($option->getStorePrice())?'disabled':null;
					}
				}
				$values[] = new Varien_Object($value);
			}
			$this->_values = $values;
		}

		return $this->_values;
	}

	/*public function getOptionValues()
    {
        $values    = parent::getOptionValues();
        $optionArr = array_reverse($this->getProduct()->getOptions(), true);
        if (!$optionArr) {
            return $values;
        }
        foreach ($values as &$value) {
            $option = $optionArr [$value->getId()];
            if ($option){
                $value['cpp_option_id']       = $option->getCppOptionId();
                $value['option_for_mask']     = $option->getOptionForMask();
                $value['is_inside_page']      = $option->getIsInsidePage();
            }

            if ($option && Mage::helper('aitcg/options')->checkAitOption($option)) {
                $value['image_template_id']       = $option->getImageTemplateId();
                $value['area_size_x']             = $option->getAreaSizeX();
                $value['area_size_y']             = $option->getAreaSizeY();
                $value['area_offset_x']           = $option->getAreaOffsetX();
                $value['area_offset_y']           = $option->getAreaOffsetY();
                $value['use_text']                = $option->getUseText();
                $value['use_user_image']          = $option->getUseUserImage();
                $value['use_predefined_image']    = $option->getUsePredefinedImage();
                $value['predefined_cats']         = $option->getPredefinedCats();
                $value['use_masks']               = $option->getUseMasks();
                $value['use_instagram']           = $option->getUseInstagram();
                $value['use_pinterest']           = $option->getUsePinterest();
                $value['use_black_white']         = $option->getUseBlackWhite();
                $value['masks_cat_id']            = $option->getMasksCatId();
                $value['mask_location']           = $option->getMaskLocation();
                $value['allow_colorpick']         = $option->getAllowColorpick();
                $value['text_length']             = $option->getTextLength();
                $value['allow_text_distortion']   = $option->getAllowTextDistortion();
                $value['input_box_type']          = $option->getInputBoxType();
                $value['allow_predefined_colors'] = $option->getAllowPredefinedColors();
                $value['curve_text']              = $option->getCurveText();
                $value['color_set_id']            = $this->_getColorSetId($option);
                $value['def_img_behind_text']     = $option->getDefImgBehindText();
                $value['def_img_behind_image']    = $option->getDefImgBehindImage();
                $value['def_img_behind_clip']     = $option->getDefImgBehindClip();
                $value['allow_save_graphics']     = $option->getAllowSaveGraphics();
                $value['scale_image']             = $option->getScaleImage();
            }

	        if( $value->getType() == Mage_Catalog_Model_Product_Option::OPTION_TYPE_DROP_DOWN ) {
				$i = 0; $tempArray = array();
		        foreach ($option->getValues() as $_value) {
			        $tempArray[$i] = array_merge( $value['optionValues'][$i], array('mask_category' => $_value->getMaskCategory()));
			        $i++;
		        }
		        $value['optionValues'] = $tempArray;
	        }
        }
        $this->_values = $values;

        return $this->_values;
    }*/

    protected function _getColorSetId($option)
    {
        $currentId = $option->getColorSetId();
        if (Mage::getModel('aitcg/font_color_set')->hasId($currentId)) {
            return $currentId;
        }

        return Aitoc_Aitcg_Helper_Font_Color_Set::XPATH_CONFIG_AITCG_FONT_COLOR_SET_DFLT;
    }

    /**
     * @return html of options cpp
     */
    public function getCPPOptions()
    {
        $product = $this->getProduct();
        $model = Mage::getModel('aitcg/product');

        $select = $this->getLayout()->createBlock('adminhtml/html_select')
            ->setData(array(
                'id' => $this->getFieldId().'_{{id}}_cpp_option_id',
                'class' => 'select'
            ))
            ->setName($this->getFieldName().'[{{id}}][cpp_option_id]')
            ->setOptions($model->getProductCPPOptions($product->getId()));

        return $select->getHtml();
    }
    /**
     * @return html of options  of section on which you want custom option
     */
    public function getSectionsOptions()
    {
        $select = $this->getLayout()->createBlock('adminhtml/html_select')
            ->setData(array(
                'id' => $this->getFieldId().'_{{id}}_option_for_section',
                'class' => 'select'
            ))
            ->setClass('select-option-section select')
            ->setName($this->getFieldName().'[{{id}}][option_for_section]')
            ->setOptions(array(
                ""=>Mage::helper('adminhtml')->__('Please select an section.'),
                'foil_text' => 'Foil Text',
                'foil_logo' => 'Foil Logo',
                'shape_mask' => 'Shape Mask',
                'digital_image' => 'Digital Image',
                'cover_image' => 'Cover Image'
            ));

        return $select->getHtml();
    }
    public function getCostOptions(){
        $select = $this->getLayout()->createBlock('adminhtml/html_select')
            ->setData(array(
                'id' => $this->getFieldId().'_{{id}}_is_one_of_cost',
                'class' => 'select'
            ))
            ->setName($this->getFieldName().'[{{id}}][is_one_of_cost]')
            ->setOptions(Mage::getSingleton('adminhtml/system_config_source_yesno')->toOptionArray());

        return $select->getHtml();
    }
	/**
	 * @return html of options mask category
	 */
	public function getMaskOptions()
	{
		$select = $this->getLayout()->createBlock('adminhtml/html_select')
		               ->setData(array(
			               'id' => $this->getFieldId().'_{{id}}_option_for_mask',
			               'class' => 'select'
		               ))
		               ->setName($this->getFieldName().'[{{id}}][option_for_mask]')
		               ->setOptions(array(
							0 => 'No',
			                1 => 'Yes'
		               ));

		return $select->getHtml();
	}

	/**
	 * @return dropdown for is inside page option
	 */
	public function getInsidePageOptions()
	{
		$select = $this->getLayout()->createBlock('adminhtml/html_select')
		               ->setData(array(
			               'id' => $this->getFieldId().'_{{id}}_is_inside_page',
			               'class' => 'select'
		               ))
		               ->setName($this->getFieldName().'[{{id}}][is_inside_page]')
		               ->setOptions(array(
			               0 => 'No',
			               1 => 'Yes'
		               ));

		return $select->getHtml();
	}
}