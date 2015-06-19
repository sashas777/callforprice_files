<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once('app/Mage.php'); //Path to Magento
umask(0);
Mage::app();
$resource = Mage::getSingleton('core/resource');
$writeConnection = $resource->getConnection('core_write');

$tableName = $resource->getTableName('callforprice/callforprice');

/*$writeConnection->query("DELETE FROM $tableName");*/

$query = "SELECT * FROM $tableName WHERE product_id=299";

$results=$writeConnection->fetchAll($query);
echo sprintf('%s', print_r($results, true));
?>
 
