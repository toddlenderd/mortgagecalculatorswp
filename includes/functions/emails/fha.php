<?php
$calculation_result = mcwp_post_text('calculation_result');
$purchase_price = mcwp_post_text('purchase_price');
$down_payment = mcwp_post_text('down_payment');
$term = mcwp_post_text('mortgage_term');
$rate = mcwp_post_text('interest_rate');
$principal_and_interest = mcwp_post_text('principal_and_interest');
$monthly_taxes = mcwp_post_amount('monthly_taxes');
$monthly_insurance = mcwp_post_amount('monthly_insurance');
$monthly_mortgage_insurance = mcwp_post_text('monthly_mortgage_insurance');
$monthly_hoa = mcwp_post_text('monthly_hoa');
$option_func = (use_network_settings('wpmc_two_use_network_settings') === 'yes') ? 'get_site_option' : 'get_option';
$wpmc_admin = $option_func('wpmc_two_email');

$subject = __('Your FHA Mortgage Calculation', 'wpmc');

$rows = array(
    array('label' => __('Purchase Price', 'wpmc'), 'value' => $curr_symbol . $purchase_price),
    array('label' => __('Down Payment', 'wpmc'), 'value' => $curr_symbol . $down_payment),
    array('label' => __('Interest Rate', 'wpmc'), 'value' => $rate . '%'),
    array('label' => __('Principal & Interest', 'wpmc'), 'value' => $curr_symbol . $principal_and_interest),
    array('label' => __('Monthly Taxes', 'wpmc'), 'value' => $curr_symbol . $monthly_taxes),
    array('label' => __('Monthly Insurance', 'wpmc'), 'value' => $curr_symbol . $monthly_insurance),
    array('label' => __('Monthly Mortgage Insurance', 'wpmc'), 'value' => $curr_symbol . $monthly_mortgage_insurance),
    array('label' => __('Monthly HOA', 'wpmc'), 'value' => $curr_symbol . $monthly_hoa),
);

$email_args = array(
    'calc_type'   => __('FHA Loan', 'wpmc'),
    'subtitle'    => $term . ' ' . __('Year Fixed', 'wpmc'),
    'total'       => $curr_symbol . $calculation_result,
    'message'     => $wpmc_mail_message,
    'rows'        => $rows,
    'curr_symbol' => $curr_symbol,
    'disclaimer'  => $option_func('wpmc_two_disclaimer'),
);
$body = mcwp_email_template($email_args);

$cc_subject = __('New FHA Calculation by ', 'wpmc') . $to;
$cc_body = mcwp_cc_email_template($to, __('FHA', 'wpmc'), $body);
