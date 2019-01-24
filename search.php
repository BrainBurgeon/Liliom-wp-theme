<?php

$context = Timber::get_context();
$context['title'] = 'Search results for ' . get_search_query();
$context['posts'] = new Timber\PostQuery();
$templates = array( 'search.twig' );

Timber::render( $templates, $context );