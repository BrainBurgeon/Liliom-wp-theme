<?php

// Timber is a must-use plugin, no need to check its existence

Timber::$dirname = array( 'templates' );
Timber::$autoescape = false;

class Liliom extends Timber\Site {
    public function __construct() {
        add_action( 'after_setup_theme', array( $this, 'theme_supports' ) );
        add_action( 'widgets_init', array( $this, 'widget_awareness' ) );
        add_filter( 'timber_context', array( $this, 'add_to_context' ) );

        // WooCommerce
        remove_action( 'woocommerce_sidebar', 'woocommerce_get_sidebar', 10);
        // remove_action( 'woocommerce_before_main_content', 'woocommerce_output_content_wrapper', 10);
        // remove_action( 'woocommerce_after_main_content', 'woocommerce_output_content_wrapper_end', 10);
        // add_action('woocommerce_before_main_content', array( $this, 'my_theme_wrapper_start' ), 10);
        // add_action('woocommerce_after_main_content', array( $this, 'my_theme_wrapper_end' ), 10);
    }

    public function my_theme_wrapper_start() {
        echo '<section id="main">';
    }

    public function my_theme_wrapper_end() {
        echo '</section>';
    }

    public function theme_supports() {
        add_theme_support( 'title-tag' );
        add_theme_support( 'woocommerce' );
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
