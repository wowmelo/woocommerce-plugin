<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    Wowmelo_Payment
 * @subpackage Wowmelo_Payment/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Wowmelo_Payment
 * @subpackage Wowmelo_Payment/public
 * @author     Your Name <email@example.com>
 */
class Wowmelo_Payment_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $wowmelo_payment    The ID of this plugin.
	 */
	private $wowmelo_payment;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $wowmelo_payment       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $wowmelo_payment, $version ) {

		$this->wowmelo_payment = $wowmelo_payment;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

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

		wp_enqueue_style( $this->wowmelo_payment, plugin_dir_url( __FILE__ ) . 'css/wowmelo-payment-public.css', array(), $this->version, 'all' );
	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

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

		wp_enqueue_script( $this->wowmelo_payment, plugin_dir_url( __FILE__ ) . 'js/wowmelo-payment-public.js', array( 'jquery' ), $this->version, false );

	}

}
