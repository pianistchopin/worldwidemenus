<?php
/**
 * Install Custom Products 2.0.0
 * Disables Fakepro(deprecated)
 * @var $installer Mage_Core_Model_Resource_Setup
 */
$installer = $this;
$installer->startSetup();
$moduleName = 'Ophirah_Fakepro';
$modulesDir = Mage::getConfig()->getOptions()->getEtcDir() . DS . 'modules' . DS;
$filePath = sprintf('%s%s.xml', $modulesDir, $moduleName);

if (file_exists($filePath)) {
    $newNodeValue = 'false';
    $dom = new DOMDocument();
    $dom->load($filePath);
    $dom->getElementsByTagName('active')->item(0)->nodeValue = $newNodeValue;
    $dom->save($filePath);
}
$installer->endSetup();
