<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_GeoipRedirect
 */
class Amasty_GeoipRedirect_Block_System_Config_Form_Field_Url extends Mage_Adminhtml_Block_System_Config_Form_Field_Array_Abstract
{
    public function __construct()
    {
        $this->addColumn('country_url', array(
            'label' => Mage::helper('amgeoipredirect')->__('Country'),
            'style' => 'width:120px',
        ));
        $this->addColumn('url_mapping', array(
            'label' => Mage::helper('amgeoipredirect')->__('Url'),
            'style' => 'width:120px',
        ));
        $this->_addAfter = false;
        $this->_addButtonLabel = Mage::helper('amgeoipredirect')->__('Add');

        $this->setTemplate('amasty/geoipredirect/system/config/form/field/array.phtml');
        parent::__construct();
    }

    protected function _renderCellTemplate($columnName)
    {
        if (empty($this->_columns[$columnName])) {
            throw new Exception('Wrong column name specified.');
        }
        $column = $this->_columns[$columnName];
        $name = $this->getElement()->getName() . '[#{_id}][' . $columnName . ']';

        if ($columnName == 'country_url') {
            $options = Mage::getResourceModel('directory/country_collection')->loadData()->toOptionArray(false);
            $country = '<select multiple="multiple" name="' . $name . '[]">';
            foreach ($options as $option) {
                $country .= '<option value="' . $option['value'] . '">' . addslashes($option['label']) . '</option>';
            }
            $country .= '</select>';
            return $country;
        }
        return '<input type="text" name="' . $name . '" value="#{' . $columnName . '}" ' .
        ($column['size'] ? 'size="' . $column['size'] . '"' : '') . ' class="' .
        (isset($column['class']) ? $column['class'] : 'input-text') . '"' .
        (isset($column['style']) ? ' style="' . $column['style'] . '"' : '') . '/>';

    }
}
