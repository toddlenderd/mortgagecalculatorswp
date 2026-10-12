<?php
// Only the new loan's rate defaults to today's average; the current rate is the borrower's existing loan
$rc_rate = mcwp_default_rate('rc', 'wpmc_six_nir_initial', '4');
$email = mcwp_email_block('rc_cal6_email', 'rc_wpmc6_send_mail', 'rc_submit', $mcwp_modern);
$summary = $mcwp_modern ? mcwp_summary_line(__('New %1$s loan · %2$s months at %3$s', 'wpmc'), $curr_symbol, '#rc_new_loan_amount', '', '#rc_new_loan_term', '#rc_new_interest_rate') : '';

$calTemplate2 .= '<div class="mcalc-layout">
    <div class="mcalc-main">
        <div class="mcalc-half">
            <label for="rc_original_loan_amount">' . calc_fields('rc', 'wpmc_six_la', __('Current loan amount', 'wpmc')) . '</label>
            <i>' . $curr_symbol . '</i>
            <input name="original_loan_amount" class="mha_input_data" autocomplete="off" currency="true" type="text" id="rc_original_loan_amount" value="' . calc_fields('rc', 'wpmc_six_la_initial', '300,000') . '" />
        </div>
        <div class="mcalc-half mha_main_wrapper">
            <label for="rc_interest_rate">' . calc_fields('rc', 'wpmc_six_ir', __('Interest rate', 'wpmc')) . '</label>
            <input name="interest_rate" autocomplete="off" currency="true" type="text" inputmode="decimal" id="rc_interest_rate" value="' . calc_fields('rc', 'wpmc_six_ir_initial', '5') . '" />
            <i>%</i>
        </div>
        <div class="mcalc-half mha_main_wrapper">
            <label for="rc_current_term">' . calc_fields('rc', 'wpmc_six_ct', __('Current term', 'wpmc')) . '</label>
            <input name="current_term" autocomplete="off" currency="true" type="text" inputmode="numeric" id="rc_current_term" value="' . calc_fields('rc', 'wpmc_six_ct_initial', '360') . '" />
            <i>' . __('months', 'wpmc') . '</i>
        </div>
        <div class="mcalc-half">
            <label for="rc_origination_year">' . calc_fields('rc', 'wpmc_six_oy', __('Origination year', 'wpmc')) . '</label>
            <input name="origination_year" autocomplete="off" currency="true" type="text" inputmode="numeric" id="rc_origination_year" value="' . calc_fields('rc', 'wpmc_six_oy_initial', '2015') . '" />
        </div>
        <div class="mcalc-half">
            <label for="rc_new_loan_amount">' . calc_fields('rc', 'wpmc_six_nla', __('New loan amount', 'wpmc')) . '</label>
            <i>' . $curr_symbol . '</i>
            <input name="new_loan_amount" class="mha_input_data" autocomplete="off" currency="true" type="text" id="rc_new_loan_amount" value="' . calc_fields('rc', 'wpmc_six_nla_initial', '250,000') . '" />
        </div>
        <div class="mcalc-half mha_main_wrapper">
            <label for="rc_new_interest_rate">' . calc_fields('rc', 'wpmc_six_nir', __('New interest rate', 'wpmc')) . '</label>
            <input name="new_interest_rate" autocomplete="off" currency="true" type="text" inputmode="decimal" id="rc_new_interest_rate" value="' . $rc_rate['rate'] . '" />
            <i>%</i>
            ' . $rc_rate['note'] . '
        </div>
        <div class="mcalc-half mha_main_wrapper">
            <label for="rc_new_loan_term">' . calc_fields('rc', 'wpmc_six_nlt', __('New loan term', 'wpmc')) . '</label>
            <input name="new_loan_term" autocomplete="off" currency="true" type="text" inputmode="numeric" id="rc_new_loan_term" value="' . calc_fields('rc', 'wpmc_six_nlt_initial', '360') . '" />
            <i>' . __('months', 'wpmc') . '</i>
        </div>
        <div class="mcalc-half">
            <label for="rc_new_refinance_fees">' . calc_fields('rc', 'wpmc_six_nrf', __('Refinance fees', 'wpmc')) . '</label>
            <i>' . $curr_symbol . '</i>
            <input name="new_refinance_fees" class="mha_input_data" autocomplete="off" currency="true" type="text" id="rc_new_refinance_fees" value="' . calc_fields('rc', 'wpmc_six_nrf_initial', '1,000') . '" />
        </div>
        ' . ($mcwp_modern ? '' : $email) . '
    </div>
    <div class="mcalc-values">
        <div class="mcalc-results">
            <h4>' . __('Refinancing could save you', 'wpmc') . '</h4>
            <h2 class="mcalc-value">' . $curr_symbol . '<span id="rc_afford_house_div_span"></span><em>/' . __('month', 'wpmc') . '</em></h2>
            ' . $summary . '
            <p class="mcwp-pi">' . __('New monthly payment', 'wpmc') . ' <strong>' . $curr_symbol . '<span id="rc_emmp_div_span"></span></strong></p>
            <p class="mcwp-pi">' . __('Refinance fees', 'wpmc') . ' <strong>' . $curr_symbol . '<span id="rc_pi_div_span"></span></strong></p>
            <p class="mcwp-pi">' . __('Lifetime savings', 'wpmc') . ' <strong>' . $curr_symbol . '<span id="rc_lifetime_div_span"></span></strong></p>
            ' . ($mcwp_modern ? $email : '') . '
            <small>' . calc_fields('rc', 'wpmc_six_disclaimer', '') . '</small>
        </div>
    </div>
</div>
  <input type="hidden" name="cal_result_home_afford" class="rc_afford_house_div_span" />
  <input type="hidden" name="new_monthly_payment" class="rc_emmp_div_span" />
  <input type="hidden" name="refinance_fees" class="rc_pi_div_span" />
  <input type="hidden" name="lifetime_value" class="rc_lifetime_div_span" />
  <input type="hidden" name="type" value="rc"/>
';
if ($status !== false && $status == 'valid' && $license !== false) {
    $calculator_layout = $calTemplate2;
} else {
    $calculator_layout = mcwp_unlicensed_notice();
}
