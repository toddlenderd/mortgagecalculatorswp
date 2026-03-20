<?php
$calculation_result = sanitize_text_field($_POST['calculation_result']);
$principal_and_interest = sanitize_text_field($_POST['principal_and_interest']);
$price = sanitize_text_field($_POST['purchase_price']);
$term = sanitize_text_field($_POST['mortgage_term']);
$interest_rate = sanitize_text_field($_POST['interest_rate']);
$monthly_pmi = sanitize_text_field($_POST['monthly_pmi']);
$down_payment = sanitize_text_field($_POST['down_payment']);
$monthly_taxes = sanitize_text_field($_POST['monthly_taxes']); //round(sanitize_text_field($_POST['monthly_taxes']),2);
$monthly_insurance = round(sanitize_text_field($_POST['monthly_insurance']), 2);
$monthly_mortgage_insurance = sanitize_text_field($_POST['monthly_mortgage_insurance']);
$monthly_hoa = sanitize_text_field($_POST['monthly_hoa']);
$option_func = (use_network_settings('wpmc_one_use_network_settings') === 'yes') ? 'get_site_option' : 'get_option';
$wpmc_admin = $option_func('wpmc_one_email');
$site_admin = checksettings('admin_email');

// Dynamically Create the Body
$msg_body = $option_func('wpmc_one_msg_bdy');
$body_part_dynamic = body_dynamic($msg_body, $_REQUEST);
$subject = __('Your Conventional Mortgage Calculation', 'wpmc');

$body_part_static = __('Based on a purchase price of', 'wpmc') . " <strong>$curr_symbol$price</strong>, " . __('and a down payment of', 'wpmc') . " <strong>$curr_symbol$down_payment</strong>, " . __('your new', 'wpmc') . " <strong>$term " . __('year', 'wpmc') . "</strong> " . __('loan with an interest rate of', 'wpmc') . " <strong>$interest_rate%</strong> " . __('will have a payment of', 'wpmc') . " <strong>$curr_symbol$calculation_result</strong>. " . __('This includes monthly taxes of', 'wpmc') . " <strong>$curr_symbol$monthly_taxes</strong>, " . __('monthly insurance of', 'wpmc') . " <strong>$curr_symbol$monthly_insurance</strong>, " . __('monthly PMI of', 'wpmc') . " <strong>$curr_symbol$monthly_pmi</strong> " . __('and monthly hoa of', 'wpmc') . " <strong>$curr_symbol$monthly_hoa</strong>.";

$body .= "<div style='font-family:Arial;font-size: 13px;padding:0 10px;'>
    <p style='line-height: 20px; max-width: 500px'>$wpmc_mail_message</p>
    " . (!empty($body_part_dynamic) ? $body_part_dynamic : $body_part_static) . "
  </div>";

$cc_subject = __('New Conventional Calculation by ', 'wpmc') . $to;
$cc_body = "<div style='font-family:Arial;font-size: 13px;padding:0 10px;'><p><a href='mailto:$to'>" . __('Click Here', 'wpmc') . "</a> " . __('to follow up with', 'wpmc') . " $to. " . __('They requested a calculation and a copy of the email they received is below for reference', 'wpmc') . ":</p><em>" . (!empty($body_part_dynamic) ? $body_part_dynamic : $body_part_static) . "</em></div>";
