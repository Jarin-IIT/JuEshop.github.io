<?php

namespace WeDevs\DokanPro\Modules\Stripe;

use Stripe\BalanceTransaction;
use Stripe\Stripe;
use WeDevs\DokanPro\Modules\Stripe\Settings\RetrieveSettings;

defined( 'ABSPATH' ) || exit;

/**
 * Stripe Helper class
 *
 * @since 3.0.3
 */
class Helper {

    public static function get_settings() {
        return RetrieveSettings::instance()->settings;
    }

    /**
     * Check wheter the 3d secure is enabled or not
     *
     * @since 3.0.3
     *
     * @return bool
     */
    public static function is_3d_secure_enabled() {
        $settings = self::get_settings();

        if ( empty( $settings['enable_3d_secure'] ) || 'yes' !== $settings['enable_3d_secure'] ) {
            return false;
        }

        return true;
    }

    /**
     * Check wheter we are paying with 3ds or non_3ds payment method
     *
     * @since 3.0.3
     *
     * @return string
     */
    public static function payment_method() {
        return self::is_3d_secure_enabled() ? '3ds' : 'non_3ds';
    }

    /**
     * Check wheter the gateway in test mode or not
     *
     * @since 3.0.3
     *
     * @return bool
     */
    public static function is_test_mode() {
        $settings = self::get_settings();

        if ( empty( $settings['testmode'] ) || 'yes' !== $settings['testmode'] ) {
            return false;
        }

        return 'yes' === $settings['testmode'];
    }

    /**
     * Check wheter subscription module is enabled or not
     *
     * @since 3.0.3
     *
     * @return bool
     */
    public static function has_subscription_module() {
        return dokan_pro()->module->is_active( 'product_subscription' );
    }

    /**
     * Set stripe app info
     *
     * @since 3.0.3
     *
     * @return void
     */
    public static function set_app_info() {
        Stripe::setAppInfo(
            'Dokan Stripe-Connect',
            DOKAN_PRO_PLUGIN_VERSION,
            'https://wedevs.com/dokan/modules/stripe-connect/',
            'pp_partner_Ee9F0QbhSGowvH'
        );
    }

    /**
     * Set stripe API version
     *
     * @since 3.0.3
     *
     * @return void
     */
    public static function set_api_version() {
        Stripe::setApiVersion( '2020-08-27' );
    }

    /**
    * Check if the order is a subscription order
    *
    * @since 1.3.3
    *
    * @return bool
    **/
    public static function is_subscription_order( $order ) {
        if ( ! self::has_subscription_module() ) {
            return false;
        }

        $product = self::get_subscription_product_by_order( $order );

        return $product ? true : false;
    }

    /**
     * Get subscription product from an order
     *
     * @param \WC_Order $order
     *
     * @return \WC_Order|null
     */
    public static function get_subscription_product_by_order( $order ) {
        foreach ( $order->get_items() as $item ) {
            $product = $item->get_product();

            if ( 'product_pack' === $product->get_type() ) {
                return $product;
            }
        }

        return null;
    }

    /**
     * Check if this gateway is enabled and ready to use
     *
     * @since 3.0.3
     *
     * @return bool
     */
    public static function is_ready() {
        if ( ! self::is_enabled() || ! self::get_secret_key() || ! self::get_client_id() ) {
            return false;
        }

        if ( ! is_ssl() && ! self::is_test_mode() ) {
            return false;
        }

        return true;
    }

    /**
     * Check wheter it's enabled or not
     *
     * @since 3.0.3
     *
     * @return bool
     */
    public static function is_enabled() {
        $settings = self::get_settings();

        return ! empty( $settings['enabled'] ) && 'yes' === $settings['enabled'];
    }

    /**
     * Bootstrap stripe
     *
     * @since 3.0.3
     *
     * @return void
     */
    public static function bootstrap_stripe() {
        self::set_app_info();
        self::set_api_version();

        if ( self::is_test_mode() ) {
            Stripe::setVerifySslCerts( false );
        }

        Stripe::setClientId( self::get_client_id() );
        Stripe::setApiKey( self::get_secret_key() );
    }

    /**
     * Get secret key
     *
     * @since 3.0.3
     *
     * @return string
     */
    public static function get_secret_key() {
        $key      = self::is_test_mode() ? 'test_secret_key' : 'secret_key';
        $settings = self::get_settings();

        if ( ! empty( $settings[ $key ] ) ) {
            return $settings[ $key ];
        }
    }

    /**
     * Get client id
     *
     * @since 3.0.3
     *
     * @return string
     */
    public static function get_client_id() {
        $key      = self::is_test_mode() ? 'test_client_id' : 'client_id';
        $settings = self::get_settings();

        if ( ! empty( $settings[ $key ] ) ) {
            return $settings[ $key ];
        }
    }

    /**
     * Check wheter non-connected sellers can sale product or not
     *
     * @since 3.0.3
     *
     * @return bool
     */
    public static function allow_non_connected_sellers() {
        $settings = self::get_settings();

        return ! empty( $settings['allow_non_connected_sellers'] ) && 'yes' === $settings['allow_non_connected_sellers'];
    }

    /**
     * Show checkout modal
     *
     * @since  3.0.3
     *
     * @return bool
     */
    public static function show_checkout_modal() {
        $settings = self::get_settings();

        return ! empty( $settings['stripe_checkout'] ) && 'yes' === $settings['stripe_checkout'];
    }

    /**
     * Get gateway id
     *
     * @since 3.0.3
     *
     * @return string
     */
    public static function get_gateway_id() {
        return 'dokan-stripe-connect';
    }

