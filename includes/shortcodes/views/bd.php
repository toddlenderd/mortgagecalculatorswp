<?php
$bd_type_labels = array(
    '3-2-1'     => __('3-2-1 Buydown (temporary)', 'wpmc'),
    '2-1'       => __('2-1 Buydown (temporary)', 'wpmc'),
    '1-0'       => __('1-0 Buydown (temporary)', 'wpmc'),
    'permanent' => __('Permanent (discount points)', 'wpmc'),
);
$bd_type_options = '';
foreach ($bd_type_labels as $bd_value => $bd_label) {
    $bd_type_options .= '<option value="' . esc_attr($bd_value) . '"' . ($bd_value === '2-1' ? ' selected' : '') . '>' . esc_html($bd_label) . '</option>';
}

$calTemplate2 .= '<div class="mcalc-layout">
    <div class="mcalc-main">
        <div class="mcalc-half">
            <label for="bd_loan_amount">' . calc_fields('bd', 'wpmc_four_la', __('Loan Amount', 'wpmc')) . '</label>
            <i>' . $curr_symbol . '</i>
            <input name="loan_amount" class="mcalc-dollar bd-loan" autocomplete="off" type="text" inputmode="decimal" id="bd_loan_amount" value="' . esc_attr(calc_fields('bd', 'wpmc_four_la_initial', '400,000')) . '" />
        </div>
        <div class="mcalc-half mha_main_wrapper">
            <label for="bd_interest_rate">' . calc_fields('bd', 'wpmc_four_ir', __('Interest Rate', 'wpmc')) . '</label>
            <input name="interest_rate" class="bd-rate" autocomplete="off" type="text" inputmode="decimal" id="bd_interest_rate" value="' . esc_attr(calc_fields('bd', 'wpmc_four_ir_initial', '7')) . '" />
            <i>%</i>
        </div>
        <div class="mcalc-half">
            <label for="bd_loan_term">' . calc_fields('bd', 'wpmc_four_lt', __('Loan Term', 'wpmc')) . '</label>
            <select name="mortgage_term" class="bd-term" id="bd_loan_term">
                <option value="30">30 ' . __('Years', 'wpmc') . '</option>
                <option value="25">25 ' . __('Years', 'wpmc') . '</option>
                <option value="20">20 ' . __('Years', 'wpmc') . '</option>
                <option value="15">15 ' . __('Years', 'wpmc') . '</option>
                <option value="10">10 ' . __('Years', 'wpmc') . '</option>
            </select>
        </div>
        <div class="mcalc-half">
            <label for="bd_buydown_type">' . calc_fields('bd', 'wpmc_four_bt', __('Buydown Type', 'wpmc')) . '</label>
            <select name="buydown_type" class="bd-type" id="bd_buydown_type">' . $bd_type_options . '</select>
        </div>
        <div class="mcalc-half bd-permanent-only">
            <label for="bd_discount_points">' . calc_fields('bd', 'wpmc_four_dp', __('Discount Points', 'wpmc')) . '</label>
            <input name="discount_points" class="bd-points" autocomplete="off" type="text" inputmode="decimal" id="bd_discount_points" value="' . esc_attr(calc_fields('bd', 'wpmc_four_dp_initial', '1')) . '" />
        </div>
        <div class="mcalc-half mha_main_wrapper bd-permanent-only">
            <label for="bd_rate_per_point">' . calc_fields('bd', 'wpmc_four_rpp', __('Rate Reduction per Point', 'wpmc')) . '</label>
            <input name="rate_per_point" class="bd-per-point" autocomplete="off" type="text" inputmode="decimal" id="bd_rate_per_point" value="' . esc_attr(calc_fields('bd', 'wpmc_four_rpp_initial', '0.25')) . '" />
            <i>%</i>
        </div>
        <div class="mcalc-full mcwp-results">
            <label for="bd_cal4_email">' . __('Want a Copy of the Results?', 'wpmc') . '</label>
            <input type="email" id="bd_cal4_email" placeholder="' . __('Enter your email address', 'wpmc') . '" value="" name="email" />
            <input type="button" id="bd_wpmc4_send_mail" class="mcwp-submit bg bd_submit mcalc-color" value="' . __('Send Results!', 'wpmc') . '">
        </div>
    </div>
    <div class="mcalc-values">
        <div class="mcalc-results">
            <h4 class="bd-headline-label"
                data-temporary="' . esc_attr__('Year 1 Payment', 'wpmc') . '"
                data-permanent="' . esc_attr__('New Monthly Payment', 'wpmc') . '">' . __('Year 1 Payment', 'wpmc') . '</h4>
            <h2 class="mcalc-value">' . $curr_symbol . '<span class="bd-headline">0</span><em>/' . __('month', 'wpmc') . '</em></h2>
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
