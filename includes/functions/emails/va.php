<?php
$calculation_result = mcwp_post_text('calculation_result');
$principal_and_interest = mcwp_post_text('principal_and_interest');
$monthly_taxes = mcwp_post_amount('monthly_taxes');
$monthly_insurance = mcwp_post_amount('monthly_insurance');
$term = mcwp_post_text('mortgage_term');
$funding_fee = mcwp_post_text('funding_fee');
$rate = mcwp_post_text('interest_rate');
$monthly_hoa = mcwp_post_text('monthly_hoa');
$purchase_price = mcwp_post_text('purchase_price');
$amount_financed = mcwp_post_text('amount_financed');
$option_func = (use_network_settings('wpmc_three_use_network_settings') === 'yes') ? 'get_site_option' : 'get_option';
$wpmc_admin = $option_func('wpmc_three_email');

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

$email_args = array(
    'calc_type'   => __('VA Loan', 'wpmc'),
    'subtitle'    => $term . ' ' . __('Year Fixed', 'wpmc'),
    'total'       => $curr_symbol . $calculation_result,
    'message'     => $wpmc_mail_message,
    'rows'        => $rows,
    'curr_symbol' => $curr_symbol,
    'disclaimer'  => $option_func('wpmc_three_disclaimer'),
);
$body = mcwp_email_template($email_args);

$cc_subject = __('New VA Calculation by ', 'wpmc') . $to;
$cc_body = mcwp_cc_email_template($to, __('VA', 'wpmc'), $body);
