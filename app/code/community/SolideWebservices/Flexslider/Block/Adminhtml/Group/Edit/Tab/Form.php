<?php
/**
 * @category	Solide Webservices
 * @package		Flexslider
 */

class SolideWebservices_Flexslider_Block_Adminhtml_Group_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form {

	protected function _prepareForm() {

		$_model = Mage::registry('group_data');
		$form = new Varien_Data_Form();

		$this->setForm($form);

		$fieldset = $form->addFieldset('general_form', array('legend'=>Mage::helper('flexslider')->__('General Settings')));
		$fieldset->addType('customselect','SolideWebservices_Flexslider_Varien_Data_Form_Element_Customselect');

		$title = $fieldset->addField('title', 'text', array(
			'name'		=> 'title',
			'label'		=> Mage::helper('flexslider')->__('Title'),
			'required'	=> true,
			'class'		=> 'required-entry',
			'value'		=> $_model->getTitle()
		));

		$code = $fieldset->addField('code', 'text', array(
			'name'		=> 'code',
			'label'		=> Mage::helper('flexslider')->__('Code'),
			'note'		=> Mage::helper('flexslider')->__('a unique identifier that is used to inject the slide group via XML'),
			'required'	=> true,
			'class'		=> 'required-entry validate-code',
			'value'		=> $_model->getCode()
		));

		$position = $fieldset->addField('position', 'customselect', array(
			'name'		=> 'position',
			'label'		=> Mage::helper('flexslider')->__('Position'),
			'required'	=> true,
			'values'	=> Mage::getSingleton('flexslider/config_source_position')->toOptionArray(),
			'value'		=> $_model->getPosition()
		));

		$sort_order = $fieldset->addField('sort_order', 'text', array(
			'name'		=> 'sort_order',
			'label'		=> Mage::helper('flexslider')->__('Sort Order'),
			'note'		=> Mage::helper('flexslider')->__('set the sort order in case of multiple sliders on one page'),
			'value'		=> $_model->getSortOrder()
		));

		$slider_random = $fieldset->addField('slider_random', 'select', array(
			'name'		=> 'slider_random',
			'label'		=> Mage::helper('flexslider')->__('Random Order'),
			'note'		=> Mage::helper('flexslider')->__('set the sort order of the slides as random or use the order configured in the slides'),
			'values'	=> Mage::getModel('adminhtml/system_config_source_yesno')->toOptionArray(),
			'value'		=> $_model->getSliderRandom()
		));

		$slider_startdate = $fieldset->addField('slider_startdate', 'date', array(
			'name'		=> 'slider_startdate',
			'label'		=> $this->__('Slider Start Date'),
			'format' => 'yyyy-MM-dd HH:mm:ss',
			'time' => true,
			'image' => $this->getSkinUrl('images/grid-cal.gif'),
			'style'   => "width:140px",
			'note'		=> Mage::helper('flexslider')->__('leave empty to always show this group'),
			'value'		=> $_model->getSliderStartdate()
		));

		$slider_enddate = $fieldset->addField('slider_enddate', 'date', array(
			'name'		=> 'slider_enddate',
			'label'		=> $this->__('Slider End Date'),
			'format' => 'yyyy-MM-dd HH:mm:ss',
			'time' => true,
			'image' => $this->getSkinUrl('images/grid-cal.gif'),
			'style'   => "width:140px",
			'note'		=> Mage::helper('flexslider')->__('leave empty to always show this group'),
			'value'		=> $_model->getSliderEnddate()
		));

		$slider_loggedin = $fieldset->addField('slider_loggedin', 'select', array(
			'name'		=> 'slider_loggedin',
			'label'		=> Mage::helper('flexslider')->__('Show to logged in customers only?'),
			'values'	=> Mage::getModel('adminhtml/system_config_source_yesno')->toOptionArray(),
			'value'		=> $_model->getSliderLoggedin()
		));

		$is_active = $fieldset->addField('is_active', 'select', array(
			'name'		=> 'is_active',
			'label'		=> Mage::helper('flexslider')->__('Is Enabled'),
			'required'	=> true,
			'values'	=> Mage::getSingleton('adminhtml/system_config_source_yesno')->toOptionArray(),
			'value'		=> $_model->getIsActive()
		));

		if (!Mage::app()->isSingleStoreMode()) {
			$stores = $fieldset->addField('stores', 'multiselect', array(
				'name'		=> 'stores[]',
				'label'		=> Mage::helper('flexslider')->__('Visible In'),
				'required'	=> true,
				'values'	=> Mage::getSingleton('adminhtml/system_store')->getStoreValuesForForm(),
				'value'		=> $_model->getStoreId()
			));
		}
		else {
			$stores = $fieldset->addField('stores', 'hidden', array(
				'name'		=> 'stores[]',
				'value'		=> Mage::app()->getStore(true)->getId()
			));
		}

		$fieldset = $form->addFieldset('group_style', array('legend'=>Mage::helper('flexslider')->__('Slider Style')));

		$type = $fieldset->addField('type', 'select', array(
			'name'		=> 'type',
			'label'		=> Mage::helper('flexslider')->__('Slider Type'),
			'values'	=> Mage::getModel('flexslider/config_source_type')->toOptionArray(),
			'value'		=> $_model->getType()
		));

		$thumbnail_size = $fieldset->addField('thumbnail_size', 'text', array(
			'name'		=> 'thumbnail_size',
			'label'		=> Mage::helper('flexslider')->__('Thumbnail Width'),
			'note'		=> Mage::helper('flexslider')->__('width of the images in carousel, should not be larger then thumbnail upload width in general settings (default is 200)'),
			'class'		=> 'validate-greater-than-zero',
			'value'		=> $this->returnThumbnailSize()
		));

		$carousel_minitems = $fieldset->addField('carousel_minitems', 'text', array(
			'name'		=> 'carousel_minitems',
			'label'		=> Mage::helper('flexslider')->__('Carousel Minimum Images'),
			'note'		=> Mage::helper('flexslider')->__('minimum number of carousel items that should be visible, items will resize fluidly when below this.'),
			'value'		=> $_model->getCarouselMinitems()
		));

		$carousel_maxitems = $fieldset->addField('carousel_maxitems', 'text', array(
			'name'		=> 'carousel_maxitems',
			'label'		=> Mage::helper('flexslider')->__('Carousel Maximum Images'),
			'note'		=> Mage::helper('flexslider')->__('maximum number of carousel items that should be visible, items will resize fluidly when above this limit.'),
			'value'		=> $_model->getCarouselMaxitems()
		));

		$carousel_move = $fieldset->addField('carousel_move', 'text', array(
			'name'		=> 'carousel_move',
			'label'		=> Mage::helper('flexslider')->__('Carousel Move Images'),
			'note'		=> Mage::helper('flexslider')->__('number of carousel items that should move on animation, if 0 then the slider will move all visible items.'),
			'value'		=> $_model->getCarouselMove()
		));

		$overlay_position = $fieldset->addField('overlay_position', 'select', array(
			'name'		=> 'overlay_position',
			'label'		=> Mage::helper('flexslider')->__('Overlay Position'),
			'values'	=> Mage::getModel('flexslider/config_source_overlayposition')->toOptionArray(),
			'value'		=> $_model->getOverlayPosition()
		));

		$overlay_textcolor = $fieldset->addField('overlay_textcolor', 'text', array(
			'name'		=> 'overlay_textcolor',
			'label'		=> Mage::helper('flexslider')->__('Overlay Text Color'),
			'class'		=> 'colorpicker',
			'value'		=> $this->returnColor('getOverlayTextcolor', '#ffffff')
		));

		$overlay_bgcolor = $fieldset->addField('overlay_bgcolor', 'text', array(
			'name'		=> 'overlay_bgcolor',
			'label'		=> Mage::helper('flexslider')->__('Overlay Background Color'),
			'class'		=> 'colorpicker',
			'value'		=> $this->returnColor('getOverlayBgcolor', '#222222')
		));

		$overlay_hovercolor = $fieldset->addField('overlay_hovercolor', 'text', array(
			'name'		=> 'overlay_hovercolor',
			'label'		=> Mage::helper('flexslider')->__('Overlay Hover Color'),
			'class'		=> 'colorpicker',
			'value'		=> $this->returnColor('getOverlayHovercolor', '#666666')
		));

		$width = $fieldset->addField('width', 'text', array(
			'name'		=> 'width',
			'label'		=> Mage::helper('flexslider')->__('Maximum Width Slider'),
			'note'		=> Mage::helper('flexslider')->__('maximum width of the slider in pixels, leave empty or 0 for full responsive width'),
			'value'		=> $_model->getWidth()
		));

		$theme = $fieldset->addField('theme', 'select', array(
			'name'		=> 'theme',
			'label'		=> Mage::helper('flexslider')->__('Slider Theme'),
			'values'	=> Mage::getModel('flexslider/config_source_theme')->toOptionArray(),
			'value'		=> $_model->getTheme()
		));

		$custom_theme = $fieldset->addField('custom_theme', 'textarea', array(
			'name'		=> 'custom_theme',
			'label'		=> $this->__('Custom CSS'),
			'note'		=> Mage::helper('flexslider')->__('enter your custom css here'),
			'value'		=> $_model->getCustomTheme()
		));


		$fieldset = $form->addFieldset('group_nav', array('legend'=>Mage::helper('flexslider')->__('Navigation Style')));

		$nav_show = $fieldset->addField('nav_show', 'select', array(
			'name'		=> 'nav_show',
			'label'		=> Mage::helper('flexslider')->__('Show Navigation Arrows'),
			'values'	=> Mage::getModel('flexslider/config_source_navshow')->toOptionArray(),
			'value'		=> $_model->getNavShow()
		));

		$nav_style = $fieldset->addField('nav_style', 'select', array(
			'name'		=> 'nav_style',
			'label'		=> Mage::helper('flexslider')->__('Navigation Arrows Style'),
			'values'	=> Mage::getModel('flexslider/config_source_navstyle')->toOptionArray(),
			'value'		=> $_model->getNavStyle()
		));

		$nav_position = $fieldset->addField('nav_position', 'select', array(
			'name'		=> 'nav_position',
			'label'		=> Mage::helper('flexslider')->__('Navigation Arrows Position'),
			'values'	=> Mage::getModel('flexslider/config_source_navposition')->toOptionArray(),
			'value'		=> $_model->getNavPosition()
		));

		$nav_color = $fieldset->addField('nav_color', 'text', array(
			'name'		=> 'nav_color',
			'label'		=> Mage::helper('flexslider')->__('Navigation Arrows Color'),
			'class'		=> 'colorpicker',
			'value'		=> $this->returnColor('getNavColor', '#666666')
		));

		$fieldset = $form->addFieldset('group_pagination', array('legend'=>Mage::helper('flexslider')->__('Pagination Style')));

		$pagination_show = $fieldset->addField('pagination_show', 'select', array(
			'name'		=> 'pagination_show',
			'label'		=> Mage::helper('flexslider')->__('Show Pagination'),
			'values'	=> Mage::getModel('flexslider/config_source_paginationshow')->toOptionArray(),
			'value'		=> $_model->getPaginationShow()
		));

		$pagination_style = $fieldset->addField('pagination_style', 'select', array(
			'name'		=> 'pagination_style',
			'label'		=> Mage::helper('flexslider')->__('Pagination Style'),
			'values'	=> Mage::getModel('flexslider/config_source_pagination')->toOptionArray(),
			'value'		=> $_model->getPaginationStyle()
		));

		$pagination_position = $fieldset->addField('pagination_position', 'select', array(
			'name'		=> 'pagination_position',
			'label'		=> Mage::helper('flexslider')->__('Pagination Position'),
			'values'	=> Mage::getModel('flexslider/config_source_paginationposition')->toOptionArray(),
			'value'		=> $_model->getPaginationPosition()
		));

		$pagination_color = $fieldset->addField('pagination_color', 'text', array(
			'name'		=> 'pagination_color',
			'label'		=> Mage::helper('flexslider')->__('Pagination Color'),
			'class'		=> 'colorpicker',
			'value'		=> $this->returnColor('getPaginationColor', '#ffffff')
		));

		$fieldset = $form->addFieldset('group_loader', array('legend'=>Mage::helper('flexslider')->__('Loader Style')));

		$loader_show = $fieldset->addField('loader_show', 'select', array(
			'name'		=> 'loader_show',
			'label'		=> Mage::helper('flexslider')->__('Show Loader (progressbar)'),
			'values'	=> Mage::getSingleton('adminhtml/system_config_source_yesno')->toOptionArray(),
			'value'		=> $_model->getLoaderShow()
		));

		$loader_position = $fieldset->addField('loader_position', 'select', array(
			'name'		=> 'loader_position',
			'label'		=> Mage::helper('flexslider')->__('Loader Position'),
			'values'	=> Mage::getModel('flexslider/config_source_loaderposition')->toOptionArray(),
			'value'		=> $_model->getLoaderPosition()
		));

		$loader_color = $fieldset->addField('loader_color', 'text', array(
			'name'		=> 'loader_color',
			'label'		=> Mage::helper('flexslider')->__('Loader Color'),
			'class'		=> 'colorpicker',
			'value'		=> $this->returnColor('getLoaderColor', '#eeeeee')
		));

		$loader_bgcolor = $fieldset->addField('loader_bgcolor', 'text', array(
			'name'		=> 'loader_bgcolor',
			'label'		=> Mage::helper('flexslider')->__('Loader Gutter Color'),
			'class'		=> 'colorpicker',
			'value'		=> $this->returnColor('getLoaderBgcolor', '#222222')
		));

		$fieldset = $form->addFieldset('group_caption', array('legend'=>Mage::helper('flexslider')->__('Caption Style')));

		$fieldset->addField('caption_note', 'note', array(
			'text'     => Mage::helper('flexslider')->__('The caption is set per slide but these settings control their appearance'),
		));

		$caption_textcolor = $fieldset->addField('caption_textcolor', 'text', array(
			'name'		=> 'caption_textcolor',
			'label'		=> Mage::helper('flexslider')->__('Caption Default Text Color'),
			'class'		=> 'colorpicker',
			'value'		=> $this->returnColor('getCaptionTextcolor', '#ffffff')
		));

		$caption_bgcolor = $fieldset->addField('caption_bgcolor', 'text', array(
			'name'		=> 'caption_bgcolor',
			'label'		=> Mage::helper('flexslider')->__('Caption Background Color'),
			'class'		=> 'colorpicker',
			'value'		=> $this->returnColor('getCaptionBgcolor', '#222222')
		));

		$fieldset = $form->addFieldset('group_effects', array('legend'=>Mage::helper('flexslider')->__('Slider Effects')));

		$slider_auto = $fieldset->addField('slider_auto', 'select', array(
			'name'		=> 'slider_auto',
			'label'		=> Mage::helper('flexslider')->__('Auto Start Animation'),
			'values'	=> Mage::getModel('adminhtml/system_config_source_yesno')->toOptionArray(),
			'value'		=> $_model->getSliderAuto()
		));

		$slider_animationloop = $fieldset->addField('slider_animationloop', 'select', array(
			'name'		=> 'slider_animationloop',
			'label'		=> Mage::helper('flexslider')->__('Loop Slider'),
			'values'	=> Mage::getModel('adminhtml/system_config_source_yesno')->toOptionArray(),
			'value'		=> $_model->getSliderAnimationloop()
		));

		$slider_pauseonaction = $fieldset->addField('slider_pauseonaction', 'select', array(
			'name'		=> 'slider_pauseonaction',
			'label'		=> Mage::helper('flexslider')->__('Stop Auto Slide On Navigation'),
			'values'	=> Mage::getModel('adminhtml/system_config_source_yesno')->toOptionArray(),
			'value'		=> $_model->getSliderPauseonaction()
		));

		$slider_pauseonhover = $fieldset->addField('slider_pauseonhover', 'select', array(
			'name'		=> 'slider_pauseonhover',
			'label'		=> Mage::helper('flexslider')->__('Pause Slider On Hover'),
			'values'	=> Mage::getModel('adminhtml/system_config_source_yesno')->toOptionArray(),
			'value'		=> $_model->getSliderPauseonhover()
		));

		$slider_animation = $fieldset->addField('slider_animation', 'select', array(
			'name'		=> 'slider_animation',
			'label'		=> Mage::helper('flexslider')->__('Animation Type'),
			'values'	=> Mage::getModel('flexslider/config_source_animation')->toOptionArray(),
			'value'		=> $_model->getSliderAnimation()
		));

		$slider_aniduration = $fieldset->addField('slider_aniduration', 'text', array(
			'name'		=> 'slider_aniduration',
			'label'		=> Mage::helper('flexslider')->__('Animation Duration'),
			'note'		=> Mage::helper('flexslider')->__('in milliseconds (default is 600)'),
			'required'	=> true,
			'class'		=> 'required-entry validate-greater-than-zero',
			'value'		=> $this->returnSliderAniduration()
		));

		$slider_direction = $fieldset->addField('slider_direction', 'select', array(
			'name'		=> 'slider_direction',
			'label'		=> Mage::helper('flexslider')->__('Animation Direction'),
			'values'	=> Mage::getModel('flexslider/config_source_direction')->toOptionArray(),
			'value'		=> $_model->getSliderDirection()
		));

		$slider_slideduration = $fieldset->addField('slider_slideduration', 'text', array(
			'name'		=> 'slider_slideduration',
			'label'		=> Mage::helper('flexslider')->__('Slide Duration'),
			'note'		=> Mage::helper('flexslider')->__('in milliseconds (default is 7000)'),
			'required'	=> true,
			'class'		=> 'required-entry validate-greater-than-zero',
			'value'		=> $this->returnSliderSlideduration()
		));

		$slider_easing = $fieldset->addField('slider_easing', 'select', array(
			'name'		=> 'slider_easing',
			'label'		=> Mage::helper('flexslider')->__('Easing Effect'),
			'values'	=> Mage::getModel('flexslider/config_source_easing')->toOptionArray(),
			'value'		=> $_model->getSliderEasing()
		));

		$slider_smoothheight = $fieldset->addField('slider_smoothheight', 'select', array(
			'name'		=> 'slider_smoothheight',
			'label'		=> Mage::helper('flexslider')->__('Smooth Height'),
			'note'		=> Mage::helper('flexslider')->__('allow slider to scale height if slider images differ in height'),
			'values'	=> Mage::getModel('adminhtml/system_config_source_yesno')->toOptionArray(),
			'value'		=> $_model->getSliderSmoothheight()
		));

		if( Mage::getSingleton('adminhtml/session')->getGroupData() ) {
			$form->setValues(Mage::getSingleton('adminhtml/session')->getGroupData());
			Mage::getSingleton('adminhtml/session')->setGroupData(null);
		}

		if (version_compare(Mage::getVersion(), '1.7', '>=')) {
			$this->setChild('form_after', $this->getLayout()->createBlock('adminhtml/widget_form_element_dependence')
				->addFieldMap($type->getHtmlId(), 					$type->getName())
				->addFieldMap($carousel_minitems->getHtmlId(), 		$carousel_minitems->getName())
				->addFieldMap($carousel_maxitems->getHtmlId(), 		$carousel_maxitems->getName())
				->addFieldMap($carousel_move->getHtmlId(), 			$carousel_move->getName())
				->addFieldMap($overlay_position->getHtmlId(), 		$overlay_position->getName())
				->addFieldMap($overlay_textcolor->getHtmlId(), 		$overlay_textcolor->getName())
				->addFieldMap($overlay_bgcolor->getHtmlId(), 		$overlay_bgcolor->getName())
				->addFieldMap($overlay_hovercolor->getHtmlId(), 	$overlay_hovercolor->getName())
				->addFieldMap($theme->getHtmlId(), 					$theme->getName())
				->addFieldMap($custom_theme->getHtmlId(), 			$custom_theme->getName())
				->addFieldMap($thumbnail_size->getHtmlId(), 		$thumbnail_size->getName())
				->addFieldMap($nav_show->getHtmlId(), 				$nav_show->getName())
				->addFieldMap($nav_style->getHtmlId(), 				$nav_style->getName())
				->addFieldMap($nav_position->getHtmlId(), 			$nav_position->getName())
				->addFieldMap($nav_color->getHtmlId(), 				$nav_color->getName())
				->addFieldMap($pagination_show->getHtmlId(), 		$pagination_show->getName())
				->addFieldMap($pagination_style->getHtmlId(), 		$pagination_style->getName())
				->addFieldMap($pagination_position->getHtmlId(), 	$pagination_position->getName())
				->addFieldMap($pagination_color->getHtmlId(), 		$pagination_color->getName())
				->addFieldMap($slider_auto->getHtmlId(), 			$slider_auto->getName())
				->addFieldMap($slider_animation->getHtmlId(), 		$slider_animation->getName())
				->addFieldMap($slider_animationloop->getHtmlId(), 	$slider_animationloop->getName())
				->addFieldMap($slider_pauseonaction->getHtmlId(), 	$slider_pauseonaction->getName())
				->addFieldMap($slider_pauseonhover->getHtmlId(), 	$slider_pauseonhover->getName())
				->addFieldMap($slider_direction->getHtmlId(), 		$slider_direction->getName())
				->addFieldMap($slider_easing->getHtmlId(), 			$slider_easing->getName())
				->addFieldMap($loader_show->getHtmlId(), 			$loader_show->getName())
				->addFieldMap($loader_position->getHtmlId(), 		$loader_position->getName())
				->addFieldMap($loader_color->getHtmlId(), 			$loader_color->getName())
				->addFieldMap($loader_bgcolor->getHtmlId(), 		$loader_bgcolor->getName())
				->addFieldDependence(
					$thumbnail_size->getName(),
					$type->getName(),
					array('carousel','basic-carousel')
				)
				->addFieldDependence(
					$carousel_minitems->getName(),
					$type->getName(),
					array('carousel','basic-carousel')
				)
				->addFieldDependence(
					$carousel_maxitems->getName(),
					$type->getName(),
					array('carousel','basic-carousel')
				)
				->addFieldDependence(
					$carousel_move->getName(),
					$type->getName(),
					array('carousel','basic-carousel')
				)
				->addFieldDependence(
					$overlay_position->getName(),
					$type->getName(),
					'overlay'
				)
				->addFieldDependence(
					$overlay_textcolor->getName(),
					$type->getName(),
					'overlay'
				)
				->addFieldDependence(
					$overlay_bgcolor->getName(),
					$type->getName(),
					'overlay'
				)
				->addFieldDependence(
					$overlay_hovercolor->getName(),
					$type->getName(),
					'overlay'
				)
				->addFieldDependence(
					$custom_theme->getName(),
					$theme->getName(),
					'custom'
				)
				->addFieldDependence(
					$nav_style->getName(),
					$nav_show->getName(),
					array('always','hover')
				)
				->addFieldDependence(
					$nav_position->getName(),
					$nav_show->getName(),
					array('always','hover')
				)
				->addFieldDependence(
					$nav_color->getName(),
					$nav_show->getName(),
					array('always','hover')
				)
				->addFieldDependence(
					$pagination_style->getName(),
					$pagination_show->getName(),
					array('always','hover')
				)
				->addFieldDependence(
					$pagination_position->getName(),
					$pagination_show->getName(),
					array('always','hover')
				)
				->addFieldDependence(
					$pagination_color->getName(),
					$pagination_show->getName(),
					array('always','hover')
				)
				->addFieldDependence(
					$slider_animationloop->getName(),
					$slider_auto->getName(),
					1
				)
				->addFieldDependence(
					$slider_direction->getName(),
					$slider_animation->getName(),
					'slide'
				)
				->addFieldDependence(
					$slider_easing->getName(),
					$slider_animation->getName(),
					'slide'
				)
				->addFieldDependence(
					$slider_pauseonaction->getName(),
					$slider_auto->getName(),
					1
				)
				->addFieldDependence(
					$slider_pauseonhover->getName(),
					$slider_auto->getName(),
					1
				)
				->addFieldDependence(
					$loader_position->getName(),
					$loader_show->getName(),
					1
				)
				->addFieldDependence(
					$loader_color->getName(),
					$loader_show->getName(),
					1
				)
				->addFieldDependence(
					$loader_bgcolor->getName(),
					$loader_show->getName(),
					1
				)
			);
		}

		return parent::_prepareForm();
	}

	public function returnColor($field, $color) {
		$_model = Mage::registry('group_data');
		if($_model->$field()) { return $_model->$field(); } else { return $color; }
	}

	public function returnSliderAniduration() {
		$_model = Mage::registry('group_data');
		if($_model->getSliderAniduration()) { return $_model->getSliderAniduration(); } else { return '600'; }
	}

	public function returnSliderSlideduration() {
		$_model = Mage::registry('group_data');
		if($_model->getSliderSlideduration()) { return $_model->getSliderSlideduration(); } else { return '7000'; }
	}

	public function returnThumbnailSize() {
		$_model = Mage::registry('group_data');
		if($_model->getThumbnailSize()) { return $_model->getThumbnailSize(); } else { return '200'; }
	}

}
