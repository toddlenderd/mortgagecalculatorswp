<?php
// Define Options When Admin Initializes
function wpmc_admin_init()
{

    // Calculator Mail Options
    // Set Section or Option-Group
    add_settings_section('wpmc_mail', '', 'wpmc_mail_display_shortcode', 'wpmc-settings-mail');

    if (is_multisite() && !is_network_admin()) {
        add_settings_field('wpmc_mail_use_network_settings', __('Use Network Settings', 'wpmc'), 'mcwp_checkbox', 'wpmc-settings-mail', 'wpmc_mail', array('wpmc_mail_use_network_settings', 'wpmc_mail'));
        register_setting('wpmc_mail', 'wpmc_mail_use_network_settings');
    }

    $text = 'display_text_element';
    $textarea = 'mcwp_textarea';

    $allfields = array(
        array('mcwp_currency', 'type' => 'mcwp_currency', 'section_name' => 'wpmc-settings-mail', 'label' => __('Currency', 'wpmc'), 'group' => 'wpmc_mail', 'placeholder' => __('Choose Currency', 'wpmc'),),
        array('mcwp_color', 'type' => 'color_input', 'section_name' => 'wpmc-settings-mail', 'label' => __('Calculator Color', 'wpmc'), 'group' => 'wpmc_mail',  'placeholder' => __('Choose Color', 'wpmc'),),
        array('mcwp_recaptcha_version', 'type' => 'mcwp_recaptcha_version_select', 'section_name' => 'wpmc-settings-mail', 'label' => __('reCAPTCHA Type', 'wpmc'), 'group' => 'wpmc_mail',),
        array('mcwp_recaptcha_site_key', 'type' => $text, 'section_name' => 'wpmc-settings-mail', 'label' => __('reCAPTCHA Site Key', 'wpmc'), 'group' => 'wpmc_mail', 'placeholder' => __('Required to enable reCAPTCHA', 'wpmc'),),
        array('mcwp_recaptcha_secret_key', 'type' => $text, 'section_name' => 'wpmc-settings-mail', 'label' => __('reCAPTCHA Secret Key', 'wpmc'), 'group' => 'wpmc_mail', 'placeholder' => __('Required to enable reCAPTCHA', 'wpmc'),),
        array('mcwp_webhook_url', 'type' => $text, 'section_name' => 'wpmc-settings-mail', 'label' => __('Lead Webhook URL', 'wpmc'), 'group' => 'wpmc_mail', 'placeholder' => __('Optional: POST each lead as JSON to this URL (e.g. a GoHighLevel or Zapier webhook)', 'wpmc'),),
        array('wpmc_mail_from', 'type' => $text, 'section_name' => 'wpmc-settings-mail', 'label' => __('From Email', 'wpmc'), 'group' => 'wpmc_mail',  'placeholder' => __('Email address your leads will receive email from...', 'wpmc'),),
        array('wpmc_mail_from_name', 'type' => $text, 'section_name' => 'wpmc-settings-mail', 'label' => __('From Name', 'wpmc'), 'group' => 'wpmc_mail',  'placeholder' => __('Name that your leads will receive email from...', 'wpmc'),),
        array('wpmc_mail_reply_to', 'type' => $text, 'section_name' => 'wpmc-settings-mail', 'label' => __('Reply-To Email', 'wpmc'), 'group' => 'wpmc_mail',  'placeholder' => __('Email address your leads will reply to if different from above...', 'wpmc'),),
        array('wpmc_mail_message', 'type' => $textarea, 'section_name' => 'wpmc-settings-mail', 'label' => __('Message', 'wpmc'), 'group' => 'wpmc_mail',  'placeholder' => __('This message will appear at the top of the emails sent to your leads...', 'wpmc'),),
    );

    $sanitizers = array(
        'mcwp_recaptcha_version'    => 'mcwp_sanitize_recaptcha_version',
        'mcwp_recaptcha_site_key'   => 'sanitize_text_field',
        'mcwp_recaptcha_secret_key' => 'sanitize_text_field',
        'mcwp_webhook_url'          => 'esc_url_raw',
    );
    foreach ($allfields as $key => $val) {
        add_settings_field($val[0], $val['label'], $val['type'], $val['section_name'], $val['group'], $val);
        if (isset($sanitizers[$val[0]])) {
            register_setting($val['group'], $val[0], array('sanitize_callback' => $sanitizers[$val[0]]));
        } else {
            register_setting($val['group'], $val[0]);
        }
    }

    // Calculator One Options
    // Set Section or Option-Group
    add_settings_section('wpmc_one', '', 'wpmc_one_display_shortcode', 'wpmc-settings-one');

    if (is_multisite() && !is_network_admin()) {
        add_settings_field('wpmc_one_use_network_settings', __('Use Network Settings', 'wpmc'), 'mcwp_checkbox', 'wpmc-settings-one', 'wpmc_one', array('wpmc_one_use_network_settings', 'wpmc_one'));
        register_setting('wpmc_one', 'wpmc_one_use_network_settings');
    }
    $allfields = array(
        array(
            'wpmc_one_email', 'type' => $text, 'section_name' => 'wpmc-settings-one', 'label' => __('Email Address', 'wpmc'), 'group' => 'wpmc_one',
            'placeholder' => __('Email address your leads will be sent to...', 'wpmc'),
        ),
        array(
            'wpmc_one_disclaimer', 'type' => $textarea, 'section_name' => 'wpmc-settings-one', 'label' => __('Disclaimer', 'wpmc'), 'group' => 'wpmc_one',
            'placeholder' => __('If you would like to display a disclaimer under the calculator\'s values, place that here...', 'wpmc'),
        ),

        array('wpmc_one_pp', 'type' => $text, 'section_name' => 'wpmc-settings-one', 'label' => __('Purchase Price', 'wpmc'), 'group' => 'wpmc_one',),
        array('wpmc_one_dp', 'type' => $text, 'section_name' => 'wpmc-settings-one', 'label' => __('Down Payment', 'wpmc'), 'group' => 'wpmc_one',),
        array('wpmc_one_ir', 'type' => $text, 'section_name' => 'wpmc-settings-one', 'label' => __('Interest Rate', 'wpmc'), 'group' => 'wpmc_one',),
        array('wpmc_one_mt', 'type' => $text, 'section_name' => 'wpmc-settings-one', 'label' => __('Mortgage Term (Year)', 'wpmc'), 'group' => 'wpmc_one',),
        array('wpmc_one_at', 'type' => $text, 'section_name' => 'wpmc-settings-one', 'label' => __('Annual Taxes', 'wpmc'), 'group' => 'wpmc_one',),

        array('mcwp_hide_insurance_one', 'type' => 'mcwp_dropdown', 'section_name' => 'wpmc-settings-one', 'label' => __('Hide Insurance', 'wpmc'), 'group' => 'wpmc_one',),
        array('wpmc_one_ai', 'type' => $text, 'section_name' => 'wpmc-settings-one', 'label' => __('Annual Insurance', 'wpmc'), 'group' => 'wpmc_one',),

        array('mcwp_hide_hoa_one', 'type' => 'mcwp_dropdown', 'section_name' => 'wpmc-settings-one', 'label' => __('Hide HOA', 'wpmc'), 'group' => 'wpmc_one',),
        array('wpmc_one_mhoa', 'type' => $text, 'section_name' => 'wpmc-settings-one', 'label' => __('Monthly HOA', 'wpmc'), 'group' => 'wpmc_one',),

        array('mcwp_hide_insurance_rate', 'type' => 'mcwp_dropdown', 'section_name' => 'wpmc-settings-one', 'label' => __('Hide Mortgage Insurance Rate', 'wpmc'), 'group' => 'wpmc_one',),
        array('wpmc_one_mpmi', 'type' => $text, 'section_name' => 'wpmc-settings-one', 'label' => __('Monthly PMI', 'wpmc'), 'group' => 'wpmc_one',),
        array('wpmc_one_inr', 'type' => $text, 'section_name' => 'wpmc-settings-one', 'label' => __('Mortgage Insurance Rate', 'wpmc'), 'group' => 'wpmc_one',),

        array('wpmc_one_pp_initial', 'type' => $text, 'section_name' => 'wpmc-settings-one', 'label' => __('Purchase Price', 'wpmc'), 'group' => 'wpmc_one', 'placeholder' => __('Default: 250,000', 'wpmc'),),
        array('wpmc_one_dp_initial', 'type' => $text, 'section_name' => 'wpmc-settings-one', 'label' => __('Down Payment %', 'wpmc'), 'group' => 'wpmc_one', 'placeholder' => __('Default: 5', 'wpmc'),),
        array('wpmc_one_ir_min', 'type' => $text, 'section_name' => 'wpmc-settings-one', 'label' => __('Interest Rate %', 'wpmc'), 'group' => 'wpmc_one', 'placeholder' => __('Default: 5', 'wpmc'),),
        array('wpmc_one_at_initial', 'type' => $text, 'section_name' => 'wpmc-settings-one', 'label' => __('Annual Taxes %', 'wpmc'), 'group' => 'wpmc_one', 'placeholder' => __('Default: 1', 'wpmc'),),

        array('wpmc_one_ai_initial', 'type' => $text, 'section_name' => 'wpmc-settings-one', 'label' => __('Annual Insurance', 'wpmc'), 'group' => 'wpmc_one', 'placeholder' => __('Default: 600', 'wpmc'),),
        array('wpmc_one_mhoa_initial', 'type' => $text, 'section_name' => 'wpmc-settings-one', 'label' => __('Monthly HOA', 'wpmc'), 'group' => 'wpmc_one', 'placeholder' => __('Default: 50', 'wpmc'),),

        array('wpmc_one_inr_init', 'type' => $text, 'section_name' => 'wpmc-settings-one', 'label' => __('Mortgage Insurance Rate %', 'wpmc'), 'group' => 'wpmc_one', 'placeholder' => __('Default: 0', 'wpmc'),),

    );

    foreach ($allfields as $key => $val) {
        add_settings_field($val[0], $val['label'], $val['type'], $val['section_name'], $val['group'], $val);
        register_setting($val['group'], $val[0]);
    }

    // Calculator Two Options
    // Set Section or Option-Group
    add_settings_section('wpmc_two', '', 'wpmc_two_display_shortcode', 'wpmc-settings-two');

    if (is_multisite() && !is_network_admin()) {
        add_settings_field('wpmc_two_use_network_settings', __('Use Network Settings', 'wpmc'), 'mcwp_checkbox', 'wpmc-settings-two', 'wpmc_two', array('wpmc_two_use_network_settings', 'wpmc_two'));
        register_setting('wpmc_two', 'wpmc_two_use_network_settings');
    }

    $allfields = array(
        array(
            'wpmc_two_email', 'type' => $text, 'section_name' => 'wpmc-settings-two', 'label' => __('Email Address', 'wpmc'), 'group' => 'wpmc_two',
            'placeholder' => __('Email address your leads will be sent to...', 'wpmc'),
        ),

        array(
            'wpmc_two_disclaimer', 'type' => $textarea, 'section_name' => 'wpmc-settings-two', 'label' => __('Disclaimer', 'wpmc'), 'group' => 'wpmc_two',
            'placeholder' => __('If you would like to display a disclaimer under the calculator\'s values, place that here...', 'wpmc'),
        ),

        array('wpmc_two_pp', 'type' => $text, 'section_name' => 'wpmc-settings-two', 'label' => __('Purchase Price', 'wpmc'), 'group' => 'wpmc_two',),
        array('wpmc_two_dp', 'type' => $text, 'section_name' => 'wpmc-settings-two', 'label' => __('Down Payment', 'wpmc'), 'group' => 'wpmc_two',),
        array('wpmc_two_ir', 'type' => $text, 'section_name' => 'wpmc-settings-two', 'label' => __('Interest Rate', 'wpmc'), 'group' => 'wpmc_two',),
        array('wpmc_two_mt', 'type' => $text, 'section_name' => 'wpmc-settings-two', 'label' => __('Mortgage Term (Year)', 'wpmc'), 'group' => 'wpmc_two',),
        array('wpmc_two_at', 'type' => $text, 'section_name' => 'wpmc-settings-two', 'label' => __('Annual Taxes', 'wpmc'), 'group' => 'wpmc_two',),

        array('mcwp_hide_insurance_two', 'type' => 'mcwp_dropdown', 'section_name' => 'wpmc-settings-two', 'label' => __('Hide Insurance', 'wpmc'), 'group' => 'wpmc_two',),
        array('wpmc_two_ai', 'type' => $text, 'section_name' => 'wpmc-settings-two', 'label' => __('Annual Insurance', 'wpmc'), 'group' => 'wpmc_two',),

        array('mcwp_hide_hoa_two', 'type' => 'mcwp_dropdown', 'section_name' => 'wpmc-settings-two', 'label' => __('Hide HOA', 'wpmc'), 'group' => 'wpmc_two',),
        array('wpmc_two_mhoa', 'type' => $text, 'section_name' => 'wpmc-settings-two', 'label' => __('Monthly HOA', 'wpmc'), 'group' => 'wpmc_two',),

        array('wpmc_two_pp_initial', 'type' => $text, 'section_name' => 'wpmc-settings-two', 'label' => __('Purchase Price', 'wpmc'), 'group' => 'wpmc_two',  'placeholder' => __('Default: 250,000', 'wpmc'),),
        array('wpmc_two_dp_initial', 'type' => $text, 'section_name' => 'wpmc-settings-two', 'label' => __('Down Payment %', 'wpmc'), 'group' => 'wpmc_two',  'placeholder' => __('Default: 5', 'wpmc'),),
        array('wpmc_two_ir_min', 'type' => $text, 'section_name' => 'wpmc-settings-two', 'label' => __('Interest Rate %', 'wpmc'), 'group' => 'wpmc_two',  'placeholder' => __('Default: 5', 'wpmc'),),
        array('wpmc_two_at_initial', 'type' => $text, 'section_name' => 'wpmc-settings-two', 'label' => __('Annual Taxes %', 'wpmc'), 'group' => 'wpmc_two',  'placeholder' => __('Default: 1', 'wpmc'),),
        array('wpmc_two_ai_initial', 'type' => $text, 'section_name' => 'wpmc-settings-two', 'label' => __('Annual Insurance', 'wpmc'), 'group' => 'wpmc_two',  'placeholder' => __('Default: 600', 'wpmc'),),
        array('wpmc_two_mhoa_initial', 'type' => $text, 'section_name' => 'wpmc-settings-two', 'label' => __('Monthly HOA', 'wpmc'), 'group' => 'wpmc_two',  'placeholder' => __('Default: 50', 'wpmc'),),
    );

    foreach ($allfields as $key => $val) {
        add_settings_field($val[0], $val['label'], $val['type'], $val['section_name'], $val['group'], $val);
        register_setting($val['group'], $val[0]);
    }

    // Calculator Three Options
    // Set Section or Option-Group
    add_settings_section('wpmc_three', '', 'wpmc_three_display_shortcode', 'wpmc-settings-three');

    if (is_multisite() && !is_network_admin()) {
        add_settings_field('wpmc_three_use_network_settings', __('Use Network Settings', 'wpmc'), 'mcwp_checkbox', 'wpmc-settings-three', 'wpmc_three', array('wpmc_three_use_network_settings', 'wpmc_three'));
        register_setting('wpmc_three', 'wpmc_three_use_network_settings');
    }

    $allfields = array(
        array(
            'wpmc_three_email', 'type' => $text, 'section_name' => 'wpmc-settings-three', 'label' => __('Email Address', 'wpmc'), 'group' => 'wpmc_three',
            'placeholder' => __('Email address your leads will be sent to...', 'wpmc'),
        ),
        array('wpmc_three_disclaimer', 'type' => $textarea, 'section_name' => 'wpmc-settings-three', 'label' => __('Disclaimer', 'wpmc'), 'group' => 'wpmc_three', 'placeholder' => __('If you would like to display a disclaimer under the calculator\'s values, place that here...', 'wpmc'),),

        array('wpmc_three_ftvl', 'type' => $text, 'section_name' => 'wpmc-settings-three', 'label' => __('First Time VA Loan?', 'wpmc'), 'group' => 'wpmc_three',),
        array('wpmc_three_tos', 'type' => $text, 'section_name' => 'wpmc-settings-three', 'label' => __('Type of Service', 'wpmc'), 'group' => 'wpmc_three',),
        array('wpmc_three_dp', 'type' => $text, 'section_name' => 'wpmc-settings-three', 'label' => __('Down Payment', 'wpmc'), 'group' => 'wpmc_three',),
        array('wpmc_three_pp', 'type' => $text, 'section_name' => 'wpmc-settings-three', 'label' => __('Purchase Price', 'wpmc'), 'group' => 'wpmc_three',),
        array('wpmc_three_ir', 'type' => $text, 'section_name' => 'wpmc-settings-three', 'label' => __('Interest Rate', 'wpmc'), 'group' => 'wpmc_three',),
        array('wpmc_three_mt', 'type' => $text, 'section_name' => 'wpmc-settings-three', 'label' => __('Mortgage Term (Year)', 'wpmc'), 'group' => 'wpmc_three',),
        array('wpmc_three_at', 'type' => $text, 'section_name' => 'wpmc-settings-three', 'label' => __('Annual Taxes', 'wpmc'), 'group' => 'wpmc_three',),

        array('mcwp_hide_insurance_three', 'type' => 'mcwp_dropdown', 'section_name' => 'wpmc-settings-three', 'label' => __('Hide Insurance', 'wpmc'), 'group' => 'wpmc_three',),
        array('wpmc_three_ai', 'type' => $text, 'section_name' => 'wpmc-settings-three', 'label' => __('Annual Insurance', 'wpmc'), 'group' => 'wpmc_three',),

        array('mcwp_hide_hoa_three', 'type' => 'mcwp_dropdown', 'section_name' => 'wpmc-settings-three', 'label' => __('Hide HOA', 'wpmc'), 'group' => 'wpmc_three',),
        array('wpmc_three_mhoa', 'type' => $text, 'section_name' => 'wpmc-settings-three', 'label' => __('Monthly HOA', 'wpmc'), 'group' => 'wpmc_three',),

        array('wpmc_three_pp_initial', 'type' => $text, 'section_name' => 'wpmc-settings-three', 'label' => __('Purchase Price', 'wpmc'), 'group' => 'wpmc_three',         'placeholder' => __('Default: 250,000', 'wpmc'),),
        array('wpmc_three_dp_initial', 'type' => $text, 'section_name' => 'wpmc-settings-three', 'label' => __('Down Payment %', 'wpmc'), 'group' => 'wpmc_three', 'placeholder' => __('Default: 0', 'wpmc'),),
        array('wpmc_three_ir_initial', 'type' => $text, 'section_name' => 'wpmc-settings-three', 'label' => __('Interest Rate %', 'wpmc'), 'group' => 'wpmc_three', 'placeholder' => __('Default: 5', 'wpmc'),),
        array('wpmc_three_at_initial', 'type' => $text, 'section_name' => 'wpmc-settings-three', 'label' => __('Annual Taxes %', 'wpmc'), 'group' => 'wpmc_three', 'placeholder' => __('Default: 1', 'wpmc'),),
        array('wpmc_three_ai_initial', 'type' => $text, 'section_name' => 'wpmc-settings-three', 'label' => __('Annual Insurance', 'wpmc'), 'group' => 'wpmc_three', 'placeholder' => __('Default: 600', 'wpmc'),),
        array('wpmc_three_mhoa_initial', 'type' => $text, 'section_name' => 'wpmc-settings-three', 'label' => __('Monthly HOA', 'wpmc'), 'group' => 'wpmc_three', 'placeholder' => __('Default: 50', 'wpmc'),),
    );


    foreach ($allfields as $key => $val) {
        add_settings_field($val[0], $val['label'], $val['type'], $val['section_name'], $val['group'], $val);
        register_setting($val['group'], $val[0]);
    }

    // Calculator Five Options
    // Set Section or Option-Group
    add_settings_section('wpmc_five', '', 'wpmc_five_display_shortcode', 'wpmc-settings-five');

    if (is_multisite() && !is_network_admin()) {
        add_settings_field('wpmc_five_use_network_settings', __('Use Network Settings', 'wpmc'), 'mcwp_checkbox', 'wpmc-settings-five', 'wpmc_five', array('wpmc_five_use_network_settings', 'wpmc_five'));
        register_setting('wpmc_five', 'wpmc_five_use_network_settings');
    }

    $allfields = array(
        array(
            'wpmc_five_email', 'type' => $text, 'section_name' => 'wpmc-settings-five', 'label' => __('Email Address', 'wpmc'), 'group' => 'wpmc_five',
            'placeholder' => __('Email address your leads will be sent to...', 'wpmc'),
        ),

        array('wpmc_five_disclaimer', 'type' => $textarea, 'section_name' => 'wpmc-settings-five', 'label' => __('Disclaimer', 'wpmc'), 'group' => 'wpmc_five', 'placeholder' => __('If you would like to display a disclaimer under the calculator\'s values, place that here...', 'wpmc'),),

        array('wpmc_five_ai', 'type' => $text, 'section_name' => 'wpmc-settings-five', 'label' => __('Annual Income', 'wpmc'), 'group' => 'wpmc_five',),
        array('wpmc_five_md', 'type' => $text, 'section_name' => 'wpmc-settings-five', 'label' => __('Monthly debts', 'wpmc'), 'group' => 'wpmc_five',),
        array('wpmc_five_eapt', 'type' => $text, 'section_name' => 'wpmc-settings-five', 'label' => __('Estimated annual property taxes', 'wpmc'), 'group' => 'wpmc_five',),
        array('wpmc_five_eahi', 'type' => $text, 'section_name' => 'wpmc-settings-five', 'label' => __('Estimated annual home insurance', 'wpmc'), 'group' => 'wpmc_five',),
        array('wpmc_five_dp', 'type' => $text, 'section_name' => 'wpmc-settings-five', 'label' => __('Down payment', 'wpmc'), 'group' => 'wpmc_five',),
        array('wpmc_five_ir', 'type' => $text, 'section_name' => 'wpmc-settings-five', 'label' => __('Interest rate', 'wpmc'), 'group' => 'wpmc_five',),
        array('wpmc_five_lt', 'type' => $text, 'section_name' => 'wpmc-settings-five', 'label' => __('Loan term', 'wpmc'), 'group' => 'wpmc_five',),


        array('wpmc_five_mhaai_initial', 'type' => $text, 'section_name' => 'wpmc-settings-five', 'label' => __('Annual Income', 'wpmc'), 'group' => 'wpmc_five', 'placeholder' => __('Default: 100,000', 'wpmc'),),
        array('wpmc_five_mhamd_initial', 'type' => $text, 'section_name' => 'wpmc-settings-five', 'label' => __('Monthly debts', 'wpmc'), 'group' => 'wpmc_five', 'placeholder' => __('Default: 2,000', 'wpmc'),),
        array('wpmc_five_mhaeapt_initial', 'type' => $text, 'section_name' => 'wpmc-settings-five', 'label' => __('Estimated annual property taxes', 'wpmc'), 'group' => 'wpmc_five', 'placeholder' => __('Default: 1,500', 'wpmc'),),
        array('wpmc_five_mhaeahi_initial', 'type' => $text, 'section_name' => 'wpmc-settings-five', 'label' => __('Estimated annual home insurance', 'wpmc'), 'group' => 'wpmc_five', 'placeholder' => __('Default: 1,000', 'wpmc'),),
        array('wpmc_five_mhadp_initial', 'type' => $text, 'section_name' => 'wpmc-settings-five', 'label' => __('Down payment', 'wpmc'), 'group' => 'wpmc_five', 'placeholder' => __('Default: 10,000', 'wpmc'),),
        array('wpmc_five_mhair_initial', 'type' => $text, 'section_name' => 'wpmc-settings-five', 'label' => __('Interest rate', 'wpmc'), 'group' => 'wpmc_five', 'placeholder' => __('Default: 4', 'wpmc'),),
    );


    foreach ($allfields as $key => $val) {
        add_settings_field($val[0], $val['label'], $val['type'], $val['section_name'], $val['group'], $val);
        register_setting($val['group'], $val[0]);
    }

    // Calculator Six RC Options
    // Set Section or Option-Group
    add_settings_section('wpmc_six', '', 'wpmc_six_display_shortcode', 'wpmc-settings-six');

    if (is_multisite() && !is_network_admin()) {
        add_settings_field('wpmc_six_use_network_settings', __('Use Network Settings', 'wpmc'), 'mcwp_checkbox', 'wpmc-settings-six', 'wpmc_six', array('wpmc_six_use_network_settings', 'wpmc_six'));
        register_setting('wpmc_six', 'wpmc_six_use_network_settings');
    }

    $allfields = array(
        array(
            'wpmc_six_email', 'type' => $text, 'section_name' => 'wpmc-settings-six', 'label' => __('Email Address', 'wpmc'), 'group' => 'wpmc_six',
            'placeholder' => __('Email address your leads will be sent to...', 'wpmc'),
        ),

        array('wpmc_six_disclaimer', 'type' => $textarea, 'section_name' => 'wpmc-settings-six', 'label' => __('Disclaimer', 'wpmc'), 'group' => 'wpmc_six', 'placeholder' => __('If you would like to display a disclaimer under the calculator\'s values, place that here...', 'wpmc'),),


        array('wpmc_six_first_heading', 'type' => $text, 'section_name' => 'wpmc-settings-six', 'label' => __('First Heading', 'wpmc'), 'group' => 'wpmc_six',),
        array('wpmc_six_second_heading', 'type' => $text, 'section_name' => 'wpmc-settings-six', 'label' => __('Second Heading', 'wpmc'), 'group' => 'wpmc_six',),
        array('wpmc_six_la', 'type' => $text, 'section_name' => 'wpmc-settings-six', 'label' => __('Original loan amount', 'wpmc'), 'group' => 'wpmc_six',),
        array('wpmc_six_ir', 'type' => $text, 'section_name' => 'wpmc-settings-six', 'label' => __('Interest rate', 'wpmc'), 'group' => 'wpmc_six',),
        array('wpmc_six_ct', 'type' => $text, 'section_name' => 'wpmc-settings-six', 'label' => __('Current term', 'wpmc'), 'group' => 'wpmc_six',),
        array('wpmc_six_oy', 'type' => $text, 'section_name' => 'wpmc-settings-six', 'label' => __('Origination year', 'wpmc'), 'group' => 'wpmc_six',),
        array('wpmc_six_nla', 'type' => $text, 'section_name' => 'wpmc-settings-six', 'label' => __('New loan amount', 'wpmc'), 'group' => 'wpmc_six',),
        array('wpmc_six_nir', 'type' => $text, 'section_name' => 'wpmc-settings-six', 'label' => __('New interest rate', 'wpmc'), 'group' => 'wpmc_six',),
        array('wpmc_six_nlt', 'type' => $text, 'section_name' => 'wpmc-settings-six', 'label' => __('New loan term', 'wpmc'), 'group' => 'wpmc_six',),
        array('wpmc_six_nrf', 'type' => $text, 'section_name' => 'wpmc-settings-six', 'label' => __('New refinance fees', 'wpmc'), 'group' => 'wpmc_six',),

        array('wpmc_six_la_initial', 'type' => $text, 'section_name' => 'wpmc-settings-six', 'label' => __('Original loan amount', 'wpmc'), 'group' => 'wpmc_six', 'placeholder' => __('Default: 300,000', 'wpmc'),),
        array('wpmc_six_ir_initial', 'type' => $text, 'section_name' => 'wpmc-settings-six', 'label' => __('Interest rate', 'wpmc'), 'group' => 'wpmc_six', 'placeholder' => __('Default: 5', 'wpmc'),),
        array('wpmc_six_ct_initial', 'type' => $text, 'section_name' => 'wpmc-settings-six', 'label' => __('Current term', 'wpmc'), 'group' => 'wpmc_six', 'placeholder' => __('Default: 360', 'wpmc'),),
        array('wpmc_six_oy_initial', 'type' => $text, 'section_name' => 'wpmc-settings-six', 'label' => __('Origination year', 'wpmc'), 'group' => 'wpmc_six', 'placeholder' => __('Default: 2020', 'wpmc'),),
        array('wpmc_six_nla_initial', 'type' => $text, 'section_name' => 'wpmc-settings-six', 'label' => __('New loan amount', 'wpmc'), 'group' => 'wpmc_six', 'placeholder' => __('Default: 250,000', 'wpmc'),),
        array('wpmc_six_nir_initial', 'type' => $text, 'section_name' => 'wpmc-settings-six', 'label' => __('New interest rate', 'wpmc'), 'group' => 'wpmc_six', 'placeholder' => __('Default: 4', 'wpmc'),),
        array('wpmc_six_nlt_initial', 'type' => $text, 'section_name' => 'wpmc-settings-six', 'label' => __('New loan term', 'wpmc'), 'group' => 'wpmc_six', 'placeholder' => __('Default: 360', 'wpmc'),),
        array('wpmc_six_nrf_initial', 'type' => $text, 'section_name' => 'wpmc-settings-six', 'label' => __('New refinance fees', 'wpmc'), 'group' => 'wpmc_six', 'placeholder' => __('Default: 1,000', 'wpmc'),),
    );


    foreach ($allfields as $key => $val) {
        add_settings_field($val[0], $val['label'], $val['type'], $val['section_name'], $val['group'], $val);
        register_setting($val['group'], $val[0]);
    }
}
function wpmc_mail_display_shortcode()
{
    /*echo '<p style="background: #fff; border-left: 4px solid #008ec2; padding: 5px 10px;">Use this shortcode for administration email: <strong>[email]</strong></p>';
        */
    echo '';
}
function copyShortText($text)
{
    $copyT = '<p style="background: #fff; border-left: 4px solid #008ec2; padding: 5px 10px;">';
    $copyT .= __('Copy this <strong>shortcode</strong> and paste it into your <strong>post, page, or text widget</strong> content: ', 'wpmc');
    $copyT .= '<strong>[mcwp type="' . $text . '"]</strong></p>';
    echo $copyT;
}
// Calculator One Options
function wpmc_one_display_shortcode()
{
    copyShortText('cv');
}
// Calculator Two Options
function wpmc_two_display_shortcode()
{
    copyShortText('fha');
}
// Calculator Three Options
function wpmc_three_display_shortcode()
{
    copyShortText('va');
}
// Calculator Five Options
function wpmc_five_display_shortcode()
{
    copyShortText('mha');
}
// Calculator Six Options
function wpmc_six_display_shortcode()
{
    copyShortText('rc');
}

