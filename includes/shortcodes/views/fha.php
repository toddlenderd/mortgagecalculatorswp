<?php
$option_func = (use_network_settings('wpmc_two_use_network_settings') === 'yes') ? 'get_site_option' : 'get_option';

$mcwp_hide_insurance_two = $option_func('mcwp_hide_insurance_two');
$mcwp_hide_hoa_two = $option_func('mcwp_hide_hoa_two');

$show_hoa = ($mcwp_hide_hoa_two === 'yes') ? '' : '<p class="mcalc-part">' . calc_fields('fha', 'wpmc_two_mhoa', __('Monthly HOA', 'wpmc')) . ' <strong>' . $curr_symbol . '<span id="fha_hoa_div_span">1421</span></strong></p>';
$show_in = ($mcwp_hide_insurance_two === 'yes') ? '' : '
      <p class="mcalc-part">' . calc_fields('fha', 'wpmc_two_ai', __('Monthly insurance', 'wpmc')) . ' <strong>' . $curr_symbol . '<span id="fha_minsure_div_span">1421</span></strong></p>
      <p>' . __('Up-front MIP (1.75%)', 'wpmc') . ' <strong>' . $curr_symbol . '<span id="fha_upmip_div_span">0</span></strong></p>
      <p class="mcalc-part">' . __('Monthly mortgage insurance', 'wpmc') . ' <strong>' . $curr_symbol . '<span id="fha_mmi_div_span">0</span></strong></p>';

$f_price = '<div class="mcalc-half mcwp-purchase">
        <label for="fha_inp_purchase_price">' . calc_fields('fha', 'wpmc_two_pp', __('Purchase price', 'wpmc')) . '</label>
        <i>' . $curr_symbol . '</i>
        <input type="text" name="purchase_price" id="fha_inp_purchase_price" value="' . calc_fields('fha', 'wpmc_two_pp_initial', '250,000') . '" class="mcalc-dollar">
      </div>';

$f_term = '<div class="mcalc-half mcwp-term mcalc-seg-field">
        <label for="fha_mortgage_term_yr">' . calc_fields('fha', 'wpmc_two_mt', __('Loan term', 'wpmc')) . '</label>
        <select name="mortgage_term" id="fha_mortgage_term_yr" data-mcalc-seg data-unit="' . esc_attr__('yr', 'wpmc') . '">
          <option value="30">30 ' . __('Years', 'wpmc') . '</option>
          <option value="25">25 ' . __('Years', 'wpmc') . '</option>
          <option value="20">20 ' . __('Years', 'wpmc') . '</option>
          <option value="15">15 ' . __('Years', 'wpmc') . '</option>
          <option value="10">10 ' . __('Years', 'wpmc') . '</option>
          <option value="5">5 ' . __('Years', 'wpmc') . '</option>
        </select>
      </div>';

$f_dp = '<div class="mcalc-half mcwp-down-payment">
        <label class="mcalc-half" for="fha_down_payment_inp">' . mcwp_label_unit(calc_fields('fha', 'wpmc_two_dp', __('Down payment', 'wpmc')), $curr_symbol, $mcwp_modern) . '</label>
        ' . ($mcwp_modern ? '<i>' . $curr_symbol . '</i>' : '') . '
        <input type="text" name="down_payment" id="fha_down_payment_inp" value="" class="mcalc-half">
        <input id="mcwp_fha_dp_slider" class="ex1 fha_down_payment_scrl" type="text" data-slider-min="0" data-slider-max="80" data-slider-step=".5" data-slider-value="' . calc_fields('fha', 'wpmc_two_dp_initial', '5') . '" aria-label="' . esc_attr__('Down payment', 'wpmc') . '" />
        <p class="mcalc-percent">' . calc_fields('fha', 'wpmc_two_dp_initial', '5') . '%</p>
      </div>';

$f_tax = '<div class="mcalc-half mcwp-taxes">
        <label class="mcalc-half" for="fha_annual_tax_inp">' . mcwp_label_unit(calc_fields('fha', 'wpmc_two_at', __('Annual taxes', 'wpmc')), $curr_symbol, $mcwp_modern) . '</label>
        ' . ($mcwp_modern ? '<i>' . $curr_symbol . '</i>' : '') . '
        <input type="text" name="annual_taxes" id="fha_annual_tax_inp" value="" class="mcalc-half">
        <input id="mcwp_fha_tax_slider" class="ex1 fha_annual_tax_scrl" type="text" data-slider-min="0" data-slider-max="20" data-slider-step="0.1" data-slider-value="' . calc_fields('fha', 'wpmc_two_at_initial', '1') . '" aria-label="' . esc_attr__('Annual taxes', 'wpmc') . '"/>
        <p class="mcalc-percent">' . calc_fields('fha', 'wpmc_two_at_initial', '1') . '%</p>
      </div>';

