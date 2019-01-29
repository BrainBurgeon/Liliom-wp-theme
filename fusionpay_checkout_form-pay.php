<?php

$meta = get_post_meta($order->id, 'tgpayqrcode');
if( is_array( $meta ) && count( $meta ) == 1 ) {
    $meta = $meta[0];
}

$context = Timber::get_context();
$context['order_data'] = $order->data;
$context['meta'] = $meta;

Timber::render( array( 'fusionpay-alipay.twig' ), $context );