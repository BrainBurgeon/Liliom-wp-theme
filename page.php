<?php

$context = Timber::get_context();
$context['post'] = new Timber\Post();
$templates = array( 'page.twig' );

Timber::render( $templates, $context );
