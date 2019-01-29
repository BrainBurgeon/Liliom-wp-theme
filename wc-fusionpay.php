<?php
/*
    Plugin Name: Fusionpay for WooCommerce
    Version: 0.1
    Author: Adrian
    Text Domain: wcfusionpay
*/

defined( 'ABSPATH' ) or exit;

// Make sure WooCommerce is active
if ( ! in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
	return;
}

const META_KEY = 'tgpayqrcode';

add_filter( 'woocommerce_payment_gateways', 'wc_add_fusionpay_gateway' );
function wc_add_fusionpay_gateway( $gateways ) {
	$gateways[] = 'WC_Gateway_Fusionpay';
	return $gateways;
}

// add_action( 'woocommerce_receipt_wc_fusionpay', 'add_fusionpay_payment', 10, 1 );
// function add_fusionpay_payment( $order_id ) {
//     // $meta = get_post_meta($order_id, META_KEY);
//     echo '<pre>';
//     // var_export($meta);
//     echo '</pre>';
// }

add_filter( 'wc_get_template', 'fusionpay_get_template', 10, 5 );
function fusionpay_get_template( $located, $template_name, $args, $template_path, $default_path ) {
    if( $template_name == 'checkout/form-pay.php' && $args['order']->data['payment_method'] == 'wc_fusionpay' ) {
        return get_template_directory() . '/fusionpay_checkout_form-pay.php';
    }
    return $located;
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
            $this->api_url  = $this->get_option( 'apiurl' );
            $this->merchant_id = $this->get_option( 'merchantid' );
            $this->token = $this->get_option( 'token' );

            add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
        }

        public function init_form_fields() {
            $this->form_fields = array(
                'enabled' => array(
                    'title' => __( 'Enable/Disable', 'wcfusionpay' ),
                    'type' => 'checkbox',
                    'label' => __( 'Enable Fusionpay Payment', 'wcfusionpay' ),
                    'default' => 'yes'
                ),
                'title' => array(
                    'title' => __( 'Title', 'wcfusionpay' ),
                    'type' => 'text',
                    'description' => __( 'This controls the title which the user sees during checkout.', 'wcfusionpay' ),
                    'default' => __( 'Fusionpay Payment', 'wcfusionpay' ),
                    'desc_tip' => true,
                ),
                'description' => array(
                    'title' => __( 'Customer Message', 'wcfusionpay' ),
                    'type' => 'textarea',
                    'default' => ''
                ),
                'apiurl' => array(
                    'title' => __( 'API URL', 'wcfusionpay' ),
                    'type' => 'text',
                    'default' => 'https://sys.tgpaypro.com:8443/api/'
                ),
                'merchantid' => array(
                    'title' => __( 'Merchant ID', 'wcfusionpay' ),
                    'type' => 'text',
                    'default' => ''
                ),
                'token' => array(
                    'title' => __( 'Token', 'wcfusionpay' ),
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
            $xml_url = $this->api_url . 'tgpayqrcode.php?' . http_build_query($args);
            
            try {
                $xml = simplexml_load_file($xml_url);
                if ( $xml !== false && $xml->is_success == 'T' && $xml->result_code == 'SUCCESS' ) {
                    $xml_array = $this->xml2array($xml);
                    add_post_meta( $order_id, META_KEY, $xml_array );
                }
            } catch( Exception $e ) {
                // damn
            }

            // Mark as on-hold
            // $order->update_status('on-hold', __( 'Awaiting Fusionpay payment', 'wcfusionpay' ));

            // Reduce stock levels
            $order->reduce_order_stock();

            // Remove cart
            $woocommerce->cart->empty_cart();

            // Return thankyou redirect
            return array(
                'result' => 'success',
                'redirect' => $order->get_checkout_payment_url()
            );
        }

        private function getOutTradeNo( $order_id ) {
            return $this->merchant_id . $order_id;
        }

        private function xml2array( $xmlObject, $out = array () ) {
            foreach ($xmlObject->children() as $node) {
                $out[$node->getName()] = is_array($node) ? $this->xml2array($node) : (string) $node;
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