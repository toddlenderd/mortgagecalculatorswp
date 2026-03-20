<?php
$calculation_result = sanitize_text_field($_POST['calculation_result']);
$principal_and_interest = sanitize_text_field($_POST['principal_and_interest']);
$monthly_taxes = round(sanitize_text_field($_POST['monthly_taxes']), 2);
$monthly_insurance = round(sanitize_text_field($_POST['monthly_insurance']), 2);
$term = sanitize_text_field($_POST['mortgage_term']);
$funding_fee = sanitize_text_field($_POST['funding_fee']);
$rate = sanitize_text_field($_POST['interest_rate']);
$monthly_hoa = sanitize_text_field($_POST['monthly_hoa']);
$purchase_price = sanitize_text_field($_POST['purchase_price']);
$va_funding_fee_2 = sanitize_text_field($_POST['va_funding_fee_2']);
$amount_financed = sanitize_text_field($_POST['amount_financed']);
$option_func = (use_network_settings('wpmc_three_use_network_settings') === 'yes') ? 'get_site_option' : 'get_option';
$wpmc_admin = $option_func('wpmc_three_email');
$site_admin = checksettings('admin_email');

$subject = __('Your VA Mortgage Calculation', 'wpmc');

$rows = array(
    array('label' => __('Purchase Price', 'wpmc'), 'value' => $curr_symbol . $purchase_price),
    array('label' => __('Amount Financed', 'wpmc'), 'value' => $curr_symbol . $amount_financed),
    array('label' => __('VA Funding Fee', 'wpmc'), 'value' => $curr_symbol . $funding_fee),
    array('label' => __('Interest Rate', 'wpmc'), 'value' => $rate . '%'),
    array('label' => __('Principal & Interest', 'wpmc'), 'value' => $curr_symbol . $principal_and_interest),
    array('label' => __('Monthly Taxes', 'wpmc'), 'value' => $curr_symbol . $monthly_taxes),
    array('label' => __('Monthly Insurance', 'wpmc'), 'value' => $curr_symbol . $monthly_insurance),
    array('label' => __('Monthly HOA', 'wpmc'), 'value' => $curr_symbol . $monthly_hoa),
);

$body = mcwp_email_template(array(
    'calc_type'   => __('VA Loan', 'wpmc'),
    'subtitle'    => $term . ' ' . __('Year Fixed', 'wpmc'),
    'total'       => $curr_symbol . $calculation_result,
    'message'     => $wpmc_mail_message,
    'rows'        => $rows,
    'curr_symbol' => $curr_symbol,
    'disclaimer'  => $option_func('wpmc_three_disclaimer'),
));

$cc_subject = __('New VA Calculation by ', 'wpmc') . $to;
$cc_body = mcwp_cc_email_template($to, __('VA', 'wpmc'), $body);
