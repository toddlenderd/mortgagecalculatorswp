<?php
// data-short is the button label when Modern shows the type as a row of buttons
$bd_types = array(
    '3-2-1'     => array(__('3-2-1 Buydown (temporary)', 'wpmc'), '3-2-1'),
    '2-1'       => array(__('2-1 Buydown (temporary)', 'wpmc'), '2-1'),
    '1-0'       => array(__('1-0 Buydown (temporary)', 'wpmc'), '1-0'),
    'permanent' => array(__('Permanent (discount points)', 'wpmc'), __('Points', 'wpmc')),
);
$bd_type_options = '';
foreach ($bd_types as $bd_value => $bd_type) {
    $bd_type_options .= '<option value="' . esc_attr($bd_value) . '" data-short="' . esc_attr($bd_type[1]) . '"' . ($bd_value === '2-1' ? ' selected' : '') . '>' . esc_html($bd_type[0]) . '</option>';
}

$f_loan = '<div class="mcalc-half">
            <label for="bd_loan_amount">' . calc_fields('bd', 'wpmc_four_la', __('Loan amount', 'wpmc')) . '</label>
            <i>' . $curr_symbol . '</i>
            <input name="loan_amount" class="mcalc-dollar bd-loan" autocomplete="off" type="text" inputmode="decimal" id="bd_loan_amount" value="' . esc_attr(calc_fields('bd', 'wpmc_four_la_initial', '400,000')) . '" />
        </div>';
$bd_rate = mcwp_default_rate('bd', 'wpmc_four_ir_initial', '7');
$f_rate = '<div class="mcalc-half mha_main_wrapper">
            <label for="bd_interest_rate">' . calc_fields('bd', 'wpmc_four_ir', __('Interest rate', 'wpmc')) . '</label>
            <input name="interest_rate" class="bd-rate" autocomplete="off" type="text" inputmode="decimal" id="bd_interest_rate" value="' . esc_attr($bd_rate['rate']) . '" />
            <i>%</i>
            ' . $bd_rate['note'] . '
        </div>';
$f_term = '<div class="mcalc-half mcalc-seg-field">
            <label for="bd_loan_term">' . calc_fields('bd', 'wpmc_four_lt', __('Loan term', 'wpmc')) . '</label>
            <select name="mortgage_term" class="bd-term" id="bd_loan_term" data-mcalc-seg data-unit="' . esc_attr__('yr', 'wpmc') . '">
                <option value="30">30 ' . __('Years', 'wpmc') . '</option>
                <option value="25">25 ' . __('Years', 'wpmc') . '</option>
                <option value="20">20 ' . __('Years', 'wpmc') . '</option>
                <option value="15">15 ' . __('Years', 'wpmc') . '</option>
                <option value="10">10 ' . __('Years', 'wpmc') . '</option>
            </select>
        </div>';
$f_type = '<div class="mcalc-half mcalc-seg-field">
            <label for="bd_buydown_type">' . calc_fields('bd', 'wpmc_four_bt', __('Buydown type', 'wpmc')) . '</label>
            <select name="buydown_type" class="bd-type" id="bd_buydown_type" data-mcalc-seg>' . $bd_type_options . '</select>
        </div>';
$f_points = '<div class="mcalc-half bd-permanent-only">
            <label for="bd_discount_points">' . calc_fields('bd', 'wpmc_four_dp', __('Discount points', 'wpmc')) . '</label>
            <input name="discount_points" class="bd-points" autocomplete="off" type="text" inputmode="decimal" id="bd_discount_points" value="' . esc_attr(calc_fields('bd', 'wpmc_four_dp_initial', '1')) . '" />
        </div>
        <div class="mcalc-half mha_main_wrapper bd-permanent-only">
            <label for="bd_rate_per_point">' . calc_fields('bd', 'wpmc_four_rpp', __('Rate reduction per point', 'wpmc')) . '</label>
            <input name="rate_per_point" class="bd-per-point" autocomplete="off" type="text" inputmode="decimal" id="bd_rate_per_point" value="' . esc_attr(calc_fields('bd', 'wpmc_four_rpp_initial', '0.25')) . '" />
            <i>%</i>
        </div>';

$email = mcwp_email_block('bd_cal4_email', 'bd_wpmc4_send_mail', 'bd_submit', $mcwp_modern);
$summary = $mcwp_modern ? mcwp_summary_line(__('%1$s loan · %2$s-year fixed at %3$s', 'wpmc'), $curr_symbol, '.bd-loan', '', '.bd-term', '.bd-rate') : '';
$inputs = $f_loan . $f_rate . ($mcwp_modern ? $f_type . $f_term : $f_term . $f_type) . $f_points . ($mcwp_modern ? '' : $email);

$calTemplate2 .= '<div class="mcalc-layout">
    <div class="mcalc-main">' . $inputs . '</div>
    <div class="mcalc-values">
        <div class="mcalc-results">
            <h4 class="bd-headline-label"
                data-temporary="' . esc_attr__('Year 1 payment', 'wpmc') . '"
                data-permanent="' . esc_attr__('New monthly payment', 'wpmc') . '">' . __('Year 1 payment', 'wpmc') . '</h4>
            <h2 class="mcalc-value">' . $curr_symbol . '<span class="bd-headline">0</span><em>/' . __('month', 'wpmc') . '</em></h2>
            ' . $summary . '
            <div class="bd-chart" aria-hidden="true" data-label-year="' . esc_attr__('Yr %s', 'wpmc') . '" data-label-rest="' . esc_attr__('Yr %s+', 'wpmc') . '"></div>
            <div class="bd-rows" data-currency="' . esc_attr($curr_symbol) . '"
                data-label-year="' . esc_attr__('Year %1$s at %2$s', 'wpmc') . '"
                data-label-rest="' . esc_attr__('Years %1$s-%2$s at %3$s', 'wpmc') . '"
                data-label-cost="' . esc_attr__('Total buydown cost', 'wpmc') . '"
                data-label-new-rate="' . esc_attr__('New interest rate', 'wpmc') . '"
                data-label-original="' . esc_attr__('Payment at %s', 'wpmc') . '"
                data-label-savings="' . esc_attr__('Monthly savings', 'wpmc') . '"
                data-label-points-cost="' . esc_attr__('Cost of points', 'wpmc') . '"
                data-label-breakeven="' . esc_attr__('Break-even', 'wpmc') . '"
                data-label-months="' . esc_attr__('%s months', 'wpmc') . '"
                data-label-never="' . esc_attr__('Never', 'wpmc') . '"></div>
            ' . ($mcwp_modern ? $email : '') . '
            <small>' . calc_fields('bd', 'wpmc_four_disclaimer', '') . '</small>
        </div>
    </div>
</div>
<input type="hidden" name="type" value="bd"/>';

if ($status !== false && $status == 'valid' && $license !== false) {
    $calculator_layout = $calTemplate2;
} else {
    $calculator_layout = mcwp_unlicensed_notice();
}
