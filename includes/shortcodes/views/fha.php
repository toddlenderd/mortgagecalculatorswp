<?php
$option_func = (use_network_settings('wpmc_two_use_network_settings') === 'yes') ? 'get_site_option' : 'get_option';

//$license = $option_func('edd_wpmc_license_key' );
//$status  = $option_func('edd_wpmc_license_status' );

$mcwp_hide_insurance_two = $option_func('mcwp_hide_insurance_two');
$mcwp_hide_hoa_two = $option_func('mcwp_hide_hoa_two');

$wpmc_email = $option_func('wpmc_two_email');
$admin_email = $option_func('admin_email');
$wpmc_two_email = (!empty($wpmc_email) && $wpmc_email === '[email]') ? $admin_email : ((!empty($wpmc_email) && $wpmc_email !== '[email]') ? $wpmc_email : $admin_email);


$show_hoa = ($mcwp_hide_hoa_two === 'yes') ? '' : '<p>' . calc_fields('fha', 'wpmc_two_mhoa', __('Monthly HOA', 'wpmc')) . ' <strong>' . $curr_symbol . '<span id="fha_hoa_div_span">1421</span></strong></p>';
$show_in = ($mcwp_hide_insurance_two === 'yes') ? '' : '
      <p>' . calc_fields('fha', 'wpmc_two_ai', __('Monthly Insurance', 'wpmc')) . ' <strong>' . $curr_symbol . '<span id="fha_minsure_div_span">1421</span></strong></p>
      <p>Up-Front MIP (1.75%)<strong>$<span id="fha_upmip_div_span">0</span></strong></p>
      <p>' . __('Monthly Mortgage Insurance', 'wpmc') . ' <strong>' . $curr_symbol . '<span id="fha_mmi_div_span">0</span></strong></p>';

$calTemplate2 = '
<div>
  <div class="mcalc-main">
      <div class="mcalc-half mcwp-purchase">
        <label for="fha_inp_purchase_price">' . calc_fields('fha', 'wpmc_two_pp', __('Purchase Price', 'wpmc')) . '</label>
        <i>' . $curr_symbol . '</i>
        <input type="text" name="purchase_price" id="fha_inp_purchase_price" value="' . calc_fields('fha', 'wpmc_two_pp_initial', '250,000') . '" class="mcalc-dollar">

      </div>

      <div class="mcalc-half mcwp-term">
        <label for="fha_mortgage_term_yr">' . calc_fields('fha', 'wpmc_two_mt', __('Mortgage Term', 'wpmc')) . '</label>
        <select name="mortgage_term" id="fha_mortgage_term_yr">
            <option value="30">30 ' . __('Years', 'wpmc') . '</option>
                            <option value="25">25 ' . __('Years', 'wpmc') . '</option>
                            <option value="20">20 ' . __('Years', 'wpmc') . '</option>
                            <option value="15">15 ' . __('Years', 'wpmc') . '</option>
                            <option value="10">10 ' . __('Years', 'wpmc') . '</option>
                            <option value="5">5 ' . __('Years', 'wpmc') . '</option>
          </select>
      </div>

      <div class="mcalc-half mcwp-down-payment">
        <label class="mcalc-half" for="fha_down_payment_inp">' . calc_fields('fha', 'wpmc_two_dp', __('Down Payment', 'wpmc')) . ' (' . $curr_symbol . ')</label>

        <input type="text" name="down_payment" id="fha_down_payment_inp" value="" class="mcalc-half">

        <input id="mcwp_fha_dp_slider" class="ex1 fha_down_payment_scrl" type="text" data-slider-min="0" data-slider-max="80" data-slider-step=".5" data-slider-value="' . calc_fields('fha', 'wpmc_two_dp_initial', '5') . '" aria-label="FHA Down Payment" />
        <p class="mcalc-percent">' . calc_fields('fha', 'wpmc_two_dp_initial', '5') . '%</p>
      </div>

      <div class="mcalc-half mcwp-taxes">
        <label class="mcalc-half" for="fha_annual_tax_inp">' . calc_fields('fha', 'wpmc_two_at', __('Annual Taxes', 'wpmc')) . ' (' . $curr_symbol . ')</label>

        <input type="text" name="annual_taxes" id="fha_annual_tax_inp" value="" class="mcalc-half">

        <input id="mcwp_fha_tax_slider" class="ex1 fha_annual_tax_scrl" type="text" data-slider-min="0" data-slider-max="20" data-slider-step="0.1" data-slider-value="' . calc_fields('fha', 'wpmc_two_at_initial', '1') . '"/>
        <p class="mcalc-percent">' . calc_fields('fha', 'wpmc_two_at_initial', '1') . '%</p>
      </div>

      <div class="mcalc-full mcwp-interest-rate">
        <label for="mcwp_fha_ir_slider">' . calc_fields('fha', 'wpmc_two_ir', __('Interest Rate', 'wpmc')) . ' (%)</label>
        <input name="interest_rate" id="mcwp_fha_ir_slider" class="ex1 fha_interest_rate_scrl" type="text" data-slider-min="1" data-slider-max="30" data-slider-step=".125" data-slider-value="' . calc_fields('fha', 'wpmc_two_ir_min', '5') . '">
        <p class="mcalc-percent">' . calc_fields('fha', 'wpmc_two_ir_min', '5') . '%</p>
      </div>';

