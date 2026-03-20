<?php

/**
 * Plugin Name:  Mortgage Calculators WP
 * Plugin URI:   https://mortgagecalculatorsplugin.com
 * Description:  A contemporary set of mortgage calculators from Lenderd.com
 * Version:      2.2
 * Author:       Lenderd
 * Author URI:   https://lenderd.com
 * License:      GPL2
 * License URI:  https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:  wpmc
 * Domain Path:  /languages
 */

// Blocking direct access to your plugin PHP files
defined('ABSPATH') or die('No script kiddies please!');
define('MC_PATH', plugin_dir_path(__FILE__));
define('MC_DIR', __DIR__);
define('MC_FILE', __FILE__);
define('MC_URL', plugin_dir_url(__FILE__));
define('MC_VERSION', '2.2');

// Default reCAPTCHA v3 keys (obfuscated) — users can override via admin settings
define('MCWP_RC_SK', base64_decode('NkxkQXBDMGhBQUFBQUZrNk5hRVhsbW9rd2ZNdGNpUEc5alllNmw4Nw=='));
define('MCWP_RC_SS', base64_decode('NkxkQXBDMGhBQUFBQU92dm1OOG5RQkVMb2lUS3dpMGFuSGIyMEhYSQ=='));

// Load common  functions
require(dirname(__FILE__) . '/includes/functions/functions.php');
require_once(dirname(__FILE__) . '/includes/licenses/licenses.php');
// Load template functions
require_once(dirname(__FILE__) . '/includes/templates/templates.php');
// Load options functions
require_once(dirname(__FILE__) . '/includes/options/options.php');
require_once(dirname(__FILE__) . '/includes/shortcodes/mcwp.php');
// Load update network option functions
require_once(dirname(__FILE__) . '/includes/options/update_network_options.php');
// Runs when plugin is activated
register_activation_hook(__FILE__, 'mortgage_calculator_install');
// Runs on plugin deactivation
register_deactivation_hook(__FILE__, 'mortgage_calculator_remove');


function custom_theme_setup()
{
    load_plugin_textdomain('wpmc', false, dirname(plugin_basename(__FILE__)) . '/languages/');
} // end custom_theme_setup
add_action('after_setup_theme', 'custom_theme_setup');


function mortgage_calculator_install()
{
    // do something when plugin is activated or installed
}
function mortgage_calculator_remove()
{
    // do something when plugin is deactivated or removed
}
// Load CSS & JS Files
function mcwp_enqueue()
{
    wp_enqueue_script('recaptcha', 'https://www.google.com/recaptcha/api.js', array('jquery'), null, true);
    // wp_enqueue_script('wpmc_slider', plugin_dir_url(__FILE__).'assets/bootstrap-slider/bootstrap-slider.js', array( 'jquery' ), null, true);
    // wp_enqueue_script('wpmc_calculator', plugin_dir_url(__FILE__).'assets/js/wpmc.js', array( 'jquery' ), null, true);
    wp_enqueue_script('wpmc_calculator', plugin_dir_url(__FILE__) . 'assets/js/bundle.js', array('jquery'), null, true);
    wp_enqueue_style('wpmc_slider', plugin_dir_url(__FILE__) . 'assets/css/wpmc.css');
    wp_localize_script('wpmc_calculator', 'mcwp_ajax', array(
        'ajaxurl' => admin_url('admin-ajax.php'),
        'calc_res' => __('Your calculations are on the way to your inbox!', 'wpmc'),
    ));
}

add_action('admin_enqueue_scripts', 'softlights_admin_scripts');
function softlights_admin_scripts($hook)
{
    // Only load admin assets on our plugin's settings page
    if ('toplevel_page_wpmc' !== $hook) {
        return;
    }
    wp_enqueue_style('mcwp-css', plugin_dir_url(__FILE__) . 'admin/admin.css');
    wp_enqueue_script('jquery');
    wp_enqueue_style('wp-color-picker');
    wp_enqueue_script('wpmc-script-handle', plugin_dir_url(__FILE__) . 'admin/admin.js', array('wp-color-picker', 'jquery'), false, true);
}

add_action('wp_head', function () {
    $mcwp_color = mcwp_get_color();
    if ($mcwp_color) {
        echo '<style type="text/css">.mcalc-color,.mcalc .slider-handle.round,.mcalc .slider.slider-horizontal .slider-selection{background:' . esc_attr($mcwp_color) . ' !important;}</style>';
    }
});


add_action("wp_enqueue_scripts", "mcwp_enqueue", 11);

