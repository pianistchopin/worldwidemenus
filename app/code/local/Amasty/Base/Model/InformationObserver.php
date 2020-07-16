<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Base
 */


class Amasty_Base_Model_InformationObserver
{
    const SEO_PARAMS = '?utm_source=extension&utm_medium=backend&utm_campaign=';
    const MAGENTO_VERSION = '_m1';

    protected $_fixConflict = 'https://amasty.com/knowledge-base/how-to-fix-3rd-party-extension-conflicts.html';

    protected $_block;

    protected $_moduleLink;

    protected $_moduleData = array();

    public function addInformationContent($observer)
    {
        $block = $observer->getBlock();
        if ($block) {
            $this->setBlock($block);
            $html = $this->_generateHtml();
            $block->setContentHtml($html);
        }
    }

    /**
     * @return string
     */
    protected function _generateHtml()
    {
        $html = '<div class="amasty-info-block">'
            . $this->_showVersionInfo()
            . $this->_showUserGuideLink()
            . $this->_additionalContent();

        $conflictHtml = $this->_showModuleConflicts()
            . $this->_showModuleExistingConflicts();

        if ($conflictHtml) {
            $html .= $this->_showConflictTitle() . $conflictHtml;
        }

        $html .= '</div>';

        return $html;
    }

    protected function _additionalContent()
    {
        $html = '';
        if ($content = $this->getBlock()->getAdditionalModuleContent()) {
            if (is_array($content)) {
                foreach ($content as $type => $message) {
                    $html .= '<div class="amasty-additional-content amasty-'
                        . $type
                        . '">'
                        . $message
                        . '</div>';
                }
            } else {
                $html = '<div class="amasty-additional-content">'
                    . $content
                    .'</div>';
            }
        }

        return $html;
    }

    /**
     * @return string
     */
    protected function _showVersionInfo()
    {
        $html = '<div class="amasty-module-version">';

        $currentVer = (string)Mage::getConfig()->getModuleConfig($this->getBlock()->getModuleCode())->version;
        if ($currentVer) {
            $isVersionLast = $this->_isLastVersion($currentVer);
            $class = $isVersionLast ? 'last-version' : '';
            $html .= '<span class="version-title">'
                . $this->getBaseHelper()->__('Extension version installed: ')
                . '</span>'
                . '<span class="module-version ' . $class . '">' . $currentVer . '</span>';

            if (!$isVersionLast) {
                $html .=
                    '<br/><span class="upgrade-error">'
                    . $this->getBaseHelper()->__(
                        'Update is available and recommended. See the '
                        . '<a target="_blank" href="%s">Change Log</a>',
                        $this->_getChangeLogLink()
                    )
                    . '</span>';
            }
        }

        $html .= '</div>';

        return $html;
    }

    /**
     * @return string
     */
    protected function _getChangeLogLink()
    {
        return $this->_getModuleLink()
            . $this->_getSeoparams() . 'changelog_' . $this->_getShortModuleName() . '#changelog';
    }

    /**
     * @return string
     */
    protected function _getSeoparams()
    {
        return self::SEO_PARAMS;
    }

    /**
     * @return array|mixed
     */
    protected function _getShortModuleName()
    {
        $code = $this->getBlock()->getModuleCode();
        $code = explode('_', $code);
        $code = end($code);

        return $code . self::MAGENTO_VERSION;
    }

    /**
     * @param $currentVer
     * @return bool
     */
    protected function _isLastVersion($currentVer)
    {
        $result = true;
        $allExtensions = Amasty_Base_Helper_Module::getAllExtensions();
        if ($allExtensions && isset($allExtensions[$this->getBlock()->getModuleCode()])) {
            $module = $allExtensions[$this->getBlock()->getModuleCode()];
            if ($module && is_array($module)) {
                $module = array_shift($module);
            }

            if (isset($module['version']) && version_compare($currentVer, $module['version'], '<')) {
                $result = false;
            }
        }

        return $result;
    }

    /**
     * @return string
     */
    protected function _getModuleLink()
    {
        if (!$this->_moduleLink) {
            $this->_moduleLink = '';
            $module = $this->_getModuleData();
            if ($module && isset($module['url']) && $module['url']) {
                $this->_moduleLink = $module['url'];
            }
        }

        return $this->_moduleLink;
    }

    /**
     * @return array
     */
    protected function _getModuleData()
    {
        if (!$this->_moduleData) {
            $this->_moduleData = array();
            $allExtensions = Amasty_Base_Helper_Module::getAllExtensions();
            if ($allExtensions && isset($allExtensions[$this->getBlock()->getModuleCode()])) {
                $module = $allExtensions[$this->getBlock()->getModuleCode()];
                if ($module && is_array($module)) {
                    $module = array_shift($module);
                }

                $this->_moduleData = $module;
            }
        }

        return $this->_moduleData;
    }

