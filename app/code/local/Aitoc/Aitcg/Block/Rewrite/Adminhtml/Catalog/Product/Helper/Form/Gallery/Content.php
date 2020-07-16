<?php
class Aitoc_Aitcg_Block_Rewrite_Adminhtml_Catalog_Product_Helper_Form_Gallery_Content extends Mage_Adminhtml_Block_Catalog_Product_Helper_Form_Gallery_Content
{

    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('aitcommonfiles/design--adminhtml--default--default--template--catalog--product--helper--gallery.phtml');
    }

    protected function _afterToHtml($html)
    {
        $html .= '<script type="text/javascript">' . "\n";
        $html .= '//<![CDATA[' . "\n";

        $html .= 'var removeAitcgConfirmText = "' . Mage::helper('aitcg')->__('By deleting \'Custom Product Preview\' image product option may not work') . '";' . "\n";
        if (is_null($this->getUploader()->getJsObjectName())) {
            $html .= 'var ' . $this->getJsObjectName() . ' = new Product.AitGallery(\'' . $this->getHtmlId() . '\', ' . $this->getImageTypesJson() .');' . "\n";
        } else {
            $html .= 'var ' . $this->getJsObjectName() . ' = new Product.AitGallery(\'' . $this->getHtmlId() . '\', '. ($this->getElement()->getReadonly() ? 'null' : $this->getUploader()->getJsObjectName()) . ', ' . $this->getImageTypesJson() . ');' . "\n";
        }
        $html .= '//]]>' . "\n";
        $html .= '</script>' . "\n";
        return $html;
    }
}
