<?php

if ( ! defined( 'ABSPATH' ) ) exit;

$context = Timber::get_context();
$templates = array( 'index.twig' );

Timber::render( $templates, $context );
