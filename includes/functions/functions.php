<?php
add_action('wp_ajax_mcwp_sendmail', 'mcwp_sendmail');
add_action('wp_ajax_nopriv_mcwp_sendmail', 'mcwp_sendmail');
function mcwp_sendmail()
{
    // Verify nonce to prevent CSRF attacks
    if (!isset($_POST['mcwp_nonce']) || !wp_verify_nonce($_POST['mcwp_nonce'], 'mcwp_sendmail_action')) {
        wp_send_json(array(
            'message' => __('Security check failed. Please refresh and try again.', 'wpmc'),
            'status' => 'error'
        ));
    }

    // Honeypot: real visitors never see this field, so anything in it is a bot. Pretend it worked.
    if (mcwp_post_text('mcwp_website') !== '') {
        wp_send_json(array(
            'message' => __('Your calculations are on the way to your inbox!', 'wpmc'),
            'status' => 'emails_sent'
        ));
    }

    $to = sanitize_email(mcwp_post_text('email'));
    if (!is_email($to)) {
        wp_send_json(array(
            'message' => __('Please enter a valid email address.', 'wpmc'),
            'status' => 'error'
        ));
    }

    // Sanitize calculator type and validate against allowed values
    $calc_type = mcwp_post_text('type');
    $allowed_types = array('cv', 'fha', 'va', 'mha', 'rc', 'bd');
    if (!in_array($calc_type, $allowed_types, true)) {
        wp_send_json(array(
            'message' => __('Invalid calculator type.', 'wpmc'),
            'status' => 'error'
        ));
    }

    if (!mcwp_human_check_passed()) {
        wp_send_json(mcwp_new_challenge());
    }

    if (!mcwp_within_rate_limits($to)) {
        wp_send_json(array(
            'message' => __('Too many requests. Please try again later.', 'wpmc'),
            'status' => 'rate_limited'
        ));
    }

    $option_func = (use_network_settings('wpmc_mail_use_network_settings') === 'yes') ? 'get_site_option' : 'get_option';
    $wpmc_mail_message = do_shortcode($option_func('wpmc_mail_message'));
    $curr_symbol = $option_func('mcwp_currency');

    // Builds $subject, $body, $cc_subject, $cc_body, $email_args and $wpmc_admin for this calculator
    require __DIR__ . '/emails/' . $calc_type . '.php';

    do_action('mcwp_sendmail', $to, $subject, $body);

    $owner_emails = mcwp_owner_emails($wpmc_admin);

    // Email to lead — Reply-To is the site owner so they can reply back
    $mail1_sent = wp_mail($to, $subject, $body, email_headers($owner_emails[0]));

    // Email to site owner — Reply-To is the lead so owner can reply directly
    $mail2_sent = wp_mail(implode(',', $owner_emails), $cc_subject, $cc_body, email_headers($to));
    if (!$mail2_sent) {
        write_log('[WPMC] Lead notification email to the site owner failed (' . $calc_type . ').');
    }

    $lead = mcwp_build_lead($to, $calc_type, $email_args);
    mcwp_send_webhook($lead);
    do_action('mcwp_lead_submitted', $lead);

    if ($mail1_sent) {
        wp_send_json(array(
            'message' => __('Your calculations are on the way to your inbox!', 'wpmc'),
            'status' => 'emails_sent'
        ));
    }
    wp_send_json(array(
        'message' => __("We're having trouble sending your email. Please try again later.", 'wpmc'),
        'status' => 'emails_failed'
    ));
}

/**
 * Read a text value from the POST body. Missing keys return an empty string.
 */
function mcwp_post_text($key)
{
    return isset($_POST[$key]) ? sanitize_text_field(wp_unslash($_POST[$key])) : '';
}

/**
 * Read a numeric POST value that the calculators format with thousands separators
 * (e.g. "1,250.5") and return it formatted for display ("1,250.50"). Whole numbers
 * keep no decimals ("1,250").
 */
function mcwp_post_amount($key)
{
    $raw = str_replace(',', '', mcwp_post_text($key));
    if (!is_numeric($raw)) {
        return '0';
    }
    $amount = round((float) $raw, 2);
    return number_format($amount, (floor($amount) == $amount) ? 0 : 2);
}

