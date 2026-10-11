<?php
$option_func = (use_network_settings('wpmc_five_use_network_settings') === 'yes') ? 'get_site_option' : 'get_option';
$wpmc_email = $option_func('wpmc_five_email');
$admin_email = $option_func('admin_email');
$wpmc_five_email = (!empty($wpmc_email) && $wpmc_email==='[email]') ? $admin_email : ((!empty($wpmc_email) && $wpmc_email !=='[email]') ? $wpmc_email : $admin_email);
$calTemplate2 .='<div class="mcalc-layout">
      <div class="mcalc-main">
        <div class="mcalc-half">
          <label for="mha_annual_income">'.calc_fields('mha', 'wpmc_five_ai', __('Annual Income', 'wpmc')).'</label>
          <i>' . $curr_symbol . '</i>
          <input name="annual_income" class="mha_input_data" autocomplete="off" currency="true" type="text" id="mha_annual_income" value="'.calc_fields('mha', 'wpmc_five_mhaai_initial', '100,000').'" />
        </div>

        <div class="mcalc-half">
          <label for="mha_monthly_debts">'.calc_fields('mha', 'wpmc_five_md', __('Monthly Debts', 'wpmc')).'</label>
          <i>' . $curr_symbol . '</i>
          <input name="monthly_debts" class="mha_input_data" autocomplete="off" currency="true" type="text" id="mha_monthly_debts" value="'.calc_fields('mha', 'wpmc_five_mhamd_initial', '2,000').'" />
        </div>

        <div class="mcalc-half">
          <label for="mha_estimated_annual_property_taxes">'.calc_fields('mha', 'wpmc_five_eapt', __('Estimated annual property taxes', 'wpmc')).'</label>
          <i>' . $curr_symbol . '</i>
          <input name="estimated_annual_property_taxes" class="mha_input_data" autocomplete="off" currency="true" type="text"  id="mha_estimated_annual_property_taxes" value="'.calc_fields('mha', 'wpmc_five_mhaeapt_initial', '1,500').'" />
        </div>

        <div class="mcalc-half">
          <label for="mha_estimated_annual_home_insurance">'.calc_fields('mha', 'wpmc_five_eahi', __('Estimated Annual Home Insurance', 'wpmc')).'</label>
          <i>' . $curr_symbol . '</i>
          <input name="estimated_annual_home_insurance" class="mha_input_data" autocomplete="off" currency="true" type="text"  id="mha_estimated_annual_home_insurance" value="'.calc_fields('mha', 'wpmc_five_mhaeahi_initial', '1,000').'" />
        </div>

        <div class="mcalc-half">
          <label for="mha_down_payment">'.calc_fields('mha', 'wpmc_five_dp', __('Down Payment', 'wpmc')).'</label>
          <i>' . $curr_symbol . '</i>
          <input name="down_payment" class="mha_input_data" autocomplete="off" currency="true" type="text" id="mha_down_payment" value="'.calc_fields('mha', 'wpmc_five_mhadp_initial', '10,000').'" />
        </div>

        <div class="mcalc-half mha_main_wrapper">
          <label for="mha_interest_rate">'.calc_fields('mha', 'wpmc_five_ir', __('Interest Rate', 'wpmc')).'</label>
          <input name="interest_rate"  class="mha_input_data_right" autocomplete="off" currency="true" type="text" id="mha_interest_rate" value="'.calc_fields('mha', 'wpmc_five_mhair_initial', '4').'" />
          <i>%</i>
        </div>

        <div class="mcalc-full">
          <label for="mha_loan_term">'.calc_fields('mha', 'wpmc_five_lt', __('Loan Term', 'wpmc')).'</label>
          <select name="mortgage_term" id="mha_loan_term">
            <option value="30">30 '. __('year fixed', 'wpmc').'</option>
            <option value="15">15 '. __('year fixed', 'wpmc').'</option>
          </select>
        </div>

        <div class="mcalc-full mcwp-results">
          <label for="mha_cal5_email">'. __('Want a Copy of the Results?', 'wpmc').'</label>
          <input type="email" id="mha_cal5_email" placeholder="'. __('Enter your email address', 'wpmc').'" value="" name="email" />
          <input type="button" id="mha_wpmc5_send_mail" class="mcwp-submit bg mha_submit mcalc-color" value="'. __('Send Results!', 'wpmc').'">
        </div>
      </div>

      <div class="mcalc-values">
        <div class="mcalc-results">
          <h4>'. __('You can afford up to', 'wpmc').'</h4>
          <h2 class="mcalc-value">'.$curr_symbol.'<span id="mha_afford_house_div_span"></span></h2>
          <div class="mcalc-breakdown" aria-hidden="true"></div>
          <p class="mcwp-pi">'. __('Monthly Payment', 'wpmc').' <strong>'.$curr_symbol.'<span id="mha_emmp_div_span"></span></strong></p>
          <p class="mcwp-pi mcalc-part">'. __('Principal & Interest', 'wpmc').' <strong>'.$curr_symbol.'<span id="mha_pi_div_span"></span></strong></p>
          <p class="mcwp-pi mcalc-part">'. __('Tax', 'wpmc').' <strong>'.$curr_symbol.'<span id="mha_taxes_div_span"></span></strong></p>
          <p class="mcwp-pi mcalc-part">'. __('Insurance', 'wpmc').' <strong>'.$curr_symbol.'<span id="mha_insurance_div_span"></span></strong></p>
           <small>'.calc_fields('mha', 'wpmc_five_disclaimer', '').'</small>
        </div>
      </div>
     </div>';

      $calTemplate2 .= '
        <input type="hidden" name="cal_result_home_afford" class="mha_afford_house_div_span" />
        <input type="hidden" name="monthly_payment" class="mha_emmp_div_span" />
        <input type="hidden" name="principal_interest" class="mha_pi_div_span" />
        <input type="hidden" name="tax_value" class="mha_taxes_div_span" value=""/>
        <input type="hidden" name="insurance_value" class="mha_insurance_div_span" value=""/>
        <input type="hidden" name="type" value="mha"/>
      ';

if ($status !== false && $status == 'valid' && $license !== false) {
    $calculator_layout = $calTemplate2;
} else {
    $calculator_layout = mcwp_unlicensed_notice();
}
