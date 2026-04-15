<?php
$option_func = (use_network_settings('wpmc_three_use_network_settings') === 'yes') ? 'get_site_option' : 'get_option';

//$license = $option_func('edd_wpmc_license_key' );
//$status  = $option_func('edd_wpmc_license_status' );

$mcwp_hide_insurance_three = $option_func('mcwp_hide_insurance_three');
$mcwp_hide_hoa_three = $option_func('mcwp_hide_hoa_three');

$wpmc_email = $option_func('wpmc_three_email');
$admin_email = $option_func('admin_email');
$wpmc_three_email = (!empty($wpmc_email) && $wpmc_email === '[email]') ? $admin_email : ((!empty($wpmc_email) && $wpmc_email !== '[email]') ? $wpmc_email : $admin_email);

$show_hoa = ($mcwp_hide_hoa_three === 'yes') ? '' : '<p>' . calc_fields('va', 'wpmc_three_mhoa', __('Monthly HOA', 'wpmc')) . ' <strong>' . $curr_symbol . '<span id="va_hoa_div_span">1421</span></strong></p>';
$show_in = ($mcwp_hide_insurance_three === 'yes') ? '' : '
      <p>' . calc_fields('va', 'wpmc_three_ai', __('Monthly Insurance', 'wpmc')) . ' <strong>' . $curr_symbol . '<span id="va_minsure_div_span">1421</span></strong></p>';


$calTemplate3 = '
<div>
  <div class="mcalc-main">
    <div class="mcalc-half mcwp-veteran-status">
      <label for="va_service_type">' . calc_fields('va', 'wpmc_three_vs', __('Veteran Status', 'wpmc')) . '</label>
      <select name="service_type" id="va_service_type">
        <option value="regular_military" selected>' . __('Regular Military', 'wpmc') . '</option>
        <option value="reserves_national">' . __('Reserves/National Guard', 'wpmc') . '</option>
        <option value="surviving_spouse">' . __('Surviving Spouse', 'wpmc') . '</option>
        <option value="disabled_veteran">' . __('Disabled Veteran', 'wpmc') . '</option>
      </select>
    </div>

    <div class="mcalc-half mcwp-first-time">
      <label for="va_frist_time">' . calc_fields('va', 'wpmc_three_ftvl', __('First Time VA Loan?', 'wpmc')) . '</label>
      <select name="first_time" id="va_frist_time">
        <option value="yes" selected>' . __('Yes', 'wpmc') . '</option>
        <option value="no">' . __('No', 'wpmc') . '</option>
      </select>
    </div>

    <div class="mcalc-half mcwp-term">
      <label for="va_mortgage_term_yr">' . calc_fields('va', 'wpmc_three_mt', __('Mortgage Term', 'wpmc')) . '</label>
      <select name="mortgage_term" id="va_mortgage_term_yr">
        <option value="30">30 ' . __('Years', 'wpmc') . '</option>
        <option value="25">25 ' . __('Years', 'wpmc') . '</option>
        <option value="20">20 ' . __('Years', 'wpmc') . '</option>
        <option value="15">15 ' . __('Years', 'wpmc') . '</option>
        <option value="10">10 ' . __('Years', 'wpmc') . '</option>
        <option value="5">5 ' . __('Years', 'wpmc') . '</option>
      </select>
    </div>

    <div class="mcalc-half mcwp-purchase">
      <label for="va_inp_purchase_price">' . calc_fields('va', 'wpmc_three_pp', __('Purchase Price', 'wpmc')) . '</label>
      <i>' . $curr_symbol . '</i>
      <input type="text" name="purchase_price" id="va_inp_purchase_price" value="' . calc_fields('va', 'wpmc_three_pp_initial', '250,000') . '" class="mcalc-dollar">
    </div>

    <div class="mcalc-half mcwp-down-payment" style="clear:none;">
      <label class="mcalc-half" for="va_down_payment_inp">' . calc_fields('va', 'wpmc_three_dp', __('Down Payment', 'wpmc')) . ' (' . $curr_symbol . ')</label>
      <input type="text" name="down_payment" id="va_down_payment_inp" value="" class="mcalc-half">
      <input id="ex1 e7" class="ex1 va_down_payment_scrl" data-slider-id="ex1Slider" type="text" data-slider-min="0" data-slider-max="80" data-slider-step=".5" data-slider-value="' . calc_fields('va', 'wpmc_three_dp_initial', '0') . '"/>
      <p class="mcalc-percent">' . calc_fields('va', 'wpmc_three_dp_initial', '0') . '%</p>
    </div>

    <div class="mcalc-half mcwp-interest-rate">
      <label for="va_interest_rate" style="margin-bottom:10px;">' . calc_fields('va', 'wpmc_three_ir', __('Interest Rate', 'wpmc')) . ' (' . $curr_symbol . ')</label>
      <input id="ex1 e9" name="interest_rate" class="ex1 va_interest_rate_scrl" data-slider-id="ex1Slider" type="text" data-slider-min="1" data-slider-max="30" data-slider-step=".125" data-slider-value="' . calc_fields('va', 'wpmc_three_ir_initial', '5') . '" name="va_interest_rate">
      <p class="mcalc-percent">' . calc_fields('va', 'wpmc_three_ir_initial', '5') . '%</p>
    </div>

    <div class="mcalc-half mcwp-taxes">
      <label class="mcalc-half" for="va_annual_tax_inp">' . calc_fields('va', 'wpmc_three_at', __('Annual Taxes', 'wpmc')) . ' (' . $curr_symbol . ')</label>
      <input type="text" name="annual_taxes" id="va_annual_tax_inp" value="" class="mcalc-half">
      <input id="ex1 e8" class="ex1 va_annual_tax_scrl" data-slider-id="ex1Slider" type="text" data-slider-min="0" data-slider-max="20" data-slider-step="0.1" data-slider-value="' . calc_fields('va', 'wpmc_three_at_initial', '1') . '"/>
      <p class="mcalc-percent">' . calc_fields('va', 'wpmc_three_at_initial', '1') . '%</p>
    </div>
    ';


