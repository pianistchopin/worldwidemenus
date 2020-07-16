<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Base
 */


class Amasty_Base_Block_Adminhtml_Notification_Grid_Renderer_Actions
    extends Mage_Adminhtml_Block_Notification_Grid_Renderer_Actions
{
    /**
     * Renders grid column
     *
     * @param   Varien_Object $row
     * @return  string
     */
    public function render(Varien_Object $row)
    {
        $result = parent::render($row);
        if ($row->getData('is_amasty')) {
            $result .= sprintf(
                ' | <a class="action" href="%s" title="%s">%s</a> | ',
                $this->getUrl(
                    'adminhtml/ambase_notification/frequency/',
                    array(
                        'action'=> 'less',
                        Mage_Core_Controller_Front_Action::PARAM_NAME_URL_ENCODED => $this->helper('core/url')->getEncodedUrl()
                    )
                ),
                $this->__('Show less of these messages'),
                $this->__('Show less of these messages')
            );
            $result .= sprintf(
                '<a class="action" href="%s" title="%s">%s</a> | ',
                $this->getUrl(
                    'adminhtml/ambase_notification/frequency/',
                    array(
                        'action'=> 'more',
                        Mage_Core_Controller_Front_Action::PARAM_NAME_URL_ENCODED => $this->helper('core/url')->getEncodedUrl()
                    )
                ),
                $this->__('Show more of these messages'),
                $this->__('Show more of these messages')
            );
            $result .= sprintf(
                '<a class="action" href="%s" title="%s">%s</a>',
                $this->getUrl('adminhtml/system_config/edit/'). 'section/ambase',
                $this->__('Unsubscribe'),
                $this->__('Unsubscribe')
            );
        }

        return $result;
    }
}
