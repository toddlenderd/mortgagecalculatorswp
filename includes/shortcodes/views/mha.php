<?php
$f_income = '<div class="mcalc-half">
          <label for="mha_annual_income">' . calc_fields('mha', 'wpmc_five_ai', __('Annual income', 'wpmc')) . '</label>
          <i>' . $curr_symbol . '</i>
          <input name="annual_income" class="mha_input_data" autocomplete="off" currency="true" type="text" id="mha_annual_income" value="' . calc_fields('mha', 'wpmc_five_mhaai_initial', '100,000') . '" />
        </div>';

$f_debts = '<div class="mcalc-half">
          <label for="mha_monthly_debts">' . calc_fields('mha', 'wpmc_five_md', __('Monthly debts', 'wpmc')) . '</label>
          <i>' . $curr_symbol . '</i>
          <input name="monthly_debts" class="mha_input_data" autocomplete="off" currency="true" type="text" id="mha_monthly_debts" value="' . calc_fields('mha', 'wpmc_five_mhamd_initial', '2,000') . '" />
        </div>';

$f_taxes = '<div class="mcalc-half">
          <label for="mha_estimated_annual_property_taxes">' . calc_fields('mha', 'wpmc_five_eapt', __('Estimated annual property taxes', 'wpmc')) . '</label>
          <i>' . $curr_symbol . '</i>
          <input name="estimated_annual_property_taxes" class="mha_input_data" autocomplete="off" currency="true" type="text" id="mha_estimated_annual_property_taxes" value="' . calc_fields('mha', 'wpmc_five_mhaeapt_initial', '1,500') . '" />
        </div>';

$f_insurance = '<div class="mcalc-half">
          <label for="mha_estimated_annual_home_insurance">' . calc_fields('mha', 'wpmc_five_eahi', __('Estimated annual home insurance', 'wpmc')) . '</label>
          <i>' . $curr_symbol . '</i>
          <input name="estimated_annual_home_insurance" class="mha_input_data" autocomplete="off" currency="true" type="text" id="mha_estimated_annual_home_insurance" value="' . calc_fields('mha', 'wpmc_five_mhaeahi_initial', '1,000') . '" />
        </div>';

$f_dp = '<div class="mcalc-half">
          <label for="mha_down_payment">' . calc_fields('mha', 'wpmc_five_dp', __('Down payment', 'wpmc')) . '</label>
          <i>' . $curr_symbol . '</i>
          <input name="down_payment" class="mha_input_data" autocomplete="off" currency="true" type="text" id="mha_down_payment" value="' . calc_fields('mha', 'wpmc_five_mhadp_initial', '10,000') . '" />
        </div>';

$mha_rate = mcwp_default_rate('mha', 'wpmc_five_mhair_initial', '4');
$f_rate = '<div class="mcalc-half mha_main_wrapper">
          <label for="mha_interest_rate">' . calc_fields('mha', 'wpmc_five_ir', __('Interest rate', 'wpmc')) . '</label>
          <input name="interest_rate" class="mha_input_data_right" autocomplete="off" currency="true" type="text" inputmode="decimal" id="mha_interest_rate" value="' . $mha_rate['rate'] . '" />
          <i>%</i>
          ' . $mha_rate['note'] . '
        </div>';

$f_term = '<div class="mcalc-full mcalc-seg-field">
          <label for="mha_loan_term">' . calc_fields('mha', 'wpmc_five_lt', __('Loan term', 'wpmc')) . '</label>
          <select name="mortgage_term" id="mha_loan_term" data-mcalc-seg data-unit="' . esc_attr__('yr', 'wpmc') . '">
            <option value="30">30 ' . __('year fixed', 'wpmc') . '</option>
            <option value="15">15 ' . __('year fixed', 'wpmc') . '</option>
          </select>
        </div>';

$email = mcwp_email_block('mha_cal5_email', 'mha_wpmc5_send_mail', 'mha_submit', $mcwp_modern);

if ($mcwp_modern) {
    $inputs = $f_income . $f_debts . $f_dp . $f_rate . $f_term
        . mcwp_more_fields($f_taxes . $f_insurance, __('Taxes and insurance', 'wpmc'), $curr_symbol);
    $summary = mcwp_summary_line(__('Based on %1$s yearly income · %2$s-year fixed at %3$s', 'wpmc'), $curr_symbol, '#mha_annual_income', '', '#mha_loan_term', '#mha_interest_rate');
} else {
    $inputs = $f_income . $f_debts . $f_taxes . $f_insurance . $f_dp . $f_rate . $f_term . $email;
    $summary = '';
}

$calTemplate2 .= '<div class="mcalc-layout">
      <div class="mcalc-main">' . $inputs . '</div>
      <div class="mcalc-values">
        <div class="mcalc-results">
          <h4>' . __('You can afford up to', 'wpmc') . '</h4>
          <h2 class="mcalc-value">' . $curr_symbol . '<span id="mha_afford_house_div_span"></span></h2>
          ' . $summary . '
          <div class="mcalc-breakdown" aria-hidden="true"></div>
          <p class="mcwp-pi">' . __('Monthly payment', 'wpmc') . ' <strong>' . $curr_symbol . '<span id="mha_emmp_div_span"></span></strong></p>
          <p class="mcwp-pi mcalc-part">' . __('Principal and interest', 'wpmc') . ' <strong>' . $curr_symbol . '<span id="mha_pi_div_span"></span></strong></p>
          <p class="mcwp-pi mcalc-part">' . __('Tax', 'wpmc') . ' <strong>' . $curr_symbol . '<span id="mha_taxes_div_span"></span></strong></p>
          <p class="mcwp-pi mcalc-part">' . __('Insurance', 'wpmc') . ' <strong>' . $curr_symbol . '<span id="mha_insurance_div_span"></span></strong></p>
          ' . ($mcwp_modern ? $email : '') . '
          <small>' . calc_fields('mha', 'wpmc_five_disclaimer', '') . '</small>
        </div>
      </div>
     </div>
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