    /**
     * @return string
     */
    protected function _showModuleConflicts()
    {
        $html = '';
        $conflicts = $this->_getModuleConflicts();
        if ($conflicts) {
            $html = '<div class="amasty-conflicts">'
                . $this->getBaseHelper()->__(
                    'There are conflicts with the 3rd party modules: %s. <br/>'
                    . 'To fix the conflicts please follow the  <a target="_blank" href="%s">steps</a>.',
                    $conflicts,
                    $this->_getFixConflictGuide()
                )
                . '</div>';
        }


        return $html;
    }

    /**
     * @return string
     */
    protected function _showConflictTitle()
    {
        $html = '<div class="amasty-conflicts-title">'
            . $this->getBaseHelper()->__('Problems detected:')
            . '</div>';

        return $html;
    }

    /**
     * @return string
     */
    protected function _showUserGuideLink()
    {
        $html = '<div class="amasty-user-guide">'
            . $this->getBaseHelper()->__(
                'Need help with the settings?'
                . ' Please consult the <a target="_blank" href="%s">user guide</a> to configure the extension properly.',
                $this->_getUserGuideLink()
            )
            . '</div>';

        return $html;
    }

    protected function _getUserGuideLink()
    {
        $link = $this->getBlock()->getUserGuideLink();
        $seoLink = $this->_getSeoparams();
        if (strpos($link, '?') !== false) {
            $seoLink =str_replace('?', '&', $seoLink);
        }

        return $link . $seoLink . 'userguide_' . $this->_getShortModuleName();
    }

    /**
     * @return string
     */
    protected function _getModuleConflicts()
    {
        $result = array();
        $conflicts = Mage::helper("ambase")->getPossibleConflictsList();
        foreach ($conflicts as $type) {
            foreach ($type as $class) {
                if (isset($class['rewrite'])) {
                    foreach ($class['rewrite'] as $conflict) {
                        $classes = $this->_getModuleConflict($conflict);
                        if ($classes) {
                            $result[] = $classes;
                        }
                    }
                }
            }
        }

        $result = implode(', ', $result);
        $result = explode(', ', $result);
        $result = array_unique($result);
        $result = implode(', ', $result);


        return $result;
    }

    /**
     * @param $conflict
     * @return string
     */
    protected function _getModuleConflict($conflict)
    {
        $conflictModules = array();
        $isConflict = false;
        foreach ($conflict as $position => $class) {
            if (strpos($class, $this->getBlock()->getModuleCode()) !== false) {
                $isConflict = true;
                unset($conflict[$position]);
                break;
            }
        }

        if ($isConflict) {
            foreach ($conflict as $class) {
                if (strpos($class, 'Amasty') === false
                    && strpos($class, "Mageplace") === false
                ) {
                    $array = explode('_', $class);
                    if (count($array) >= 2) {
                        $conflictModules[] = implode('_', array($array[0], $array[1]));
                    }
                }
            }
        }

        return implode(', ', $conflictModules);
    }

    /**
     * @return string
     */
    protected function _getFixConflictGuide()
    {
        return $this->_fixConflict . $this->_getSeoparams() . 'faq_fix_conflict' . $this->_getShortModuleName();
    }

    /**
     * @return string
     */
    protected function _showModuleExistingConflicts()
    {
        $messages = array();
        foreach ($this->getKnownConflictExtensions() as $moduleName) {
            if (Mage::helper('core')->isModuleEnabled($moduleName)) {
                $messages[] = $this->getBaseHelper()->__(
                    'Our extension is not compatible with the %s. '
                    . 'To avoid the conflicts we strongly recommend turning off the 3rd party mod via "%s" file.',
                    'app/etc/modules/' . $moduleName . '.xml',
                    $moduleName
                );
            }
        }

        $html = '';
        if (count($messages)) {
            $html = '<div class="amasty-disable-extensions">';
            foreach ($messages as $message) {
                $html .= '<p>' . $message . '</p>';
            }

            $html .= '</div>';
        }

        return $html;
    }

    /**
     * @return array
     */
    protected function getKnownConflictExtensions()
    {
        $conflicts = $this->getBlock()->getKnownConflictExtensions();

        $module = $this->_getModuleData();
        if ($module && isset($module['conflictExtensions']) && $module['conflictExtensions']) {
            $fromSite = $module['conflictExtensions'];
            $conflictsFromSite = str_replace(' ', '', $fromSite);
            $conflictsFromSite = explode(',', $conflictsFromSite);
            $conflicts = array_merge($conflicts, $conflictsFromSite);
            $conflicts = array_unique($conflicts);
        }

        return $conflicts;
    }

    /**
     * @return mixed
     */
    public function getBlock()
    {
        return $this->_block;
    }

    /**
     * @param mixed $block
     */
    public function setBlock($block)
    {
        $this->_block = $block;
    }

    public function getBaseHelper()
    {
        return Mage::helper('ambase');
    }
}
