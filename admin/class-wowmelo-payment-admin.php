<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    Wowmelo_Payment
 * @subpackage Wowmelo_Payment/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Wowmelo_Payment
 * @subpackage Wowmelo_Payment/admin
 * @author     Your Name <email@example.com>
 */
class Wowmelo_Payment_Admin
{

    /**
     * The ID of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string $wowmelo_payment The ID of this plugin.
     */
    private $wowmelo_payment;

    /**
     * The version of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string $version The current version of this plugin.
     */
    private $version;

    /**
     * Initialize the class and set its properties.
     *
     * @param string $wowmelo_payment The name of this plugin.
     * @param string $version The version of this plugin.
     * @since    1.0.0
     */
    public function __construct($wowmelo_payment, $version)
    {

        $this->wowmelo_payment = $wowmelo_payment;
        $this->version = $version;

        $this->init_hooks();
    }

    private function init_hooks()
    {
        add_action('woocommerce_save_settings_checkout_wowmelo', array($this, 'action_save_settings'));
    }

    /**
     * Register the stylesheets for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_styles()
    {

        /**
         * This function is provided for demonstration purposes only.
         *
         * An instance of this class should be passed to the run() function
         * defined in Wowmelo_Payment_Loader as all of the hooks are defined
         * in that particular class.
         *
         * The Wowmelo_Payment_Loader will then create the relationship
         * between the defined hooks and the functions defined in this
         * class.
         */

        wp_enqueue_style($this->wowmelo_payment, plugin_dir_url(__FILE__) . 'css/wowmelo-payment-admin.css', array(), $this->version, 'all');

    }

    /**
     * Register the JavaScript for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts()
    {

        /**
         * This function is provided for demonstration purposes only.
         *
         * An instance of this class should be passed to the run() function
         * defined in Wowmelo_Payment_Loader as all of the hooks are defined
         * in that particular class.
         *
         * The Wowmelo_Payment_Loader will then create the relationship
         * between the defined hooks and the functions defined in this
         * class.
         */

        wp_enqueue_script($this->wowmelo_payment, plugin_dir_url(__FILE__) . 'js/wowmelo-payment-admin.js', array('jquery'), $this->version, false);

    }

    // Clear the transient access token when change settings
    public function action_save_settings()
    {
        if (!empty($_POST['save'])) {
            delete_transient('wowmelo_access_token');
            WC_Admin_Settings::save();
        }
    }

}
