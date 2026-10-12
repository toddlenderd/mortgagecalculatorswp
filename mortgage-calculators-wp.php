<?php

/**
 * Plugin Name:  Mortgage Calculators WP
 * Plugin URI:   https://mortgagecalculatorsplugin.com
 * Description:  A contemporary set of mortgage calculators from Lenderd.com
 * Version:      2.4.1
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
define('MC_VERSION', '2.4.1');

// Load common  functions
require(dirname(__FILE__) . '/includes/functions/functions.php');
// Freddie Mac weekly rate used as the default interest rate
require_once(dirname(__FILE__) . '/includes/functions/pmms.php');
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


function mcwp_load_textdomain()
{
    load_plugin_textdomain('wpmc', false, dirname(plugin_basename(__FILE__)) . '/languages/');
}
add_action('init', 'mcwp_load_textdomain');


function mortgage_calculator_install()
{
    // do something when plugin is activated or installed
}
function mortgage_calculator_remove()
{
    wp_clear_scheduled_hook('mcwp_pmms_refresh');
}
// Register CSS & JS so they can be enqueued only on pages that render a calculator
function mcwp_register_assets()
{
    $recaptcha = mcwp_recaptcha_config();
    $deps = array('jquery');
    if ($recaptcha['enabled']) {
        $src = ($recaptcha['version'] === 'v3')
            ? add_query_arg('render', $recaptcha['site_key'], 'https://www.google.com/recaptcha/api.js')
            : 'https://www.google.com/recaptcha/api.js';
        wp_register_script('mcwp-recaptcha', $src, array(), null, true);
        $deps[] = 'mcwp-recaptcha';
    }
    wp_register_script('wpmc_calculator', plugin_dir_url(__FILE__) . 'assets/js/bundle.js', $deps, MC_VERSION, true);
    $display = mcwp_display_options();
    $stylesheet = ($display['style'] === 'classic') ? 'wpmc.css' : 'wpmc-modern.css';
    wp_register_style('wpmc_slider', plugin_dir_url(__FILE__) . 'assets/css/' . $stylesheet, array(), MC_VERSION);
    $color = esc_attr(mcwp_get_color());
    wp_add_inline_style('wpmc_slider', '.mcalc{--mcalc-accent:' . $color . ';}.mcalc-color{background:' . $color . ';}');
    wp_localize_script('wpmc_calculator', 'mcwp_ajax', array(
        'ajaxurl'           => admin_url('admin-ajax.php'),
        'calc_res'          => __('Your calculations are on the way to your inbox!', 'wpmc'),
        'invalid_email'     => __('Please enter a valid email address.', 'wpmc'),
        'request_failed'    => __("We're having trouble sending your email. Please try again later.", 'wpmc'),
        'recaptcha_site_key' => $recaptcha['enabled'] ? $recaptcha['site_key'] : '',
        'recaptcha_version' => $recaptcha['version'],
    ));

    // Enqueue up front when the current post contains the shortcode so the CSS lands in <head>.
    // Calculators rendered elsewhere (widgets, page builders) are enqueued from the shortcode itself.
    global $post;
    if (is_singular() && $post instanceof WP_Post && has_shortcode($post->post_content, 'mcwp')) {
        mcwp_enqueue();
    }
}

function mcwp_enqueue()
{
    wp_enqueue_script('wpmc_calculator');
    wp_enqueue_style('wpmc_slider');
}

add_action('admin_enqueue_scripts', 'softlights_admin_scripts');
function softlights_admin_scripts($hook)
{
    // Only load admin assets on our plugin's settings page
    if ('toplevel_page_wpmc' !== $hook) {
        return;
    }
    wp_enqueue_style('mcwp-css', plugin_dir_url(__FILE__) . 'admin/admin.css', array(), MC_VERSION);
    wp_enqueue_script('jquery');
    wp_enqueue_style('wp-color-picker');
    wp_enqueue_script('wpmc-script-handle', plugin_dir_url(__FILE__) . 'admin/admin.js', array('wp-color-picker', 'jquery'), MC_VERSION, true);
}

add_action('wp_enqueue_scripts', 'mcwp_register_assets', 11);

// Nudge admins to configure their own reCAPTCHA keys (shown on the plugin settings page only)
function mcwp_recaptcha_admin_notice()
{
    $screen = function_exists('get_current_screen') ? get_current_screen() : null;
    if (!$screen || strpos($screen->id, 'toplevel_page_wpmc') !== 0) {
        return;
    }
    $recaptcha = mcwp_recaptcha_config();
    if ($recaptcha['enabled']) {
        return;
    }
    echo '<div class="notice notice-warning"><p>' . esc_html__('reCAPTCHA is not configured. Calculator emails are protected by a honeypot and rate limits only. Add your own reCAPTCHA site and secret keys under General Settings for stronger spam protection.', 'wpmc') . '</p></div>';
}
add_action('admin_notices', 'mcwp_recaptcha_admin_notice');
add_action('network_admin_notices', 'mcwp_recaptcha_admin_notice');

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
    // Check if current user can manage this site's options
    if (!current_user_can('manage_options')) {
        wp_die('You don\'t have the privileges to do this operation.');
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
