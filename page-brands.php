<?php

defined( 'ABSPATH' ) or exit;

$context = Timber::get_context();
$context['post'] = new Timber\Post();

$product_brand_terms_abc = array();
$product_brand_terms = get_terms( array( 'taxonomy' => 'product_brand' ) );
foreach( $product_brand_terms as $term ) {
    $term->url = get_home_url( null, 'brands/' . $term->slug . '/' );
    $first_char = mb_substr( $term->slug, 0, 1, 'utf-8' );
    $product_brand_terms_abc[ $first_char ][] = $term;
}

$context['product_brand_terms_abc'] = $product_brand_terms_abc;

$templates = array( 'brands.twig' );

Timber::render( $templates, $context );
