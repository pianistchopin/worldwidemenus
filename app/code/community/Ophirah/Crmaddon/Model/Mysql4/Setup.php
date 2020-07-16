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
 * @package     Crmaddon
 * @copyright   Copyright (c) 2020 Cart2Quote B.V. (https://www.cart2quote.com)
 * @license     https://www.cart2quote.com/ordering-licenses(https://www.cart2quote.com)
 */

/**
 * Class Ophirah_Crmaddon_Model_Mysql4_Setup
 */
class Ophirah_Crmaddon_Model_Mysql4_Setup extends Mage_Eav_Model_Entity_Setup //Mage_Sales_Model_Mysql4_Setup
{
    /**
     * Prepare database before install/upgrade
     *
     * @return Mage_Core_Model_Resource_Setup
     */
    public function startSetup() {
        $return = parent::startSetup();

        //trow event
        Mage::dispatchEvent('ophirah_crmaddon_mysql_setup_start');

        return $return;
    }

    /**
     * Prepare database after install/upgrade
     *
     * @return Mage_Core_Model_Resource_Setup
     */
    public function endSetup() {
        $return = parent::endSetup();

        //register that this update has ended
        $this->registerUpdate();

        //trow event
        Mage::dispatchEvent('ophirah_crmaddon_mysql_setup_end');

        return $return;
    }


    /**
     * Register the last installed update in the database
     */
    public function registerUpdate() {
        $currentInstallScript = $this->getCurrentInstallScript();

        if($currentInstallScript != ''){
            $lastUpdateVersion = substr(strrchr($currentInstallScript, "-"), 1);
            $lastUpdateVersion = str_replace(".php", "", $lastUpdateVersion);

            //add to database
            $this->setConfigData("qquoteadv_sales_representatives/last_update_version", $lastUpdateVersion);
        }
    }

    /**
     * Get the currently executed install/update script path
     *
     * @return string
     */
    public function getCurrentInstallScript() {
        $installFilename = '';

        //get the current install script name
        $included_files = get_included_files();
        foreach ($included_files as $filename) {
            if (strpos($filename, $this->_resourceName) !== false) {
                $installFilename = $filename;
            }
        }

        return $installFilename;
    }
}
