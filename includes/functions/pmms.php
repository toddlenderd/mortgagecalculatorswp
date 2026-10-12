<?php
/**
 * Freddie Mac Primary Mortgage Market Survey (PMMS): the weekly average 30- and 15-year fixed
 * rates, used as the default interest rate when neither the site nor the network admin has set one.
 *
 * The survey is fetched in the background (WP-Cron) and stored network-wide, so page views never
 * wait on it. Freddie Mac publishes on Thursdays; FRED mirrors the same series as a fallback.
 * Freddie Mac allows use with attribution, which the calculators show next to the rate.
 */

define('MCWP_PMMS_URL', 'https://www.freddiemac.com/pmms/docs/PMMS_history.csv');
define('MCWP_PMMS_FALLBACK_URL', 'https://fred.stlouisfed.org/graph/fredgraph.csv?id=MORTGAGE30US,MORTGAGE15US');

function mcwp_pmms_enabled()
{
    return (bool) apply_filters('mcwp_use_pmms', true);
}

/**
 * The stored survey: array('rate30' => 7.4, 'rate15' => 6.73, 'week' => '2026-10-08', 'fetched' => timestamp),
 * or null when it's turned off or hasn't been fetched yet.
 */
function mcwp_pmms()
{
    if (!mcwp_pmms_enabled()) {
        return null;
    }
    $pmms = get_site_option('mcwp_pmms');
    return (is_array($pmms) && !empty($pmms['rate30']) && !empty($pmms['week'])) ? $pmms : null;
}

/**
 * Fetch the latest survey unless the stored one is still this week's. On failure the last good
 * survey is kept.
 */
function mcwp_pmms_refresh($force = false)
{
    if (!mcwp_pmms_enabled()) {
        return;
    }
    $stored = mcwp_pmms();
    if (!$force && $stored && time() - strtotime($stored['week'] . ' 00:00:00 UTC') < 7 * DAY_IN_SECONDS) {
        return;
    }
    $latest = mcwp_pmms_fetch(MCWP_PMMS_URL);
    if (!$latest) {
        $latest = mcwp_pmms_fetch(MCWP_PMMS_FALLBACK_URL);
    }
    if ($latest) {
        $latest['fetched'] = time();
        update_site_option('mcwp_pmms', $latest);
    } else {
        write_log('[WPMC] Could not fetch the Freddie Mac weekly rate; keeping the last stored week.');
    }
}
add_action('mcwp_pmms_refresh', 'mcwp_pmms_refresh');

/**
 * Read the newest row from a PMMS CSV. Freddie Mac's file has "date,pmms30,...,pmms15" with dates
 * like 10/8/2026; FRED's has "observation_date,MORTGAGE30US,MORTGAGE15US" with 2026-10-08.
 */
function mcwp_pmms_fetch($url)
{
    $response = wp_safe_remote_get($url, array('timeout' => 15));
    if (is_wp_error($response) || 200 !== (int) wp_remote_retrieve_response_code($response)) {
        return null;
    }
    $lines = preg_split('/\r\n|\r|\n/', trim((string) wp_remote_retrieve_body($response)));
    if (count($lines) < 2) {
        return null;
    }
    // The escape argument is passed explicitly: PHP 8.4+ deprecates relying on its default
    $header = array_map('trim', str_getcsv(array_shift($lines), ',', '"', '\\'));
    $col30 = mcwp_pmms_column($header, array('pmms30', 'MORTGAGE30US'));
    $col15 = mcwp_pmms_column($header, array('pmms15', 'MORTGAGE15US'));
    if ($col30 === null) {
        return null;
    }
    for ($i = count($lines) - 1; $i >= 0; $i--) {
        $row = str_getcsv($lines[$i], ',', '"', '\\');
        $rate30 = isset($row[$col30]) ? trim($row[$col30]) : '';
        $week = mcwp_pmms_date(isset($row[0]) ? trim($row[0]) : '');
        // Skip blank or placeholder rows; a real rate is a plain number in a sane range
        if ($week === null || !is_numeric($rate30) || $rate30 < 1 || $rate30 > 20) {
            continue;
        }
        $rate15 = ($col15 !== null && isset($row[$col15]) && is_numeric(trim($row[$col15]))) ? (float) trim($row[$col15]) : null;
        return array('rate30' => (float) $rate30, 'rate15' => $rate15, 'week' => $week);
    }
    return null;
}

function mcwp_pmms_column($header, $names)
{
    foreach ($names as $name) {
        $index = array_search($name, $header, true);
        if ($index !== false) {
            return $index;
        }
    }
    return null;
}

// "10/8/2026" or "2026-10-08" -> "2026-10-08"
function mcwp_pmms_date($value)
{
    foreach (array('n/j/Y', 'Y-m-d') as $format) {
        $date = DateTime::createFromFormat('!' . $format, $value, new DateTimeZone('UTC'));
        if ($date && $date->format($format) === $value) {
            return $date->format('Y-m-d');
        }
    }
    return null;
}

/**
 * Default interest rate for a calculator: the admin's value for this field (the site's, or the
 * network admin's when the site has none), else this week's Freddie Mac 30-year average, else
 * $fallback. Returns the rate and, when it's the Freddie Mac average, the attribution to show.
 */
function mcwp_default_rate($calc, $field, $fallback)
{
    $value = trim((string) calc_fields($calc, $field, ''));
    if ($value === '' && is_multisite()) {
        $value = trim((string) get_site_option($field, ''));
    }
    if ($value !== '') {
        return array('rate' => $value, 'note' => '');
    }
    $pmms = mcwp_pmms();
    if (!$pmms) {
        return array('rate' => $fallback, 'note' => '');
    }
    $rate = rtrim(rtrim(number_format($pmms['rate30'], 3, '.', ''), '0'), '.');
    $note = sprintf(
        /* translators: %s: survey date, e.g. 10-08-2026 */
        __('Freddie Mac 30-year fixed average, week of %s', 'wpmc'),
        gmdate('m-d-Y', strtotime($pmms['week'] . ' 00:00:00 UTC'))
    );
    return array('rate' => $rate, 'note' => '<small class="mcalc-rate-source">' . esc_html($note) . '</small>');
}

// Admin placeholder for the "Interest Rate %" default fields
function mcwp_pmms_placeholder()
{
    $pmms = mcwp_pmms();
    if (!$pmms) {
        return __('Default: Freddie Mac weekly average', 'wpmc');
    }
    return sprintf(
        /* translators: 1: rate, e.g. 7.4; 2: survey date, e.g. 10-08-2026 */
        __('Default: Freddie Mac weekly average (%1$s%% for the week of %2$s)', 'wpmc'),
        rtrim(rtrim(number_format($pmms['rate30'], 3, '.', ''), '0'), '.'),
        gmdate('m-d-Y', strtotime($pmms['week'] . ' 00:00:00 UTC'))
    );
}

// Check twice a day (only fetches once the stored week is a week old). Scheduled from init so it
// self-heals on sites updated by replacing files, where activation hooks never run.
add_action('init', function () {
    if (mcwp_pmms_enabled() && !wp_next_scheduled('mcwp_pmms_refresh')) {
        wp_schedule_event(time() + MINUTE_IN_SECONDS, 'twicedaily', 'mcwp_pmms_refresh');
    }
});

// The first time an admin opens the plugin settings, fetch right away so the default shows up
add_action('admin_init', function () {
    if (isset($_GET['page']) && $_GET['page'] === 'wpmc' && current_user_can('manage_options') && !mcwp_pmms()) {
        mcwp_pmms_refresh(true);
    }
});
