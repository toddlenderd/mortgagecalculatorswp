<?php
add_action('wp_ajax_mcwp_sendmail', 'mcwp_sendmail');
add_action('wp_ajax_nopriv_mcwp_sendmail', 'mcwp_sendmail');
function mcwp_sendmail()
{
    // Verify nonce to prevent CSRF attacks
    if (!isset($_POST['mcwp_nonce']) || !wp_verify_nonce($_POST['mcwp_nonce'], 'mcwp_sendmail_action')) {
        wp_send_json(array(
            'message' => 'Security check failed. Please refresh and try again.',
            'status' => 'error'
        ));
        wp_die();
    }

    global $shortcode_tags;
    $to = sanitize_email($_POST['email']);
    $uns = get_option('wpmc_mail_use_network_settings');
    $option_func = (($uns === false) ? 'get_site_option' : (($uns == 1) ? 'get_site_option' : 'get_option'));
    if (use_network_setting_email() === 'yes') {
        $wpmc_mail_message = do_shortcode(get_site_option('wpmc_mail_message'));
    } else {
        $wpmc_mail_message = do_shortcode(get_option('wpmc_mail_message'));
    }
    $option_func = (use_network_settings('wpmc_mail_use_network_settings') === 'yes') ? 'get_site_option' : 'get_option';
    $mcwp_currency = $option_func('mcwp_currency');
    //$locale='en-US'; //browser or user locale
    //$currency= $mcwp_currency;
    //$fmt = new NumberFormatter( $locale."@currency=$currency", NumberFormatter::CURRENCY );
    $curr_symbol = $mcwp_currency;
    $body = '';

    $add_captcha_passed = false;
    if (isset($_POST['adc_1']) && isset($_POST['adc_2']) && isset($_POST['adc_3'])) {
        if ($_POST['adc_1'] + $_POST['adc_2'] == $_POST['adc_3']) {
            $add_captcha_passed = true;
        }
    }

    // reCAPTCHA secret key is stored as a WP option (set via admin settings)
    $recaptcha_secret = $option_func('mcwp_recaptcha_secret_key');
    $response = wp_remote_post('https://www.google.com/recaptcha/api/siteverify', [
        'body' => [
            'secret' => $recaptcha_secret,
            'response' => sanitize_text_field($_POST['g-recaptcha-response']),
            'remoteip' => $_SERVER['REMOTE_ADDR'] // Only use REMOTE_ADDR; forwarded headers are spoofable
        ]
    ]);

    $response = json_decode(wp_remote_retrieve_body($response));
    write_log($response);


    // Sanitize calculator type and validate against allowed values
    $calc_type = isset($_POST['type']) ? sanitize_text_field($_POST['type']) : '';
    $allowed_types = array('cv', 'fha', 'va', 'mha', 'rc');
    if (!in_array($calc_type, $allowed_types, true)) {
        wp_send_json(array(
            'message' => 'Invalid calculator type.',
            'status' => 'error'
        ));
        wp_die();
    }
    require_once('emails/' . $calc_type . '.php');

    if ((!$response->success || $response->score <= 0.3)) {
        if (!$add_captcha_passed) {
            $num1 = rand(1, 15);
            $num2 = rand(1, 15);
            wp_send_json(array(
                'message' => 'Please complete the captcha',
                'status' => 'captcha_required',
                'adc_1' => $num1,
                'adc_2' => $num2,
                'question' => 'What is ' . $num1 . ' plus ' . $num2 . '?'
            ));
            exit;
        }
    };

    do_action('mcwp_sendmail', $to, $subject, $body);

    $mail1_sent = wp_mail($to, $subject, $body, email_headers());

    // $cc_body .= $body;
    if (use_network_setting_email() === 'yes') {
        $to_form = get_site_option('wpmc_one_email');
    } else {
        $to_form = get_option('wpmc_one_email');
    }
    if (preg_match('/[\[\]\'^£$%&*()@#~?><>,|=_+¬-]/', $to_form)) {
        $to_form = do_shortcode($to_form);
    }

    $mail2_sent = wp_mail($to_form, $cc_subject, $cc_body, email_headers());
    if ($mail1_sent && $mail2_sent) {
        wp_send_json(array(
            'message' => 'Your calculations are on the way to your inbox!',
            'status' => 'emails_sent'
        ));
        exit;
    } else {
        wp_send_json(array(
            'message' => "We're having trouble sending your email. Please try again later.",
            'status' => 'emails_failed'
        ));
        exit;
    }
    wp_die();
}
function body_dynamic($msg_body, $current_post)
{
    $msg_body_arr = preg_split('/\r\n|[\r\n]/', $msg_body);
    $newpost = $current_post;
    if (is_array($newpost) && isset($newpost['action'])) {
        unset($newpost['action']);
    }
    if (is_array($newpost) && isset($newpost['type'])) {
        unset($newpost['type']);
    }
    if (is_array($newpost) && isset($newpost['email'])) {
        unset($newpost['email']);
    }
    $newpost_replace = array();
    foreach ($newpost as $key => $value) {
        $newpost_replace[str_replace("_", "-", $key)] = $value;
    }
    $emailmessage = $msg_body_arr;
    foreach ($newpost_replace as $shortkey => $val) {
        $emailmessage =  str_replace('[' . $shortkey . ']', $val, $emailmessage);
    }
    $body_part_dynamic = '';
    foreach ($emailmessage as $key => $val) {
        if ($val != "" && !empty($val)) {
            $body_part_dynamic .= '<p>' . $val . '</p>';
        }
    }
    return $body_part_dynamic;
}
function use_network_setting_email()
{
    $uns = get_option('wpmc_mail_use_network_settings');
    if ($uns == '0') {
        return 'yes';
    } else {
        return 'no';
    }
}
function checksettings($val)
{
    $uns = get_option('wpmc_mail_use_network_settings');
    if ($uns == '0') {
        return get_site_option($val);
    } else {
        return get_option($val);
    }
}
function wpmc_one_use_network_settings()
{
    // use conventional network settings
    $uns = get_option('wpmc_one_use_network_settings');
    if ($uns == '0') {
        return 'yes';
    } else {
        return 'no';
    }
}
function use_network_settings($val)
{
    // use conventional network settings
    $uns = get_option($val);
    if ($uns == '0') {
        return 'yes';
    } else {
        return 'no';
    }
}
function calc_fields($network, $field, $re)
{
    if ($network == 'cv') {
        $set = get_option('wpmc_one_use_network_settings');
    } elseif ($network == 'fha') {
        $set = get_option('wpmc_two_use_network_settings');
    } elseif ($network == 'va') {
        $set = get_option('wpmc_three_use_network_settings');
    } elseif ($network == 'mha') {
        $set = get_option('wpmc_five_use_network_settings');
    } elseif ($network == 'rc') {
        $set = get_option('wpmc_six_use_network_settings');
    }
    if ($set == '0') {
        $option = get_site_option($field);
    } else {
        $option = get_option($field);
    }
    $option = $option == '' ? __($re, 'wpmc') : $option;
    return $option;
}
function email_headers()
{
    $from = checksettings('wpmc_mail_from');
    $from = (preg_match('/[\[\]\'^£$%&*()@#~?><>,|=_+¬-]/', $from)) ? do_shortcode($from) : $from;
    $from_name = checksettings('wpmc_mail_from_name');
    $from_name = (preg_match('/[\[\]\'^£$%&*()@#~?><>,|=_+¬-]/', $from_name)) ? do_shortcode($from_name) : $from_name;
    $reply = checksettings('wpmc_mail_reply_to');
    $reply = (preg_match('/[\[\]\'^£$%&*()@#~?><>,|=_+¬-]/', $reply)) ? do_shortcode($reply) : $reply;
    $reply_name = checksettings('wpmc_mail_reply_to_name');
    $reply_name = (preg_match('/[\[\]\'^£$%&*()@#~?><>,|=_+¬-]/', $reply_name)) ? do_shortcode($reply_name) : $reply_name;

    // Strip newlines to prevent email header injection attacks
    $from = str_replace(array("\r", "\n"), '', $from);
    $from_name = str_replace(array("\r", "\n"), '', $from_name);
    $reply = str_replace(array("\r", "\n"), '', $reply);
    $reply_name = str_replace(array("\r", "\n"), '', $reply_name);

    $headers = array(
        'Content-Type: text/html; charset=UTF-8',
        'From: ' . $from_name . ' <' . sanitize_email($from) . '>',
        'Reply-To: ' . $reply_name . ' <' . sanitize_email($reply) . '>',
    );
    return $headers;
}
function get_wpmc_option($option_name)
{
    if (is_network_admin()) {
        return get_site_option($option_name);
    } else {
        return get_option($option_name);
    }
}
function update_wpmc_option($option_name, $option_value)
{
    if (is_network_admin()) {
        return update_site_option($option_name, $option_value);
    } else {
        return update_option($option_name, $option_value);
    }
}
function delete_wpmc_option($option_name)
{
    if (is_network_admin()) {
        return delete_site_option($option_name);
    } else {
        return delete_option($option_name);
    }
}
/**
 * Get the client IP address.
 * Only uses REMOTE_ADDR as HTTP_CLIENT_IP and HTTP_X_FORWARDED_FOR
 * are trivially spoofable by the client.
 */
function get_ip_address()
{
    return $_SERVER['REMOTE_ADDR'];
}
