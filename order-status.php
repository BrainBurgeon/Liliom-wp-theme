<?php

try {
    $order_id = intval($_GET['id']);

    if(empty($order_id)) exit('-1');

    define( 'WC_ABSPATH', dirname( __FILE__ ) . '/../../plugins/woocommerce/' );

    require_once('../../../wp-load.php');
    require_once(WC_ABSPATH . 'includes/abstracts/abstract-wc-data.php');
    require_once(WC_ABSPATH . 'includes/abstracts/abstract-wc-order.php');
    require_once(WC_ABSPATH . 'includes/class-wc-order.php');
    require_once(ABSPATH . 'wp-includes/functions.php');

    $order = new WC_Order($order_id);

    echo '<pre>';
    var_export($order);
} catch(Exception $e) {
    exit('-2');
}