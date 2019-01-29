<?php

$response = array('success' => true);

try {
    $order_id = intval($_GET['id']);

    if( empty($order_id) ) throw new Exception('Missing parameter: order_id');

    define( 'WC_ABSPATH', dirname( __FILE__ ) . '/../../plugins/woocommerce/' );

    require_once('../../../wp-load.php');
    require_once(WC_ABSPATH . 'includes/abstracts/abstract-wc-data.php');
    require_once(WC_ABSPATH . 'includes/abstracts/abstract-wc-order.php');
    require_once(WC_ABSPATH . 'includes/class-wc-order.php');
    require_once(ABSPATH . 'wp-includes/functions.php');

    $order = new WC_Order($order_id);

    $response['status'] = $order->data['status'];

    if ( $order->data['payment_method'] === 'wc_fusionpay' && $order->data['status'] === 'pending' ) {
        $options = get_option('woocommerce_wc_fusionpay_settings');
        $args = array(
            'merchants_id' => $options['merchantid'],
            'out_trade_no' => $options['merchantid'] . '_' . $order_id
        );
        $args['sign'] = md5( http_build_query( $args ) . $options['token'] );
        $api_url = $options['apiurl'] . 'tgpaycheck.php?' . http_build_query( $args );
        $api_response = simplexml_load_file($api_url);

        if ( $api_response->is_success == 'T' && $api_response->trade_status == 'TRADE_SUCCESS' ) {
            $order->payment_complete( $api_response->alipay_trans_id );
            $response['status'] = $order->get_status();
        }
    }
}
catch( Exception $e ) {
    $response = array('success' => false);
}

header('Content-Type: application/json');
echo json_encode($response);