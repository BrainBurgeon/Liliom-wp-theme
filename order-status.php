<?php

$response = array('success' => true);

try {
    $order_id = intval($_GET['id']);

    if( empty($order_id) ) exit('-1');

    define( 'WC_ABSPATH', dirname( __FILE__ ) . '/../../plugins/woocommerce/' );

    require_once('../../../wp-load.php');
    require_once(WC_ABSPATH . 'includes/abstracts/abstract-wc-data.php');
    require_once(WC_ABSPATH . 'includes/abstracts/abstract-wc-order.php');
    require_once(WC_ABSPATH . 'includes/class-wc-order.php');
    require_once(ABSPATH . 'wp-includes/functions.php');

    $order = new WC_Order($order_id);

    $response['status'] = $order->data['status'];

    if ( $order->data['payment_method'] === 'wc_fusionpay' && $order->data['status'] === 'pending' ) {

    }
} catch( Exception $e ) {
    $response = array('success' => false);
}

header('Content-Type: application/json');
echo json_encode($response);