$fha_rate = mcwp_default_rate('fha', 'wpmc_two_ir_min', '5');
$f_rate = '<div class="mcalc-full mcwp-interest-rate">
        <label for="mcwp_fha_ir_slider">' . mcwp_label_unit(calc_fields('fha', 'wpmc_two_ir', __('Interest rate', 'wpmc')), '%', $mcwp_modern) . '</label>
        <input name="interest_rate" id="mcwp_fha_ir_slider" class="ex1 fha_interest_rate_scrl" type="text" data-slider-min="1" data-slider-max="30" data-slider-step=".125" data-slider-value="' . $fha_rate['rate'] . '" aria-label="' . esc_attr__('Interest rate', 'wpmc') . '">
        <p class="mcalc-percent">' . $fha_rate['rate'] . '%</p>
        ' . $fha_rate['note'] . '
      </div>';

if ($mcwp_hide_insurance_two === 'yes') {
    $f_ins = '<input type="hidden" name="annual_insurance" id="fha_annual_insurance_inp" value="0" class="mcalc-dollar">';
} else {
    $f_ins = '<div class="mcalc-half mcwp-insurance">
          <label for="fha_annual_insurance_inp">' . calc_fields('fha', 'wpmc_two_ai', __('Annual insurance', 'wpmc')) . '</label>
          <i>' . $curr_symbol . '</i>
          <input type="text" name="annual_insurance" id="fha_annual_insurance_inp" value="' . calc_fields('fha', 'wpmc_two_ai_initial', '600') . '" class="mcalc-dollar">
        </div>';
}

if ($mcwp_hide_hoa_two === 'yes') {
    $f_hoa = '<input type="hidden" name="monthly_hoa_form" id="fha_monthly_hoa_inp" value="0" class="mcalc-dollar">';
} else {
    $f_hoa = '<div class="mcalc-half mcwp-hoa">
          <label for="fha_monthly_hoa_inp">' . calc_fields('fha', 'wpmc_two_mhoa', __('Monthly HOA', 'wpmc')) . '</label>
          <i>' . $curr_symbol . '</i>
          <input type="text" name="monthly_hoa_form" id="fha_monthly_hoa_inp" value="' . calc_fields('fha', 'wpmc_two_mhoa_initial', '50') . '" class="mcalc-dollar">
        </div>';
}

$email = mcwp_email_block('cal2_email', 'wpmc2_send_mail', 'fha_submit', $mcwp_modern);

if ($mcwp_modern) {
    $inputs = $f_price . $f_dp . $f_term . $f_rate
        . mcwp_more_fields($f_tax . $f_ins . $f_hoa, mcwp_more_label($mcwp_hide_insurance_two !== 'yes', $mcwp_hide_hoa_two !== 'yes'), $curr_symbol);
    $summary = mcwp_summary_line(__('%1$s loan · %2$s-year fixed at %3$s', 'wpmc'), $curr_symbol, '#fha_inp_purchase_price', '#fha_down_payment_inp', '#fha_mortgage_term_yr', '.fha_interest_rate_scrl');
} else {
    $inputs = $f_price . $f_term . $f_dp . $f_tax . $f_rate . $f_ins . $f_hoa . $email;
    $summary = '';
}

$calTemplate2 = '
<div class="mcalc-layout">
  <div class="mcalc-main">' . $inputs . '</div>
  <div class="mcalc-values">
    <div class="mcalc-results">
      <h2 class="mcalc-value">' . $curr_symbol . '<span id="fha_emmp_div_span">1421</span></h2>
      <h3>' . __('Monthly FHA payment', 'wpmc') . '</h3>
      ' . $summary . '
      <div class="mcalc-breakdown" aria-hidden="true"></div>
      <p class="mcwp-pi mcalc-part">' . __('Principal and interest', 'wpmc') . ' <strong>' . $curr_symbol . '<span id="fha_pi_div_span">1421</span></strong></p>
      <p class="mcwp-mt mcalc-part">' . calc_fields('fha', 'wpmc_two_at', __('Monthly taxes', 'wpmc')) . ' <strong>' . $curr_symbol . '<span id="fha_mtax_div_span" class="mtchange">1421</span></strong></p>
      ' . $show_hoa . '
      ' . $show_in . '
      ' . ($mcwp_modern ? $email : '') . '
      <small>' . calc_fields('fha', 'wpmc_two_disclaimer', '') . '</small>
    </div>
  </div>
</div>
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
    $calculator_layout = mcwp_unlicensed_notice();
}