function mcwp_client_ip()
{
    // Only REMOTE_ADDR is trusted; forwarded headers are spoofable. Sites behind a proxy can filter this.
    $ip = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '';
    return (string) apply_filters('mcwp_client_ip', $ip);
}

/**
 * reCAPTCHA settings. reCAPTCHA is only used when the site owner has entered both keys.
 */
function mcwp_recaptcha_config()
{
    if (is_network_admin()) {
        $option_func = 'get_site_option';
    } else {
        $option_func = (use_network_settings('wpmc_mail_use_network_settings') === 'yes') ? 'get_site_option' : 'get_option';
    }
    $site_key = trim((string) $option_func('mcwp_recaptcha_site_key'));
    $secret_key = trim((string) $option_func('mcwp_recaptcha_secret_key'));
    return array(
        'enabled'    => ($site_key !== '' && $secret_key !== ''),
        'site_key'   => $site_key,
        'secret_key' => $secret_key,
        'version'    => ($option_func('mcwp_recaptcha_version') === 'v3') ? 'v3' : 'v2',
    );
}

/**
 * Calculator Style (modern | classic) and Results Panel (light | bold) from General Settings.
 * Unset options mean Modern with a light panel, so upgraded sites get the new design.
 */
function mcwp_display_options()
{
    $option_func = (use_network_settings('wpmc_mail_use_network_settings') === 'yes') ? 'get_site_option' : 'get_option';
    return array(
        'style' => ($option_func('mcwp_style') === 'classic') ? 'classic' : 'modern',
        'panel' => ($option_func('mcwp_panel') === 'bold') ? 'bold' : 'light',
    );
}

/**
 * What a premium calculator shows without a valid license: nothing for visitors, a short
 * notice for administrators so they know why it's missing.
 */
function mcwp_unlicensed_notice()
{
    if (!current_user_can('manage_options')) {
        return '';
    }
    return '<p class="mcalc-unlicensed">' . esc_html__('This calculator needs an active Mortgage Calculators WP premium license. Only administrators see this notice.', 'wpmc') . '</p>';
}

/**
 * Decide whether this submission came from a person: a solved fallback question always
 * passes; otherwise reCAPTCHA must pass when it is configured.
 */
function mcwp_human_check_passed()
{
    if (mcwp_verify_challenge(mcwp_post_text('mcwp_challenge'), mcwp_post_text('mcwp_answer'))) {
        return true;
    }

    $recaptcha = mcwp_recaptcha_config();
    if (!$recaptcha['enabled']) {
        // No keys configured: rely on the honeypot and rate limits
        return true;
    }

    $token = mcwp_post_text('g-recaptcha-response');
    if ($token === '') {
        return false;
    }

    $response = wp_remote_post('https://www.google.com/recaptcha/api/siteverify', array(
        'timeout' => 10,
        'body' => array(
            'secret' => $recaptcha['secret_key'],
            'response' => $token,
            'remoteip' => mcwp_client_ip(),
        ),
    ));
    if (is_wp_error($response)) {
        return false;
    }

    $result = json_decode(wp_remote_retrieve_body($response));
    write_log($result);
    if (!is_object($result) || empty($result->success)) {
        return false;
    }

    // v3 returns a score; v2 does not
    if (isset($result->score)) {
        return (float) $result->score > (float) apply_filters('mcwp_recaptcha_min_score', 0.3);
    }
    return true;
}

/**
 * Fallback question shown when reCAPTCHA fails. The answer is signed rather than stored,
 * so the browser can't choose its own numbers and failed bots don't write to the database.
 */
function mcwp_new_challenge()
{
    $num1 = wp_rand(1, 15);
    $num2 = wp_rand(1, 15);
    $expires = time() + 10 * MINUTE_IN_SECONDS;
    return array(
        'status' => 'captcha_required',
        'message' => __('Please answer the question below to send your results.', 'wpmc'),
        'question' => sprintf(__('What is %1$d plus %2$d?', 'wpmc'), $num1, $num2),
        'challenge' => $expires . ':' . mcwp_challenge_signature($num1 + $num2, $expires),
    );
}

