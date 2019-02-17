<?php

defined( 'ABSPATH' ) or exit;

$context = Timber::get_context();
$templates = array( 'woocommerce.twig' );

Timber::render( $templates, $context );
