<?php
/*
Plugin Name: Fusionpay for WooCommerce
Version:     0.1
Author:      Adrian
*/

defined( 'ABSPATH' ) or exit;

// Make sure WooCommerce is active
if ( ! in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
	return;
}



add_filter( 'woocommerce_payment_gateways', 'wc_add_fusionpay_gateway' );

function wc_add_fusionpay_gateway( $gateways ) {
	$gateways[] = 'WC_Gateway_Fusionpay';
	return $gateways;
}



add_action( 'plugins_loaded', 'init_wc_fusionpay' );

function init_wc_fusionpay() {
    class WC_Gateway_Fusionpay extends WC_Payment_Gateway {
        public function __construct() {
            $this->id = 'wc_fusionpay';
            $this->has_fields = false;
            $this->method_title = 'Fusionpay';

            $this->init_form_fields();
            $this->init_settings();

            $this->title = $this->get_option( 'title' );
            $this->description  = $this->get_option( 'description' );

            add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
        }

        public function init_form_fields() {
            $this->form_fields = array(
                'enabled' => array(
                    'title' => __( 'Enable/Disable', 'woocommerce' ),
                    'type' => 'checkbox',
                    'label' => __( 'Enable Fusionpay Payment', 'woocommerce' ),
                    'default' => 'yes'
                ),
                'title' => array(
                    'title' => __( 'Title', 'woocommerce' ),
                    'type' => 'text',
                    'description' => __( 'This controls the title which the user sees during checkout.', 'woocommerce' ),
                    'default' => __( 'Fusionpay Payment', 'woocommerce' ),
                    'desc_tip' => true,
                ),
                'description' => array(
                    'title' => __( 'Customer Message', 'woocommerce' ),
                    'type' => 'textarea',
                    'default' => ''
                ),
                'merchantid' => array(
                    'title' => __( 'Merchant ID', 'woocommerce' ),
                    'type' => 'text',
                    'default' => ''
                )
            );
        }

        function process_payment( $order_id ) {
            global $woocommerce;
            $order = new WC_Order( $order_id );

            // Mark as on-hold
            $order->update_status('on-hold', __( 'Awaiting Fusionpay payment', 'woocommerce' ));
            // $order->payment_complete();

            // Reduce stock levels
            $order->reduce_order_stock();

            // Remove cart
            $woocommerce->cart->empty_cart();

            // Return thankyou redirect
            return array(
                'result' => 'success',
                'redirect' => $this->get_return_url( $order )
            );
        }
    }
}