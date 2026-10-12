<?php
$option_func = (use_network_settings('wpmc_three_use_network_settings') === 'yes') ? 'get_site_option' : 'get_option';

$mcwp_hide_insurance_three = $option_func('mcwp_hide_insurance_three');
$mcwp_hide_hoa_three = $option_func('mcwp_hide_hoa_three');

$show_hoa = ($mcwp_hide_hoa_three === 'yes') ? '' : '<p class="mcalc-part">' . calc_fields('va', 'wpmc_three_mhoa', __('Monthly HOA', 'wpmc')) . ' <strong>' . $curr_symbol . '<span id="va_hoa_div_span">1421</span></strong></p>';
$show_in = ($mcwp_hide_insurance_three === 'yes') ? '' : '
      <p class="mcalc-part">' . calc_fields('va', 'wpmc_three_ai', __('Monthly insurance', 'wpmc')) . ' <strong>' . $curr_symbol . '<span id="va_minsure_div_span">1421</span></strong></p>';

$f_status = '<div class="mcalc-full mcwp-veteran-status">
      <label for="va_service_type">' . calc_fields('va', 'wpmc_three_vs', __('Veteran status', 'wpmc')) . '</label>
      <select name="service_type" id="va_service_type">
        <option value="regular_military" selected>' . __('Regular Military', 'wpmc') . '</option>
        <option value="reserves_national">' . __('Reserves/National Guard', 'wpmc') . '</option>
        <option value="surviving_spouse">' . __('Surviving Spouse', 'wpmc') . '</option>
        <option value="disabled_veteran">' . __('Disabled Veteran', 'wpmc') . '</option>
      </select>
    </div>';

$f_first = '<div class="mcalc-full mcwp-first-time">
      <label for="va_frist_time" class="mcwp-first-time-label" style="display:inline-flex;align-items:center;gap:8px;cursor:pointer;font-weight:normal;">
        <input type="checkbox" name="first_time" id="va_frist_time" value="yes" checked />
        ' . calc_fields('va', 'wpmc_three_ftvl', __('First-time VA loan', 'wpmc')) . '
      </label>
    </div>';

$f_price = '<div class="mcalc-half mcwp-purchase">
      <label for="va_inp_purchase_price">' . calc_fields('va', 'wpmc_three_pp', __('Purchase price', 'wpmc')) . '</label>
      <i>' . $curr_symbol . '</i>
      <input type="text" name="purchase_price" id="va_inp_purchase_price" value="' . calc_fields('va', 'wpmc_three_pp_initial', '250,000') . '" class="mcalc-dollar">
    </div>';

$f_term = '<div class="mcalc-half mcwp-term mcalc-seg-field">
      <label for="va_mortgage_term_yr">' . calc_fields('va', 'wpmc_three_mt', __('Loan term', 'wpmc')) . '</label>
      <select name="mortgage_term" id="va_mortgage_term_yr" data-mcalc-seg data-unit="' . esc_attr__('yr', 'wpmc') . '">
        <option value="30">30 ' . __('Years', 'wpmc') . '</option>
        <option value="25">25 ' . __('Years', 'wpmc') . '</option>
        <option value="20">20 ' . __('Years', 'wpmc') . '</option>
        <option value="15">15 ' . __('Years', 'wpmc') . '</option>
        <option value="10">10 ' . __('Years', 'wpmc') . '</option>
        <option value="5">5 ' . __('Years', 'wpmc') . '</option>
      </select>
    </div>';

// In Modern, down payment sits beside purchase price and the rate gets a full row
$f_dp = '<div class="' . ($mcwp_modern ? 'mcalc-half' : 'mcalc-full') . ' mcwp-down-payment" style="clear:none;">
      <label class="mcalc-half" for="va_down_payment_inp">' . mcwp_label_unit(calc_fields('va', 'wpmc_three_dp', __('Down payment', 'wpmc')), $curr_symbol, $mcwp_modern) . '</label>
      ' . ($mcwp_modern ? '<i>' . $curr_symbol . '</i>' : '') . '
      <input type="text" name="down_payment" id="va_down_payment_inp" value="" class="mcalc-half">
      <input id="mcwp_va_dp_slider" class="ex1 va_down_payment_scrl" type="text" data-slider-min="0" data-slider-max="80" data-slider-step=".5" data-slider-value="' . calc_fields('va', 'wpmc_three_dp_initial', '0') . '" aria-label="' . esc_attr__('Down payment', 'wpmc') . '"/>
      <p class="mcalc-percent">' . calc_fields('va', 'wpmc_three_dp_initial', '0') . '%</p>
    </div>';

$va_rate = mcwp_default_rate('va', 'wpmc_three_ir_initial', '5');
$f_rate = '<div class="' . ($mcwp_modern ? 'mcalc-full' : 'mcalc-half') . ' mcwp-interest-rate">
      <label for="mcwp_va_ir_slider"' . ($mcwp_modern ? '' : ' style="margin-bottom:10px;"') . '>' . mcwp_label_unit(calc_fields('va', 'wpmc_three_ir', __('Interest rate', 'wpmc')), '%', $mcwp_modern) . '</label>
      <input id="mcwp_va_ir_slider" name="interest_rate" class="ex1 va_interest_rate_scrl" type="text" data-slider-min="1" data-slider-max="30" data-slider-step=".125" data-slider-value="' . $va_rate['rate'] . '" aria-label="' . esc_attr__('Interest rate', 'wpmc') . '">
      <p class="mcalc-percent">' . $va_rate['rate'] . '%</p>
      ' . $va_rate['note'] . '
    </div>';

