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
	`value_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Value ID',
	  `product_id` text COMMENT 'Product ID',
	  `value` text COMMENT 'Callforprice Value',
	  `status` int(11) DEFAULT '0' COMMENT 'Status',
	  `customer_groups` text COMMENT 'Customer groups',
	  `addtocart_enabled` int(11) DEFAULT '0' COMMENT 'Add to cart enabled',
	  `update_time` timestamp NULL DEFAULT NULL COMMENT 'Updated time Date',
	  `show_price` int(11) NOT NULL DEFAULT '0',
	  `store_id` int(11) NOT NULL DEFAULT '0',
	  PRIMARY KEY (`value_id`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8;
";

$writeConnection->query($query);
