<?php
$calculation_result = sanitize_text_field($_POST['calculation_result']);
$principal_and_interest = sanitize_text_field($_POST['principal_and_interest']);
$price = sanitize_text_field($_POST['purchase_price']);
$term = sanitize_text_field($_POST['mortgage_term']);
$interest_rate = sanitize_text_field($_POST['interest_rate']);
$monthly_pmi = sanitize_text_field($_POST['monthly_pmi']);
$down_payment = sanitize_text_field($_POST['down_payment']);
$monthly_taxes = sanitize_text_field($_POST['monthly_taxes']);
$monthly_insurance = round(sanitize_text_field($_POST['monthly_insurance']), 2);
$monthly_mortgage_insurance = sanitize_text_field($_POST['monthly_mortgage_insurance']);
$monthly_hoa = sanitize_text_field($_POST['monthly_hoa']);
$option_func = (use_network_settings('wpmc_one_use_network_settings') === 'yes') ? 'get_site_option' : 'get_option';
$wpmc_admin = $option_func('wpmc_one_email');
$site_admin = checksettings('admin_email');

$subject = __('Your Conventional Mortgage Calculation', 'wpmc');

// Build the breakdown rows for the email template
$rows = array(
    array('label' => __('Purchase Price', 'wpmc'), 'value' => $curr_symbol . $price),
    array('label' => __('Down Payment', 'wpmc'), 'value' => $curr_symbol . $down_payment),
    array('label' => __('Interest Rate', 'wpmc'), 'value' => $interest_rate . '%'),
    array('label' => __('Principal & Interest', 'wpmc'), 'value' => $curr_symbol . $principal_and_interest),
    array('label' => __('Monthly Taxes', 'wpmc'), 'value' => $curr_symbol . $monthly_taxes),
    array('label' => __('Monthly Insurance', 'wpmc'), 'value' => $curr_symbol . $monthly_insurance),
    array('label' => __('Monthly PMI', 'wpmc'), 'value' => $curr_symbol . $monthly_pmi),
    array('label' => __('Monthly HOA', 'wpmc'), 'value' => $curr_symbol . $monthly_hoa),
);

$body = mcwp_email_template(array(
    'calc_type'   => __('Conventional Loan', 'wpmc'),
    'subtitle'    => $term . ' ' . __('Year Fixed', 'wpmc'),
    'total'       => $curr_symbol . $calculation_result,
    'message'     => $wpmc_mail_message,
    'rows'        => $rows,
    'curr_symbol' => $curr_symbol,
    'disclaimer'  => $option_func('wpmc_one_disclaimer'),
));

$cc_subject = __('New Conventional Calculation by ', 'wpmc') . $to;
$cc_body = mcwp_cc_email_template($to, __('Conventional', 'wpmc'), $body);
