<?php
/**
 * @category	Solide Webservices
 * @package		Flexslider
 */

class SolideWebservices_Flexslider_Block_Adminhtml_Group_Edit_Tab_Page extends Mage_Adminhtml_Block_Widget_Form {

	protected function _prepareForm() {
	
		$_model = Mage::registry('group_data');
		$form = new Varien_Data_Form();
		$this->setForm($form);

		$fieldset = $form->addFieldset('page_form', array('legend'=>Mage::helper('flexslider')->__('Group Pages')));

		$fieldset->addField('pages', 'multiselect', array(
			'name'		=> 'pages[]',
			'label'		=> Mage::helper('flexslider')->__('Visible In'),
			'required'	=> false,
			'values'	=> Mage::getSingleton('flexslider/config_source_page')->toOptionArray(),
			'value'		=> $_model->getPageId()
		));

		return parent::_prepareForm();
	}
}
