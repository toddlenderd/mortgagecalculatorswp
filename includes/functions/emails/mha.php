<?php
$cal_result_home_afford = sanitize_text_field($_POST['cal_result_home_afford']);
$mha_monthly_payment = sanitize_text_field($_POST['monthly_payment']);
$mha_principal_interest = sanitize_text_field($_POST['principal_interest']);
$mha_tax_value = sanitize_text_field($_POST['tax_value']);
$mha_insurance_value = sanitize_text_field($_POST['insurance_value']);
$mha_term = sanitize_text_field($_POST['mortgage_term']);
$mha_rate = sanitize_text_field($_POST['interest_rate']);
$mha_income = sanitize_text_field($_POST['annual_income']);
$mha_debts = sanitize_text_field($_POST['monthly_debts']);
$option_func = (use_network_settings('wpmc_five_use_network_settings') === 'yes') ? 'get_site_option' : 'get_option';
$wpmc_admin = $option_func('wpmc_five_email');
$site_admin = checksettings('admin_email');

$subject = __('Your Affordability Calculation', 'wpmc');

$rows = array(
    array('label' => __('Estimated Monthly Payment', 'wpmc'), 'value' => $curr_symbol . $mha_monthly_payment),
    array('label' => __('Annual Income', 'wpmc'), 'value' => $curr_symbol . $mha_income),
    array('label' => __('Monthly Debts', 'wpmc'), 'value' => $curr_symbol . $mha_debts),
    array('label' => __('Interest Rate', 'wpmc'), 'value' => $mha_rate . '%'),
    array('label' => __('Principal & Interest', 'wpmc'), 'value' => $curr_symbol . $mha_principal_interest),
    array('label' => __('Monthly Taxes', 'wpmc'), 'value' => $curr_symbol . $mha_tax_value),
    array('label' => __('Monthly Insurance', 'wpmc'), 'value' => $curr_symbol . $mha_insurance_value),
);

$body = mcwp_email_template(array(
    'calc_type'   => __('Affordability', 'wpmc'),
    'subtitle'    => $mha_term . ' ' . __('Year Fixed', 'wpmc'),
    'total'       => $curr_symbol . $cal_result_home_afford,
    'total_label' => __('You Can Afford Up To', 'wpmc'),
    'message'     => $wpmc_mail_message,
    'rows'        => $rows,
    'curr_symbol' => $curr_symbol,
    'disclaimer'  => $option_func('wpmc_five_disclaimer'),
));

$cc_subject = __('New Affordability Calculation by ', 'wpmc') . $to;
$cc_body = mcwp_cc_email_template($to, __('Affordability', 'wpmc'), $body);
