<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Base
 */


class Amasty_Base_Adminhtml_Ambase_NotificationController extends Mage_Adminhtml_Controller_Action
{
    public function frequencyAction()
    {
        $action = $this->getRequest()->getParam('action');
        switch ($action) {
            case 'less':
                $this->increaseFrequency();
                break;
            case 'more':
                $this->decreaseFrequency();
                break;
            default:
                Mage::getSingleton('adminhtml/session')->addError(
                    $this->__(
                        'An error occurred while changing the frequency.'
                    )
                );
        }

        $this->_redirectReferer();
    }

    protected function decreaseFrequency()
    {
        $currentValue = $this->getCurrentValue();
        $allValues = Mage::getModel('ambase/source_frequency')->toOptionArray();
        $resultValue = null;
        foreach ($allValues as $option) {
            if ($option['value'] != $currentValue) {
                $resultValue = $option['value'];
            } else {
                if ($resultValue) {
                    $this->changeFrequency($resultValue);
                }

                break;
            }
        }

        Mage::getSingleton('adminhtml/session')->addSuccess(
            $this->__(
                'You will get more messages of this type. Notification frequency has been updated.'
            )
        );
    }

    protected function increaseFrequency()
    {
        $currentValue = $this->getCurrentValue();
        $allValues = Mage::getModel('ambase/source_frequency')->toOptionArray();
        $resultValue = null;
        foreach ($allValues as $option) {
            if ($option['value'] == $currentValue) {
                $resultValue = $option['value'];
            }

            if ($resultValue && $option['value'] != $resultValue) {
                $this->changeFrequency($option['value']);//save next option
                break;
            }
        }

        Mage::getSingleton('adminhtml/session')->addSuccess(
            $this->__(
                'You will get less messages of this type. Notification frequency has been updated.'
            )
        );
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('system/adminnotification/show_list');
    }

    protected function changeFrequency($value)
    {
        $config = Mage::getModel('core/config');
        /* @var $config Mage_Core_Model_Config */
        $config->saveConfig(Amasty_Base_Model_Feed::XML_FREQUENCY_PATH, $value);
        Mage::getConfig()->cleanCache();

        return $this;
    }

    protected function getCurrentValue()
    {
        return Mage::getStoreConfig(Amasty_Base_Model_Feed::XML_FREQUENCY_PATH);
    }
}  