function mcwp_checkbox($args)
{
    $options = get_wpmc_option($args[0]);
    $val = ($options == '0') ? '0' : '1';
    $main_val = ($val == '0') ? '0' : '1';
    $checked = ($val === '0') ? 'checked' : ''; ?>

    <input type="checkbox" name="<?php echo esc_attr($args[0]); ?>" class="<?php echo esc_attr($args[1]); ?>" value="<?php echo esc_attr($main_val); ?>" size="64" <?php echo $checked; ?> />

<?php
}

function display_text_element($args)
{
    //var_dump($args);
    $options = get_wpmc_option($args[0]);

    $placeholder = empty($args['placeholder']) ? $args['label'] : $args['placeholder']; ?>

    <input type="text" name="<?php echo esc_attr($args[0]); ?>" id="<?php echo esc_attr($args[0]); ?>" class="<?php echo esc_attr($args['group']); ?>" placeholder="<?php echo esc_attr($placeholder); ?>" value="<?php echo isset($options) ? esc_attr($options) : ''; ?>" size="64" />
<?php
}


function mcwp_dropdown($args)
{
    $options = get_wpmc_option($args[0]); ?>
    <select name="<?php echo esc_attr($args[0]); ?>" class="<?php echo esc_attr($args['group']); ?>">
        <option value="no" <?php echo $options == 'no' ? 'selected' : ''; ?>><?php echo __('No', 'wpmc'); ?></option>
        <option value="yes" <?php echo $options == 'yes' ? 'selected' : ''; ?>><?php echo __('Yes', 'wpmc'); ?></option>
    </select>

<?php
}
function mcwp_textarea($args)
{
    $options = get_wpmc_option($args[0]); ?>
    <textarea name="<?php echo esc_attr($args[0]); ?>" class="<?php echo esc_attr($args['group']); ?>" placeholder="<?php echo esc_attr($args['placeholder']); ?>" rows="5" cols="65" size="64"><?php echo isset($options) ? esc_textarea($options) : ''; ?></textarea>
<?php
}

