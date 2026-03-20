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

    // reCAPTCHA secret key: use admin setting if set, otherwise fall back to built-in default
    $recaptcha_secret = $option_func('mcwp_recaptcha_secret_key');
    if (empty($recaptcha_secret)) {
        $recaptcha_secret = MCWP_RC_SS;
    }
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

    // Resolve the site owner email address
    if (use_network_setting_email() === 'yes') {
        $to_form = get_site_option('wpmc_one_email');
    } else {
        $to_form = get_option('wpmc_one_email');
    }
    if (preg_match('/[\[\]\'^£$%&*()@#~?><>,|=_+¬-]/', $to_form)) {
        $to_form = do_shortcode($to_form);
    }

    // Email to lead — Reply-To is the site owner so they can reply back
    $mail1_sent = wp_mail($to, $subject, $body, email_headers($to_form));

    // Email to site owner — Reply-To is the lead so owner can reply directly
    $mail2_sent = wp_mail($to_form, $cc_subject, $cc_body, email_headers($to));
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
function email_headers($reply_to_override = '')
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

    // Allow overriding Reply-To (used for CC emails so owner can reply directly to the lead)
    if (!empty($reply_to_override)) {
        $reply_to_override = str_replace(array("\r", "\n"), '', $reply_to_override);
        $reply = sanitize_email($reply_to_override);
        $reply_name = ''; // Use just the email address
    }

    $headers = array(
        'Content-Type: text/html; charset=UTF-8',
        'From: ' . $from_name . ' <' . sanitize_email($from) . '>',
        'Reply-To: ' . ($reply_name ? $reply_name . ' <' . sanitize_email($reply) . '>' : sanitize_email($reply)),
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

/**
 * Build a styled email using the "Clean Card" template.
 * Uses the Calculator Color from admin settings as the accent color.
 *
 * @param array $args {
 *     @type string $calc_type    Display name, e.g. "Conventional Loan"
 *     @type string $subtitle     Subtitle line, e.g. "30 Year Fixed"
 *     @type string $total        Formatted total payment, e.g. "$1,687"
 *     @type string $message      Custom message from admin settings
 *     @type array  $rows         Array of ['label' => ..., 'value' => ...] for the breakdown table
 *     @type string $curr_symbol  Currency symbol
 *     @type string $disclaimer   Optional disclaimer text
 * }
 * @return string Full HTML email body
 */
function mcwp_email_template($args)
{
    $option_func = (use_network_settings('wpmc_mail_use_network_settings') === 'yes') ? 'get_site_option' : 'get_option';
    $color = sanitize_hex_color($option_func('mcwp_color'));
    if (!$color) {
        $color = '#1a56db'; // Fallback blue
    }

    $calc_type  = esc_html($args['calc_type']);
    $subtitle   = esc_html($args['subtitle']);
    $total      = esc_html($args['total']);
    $message    = $args['message'];
    $rows       = $args['rows'];
    $disclaimer = isset($args['disclaimer']) ? $args['disclaimer'] : '';

    // Build the breakdown rows
    $rows_html = '';
    foreach ($rows as $row) {
        $rows_html .= '
                <tr>
                  <td style="padding:12px 0; border-bottom:1px solid #f3f4f6; color:#6b7280;">' . esc_html($row['label']) . '</td>
                  <td style="padding:12px 0; border-bottom:1px solid #f3f4f6; text-align:right; font-weight:600; color:#111827;">' . esc_html($row['value']) . '</td>
                </tr>';
    }

    // Total row
    $rows_html .= '
                <tr>
                  <td style="padding:14px 0 0; font-weight:700; color:#111827; font-size:15px;">' . __('Total Monthly Payment', 'wpmc') . '</td>
                  <td style="padding:14px 0 0; text-align:right; font-weight:700; color:' . $color . '; font-size:15px;">' . $total . '</td>
                </tr>';

    // Disclaimer section
    $disclaimer_html = '';
    if (!empty($disclaimer)) {
        $disclaimer_html = '
          <tr>
            <td style="padding:0 40px 32px;">
              <p style="margin:0; font-size:12px; line-height:18px; color:#9ca3af;">' . $disclaimer . '</p>
            </td>
          </tr>';
    }

    // Custom message section
    $message_html = '';
    if (!empty($message)) {
        $message_html = '
          <tr>
            <td style="padding:24px 40px 8px;">
              <p style="margin:0; font-size:15px; line-height:24px; color:#4b5563;">' . $message . '</p>
            </td>
          </tr>';
    }

    $html = '
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#f4f5f7; padding:40px 20px; font-family:\'Helvetica Neue\', Helvetica, Arial, sans-serif; color:#2d3748;">
  <tr>
    <td align="center">
      <table role="presentation" width="600" cellpadding="0" cellspacing="0" style="background-color:#ffffff; border-radius:12px; overflow:hidden; box-shadow:0 4px 6px rgba(0,0,0,0.07);">

        <tr>
          <td style="background-color:' . $color . '; padding:32px 40px; text-align:center;">
            <h1 style="margin:0; font-size:24px; font-weight:600; color:#ffffff; letter-spacing:-0.3px;">' . __('Your Calculations', 'wpmc') . '</h1>
            <p style="margin:8px 0 0; font-size:14px; color:rgba(255,255,255,0.75);">' . $calc_type . ' &bull; ' . $subtitle . '</p>
          </td>
        </tr>

        <tr>
          <td style="padding:36px 40px 20px; text-align:center; border-bottom:1px solid #e5e7eb;">
            <p style="margin:0 0 4px; font-size:13px; text-transform:uppercase; letter-spacing:1px; color:#6b7280;">' . __('Estimated Monthly Payment', 'wpmc') . '</p>
            <p style="margin:0; font-size:48px; font-weight:700; color:' . $color . '; letter-spacing:-1px;">' . $total . '</p>
          </td>
        </tr>

        ' . $message_html . '

        <tr>
          <td style="padding:16px 40px 32px;">
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="font-size:14px;">
              ' . $rows_html . '
            </table>
          </td>
        </tr>

        ' . $disclaimer_html . '

      </table>
    </td>
  </tr>
</table>';

    return $html;
}

/**
 * Build a styled admin notification (CC) email.
 *
 * @param string $to         The lead's email address
 * @param string $calc_type  Display name of calculator type
 * @param string $body_content The lead's email body content to include as reference
 * @return string Full HTML email body for admin
 */
function mcwp_cc_email_template($to, $calc_type, $body_content)
{
    $option_func = (use_network_settings('wpmc_mail_use_network_settings') === 'yes') ? 'get_site_option' : 'get_option';
    $color = sanitize_hex_color($option_func('mcwp_color'));
    if (!$color) {
        $color = '#1a56db';
    }

    $html = '
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#f4f5f7; padding:40px 20px; font-family:\'Helvetica Neue\', Helvetica, Arial, sans-serif; color:#2d3748;">
  <tr>
    <td align="center">
      <table role="presentation" width="600" cellpadding="0" cellspacing="0" style="background-color:#ffffff; border-radius:12px; overflow:hidden; box-shadow:0 4px 6px rgba(0,0,0,0.07);">

        <tr>
          <td style="background-color:' . $color . '; padding:24px 40px; text-align:center;">
            <h1 style="margin:0; font-size:20px; font-weight:600; color:#ffffff;">' . sprintf(__('New %s Lead', 'wpmc'), esc_html($calc_type)) . '</h1>
          </td>
        </tr>

        <tr>
          <td style="padding:28px 40px;">
            <p style="margin:0 0 16px; font-size:15px; line-height:24px; color:#4b5563;">
              <a href="mailto:' . esc_attr($to) . '" style="color:' . $color . '; font-weight:600; text-decoration:none;">' . esc_html($to) . '</a>
              ' . __('requested a calculation. A copy of the email they received is below for reference.', 'wpmc') . '
            </p>
            <div style="background-color:#f9fafb; border-radius:8px; padding:20px; border:1px solid #e5e7eb;">
              ' . $body_content . '
            </div>
          </td>
        </tr>

      </table>
    </td>
  </tr>
</table>';

    return $html;
}
