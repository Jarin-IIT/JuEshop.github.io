<?php

namespace WeDevs\DokanPro;

/**
 * Dokan Update class
 *
 * Performas license validation and update checking
 */
class Update {

    /**
     * Appsero License Instance
     *
     * @var \Appsero\License
     */
    private $license;

    /**
     * The license product ID
     *
     * @var string
     */
    private $product_id = 'dokan-pro';

    /**
     * Initialize the class
     */
    public function __construct() {
        if ( ! class_exists( '\Appsero\Client' ) ) {
            return;
        }

        $this->init_appsero();

        if ( is_multisite() ) {
            if ( is_main_site() ) {
                add_action( 'admin_notices', [ $this, 'license_enter_notice' ] );
            }
        } else {
            add_action( 'admin_notices', [ $this, 'license_enter_notice' ] );
        }

        add_action( 'in_plugin_update_message-' . plugin_basename( DOKAN_PRO_FILE ), [ $this, 'plugin_update_message' ] );
    }

    /**
     * Initialize the updater
     *
     * @return void
     */
    protected function init_appsero() {
        $client = new \Appsero\Client( '8f0a1669-b8db-46eb-9fc4-02ac5bfe89e7', __( 'Dokan Pro', 'dokan' ), DOKAN_PRO_FILE );

        // Active license page and checker
        $args = [
            'type'        => 'submenu',
            'menu_title'  => __( 'License', 'dokan' ),
            'page_title'  => __( 'Dokan Pro License', 'dokan' ),
            'capability'  => 'manage_options',
            'parent_slug' => 'dokan',
            'menu_slug'   => 'dokan_updates',
        ];

        $this->license = $client->license();

        // just to be safe if old Appsero SDK is being used
        if ( method_exists( $this->license, 'set_option_key' ) ) {
            $this->license->set_option_key( 'dokan_pro_license' );
        }

        $this->license->add_settings_page( $args );

        // Active automatic updater
        $client->updater();
    }

    /**
     * Prompts the user to add license key if it's not already filled out
     *
     * @return void
     */
    public function license_enter_notice() {
        if ( $this->license->is_valid() ) {
            return;
        } ?>
        <div class="notice error dokan-license-notice">
            <div class="dokan-license-notice__logo">
                <img src="<?php echo DOKAN_PLUGIN_ASSEST; ?>/images/dokan-logo-small.svg" alt="Dokan Logo">
            </div>
            <div class="dokan-license-notice__message">
                <strong><?php esc_html_e( 'Activate Dokan Pro License', 'dokan' ); ?></strong>
                <p><?php printf( __( 'Please <a href="%s">enter</a> your valid <strong>Dokan Pro</strong> plugin license key to unlock more features, premium support and future updates.', 'dokan' ), admin_url( 'admin.php?page=dokan_updates' ) ); ?></p>
            </div>

            <div class="dokan-license-notice__button">
                <a class="button" href="<?php echo admin_url( 'admin.php?page=dokan_updates' ); ?>"><?php esc_html_e( 'Activate License', 'dokan' ); ?></a>
            </div>
        </div>

        <style>
            .notice.dokan-license-notice {
                display: flex;
                align-items: center;
                padding: 15px 10px;
                border: 1px solid #e4e4e4;
                border-left: 4px solid #fb6e76;
                background-image: url('<?php echo DOKAN_PLUGIN_ASSEST; ?>/images/dokan-notification-banner.svg');
                background-repeat: no-repeat;
                background-position: bottom right;
            }

            .dokan-license-notice__logo {
                margin-right: 10px;
            }

            .dokan-license-notice__logo img {
                width: 48px;
                height: auto;
            }

            .dokan-license-notice__message {
                flex-basis: 100%;
            }

            .dokan-license-notice__button {
                padding: 0 25px;
            }

            .dokan-license-notice__button .button {
                background: #fb6e76;
                color: #fff;
                border-color: #fb6e76;
                font-size: 15px;
                padding: 3px 15px;
            }

            .dokan-license-notice__button .button:hover {
                background: #f1545d;
                color: #fff;
                border-color: #fb6e76;
            }
        </style>
        <?php
    }

    /**
     * Show plugin udpate message
     *
     * @since  2.7.1
     *
     * @param array $args
     *
     * @return void
     */
    public function plugin_update_message( $args ) {
        if ( $this->license->is_valid() ) {
            return;
        }

        $upgrade_notice = sprintf(
            '</p><p class="dokan-pro-plugin-upgrade-notice" style="background: #dc4b02;color: #fff;padding: 10px;">Please <a href="%s" target="_blank">activate</a> your license key for getting regular updates and support',
            admin_url( 'admin.php?page=dokan_updates' )
        );

        echo apply_filters( $this->product_id . '_in_plugin_update_message', wp_kses_post( $upgrade_notice ) );
    }
}