if ($mcwp_hide_insurance_two === 'yes') {
  $calTemplate2 .= '
        <input type="hidden" name="annual_insurance" id="fha_annual_insurance_inp" value="0" class="mcalc-dollar">
      ';
} else {
  $calTemplate2 .= '<div class="mcalc-half mcwp-insurance">
          <label for="fha_annual_insurance_inp">' . calc_fields('fha', 'wpmc_two_ai', __('Annual Insurance', 'wpmc')) . '</label>
          <i>' . $curr_symbol . '</i>
          <input type="text" name="annual_insurance" id="fha_annual_insurance_inp" value="' . calc_fields('fha', 'wpmc_two_ai_initial', '600') . '" class="mcalc-dollar">
        </div>';
}

if ($mcwp_hide_hoa_two === 'yes') {
  $calTemplate2 .= '<input type="hidden" name="monthly_hoa_form" id="fha_monthly_hoa_inp" value="0" class="mcalc-dollar">';
} else {
  $calTemplate2 .= '<div class="mcalc-half mcwp-hoa">
          <label for="fha_monthly_hoa_inp">' . calc_fields('fha', 'wpmc_two_mhoa', __('Monthly HOA', 'wpmc')) . '</label>
          <i>' . $curr_symbol . '</i>
          <input type="text" name="monthly_hoa_form" id="fha_monthly_hoa_inp" value="' . calc_fields('fha', 'wpmc_two_mhoa_initial', '50') . '" class="mcalc-dollar">
        </div>';
}

$calTemplate2 .= '<div class="mcalc-full mcwp-results">
        <label for="cal2_email">' . __('Want a Copy of the Results?', 'wpmc') . '</label>
        <input type="email" id="cal2_email" placeholder="' . __('Enter your email address', 'wpmc') . '" value="" name="email" />
        <input type="button" id="wpmc2_send_mail" class="mcwp-submit bg fha_submit mcalc-color" value="' . __('Send Results!', 'wpmc') . '">
      </div>
  </div>
  <div class="mcalc-values">
    <div class="mcalc-results">
      <h2 class="mcalc-value">' . $curr_symbol . '<span id="fha_emmp_div_span">1421</span></h2>
      <h3>' . __('Monthly FHA Payment', 'wpmc') . '</h3>
      <p class="mcwp-pi">' . __('Principal & Interest', 'wpmc') . ' <strong>' . $curr_symbol . '<span id="fha_pi_div_span">1421</span></strong></p>
      <p class="mcwp-mt">' . calc_fields('fha', 'wpmc_two_at', __('Monthly Taxes', 'wpmc')) . ' <strong>' . $curr_symbol . '<span id="fha_mtax_div_span" class="mtchange">1421</span></strong></p>
      ' . $show_hoa . '
      ' . $show_in . '
      <small>' . calc_fields('fha', 'wpmc_two_disclaimer', '') . '</small>
    </div>
  </div>
</div>
';

$calTemplate2 .= '
  <input type="hidden" name="calculation_result" class="fha_emmp_div_span" />
  <input type="hidden" name="principal_and_interest" class="fha_pi_div_span" />
  <input type="hidden" name="monthly_taxes" class="fha_mtax_div_span" />
  <input type="hidden" name="monthly_insurance" class="fha_minsure_div_span" value=""/>
  <input type="hidden" name="monthly_mortgage_insurance" class="fha_mmi_div_span" value=""/>
  <input type="hidden" name="monthly_hoa" class="fha_hoa_div_span" value=""/>
  <input type="hidden" name="type" value="fha"/>
';

if ($status !== false && $status == 'valid' && $license !== false) {
  $calculator_layout = $calTemplate2;
} else {
  $calculator_layout = '<p>&nbsp;</p><p style="color: red; border: 2px solid red; padding: 40px; margin: 100px auto; text-align: center; width: 50%;"><strong style="color: red;">' . __('Warning: You are NOT subscribed to view this calculator', 'wpmc') . '</strong></p>';
}
