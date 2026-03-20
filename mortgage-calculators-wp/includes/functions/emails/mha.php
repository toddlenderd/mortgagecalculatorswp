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
// Dynamically Create the Body
$msg_body = $option_func('wpmc_five_msg_bdy');
$body_part_dynamic = body_dynamic($msg_body, $_REQUEST);

$body_part_static = __('You may be able to afford a loan with a', 'wpmc') . " <strong>$mha_term " . __('year term', 'wpmc') . "</strong> " . __('in the amount of', 'wpmc') . " <strong>$curr_symbol$cal_result_home_afford</strong> " . __('at', 'wpmc') . " <strong>$mha_rate%</strong> " . __('that has a total monthly payment of', 'wpmc') . " <strong>$curr_symbol$mha_monthly_payment</strong>" . __('. This is based on your annual income of', 'wpmc') . " <strong>$curr_symbol$mha_income</strong> " . __('and monthly debts of', 'wpmc') . " <strong>$curr_symbol$mha_debts</strong>.";

$body .= "<div style='font-family:Arial;font-size: 13px;padding:0 10px;'>
    <p style='line-height: 20px; max-width: 500px'>$wpmc_mail_message</p>
    " . (!empty($body_part_dynamic) ? $body_part_dynamic : $body_part_static) . "
  </div>";
$cc_subject = 'New Affordability Calculation by ' . $to;
$cc_body = "<div style='font-family:Arial;font-size: 13px;padding:0 10px;'><p><a href='mailto:$to'>" . __('Click Here', 'wpmc') . "</a> " . __('to follow up with', 'wpmc') . " $to. " . __('They requested a calculation and a copy of the email they received is below for reference', 'wpmc') . ":</p><em>" . (!empty($body_part_dynamic) ? $body_part_dynamic : $body_part_static) . "</em></div>";
