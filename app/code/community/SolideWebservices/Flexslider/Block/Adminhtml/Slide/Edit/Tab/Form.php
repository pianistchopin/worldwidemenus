<?php
/**
 * @category	Solide Webservices
 * @package		Flexslider
 */

class SolideWebservices_Flexslider_Block_Adminhtml_Slide_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form {

	/**
	 * Retrieve Additional Element Types
	 *
	 * @return array
	*/
	protected function _getAdditionalElementTypes() {
		return array(
			'image' => Mage::getConfig()->getBlockClassName('flexslider/adminhtml_slide_helper_image')
		);
	}

	protected function _prepareForm() {
		$form = new Varien_Data_Form();

		$form->setHtmlIdPrefix('slide_');
		$form->setFieldNameSuffix('slide');

		$this->setForm($form);

		$fieldset = $form->addFieldset('slide_general', array('legend'=> $this->__('General Information')));

		$this->_addElementTypes($fieldset);

		$group_id = $fieldset->addField('group_id', 'select', array(
			'name'			=> 'group_id',
			'label'			=> $this->__('Group'),
			'required'		=> true,
			'class'			=> 'required-entry',
			'values'		=> $this->_getGroups()
		));

		$title = $fieldset->addField('title', 'text', array(
			'name'		=> 'title',
			'label'		=> $this->__('Title'),
			'required'	=> true,
			'class'		=> 'required-entry'
		));
		
		$slidetype = $fieldset->addField('slidetype', 'select', array(
			'name'		=> 'slidetype',
			'label'		=> $this->__('Image or Video'),
			'disabled' 	=> $this->_addOrEdit(),
			'values'	=> Mage::getSingleton('flexslider/config_source_Slidetype')->toOptionArray()
		));

		$hosted_image = $fieldset->addField('hosted_image', 'select', array(
			'name'		=> 'hosted_image',
			'label'		=> Mage::helper('flexslider')->__('Use External Image Hosting'),
			'note'		=> Mage::helper('flexslider')->__('instead of uploading images you can host your images on a image hoster and just enter the link to the image and thumbnail'),
			'required'	=> true,
			'disabled' 	=> $this->_addOrEdit(),
			'values'	=> Mage::getSingleton('adminhtml/system_config_source_yesno')->toOptionArray(),
		));

		$hosted_image_url = $fieldset->addField('hosted_image_url', 'text', array(
			'name'		=> 'hosted_image_url',
			'label'		=> $this->__('Hosted Image URL')
		));

		$hosted_image_thumburl = $fieldset->addField('hosted_image_thumburl', 'text', array(
			'name'		=> 'hosted_image_thumburl',
			'label'		=> $this->__('Hosted Image Thumb URL'),
			'note'		=> Mage::helper('flexslider')->__('you can use the same URL as above but for performance reasons it\'s better to upload a seperate small thumbnail of this image, the thumbnails are used in carousels'),
		));

		$image = $fieldset->addField('image', 'image', array(
			'name'		=> 'image',
			'label'		=> $this->__('Image')
		));
		
		$video_id = $fieldset->addField('video_id', 'text', array(
			'name'		=> 'video_id',
			'label'		=> $this->__('Video ID'),
			'note'		=> Mage::helper('flexslider')->__('enter the video id of your YouTube or Vimeo video (not the full link)')
		));
		
		$video_autoplay = $fieldset->addField('video_autoplay', 'select', array(
			'name' => 'video_autoplay',
			'label' => $this->__('Autoplay Video'),
			'note'		=> Mage::helper('flexslider')->__('pause the slider and start the video automatically'),
			'required' => true,
			'values' => Mage::getModel('adminhtml/system_config_source_yesno')->toOptionArray()
		));

		$alt_text = $fieldset->addField('alt_text', 'text', array(
			'name'		=> 'alt_text',
			'label'		=> $this->__('ALT Text')
		));
		
		$url = $fieldset->addField('url', 'text', array(
			'name'		=> 'url',
			'label'		=> $this->__('URL')
		));

		$url_target = $fieldset->addField('url_target', 'select', array(
			'name'		=> 'url_target',
			'label'		=> $this->__('URL Target'),
			'values'	=> Mage::getSingleton('flexslider/config_source_URLTarget')->toOptionArray()
		));

		/* config WYSIWYG */
		$wysiwygConfig = Mage::getSingleton('cms/wysiwyg_config')->getConfig(array(
			'add_variables' => false,
			'add_widgets' => false,
			'add_images' => true,
			'files_browser_window_url' => Mage::getSingleton('adminhtml/url')->getUrl('adminhtml/cms_wysiwyg_images/index'),
			'files_browser_window_width' => (int) Mage::getConfig()->getNode('adminhtml/cms/browser/window_width'),
			'files_browser_window_height'=> (int) Mage::getConfig()->getNode('adminhtml/cms/browser/window_height')
         ));

		$html = $fieldset->addField('html', 'editor', array(
			'name'		=> 'html',
			'label'		=> $this->__('Caption'),
			'style'     => 'width:550px; height:300px;',
			'config' 	=> $wysiwygConfig,
			'wysiwyg'	=> true
		));

		$caption_position = $fieldset->addField('caption_position', 'select', array(
			'name' => 'caption_position',
			'label' => $this->__('Caption Position'),
			'values' => Mage::getModel('flexslider/config_source_captionposition')->toOptionArray()
		));
		
		$caption_animation = $fieldset->addField('caption_animation', 'select', array(
			'name' => 'caption_animation',
			'label' => $this->__('Caption Animation'),
			'note'		=> Mage::helper('flexslider')->__('should the caption animate into the slide'),
			'required' => true,
			'values' => Mage::getModel('adminhtml/system_config_source_yesno')->toOptionArray()
		));

		$sort_order = $fieldset->addField('sort_order', 'text', array(
			'name'		=> 'sort_order',
			'label'		=> $this->__('Sort Order'),
			'class'		=> 'validate-digits'
		));

		$slide_startdate = $fieldset->addField('slide_startdate', 'date', array(
			'name'		=> 'slide_startdate',
			'label'		=> $this->__('Slide Start Date'),
			'format' => 'yyyy-MM-dd HH:mm:ss',
			'time' => true,
			'image' => $this->getSkinUrl('images/grid-cal.gif'),
			'style'   => "width:140px",
			'note'		=> Mage::helper('flexslider')->__('leave empty to always show this slide')
		));

		$slide_enddate = $fieldset->addField('slide_enddate', 'date', array(
			'name'		=> 'slide_enddate',
			'label'		=> $this->__('Slide End Date'),
			'format' => 'yyyy-MM-dd HH:mm:ss',
			'time' => true,
			'image' => $this->getSkinUrl('images/grid-cal.gif'),
			'style'   => "width:140px",
			'note'		=> Mage::helper('flexslider')->__('leave empty to always show this slide')
		));

		$slide_loggedin = $fieldset->addField('slide_loggedin', 'select', array(
			'name' => 'slide_loggedin',
			'label' => $this->__('Show to logged in customers only?'),
			'required' => true,
			'values' => Mage::getModel('adminhtml/system_config_source_yesno')->toOptionArray()
		));

		$is_enabled = $fieldset->addField('is_enabled', 'select', array(
			'name' => 'is_enabled',
			'label' => $this->__('Enabled'),
			'required' => true,
			'values' => Mage::getModel('adminhtml/system_config_source_yesno')->toOptionArray()
		));

		if ($slide = Mage::registry('flexslider_slide')) {
			$form->setValues($slide->getData());
		}

		if (Mage::getSingleton('cms/wysiwyg_config')->isEnabled()) {
			$this->getLayout()->getBlock('head')->setCanLoadTinyMce(true);
		}

		if (version_compare(Mage::getVersion(), '1.7', '>=')) {
			$this->setChild('form_after', $this->getLayout()->createBlock('adminhtml/widget_form_element_dependence')
				->addFieldMap($slidetype->getHtmlId(), 				$slidetype->getName())
				->addFieldMap($hosted_image->getHtmlId(), 			$hosted_image->getName())
				->addFieldMap($hosted_image_url->getHtmlId(), 		$hosted_image_url->getName())
				->addFieldMap($hosted_image_thumburl->getHtmlId(), 	$hosted_image_thumburl->getName())
				->addFieldMap($image->getHtmlId(), 					$image->getName())
				->addFieldMap($video_id->getHtmlId(), 				$video_id->getName())
				->addFieldMap($video_autoplay->getHtmlId(), 		$video_autoplay->getName())
				->addFieldMap($alt_text->getHtmlId(), 				$alt_text->getName())
				->addFieldMap($url->getHtmlId(), 					$url->getName())
				->addFieldMap($url_target->getHtmlId(), 			$url_target->getName())
				->addFieldDependence(
					$image->getName(),
					$slidetype->getName(),
					'image'
				)
				->addFieldDependence(
					$hosted_image->getName(),
					$slidetype->getName(),
					'image'
				)
				->addFieldDependence(
					$hosted_image_url->getName(),
					$slidetype->getName(),
					'image'
				)
				->addFieldDependence(
					$hosted_image_thumburl->getName(),
					$slidetype->getName(),
					'image'
				)
				->addFieldDependence(
					$video_id->getName(),
					$slidetype->getName(),
					array('youtube','vimeo')
				)
				->addFieldDependence(
					$video_autoplay->getName(),
					$slidetype->getName(),
					array('youtube','vimeo')
				)
				->addFieldDependence(
					$alt_text->getName(),
					$slidetype->getName(),
					'image'
				)
				->addFieldDependence(
					$url->getName(),
					$slidetype->getName(),
					'image'
				)
				->addFieldDependence(
					$url_target->getName(),
					$slidetype->getName(),
					'image'
				)
				->addFieldDependence(
					$image->getName(),
					$hosted_image->getName(),
					0
				)
				->addFieldDependence(
					$hosted_image_url->getName(),
					$hosted_image->getName(),
					1
				)
				->addFieldDependence(
					$hosted_image_thumburl->getName(),
					$hosted_image->getName(),
					1
				)
			);
		}

		return parent::_prepareForm();
	}

	/**
	 * Retrieve an array of all of the slider groups
	 *
	 * @return array
	 */
	protected function _getGroups() {
		$groups = Mage::getResourceModel('flexslider/group_collection');
		$options = array('' => $this->__('-- Please Select --'));

		foreach($groups as $group) {
			$options[$group->getId()] = $group->getTitle();
		}

		return $options;
	}

	/**
	 * Check if we are adding or editing
	 *
	 * @return bool
	 */
	public function _addOrEdit() {
		if($this->getRequest()->getParam('id')) { return true; } else { return false; }
	}

}