<?php

$meta = get_post_meta($order->id, 'tgpayqrcode');
if( is_array( $meta ) && count( $meta ) == 1 ) {
    $meta = $meta[0];
}

$context = Timber::get_context();
$context['order'] = $order;
$context['meta'] = $meta;

var_export($order);exit;

Timber::render( array( 'fusionpay-alipay.twig' ), $context );