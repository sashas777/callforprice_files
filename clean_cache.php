<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once('app/Mage.php'); //Path to Magento
umask(0);
Mage::app();
 
Mage::app()->cleanCache();
Mage::app()->getCacheInstance()->flush();
?>
 