function mcwp_challenge_signature($answer, $expires)
{
    return hash_hmac('sha256', (int) $answer . '|' . (int) $expires, wp_salt('nonce'));
}

function mcwp_verify_challenge($challenge, $answer)
{
    if ($challenge === '' || !is_numeric($answer)) {
        return false;
    }
    $parts = explode(':', $challenge, 2);
    if (count($parts) !== 2 || (int) $parts[0] < time()) {
        return false;
    }
    return hash_equals(mcwp_challenge_signature((int) $answer, (int) $parts[0]), $parts[1]);
}

/**
 * Cap how often one recipient address or one visitor IP can trigger emails, so the
 * form can't be used to spam people. Site administrators are exempt for testing.
 */
function mcwp_within_rate_limits($to)
{
    if (current_user_can('manage_options')) {
        return true;
    }
    $limits = array(
        'mcwp_rl_email_' . md5(strtolower($to)) => (int) apply_filters('mcwp_rate_limit_per_email', 5),
        'mcwp_rl_ip_' . md5(mcwp_client_ip())   => (int) apply_filters('mcwp_rate_limit_per_ip', 20),
    );
    foreach ($limits as $key => $max) {
        if ((int) get_transient($key) >= $max) {
            return false;
        }
    }
    foreach ($limits as $key => $max) {
        set_transient($key, (int) get_transient($key) + 1, HOUR_IN_SECONDS);
    }
    return true;
}

/**
 * Resolve where lead notifications go: the calculator's own Email Address setting, then
 * the Conventional calculator's, then the site admin email. Supports comma-separated lists
 * and shortcodes; [email] means the site admin email.
 */
function mcwp_owner_emails($calculator_email)
{
    $site_admin = get_option('admin_email');
    foreach (array($calculator_email, checksettings('wpmc_one_email')) as $setting) {
        $setting = trim((string) $setting);
        if ($setting === '') {
            continue;
        }
        $setting = str_replace('[email]', $site_admin, $setting);
        if (strpos($setting, '[') !== false) {
            $setting = do_shortcode($setting);
        }
        $emails = array_filter(array_map('sanitize_email', explode(',', $setting)), 'is_email');
        if (!empty($emails)) {
            return array_values($emails);
        }
    }
    return array($site_admin);
}

/**
 * Monthly principal & interest, rounded to cents.
 */
function mcwp_payment($principal, $annual_rate, $months)
{
    if ($principal <= 0 || $months <= 0) {
        return 0.0;
    }
    $rate = $annual_rate / 1200;
    if ($rate <= 0) {
        return round($principal / $months, 2);
    }
    return round($principal * $rate / (1 - pow(1 + $rate, -$months)), 2);
}

/**
 * Buydown types: temporary buydowns map to the rate reduction for each bought-down year.
 */
function mcwp_buydown_types()
{
    return array(
        '3-2-1'     => array(3, 2, 1),
        '2-1'       => array(2, 1),
        '1-0'       => array(1),
        'permanent' => array(),
    );
}

/**
 * Buydown math shared by the email and webhook. Mirrors bd_calculate() in bundle.js;
 * payments are rounded to cents before differencing so the schedule adds up exactly.
 */
