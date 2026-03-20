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
$subject =  __('Your VA Mortgage Calculation', 'wpmc');
// Dynamically Create the Body
$msg_body = $option_func('wpmc_three_msg_bdy');
$body_part_dynamic = body_dynamic($msg_body, $_REQUEST);


$body_part_static = __('Based on a purchase price of', 'wpmc') . " <strong>$curr_symbol$purchase_price</strong>, " . __('your new', 'wpmc') . " <strong>$term " . __('year', 'wpmc') . "</strong> " . __('VA loan in the amount of', 'wpmc') . " <strong>$curr_symbol$amount_financed</strong>, " . __('which includes a funding fee of', 'wpmc') . " <strong>$curr_symbol$funding_fee</strong>, " . __(' with an interest rate of', 'wpmc') . " <strong>$rate%</strong> " . __('will have a payment of', 'wpmc') . " <strong>$curr_symbol$calculation_result</strong>. " . __('This includes monthly taxes of', 'wpmc') . " <strong>$curr_symbol$monthly_taxes</strong>, " . __('monthly insurance of', 'wpmc') . " <strong>$curr_symbol$monthly_insurance</strong>, " . __('and monthly hoa of', 'wpmc') . " <strong>$curr_symbol$monthly_hoa</strong>.";

$body .= "<div style='font-family:Arial;font-size: 13px;padding:0 10px;'>
    <p style='line-height: 20px; max-width: 500px'>$wpmc_mail_message</p>
    " . (!empty($body_part_dynamic) ? $body_part_dynamic : $body_part_static) . "
  </div>";
$cc_subject = __('New VA Calculation by ', 'wpmc') . $to;
$cc_body = "<div style='font-family:Arial;font-size: 13px;padding:0 10px;'><p><a href='mailto:$to'>" . __('Click Here', 'wpmc') . "</a> " . __('to follow up with', 'wpmc') . " $to. " . __('They requested a calculation and a copy of the email they received is below for reference', 'wpmc') . ":</p><em>" . (!empty($body_part_dynamic) ? $body_part_dynamic : $body_part_static) . "</em></div>";
