<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once('app/Mage.php'); //Path to Magento
umask(0);
Mage::app();
$resource = Mage::getSingleton('core/resource');
$writeConnection = $resource->getConnection('core_write');

$query = "
CREATE TABLE IF NOT EXISTS  sashas_callforprice_product (
		`value_id` int(11) unsigned NOT NULL auto_increment,
		`product_id`  int(11) NOT NULL,
		`value` varchar(255) NOT NULL,
		`status` smallint(6) NOT NULL default '0',
		`addtocart_enabled` smallint(6) NOT NULL default '0',		 
		`update_time` TIMESTAMP  DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
		`customer_groups` varchar(255) NOT NULL default '-1',
		PRIMARY KEY (`value_id`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8;
";

$writeConnection->query($query);