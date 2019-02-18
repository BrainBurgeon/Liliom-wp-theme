<?php

defined( 'ABSPATH' ) or exit;

// Timber is a must-use plugin, no need to check its existence

Timber::$dirname = array( 'templates' );
Timber::$autoescape = false;

class Liliom extends Timber\Site {
    private $domain = 'liliom';
    private $translations = array( 'CN' => 'zh_CN' );
    private $taxonomy_brand_name = 'product_brand';
    private $taxonomy_brand_slug = 'brands';
    private $taxonomy_term_id_cache = array();

    public function __construct() {
        add_action( 'init', array( $this, 'register_taxonomy_brand' ) );
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

        add_filter( 'woocommerce_output_related_products_args', array( $this, 'output_related_products_args' ), 9999 );
        remove_action( 'woocommerce_shop_loop_item_title', 'woocommerce_template_loop_product_title', 10 );
        add_action( 'woocommerce_shop_loop_item_title', array( $this, 'shop_loop_item_title' ) );
        add_filter( 'woocommerce_cart_item_name', array( $this, 'cart_item_name' ), 10, 3 );
        remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_title', 5 );
        add_action( 'woocommerce_single_product_summary', array( $this, 'single_title' ), 5 );

        // Extend WC Import with Brand
        add_filter( 'woocommerce_csv_product_import_mapping_options', array( $this, 'csv_product_import_mapping_options' ) );
        add_filter( 'woocommerce_product_importer_parsed_data', array( $this, 'product_importer_parsed_data' ), 10, 2 );
        add_filter( 'woocommerce_product_import_inserted_product_object', array( $this, 'product_import_inserted_product_object' ), 10, 2 );
        add_filter( 'woocommerce_csv_product_import_mapping_default_columns', array( $this, 'csv_product_import_mapping_default_columns' ) );

        add_filter( 'woocommerce_show_page_title', '__return_false' );

        add_filter( 'woocommerce_pagination_args', array( $this, 'pagination_args' ), 10, 1 );
    }

    public function pagination_args( $args ) {
        $args[ 'end_size' ] = 0;
        $args[ 'mid_size' ] = 0;
        return $args;
    }

    public function register_taxonomy_brand() {
        $labels = array(
            'name' => __( 'Brands', $this->domain ),
            'singular_name' => __( 'Brand', $this->domain ),
            'add_new_item' => __( 'Add New Brand', $this->domain )
        );

        $args = array(
            'hierarchical' => true,
            'labels' => $labels,
            'show_ui' => true,
            'show_admin_column' => true,
            'query_var' => true,
            'rewrite' => array( 'slug' => $this->taxonomy_brand_slug )
        );

        register_taxonomy( $this->taxonomy_brand_name, array( 'product' ), $args );
    }



    // Based on https://gist.github.com/helgatheviking/114c8df50cabb7119b3c895b1d854533

    public function csv_product_import_mapping_options( $columns ) {
        $columns[ $this->taxonomy_brand_name ] = __( 'Brand', $this->domain );
        return $columns;
    }

    public function csv_product_import_mapping_default_columns( $columns ) {
        $columns[ 'Brand' ] = $this->taxonomy_brand_name;
        return $columns;
    }

    public function product_importer_parsed_data( $parsed_data, $importer ) {
        if ( empty( $parsed_data[ $this->taxonomy_brand_name ] ) ) {
            return $parsed_data;
        }

        $data = explode( ',', $parsed_data[ $this->taxonomy_brand_name ] );
        $data = array_map( 'trim', $data );
        unset( $parsed_data[ $this->taxonomy_brand_name ] );

        if ( is_array( $data ) ) {
            $parsed_data[ $this->taxonomy_brand_name ] = array();

            foreach( $data as $term_name ) {
                $parsed_data[ $this->taxonomy_brand_name ][] = $this->get_or_create_term_id( $term_name );
            }
        }

        return $parsed_data;
    }

    public function product_import_inserted_product_object( $product, $data ) {
        if ( is_a( $product, 'WC_Product' ) ) {
            if ( ! empty( $data[ $this->taxonomy_brand_name ] ) ) {
                wp_set_object_terms( $product->get_id(),  array_values( (array) $data[ $this->taxonomy_brand_name ] ), $this->taxonomy_brand_name );
            }
        }

        return $product;
    }



    public function set_my_locale( $lang ) {
        $lang_key = '';
        if ( !empty( $_GET['language'] ) ) {
            $lang_key = $_GET['language'];
        } else if ( !empty( $_SERVER['HTTP_CF_IPCOUNTRY'] ) ) {
            $lang_key = $_SERVER['HTTP_CF_IPCOUNTRY'];
        }
        if ( !empty( $this->translations[$lang_key] ) ) {
            return $this->translations[$lang_key];
        }
        return $lang;
    }

    public function theme_supports() {
        add_theme_support( 'title-tag' );
        add_theme_support( 'woocommerce' );
    }

    public function widget_awareness() {
        register_sidebar( array( 'id' => 'liliom-sidebar-1' ) );
    }

    public function add_to_context( $context ) {
        if ( !empty( $_SERVER['HTTP_CF_IPCOUNTRY'] ) ) {
            $context['CF_IPCOUNTRY'] = $_SERVER['HTTP_CF_IPCOUNTRY'];
        }
	    $context['menu'] = new Timber\Menu( 'Main Menu' );
	    $context['site'] = new TimberSite();
	    $context['available_payment_gateways'] = WC()->payment_gateways->get_available_payment_gateways();
	    return $context;
    }

    public function output_related_products_args( $args ) {
        $args['posts_per_page'] = 3;
        $args['columns'] = 3;
        return $args;
    }

    public function shop_loop_item_title() {
        $title = $this->process_title( get_the_title() );
        echo '<h3 class="brand-name">' . $title['brand_name'] . '</h3><h2 class="product-name">' . $title['product_name'] . '</h2>' . '<span class="product-info">' . $title['info'] . '</span>';
    }

    public function cart_item_name( $title, $item, $key ) {
        $name = $this->process_title( $title );
        return '<div class="the-product"><div class="the-product-name">' .  $name['product_name'] . '</div><div class="the-brand-name">' . strip_tags($name['brand_name']) . '</div><div class="the-info">' . $name['info'] . '</div></div>';
    }

    public function single_title() {
        $title = $this->process_title( get_the_title() );
        $brand = $title['brand_name'];
        if ( !empty( $title['brand_name'] ) ) {
            $taxonomy_url = get_home_url( null, $this->taxonomy_brand_slug . '/' . sanitize_title( $title['brand_name'] ) . '/' );
            $brand = '<a href="' . $taxonomy_url . '">' . $title['brand_name'] . '</a>';
        }
        echo '<h1 class="product-name">' . $title['product_name'] . '</h1><div class="brand-name">' . $brand . '</div>';
    }

    private function process_title( $title ) {
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

    private function get_or_create_term_id( $term_name ) {
        if ( empty( $this->taxonomy_term_id_cache[ $term_name ] ) ) {
            $term = get_term_by( 'name', $term_name, $this->taxonomy_brand_name, ARRAY_A );
            if ( empty( $term ) ) {
                // Create a new term
                $term = wp_insert_term( $term_name, $this->taxonomy_brand_name );
            }
            $this->taxonomy_term_id_cache[ $term_name ] = (int) $term[ 'term_id' ];
        }
        
        return $this->taxonomy_term_id_cache[ $term_name ];
    }
}

new Liliom();
