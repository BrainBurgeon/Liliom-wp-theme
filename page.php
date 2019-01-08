<?php

$context = Timber::get_context();
$templates = array( 'page.twig' );

Timber::render( $templates, $context );