if ($mcwp_hide_insurance_three === 'yes') {
  $calTemplate3 .= '
        <input type="hidden" name="annual_insurance" id="va_annual_insurance_inp" value="0" class="mcalc-dollar">
      ';
} else {
  $calTemplate3 .= '<div class="mcalc-half mcwp-insurance">
        <label for="va_annual_insurance_inp">' . calc_fields('va', 'wpmc_three_ai', __('Annual Insurance', 'wpmc')) . ' (' . $curr_symbol . ')</label>
        <i>' . $curr_symbol . '</i>
        <input type="text" name="annual_insurance" id="va_annual_insurance_inp" value="' . calc_fields('va', 'wpmc_three_ai_initial', '600') . '" class="mcalc-dollar">
      </div>';
}


if ($mcwp_hide_hoa_three === 'yes') {
  $calTemplate3 .= '
        <input type="hidden" name="monthly_hoa_form" id="va_monthly_hoa_inp" value="0" class="mcalc-dollar">
      ';
} else {
  $calTemplate3 .= '<div class="mcalc-half mcwp-hoa">
        <label for="va_monthly_hoa_inp">' . calc_fields('va', 'wpmc_three_mhoa', __('Monthly HOA', 'wpmc')) . '</label>
        <i>' . $curr_symbol . '</i>
        <input type="text" name="monthly_hoa_form" id="va_monthly_hoa_inp" value="' . calc_fields('va', 'wpmc_three_mhoa_initial', '50') . '" class="mcalc-dollar">
      </div>';
}

$calTemplate3 .= '<div class="mcalc-full mcwp-results">
      <label for="cal3_email">' . __('Want a Copy of the Results?', 'wpmc') . '</label>
      <input type="email" id="cal3_email" placeholder="' . __('Enter your email address', 'wpmc') . '" value="" name="email" />
      <input type="button" id="wpmc3_send_mail" class="mcwp-submit bg va_submit mcalc-color" value="' . __('Send Results!', 'wpmc') . '">
    </div>
  </div>

  <div class="mcalc-values">
    <div class="mcalc-results">
      <h2 class="mcalc-value">' . $curr_symbol . '<span id="va_emmp_div_span">1421</span></h2>
      <h3>' . __('Monthly VA Payment', 'wpmc') . '</h3>
      <p class="mcwp-pi">' . __('Principal & Interest', 'wpmc') . ' <strong>' . $curr_symbol . '<span id="va_pi_div_span">1421</span></strong></p>
      <p class="mcwp-mt">' . calc_fields('va', 'wpmc_three_at', __('Monthly Taxes', 'wpmc')) . ' <strong>' . $curr_symbol . '<span id="va_mtax_div_span">1421</span></strong></p>
      ' . $show_hoa . '
      ' . $show_in . '
      <p class="mcwp-pp">' . calc_fields('va', 'wpmc_three_pp', __('Purchase Price', 'wpmc')) . ' <strong>' . $curr_symbol . '<span id="va_purchase_p_span">1421</span></strong></p>
      <p class="mcwp-vaff">' . __('VA Funding Fee', 'wpmc') . ' <strong>' . $curr_symbol . '<span id="va_funding_fee_p_span">1421</span></strong></p>
      <p class="mcwp-af">' . __('Amount Financed', 'wpmc') . ' <strong>' . $curr_symbol . '<span id="va_amount_finance_p_span">1421</span></strong></p>
                    <small>' . calc_fields('va', 'wpmc_three_disclaimer', '') . '</small>
    </div>
  </div>
</div>';


$calTemplate3 .= '
  <input type="hidden" name="calculation_result" class="va_emmp_div_span" />
  <input type="hidden" name="principal_and_interest" class="va_pi_div_span" />
  <input type="hidden" name="monthly_taxes" class="va_mtax_div_span" />
  <input type="hidden" name="monthly_insurance" class="va_minsure_div_span" value=""/>
  <input type="hidden" name="monthly_hoa" class="va_hoa_div_span" value=""/>
  <input type="hidden" name="funding_fee" class="va_funding_fee_p_span" value=""/>
  <input type="hidden" name="va_funding_fee_2" class="va_funding_fee_p_span" value=""/>
  <input type="hidden" name="amount_financed" class="va_amount_finance_p_span" value=""/>
  <input type="hidden" name="type" value="va"/>
';

if ($status !== false && $status == 'valid' && $license !== false) {
  $calculator_layout = $calTemplate3;
} else {
  $calculator_layout = '<p style="color: red; border: 2px solid red; padding: 40px; margin: 100px auto; text-align: center; width: 50%;"><strong>' . __('Warning: You are NOT subscribed to view this calculator', 'wpmc') . '</strong></p>';
}