function mcwp_sanitize_recaptcha_version($value)
{
    return ($value === 'v3') ? 'v3' : 'v2';
}

function mcwp_recaptcha_version_select($args)
{
    $options = get_wpmc_option($args[0]);
    $choices = array(
        'v2' => __('v2 Invisible', 'wpmc'),
        'v3' => __('v3', 'wpmc'),
    ); ?>
    <select name="<?php echo esc_attr($args[0]); ?>" class="<?php echo esc_attr($args['group']); ?>">
        <?php foreach ($choices as $key => $label) { ?>
            <option value="<?php echo esc_attr($key); ?>" <?php selected($options === 'v3' ? 'v3' : 'v2', $key); ?>><?php echo esc_html($label); ?></option>
        <?php } ?>
    </select>
    <p class="description"><?php esc_html_e('Choose the type that matches the keys you created at google.com/recaptcha/admin. Leave both keys blank to turn reCAPTCHA off (a honeypot and rate limits still apply).', 'wpmc'); ?></p>
<?php
}


function mcwp_currency($args)
{
    $options = get_wpmc_option($args[0]);

    $currencies = array(
        '$' => __('United States Dollar', 'wpmc'),
        '£' => __('United Kingdom Pounds', 'wpmc'),
        '€' => __('Euro', 'wpmc'),
        'A$' => __('Australia Dollar', 'wpmc'),
    ); ?>
    <select name="<?php echo esc_attr($args[0]); ?>" class="<?php echo esc_attr($args['group']); ?>">
        <?php
        foreach ($currencies as $key => $val) {
            if ($key ===  $options) {
                echo '<option value="' . esc_attr($key) . '" selected="selected">' . esc_html($val) . '</option>';
            } else {
                echo '<option value="' . esc_attr($key) . '">' . esc_html($val) . '</option>';
            }
        } ?>
    </select>
<?php
}

