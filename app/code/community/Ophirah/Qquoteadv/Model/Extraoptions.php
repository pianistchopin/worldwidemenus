<?php
/**
 *
 * CART2QUOTE CONFIDENTIAL
 * __________________
 *
 *  [2009] - [2020] Cart2Quote B.V.
 *  All Rights Reserved.
 *
 * NOTICE OF LICENSE
 *
 * All information contained herein is, and remains
 * the property of Cart2Quote B.V. and its suppliers,
 * if any.  The intellectual and technical concepts contained
 * herein are proprietary to Cart2Quote B.V.
 * and its suppliers and may be covered by European and Foreign Patents,
 * patents in process, and are protected by trade secret or copyright law.
 * Dissemination of this information or reproduction of this material
 * is strictly forbidden unless prior written permission is obtained
 * from Cart2Quote B.V.
 *
 * @category    Ophirah
 * @package     Qquoteadv
 * @copyright   Copyright (c) 2020 Cart2Quote B.V. (https://www.cart2quote.com)
 * @license     https://www.cart2quote.com/ordering-licenses(https://www.cart2quote.com)
 */

/**
 * Class Ophirah_Qquoteadv_Model_Extraoptions
 */
class Ophirah_Qquoteadv_Model_Extraoptions extends Mage_Sales_Model_Quote
{
    // Defining Constants
    CONST FORM_INPUT = 'input ';
    CONST FORM_TEXTAREA = 'textarea ';
    CONST FORM_SELECT = 'select ';
    CONST FORM_REGION = 'region';
    CONST FORM_COUNTRY = 'country';
    CONST FORM_MULTI_SELECT = 'select multiple="multiple"';
    CONST FORM_POS1 = 'left';
    CONST FORM_POS2 = 'right';

    // EXAMPLE
    // create your own constant here
    // See your install script for column names
    // For options update implodeOptions()!
    CONST COLUMN_NAME_SHIPPING_SERVICE = ''; //give columnname from DB

    // EXAMPLE
    // create your own constant here
    // See your install script for option type numbers
    CONST OPTION_TYPE_SHIPPING_SERVICE = ''; //give option type from DB


    public function _construct()
    {
        parent::_construct();
        $this->_init('qquoteadv/extraoptions');
    }

    /**
     * Get and sort data for creating extra option fields in
     * frontend form for submitting quotation
     * 
     * @param   int|string|arrayarray       // Option types to add
     * @return  array || object             // Data for extra option fields
     */
    public function getExtraoptionsForm($typesToAdd = null)
    {
        /*  Needed:
         * 
         *  array  LEFT and RIGHT
         * 
         *  Each array:
         *  TITLE;
         *  CLASS;
         *  TYPE;
         *  INPUT;          input type
         *  OPTIONS;        depends input type
         *  COLUMNNAME;
         *  REQUIRED;       boolean
         * 
         */
        if ($typesToAdd == null) {
            return null;
        }

        // create empty arrays
        $optionToAdd = array();
        $extraOption = array();
        $AllExtraOptions = array();
        $return = array();

        $pos1 = self::FORM_POS1;
        $pos2 = self::FORM_POS2;

        $optionToAdd = $this->getAllOptionsToAdd($typesToAdd);

        // Create Array with all options to add
        // based on position
        foreach ($optionToAdd as $option) {
            $AllExtraOptions[$option['position']][] = $option['optionToAdd'];
        }

        $fieldCount = (isset($AllExtraOptions[$pos1])) ? count($AllExtraOptions[$pos1]) : 0;
        if (isset($AllExtraOptions[$pos2])) {
            $fieldCount = (count($AllExtraOptions[$pos2]) > $fieldCount) ? count($AllExtraOptions[$pos2]) : $fieldCount;
        }

        // Ordering all options to Add into one array
        // with subarray with position
        $return = array();
        $count = 0;

        while ($count < $fieldCount) {
            $input1 = $input2 = $addInput = '';
            if (isset($AllExtraOptions[$pos1][$count])) {
                $input1 = $AllExtraOptions[$pos1][$count];
            }
            $addInput[$pos1] = $input1;
            if (isset($AllExtraOptions[$pos2][$count])) {
                $input2 = $AllExtraOptions[$pos2][$count];
            }
            $addInput[$pos2] = $input2;

            $return[] = $addInput;
            $count++;
        }
        return $return;
    }

