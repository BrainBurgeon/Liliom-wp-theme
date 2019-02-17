<?php

defined( 'ABSPATH' ) or exit;

$context = Timber::get_context();
$context['order_id'] = $order->id;
$context['order_data'] = $order->data;

$meta = get_post_meta( $order->id, 'tgpayqrcode' );
if( is_array( $meta ) && count( $meta ) == 1 ) {
    $context['meta'] = $meta[0];
}

Timber::render( array( 'fusionpay-alipay.twig' ), $context );