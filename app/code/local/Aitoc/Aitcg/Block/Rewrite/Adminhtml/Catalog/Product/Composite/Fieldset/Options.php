<?php
class Aitoc_Aitcg_Block_Rewrite_Adminhtml_Catalog_Product_Composite_Fieldset_Options extends Mage_Adminhtml_Block_Catalog_Product_Composite_Fieldset_Options
{
    public function getOptionHtml(Mage_Catalog_Model_Product_Option $option)
    {
        if(! Mage::helper('aitcg/options')->checkAitOption( $option ) ) {
            return parent::getOptionHtml($option);
        }
        $renderer = $this->getOptionRender(
            $this->getGroupOfOption($option->getType())
        );
        if (is_null($renderer['renderer'])) {
            $renderer['renderer'] = $this->getLayout()->createBlock($renderer['block'])
                ->setTemplate('aitcg/catalog/product/composite/fieldset/options/type/cgfile.phtml')
                ->setSkipJsReloadPrice(1);
        }
        return $renderer['renderer']
            ->setProduct($this->getProduct())
            ->setOption($option)
            ->toHtml();
    }
}
