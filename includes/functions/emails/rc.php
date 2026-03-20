<?php
$cal_result_home_afford = sanitize_text_field($_POST['cal_result_home_afford']);
$rc_lifetime_value = sanitize_text_field($_POST['lifetime_value']);
$rc_refinance_fees = sanitize_text_field($_POST['refinance_fees']);
$rc_monthly_payment = sanitize_text_field($_POST['new_monthly_payment']);
$rc_new_loan_amount = sanitize_text_field($_POST['new_loan_amount']);
$rc_new_interest_rate = sanitize_text_field($_POST['new_interest_rate']);
$rc_new_loan_term = sanitize_text_field($_POST['new_loan_term']);

$option_func = (use_network_settings('wpmc_six_use_network_settings') === 'yes') ? 'get_site_option' : 'get_option';
$wpmc_admin = $option_func('wpmc_six_email');
$site_admin = checksettings('admin_email');

$subject = __('Your Refinance Calculation', 'wpmc');

$rows = array(
    array('label' => __('Monthly Savings', 'wpmc'), 'value' => $curr_symbol . $cal_result_home_afford),
    array('label' => __('New Loan Amount', 'wpmc'), 'value' => $curr_symbol . $rc_new_loan_amount),
    array('label' => __('New Interest Rate', 'wpmc'), 'value' => $rc_new_interest_rate . '%'),
    array('label' => __('New Loan Term', 'wpmc'), 'value' => $rc_new_loan_term . ' ' . __('months', 'wpmc')),
    array('label' => __('Refinance Fees', 'wpmc'), 'value' => $curr_symbol . $rc_refinance_fees),
    array('label' => __('Lifetime Savings', 'wpmc'), 'value' => $curr_symbol . $rc_lifetime_value),
);

$body = mcwp_email_template(array(
    'calc_type'   => __('Refinance', 'wpmc'),
    'subtitle'    => __('New Rate', 'wpmc') . ': ' . $rc_new_interest_rate . '%',
    'total'       => $curr_symbol . $rc_monthly_payment,
    'message'     => $wpmc_mail_message,
    'rows'        => $rows,
    'curr_symbol' => $curr_symbol,
    'disclaimer'  => $option_func('wpmc_six_disclaimer'),
));

$cc_subject = __('New Refinance Calculation by ', 'wpmc') . $to;
$cc_body = mcwp_cc_email_template($to, __('Refinance', 'wpmc'), $body);
