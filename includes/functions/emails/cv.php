<?php
$calculation_result = mcwp_post_text('calculation_result');
$principal_and_interest = mcwp_post_text('principal_and_interest');
$price = mcwp_post_text('purchase_price');
$term = mcwp_post_text('mortgage_term');
$interest_rate = mcwp_post_text('interest_rate');
$monthly_pmi = mcwp_post_text('monthly_pmi');
$down_payment = mcwp_post_text('down_payment');
$monthly_taxes = mcwp_post_text('monthly_taxes');
$monthly_insurance = mcwp_post_amount('monthly_insurance');
$monthly_hoa = mcwp_post_text('monthly_hoa');
$option_func = (use_network_settings('wpmc_one_use_network_settings') === 'yes') ? 'get_site_option' : 'get_option';
$wpmc_admin = $option_func('wpmc_one_email');

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

$email_args = array(
    'calc_type'   => __('Conventional Loan', 'wpmc'),
    'subtitle'    => $term . ' ' . __('Year Fixed', 'wpmc'),
    'total'       => $curr_symbol . $calculation_result,
    'message'     => $wpmc_mail_message,
    'rows'        => $rows,
    'curr_symbol' => $curr_symbol,
    'disclaimer'  => $option_func('wpmc_one_disclaimer'),
);
$body = mcwp_email_template($email_args);

$cc_subject = __('New Conventional Calculation by ', 'wpmc') . $to;
$cc_body = mcwp_cc_email_template($to, __('Conventional', 'wpmc'), $body);