    /**
     * Create input HTML fields
     * 
     * @param   array       // array with options info
     * @param   boolean     // required or not
     * @return  string      // HTML string
     */
    public function createInputHtml($options, $required = false)
    {
        if ($required === true){
            $requiredSpan = '<span class="required">*</span>';
            $requiredClass = 'required-entry ';
        } else {
            $requiredSpan = '';
            $requiredClass = '';
        }

        if ($options['type'] == self::FORM_REGION){
            $customerId = '';
            $inputAdd = '<input onfocus="Element.setStyle(this, {color:\'#2F2F2F\'});" type="text"';
            $inputAdd .= 'name=\'' . $options['region']['name'] . '\' id=\'' . $options['region']['id'] . '\'';
            $inputAdd .= 'value="' . $options['region']['default'] . '"  title="' . $options['region']['title'] . '"';
            $inputAdd .= 'class="' . $requiredClass . $options['class'] . '" style="display:none;" />';
        } else {
            $customerId = 'customer:';
            $inputAdd = '';
        }

        ($options['input'] == self::FORM_MULTI_SELECT) ? $multiInput = '[]' : $multiInput = '';

        // creating variables
        $helper = Mage::helper('qquoteadv');
        $title = $helper->__($options['title']) . $requiredSpan . "<br />";

        $input = '<' . $options['input'];
        $input .= 'class="' . $requiredClass . $options['class'] . '"';
        $input .= 'name="customer[' . $options['columnname'] . ']' . $multiInput . '"';
        $input .= 'id="' . $customerId . $options['columnname'] . '">';

        if ($required === true) {
            $select = '';
        } else {
            $select = '<option value="0">&nbsp;</option>';
        }
        if (isset($options['options'])){
            foreach ($options['options'] as $option) {
                $select .= '<option value="' . $option['value'] . '">' . $helper->__($option['label']) . '</option>';
            }
        }

        $close = '</' . $options['input'] . '>';

        return $title . $input . $select . $close . $inputAdd;
    }

    /**
     * Create input HTML fields
     * 
     * @param   array           // array with options info
     * @param   string          // default selected
     * @return  string          // HTML string
     */
    public function createAdminHtml($options, $default)
    {

        if ($options['input'] == self::FORM_MULTI_SELECT) {
            $multiInput = '[]';
            $defaultSelected = explode(',', $default);
        } else {
            $multiInput = '';
            $defaultSelected = array($default);
        }

        // creating variables
        $helper = Mage::helper('qquoteadv');

        $input = '<' . $options['input'];
        $input .= 'class="' . $options['class'] . '"';
        $input .= 'name="extra_options[' . $options['columnname'] . ']' . $multiInput . '"';
        $input .= 'id="extra_options:' . $options['columnname'] . '">';

        $select = '';
        if ($options['options']){
            foreach ($options['options'] as $option) {
                $selected = (in_array($option['value'], $defaultSelected)) ? 'selected' : '';
                $select .= '<option value="' . $option['value'] . '" ' . $selected . '>' . $helper->__($option['label']) . '</option>';
            }
        }

        $close = '</' . $options['input'] . '>';

        return $input . $select . $close;
    }


    /**
     * Get all options to add to Form
     * 
     * @param   int|string|array       // Option Types to add
     * @return  array                  // Data to create input fields
     */
    public function getAllOptionsToAdd($typesToAdd)
    {
        if (!is_array($typesToAdd)) {
            $typesToAdd = array($typesToAdd);
        }

        $optionsToAdd = array();

        // Adding Shipping Service, Select Multiple
        // EXAMPLE
        /*
        if(in_array(self::OPTION_TYPE_SHIPPING_SERVICE, $typesToAdd) && Mage::getStoreConfig('qquoteadv_quote_form_builder/options/require_delivery_options') > 0 ){
            $optionsToAdd[] = $this->getShippingService();
        }
        */

        return $optionsToAdd;
    }

