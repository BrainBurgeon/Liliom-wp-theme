<?php

// Timber is a must-use plugin, no need to check its existence

Timber::$dirname = array( 'templates' );
Timber::$autoescape = false;

class Liliom extends Timber\Site {
    public function __construct() {
        add_action( 'after_setup_theme', array( $this, 'theme_supports' ) );
        add_filter( 'timber_context', array( $this, 'add_to_context' ) );
    }

    public function theme_supports() {
        add_theme_support( 'title-tag' );
    }

    public function add_to_context( $context ) {
		$context['CF_IPCOUNTRY'] = $_SERVER['HTTP_CF_IPCOUNTRY'];
		// $context['menu'] = new Timber\Menu();
        $context['site'] = $this;
		return $context;
	}
}

new Liliom();