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
 * Class Ophirah_Qquoteadv_Model_Source_Alloworder
 */
class Ophirah_Qquoteadv_Model_Source_Alloworder extends Mage_Eav_Model_Entity_Attribute_Source_Abstract
{
    /**
     * Returns an array of options
     *
     * @return array
     */
    public function getAllOptions()
    {
        $options = array(
            array(
                'value' => '1',
                'label' => 'Yes'
            ),
            array(
                'value' => '0',
                'label' => 'No'
            )
        );
        return $options;
    }

    /**
     * Returns the label of a given option value
     *
     * @param $value
     * @return bool
     */
    public function getOptionText($value)
    {
        $options = $this->getAllOptions();
        foreach ($options as $option) {
            if (is_array($value)) {
                if (in_array($option['value'], $value)) {
                    return $option['label'];
                }
            } else {
                if ($option['value'] == $value) {
                    return $option['label'];
                }
            }

        }
        return false;
    }
}
