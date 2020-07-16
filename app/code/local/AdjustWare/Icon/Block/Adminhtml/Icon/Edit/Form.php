<?php
class AdjustWare_Icon_Block_Adminhtml_Icon_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form(array(
            'id'      => 'edit_form',
            'action'  => $this->getUrl('*/*/save', array('id' => $this->getRequest()->getParam('id'))),
            'method'  => 'post',
            'enctype' => 'multipart/form-data',
        ));
        
        $form->setUseContainer(true);
        $this->setForm($form);
        
        $hlp     = Mage::helper('adjicon');
        $model   = Mage::registry('adjicon_attribute');
        $options = $model->getOptions();
        $resized = $hlp->getIconSizeArray();

        $fieldInfo = $form->addFieldset('adjicon_info', array('legend'=> $hlp->__('Attribute')));
        if ($model->getAttributeId() && $model->getFrontendLabel()){
            $fieldInfo->addField('frontend_label', 'text', array(
              'label'     => $hlp->__('Attribute'),
              'required'  => false,
              'name'      => 'frontend_label',
    	      'readonly'  => true,
    	      'disabled'  => true,
            ));
            $fieldInfo->addField('attribute_id', 'hidden', array(
              'name'      => 'attribute_id',
            ));
        }
        else {
            $fieldInfo->addField('attribute_id', 'select', array(
                'label'     => $hlp->__('Attribute'),
                'name'      => 'attribute_id',
                'values'    => $model->getAvailableAttributesAsOptions(),
                'onchange'  => "getAttributeOptions('". $this->getUrl('*/*/formload') ."', this.value)",
            ));
        }

        $fieldInfo->addField('pos', 'text', array(
          'label'     => $hlp->__('Sorting Order'),
          'class'     => 'validate-number',
          'name'      => 'pos',
        ));
        
        $yesno = array(
            array(
                'value' => 0,
                'label' => Mage::helper('catalog')->__('No')
            ),
            array(
                'value' => 1,
                'label' => Mage::helper('catalog')->__('Yes')
        ));

        $fieldsetAttrSettings = $form->addFieldset('adjicon_attributes_settings', array('legend'=>$hlp->__('Attribute visualization settings')));

        $fieldsetAttrSettings->addField('show_images_product', 'select', array(
            'label'    => $hlp->__('Show Icons on Product View'),
            'name'     => 'show_images_product',
            'values'   => $yesno,
        ));

        $fieldsetAttrSettings->addField('show_images', 'select', array(
            'label'    => $hlp->__('Show Icons in Layered Navigation'),
            'name'     => 'show_images',
            'values'   => $yesno,
        ));

		$fieldsetAttrSettings->addField('show_qty', 'select', array(
			'label'    => $hlp->__('Show Quantity in Icons Hint (Layered Navigation)'),
			'name'     => 'show_qty',
			'values'   => $yesno,
		));

        $fieldsetAttrSettings->addField('columns_num', 'select', array(
            'label'     => $hlp->__('Display Type in Layered Navigation'),
            'name'      => 'columns_num',
            'values'    => array(
                array('value'=>2, 'label'=>$hlp->__('Icons Only')),
                array('value'=>1, 'label'=>$hlp->__('Labels and Icons')),
            ),
        ));

        $fieldsetConfigurableSettings = $form->addFieldset('adjicon_configurable_settings', array('legend'=>$hlp->__('Configurable product attributes')));

        $fieldsetConfigurableSettings->addField('show_images_configurable', 'select', array(
            'label'    => $hlp->__('Enable Icons in Configurable Product'),
            'name'     => 'show_images_configurable',
            'values'   => $yesno,
        ));
        $fieldsetConfigurableSettings->addField('config_option_type', 'select', array(
            'label'     => $hlp->__('Display Type'),
            'name'      => 'config_option_type',
            'values'    => array(
				array('value'=>2, 'label'=>$hlp->__('Icons Only')),
                array('value'=>1, 'label'=>$hlp->__('Default Options and Icons'))
            ),
        ));

        if($options) {
            $fieldsetAttrType = $form->addFieldset('adjicon_attributes_type', array('legend'=>$hlp->__('Attribute visualization type')));

            $fieldsetAttrType->addField('visualization_type', 'radios', array(
                'label'     => $hlp->__('Visualization Type'),
                'name'      => 'visualization_type',
                'class'     => 'visualization_type',
                'values'    => array(
                    array('value' => AdjustWare_Icon_Helper_Data::VISUALIZATION_TYPE_NONE, 'label' => $hlp->__('None')),
                    array('value' => AdjustWare_Icon_Helper_Data::VISUALIZATION_TYPE_COLOR, 'label' => $hlp->__('Color')),
                    array('value' => AdjustWare_Icon_Helper_Data::VISUALIZATION_TYPE_IMAGES, 'label' => $hlp->__('Custom Images')),
                    array('value' => AdjustWare_Icon_Helper_Data::VISUALIZATION_TYPE_ICONS, 'label' => $hlp->__('Text Icons'))
                )
            ));

            $fieldsetImages = $form->addFieldset('adjicon_images', array('legend'=>$hlp->__('Custom Images')));

            $fieldsetImages->addField('preview', 'note', array(
                'text' => $hlp->__('* Resized Preview - ' . $resized['adjlarge']['icon'] . 'px, ' . $resized['adjmedium']['icon'] . 'px, ' . $resized['adjsmall']['icon'] . 'px, ' . $resized['adjoption']['icon'] . 'px')
            ));

            foreach ($options as $info){
                $html = '<p style="margin-top: 5px">';
                if (!empty($info['filename'])){
                    $html .= '<img src="'.Mage::getBaseUrl('media') . 'icons/' . 'v_' . $info['filename'].'" valign="top"/>';
					$html .= ' &nbsp; ';
					$html .= '<img src="'.Mage::getBaseUrl('media') . 'icons/' . 'pl_' . $info['filename'].'" valign="top"/>';
					$html .= ' &nbsp; ';
					$html .= '<img src="'.Mage::getBaseUrl('media') . 'icons/' . 'l_' . $info['filename'].'" valign="top"/>';
                    $html .= ' &nbsp; ';
                    $html .= '<img src="'.Mage::getBaseUrl('media') . 'icons/' . 'o_' . $info['filename'].'" valign="top"/>';
                    $html .= '<br />';
                    $html .= '<a onclick="return confirm(\''.$hlp->__('Are you sure?').'\')" href="'.$this->getUrl('*/*/deleteIcon', array('id'=>$model->getId(), 'icon_id'=>$info['icon_id'])).'">';
                    $html .= $hlp->__('Delete');
                    $html .= '</a>';
                }
                $html .= '</p>';

                $fieldsetImages->addField('option_' . $info['option_id'], 'file', array(
                    'label'     => $info['value'],
                    'name'      => 'option_'. $info['option_id'],
					'onchange'  => "uploadImageIcon('".$this->getUrl('*/*/uploadIcon', array('attribute_id'=>$model->getAttributeId(), 'option_id'=>$info['option_id']))."', 'option_".$info['option_id']."', '" . Mage::getSingleton('core/session')->getFormKey() . "' , {onComplete: addUploadedImage})",
                    'required'  => false,
                    'after_element_html' => $html,
                ));
            }

            $fieldsetColor = $form->addFieldset('adjicon_color', array('legend'=>$hlp->__('Color')));
            $html = '<div style="float: right;"><div><p style="margin-top: 20px;">*Default Icon </p><br/><p style="margin-top: 5px;"> *Disabled Icon </p><br/><p style="margin-top: 5px;"> *Selected Icon </p></div></div>';
            $fieldsetColor->addField('color_icon_type', 'radios', array(
                'label'     => $hlp->__('Icons Type'),
                'name'      => 'color_icon_type',
				'class'     => 'icon_type',
                'values'    => array(
                    array('value' => AdjustWare_Icon_Helper_Data::ICON_TYPE_COLOR_CIRCLE_1,
                        'label' => '<a href="#" class="adjcolor'.AdjustWare_Icon_Helper_Data::ICON_TYPE_COLOR_CIRCLE_1.' adjdef adjsmall" onclick="return false;"></div></a><a href="#" class="adjcolor'.AdjustWare_Icon_Helper_Data::ICON_TYPE_COLOR_CIRCLE_1.' adjdisabled adjsmall" onclick="return false;"></a><a href="#" class="adjcolor'.AdjustWare_Icon_Helper_Data::ICON_TYPE_COLOR_CIRCLE_1.' adjselected adjsmall" onclick="return false;"></a>'),
                    array('value' => AdjustWare_Icon_Helper_Data::ICON_TYPE_COLOR_SQUARE_1,
                        'label' => '<a href="#" class="adjcolor'.AdjustWare_Icon_Helper_Data::ICON_TYPE_COLOR_SQUARE_1.' adjdef adjsmall" onclick="return false;"></a><a href="#" class="adjcolor'.AdjustWare_Icon_Helper_Data::ICON_TYPE_COLOR_SQUARE_1.' adjdisabled adjsmall" onclick="return false;"></a><a href="#" class="adjcolor'.AdjustWare_Icon_Helper_Data::ICON_TYPE_COLOR_SQUARE_1.' adjselected adjsmall" onclick="return false;"></a>'),
                    array('value' => AdjustWare_Icon_Helper_Data::ICON_TYPE_COLOR_ROUND_1,
                        'label' => '<a href="#" class="adjcolor'.AdjustWare_Icon_Helper_Data::ICON_TYPE_COLOR_ROUND_1.' adjdef adjsmall" onclick="return false;"></a><a href="#" class="adjcolor'.AdjustWare_Icon_Helper_Data::ICON_TYPE_COLOR_ROUND_1.' adjdisabled adjsmall" onclick="return false;"></a><a href="#" class="adjcolor'.AdjustWare_Icon_Helper_Data::ICON_TYPE_COLOR_ROUND_1.' adjselected adjsmall" onclick="return false;"></a>'),
                    array('value' => AdjustWare_Icon_Helper_Data::ICON_TYPE_COLOR_CIRCLE_2,
                        'label' => '<a href="#" class="adjcolor'.AdjustWare_Icon_Helper_Data::ICON_TYPE_COLOR_CIRCLE_2.' adjdef adjsmall" onclick="return false;"></div></a><a href="#" class="adjcolor'.AdjustWare_Icon_Helper_Data::ICON_TYPE_COLOR_CIRCLE_2.' adjdisabled adjsmall" onclick="return false;"></a><a href="#" class="adjcolor'.AdjustWare_Icon_Helper_Data::ICON_TYPE_COLOR_CIRCLE_2.' adjselected adjsmall" onclick="return false;"></a>'),
                    array('value' => AdjustWare_Icon_Helper_Data::ICON_TYPE_COLOR_SQUARE_2,
                        'label' => '<a href="#" class="adjcolor'.AdjustWare_Icon_Helper_Data::ICON_TYPE_COLOR_SQUARE_2.' adjdef adjsmall" onclick="return false;"></a><a href="#" class="adjcolor'.AdjustWare_Icon_Helper_Data::ICON_TYPE_COLOR_SQUARE_2.' adjdisabled adjsmall" onclick="return false;"></a><a href="#" class="adjcolor'.AdjustWare_Icon_Helper_Data::ICON_TYPE_COLOR_SQUARE_2.' adjselected adjsmall" onclick="return false;"></a>'),
                    array('value' => AdjustWare_Icon_Helper_Data::ICON_TYPE_COLOR_ROUND_2,
                        'label' => '<a href="#" class="adjcolor'.AdjustWare_Icon_Helper_Data::ICON_TYPE_COLOR_ROUND_2.' adjdef adjsmall" onclick="return false;"></a><a href="#" class="adjcolor'.AdjustWare_Icon_Helper_Data::ICON_TYPE_COLOR_ROUND_2.' adjdisabled adjsmall" onclick="return false;"></a><a href="#" class="adjcolor'.AdjustWare_Icon_Helper_Data::ICON_TYPE_COLOR_ROUND_2.' adjselected adjsmall" onclick="return false;"></a>'),
                ),
                'after_element_html' => $html
            ));

            $fieldsetColor->addField('note', 'note', array(
                'text' => $hlp->__('* Click in the field to choose a color'),
            ));

            foreach ($options as $info){
                $fieldsetColor->addField('color' . $info['option_id'], 'text', array(
                        'label'     => $info['value'],
                        'name'      => 'color'. $info['option_id'],
                        'class'     => "adjname-". strtolower($this->escapeHtml($info['value']) . " color {hash:true, required:false, pickerPosition:'right', adjust:false}"),
                    ));
            }
       
            $fieldsetIcons = $form->addFieldset('adjicon_icon', array('legend'=>$hlp->__('Icons')));
            $html = '<div style="float: right;"><div><p style="margin-top: 20px;">*Default Icon </p><br/><p style="margin-top: 5px;"> *Disabled Icon </p><br/><p style="margin-top: 5px;"> *Selected Icon </p></div></div>';
            $fieldsetIcons->addField('icon_type', 'radios', array(
                'label'     => $hlp->__('Icons Type'),
                'name'      => 'icon_type',
				'class'     => 'icon_type',
                'values'    => array(
					array('value' => AdjustWare_Icon_Helper_Data::ICON_TYPE_CIRCLE_1,
                        'label' => '<a href="#" class="icon'.AdjustWare_Icon_Helper_Data::ICON_TYPE_CIRCLE_1.' adjdef adjsmall" onclick="return false;"><span></span>38</a><a href="#" class="icon'.AdjustWare_Icon_Helper_Data::ICON_TYPE_CIRCLE_1.' adjdisabled adjsmall" onclick="return false;"><span></span>38</a><a href="#" class="icon'.AdjustWare_Icon_Helper_Data::ICON_TYPE_CIRCLE_1.' adjselected adjsmall" onclick="return false;"><span></span>38</a>'),
					array('value' => AdjustWare_Icon_Helper_Data::ICON_TYPE_SQUARE_1,
                        'label' => '<a href="#" class="icon'.AdjustWare_Icon_Helper_Data::ICON_TYPE_SQUARE_1.' adjdef adjsmall" onclick="return false;"><span></span>38</a><a href="#" class="icon'.AdjustWare_Icon_Helper_Data::ICON_TYPE_SQUARE_1.' adjdisabled adjsmall" onclick="return false;"><span></span>38</a><a href="#" class="icon'.AdjustWare_Icon_Helper_Data::ICON_TYPE_SQUARE_1.' adjselected adjsmall" onclick="return false;"><span></span>38</a>'),
					array('value' => AdjustWare_Icon_Helper_Data::ICON_TYPE_ROUND_1,
                        'label' => '<a href="#" class="icon'.AdjustWare_Icon_Helper_Data::ICON_TYPE_ROUND_1.' adjdef adjsmall" onclick="return false;"><span></span>38</a><a href="#" class="icon'.AdjustWare_Icon_Helper_Data::ICON_TYPE_ROUND_1.' adjdisabled adjsmall" onclick="return false;"><span></span>38</a><a href="#" class="icon'.AdjustWare_Icon_Helper_Data::ICON_TYPE_ROUND_1.' adjselected adjsmall" onclick="return false;"><span></span>38</a>'),
                    array('value' => AdjustWare_Icon_Helper_Data::ICON_TYPE_CIRCLE_2,
                        'label' => '<a href="#" class="icon'.AdjustWare_Icon_Helper_Data::ICON_TYPE_CIRCLE_2.' adjdef adjsmall" onclick="return false;"><span></span>38</a><a href="#" class="icon'.AdjustWare_Icon_Helper_Data::ICON_TYPE_CIRCLE_2.' adjdisabled adjsmall" onclick="return false;"><span></span>38</a><a href="#" class="icon'.AdjustWare_Icon_Helper_Data::ICON_TYPE_CIRCLE_2.' adjselected adjsmall" onclick="return false;"><span></span>38</a>'),
                    array('value' => AdjustWare_Icon_Helper_Data::ICON_TYPE_SQUARE_2,
                        'label' => '<a href="#" class="icon'.AdjustWare_Icon_Helper_Data::ICON_TYPE_SQUARE_2.' adjdef adjsmall" onclick="return false;"><span></span>38</a><a href="#" class="icon'.AdjustWare_Icon_Helper_Data::ICON_TYPE_SQUARE_2.' adjdisabled adjsmall" onclick="return false;"><span></span>38</a><a href="#" class="icon'.AdjustWare_Icon_Helper_Data::ICON_TYPE_SQUARE_2.' adjselected adjsmall" onclick="return false;"><span></span>38</a>'),
					array('value' => AdjustWare_Icon_Helper_Data::ICON_TYPE_ROUND_2,
                        'label' => '<a href="#" class="icon'.AdjustWare_Icon_Helper_Data::ICON_TYPE_ROUND_2.' adjdef adjsmall" onclick="return false;"><span></span>38</a><a href="#" class="icon'.AdjustWare_Icon_Helper_Data::ICON_TYPE_ROUND_2.' adjdisabled adjsmall" onclick="return false;"><span></span>38</a><a href="#" class="icon'.AdjustWare_Icon_Helper_Data::ICON_TYPE_ROUND_2.' adjselected adjsmall" onclick="return false;"><span></span>38</a>')
                ),
                'after_element_html' => $html
            ));

			$fieldsetIcons->addField('icon_color', 'text', array(
				'label'     => $hlp->__('Icons Color'),
				'name'      => 'icon_color',
				'class'     => "color {hash:true, required:false, pickerPosition:'right', adjust:false}",
			));

			$fieldsetIcons->addField('icon_text_color', 'text', array(
				'label'     => $hlp->__('Icons Text Color'),
				'name'      => 'icon_text_color',
				'class'     => "color {hash:true, required:false, pickerPosition:'right', adjust:false}",
			));
        }

        $data = Mage::getSingleton('adminhtml/session')->getFormData();
        if ($data) {
            $form->setValues($data);
            Mage::getSingleton('adminhtml/session')->setFormData(null);
        }
        elseif ($model) {
            $form->setValues($model->getData());
			$form->addValues($model->getColorOptions());
        }
        
        return parent::_prepareForm();
    }
}