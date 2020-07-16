<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Base
 */


class Amasty_Base_Block_Adminhtml_Logo extends Mage_Adminhtml_Block_Widget_Form
{
    const SEO_PARAMS = '?utm_source=extension&utm_medium=backend&utm_campaign=';


    /**
     * @return string
     */
    private function getSeoparams()
    {
        return self::SEO_PARAMS;
    }

    /**
     * @return string
     */
    public function getLogoHref()
    {
        $href = 'https://amasty.com' . $this->getSeoparams() . 'amasty_logo';

        return $href;
    }
}
