<?php
function mcwp_shortcode($atts = array(), $content = null, $tag = '') {
    // Load CSS & JS only on pages that actually render a calculator
    mcwp_enqueue();
    // normalize attribute keys, lowercase
    $atts = array_change_key_case((array)$atts, CASE_LOWER);
    // override default attributes with user attributes
    $atts = shortcode_atts(array('type' => 'cv',), $atts, $tag);

    $chek = get_site_option('edd_wpmc_license_key');
    if (!empty($chek)) {
        $license = get_site_option('edd_wpmc_license_key');
        $status  = get_site_option('edd_wpmc_license_status');
    } else {
        $license = get_option('edd_wpmc_license_key');
        $status  = get_option('edd_wpmc_license_status');
    }
    $calTemplate2 = '';
    $option_func = (use_network_settings('wpmc_mail_use_network_settings') === 'yes') ? 'get_site_option' : 'get_option';
    $mcwp_currency = $option_func('mcwp_currency');
    $curr_symbol = $mcwp_currency;
    // Views arrange their fields differently for the Modern and Classic styles
    $display = mcwp_display_options();
    $mcwp_modern = ($display['style'] === 'modern');

    $wrap_class = '';
    // Use include (not require_once) so the same shortcode type can appear multiple times on a page
    if ($atts['type'] == 'cv') {
        include(__DIR__ . '/views/conventional.php');
        $wrap_class = 'mcalc-conventional';
    } elseif ($atts['type'] == 'fha') {
        include(__DIR__ . '/views/fha.php');
        $wrap_class = 'mcalc-fha';
    } elseif ($atts['type'] == 'va') {
        include(__DIR__ . '/views/va.php');
        $wrap_class = 'mcalc-va';
    } elseif ($atts['type'] == 'mha') {
        include(__DIR__ . '/views/mha.php');
        $wrap_class = 'mcalc-ha';
    } elseif ($atts['type'] == 'rc') {
        include(__DIR__ . '/views/rc.php');
        $wrap_class = 'mcalc-refi';
    } elseif ($atts['type'] == 'bd') {
        include(__DIR__ . '/views/bd.php');
        $wrap_class = 'mcalc-buydown';
    }
    
    // Unlicensed premium calculators render nothing for visitors
    if (!isset($calculator_layout) || $calculator_layout === '') {
        return '';
    }
    $style_class = ($display['style'] === 'classic') ? 'mcalc-color' : 'mcalc-modern mcalc-panel-' . $display['panel'];

    $template = file_get_contents(MC_DIR.'/templates/main.html');
    $template = str_replace('{{wrap-class}}', esc_attr($wrap_class), $template);
    $template = str_replace('{{style-class}}', esc_attr($style_class), $template);
    $template = str_replace('{{type}}', esc_attr($atts['type']), $template);
    $template = str_replace('{{layout}}', $calculator_layout, $template);
    // Inject a nonce field for CSRF protection on the AJAX email submission
    $template = str_replace('{{nonce}}', wp_create_nonce('mcwp_sendmail_action'), $template);
    return $template;
}
function wporg_shortcodes_init() {
    add_shortcode('mcwp', 'mcwp_shortcode');
}
add_action('init', 'wporg_shortcodes_init');

// reCAPTCHA v2 Invisible renders from this widget; v3 needs no markup. Only printed where a calculator is on the page.
add_action('wp_footer', function(){
    if (!wp_script_is('wpmc_calculator', 'enqueued')) {
        return;
    }
    $recaptcha = mcwp_recaptcha_config();
    if ($recaptcha['enabled'] && $recaptcha['version'] === 'v2') {
        echo '<div class="g-recaptcha" data-sitekey="' . esc_attr($recaptcha['site_key']) . '" data-size="invisible"></div>';
    }
});