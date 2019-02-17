<?php

defined( 'ABSPATH' ) or exit;

$context = Timber::get_context();
Timber::render( 'searchform.twig', $context );
