<?php

// Timber is a must-use plugin, no need to check its existence

Timber::$dirname = array( 'templates' );
Timber::$autoescape = false;

class Liliom extends Timber\Site {
    public function __construct() {
        add_action( 'after_setup_theme', array( $this, 'after_setup_liliom' ) );
        add_action( 'widgets_init', array( $this, 'widget_awareness' ) );
        add_filter( 'timber_context', array( $this, 'add_to_context' ) );
    }

    public function after_setup_liliom() {
        add_theme_support( 'title-tag' );
        add_theme_support( 'woocommerce' );
        
        remove_action( 'woocommerce_sidebar', 'woocommerce_get_sidebar', 10);
    }

    public function widget_awareness() {
        register_sidebar(array('id' => 'liliom-sidebar-1'));
    }

    public function add_to_context( $context ) {
	    $context['CF_IPCOUNTRY'] = $_SERVER['HTTP_CF_IPCOUNTRY'];
	    // $context['menu'] = new Timber\Menu();
	    $context['site'] = new TimberSite();
	    return $context;
    }
}

new Liliom();
