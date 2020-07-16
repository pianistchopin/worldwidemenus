<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_GeoipRedirect
 */


class Amasty_GeoipRedirect_Block_Adminhtml_System_Config_Fieldset_Info
    extends Mage_Adminhtml_Block_System_Config_Form_Fieldset
{
    protected $_contentHtml = '';

    protected $_moduleCode = 'Amasty_GeoipRedirect';

    protected $_userGuideLink = 'https://amasty.com/docs/doku.php?id=magento_1:geoip_redirect';

    protected $_knownConflictExtensions = array();

    /**
     * @return array|string
     */
    public function getAdditionalModuleContent()
    {
        $messages = array();

        if (!@class_exists('Amasty_Base_Helper_String')) {
            $messages['error'] = Mage::helper('amgeoipredirect')->getUnserializeError();
        }

        return $messages;
    }

    /**
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    protected function _getHeaderCommentHtml($element)
    {
        $html = parent::_getHeaderCommentHtml($element);

        $this->setContentHtml($this->__('Please Update Amasty Base module. Re-upload it and replace all the files.'));
        Mage::dispatchEvent('amasty_base_add_information_content', array('block' => $this));
        $html .= $this->getContentHtml();

        return $html;
    }

    /**
     * @return array
     */
    public function getKnownConflictExtensions()
    {
        return $this->_knownConflictExtensions;
    }

    /**
     * @return string
     */
    public function getModuleCode()
    {
        return $this->_moduleCode;
    }

    /**
     * @return string
     */
    public function getUserGuideLink()
    {
        return $this->_userGuideLink;
    }

    /**
     * @param string $contentHtml
     */
    public function setContentHtml($contentHtml)
    {
        $this->_contentHtml = $contentHtml;
    }

    /**
     * @return string
     */
    public function getContentHtml()
    {
        return $this->_contentHtml;
    }
}
