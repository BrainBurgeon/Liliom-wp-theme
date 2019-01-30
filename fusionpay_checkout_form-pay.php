<?php

$meta = get_post_meta( $order->id, 'tgpayqrcode' );

echo '<pre>';
var_export($meta);
exit;

$context = Timber::get_context();
$context['order_id'] = $order->id;
$context['order_data'] = $order->data;
$context['meta'] = $meta;

Timber::render( array( 'fusionpay-alipay.twig' ), $context );