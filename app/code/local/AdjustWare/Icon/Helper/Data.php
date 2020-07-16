<?php
class AdjustWare_Icon_Helper_Data extends Mage_Core_Helper_Abstract
{
    const VISUALIZATION_TYPE_NONE = 0;
    const VISUALIZATION_TYPE_COLOR = 1;
    const VISUALIZATION_TYPE_IMAGES = 2;
    const VISUALIZATION_TYPE_ICONS = 3;

    const ICON_TYPE_COLOR_CIRCLE_1 = 0;
    const ICON_TYPE_COLOR_SQUARE_1 = 1;
    const ICON_TYPE_COLOR_ROUND_1  = 2;
    const ICON_TYPE_COLOR_CIRCLE_2 = 3;
    const ICON_TYPE_COLOR_SQUARE_2 = 4;
    const ICON_TYPE_COLOR_ROUND_2  = 5;

    const ICON_TYPE_CIRCLE_1 = 0;
    const ICON_TYPE_SQUARE_1 = 1;
    const ICON_TYPE_ROUND_1 = 2;
    const ICON_TYPE_CIRCLE_2 = 3;
    const ICON_TYPE_SQUARE_2 = 4;
    const ICON_TYPE_ROUND_2 = 5;

    // icons and titles
    protected $_icons = null;
    // attributes with images
    protected $_attributes = null;

    /*
     * compatibility with CPP extension
     */
    protected $_isCPPEnabled = null;

    // use array(_product) on the product page to uniform interface
    public function init($productCollection)
    {
        if (!is_null($this->_icons))
            return;

        $attributesCollection = $this->getAttributes();
        $ids = array();    
        foreach ($productCollection as $prod){
            foreach ($attributesCollection as $attr){
                if($prod->isConfigurable()) {
                    $attrOptions = $this->getConfigurableAttributes($prod, $attr->getAttributeCode());
                    $ids = array_merge($ids, array_keys($attrOptions));
                }
                $optionIds  = trim($prod->getData($attr->getAttributeCode()), ',');
                if ($optionIds){
                    $ids = array_merge($ids, explode(',', $optionIds));
                }
            }
        } 

        $this->_icons = Mage::getModel('adjicon/attribute')
                ->getIconsByOptions(array_unique($ids), Mage::app()->getStore()->getId());
    }

    // get only attributes with icons
    public function getAttributes()
    {
        if (is_null($this->_attributes)){
            $this->_attributes = Mage::getResourceModel('adjicon/attribute_collection')
                ->addUsedInProductListing()
                ->setOrder('pos','asc')
                ->load();
        }
        return $this->_attributes;
    }

    private function getConfigurableAttributes($product, $attributeCode)
    {
        $products = array();
        $toReturn = array();
        $attributes = $product->getTypeInstance()->getConfigurableAttributes($product)->getItems();
        $mainAttribute = $this->getFirstAttribute($attributes);
        $mainAttributeCode = $mainAttribute->getProductAttribute()->getAttributeCode();
        if($mainAttributeCode && ($mainAttributeCode == $attributeCode)) {
            $allProducts = $product->getTypeInstance(true)->getUsedProducts(null, $product);
            foreach ($allProducts as $_product) {
                if ($_product->isSaleable()) {
                    $products[] = $_product->getData($attributeCode);
                }
            }
            $attrOptions = Mage::getModel('adjicon/attribute')->load($mainAttribute->getAttributeId(), 'attribute_id')->getAttributeOptions();
            asort($attrOptions);
            foreach($attrOptions as $key => $value) {
                if(in_array($key, $products)) {
                    $toReturn[$key] = $value;
                }
            }
            return $toReturn;
        }
        return array();
    }

