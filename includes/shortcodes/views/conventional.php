<?php
$option_func = (use_network_settings('wpmc_one_use_network_settings') === 'yes') ? 'get_site_option' : 'get_option';
$mcwp_hide_insurance_one = $option_func('mcwp_hide_insurance_one');
$mcwp_hide_insurance_rate = $option_func('mcwp_hide_insurance_rate');
$mcwp_hide_hoa_one = $option_func('mcwp_hide_hoa_one');

$wpmc_one_dp_initial = calc_fields('cv', 'wpmc_one_dp_initial', '5');
$cv_rate = mcwp_default_rate('cv', 'wpmc_one_ir_min', '5');
$wpmc_one_ir_initial = $cv_rate['rate'];
$wpmc_one_inr_initial = calc_fields('cv', 'wpmc_one_inr_init', '0');
$wpmc_one_at_initial = calc_fields('cv', 'wpmc_one_at_initial', '1');

$show_hoa = ($mcwp_hide_hoa_one === 'yes') ? '' : '<p class="mcalc-part">' . calc_fields('cv', 'wpmc_one_mhoa', __('Monthly HOA', 'wpmc')) . ' <strong class="mcalc-value">' . $curr_symbol . '<span id="hoa_div_span">1421</span></strong></p>';
$show_in = ($mcwp_hide_insurance_one === 'yes') ? '' : '<p class="mcalc-part">' . calc_fields('cv', 'wpmc_one_ai', __('Monthly insurance', 'wpmc')) . ' <strong class="mcalc-value">' . $curr_symbol . '<span id="minsure_div_span">1421</span></strong></p>';
$show_mpmi = ($mcwp_hide_insurance_rate === 'yes') ? '' : '<p class="mcalc-part">' . calc_fields('cv', 'wpmc_one_mpmi', __('Monthly PMI', 'wpmc')) . ' <strong class="mcalc-value">' . $curr_symbol . '<span id="mpmi">0</span></strong></p>';

$f_price = '<div class="mcalc-half mcwp-purchase">
      <label for="inp_purchase_price">' . calc_fields('cv', 'wpmc_one_pp', __('Purchase price', 'wpmc')) . '</label>
      <i>' . $curr_symbol . '</i>
      <input type="text" name="purchase_price" id="inp_purchase_price" value="' . calc_fields('cv', 'wpmc_one_pp_initial', '250,000') . '" class="mcalc-dollar">
    </div>';

$f_term = '<div class="mcalc-half mcwp-term mcalc-seg-field">
      <label for="mortgage_term_yr">' . calc_fields('cv', 'wpmc_one_mt', __('Loan term', 'wpmc')) . '</label>
      <select name="mortgage_term" id="mortgage_term_yr" data-mcalc-seg data-unit="' . esc_attr__('yr', 'wpmc') . '">
        <option value="30">30 ' . __('Years', 'wpmc') . '</option>
        <option value="25">25 ' . __('Years', 'wpmc') . '</option>
        <option value="20">20 ' . __('Years', 'wpmc') . '</option>
        <option value="15">15 ' . __('Years', 'wpmc') . '</option>
        <option value="10">10 ' . __('Years', 'wpmc') . '</option>
        <option value="5">5 ' . __('Years', 'wpmc') . '</option>
      </select>
    </div>';

$f_dp = '<div class="mcalc-half mcwp-down-payment">
      <label class="mcalc-half" for="down_payment_inp">' . mcwp_label_unit(calc_fields('cv', 'wpmc_one_dp', __('Down payment', 'wpmc')), $curr_symbol, $mcwp_modern) . '</label>
      ' . ($mcwp_modern ? '<i>' . $curr_symbol . '</i>' : '') . '
      <input type="text" name="down_payment" id="down_payment_inp" value="" class="mcalc-half">
      <input id="mcwp_cv_dp_slider" class="ex1 down_payment_scrl" type="text" data-slider-min="0" data-slider-max="80" data-slider-step=".5" data-slider-value="' . $wpmc_one_dp_initial . '" aria-label="' . esc_attr__('Down payment', 'wpmc') . '" />
      <p class="mcalc-percent">' . $wpmc_one_dp_initial . '%</p>
    </div>';

$f_tax = '<div class="mcalc-half mcwp-taxes">
      <label class="mcalc-half" for="annual_tax_inp">' . mcwp_label_unit(calc_fields('cv', 'wpmc_one_at', __('Annual taxes', 'wpmc')), $curr_symbol, $mcwp_modern) . '</label>
      ' . ($mcwp_modern ? '<i>' . $curr_symbol . '</i>' : '') . '
      <input type="text" name="annual_taxes" id="annual_tax_inp" value="" class="mcalc-half">
      <input id="mcwp_cv_tax_slider" class="ex1 annual_tax_scrl" type="text" data-slider-min="0" data-slider-max="20" data-slider-step="0.1" data-slider-value="' . $wpmc_one_at_initial . '" aria-label="' . esc_attr__('Annual taxes', 'wpmc') . '" />
      <p class="mcalc-percent">' . $wpmc_one_at_initial . '%</p>
    </div>';

$f_rate = '<div class="mcalc-full mcwp-interest-rate">
      <label for="mcwp_cv_ir_slider">' . mcwp_label_unit(calc_fields('cv', 'wpmc_one_ir', __('Interest rate', 'wpmc')), '%', $mcwp_modern) . '</label>
      <input id="mcwp_cv_ir_slider" name="interest_rate" class="ex1 interest_rate_scrl" type="text" data-slider-min="1" data-slider-max="30" data-slider-step=".125" data-slider-value="' . $wpmc_one_ir_initial . '" aria-label="' . esc_attr__('Interest rate', 'wpmc') . '"/>
      <p class="mcalc-percent">' . $wpmc_one_ir_initial . '%</p>
      ' . $cv_rate['note'] . '
    </div>';

