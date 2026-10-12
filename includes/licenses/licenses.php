<?php

define('WPMC_EDD_SL_STORE_URL', 'https://mortgagecalculatorsplugin.com');

// the download ID. This is the ID of your product in EDD and should match the download ID visible in your Downloads list
define('WPMC_EDD_SL_ITEM_ID', 1726);

// the name of the settings page for the license input to be displayed
define('WPMC_EDD_LICENSE_PAGE', 'wpmc');

// GitHub releases base URL — used as fallback download source when EDD doesn't provide one
define('WPMC_GITHUB_RELEASES_URL', 'https://github.com/toddlenderd/mortgagecalculatorswp/releases/download');

// Verify the store's SSL certificate on license calls (same filter the EDD updater uses)
function wpmc_license_sslverify()
{
    return (bool) apply_filters('edd_sl_api_request_verify_ssl', true, null);
}

if (!class_exists('WPMC_EDD_SL_Plugin_Updater')) {
    // load our custom updater
    include(dirname(__FILE__) . '/WPMC_EDD_SL_Plugin_Updater.php');
}

function edd_sl_wpmc_plugin_updater()
{

    $doing_cron = defined('DOING_CRON') && DOING_CRON;
    if (!current_user_can('manage_options') && !$doing_cron) {
        return;
    }

    // retrieve our license key from the DB
    $license_key = trim(get_wpmc_option('edd_wpmc_license_key'));
    $version = MC_VERSION;

    $edd_updater = new WPMC_EDD_SL_Plugin_Updater(
        WPMC_EDD_SL_STORE_URL,
        MC_FILE,
        array(
            'version'     => $version,                 // current version number
            'license'     => $license_key,         // license key (used get_wpmc_option above to retrieve from DB)
            'item_id' => WPMC_EDD_SL_ITEM_ID,     // name of this plugin
            'author'     => 'Todd Helvik',  // author of this plugin
            'wp_override' => false,                    // frequency of update checks
            'beta'        => false
        )
    );
}

add_action('init', 'edd_sl_wpmc_plugin_updater', 10);

