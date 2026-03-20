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
// Dynamically Create the Body
$msg_body = $option_func('wpmc_six_msg_bdy');
$body_part_dynamic = body_dynamic($msg_body, $_REQUEST);

$forPara = __('Principal & Interest', 'wpmc');

$body_part_static = __('Refinancing could save you', 'wpmc') . " <strong>$curr_symbol$cal_result_home_afford</strong> " . __('per month and', 'wpmc') . " <strong>$curr_symbol$rc_lifetime_value</strong> " . __('over the life of the loan. This is based on a new loan amount of', 'wpmc') . " <strong>$curr_symbol$rc_new_loan_amount</strong> " . __('at', 'wpmc') . " <strong>$rc_new_interest_rate%</strong> " . __('for', 'wpmc') . " <strong>$rc_new_loan_term " . __('months', 'wpmc') . "</strong>.";

$body .= "<div style='font-family:Arial;font-size: 13px;padding:0 10px;'>
    <p style='line-height: 20px; max-width: 500px'>$wpmc_mail_message</p>
    " . (!empty($body_part_dynamic) ? $body_part_dynamic : $body_part_static) . "
  </div>";
$cc_subject = __('New Refinance Calculation by ', 'wpmc') . $to;
$cc_body = "<div style='font-family:Arial;font-size: 13px;padding:0 10px;'><p><a href='mailto:$to'>" . __('Click Here', 'wpmc') . "</a> " . __('to follow up with', 'wpmc') . " $to. " . __('They requested a calculation and a copy of the email they received is below for reference', 'wpmc') . ":</p><em>" . (!empty($body_part_dynamic) ? $body_part_dynamic : $body_part_static) . "</em></div>";
