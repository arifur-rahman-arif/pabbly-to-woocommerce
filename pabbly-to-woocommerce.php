<?php

/**
 * Plugin Name:       Pabbly To Woocommerce
 * Plugin URI:        https://www.linkedin.com/in/arifur-rahman-arif-51222a1b8/
 * Description:       Pabbly to Woocommere Order Creation Plugin
 * Version:           1.0.0
 * Requires at least: 5.0
 * Requires PHP:      5.4
 * Author:            AR Arif
 * Author URI:        https://www.linkedin.com/in/arifur-rahman-arif-51222a1b8/
 * Text Domain:       ptw
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 */

/* if accessed directly exit from plugin */
defined('ABSPATH') || wp_die(__('You can\'t access this page', 'wts'));

if (!defined('PTW_VERSION')) {
    // define('PTW_VERSION', '1.0.0');
    define('PTW_VERSION', time());
}

if (!defined('PTW_BASE_PATH')) {
    define('PTW_BASE_PATH', plugin_dir_path(__FILE__));
}

if (!defined('PTW_BASE_URL')) {
    define('PTW_BASE_URL', plugin_dir_url(__FILE__));
}

if (!defined('PTW_PlUGIN_NAME')) {
    define('PTW_PlUGIN_NAME', 'Pabbly To Woocommerce');
}

if (!file_exists(PTW_BASE_PATH . 'vendor/autoload.php')) {
    return;
}

require_once PTW_BASE_PATH . 'vendor/autoload.php';

final class PabblyToWoocommerce {
    /**
     * @return null
     */
    public function __construct() {
        require_once ABSPATH . 'wp-admin/includes/plugin.php';

        if ($this->version_check() == 'version_low') {
            return;
        }
        $this->register_active_deactive_hooks();
        $this->checkRequiredPluginExists();
        $this->initiatePlugin();
    }

    /**
     * @requiring all the classes once
     * @return void
     */
    public function includeFiles() {
        new \PTW\includes\PluginBase();
    }

    public function initiatePlugin() {
        if (is_plugin_active(plugin_basename(__FILE__))) {
            add_action('plugins_loaded', [$this, 'includeFiles']);
        }
    }

    /**
     * @return mixed
     */
    public function checkRequiredPluginExists() {
        if (!in_array(
            'woocommerce/woocommerce.php',
            apply_filters('active_plugins', get_option('active_plugins'))
        )) {

            if (is_plugin_active(plugin_basename(__FILE__))) {
                deactivate_plugins(plugin_basename(__FILE__));
                add_action('admin_notices', function () {
                    printf('<div class="notice notice-error is-dismissible"><h3><strong>%s %s </strong></h3><p>%s</p></div>', esc_html(WTS_PlUGIN_NAME), __('Plugin', 'ptw'), __('cannot be activated - requires the Woocommerce plugin to be activated.', 'ptw'));
                    return;
                });
            }
        }
    }

    /**
     * registering activation and deactivation Hooks
     * @return void
     */
    public function register_active_deactive_hooks() {
        register_activation_hook(__FILE__, function () {
            flush_rewrite_rules();
        });
    }

    /**
     * @return null
     */
    public function show_notice() {
        printf('<div class="notice notice-error is-dismissible"><h3><strong>%s </strong></h3><p>%s</p></div>', __('Plugin', 'ptw'), __('cannot be activated - requires at least PHP 5.4. Plugin automatically deactivated.', 'ptw'));
        return;
    }

    /**
     * @return null
     */
    public function showReviewNotice() {
        load_template(WTS_BASE_PATH . 'Includes/Templates/Parts/review_notice.php');
        return;
    }

    public function version_check() {
        if (version_compare(PHP_VERSION, '5.4') < 0) {
            if (is_plugin_active(plugin_basename(__FILE__))) {
                deactivate_plugins(plugin_basename(__FILE__));
                add_action('admin_notices', [$this, 'show_notice']);
            }
            return 'version_low';
        }
    }
}

if (!class_exists('PabblyToWoocommerce')) {
    return;
}

if (!function_exists('pabblyToWoocommerce')) {
    function pabblyToWoocommerce() {
        return new PabblyToWoocommerce();
    }
}

pabblyToWoocommerce();