// Start Options
function WPMC_EDD_LICENSE_PAGE()
{
    $license = get_wpmc_option('edd_wpmc_license_key');
    $status  = get_wpmc_option('edd_wpmc_license_status'); ?>
    <div class="wrap">
        <?php _e('<p style="background: #3e50b4; padding:10px 15px; border-radius: 3px; font-size: 16px; color:#fff">To upgrade or get plugin support please visit <a href="https://mortgagecalculatorsplugin.com" target="_blank" style="color:#fff;">MortgageCalculatorsPlugin.com</a></p>'); ?>

        <form method="post" action="<?php echo (is_network_admin() ?  'edit.php?action=wpmc_update_network_options' : 'options.php') ?>">
            <?php settings_fields('edd_wpmc_license'); ?>
            <table class="form-table">
                <tbody>
                    <tr valign="top">
                        <th scope="row" valign="top">
                            <?php _e('License Key'); ?>
                        </th>
                        <td>
                            <input id="edd_wpmc_license_key" name="edd_wpmc_license_key" type="text" class="regular-text" value="<?php esc_attr_e($license); ?>" placeholder="<?php _e('Enter your license key'); ?>" />
                        </td>
                    </tr>
                    <?php if (false !== $license) { ?>
                        <tr valign="top">
                            <th scope="row" valign="top">
                                <?php _e('Activate License'); ?>
                            </th>
                            <td>
                                <?php if ($status !== false && $status == 'valid') { ?>
                                    <span style="color:green;"><?php _e('Your license is active now '); ?></span>
                                    <?php wp_nonce_field('edd_wpmc_action', 'edd_wpmc_nonce'); ?>
                                    <input type="submit" class="button-primary" name="edd_license_deactivate" value="<?php _e('Deactivate License'); ?>" />
                                <?php } else {
                                    wp_nonce_field('edd_wpmc_action', 'edd_wpmc_nonce'); ?>
                                    <input type="submit" class="button-primary" name="edd_license_activate" value="<?php _e('Activate License'); ?>" />
                                <?php
                                } ?>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
            <?php submit_button(); ?>
        </form>


        <div>
            <?php
            // retrieve the license from the database
            $license = trim(get_wpmc_option('edd_wpmc_license_key'));
            $status  = get_wpmc_option('edd_wpmc_license_status');
            // data to send in our API request
            $api_params = array(
                'edd_action' => 'check_license',
                'license'    => $license,
                'item_id'  => WPMC_EDD_SL_ITEM_ID, // the name of our product in EDD
                'url'        => home_url()
            );
            // Call the custom API.
            $response = wp_remote_post(WPMC_EDD_SL_STORE_URL, array('timeout' => 15, 'sslverify' => wpmc_license_sslverify(), 'body' => $api_params));
            $responseBody = is_wp_error($response) ? null : json_decode(wp_remote_retrieve_body($response));
            if (!is_object($responseBody)) {
                $responseBody = new stdClass();
            }
            foreach (array('customer_name', 'customer_email', 'license_limit', 'site_count', 'activations_left', 'expires') as $field) {
                if (!isset($responseBody->$field)) {
                    $responseBody->$field = '';
                }
            }
            $expires_time = strtotime($responseBody->expires);
            if ($responseBody->expires === 'lifetime') {
                $purchased_display = '';
                $expires_display = __('Lifetime', 'wpmc');
            } elseif ($expires_time) {
                $purchased_display = date('m-d-Y', strtotime('-1 year', $expires_time));
                $expires_display = date('m-d-Y', $expires_time);
            } else {
                $purchased_display = '';
                $expires_display = '';
            }
            if (!empty($license)) {
            ?>
                <h3>License Details</h3>
                <table>
                    <tr>
                        <th style="padding: 10px 20px 20px 0px;">User Name</th>
                        <th style="padding: 10px 20px 20px 0px;">Email</th>
                        <th style="padding: 10px 20px 20px 0px;">License Key</th>
                        <th style="padding: 10px 20px 20px 0px;">License Limit</th>
                        <th style="padding: 10px 20px 20px 0px;">Site Count</th>
                        <th style="padding: 10px 20px 20px 0px;">Activations Left</th>
                        <th style="padding: 10px 20px 20px 0px;">Purchased</th>
                        <th style="padding: 10px 20px 20px 0px;">Expires</th>
                        <th style="padding: 10px 20px 20px 0px;">Status</th>
                    </tr>
                    <tr>
                        <td style="padding: 10px 20px 20px 0px;"><?php echo esc_html($responseBody->customer_name) ?></td>
                        <td style="padding: 10px 20px 20px 0px;"><?php echo esc_html($responseBody->customer_email) ?></td>
                        <td style="padding: 10px 20px 20px 0px;"><?php echo esc_html($license) ?></td>
                        <td style="padding: 10px 20px 20px 0px;"><?php echo esc_html($responseBody->license_limit) ?></td>
                        <td style="padding: 10px 20px 20px 0px;"><?php echo esc_html($responseBody->site_count) ?></td>
                        <td style="padding: 10px 20px 20px 0px;"><?php echo esc_html($responseBody->activations_left) ?></td>
                        <td style="padding: 10px 20px 20px 0px;"><?php echo esc_html($purchased_display) ?></td>
                        <td style="padding: 10px 20px 20px 0px;"><?php echo esc_html($expires_display) ?></td>
                        <td style="padding: 10px 20px 20px 0px;"><?php echo (($status !== false && $status == 'valid') ? 'Active' : 'Inactive') ?></td>
                    </tr>
                </table>
            <?php
            } ?>
        </div>
        <?php
    }

    function edd_wpmc_register_option()
    {

        // creates our settings in the options table
        register_setting('edd_wpmc_license', 'edd_wpmc_license_key', 'edd_sanitize_license');
    }

    add_action('admin_init', 'edd_wpmc_register_option');

    function edd_sanitize_license($new)
    {
        $old = get_wpmc_option('edd_wpmc_license_key');

        if ($old && $old != $new) {
            delete_wpmc_option('edd_wpmc_license_status'); // new license has been entered, so must reactivate
        }
        return $new;
    }
    //License Key Activation
    function edd_wpmc_activate_license()
    {

        // listen for our activate button to be clicked
        if (isset($_POST['edd_license_activate'])) {

            // run a quick security check
            if (!check_admin_referer('edd_wpmc_action', 'edd_wpmc_nonce')) {
                return;
            } // get out if we didn't click the Activate button

            // retrieve the license from the database
            $license = trim(get_wpmc_option('edd_wpmc_license_key'));

            // data to send in our API request
            $api_params = array(
                'edd_action' => 'activate_license',
                'license'    => $license,
                'item_id'  => WPMC_EDD_SL_ITEM_ID, // the name of our product in EDD
                'url'        => home_url()
            );

            // Call the custom API.
            $response = wp_remote_post(WPMC_EDD_SL_STORE_URL, array('timeout' => 15, 'sslverify' => wpmc_license_sslverify(), 'body' => $api_params));


            // make sure the response came back okay
            if (is_wp_error($response) || 200 !== wp_remote_retrieve_response_code($response)) {
                if (is_wp_error($response)) {
                    $message = $response->get_error_message();
                } else {
                    $message = __('An error occurred, please try again.');
                }
            } else {
                $license_data = json_decode(wp_remote_retrieve_body($response));
                if (false === $license_data->success) {
                    switch ($license_data->error) {
                        case 'expired':
                            $message = sprintf(
                                __('Your license key expired on %s.'),
                                date_i18n(get_wpmc_option('date_format'), strtotime($license_data->expires, current_time('timestamp')))
                            );
                            break;
                        case 'revoked':
                            $message = __('Your license key has been disabled.');
                            break;
                        case 'missing':
                            $message = __('Invalid license.');
                            break;
                        case 'invalid':
                        case 'site_inactive':
                            $message = __('Your license is not active for this URL.');
                            break;
                        case 'item_name_mismatch':
                            $message = sprintf(__('This appears to be an invalid license key for %s.'), WPMC_EDD_SL_ITEM_ID);
                            break;
                        case 'no_activations_left':
                            $message = __('Your license key has reached its activation limit.');
                            break;
                        default:
                            $message = __('An error occurred, please try again.');
                            break;
                    }
                }
            }

            // Check if anything passed on a message constituting a failure
            if (!empty($message)) {
                if (is_network_admin()) {
                    $base_url = network_admin_url('plugins.php?page=' . WPMC_EDD_LICENSE_PAGE);
                } else {
                    $base_url = admin_url('plugins.php?page=' . WPMC_EDD_LICENSE_PAGE);
                }
                $redirect = add_query_arg(array('action' => 'license', 'sl_activation' => 'false', 'message' => urlencode($message)), $base_url);
                wp_redirect($redirect);
                exit();
            }

            // $license_data->license will be either "valid" or "invalid"
            update_wpmc_option('edd_wpmc_license_status', $license_data->license);
            if (is_network_admin()) {
                $base_url = network_admin_url('plugins.php?page=' . WPMC_EDD_LICENSE_PAGE);
            } else {
                $base_url = admin_url('plugins.php?page=' . WPMC_EDD_LICENSE_PAGE);
            }
            $redirect = add_query_arg(array('action' => 'license'), $base_url);
            wp_redirect($redirect);
            exit();
        }
    }
    add_action('admin_init', 'edd_wpmc_activate_license');

    // Deactivation
    function edd_wpmc_deactivate_license()
    {


        // listen for our activate button to be clicked
        if (isset($_REQUEST['edd_license_deactivate'])) {
            //if ( ! empty( $_POST ) && check_admin_referer( 'name_of_my_action', 'name_of_nonce_field' ) ) {
            // run a quick security check
            if (!check_admin_referer('edd_wpmc_action', 'edd_wpmc_nonce')) {
                return;
            } // get out if we didn't click the Activate button

            // retrieve the license from the database
            $license = trim(get_wpmc_option('edd_wpmc_license_key'));

            // data to send in our API request
            $api_params = array(
                'edd_action' => 'deactivate_license',
                'license'    => $license,
                //'item_id'  => urlencode(WPMC_EDD_SL_ITEM_ID), // the name of our product in EDD
                'item_id'  => WPMC_EDD_SL_ITEM_ID, // the name of our product in EDD
                'url'        => home_url()
            );

            // Call the custom API.
            $response = wp_remote_post(WPMC_EDD_SL_STORE_URL, array('timeout' => 15, 'sslverify' => wpmc_license_sslverify(), 'body' => $api_params));

            // make sure the response came back okay
            if (is_wp_error($response) || 200 !== wp_remote_retrieve_response_code($response)) {
                if (is_wp_error($response)) {
                    $message = $response->get_error_message();
                } else {
                    $message = __('An error occurred, please try again.');
                }
                if (is_network_admin()) {
                    $base_url = network_admin_url('plugins.php?page=' . WPMC_EDD_LICENSE_PAGE);
                } else {
                    $base_url = admin_url('plugins.php?page=' . WPMC_EDD_LICENSE_PAGE);
                }
                $redirect = add_query_arg(array('action' => 'license', 'sl_activation' => 'false', 'message' => urlencode($message)), $base_url);
                wp_redirect($redirect);
                exit();
            }

            // decode the license data
            $license_data = json_decode(wp_remote_retrieve_body($response));

            // $license_data->license will be either "deactivated" or "failed"
            if ($license_data->license == 'deactivated' || $license_data->license == 'failed') {
                delete_wpmc_option('edd_wpmc_license_status');
            }

            if (is_network_admin()) {
                $base_url = network_admin_url('plugins.php?page=' . WPMC_EDD_LICENSE_PAGE);
            } else {
                $base_url = admin_url('plugins.php?page=' . WPMC_EDD_LICENSE_PAGE);
            }
            $redirect = add_query_arg(array('action' => 'license'), $base_url);
            wp_redirect($redirect);
            exit();
        }
    }
    add_action('admin_init', 'edd_wpmc_deactivate_license');

    // Error Checking
    function edd_wpmc_admin_notices()
    {
        if (isset($_GET['sl_activation']) && !empty($_GET['message'])) {
            switch ($_GET['sl_activation']) {
                case 'false':
                    $message = urldecode(wp_unslash($_GET['message']));
        ?>
                    <div class="error">
                        <p><?php echo esc_html($message); ?></p>
                    </div>
    <?php
                    break;
                case 'true':
                default:
                    // Developers can put a custom success message here for when activation is successful if they way.
                    break;
            }
        }
    }
    add_action('admin_notices', 'edd_wpmc_admin_notices');
