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

class MT_Exitoffer_Model_Field
    extends Mage_Core_Model_Abstract
{


    protected function _construct()
    {
        $this->_init('exitoffer/field');
    }

    public function addNew($data)
    {
        if (!$this->isValidNew($data)) {
            return false;
        }
        if (isset($data['field_id'])) {
            unset($data['field_id']);
        }
        $field = Mage::getModel('exitoffer/field');
        $field->setData($data);
        $field->save();
//
        return true;
    }

    public function update($data)
    {
        if (!$this->isValidUpdate($data)) {
            return false;
        }
        $fieldId = $data['field_id'];
        unset($data['field_id']);
        $field = Mage::getModel('exitoffer/field')->load($fieldId);
        foreach ($data as $key => $value) {
            $field->setData($key, $value);
        }
        $field->save();

        return true;

    }

    public function isValidNew($data)
    {
        $helper = Mage::helper('exitoffer');
        if (!isset($data['popup_id']) || !is_numeric($data['popup_id'])) {
            throw new Mage_Core_Exception($helper->__('Exit intent popup is not defined'));
        }

        if (!isset($data['name']) || empty($data['name'])) {
            throw new Mage_Core_Exception($helper->__('Field "Name" is required!'));
        }

        $popupId = $data['popup_id'];
        $popup = Mage::getModel('exitoffer/popup')->load($popupId);

        $field = Mage::getModel('exitoffer/field')->getCollection()
            ->addFieldToFilter('popup_id', $popup->getId())
            ->addFieldToFilter('name', $data['name']);

        if ($field->count() > 0) {
            throw new Mage_Core_Exception($helper->__('Field "Name" is already exist.'));
        }

        return true;
    }


    public function isValidUpdate($data)
    {
        $helper = Mage::helper('exitoffer');
        if (!isset($data['popup_id']) || !is_numeric($data['popup_id'])) {
            throw new Mage_Core_Exception($helper->__('Exit intent popup is not defined'));
        }

        if (!isset($data['field_id']) || !is_numeric($data['field_id'])) {
            throw new Mage_Core_Exception($helper->__('Field is not defined'));
        }

        if (!isset($data['name']) || empty($data['name'])) {
            throw new Mage_Core_Exception($helper->__('Field "Name" is required!'));
        }

        $popupId = $data['popup_id'];
        $fieldId = $data['field_id'];
        $popup = Mage::getModel('exitoffer/popup')->load($popupId);

        $field = Mage::getModel('exitoffer/field')->getCollection()
            ->addFieldToFilter('popup_id', $popup->getId())
            ->addFieldToFilter('entity_id', array('neq' => $fieldId))
            ->addFieldToFilter('name', $data['name']);

        if ($field->count() > 0) {
            throw new Mage_Core_Exception($helper->__('Field "Name" is already exist.'));
        }

        return true;
    }

    public function getHtml()
    {
        $block = Mage::getBlockSingleton('exitoffer/popup_field')
            ->setData($this->getData())
            ->setData('options', $this->getOptionsAsArray())
            ->setTemplate('mt/exitoffer/popup/field/'. $this->getType(). '.phtml');

        return $block->toHtml();
    }

    public function getOptionsAsArray()
    {
        $options = $this->getOptions();
        if (empty($options)) {
            return array();
        }

        $options = explode('|', $options);
        return $options;
    }
}