$f_tax = '<div class="mcalc-half mcwp-taxes">
      <label class="mcalc-half" for="va_annual_tax_inp">' . mcwp_label_unit(calc_fields('va', 'wpmc_three_at', __('Annual taxes', 'wpmc')), $curr_symbol, $mcwp_modern) . '</label>
      ' . ($mcwp_modern ? '<i>' . $curr_symbol . '</i>' : '') . '
      <input type="text" name="annual_taxes" id="va_annual_tax_inp" value="" class="mcalc-half">
      <input id="mcwp_va_tax_slider" class="ex1 va_annual_tax_scrl" type="text" data-slider-min="0" data-slider-max="20" data-slider-step="0.1" data-slider-value="' . calc_fields('va', 'wpmc_three_at_initial', '1') . '" aria-label="' . esc_attr__('Annual taxes', 'wpmc') . '"/>
      <p class="mcalc-percent">' . calc_fields('va', 'wpmc_three_at_initial', '1') . '%</p>
    </div>';

if ($mcwp_hide_insurance_three === 'yes') {
    $f_ins = '<input type="hidden" name="annual_insurance" id="va_annual_insurance_inp" value="0" class="mcalc-dollar">';
} else {
    $f_ins = '<div class="mcalc-half mcwp-insurance">
        <label for="va_annual_insurance_inp">' . mcwp_label_unit(calc_fields('va', 'wpmc_three_ai', __('Annual insurance', 'wpmc')), $curr_symbol, $mcwp_modern) . '</label>
        <i>' . $curr_symbol . '</i>
        <input type="text" name="annual_insurance" id="va_annual_insurance_inp" value="' . calc_fields('va', 'wpmc_three_ai_initial', '600') . '" class="mcalc-dollar">
      </div>';
}

if ($mcwp_hide_hoa_three === 'yes') {
    $f_hoa = '<input type="hidden" name="monthly_hoa_form" id="va_monthly_hoa_inp" value="0" class="mcalc-dollar">';
} else {
    $f_hoa = '<div class="mcalc-half mcwp-hoa">
        <label for="va_monthly_hoa_inp">' . calc_fields('va', 'wpmc_three_mhoa', __('Monthly HOA', 'wpmc')) . '</label>
        <i>' . $curr_symbol . '</i>
        <input type="text" name="monthly_hoa_form" id="va_monthly_hoa_inp" value="' . calc_fields('va', 'wpmc_three_mhoa_initial', '50') . '" class="mcalc-dollar">
      </div>';
}

$email = mcwp_email_block('cal3_email', 'wpmc3_send_mail', 'va_submit', $mcwp_modern);

if ($mcwp_modern) {
    $inputs = $f_status . $f_first . $f_price . $f_dp . $f_term . $f_rate
        . mcwp_more_fields($f_tax . $f_ins . $f_hoa, mcwp_more_label($mcwp_hide_insurance_three !== 'yes', $mcwp_hide_hoa_three !== 'yes'), $curr_symbol);
    $summary = mcwp_summary_line(__('%1$s loan · %2$s-year fixed at %3$s', 'wpmc'), $curr_symbol, '#va_inp_purchase_price', '#va_down_payment_inp', '#va_mortgage_term_yr', '.va_interest_rate_scrl');
} else {
    $inputs = $f_status . $f_first . $f_price . $f_term . $f_dp . $f_rate . $f_tax . $f_ins . $f_hoa . $email;
    $summary = '';
}

$calTemplate3 = '
<div class="mcalc-layout">
  <div class="mcalc-main">' . $inputs . '</div>
  <div class="mcalc-values">
    <div class="mcalc-results">
      <h2 class="mcalc-value">' . $curr_symbol . '<span id="va_emmp_div_span">1421</span></h2>
      <h3>' . __('Monthly VA payment', 'wpmc') . '</h3>
      ' . $summary . '
      <div class="mcalc-breakdown" aria-hidden="true"></div>
      <p class="mcwp-pi mcalc-part">' . __('Principal and interest', 'wpmc') . ' <strong>' . $curr_symbol . '<span id="va_pi_div_span">1421</span></strong></p>
      <p class="mcwp-mt mcalc-part">' . calc_fields('va', 'wpmc_three_at', __('Monthly taxes', 'wpmc')) . ' <strong>' . $curr_symbol . '<span id="va_mtax_div_span">1421</span></strong></p>
      ' . $show_hoa . '
      ' . $show_in . '
      <p class="mcwp-pp">' . calc_fields('va', 'wpmc_three_pp', __('Purchase price', 'wpmc')) . ' <strong>' . $curr_symbol . '<span id="va_purchase_p_span">1421</span></strong></p>
      <p class="mcwp-vaff">' . __('VA funding fee', 'wpmc') . ' <strong>' . $curr_symbol . '<span id="va_funding_fee_p_span">1421</span></strong></p>
      <p class="mcwp-af">' . __('Amount financed', 'wpmc') . ' <strong>' . $curr_symbol . '<span id="va_amount_finance_p_span">1421</span></strong></p>
      ' . ($mcwp_modern ? $email : '') . '
      <small>' . calc_fields('va', 'wpmc_three_disclaimer', '') . '</small>
    </div>
  </div>
</div>
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
    $calculator_layout = mcwp_unlicensed_notice();
}