    // ######### EXAMPLE FUNCTION ##########
    /** Extra Options for Shipping Services
     * 
     * @return  array       // array['position'] 
     */
    public function getShippingService()
    {
        $optionType = self::OPTION_TYPE_SHIPPING_SERVICE;
        $selected = explode(',', Mage::getStoreConfig('qquoteadv_quote_form_builder/options/delivery_options'));
        array_push($selected, 'title');
        $collection = Mage::getModel('qquoteadv/extraoptions')->getCollection()
            ->addFieldToFilter('option_type', array('eq' => $optionType))
            ->addFieldToFilter('value', array('in' => $selected))
            ->addFieldToFilter('status', 1);

        $collection->getSelect()->order('order ASC');

        $options = array();
        foreach ($collection as $optionItem) {
            if ($optionItem->getData('title') == 1) {
                $optionTitle = $optionItem->getData('label');
            } else {
                $option['value'] = $optionItem->getData('value');
                $option['label'] = $optionItem->getData('label');
                $options[] = $option;
            }
        }

        $vars = array();
        $vars['title'] = $optionTitle;
        $vars['class'] = 'select multiselect';
        $vars['type'] = 'multiselect';
        $vars['input'] = self::FORM_MULTI_SELECT;
        $vars['options'] = $options;
        $vars['columnname'] = self::COLUMN_NAME_SHIPPING_SERVICE;
        $vars['required'] = (Mage::getStoreConfig('qquoteadv_quote_form_builder/options/require_delivery_options') == 2) ? true : false;

        $shippingService['position'] = self::FORM_POS1;
        $shippingService['optionToAdd'] = $vars;

        return $shippingService;
    }
    // ######### END EXAMPLE FUNCTION ##########    

    /** Retrieve selected options from DB, multiselect
     * 
     * @param   array           // Array with selected options
     * @param   int||string     // Option Type
     * @return  array           // Selected options and title
     */
    public function getMultipleoptionData($extraOptions, $optionType)
    {
        $return = array();
        $extraOptions = explode(',', $extraOptions);
        array_push($extraOptions, 'title');
        $collection = Mage::getModel('qquoteadv/extraoptions')->getCollection()
            ->addFieldToFilter('option_type', array('eq' => $optionType))
            ->addFieldToFilter('value', array('in' => $extraOptions))
            ->addFieldToFilter('status', 1);

        $collection->getSelect()->order('order ASC');

        foreach ($collection as $item) {
            if ($item->getData('title') == 1) {
                $return['title'] = $item->getData('label');
            } else {
                $return['options'][] = $item->getData();
            }
        }
        return $return;
    }

    /**
     * Retrieve selected option from DB
     * 
     * @param   int||string     // Selected option
     * @param   int||string     // Option Type
     * @return  array           // Selected option label and title
     */
    public function getOptionData($optionData, $optionType)
    {
        $return = array();
        $collection = Mage::getModel('qquoteadv/extraoptions')->getCollection()
            ->addFieldToFilter('option_type', array('eq' => $optionType))
            ->addFieldToFilter('status', 1);

        $collection->getSelect()->order('order ASC');

        foreach ($collection as $item) {
            if ($item->getData('title') == 1) {
                $return['title'] = $item->getData('label');
            } elseif ($item->getData('value') == $optionData) {
                $return['option'] = $item->getData('label');
            }
        }

        return $return;
    }

    /** Implode array data from multiselect from Form
     * 
     * @param   array           // Post data [customer]
     * @return  array           // Post data [customer] with imploded array
     */
    public function implodeOptions($post)
    {
        $return = false;

        // EXAMPLE
        // All columns with options should be added to this array!!
        // $columnNames = array( self::COLUMN_NAME_SHIPPING_SERVICE );
        $columnNames = array();

        foreach ($post as $key => $postItem) {
            if (in_array($key, $columnNames) && is_array($postItem)) {
                $post[$key] = implode(",", $postItem);
                $return = true;
            }
        }

        return ($return === true) ? $post : $return;
    }
}
