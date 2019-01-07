<?php

$context = Timber::get_context();
$templates = array( 'index.twig' );

if(isset($_GET['testing123'])) {
	//var_export(get_stylesheet_directory_uri());
	var_export($context);
	exit;
}

Timber::render( $templates, $context );
