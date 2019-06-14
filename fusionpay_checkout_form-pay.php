<?php

defined( 'ABSPATH' ) or exit;

$context = Timber::get_context();
$context['order_id'] = $order->id;
$context['order_data'] = $order->data;
$context['meta'] = get_post_meta( $order->id, 'tgpayqrcode', true );

Timber::render( array( 'fusionpay-alipay.twig' ), $context );