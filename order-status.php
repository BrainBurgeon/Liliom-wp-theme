<?php

$order_id = intval($_GET['id'] || 0);

if(empty($order_id)) exit;

$order = new WooCommerce\Classes\WC_Order($order_id);

var_export($order);