    public function display($product, $mode='view')
    {
        if (!$this->_icons)
            return '';

        $icons = array();  
        $prefix = ('view' == $mode ? 'v_' : 'pl_'); // show full image only on product page
        foreach ($this->getAttributes() as $attr){
            $code = $attr->getAttributeCode();
            if (!$code)
                continue;

            if($product->isConfigurable() && $mode != 'view') {
                $configOptions = $this->getConfigurableAttributes($product, $code);
                $optionIds = array_keys($configOptions);
            }
            else {
                $optionIds  = trim($product->getData($code), ',');
                $optionIds = explode(',', $optionIds);
            }

            if (!$optionIds){
                continue;
            }

            foreach ($optionIds as $id){
                if (!empty($this->_icons[$id])){
                    $icons[] = array(
                        'id' => $attr->getId(),
                        'optionId' => $id,
                        'title' => $this->_icons[$id][0],
                        'filename' => $prefix . $this->_icons[$id][1],
                        'color' => $this->_icons[$id][2],
                        'visualization_type' => $attr->getVisualizationType(),
                        'icon_type' => $attr->getIconType(),
                        'color_icon_type' => $attr->getColorIconType(),
                        'show_images_product' => $attr->getShowImagesProduct(),
                        'show_images_product_listing' => $attr->getUsedInProductListing()
                    );
                }
            }
        }

        $block = Mage::getModel('core/layout')->createBlock('core/template')
            ->setArea('frontend')
            ->setTemplate('adjicon/icons.phtml');
        $block->assign('_type', 'html')
            ->assign('_section', 'body')        
            ->setIcons($icons)
            ->setMode($mode); 

        return $block->toHtml();         
    }

