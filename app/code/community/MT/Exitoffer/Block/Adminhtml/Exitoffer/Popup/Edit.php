<?php
/**
 * MB "Vienas bitas" (Magetrend.com)
 *
 * PHP version 5.2 or later
 *
 * @category MageTrend
 * @package  MT/Exitoffer
 * @author   Edvinas Stulpinas <edwin@magetrend.com>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     https://www.magetrend.com/exit-intent-popup
 */

class MT_Exitoffer_Block_Adminhtml_Exitoffer_Popup_Edit extends Mage_Adminhtml_Block_Widget_Form_Container {

    public function __construct()
    {
        parent::__construct();
        $helper = Mage::helper('exitoffer');
        $this->_objectId = 'id';
        $this->_blockGroup = 'exitoffer';
        $this->_controller = 'adminhtml_exitoffer_popup';
        $this->_mode = 'edit';
        $this->_updateButton('save', 'label', $helper->__('Save'));
        $this->_updateButton('delete', 'label', $helper->__('Delete'));
        $objId = $this->getRequest()->getParam($this->_objectId);

        $popup = Mage::registry('exitoffer_popup_data');
        if (!$popup->getId() && !Mage::app()->getRequest()->getParam('content_type')) {
            $this->_removeButton('save');
            $this->_removeButton('delete');
            $this->_removeButton('reset');
        } else {
            $this->_addButton('saveandcontinue', array(
                'label' => $helper->__('Save And Continue Edit'),
                'onclick' => 'saveAndContinueEdit()',
                'class' => 'save',
            ), -100);
        }

        $this->_formScripts[] = " function saveAndContinueEdit(){ editForm.submit($('edit_form').action + 'back/edit/') } ";
    }

    public function getDeleteAllUrl()
    {
        $objId = $this->getRequest()->getParam($this->_objectId);
        return Mage::helper("adminhtml")->getUrl('*/*/deleteall', array('id' => $objId));
    }

    public function getHeaderText()
    {
        $series = Mage::registry('exitoffer_popup_data');
        if ($series && $series->getId()) {
            return Mage::helper('exitoffer')->__('Edit Popup "%s"', $this->htmlEscape($series->getName()));
        } else {
            return Mage::helper('exitoffer')->__('New Popup');
        }
    }
}