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

const API_URL = 'https://sys.tgpaypro.com:8443/api/tgpayqrcode.php?';

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
            $this->merchant_id = $this->get_option( 'merchantid' );
            $this->token = $this->get_option( 'token' );

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
                ),
                'token' => array(
                    'title' => __( 'Token', 'woocommerce' ),
                    'type' => 'text',
                    'default' => ''
                )
            );
        }

        public function process_payment( $order_id ) {
            global $woocommerce;
            $order = new WC_Order( $order_id );

            $args = $this->getArgs( $order );
            $args['sign'] = $this->getSignature( $args );
            $xml_url = API_URL . http_build_query($args);
            
            try {
                $xml = simplexml_load_file($xml_url);
                var_export($xml);
                if ( $xml->is_success == 'T' && $xml->result_code == 'SUCCESS' ) {
                    // $xml_array = $this->xml2array($xml);
                    add_post_meta( $order_id, 'tgpayqrcode', $xml );
                    var_export(get_post_meta($order_id, 'tgpayqrcode'));
                }
            } catch( Exception $e ) {
                // damn
            }

            exit;

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

        private function getOutTradeNo( $order_id ) {
            return $this->merchant_id . '_' . $order_id;
        }

        private function xml2array( $xmlObject, $out = array () ) {
            foreach( (array) $xmlObject as $index => $node ) {
                $out[$index] = is_object( $node ) ? $this->xml2array( $node ) : $node;
            }
            return $out;
        }

        private function getArgs( $order ) {
            return array(
                'it_b_pay' => '1c',
                'merchants_id' => $this->merchant_id,
                'out_trade_no' => $this->getOutTradeNo($order->id),
                'total_fee' => $order->total
            );
        }

        private function getSignature( $args ) {
            return md5( http_build_query( $args ) . $this->token );
        }
    }
}