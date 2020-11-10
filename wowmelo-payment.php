<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://wowmelo.com/
 * @since             1.0.0
 * @package           Wowmelo_Payment
 *
 * @wordpress-plugin
 * Plugin Name:       Wowmelo Payment
 * Plugin URI:        https://wowmelo.com/
 * Description:       This plugin helps to simplify the Wowmelo API integration into WooCommerce
 * Version:           1.0.0
 * Author:            Wowmelo
 * Author URI:        https://wowmelo.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       wowmelo-payment
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define('WOWMELO_PAYMENT_VERSION', '1.0.0');

if ( ! defined( 'WOWMELO_PLUGIN_FILE' ) ) {
    define( 'WOWMELO_PLUGIN_FILE', __FILE__ );
}
/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-wowmelo-payment-activator.php
 */
function activate_wowmelo_payment()
{
    require_once plugin_dir_path(__FILE__) . 'includes/class-wowmelo-payment-activator.php';
    Wowmelo_Payment_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-wowmelo-payment-deactivator.php
 */
function deactivate_wowmelo_payment()
{
    require_once plugin_dir_path(__FILE__) . 'includes/class-wowmelo-payment-deactivator.php';
    Wowmelo_Payment_Deactivator::deactivate();
}

register_activation_hook(__FILE__, 'activate_wowmelo_payment');
register_deactivation_hook(__FILE__, 'deactivate_wowmelo_payment');

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path(__FILE__) . 'includes/class-wowmelo-payment.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */

function run_wowmelo_payment()
{

    $plugin = new Wowmelo_Payment();
    $plugin->run();

}

run_wowmelo_payment();
