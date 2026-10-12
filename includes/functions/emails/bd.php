<?php
// Recompute from the raw inputs rather than trusting results posted by the browser
$bd_number = function ($key) {
    $raw = str_replace(',', '', mcwp_post_text($key));
    return is_numeric($raw) ? max(0, (float) $raw) : 0;
};
$bd_loan = $bd_number('loan_amount');
$bd_rate = $bd_number('interest_rate');
$bd_term = (int) $bd_number('mortgage_term');
if (!in_array($bd_term, array(10, 15, 20, 25, 30), true)) {
    $bd_term = 30;
}
$bd_type = mcwp_post_text('buydown_type');
if (!array_key_exists($bd_type, mcwp_buydown_types())) {
    $bd_type = '2-1';
}
$bd = mcwp_buydown_calculate($bd_loan, $bd_rate, $bd_term, $bd_type, $bd_number('discount_points'), $bd_number('rate_per_point'));

$bd_money = function ($amount) use ($curr_symbol) {
    return $curr_symbol . number_format((float) $amount, 2);
};
$bd_percent = function ($rate) {
    return rtrim(rtrim(number_format((float) $rate, 3), '0'), '.') . '%';
};

$option_func = (use_network_settings('wpmc_four_use_network_settings') === 'yes') ? 'get_site_option' : 'get_option';
$wpmc_admin = $option_func('wpmc_four_email');

$subject = __('Your Buydown Calculation', 'wpmc');

$rows = array(
    array('label' => __('Loan Amount', 'wpmc'), 'value' => $bd_money($bd_loan)),
);
if ($bd['type'] === 'temporary') {
    foreach ($bd['schedule'] as $bd_year) {
        $rows[] = array(
            'label' => sprintf(__('Year %1$s at %2$s', 'wpmc'), $bd_year['year'], $bd_percent($bd_year['rate'])),
            'value' => $bd_money($bd_year['payment']),
        );
    }
    $rows[] = array(
        'label' => sprintf(__('Years %1$s-%2$s at %3$s', 'wpmc'), $bd['full_from'], $bd_term, $bd_percent($bd_rate)),
        'value' => $bd_money($bd['full_payment']),
    );
    $rows[] = array('label' => __('Total buydown cost', 'wpmc'), 'value' => $bd_money($bd['cost']));
    $calc_name = sprintf(__('%s Buydown', 'wpmc'), $bd_type);
    $total = $bd_money($bd['schedule'][0]['payment']);
    $total_label = __('Year 1 Payment', 'wpmc');
} else {
    $rows[] = array('label' => sprintf(__('Payment at %s', 'wpmc'), $bd_percent($bd_rate)), 'value' => $bd_money($bd['full_payment']));
    $rows[] = array('label' => __('New interest rate', 'wpmc'), 'value' => $bd_percent($bd['new_rate']));
    $rows[] = array('label' => __('Monthly savings', 'wpmc'), 'value' => $bd_money($bd['monthly_savings']));
    $rows[] = array('label' => __('Cost of points', 'wpmc'), 'value' => $bd_money($bd['cost']));
    $rows[] = array(
        'label' => __('Break-even', 'wpmc'),
        'value' => $bd['breakeven_months'] > 0 ? sprintf(__('%s months', 'wpmc'), $bd['breakeven_months']) : __('Never', 'wpmc'),
    );
    $calc_name = __('Permanent Buydown', 'wpmc');
    $total = $bd_money($bd['new_payment']);
    $total_label = __('New Monthly Payment', 'wpmc');
}

$email_args = array(
    'calc_type'      => $calc_name,
    'subtitle'       => $bd_term . ' ' . __('Year Fixed', 'wpmc'),
    'total'          => $total,
    'total_label'    => $total_label,
    'show_total_row' => false,
    'message'        => $wpmc_mail_message,
    'rows'           => $rows,
    'curr_symbol'    => $curr_symbol,
    'disclaimer'     => $option_func('wpmc_four_disclaimer'),
);
$body = mcwp_email_template($email_args);

$cc_subject = __('New Buydown Calculation by ', 'wpmc') . $to;
$cc_body = mcwp_cc_email_template($to, __('Buydown', 'wpmc'), $body);