function mcwp_buydown_calculate($loan, $rate, $years, $type, $points, $per_point)
{
    $loan = max(0, (float) $loan);
    $rate = max(0, (float) $rate);
    $months = max(0, (int) $years) * 12;
    $full_payment = mcwp_payment($loan, $rate, $months);
    $types = mcwp_buydown_types();
    $steps = isset($types[$type]) ? $types[$type] : $types['2-1'];

    if ($type !== 'permanent') {
        $schedule = array();
        $cost = 0;
        foreach ($steps as $index => $reduction) {
            $year_rate = max(0, $rate - $reduction);
            $payment = mcwp_payment($loan, $year_rate, $months);
            $cost += 12 * ($full_payment - $payment);
            $schedule[] = array('year' => $index + 1, 'rate' => $year_rate, 'payment' => $payment);
        }
        return array(
            'type'         => 'temporary',
            'rate'         => $rate,
            'full_payment' => $full_payment,
            'schedule'     => $schedule,
            'full_from'    => count($steps) + 1,
            'cost'         => round($cost, 2),
        );
    }

    $new_rate = max(0, $rate - max(0, (float) $points) * max(0, (float) $per_point));
    $new_payment = mcwp_payment($loan, $new_rate, $months);
    $savings = round($full_payment - $new_payment, 2);
    $cost = round($loan * max(0, (float) $points) / 100, 2);
    return array(
        'type'             => 'permanent',
        'rate'             => $rate,
        'full_payment'     => $full_payment,
        'new_rate'         => $new_rate,
        'new_payment'      => $new_payment,
        'monthly_savings'  => $savings,
        'cost'             => $cost,
        'breakeven_months' => ($savings > 0) ? (int) ceil($cost / $savings) : 0,
    );
}

/**
 * Lead data passed to the webhook and the mcwp_lead_submitted action.
 */
function mcwp_build_lead($to, $calc_type, $email_args)
{
    $default_tags = array(
        'cv'  => 'conventional mortgage',
        'fha' => 'fha mortgage',
        'va'  => 'va mortgage',
        'mha' => 'affordability',
        'rc'  => 'refinance',
        'bd'  => 'buydown',
    );
    $fields = array();
    foreach ($email_args['rows'] as $row) {
        $fields[$row['label']] = $row['value'];
    }
    return array(
        'email'        => $to,
        'type'         => $calc_type,
        'calculator'   => $email_args['calc_type'],
        'total_label'  => isset($email_args['total_label']) ? $email_args['total_label'] : __('Estimated Monthly Payment', 'wpmc'),
        'total'        => $email_args['total'],
        'fields'       => $fields,
        'tags'         => apply_filters('mcwp_lead_tags', array('calculator submission', $default_tags[$calc_type]), $calc_type),
        'page_url'     => (string) wp_get_referer(),
        'site_url'     => home_url(),
        'submitted_at' => gmdate('c'),
    );
}

/**
 * POST the lead as JSON to the Lead Webhook URL from General Settings (e.g. a GoHighLevel
 * inbound webhook or Zapier). Non-blocking so a slow endpoint never delays the visitor.
 */