if (is_network_admin()) {
    add_filter('network_admin_menu', 'wpmc_network_admin_menu');
    function wpmc_network_admin_menu()
    {
        add_menu_page(
            __('Mortgage Calculator', 'wpmc'),
            __('Calculator', 'wpmc'),
            'manage_options',
            'wpmc',
            'mortgage_calculator_html_page',
            plugin_dir_url(__FILE__) . 'assets/images/calculator.png',
            20
        );
    }
}

// Create Top Level Menu & Sub Menu
if (is_admin()) {
    add_action('admin_menu', 'mortgage_calculator_admin_menu');

    function mortgage_calculator_admin_menu()
    {
        add_menu_page(
            __('Mortgage Calculator', 'wpmc'),
            __('Calculator', 'wpmc'),
            'manage_options',
            'wpmc',
            'mortgage_calculator_html_page',
            plugin_dir_url(__FILE__) . 'assets/images/calculator.png',
            20
        );
    }
}
// Create Tabs Template
function mortgage_calculator_html_page()
{
    wpmc_main_template(); // Load the Main template html
}
add_action('admin_init', 'wpmc_admin_init');
// Note: Removing jquery-migrate can break other plugins/themes that depend on it.
// Only remove on the frontend to minimize impact on admin.
add_action('wp_default_scripts', function ($scripts) {
    if (!is_admin() && !empty($scripts->registered['jquery'])) {
        $scripts->registered['jquery']->deps = array_diff($scripts->registered['jquery']->deps, array('jquery-migrate'));
    }
});

// Only check settings-updated on admin pages to avoid running on the frontend
if (is_admin() && isset($_GET['settings-updated'])) {
    function wpmc_admin_notice__success()
    {
        //$text = _e('<strong>Settings saved.</strong>', 'wpmc');
        //<div class="notice notice-success is-dismissible"><p><strong><?php echo $text; </strong></p></div>
?>
        <div class="updated notice">
            <p><?php _e('Settings saved.', 'wpmc'); ?></p>
        </div>
<?php
    }
    add_action('network_admin_notices', 'wpmc_admin_notice__success');
    // add_action('admin_notices', 'wpmc_admin_notice__success');
}

// write_log function
if (!function_exists('write_log')) {
    function write_log($log)
    {
        if (true === WP_DEBUG) {
            if (is_array($log) || is_object($log)) {
                error_log(print_r($log, true));
            } else {
                error_log($log);
            }
        }
    }
}


add_action('admin_action_academy_wpmc_update_local_options', 'academy_wpmc_update_local_options');
function academy_wpmc_update_local_options()
{
    // Check if current user is a site administrator
    if (!current_user_can('administrator')) {
        wp_die('You don\'t have the privileges to do this operation (should be: administrator).');
    }
    // $_POST[ 'option_page' ] below comes from a hidden input that WordPress automatically generates for admin forms. The value equals to the admin page slug.
    $page_slug = sanitize_text_field($_POST['option_page']);
    // Check that the request is coming from the administration area
    check_admin_referer($page_slug . '-options');
    // Cycle through the settings we're submitting. If there are any changes, update them.
    // Use $new_allowed_options (renamed from $new_whitelist_options in WP 5.5)
    global $new_allowed_options;
    $options = $new_allowed_options[$page_slug];

    foreach ($options as $option) {
        if (isset($_POST[$option])) {
            if ('wpmc_mail_message' == $option) {
                update_option($option, wp_kses_post(stripslashes($_POST[$option])));
            } else {
                update_option($option, sanitize_text_field(stripslashes($_POST[$option])));
            }
        }
    }

    // Finally, after saving the settings, redirect to the settings page. ()
    $query_args = array('page' => 'wpmc');
    if ($page_slug == 'wpmc_one') {
        $query_args['action'] = 'cal-one';
    } elseif ($page_slug == 'wpmc_two') {
        $query_args['action'] = 'cal-two';
    } elseif ($page_slug == 'wpmc_three') {
        $query_args['action'] = 'cal-three';
    } elseif ($page_slug == 'wpmc_four') {
        $query_args['action'] = 'cal-four';
    } elseif ($page_slug == 'wpmc_five') {
        $query_args['action'] = 'cal-five';
    } elseif ($page_slug == 'wpmc_six') {
        $query_args['action'] = 'cal-six';
    } elseif ($page_slug == 'edd_wpmc_license') {
        $query_args['view'] = 'license';
        $query_args['action'] = 'license';
        //&action=license
    }
    $query_args['settings-updated'] = 'true';
    wp_redirect(add_query_arg($query_args, admin_url('admin.php')));
    exit();
}
