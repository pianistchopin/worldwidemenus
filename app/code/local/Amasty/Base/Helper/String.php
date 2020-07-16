<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Base
 */


/**
 * Class for security unserialize string (not support object)
 */
class Amasty_Base_Helper_String
{
    /**
     * UnSerialize string
     *
     * @param $str
     *
     * @return mixed|false
     */
    public static function unserialize($str)
    {
        try {
            if (@class_exists('Unserialize_Reader_ArrValue')) {
                $reader = new Unserialize_Reader_ArrValue('data');
            } else {
                $reader = new Amasty_Unserialize_Reader_ArrValue('data');
            }

            $prevChar = null;
            for ($i = 0; $i < strlen($str); $i++) {
                $char = $str[$i];
                $result = $reader->read($char, $prevChar);
                if (!is_null($result)) {
                    return $result;
                }
                $prevChar = $char;
            }
        } catch (Exception $ex) {
            return false;
        }
    }
}