function mcwp_send_webhook($lead)
{
    $option_func = (use_network_settings('wpmc_mail_use_network_settings') === 'yes') ? 'get_site_option' : 'get_option';
    $url = trim((string) $option_func('mcwp_webhook_url'));
    if ($url === '') {
        return;
    }
    // wp_safe_remote_post rejects invalid URLs and private/internal addresses
    wp_safe_remote_post($url, array(
        'timeout'  => 5,
        'blocking' => false,
        'headers'  => array('Content-Type' => 'application/json'),
        'body'     => wp_json_encode(apply_filters('mcwp_webhook_payload', $lead)),
    ));
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
/**
 * Read a calculator setting, falling back to $re when it is empty. Callers pass $re
 * already translated.
 */
function calc_fields($network, $field, $re)
{
    $set = '';
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
    } elseif ($network == 'bd') {
        $set = get_option('wpmc_four_use_network_settings');
    }
    if ($set == '0') {
        $option = get_site_option($field);
    } else {
        $option = get_option($field);
    }
    $option = $option == '' ? $re : $option;
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
 * Resolve the Calculator Color value.
 * Processes shortcodes, validates hex colors, or returns as-is for CSS color names.
 * Falls back to default blue if empty.
 */
function mcwp_get_color()
{
    $option_func = (use_network_settings('wpmc_mail_use_network_settings') === 'yes') ? 'get_site_option' : 'get_option';
    $color = $option_func('mcwp_color');

    if (empty($color)) {
        return '#1a56db';
    }

    // Process shortcodes if present
    if (strpos($color, '[') !== false) {
        $color = do_shortcode($color);
    }

    $color = trim($color);

    // Validate: hex colors, CSS named colors, and CSS functions (rgb, rgba, hsl, hsla)
    if (preg_match('/^#[0-9a-fA-F]{3,8}$/', $color)) {
        return $color;
    }
    if (preg_match('/^[a-zA-Z]+$/', $color)) {
        return $color;
    }
    if (preg_match('/^(rgba?|hsla?)\(\s*[\d\s,.\/%]+\)$/', $color)) {
        return $color;
    }

    return '#1a56db';
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
    $color = mcwp_get_color();

    $calc_type   = esc_html($args['calc_type']);
    $subtitle    = esc_html($args['subtitle']);
    $total       = esc_html($args['total']);
    $total_label = isset($args['total_label']) ? esc_html($args['total_label']) : __('Estimated Monthly Payment', 'wpmc');
    $message     = $args['message'];
    $rows        = $args['rows'];
    $disclaimer  = isset($args['disclaimer']) ? $args['disclaimer'] : '';
    $show_total_row = isset($args['show_total_row']) ? $args['show_total_row'] : true;

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
    if ($show_total_row) {
        $rows_html .= '
                <tr>
                  <td style="padding:14px 0 0; font-weight:700; color:#111827; font-size:15px;">' . __('Total Monthly Payment', 'wpmc') . '</td>
                  <td class="color" style="padding:14px 0 0; text-align:right; font-weight:700; color:' . esc_attr($color) . '; font-size:15px;">' . $total . '</td>
                </tr>';
    }

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
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" class="bg" style="background-color:#f4f5f7; padding:40px 20px; font-family:\'Helvetica Neue\', Helvetica, Arial, sans-serif; color:#2d3748;">
  <tr>
    <td align="center">
      <table role="presentation" width="600" cellpadding="0" cellspacing="0" class="bg" style="background-color:#ffffff; border-radius:12px; overflow:hidden; box-shadow:0 4px 6px rgba(0,0,0,0.07);">

        <tr>
          <td class="bg color" style="background-color:' . esc_attr($color) . '; padding:32px 40px; text-align:center;">
            <h1 style="margin:0; font-size:24px; font-weight:600; color:#ffffff; letter-spacing:-0.3px;">' . __('Your Calculations', 'wpmc') . '</h1>
            <p style="margin:8px 0 0; font-size:14px; color:rgba(255,255,255,0.75);">' . $calc_type . ' &bull; ' . $subtitle . '</p>
          </td>
        </tr>

        <tr>
          <td style="padding:36px 40px 20px; text-align:center; border-bottom:1px solid #e5e7eb;">
            <p style="margin:0 0 4px; font-size:13px; text-transform:uppercase; letter-spacing:1px; color:#6b7280;">' . $total_label . '</p>
            <p class="color" style="margin:0; font-size:48px; font-weight:700; color:' . esc_attr($color) . '; letter-spacing:-1px;">' . $total . '</p>
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
    $color = mcwp_get_color();

    $html = '
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" class="bg" style="background-color:#f4f5f7; padding:40px 20px; font-family:\'Helvetica Neue\', Helvetica, Arial, sans-serif; color:#2d3748;">
  <tr>
    <td align="center">
      <table role="presentation" width="600" cellpadding="0" cellspacing="0" class="bg" style="background-color:#ffffff; border-radius:12px; overflow:hidden; box-shadow:0 4px 6px rgba(0,0,0,0.07);">

        <tr>
          <td class="bg color" style="background-color:' . esc_attr($color) . '; padding:24px 40px; text-align:center;">
            <h1 style="margin:0; font-size:20px; font-weight:600; color:#ffffff;">' . sprintf(__('New %s Lead', 'wpmc'), esc_html($calc_type)) . '</h1>
          </td>
        </tr>

        <tr>
          <td style="padding:28px 40px;">
            <p style="margin:0 0 16px; font-size:15px; line-height:24px; color:#4b5563;">
              <a href="mailto:' . esc_attr($to) . '" class="color" style="color:' . esc_attr($color) . '; font-weight:600; text-decoration:none;">' . esc_html($to) . '</a>
              ' . __('requested a calculation. A copy of the email they received is below for reference.', 'wpmc') . '
            </p>
            <div class="bg" style="background-color:#f9fafb; border-radius:8px; padding:20px; border:1px solid #e5e7eb;">
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
