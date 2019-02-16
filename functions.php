<?php

$translations = array( 'CN' => 'zh_CN' );

// Timber is a must-use plugin, no need to check its existence

Timber::$dirname = array( 'templates' );
Timber::$autoescape = false;

class Liliom extends Timber\Site {
    public function __construct() {
        add_filter( 'locale', array( $this, 'set_my_locale' ) );
        add_action( 'after_setup_theme', array( $this, 'theme_supports' ) );
        add_action( 'widgets_init', array( $this, 'widget_awareness' ) );
        add_filter( 'timber_context', array( $this, 'add_to_context' ) );

        ### WooCommerce
        add_filter( 'woocommerce_enqueue_styles', '__return_false' );
        // Uncheck the "Deliver to a different address?" checkbox
        add_filter( 'woocommerce_ship_to_different_address_checked', '__return_false' );
        remove_action( 'woocommerce_sidebar', 'woocommerce_get_sidebar', 10);
        // Remove product images from the shop loop
        remove_action( 'woocommerce_before_shop_loop_item_title', 'woocommerce_template_loop_product_thumbnail', 10 );
        // Remove product thumbnail from the cart page
        add_filter( 'woocommerce_cart_item_thumbnail', '__return_empty_string' );
        // Remove image from product pages
        remove_action( 'woocommerce_before_single_product_summary', 'woocommerce_show_product_images', 20 );
        // Remove sale badge from product page
        // remove_action( 'woocommerce_before_single_product_summary', 'woocommerce_show_product_sale_flash', 10 );
        // Remove the result count from WooCommerce
        remove_action( 'woocommerce_before_shop_loop' , 'woocommerce_result_count', 20 );
        // Remove the sorting dropdown from Woocommerce
        remove_action( 'woocommerce_before_shop_loop' , 'woocommerce_catalog_ordering', 30 );
        // Remove tabs from product detail page
        remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_output_product_data_tabs', 10 );

        // remove_action( 'woocommerce_before_main_content', 'woocommerce_output_content_wrapper', 10);
        // remove_action( 'woocommerce_after_main_content', 'woocommerce_output_content_wrapper_end', 10);
        // add_action('woocommerce_before_main_content', array( $this, 'my_theme_wrapper_start' ), 10);
        // add_action('woocommerce_after_main_content', array( $this, 'my_theme_wrapper_end' ), 10);

        add_filter( 'woocommerce_output_related_products_args', array( $this, 'output_related_products_args' ), 9999 );

        remove_action( 'woocommerce_shop_loop_item_title', 'woocommerce_template_loop_product_title', 10 );
        add_action( 'woocommerce_shop_loop_item_title', array( $this, 'shop_loop_item_title' ) );

        add_filter( 'woocommerce_cart_item_name', array( $this, 'cart_item_name' ), 10, 3 );

        remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_title', 5 );
        add_action( 'woocommerce_single_product_summary', array( $this, 'single_title' ), 5 );
    }

    // public function my_theme_wrapper_start() {
    //     echo '<section id="main">';
    // }

    // public function my_theme_wrapper_end() {
    //     echo '</section>';
    // }

    public function set_my_locale($lang) {
        global $translations;
        $lang_key = '';
        if ( !empty( $_GET['language'] ) ) {
            $lang_key = $_GET['language'];
        } else if ( !empty( $_SERVER['HTTP_CF_IPCOUNTRY'] ) ) {
            $lang_key = $_SERVER['HTTP_CF_IPCOUNTRY'];
        }
        if ( !empty( $translations[$lang_key] ) ) {
            return $translations[$lang_key];
        }
        return $lang;
    }

    public function theme_supports() {
        add_theme_support( 'title-tag' );
        add_theme_support( 'woocommerce' );
    }

    public function widget_awareness() {
        register_sidebar(array('id' => 'liliom-sidebar-1'));
    }

    public function add_to_context( $context ) {
        if ( !empty( $_SERVER['HTTP_CF_IPCOUNTRY'] ) ) {
            $context['CF_IPCOUNTRY'] = $_SERVER['HTTP_CF_IPCOUNTRY'];
        }
	    $context['menu'] = new Timber\Menu('Main Menu');
	    $context['site'] = new TimberSite();
	    return $context;
    }

    public function output_related_products_args( $args ) {
        $args['posts_per_page'] = 3;
        $args['columns'] = 3;
        return $args;
    }

    public function shop_loop_item_title() {
        $title = $this->processTitle( get_the_title() );
        echo '<h3 class="brand-name">' . $title['brand_name'] . '</h3><h2 class="product-name">' . $title['product_name'] . '</h2>' . '<span class="product-info">' . $title['info'] . '</span>';
    }

    public function cart_item_name( $title, $item, $key ) {
        $name = $this->processTitle( $title );
        return '<div class="the-product"><div class="the-product-name">' .  $name['product_name'] . '</div><div class="the-brand-name">' . strip_tags($name['brand_name']) . '</div><div class="the-info">' . $name['info'] . '</div></div>';
    }

    public function single_title() {
        $title = $this->processTitle( get_the_title() );
        echo '<h1 class="product-name">' . $title['product_name'] . '</h1><div class="brand-name">' . $title['brand_name'] . '</div>';
    }

    private function processTitle( $title ) {
        $processed = array(
            'brand_name' => '',
            'product_name' => $title,
            'info' => ''
        );

        preg_match( '/(.*)\: (.*) \((.*)\)/', $title, $output_array );

        if ( !empty( $output_array ) ) {
            $processed['brand_name'] = $output_array[1];
            $processed['product_name'] = $output_array[2];
            $processed['info'] = $output_array[3];
        }
        
        return $processed;
    }
}

new Liliom();
