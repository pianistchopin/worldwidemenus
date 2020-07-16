<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Base
 */


class Amasty_Base_Model_Config_Backend_Unsubscribe extends Mage_Core_Model_Config_Data
{
    const PATH_TO_FEED_IMAGES = 'https://notification.amasty.com/';

    public function _afterSave()
    {
        if ($this->isValueChanged()) {
            $value = explode(',', $this->getValue());
            if (in_array(Amasty_Base_Model_Source_Type::UNSUBSCRIBE_ALL, $value)) {
                $changes = array(Amasty_Base_Model_Source_Type::UNSUBSCRIBE_ALL);
            } else {
                $oldValue = explode(',', $this->getOldValue());
                $changes = array_diff($oldValue, $value);
                $changes = array_diff($changes, array(Amasty_Base_Model_Source_Type::UNSUBSCRIBE_ALL));
            }

            if (!empty($changes)) {
                foreach ($changes as $change) {
                    $message = $this->generateMessage($change);
                    Mage::getSingleton('adminhtml/session')->addSuccess($message);
                }
            }
        }

        return parent::_afterSave();
    }

    protected function generateMessage($change)
    {
        $message = '';
        $titles = Mage::getModel('ambase/source_type')->toOptionArray();
        foreach ($titles as $title) {
            if ($title['value'] == $change) {
                if ($change == Amasty_Base_Model_Source_Type::UNSUBSCRIBE_ALL) {
                    $label = $this->getHelper()->__('All Notifications');
                } else {
                    $label = $title['label'];
                }

                $message = '<img class="ambase-unsubscribe" src="' . $this->generateLink($change) .'"/><span>'
                    . $this->getHelper()->__('You have successfully unsubscribed from %s.', $label) .'</span>';
                break;
            }
        }

        return $message;
    }

    /**
     * @param $change
     * @return string
     */
    protected function generateLink($change)
    {
        $change = mb_strtolower($change);

        return self::PATH_TO_FEED_IMAGES . $change . '.svg';
    }

    /**
     * @return Amasty_Base_Helper_Data
     */
    protected function getHelper()
    {
        return Mage::helper('ambase');
    }
}
