<?php
$newConfigPaths = array();
$newConfigPaths["not2order/general/enabled"] = "qquoteadv_general/orderability_and_prices/enabled";
$newConfigPaths["not2order/general/usetemplates"] = "qquoteadv_general/orderability_and_prices/usetemplates";
$newConfigPaths["not2order/general/autohide"] = "qquoteadv_general/orderability_and_prices/autohide";

$installer = $this;
$installer->startSetup();

foreach ($newConfigPaths as $oldPath => $newPath) {
    $installer->run("UPDATE {$this->getTable('core_config_data')} SET `path` = REPLACE(`path`, '" . $oldPath . "', '" . $newPath . "') WHERE `path` = '" . $oldPath . "'");
}

$installer->endSetup();