//color — accepts hex values, text, or shortcodes; color picker updates the field but doesn't restrict input
function color_input($args)
{
    $options = get_wpmc_option($args[0]);
    $value = !empty($options) ? $options : '#1a56db';
    $is_hex = preg_match('/^#[0-9a-fA-F]{3,6}$/', $value);
    ?>
    <input type="text" name="<?php echo esc_attr($args[0]); ?>" class="<?php echo esc_attr($args['group']); ?>" placeholder="<?php echo esc_attr($args['placeholder']); ?>" value="<?php echo esc_attr($value); ?>" id="<?php echo esc_attr($args[0]); ?>" size="64" />
    <input type="text" class="color-picker" id="<?php echo esc_attr($args[0]); ?>_picker" value="<?php echo $is_hex ? esc_attr($value) : '#1a56db'; ?>" data-target="<?php echo esc_attr($args[0]); ?>" size="10" />
    <p class="description"><?php _e('Enter a hex color, text value, or shortcode (e.g. [brand_color]). Use the color picker or type directly.', 'wpmc'); ?></p>
    <script>
    jQuery(document).ready(function($){
        $('#<?php echo esc_attr($args[0]); ?>_picker').wpColorPicker({
            change: function(event, ui) {
                $('#<?php echo esc_attr($args[0]); ?>').val(ui.color.toString());
            }
        });
    });
    </script>
<?php
}