if ($mcwp_hide_insurance_rate === 'yes') {
    $f_inr = '<input style="display:none !important" type="hidden" name="insurance_rate" class="insurance_rate_scrl" value="0" />';
} else {
    $f_inr = '<div class="mcalc-full mcwp-insurance-rate">
      <label for="mcwp_cv_inr_slider">' . mcwp_label_unit(calc_fields('cv', 'wpmc_one_inr', __('Mortgage insurance rate', 'wpmc')), '%', $mcwp_modern) . '</label>
      <input id="mcwp_cv_inr_slider" name="insurance_rate" class="ex1 insurance_rate_scrl" type="text" data-slider-min="0" data-slider-max="2.5" data-slider-step=".01" data-slider-value="' . $wpmc_one_inr_initial . '" aria-label="' . esc_attr__('Mortgage insurance rate', 'wpmc') . '" />
      <p class="mcalc-percent">' . $wpmc_one_inr_initial . '%</p>
    </div>
    <div class="mcalc-full mcwp-insurance-rate-disabled" style="display:none;">
      <label>' . mcwp_label_unit(calc_fields('cv', 'wpmc_one_inr', __('Mortgage insurance rate', 'wpmc')), '%', $mcwp_modern) . '</label>
      <p class="mcwp-insurance-message">' . __('Your LTV is less than 80%, so you will not need to pay PMI.', 'wpmc') . '</p>
    </div>';
}

if ($mcwp_hide_insurance_one === 'yes') {
    $f_ins = '<input type="hidden" name="annual_insurance" id="annual_insurance_inp" value="0" class="mcalc-dollar">';
} else {
    $f_ins = '<div class="mcalc-half mcwp-insurance">
      <label for="annual_insurance_inp">' . calc_fields('cv', 'wpmc_one_ai', __('Annual insurance', 'wpmc')) . '</label>
      <i>' . $curr_symbol . '</i>
      <input type="text" name="annual_insurance" id="annual_insurance_inp" value="' . calc_fields('cv', 'wpmc_one_ai_initial', '600') . '" class="mcalc-dollar">
    </div>';
}

if ($mcwp_hide_hoa_one === 'yes') {
    $f_hoa = '<input type="hidden" name="monthly_hoa_form" id="monthly_hoa_inp" value="0" class="mcalc-dollar">';
} else {
    $f_hoa = '<div class="mcalc-half mcwp-hoa">
      <label for="monthly_hoa_inp">' . calc_fields('cv', 'wpmc_one_mhoa', __('Monthly HOA', 'wpmc')) . '</label>
      <i>' . $curr_symbol . '</i>
      <input type="text" name="monthly_hoa_form" id="monthly_hoa_inp" value="' . calc_fields('cv', 'wpmc_one_mhoa_initial', '50') . '" class="mcalc-dollar">
    </div>';
}

$email = mcwp_email_block('cal1_email', 'wpmc1_send_mail', 'cv_submit', $mcwp_modern);

if ($mcwp_modern) {
    $inputs = $f_price . $f_dp . $f_term . $f_rate
        . mcwp_more_fields($f_tax . $f_inr . $f_ins . $f_hoa, mcwp_more_label($mcwp_hide_insurance_one !== 'yes', $mcwp_hide_hoa_one !== 'yes'), $curr_symbol);
    $summary = mcwp_summary_line(__('%1$s loan · %2$s-year fixed at %3$s', 'wpmc'), $curr_symbol, '#inp_purchase_price', '#down_payment_inp', '#mortgage_term_yr', '.interest_rate_scrl');
} else {
    $inputs = $f_price . $f_term . $f_dp . $f_tax . $f_rate . $f_inr . $f_ins . $f_hoa . $email;
    $summary = '';
}

$calculator_layout = '
<div class="mcalc-layout">
  <div class="mcalc-main">' . $inputs . '</div>
  <div class="mcalc-values">
    <div class="mcalc-results">
      <h2 class="mcalc-value mcalc-payment">' . $curr_symbol . '<span id="emmp_div_span">1421</span></h2>
      <h3>' . __('Monthly payment', 'wpmc') . '</h3>
      ' . $summary . '
      <div class="mcalc-breakdown" aria-hidden="true"></div>
      <p class="mcwp-pi mcalc-part">' . __('Principal and interest', 'wpmc') . ' <strong class="mcalc-value">' . $curr_symbol . '<span id="pi_div_span">1421</span></strong></p>
      <p class="mcwp-mt mcalc-part">' . calc_fields('cv', 'wpmc_one_at', __('Monthly taxes', 'wpmc')) . ' <strong class="mcalc-value">' . $curr_symbol . '<span id="mtax_div_span">1421</span></strong></p>
      ' . $show_hoa . '
      ' . $show_in . '
      ' . $show_mpmi . '
      ' . ($mcwp_modern ? $email : '') . '
      <small>' . calc_fields('cv', 'wpmc_one_disclaimer', '') . '</small>
    </div>
  </div>
</div>
<input type="hidden" name="principal_and_interest" class="pi_div_span" />
<input type="hidden" name="calculation_result" class="emmp_div_span" />
<input type="hidden" name="monthly_taxes" class="mtax_div_span" />
<input type="hidden" name="monthly_insurance" class="minsure_div_span" value=""/>
<input type="hidden" name="monthly_pmi" class="mpmi_div_span" value=""/>
<input type="hidden" name="monthly_mortgage_insurance" class="mmi_div_span" />
<input type="hidden" name="monthly_hoa" class="hoa_div_span" value=""/>
<input type="hidden" name="type" value="cv"/>
';
