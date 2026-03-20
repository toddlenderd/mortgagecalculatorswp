<?php
$option_func = (use_network_settings('wpmc_six_use_network_settings') === 'yes') ? 'get_site_option' : 'get_option';

$wpmc_email = $option_func('wpmc_six_email');
$admin_email = $option_func('admin_email');
$wpmc_six_email = (!empty($wpmc_email) && $wpmc_email==='[email]') ? $admin_email : ((!empty($wpmc_email) && $wpmc_email !=='[email]') ? $wpmc_email : $admin_email);

$calTemplate2 .='<div>
    <div class="mcalc-main">
        <div class="mcalc-half">
            <label for="rc_annual_income">'.calc_fields('rc', 'wpmc_six_la', __('Current Loan Amount', 'wpmc')).'</label>
            <i>' . $curr_symbol . '</i>
            <input name="original_loan_amount" class="mha_input_data" autocomplete="off" currency="true" type="text"  id="rc_original_loan_amount" value="'.calc_fields('rc', 'wpmc_six_la_initial', '300,000').'" />
        </div>
        <div class="mcalc-half mha_main_wrapper">
            <label for="rc_interest_rate">'.calc_fields('rc', 'wpmc_six_ir', __('Interest Rate', 'wpmc')).'</label>
            <input name="interest_rate" autocomplete="off" currency="true" type="text"  id="rc_interest_rate" value="'.calc_fields('rc', 'wpmc_six_ir_initial', '5').'" />
            <i>%</i>
        </div>
        <div class="mcalc-half mha_main_wrapper">
            <label for="rc_current_term">'.calc_fields('rc', 'wpmc_six_ct', __('Current Term', 'wpmc')).'</label>
            <input name="current_term" autocomplete="off" currency="true" type="text" id="rc_current_term" value="'.calc_fields('rc', 'wpmc_six_ct_initial', '360').'" />
            <i>'. __('months', 'wpmc').'</i>
        </div>
        <div class="mcalc-half">
            <label for="rc_origination_year">'.calc_fields('rc', 'wpmc_six_oy', __('Origination Year', 'wpmc')).'</label>
            <input name="origination_year" autocomplete="off" currency="true" type="text"  id="rc_origination_year" value="'.calc_fields('rc', 'wpmc_six_oy_initial', '2015').'" />
        </div>
        <div class="mcalc-half">
            <label for="rc_annual_income">'.calc_fields('rc', 'wpmc_six_nla', __('New Loan Amount', 'wpmc')).'</label>
            <i>' . $curr_symbol . '</i>
            <input name="new_loan_amount" class="mha_input_data" autocomplete="off" currency="true" type="text"  id="rc_new_loan_amount" value="'.calc_fields('rc', 'wpmc_six_nla_initial', '250,000').'" />
        </div>
        <div class="mcalc-half mha_main_wrapper">
            <label for="rc_new_interest_rate">'.calc_fields('rc', 'wpmc_six_nir', __('New Interest Rate', 'wpmc')).'</label>
            <input name="new_interest_rate" autocomplete="off" currency="true" type="text"  id="rc_new_interest_rate" value="'.calc_fields('rc', 'wpmc_six_nir_initial', '4').'" />
            <i>%</i>
        </div>
        <div class="mcalc-half mha_main_wrapper">
            <label for="rc_new_loan_term">'.calc_fields('rc', 'wpmc_six_nlt', __('New Loan Term', 'wpmc')).'</label>
            <input name="new_loan_term" autocomplete="off" currency="true" type="text" name="rc_new_loan_term" id="rc_new_loan_term" value="'.calc_fields('rc', 'wpmc_six_nlt_initial', '360').'" />
            <i>'. __('months', 'wpmc').'</i>
        </div>
        <div class="mcalc-half">
            <label for="rc_new_refinance_fees">'.calc_fields('rc', 'wpmc_six_nrf', __('Refinance Fees', 'wpmc')).'</label>
            <i>' . $curr_symbol . '</i>
            <input name="new_refinance_fees" class="mha_input_data" autocomplete="off" currency="true" type="text"  id="rc_new_refinance_fees" value="'.calc_fields('rc', 'wpmc_six_nrf_initial', '1,000').'" />
        </div>
        <div class="mcalc-full mcwp-results">
            <label for="rc_cal3_email">'.__('Want a Copy of the Results?', 'wpmc').'</label>
            <input type="email" id="rc_cal6_email" placeholder="'.__('Enter your email address', 'wpmc').'" value="" name="email" />
            <input type="button" id="rc_wpmc6_send_mail" class="mcwp-submit bg rc_submit mcalc-color" value="'. __('Send Results!', 'wpmc').'">
        </div>
    </div>
    <div class="mcalc-values">
        <div class="mcalc-results">
            <h4>'.__('Refinancing could save you', 'wpmc').'</h4>
            <h2 class="mcalc-value">'.$curr_symbol.'<span id="rc_afford_house_div_span"></span><em>/'.__('month', 'wpmc').'</em></h2>
            <p class="mcwp-pi">'.__('New monthly payment', 'wpmc').' <strong>'.$curr_symbol.'<span id="rc_emmp_div_span"></span></strong></p>
            <p class="mcwp-pi">'.__('Refinance fees', 'wpmc').' <strong>'.$curr_symbol.'<span id="rc_pi_div_span"></span></strong></p>
            <p class="mcwp-pi">'.__('Lifetime Savings ', 'wpmc').' <strong>'.$curr_symbol.'<span id="rc_lifetime_div_span"></span></strong></p>
            <small>'.calc_fields('rc', 'wpmc_six_disclaimer', '').'</small>
        </div>
    </div>
</div>';
$calTemplate2 .= '
  <input type="hidden" name="cal_result_home_afford" class="rc_afford_house_div_span" />
  <input type="hidden" name="new_monthly_payment" class="rc_emmp_div_span" />
  <input type="hidden" name="refinance_fees" class="rc_pi_div_span" />
  <input type="hidden" name="lifetime_value" class="rc_lifetime_div_span" />
  <input type="hidden" name="type" value="rc"/>
';
if ($status !== false && $status == 'valid' && $license !== false) {
    $calculator_layout = $calTemplate2;
} else {
    $calculator_layout = '<p>&nbsp;</p><p style="color: red; border: 2px solid red; padding: 40px; margin: 100px auto; text-align: center; width: 50%;"><strong style="color: red;">Warning: You are NOT subscribed to view this calculator</strong></p>';
}