    /**
     * Get gateway title
     *
     * @since 3.0.3
     *
     * @return string
     */
    public static function get_gateway_title() {
        $settings = self::get_settings();

        return ! empty( $settings['title'] ) ? $settings['title'] : __( 'Stripe Connect', 'dokan' );
    }


    public static function save_cards() {
        $settings = self::get_settings();

        return ! empty( $settings['saved_cards'] ) && 'yes' === $settings['saved_cards'];
        $settings = self::get_settings();
    }

    /**
     * Does seller pay the Stripe processing fee
     *
     * @since 3.1.0
     *
     * @return bool
     */
    public static function seller_pays_the_processing_fee() {
        $settings = self::get_settings();

        return isset( $settings['seller_pays_the_processing_fee'] ) && dokan_validate_boolean( $settings['seller_pays_the_processing_fee'] );
    }

    /**
     * Calculate the processing fee for a single vendor for an order
     *
     * @since 3.1.0
     *
     * @param float $order_processing_fee
     * @param \WC_ORDER $suborder
     * @param \WC_ORDER $order
     *
     * @return float
     */
    public static function calculate_processing_fee_for_suborder( $order_processing_fee, $suborder, $order ) {
        $stripe_fee_for_vendor = $order_processing_fee * ( $suborder->get_total() / $order->get_total() );
        return number_format( $stripe_fee_for_vendor, 10 );
    }

    /**
     * Get vendor id by subscriptoin id
     *
     * @since 3.0.3
     *
     * @param string $subscription_id
     *
     * @return int
     */
    public static function get_vendor_id_by_subscription( $subscription_id ) {
        global $wpdb;

        $vendor_id = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT `user_id` FROM $wpdb->usermeta WHERE `meta_key` = %s AND `meta_value`= %s",
                '_stripe_subscription_id',
                $subscription_id
            )
        );

        return absint( $vendor_id );
    }

    /**
     * Get list of supported webhook events
     *
     * @since 3.0.3
     *
     * @return array
     */
    public static function get_supported_webhook_events() {
        return apply_filters( 'dokan_stripe_get_supported_webhook_events', [
            'charge.dispute.closed'                => 'ChargeDisputeClosed',
            'charge.dispute.created'               => 'ChargeDisputeCreated',
            'invoice.payment_failed'               => 'InvoicePaymentFailed',
            'invoice.payment_succeeded'            => 'InvoicePaymentSucceeded',
            'invoice.payment_action_required'      => 'InvoicePaymentActionRequired',
            'customer.subscription.updated'        => 'SubscriptionUpdated',
            'customer.subscription.deleted'        => 'SubscriptionDeleted',
            'customer.subscription.trial_will_end' => 'SubscriptionTrialWillEnd',
        ] );
    }

    /**
     * Get Stripe amount to pay
     *
     * @since 3.0.3
     *
     * @return float
     */
    public static function get_stripe_amount( $total ) {
        switch ( get_woocommerce_currency() ) {
            /* Zero decimal currencies*/
            case 'BIF' :
            case 'CLP' :
            case 'DJF' :
            case 'GNF' :
            case 'JPY' :
            case 'KMF' :
            case 'KRW' :
            case 'MGA' :
            case 'PYG' :
            case 'RWF' :
            case 'VND' :
            case 'VUV' :
            case 'XAF' :
            case 'XOF' :
            case 'XPF' :
            $total = absint( $total );
            break;
            default :
            $total = round( $total, 2 ) * 100; /* In cents*/
            break;
        }

        return $total;
    }

    /**
     * Format gateway balance fee
     *
     * @param $balance_transaction
     * @return string|void
     */
    public static function format_gateway_balance_fee( $balance_transaction ) {
        if ( ! is_object( $balance_transaction ) ) {
            try {
                $balance_transaction = BalanceTransaction::retrieve( $balance_transaction );
            } catch ( \Exception $exception ) {
                return;
            }
        }

        if ( ! is_object( $balance_transaction ) ) {
            return;
        }

        $fee = $balance_transaction->fee;
        foreach ( $balance_transaction->fee_details as $fee_details ) {
            if ( $fee_details->type === 'stripe_fee' ) {
                $fee = $fee_details->amount;
                break;
            }
        }

        if ( ! in_array( strtolower( $balance_transaction->currency ), self::no_decimal_currencies() ) ) {
            $fee = number_format( $fee / 100, 2, '.', '' );
        }

        if ( $balance_transaction->exchange_rate ) {
            return number_format( $fee / $balance_transaction->exchange_rate, 2, '.', '' );
        }

        return $fee;

    }

    /**
     * Get no decimal currencies
     *
     * @return array
     */
    public static function no_decimal_currencies() {
        return array(
            'bif',
            'clp',
            'djf',
            'gnf',
            'jpy',
            'kmf',
            'krw',
            'mga',
            'pyg',
            'rwf',
            'ugx',
            'vnd',
            'vuv',
            'xaf',
            'xof',
            'xpf',
        );
    }

    /**
     * Checks to see if error is no such subscription error.
     *
     * @since 3.0.3
     *
     * @param string $error_message
     */
    public static function is_no_such_subscription_error( $error_message ) {
        return preg_match( '/No such subscription/i', $error_message );
    }

    /**
     * Include module template
     *
     * @since 3.1.0
     *
     * @param string $name
     * @param array  $args
     *
     * @return void
     */
    public static function get_template( $name, $args = [] ) {
        dokan_get_template( "$name.php", $args, 'dokan/modules/stripe', trailingslashit( DOKAN_STRIPE_TEMPLATE_PATH ) );
    }
}