    public function getImagesConfSet($product)
    {
        $options = array();
        $images = array();
        $isIcons = false;
        if($product->isConfigurable()) {
            $attributes = $product->getTypeInstance()->getConfigurableAttributes($product)->getItems();
            $mainAttribute = Mage::helper('adjicon')->getFirstAttribute($attributes);
            $adjImageModel = Mage::getModel('adjicon/image');
            foreach($mainAttribute->getPrices() as $option) {
                $options[] = $option['value_index'];
            }

            $size = Mage::helper('adjicon')->getThumbsSizes('large');
            foreach($options as $id) {
                $set = $adjImageModel->getOptionImagesSet($id, $mainAttribute->getProductId());
                foreach($set as &$row) {
                    $row['file']   = $adjImageModel->resize($row['file'], $size);
                }
                $images[$id]['files'] = $set;
                $baseImage = $adjImageModel->getOptionImageBase($id, $mainAttribute->getProductId());
                if(is_array($baseImage)) {
                    $baseImage['file'] = $adjImageModel->resize($baseImage['file'], $size);
                }
                $images[$id]['base'] = $baseImage;
                if(!empty($baseImage))
                {
                    $isIcons = true;
                }
            }
        }

        $adjAttributes = array(

            'baseMediaUrl'        => Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA).'adjconfigurable/',
            'productImage'        => '<img src="' . Mage::helper('catalog/image')->init($product, 'small_image')->resize(135) .'" width="135" height="135" />',
            'options'             => $images,
            'isIcons'             => $isIcons,
            'baseUrl'             => Mage::helper('catalog/image')->init($product, 'small_image')->resize(295)->__toString()
        );
        return $adjAttributes;
    }

    public function getIconsColorArray()
    {
        $result = array();
        foreach ($this->getAttributes() as $attr){
            $result[$attr->getId()] = $attr->getIconColor();
        }
        return $result;
    }

    public function getTextIconsColorArray()
    {
        $result = array();
        foreach ($this->getAttributes() as $attr){
            $result[$attr->getId()] = $attr->getIconTextColor();
        }
        return $result;
    }

    public function getIconSizeArray()
    {
        $result = array();
        $result['adjsmall']['icon'] = Mage::getStoreConfig('design/adjicon/layered_size');
        $result['adjsmall']['font'] = Mage::getStoreConfig('design/adjicon/layered_font_size');
        $result['adjmedium']['icon'] = Mage::getStoreConfig('design/adjicon/list_size');
        $result['adjmedium']['font'] = Mage::getStoreConfig('design/adjicon/list_font_size');
        $result['adjlarge']['icon'] = Mage::getStoreConfig('design/adjicon/product_size');
        $result['adjlarge']['font'] = Mage::getStoreConfig('design/adjicon/product_font_size');
        $result['adjoption']['icon'] = Mage::getStoreConfig('design/adjicon/option_size');
        $result['adjoption']['font'] = Mage::getStoreConfig('design/adjicon/option_font_size');
        return $result;
    }

    /**
     * @param string $key
     *
     * @return array|string
     */
    public function getThumbsSizes($key = '')
    {
        $result = array();
        $result['135'] = 135;
        $result['small'] = Mage::getStoreConfig('design/adjicon/small_thumbnail_width');
        $result['medium'] = Mage::getStoreConfig('design/adjicon/medium_thumbnail_width');
        $result['large'] = Mage::getStoreConfig('design/adjicon/large_thumbnail_width');
        if (strlen($key))
        {
            return $result[$key];
        }
        else
        {
            return $result;
        }
    }

    /**
     * @param int $thumbSize
     * @param string $fileName
     *
     * @return string
     */
    public function getThumbnailFileName($thumbSize, $fileName)
    {
        return $thumbSize . 'x_' . $fileName;
    }

    public function addIconsToFilters($filters)
    {
        // get option_id from items
        $ids = array();
        // get attributes_ids from items
        $attrIds = array();
        foreach ($filters as $f){
            if ($f->getItemsCount() && ($f instanceof Mage_Catalog_Block_Layer_Filter_Attribute)){
                $items = $f->getItems();
                foreach ($items as $item){    
                    $ids[] = $item->getValue();
                }
                $attrIds[] = $f->getAttributeModel()->getId();
            }
        }

        $attributes = $this->loadAttributes($attrIds);
        $icons = $this->loadIcons($ids);
        $colors = $this->loadColors($ids);

        // set values back to attributes(blocks) and options    
        foreach ($filters as $f){
            if (!($f->getItemsCount() && $f instanceof Mage_Catalog_Block_Layer_Filter_Attribute)){
                continue;
            }

            $attributeId = $f->getAttributeModel()->getId();
            if (isset($attributes[$attributeId])) {

                $a = $attributes[$attributeId];

                $f->addData($a->getData());

                if ($a->getVisualizationType() == self::VISUALIZATION_TYPE_IMAGES && $a->getShowImages()){
                    foreach ($f->getItems() as $item){
                        if (!empty($icons[$item->getValue()])){
                            $item->setIcon($icons[$item->getValue()]);
                        }
                    }
                }
                if ($a->getVisualizationType() == self::VISUALIZATION_TYPE_COLOR && $a->getShowImages()){
                    foreach ($f->getItems() as $item){
                        if (!empty($colors[$item->getValue()])){
                            $item->setColor($colors[$item->getValue()]);
                        }
                    }
                }
            }
        }
    }

    public function collectAttributeFilterItems($_item, $filterAttribute)
    {
        $onlyIcons = (2 == $filterAttribute->getColumnsNum());
        $qty = ($filterAttribute->getShowQty()) ? ' (' .  $_item->getCount() .')' : '';
        $label = ($onlyIcons) ? '' : $_item->getLabel();
        $href = 'href="' . $this->escapeHtml($_item->getUrl()) . '" ';

        switch($filterAttribute->getVisualizationType()) {
            case self::VISUALIZATION_TYPE_IMAGES:
                $label = ($_item->getIcon()) ? $label : $_item->getLabel();
                $icon = ($_item->getIcon()) ? '<img border="0" alt="'.$this->escapeHtml($_item->getLabel()).$qty.'" title="'.$this->escapeHtml($_item->getLabel()).$qty.'" src="'. Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA) . 'icons/l_'.$_item->getIcon().'" />' : '';
                $item = '<span class="adjicon_icon_img"><a '.$href.'>'.$icon.'<span class="label">'.$label.'</span></a></span>';
                break;
            case self::VISUALIZATION_TYPE_COLOR:
                $label = ($_item->getColor()) ? $label : $_item->getLabel();
                $htmlParams = ($_item->getColor()) ? 'class="adjcolor' . $filterAttribute->getColorIconType() .' adjdef adjsmall"' : '';
                $item = '<span class="adjicon_icon_img"><a '.$href . $htmlParams.' title="'.$this->escapeHtml($_item->getLabel()).$qty.'" style="background:'.$_item->getColor().'"></a>';
                $item .= ($label) ? '<a '.$href.'>'.$label.'</a>' : '';
                $item .= '</span>';
                break;
            case self::VISUALIZATION_TYPE_ICONS:
                $htmlParams = $href . 'class="icons-'.$filterAttribute->getId().' icon' . $filterAttribute->getIconType() . ' adjdef adjsmall"';
                $text = '&nbsp;' . $_item->getLabel() . '&nbsp;';
                $item = '<span class="adjicon_icon_img"><a '.$htmlParams.' title="'.$this->escapeHtml($_item->getLabel()).$qty.'"><span></span>'.$text.'</a>';
                $item .= ($label) ? '<a '.$href.'>'.$label.'</a>' : '';
                $item .= '</span>';
                break;
            default:
                $item = '<a '.$href.'>'.$this->escapeHtml($_item->getLabel()).'</a> ('.$_item->getCount().')';
                if(version_compare(Mage::getVersion(),'1.9.0.0','>='))
                {
                    $item = '<a '.$href.'>'.$this->escapeHtml($_item->getLabel()).' <span class="count">('.$_item->getCount().')</span></a>';
                }
        }

        return $item;
    }

    public function loadAttributes($attrIds)
    {
        $attrCollection = Mage::getResourceModel('adjicon/attribute_collection')
            ->addFieldToFilter('attribute_id', array('in'=>$attrIds))
            ->load();

        $attributes = array();
        foreach ($attrCollection as $row){
            $attributes[$row->getAttributeId()] = $row;
        }
        return $attributes;
    }

    public function loadIcons($ids)
    {
        $iconCollection = Mage::getResourceModel('adjicon/icon_collection')
            ->addFieldToFilter('option_id', array('in'=>$ids))
            ->load();

        $icons = array();
        foreach ($iconCollection as $row){
            $icons[$row->getOptionId()] = $row->getFilename();
        }
        return $icons;
    }

    public function loadColors($ids)
    {
        $colorCollection = Mage::getResourceModel('adjicon/color_collection')
            ->addFieldToFilter('option_id', array('in'=>$ids))
            ->load();

        $colors = array();
        foreach ($colorCollection as $row){
            $colors[$row->getOptionId()] = $row->getColor();
        }
        return $colors;
    }

    public function getAdditionalImagesTab()
    {
        return (Mage::registry('current_product')->isConfigurable()) ? 'adjicon/adminhtml_catalog_product_edit_tabs_images' : '';
    }

    public function getFirstAttribute($attributes)
    {
        $positions = array();
        foreach($attributes as $key => $attribute) {
            if(is_array($attribute)) {
                $positions[$key] = $attribute['position'];
            }
            else {
                $positions[$key] = $attribute->getPosition();
            }
        }
        $positions = array_keys($positions, min($positions));
        return $attributes[$positions[0]];
    }

    /*
     * compatibility with CPP extension
     * @return boolean
     */
    public function isCPPEnabled()
    {
        if($this->_isCPPEnabled === null){
            $this->_isCPPEnabled = Mage::getConfig()->getModuleConfig('Aitoc_Aitcg')->is('active', 'true');
        }

        return $this->_isCPPEnabled;
    }

    /*
     * @param Mage_Catalog_Model_Product $product
     * @return array
     */
    public function getConfigurableAttributeValues(Mage_Catalog_Model_Product $product)
    {
        $productInstance = $product->getTypeInstance(true);
        if(!($productInstance instanceof Mage_Catalog_Model_Product_Type_Configurable)){
            return array();
        }

        $productAttributeOptions = $productInstance->getConfigurableAttributesAsArray($product);
        $productAttribute = $this->getFirstAttribute($productAttributeOptions);
        $attributeOptions = array();
        foreach ($productAttribute['values'] as $attribute) {
            $attributeOptions[$attribute['value_index']] = $attribute['store_label'];
        }
        return $attributeOptions;
    }
}