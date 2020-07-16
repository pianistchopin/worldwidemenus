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

class MT_Exitoffer_Block_Adminhtml_System_Config_Form_Button_Grid
    extends Mage_Adminhtml_Block_System_Config_Form_Field_Array_Abstract
{
    protected $_typeRender;

    protected $_yesnoRender;

    protected function _construct()
    {

        $this->addColumn('type', array(
            'label' => Mage::helper('exitoffer')->__('Field Type'),
            'style' => 'width:80px',
            'renderer' => $this->_getTypeRenderer()
        ));

        $this->addColumn('label', array(
            'label' => Mage::helper('exitoffer')->__('Frontend Label'),
            'style' => 'width:100px',
        ));

        $this->addColumn('name', array(
            'label' => Mage::helper('exitoffer')->__('Name'),
            'style' => 'width:100px',
        ));
        $this->addColumn('option', array(
            'label' => Mage::helper('exitoffer')->__('Options'),
            'style' => 'width:100px',
        ));

        $this->addColumn('required', array(
            'label' => Mage::helper('exitoffer')->__('Is Required'),
            'style' => 'width:100px !important',
            'renderer' => $this->_getYesnoRenderer()
        ));
        $this->addColumn('position', array(
            'label' => Mage::helper('exitoffer')->__('Position'),
            'style' => 'width:20px',
        ));

        $this->_addAfter = false;
        $this->_addButtonLabel = Mage::helper('exitoffer')->__('Add Field');

        parent::_construct();
    }

    protected function _getTypeRenderer()
    {
        if (!$this->_typeRender) {
            $this->_typeRender = Mage::app()->getLayout()->createBlock(
                'exitoffer/adminhtml_system_config_form_field_field', '',
                array('is_render_to_js_template' => true)
            );
            $this->_typeRender->setClass('small-input');
        }
        return $this->_typeRender;
    }

    protected function _getYesnoRenderer()
    {
        if (!$this->_yesnoRender) {
            $this->_yesnoRender = Mage::app()->getLayout()->createBlock(
                'exitoffer/adminhtml_system_config_form_field_yesno', '',
                array('is_render_to_js_template' => true)
            );
            $this->_yesnoRender->setClass('small-input');
        }
        return $this->_yesnoRender;
    }

    protected function _prepareArrayRow(Varien_Object $row)
    {
        $row->setData(
            'option_extra_attr_' . $this->_getTypeRenderer()->calcOptionHash($row->getData('type')),
            'selected="selected"'
        );
        $row->setData(
            'option_extra_attr_' . $this->_getYesnoRenderer()->calcOptionHash($row->getData('required')),
            'selected="selected"'
        );
    }

    public function calcOptionHash($optionValue)
    {
        return sprintf('%u', crc32($this->getName() . $this->getId() . $optionValue));
    }
}