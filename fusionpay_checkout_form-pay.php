<?php

$context = Timber::get_context();
$context['order'] = $order;

Timber::render( array( 'fusionpay-alipay.twig' ), $context );
