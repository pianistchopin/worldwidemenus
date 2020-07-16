<?php
/**
 * MageWorx
 * GeoIP Extension
 *
 * @category   MageWorx
 * @package    MageWorx_GeoIP
 * @copyright  Copyright (c) 2015 MageWorx (http://www.mageworx.com/)
 */

$installer = $this;

$pathLike = 'mageworx_customers/geoip/%';
$configCollection = Mage::getModel('core/config_data')->getCollection();
$configCollection->getSelect()->where('path like ?', $pathLike);

foreach ($configCollection as $conf) {
    $path = $conf->getPath();
    $path = str_replace('mageworx_customers', 'mageworx_geoip', $path);
    $conf->setPath($path)->save();
}

$installer->endSetup();