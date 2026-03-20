<?php

function wpmc_main_template()
{

	$license = get_wpmc_option('edd_wpmc_license_key');

	$status  = get_wpmc_option('edd_wpmc_license_status');



	echo get_site_option('edd_wpmc_license_key');



	$cal_one_screen = (isset($_GET['action']) && 'cal-one' == $_GET['action']) ? true : false;

	$cal_two_Screen = (isset($_GET['action']) && 'cal-two' == $_GET['action']) ? true : false;

	$cal_three_Screen = (isset($_GET['action']) && 'cal-three' == $_GET['action']) ? true : false;

	$cal_four_Screen = (isset($_GET['action']) && 'cal-four' == $_GET['action']) ? true : false;



	$license_Screen = (isset($_GET['action']) && 'license' == $_GET['action']) ? true : false;



	$admin_url = (is_network_admin() ? 'network/admin.php?page=wpmc' : 'admin.php?page=wpmc');

	// show error/update messages

	settings_errors('wporg_messages');

?>

	<div class="wrap">

		<h1 id="header_tag">

			<?php

			if ($cal_one_screen) {

				echo 'Conventional Mortgage Calculator';
			} elseif ($cal_two_Screen) {

				echo 'FHA Mortgage Calculator';
			} elseif ($cal_three_Screen) {

				echo 'VA Mortgage Calculator';
			} elseif ($license_Screen) {

				echo 'License Options';
			} else {

				echo 'General Settings';
			}

			?>

		</h1>

		<h2 class="nav-tab-wrapper">

			<a href="<?php echo admin_url($admin_url); ?>" class="nav-tab<?php if (!isset($_GET['action']) || isset($_GET['action']) && 'cal-one' != $_GET['action']  && 'cal-two' != $_GET['action']  && 'cal-three' != $_GET['action']  && 'cal-four' != $_GET['action'] && 'license' != $_GET['action']) echo ' nav-tab-active'; ?>"><?php esc_html_e('Mail'); ?></a>

			<a href="<?php echo esc_url(add_query_arg(array('action' => 'cal-one'), admin_url($admin_url))); ?>" class="nav-tab<?php if ($cal_one_screen) echo ' nav-tab-active'; ?>"><?php esc_html_e('Conventional Calc'); ?></a>

			<a href="<?php echo esc_url(add_query_arg(array('action' => 'cal-two'), admin_url($admin_url))); ?>" class="nav-tab<?php if ($cal_two_Screen) echo ' nav-tab-active'; ?>"><?php esc_html_e('FHA Calc'); ?></a>

			<a href="<?php echo esc_url(add_query_arg(array('action' => 'cal-three'), admin_url($admin_url))); ?>" class="nav-tab<?php if ($cal_three_Screen) echo ' nav-tab-active'; ?>"><?php esc_html_e('VA Calc'); ?></a>

			<!--<a href="<?php echo esc_url(add_query_arg(array('action' => 'cal-four'), admin_url($admin_url))); ?>" class="nav-tab<?php if (cal_four_Screen) echo ' nav-tab-active'; ?>"><?php esc_html_e('Calculator 4'); ?></a>-->

			<a href="<?php echo esc_url(add_query_arg(array('action' => 'license'), admin_url($admin_url))); ?>" class="nav-tab<?php if ($license_Screen) echo ' nav-tab-active'; ?>"><?php esc_html_e('Premium License'); ?></a>

		</h2>



		<div class="wrap">

			<p id="settings_errors"><?php settings_errors(); ?></p>

			<form method="post" action="<?php echo (is_network_admin() ?  'edit.php?action=wpmc_update_network_options' : 'options.php') ?>"><?php

																																																																				if ($cal_one_screen) {

																																																																					settings_fields('wpmc_one');

																																																																					do_settings_sections('wpmc-settings-one');

																																																																					submit_button();
																																																																				} elseif ($cal_two_Screen) {



																																																																					settings_fields('wpmc_two');

																																																																					do_settings_sections('wpmc-settings-two');

																																																																					submit_button();



																																																																					/*

					if($status !== false && $status == 'valid' && $license !== false){

						settings_fields( 'wpmc_two' );

						do_settings_sections( 'wpmc-settings-two' );

						submit_button();

					} else {

						echo '<p style="color: red; border: 2px solid red; padding: 40px; margin: 100px auto; text-align: center; width: 50%;"><strong style="color: red;">Premium License Required to Use this Calculator</strong></p>';

					}

					*/
																																																																				} elseif ($cal_three_Screen) {



																																																																					if ($status !== false && $status == 'valid' && $license !== false) {

																																																																						settings_fields('wpmc_three');

																																																																						do_settings_sections('wpmc-settings-three');

																																																																						submit_button();
																																																																					} else {

																																																																						echo '<p style="color: red; border: 2px solid red; padding: 40px; margin: 100px auto; text-align: center; width: 50%;"><strong style="color: red;">Premium License Required to Use this Calculator</strong></p>';
																																																																					}
																																																																				} elseif ($cal_four_Screen) {

																																																																					if ($status !== false && $status == 'valid' && $license !== false) {

																																																																						echo '<p><strong>Coming Soon</strong></p>';
																																																																					} else {

																																																																						echo '<p style="color: red; border: 2px solid red; padding: 40px; margin: 100px auto; text-align: center; width: 50%;"><strong style="color: red;">Premium License Required to Use this Calculator</strong></p>';
																																																																					}
																																																																				} elseif ($license_Screen) {

																																																																					settings_fields('wpmc_license');

																																																																					do_settings_sections(WPMC_EDD_LICENSE_PAGE);

																																																																					WPMC_EDD_LICENSE_PAGE();
																																																																				} else {

																																																																					settings_fields('wpmc_mail');

																																																																					do_settings_sections('wpmc-settings-mail');

																																																																					submit_button();
																																																																				}

																																																																				?>

			</form>

			<script>
				var is_multisite = '<?php echo is_multisite() ? true : false; ?>';

				var is_network_admin = '<?php echo is_network_admin() ? true : false; ?>';



				<?php if (empty($_GET['action'])) { ?>





					<?php

					$options = get_wpmc_option('wpmc_mail_use_network_settings');

					$val = ($options == '0') ? '0' : '1';

					?>

					var wpmc_mail_use_network_settings = '<?php echo $val; ?>';



					<?php

					$wpmc_mail_use_network_settings = get_wpmc_option('wpmc_mail_use_network_settings');

					if ($wpmc_mail_use_network_settings !== false) {

					?>

						if (is_multisite && !is_network_admin && wpmc_mail_use_network_settings == '0') {

							jQuery('.wpmc_mail').not(':first').parents('tr').hide();

						}

					<?php } else { ?>

						if (is_multisite && !is_network_admin) {

							jQuery('.wpmc_mail').not(':first').parents('tr').hide();

						}

					<?php } ?>

					jQuery('input[name="wpmc_mail_use_network_settings"]').click(function() {

						if (jQuery(this).is(':checked')) {

							jQuery(this).val('0');

						} else {

							jQuery(this).val('1');

						}

						jQuery('.wpmc_mail').not(':first').parents('tr').toggle();

					});

				<?php } ?>



				<?php if ($cal_one_screen) { ?>

					jQuery('input[name="wpmc_one_email"]').parents('tr').wrap("<div class='mail_one_heading'></div>");

					jQuery('.mail_one_heading').prepend('<tr><td colspan="2"><p class="wpmc_one_label" style="border-left: 2px solid #008ec2; padding-left: 7px; background: #fff; margin-left: -10px;">Edit your calculator\'s email address & message body</p></td></tr>');

					jQuery('.mail_one_heading').prepend('<h2 class="wpmc_one_label">Email Settings</h2>');

					jQuery('input[name="wpmc_one_email"]').parents('tr').unwrap();



					jQuery('input[name="wpmc_one_pp"]').parents('tr').wrap("<div class='label_one_heading'></div>");

					jQuery('.label_one_heading').prepend('<p class="wpmc_one_label" style="border-left: 2px solid #008ec2; padding-left: 7px; background: #fff;">Edit your calculator\'s labels</strong></p>');

					jQuery('.label_one_heading').prepend('<h2 class="wpmc_one_label">Label Settings</h2>');

					jQuery('input[name="wpmc_one_pp"]').parents('tr').unwrap();



					jQuery('input[name="wpmc_one_pp_initial"]').parents('tr').wrap("<div class='value_one_heading'></div>");

					jQuery('.value_one_heading').prepend('<p class="wpmc_one_label" style="border-left: 2px solid #008ec2; padding-left: 7px; background: #fff;">Edit your calculator\'s initial values</p>');

					jQuery('.value_one_heading').prepend('<h2 class="wpmc_one_label">Initial Value Settings</h2>');

					jQuery('input[name="wpmc_one_pp_initial"]').parents('tr').unwrap();










					<?php

					$options = get_wpmc_option('wpmc_one_use_network_settings');

					$val = ($options == '0') ? '0' : '1';

					?>

					var wpmc_one_use_network_settings = '<?php echo $val; ?>';

					<?php

					$wpmc_one_use_network_settings = get_wpmc_option('wpmc_one_use_network_settings');

					if ($wpmc_one_use_network_settings !== false) {

					?>

						if (is_multisite && !is_network_admin && wpmc_one_use_network_settings == '0') {

							jQuery('.wpmc_one').not(':first').parents('tr').hide();

							jQuery('.wpmc_one_label').hide();

						}

					<?php } else { ?>

						if (is_multisite && !is_network_admin) {

							jQuery('.wpmc_one').not(':first').parents('tr').hide();

							jQuery('.wpmc_one_label').hide();

						}

					<?php } ?>

					jQuery('input[name="wpmc_one_use_network_settings"]').click(function() {

						if (jQuery(this).is(':checked')) {

							jQuery(this).val('0');

						} else {

							jQuery(this).val('1');

						}

						jQuery('.wpmc_one').not(':first').parents('tr').toggle();

						jQuery('.wpmc_one_label').toggle();

					});

				<?php } ?>



				<?php if ($cal_two_Screen) { ?>

					jQuery('input[name="wpmc_two_email"]').parents('tr').wrap("<div class='mail_two_heading'></div>");

					jQuery('.mail_two_heading').prepend('<tr><td colspan="2"><p class="wpmc_two_label" style="border-left: 2px solid #008ec2; padding-left: 7px; background: #fff; margin-left: -10px;">Edit your calculator\'s email address & message body</p></td></tr>');

					jQuery('.mail_two_heading').prepend('<h2 class="wpmc_two_label">Email Settings</h2>');

					jQuery('input[name="wpmc_two_email"]').parents('tr').unwrap();



					jQuery('input[name="wpmc_two_pp"]').parents('tr').wrap("<div class='label_two_heading'></div>");

					jQuery('.label_two_heading').prepend('<p class="wpmc_two_label" style="border-left: 2px solid #008ec2; padding-left: 7px; background: #fff;">Edit your calculator\'s labels</p>');

					jQuery('.label_two_heading').prepend('<h2 class="wpmc_two_label">Label Settings</h2>');

					jQuery('input[name="wpmc_two_pp"]').parents('tr').unwrap();



					jQuery('input[name="wpmc_two_pp_initial"]').parents('tr').wrap("<div class='value_two_heading'></div>");

					jQuery('.value_two_heading').prepend('<p class="wpmc_two_label" style="border-left: 2px solid #008ec2; padding-left: 7px; background: #fff;">Edit your calculator\'s initial values</p>');

					jQuery('.value_two_heading').prepend('<h2 class="wpmc_two_label">Initial Value Settings</h2>');

					jQuery('input[name="wpmc_two_pp_initial"]').parents('tr').unwrap();






					<?php

					$options = get_wpmc_option('wpmc_two_use_network_settings');

					$val = ($options == '0') ? '0' : '1';

					?>

					var wpmc_two_use_network_settings = '<?php echo $val; ?>';

					<?php

					$wpmc_two_use_network_settings = get_wpmc_option('wpmc_two_use_network_settings');

					if ($wpmc_two_use_network_settings !== false) {

					?>

						if (is_multisite && !is_network_admin && wpmc_two_use_network_settings == '0') {

							jQuery('.wpmc_two').not(':first').parents('tr').hide();

							jQuery('.wpmc_two_label').hide();

						}

					<?php } else { ?>

						if (is_multisite && !is_network_admin) {

							jQuery('.wpmc_two').not(':first').parents('tr').hide();

							jQuery('.wpmc_two_label').hide();

						}

					<?php } ?>

					jQuery('input[name="wpmc_two_use_network_settings"]').click(function() {

						if (jQuery(this).is(':checked')) {

							jQuery(this).val('0');

						} else {

							jQuery(this).val('1');

						}

						jQuery('.wpmc_two').not(':first').parents('tr').toggle();

						jQuery('.wpmc_two_label').toggle();

					});

				<?php } ?>



				<?php if ($cal_three_Screen) { ?>

					jQuery('input[name="wpmc_three_email"]').parents('tr').wrap("<div class='mail_three_heading'></div>");

					jQuery('.mail_three_heading').prepend('<tr><td colspan="2"><p class="wpmc_three_label" style="border-left: 2px solid #008ec2; padding-left: 7px; background: #fff; margin-left: -10px;">Edit your calculator\'s email address & message body</p></td></tr>');

					jQuery('.mail_three_heading').prepend('<h2 class="wpmc_three_label">Email Settings</h2>');

					jQuery('input[name="wpmc_three_email"]').parents('tr').unwrap();



					jQuery('input[name="wpmc_three_ftvl"]').parents('tr').wrap("<div class='label_three_heading'></div>");

					jQuery('.label_three_heading').prepend('<p class="wpmc_three_label" style="border-left: 2px solid #008ec2; padding-left: 7px; background: #fff;">Edit your calculator\'s labels</p>');

					jQuery('.label_three_heading').prepend('<h2 class="wpmc_three_label">Label Settings</h2>');

					jQuery('input[name="wpmc_three_ftvl"]').parents('tr').unwrap();



					jQuery('input[name="wpmc_three_pp_initial"]').parents('tr').wrap("<div class='value_three_heading'></div>");

					jQuery('.value_three_heading').prepend('<p class="wpmc_three_label" style="border-left: 2px solid #008ec2; padding-left: 7px; background: #fff;">Edit your calculator\'s initial values</p>');

					jQuery('.value_three_heading').prepend('<h2 class="wpmc_three_label">Initial Value Settings</h2>');

					jQuery('input[name="wpmc_three_pp_initial"]').parents('tr').unwrap();






					<?php

					$options = get_wpmc_option('wpmc_two_use_network_settings');

					$val = ($options == '0') ? '0' : '1';

					?>

					var wpmc_three_use_network_settings = '<?php echo $val; ?>';

					<?php

					$wpmc_three_use_network_settings = get_wpmc_option('wpmc_three_use_network_settings');

					if ($wpmc_three_use_network_settings !== false) {

					?>

						if (is_multisite && !is_network_admin && wpmc_three_use_network_settings == '0') {

							jQuery('.wpmc_three').not(':first').parents('tr').hide();

							jQuery('.wpmc_three_label').hide();

						}

					<?php } else { ?>

						if (is_multisite && !is_network_admin) {

							jQuery('.wpmc_three').not(':first').parents('tr').hide();

							jQuery('.wpmc_three_label').hide();

						}

					<?php } ?>

					jQuery('input[name="wpmc_three_use_network_settings"]').click(function() {

						if (jQuery(this).is(':checked')) {

							jQuery(this).val('0');

						} else {

							jQuery(this).val('1');

						}

						jQuery('.wpmc_three').not(':first').parents('tr').toggle();

						jQuery('.wpmc_three_label').toggle();

					});

				<?php } ?>
			</script>

		</div>

	</div> <?php

				}



				function wpmc_calculator_template1()
				{

					$uns = get_option('wpmc_one_use_network_settings');

					$option_func = (($uns === false) ? 'get_site_option' : (($uns == 1) ? 'get_site_option' : 'get_option'));



					$wpmc_one_pp = $option_func('wpmc_one_pp');

					$wpmc_one_dp = $option_func('wpmc_one_dp');

					$wpmc_one_ir = $option_func('wpmc_one_ir');

					$wpmc_one_mt = $option_func('wpmc_one_mt');

					$wpmc_one_at = $option_func('wpmc_one_at');

					$wpmc_one_ai = $option_func('wpmc_one_ai');

					$wpmc_one_mhoa = $option_func('wpmc_one_mhoa');



					$wpmc_one_pp_initial = $option_func('wpmc_one_pp_initial');

					$wpmc_one_dp_max = $option_func('wpmc_one_dp_max');

					$wpmc_one_dp_min = $option_func('wpmc_one_dp_min');

					$wpmc_one_dp_initial = $option_func('wpmc_one_dp_initial');

					$wpmc_one_ir_max = $option_func('wpmc_one_ir_max');

					$wpmc_one_ir_min = $option_func('wpmc_one_ir_min');

					$wpmc_one_ir_initial = $option_func('wpmc_one_ir_initial');

					$wpmc_one_at_max = $option_func('wpmc_one_at_max');

					$wpmc_one_at_min = $option_func('wpmc_one_at_min');

					$wpmc_one_at_initial = $option_func('wpmc_one_at_initial');

					//$wpmc_one_mt_initial = $option_func('wpmc_one_mt_initial');

					$wpmc_one_ai_initial = $option_func('wpmc_one_ai_initial');

					$wpmc_one_mhoa_initial = $option_func('wpmc_one_mhoa_initial');



					$wpmc_email = $option_func('wpmc_one_email');

					$admin_email = $option_func('admin_email');

					$wpmc_one_email = (!empty($wpmc_email) && $wpmc_email === '[email]') ? $admin_email : ((!empty($wpmc_email) && $wpmc_email !== '[email]') ? $wpmc_email : $admin_email);



					$calTemplate1 = '

				<div class="mcalc mcalc-conventional">

					<div class="col-md-6 col-lg-7">

						<div class="row">

							<div class="col-sm-12">

								<label>' . ((!empty($wpmc_one_pp)) ? $wpmc_one_pp : 'Purchase Price') . '($)</label>

								<input type="text" id="inp_purchase_price" value="' . ((!empty($wpmc_one_pp_initial)) ? $wpmc_one_pp_initial : '250,000') . '">

							</div>

							<div class="col-sm-6">

								<label>' . ((!empty($wpmc_one_dp)) ? $wpmc_one_dp : 'Down Payment') . ' (%)</label>

								<input id="ex1" class="ex1 down_payment_scrl" data-slider-id="ex1Slider" type="text" data-slider-min="' . ((!empty($wpmc_one_dp_min)) ? $wpmc_one_dp_min : 0) . '" data-slider-max="' . ((!empty($wpmc_one_dp_max)) ? $wpmc_one_dp_max : 100) . '" data-slider-step="1" data-slider-value="10"/>

								<p>10%</p>

							</div>

							<div class="col-sm-6">

								<label>' . ((!empty($wpmc_one_dp)) ? $wpmc_one_dp : 'Down Payment') . ' ($)</label>

								<input type="text" id="down_payment_inp" value="' . ((!empty($wpmc_one_dp_initial)) ? $wpmc_one_dp_initial : 600) . '">

							</div>

						</div>



						<div class="row">

							<div class="col-sm-6">

								<label>' . ((!empty($wpmc_one_ir)) ? $wpmc_one_ir : 'Interest Rate') . ' (%)</label>

								<input id="ex1" class="ex1 interest_rate_scrl" data-slider-id="ex1Slider" type="text" data-slider-min="' . ((!empty($wpmc_one_ir_min)) ? $wpmc_one_ir_min : 1) . '" data-slider-max="' . ((!empty($wpmc_one_ir_max)) ? $wpmc_one_ir_max : 10) . '" data-slider-step=".125" data-slider-value="' . ((!empty($wpmc_one_ir_initial)) ? $wpmc_one_ir_initial : 4) . '"/>

								<p>4%</p>

							</div>

							<div class="col-sm-6">								

								<label class="">' . ((!empty($wpmc_one_mt)) ? $wpmc_one_mt : 'Mortgage Term (Year)') . '</label>



								<select id="mortgage_term_yr">

								  <option value="15">15</option>

								  <option value="30">30</option>

  								</select>



							</div>

						</div>



						<div class="row">

							<div class="col-sm-6">

								<label>' . ((!empty($wpmc_one_at)) ? $wpmc_one_at : 'Annual Taxes') . ' (%)</label>

								<input id="ex1" class="ex1 annual_tax_scrl" data-slider-id="ex1Slider" type="text" data-slider-min="' . ((!empty($wpmc_one_at_min)) ? $wpmc_one_at_min : 0) . '" data-slider-max="' . ((!empty($wpmc_one_at_max)) ? $wpmc_one_at_max : 5) . '" data-slider-step="0.1" data-slider-value="1"/>

								<p>1%</p>

							</div>

							<div class="col-sm-6">

								<label >' . ((!empty($wpmc_one_at)) ? $wpmc_one_at : 'Annual Taxes') . ' ($)</label>

								<input type="text" id="annual_tax_inp" value="' . ((!empty($wpmc_one_at_initial)) ? $wpmc_one_at_initial : 20) . '">

							</div>

						</div>



						<div class="row">

							<div class="col-sm-6">

								<label>' . ((!empty($wpmc_one_ai)) ? $wpmc_one_ai : 'Annual Insurance') . ' ($)</label>

								<input type="text" id="annual_insurance_inp" value="' . ((!empty($wpmc_one_ai_initial)) ? $wpmc_one_ai_initial : 600) . '">

							</div>

							<div class="col-sm-6">

								<label >' . ((!empty($wpmc_one_mhoa)) ? $wpmc_one_mhoa : 'Monthly HOA') . '</label>

								<input type="text" id="monthly_hoa_inp" value="' . ((!empty($wpmc_one_mhoa_initial)) ? $wpmc_one_mhoa_initial : 50) . '">

							</div>

						</div>

					</div>

					<div class="col-md-6 col-lg-5">

						<div class="mcalc-results">

							<h2 class="color">$<span id="emmp_div_span">1421</span></h2>

							<p>Est. Monthly Payment</p>

							<h4 class="color2">Payment Breakdown</h4>

							<p>Principal & Interest: $<span id="pi_div_span" class="color">1421</span></p>

							<p>Monthly Taxes: $<span id="mtax_div_span" class="color">1421</span></p>

							<p>Monthly Insurance: $<span id="minsure_div_span" class="color">1421</span></p>

							<!--<p>Monthly Mortgage Insurance: $<span id="mmi_div_span" class="color">0</span></p>-->

							<p>Monthly HOA: $<span id="hoa_div_span" class="color">1421</span></p>

							<h5>Want a Copy of the Results?</h5>

							<input type="email" id="cal1_email" placeholder="Enter your email address" value="" />

							<input type="button" id="wpmc1_send_mail" class="wpmc-submit bg" value="Send Results!">

						</div>

					</div>

				</div>';



					echo $calTemplate1;
				}



				function wpmc_calculator_template2()
				{

					$uns = get_option('wpmc_two_use_network_settings');

					$option_func = (($uns === false) ? 'get_site_option' : (($uns == 1) ? 'get_site_option' : 'get_option'));



					$license = $option_func('edd_wpmc_license_key');

					$status  = $option_func('edd_wpmc_license_status');



					$wpmc_two_pp = $option_func('wpmc_two_pp');

					$wpmc_two_dp = $option_func('wpmc_two_dp');

					$wpmc_two_ir = $option_func('wpmc_two_ir');

					$wpmc_two_mt = $option_func('wpmc_two_mt');

					$wpmc_two_at = $option_func('wpmc_two_at');

					$wpmc_two_ai = $option_func('wpmc_two_ai');

					$wpmc_two_mhoa = $option_func('wpmc_two_mhoa');



					$wpmc_two_pp_initial = $option_func('wpmc_two_pp_initial');

					$wpmc_one_dp_max = $option_func('wpmc_two_dp_max');

					$wpmc_two_dp_min = $option_func('wpmc_two_dp_min');



					$wpmc_two_dp_initial = $option_func('wpmc_two_dp_initial');

					$wpmc_two_ir_max = $option_func('wpmc_two_ir_max');

					$wpmc_two_ir_min = $option_func('wpmc_two_ir_min');



					$wpmc_two_ir_initial = $option_func('wpmc_two_ir_initial');

					$wpmc_two_at_max = $option_func('wpmc_two_at_max');

					$wpmc_two_at_min = $option_func('wpmc_two_at_min');

					$wpmc_two_at_initial = $option_func('wpmc_two_at_initial');

					//$wpmc_two_mt_initial = $option_func('wpmc_two_mt_initial');

					$wpmc_two_ai_initial = $option_func('wpmc_two_ai_initial');

					$wpmc_two_mhoa_initial = $option_func('wpmc_two_mhoa_initial');



					$wpmc_email = $option_func('wpmc_two_email');

					$admin_email = $option_func('admin_email');

					$wpmc_two_email = (!empty($wpmc_email) && $wpmc_email === '[email]') ? $admin_email : ((!empty($wpmc_email) && $wpmc_email !== '[email]') ? $wpmc_email : $admin_email);



					$calTemplate2 = '

				<div class="mcalc mcalc-fha">	

					<div class="col-md-6 col-lg-7">

						<div class="row">

							<div class="col-sm-12">

								<label>' . ((!empty($wpmc_two_pp)) ? $wpmc_two_pp : 'Purchase Price') . '($)</label>

								<input type="text" id="fha_inp_purchase_price" value="' . ((!empty($wpmc_two_pp_initial)) ? $wpmc_two_pp_initial : '250,000') . '">

							</div>



							<div class="col-sm-6">

								<label>' . ((!empty($wpmc_two_dp)) ? $wpmc_two_dp : 'Down Payment') . ' (%)</label>

								<input id="ex1" class="ex1 fha_down_payment_scrl" data-slider-id="ex1Slider" type="text" data-slider-min="' . ((!empty($wpmc_two_dp_min)) ? $wpmc_two_dp_min : '0') . '" data-slider-max="' . ((!empty($wpmc_one_dp_max)) ? $wpmc_one_dp_max : '100') . '" data-slider-step=".5" data-slider-value="10"/>

								<p>10%</p>

							</div>

							<div class="col-sm-6">

								<label>' . ((!empty($wpmc_two_dp)) ? $wpmc_two_dp : 'Down Payment') . ' ($)</label>

								<input type="text" id="fha_down_payment_inp" value="' . ((!empty($wpmc_two_dp_initial)) ? $wpmc_two_dp_initial : '600') . '">

							</div>

						</div>



						<div class="row">

							<div class="col-sm-6">

								<label>' . ((!empty($wpmc_two_ir)) ? $wpmc_two_ir : 'Interest Rate') . ' (%)</label>

								<input id="ex1" class="ex1 fha_interest_rate_scrl" data-slider-id="ex1Slider" type="text" data-slider-min="' . ((!empty($wpmc_two_ir_min)) ? $wpmc_two_ir_min : '5') . '" data-slider-max="' . ((!empty($wpmc_two_ir_max)) ? $wpmc_two_ir_max : '10') . '" data-slider-step=".125" data-slider-value="4"/>

								<p>4%</p>

							</div>

							<div class="col-sm-6">

								<label class="">' . ((!empty($wpmc_two_mt)) ? $wpmc_two_mt : 'Mortgage Term (Year)') . '</label>

 								<select id="fha_mortgage_term_yr">

								  <option value="15">15</option>

								  <option value="30">30</option>

  								</select>

 							</div>

						</div>



						<div class="row">

							<div class="col-sm-6">

								<label>' . ((!empty($wpmc_two_at)) ? $wpmc_two_at : 'Annual Taxes') . ' (%)</label>

								<input id="ex1" class="ex1 fha_annual_tax_scrl" data-slider-id="ex1Slider" type="text" data-slider-min="' . ((!empty($wpmc_two_at_min)) ? $wpmc_two_at_min : '0') . '" data-slider-max="' . ((!empty($wpmc_two_at_max)) ? $wpmc_two_at_max : '5') . '" data-slider-step="0.1" data-slider-value="1"/>

								<p>1%</p>

							</div>

							<div class="col-sm-6">

								<label >' . ((!empty($wpmc_two_at)) ? $wpmc_two_at : 'Annual Taxes') . ' ($)</label>

							<input type="text" id="fha_annual_tax_inp" value="' . ((!empty($wpmc_two_at_initial)) ? $wpmc_two_at_initial : '20') . '">

							</div>

						</div>



						<div class="row">

							<div class="col-sm-6">

								<label>' . ((!empty($wpmc_two_ai)) ? $wpmc_two_ai : 'Annual Insurance') . ' ($)</label>

								<input type="text" id="fha_annual_insurance_inp" value="' . ((!empty($wpmc_two_ai_initial)) ? $wpmc_two_ai_initial : 600) . '">

							</div>

							<div class="col-sm-6">

								<label >' . ((!empty($wpmc_two_mhoa)) ? $wpmc_two_mhoa : 'Monthly HOA') . '</label>

								<input type="text" id="fha_monthly_hoa_inp" value="' . ((!empty($wpmc_two_mhoa_initial)) ? $wpmc_two_mhoa_initial : 50) . '">

							</div>

						</div>

					</div>

					<div class="col-md-6 col-lg-5">

						<div class="mcalc-results">

							<h2 class="color">$<span id="fha_emmp_div_span">1421</span></h2>

							<p>Est. Monthly FHA Payment</p>

							<h4 class="color2">Payment Breakdown</h4>

							<p>Principal & Interest: $<span id="fha_pi_div_span" class="color">1421</span></p>

							<p>Monthly Taxes: $<span id="fha_mtax_div_span" class="color mtchange">1421</span></p>

							<p>Monthly Insurance: $<span id="fha_minsure_div_span" class="color">1421</span></p>
              
							<p>Up-Front MIP (1.75%)<strong>$<span id="fha_upmip_div_span">0</span></strong></p>

							<p>Monthly Mortgage Insurance: $<span id="fha_mmi_div_span" class="color">0</span></p>

							<p>Monthly HOA: $<span id="fha_hoa_div_span" class="color">1421</span></p>

							<h5>Want a Copy of the Results?</h5>

							<input type="email" id="cal2_email" placeholder="Enter your email address" value="" />

							<input type="button" id="wpmc2_send_mail" class="wpmc-submit bg" value="Send Results!">

						</div>

					</div>

				</div>				

				';

					if ($status !== false && $status == 'valid' && $license !== false) {

						echo $calTemplate2;
					} else {

						echo '<p style="color: red; border: 2px solid red; padding: 40px; margin: 100px auto; text-align: center; width: 50%;"><strong style="color: red;">Warning: You are NOT subscribed to view this calculator</strong></p>';
					}
				}



				function wpmc_calculator_template3()
				{

					$uns = get_option('wpmc_three_use_network_settings');

					$option_func = (($uns === false) ? 'get_site_option' : (($uns == 1) ? 'get_site_option' : 'get_option'));



					$license = $option_func('edd_wpmc_license_key');

					$status  = $option_func('edd_wpmc_license_status');



					$wpmc_three_ftvl = $option_func('wpmc_three_ftvl');

					$wpmc_three_pp = $option_func('wpmc_three_pp');

					$wpmc_three_dp = $option_func('wpmc_three_dp');

					$wpmc_three_ir = $option_func('wpmc_three_ir');

					$wpmc_three_mt = $option_func('wpmc_three_mt');

					$wpmc_three_at = $option_func('wpmc_three_at');

					$wpmc_three_ai = $option_func('wpmc_three_ai');

					$wpmc_three_mhoa = $option_func('wpmc_three_mhoa');



					$wpmc_three_pp_initial = $option_func('wpmc_three_pp_initial');

					$wpmc_three_dp_max = $option_func('wpmc_three_dp_max');

					$wpmc_three_dp_min = $option_func('wpmc_three_dp_min');

					$wpmc_three_dp_initial = $option_func('wpmc_three_dp_initial');

					$wpmc_three_ir_max = $option_func('wpmc_three_ir_max');

					$wpmc_three_ir_min = $option_func('wpmc_three_ir_min');

					$wpmc_three_ir_initial = $option_func('wpmc_three_ir_initial');

					$wpmc_three_at_max = $option_func('wpmc_three_at_max');

					$wpmc_three_at_min = $option_func('wpmc_three_at_min');

					$wpmc_three_at_initial = $option_func('wpmc_three_at_initial');

					//$wpmc_three_mt_initial = $option_func('wpmc_three_mt_initial');

					$wpmc_three_ai_initial = $option_func('wpmc_three_ai_initial');

					$wpmc_three_mhoa_initial = $option_func('wpmc_three_mhoa_initial');



					$wpmc_email = $option_func('wpmc_three_email');

					$admin_email = $option_func('admin_email');

					$wpmc_three_email = (!empty($wpmc_email) && $wpmc_email === '[email]') ? $admin_email : ((!empty($wpmc_email) && $wpmc_email !== '[email]') ? $wpmc_email : $admin_email);



					$calTemplate3 = '

				<div class="mcalc mcalc-va">	

					<div class="col-md-6 col-lg-7">

						<div class="row">

							<div class="col-sm-6">

								<label>' . ((!empty($wpmc_three_ftvl)) ? $wpmc_three_ftvl : 'Frist Time VA Loan?') . '</label>

								<select id="va_frist_time">

									<option value="yes" selected>Yes</option>

									<option value="no">No</option>

								</select>

							</div>

							<div class="col-sm-6" >

								<label>' . ((!empty($wpmc_three_dp)) ? $wpmc_three_dp : 'Down Payment') . ' ($)</label>

								<select id="va_service_type">

									<option value="regular_military" selected>Regular Military</option>

									<option value="reserves_national">Reserves/National Guard</option>

								</select>

							</div>



							<div class="col-sm-12">

								<label>' . ((!empty($wpmc_three_pp)) ? $wpmc_three_pp : 'Purchase Price') . '($)</label>

								<input type="text" id="va_inp_purchase_price" value="' . ((!empty($wpmc_three_pp_initial)) ? $wpmc_three_pp_initial : '250,000') . '">

							</div>

						</div>



						<div class="row">

							<div class="col-sm-6">

								<label>' . ((!empty($wpmc_three_dp)) ? $wpmc_three_dp : 'Down Payment') . ' (%)</label>

								<input id="ex1" class="ex1 va_down_payment_scrl" data-slider-id="ex1Slider" type="text" data-slider-min="' . ((!empty($wpmc_three_dp_min)) ? $wpmc_three_dp_min : 0) . '" data-slider-max="' . ((!empty($wpmc_three_dp_max)) ? $wpmc_three_dp_max : 50) . '" data-slider-step="1" data-slider-value="10"/>

								<p>10%</p>

							</div>

							<div class="col-sm-6">

								<label>' . ((!empty($wpmc_three_dp)) ? $wpmc_three_dp : 'Down Payment') . ' ($)</label>

								<input type="text" id="va_down_payment_inp" value="' . ((!empty($wpmc_three_dp_initial)) ? $wpmc_three_dp_initial : '25,000') . '">

							</div>

						</div>



						<div class="row">

							<div class="col-sm-6">

								<label>' . ((!empty($wpmc_three_ir)) ? $wpmc_three_ir : 'Interest Rate') . ' (%)</label>

								<input id="ex1" class="ex1 va_interest_rate_scrl" data-slider-id="ex1Slider" type="text" data-slider-min="' . ((!empty($wpmc_three_ir_min)) ? $wpmc_three_ir_min : 1) . '" data-slider-max="' . ((!empty($wpmc_three_ir_max)) ? $wpmc_three_ir_max : 10) . '" data-slider-step=".125" data-slider-value="4.25"/>

								<p>4.25%</p>

							</div>

							<div class="col-sm-6">

								<label class="">' . ((!empty($wpmc_three_mt)) ? $wpmc_three_mt : 'Mortgage Term (Year)') . '</label>

 

								<select id="va_mortgage_term_yr">

								  <option value="15">15</option>

								  <option value="30">30</option>

  								</select>

																

							</div>

						</div>



						<div class="row">

							<div class="col-sm-6">

								<label>' . ((!empty($wpmc_three_at)) ? $wpmc_three_at : 'Annual Taxes') . ' (%)</label>

								<input id="ex1" class="ex1 va_annual_tax_scrl" data-slider-id="ex1Slider" type="text" data-slider-min="' . ((!empty($wpmc_three_at_min)) ? $wpmc_three_at_min : 0) . '" data-slider-max="' . ((!empty($wpmc_three_at_max)) ? $wpmc_three_at_max : 5) . '" data-slider-step="0.1" data-slider-value="0.9"/>

								<p>0.9%</p>

							</div>

							<div class="col-sm-6">

								<label >' . ((!empty($wpmc_three_at)) ? $wpmc_three_at : 'Annual Taxes') . ' ($)</label>

								<input type="text" id="va_annual_tax_inp" value="' . ((!empty($wpmc_three_at_initial)) ? $wpmc_three_at_initial : '2,500') . '">

							</div>

						</div>



						<div class="row">

							<div class="col-sm-6">

								<label>' . ((!empty($wpmc_three_ai)) ? $wpmc_three_ai : 'Annual Insurance') . ' ($)</label>

								<input type="text" id="va_annual_insurance_inp" value="' . ((!empty($wpmc_three_ai_initial)) ? $wpmc_three_ai_initial : 600) . '">

							</div>

							<div class="col-sm-6">

								<label >' . ((!empty($wpmc_three_mhoa)) ? $wpmc_three_mhoa : 'Monthly HOA') . '</label>

								<input type="text" id="va_monthly_hoa_inp" value="' . ((!empty($wpmc_three_mhoa_initial)) ? $wpmc_three_mhoa_initial : 0) . '">

							</div>

						</div>

					</div>

					<div class="col-md-6 col-lg-5">

						<div class="mcalc-results">

							<h2 class="color">$<span id="va_emmp_div_span">1421</span></h2>

							<p>Estimated Monthly VA Payment</p>

							<h4 class="color2">Payment Breakdown</h4>

							<p>Principal & Interest: $<span id="va_pi_div_span" class="color">1421</span></p>

							<p>Monthly Taxes: $<span id="va_mtax_div_span" class="color">1421</span></p>

							<p>Monthly Insurance: $<span id="va_minsure_div_span" class="color">1421</span></p>

							<p>Monthly HOA: $<span id="va_hoa_div_span" class="color">1421</span></p>

							

							<h4 class="color">Estimated VA Loan Totals</h4>

							<p>Purchase Price: $<span id="va_purchase_p_span" class="color">1421</span></p>

							<p>VA Funding Fee: $<span id="va_funding_fee_p_span" class="color">1421</span></p>

							<p>Amount Financed: $<span id="va_amount_finance_p_span" class="color">1421</span></p>

							<h5>Want a Copy of the Results?</h5>

							<input type="email" id="cal3_email" placeholder="Enter your email address" value="" />

							<input type="button" id="wpmc3_send_mail" class="wpmc-submit bg" value="Send Results!">

						</div>

					</div>

				</div>				

				';

					//<p>VA Funding Fee: $<span id="va_funding_fee_div_span" class="color">1421</span></p>	

					if ($status !== false && $status == 'valid' && $license !== false) {

						echo $calTemplate3;
					} else {

						echo '<p style="color: red; border: 2px solid red; padding: 40px; margin: 100px auto; text-align: center; width: 50%;"><strong>Warning: You are NOT subscribed to view this calculator</strong></p>';
					}
				}



				function wpmc_calculator_template5()
				{

					$uns = get_option('wpmc_five_use_network_settings');

					$option_func = (($uns === false) ? 'get_site_option' : (($uns == 1) ? 'get_site_option' : 'get_option'));



					$license = $option_func('edd_wpmc_license_key');

					$status  = $option_func('edd_wpmc_license_status');

					$wpmc_five_ai = $option_func('wpmc_five_ai');
					$wpmc_five_mhaai_initial = $option_func('wpmc_five_mhaai_initial');
					$wpmc_five_md = $option_func('wpmc_five_md');
					$wpmc_five_mhamd_initial = $option_func('wpmc_five_mhamd_initial');
					$wpmc_five_eapt = $option_func('wpmc_five_eapt');
					$wpmc_five_mhaeapt_initial = $option_func('wpmc_five_mhaeapt_initial');
					$wpmc_five_eahi = $option_func('wpmc_five_eahi');
					$wpmc_five_mhaeahi_initial = $option_func('wpmc_five_mhaeahi_initial');
					$wpmc_five_dp = $option_func('wpmc_five_dp');
					$wpmc_five_mhadp_initial = $option_func('wpmc_five_mhadp_initial');
					$wpmc_five_ir = $option_func('wpmc_five_ir');
					$wpmc_five_mhair_initial = $option_func('wpmc_five_mhair_initial');
					$wpmc_five_lt = $option_func('wpmc_five_lt');
					$wpmc_five_disclaimer = $option_func('wpmc_five_disclaimer');


					$wpmc_email = $option_func('wpmc_five_email');

					$admin_email = $option_func('admin_email');

					$wpmc_three_email = (!empty($wpmc_email) && $wpmc_email === '[email]') ? $admin_email : ((!empty($wpmc_email) && $wpmc_email !== '[email]') ? $wpmc_email : $admin_email);



					$calTemplate2 .= '<div class="mcalc mcalc-ha mcalc-color">	
						 	<div class="mcalc-main">
								<div class="mcalc-half">
									<label for="mha_annual_income">' . ((!empty($wpmc_five_ai)) ? $wpmc_five_ai : 'Annual Income') . '</label>
									<i>$</i>
									<input class="mha_input_data" autocomplete="off" currency="true" type="text" name="mha_annual_income" id="mha_annual_income" value="' . ((!empty($wpmc_five_mhaai_initial)) ? $wpmc_five_mhaai_initial : '100,000') . '" />
								</div>
								<div class="mcalc-half">
									<label for="mha_monthly_debts">' . ((!empty($wpmc_five_md)) ? $wpmc_five_md : 'Monthly Debts') . '</label>
									<i>$</i>
									<input class="mha_input_data" autocomplete="off" currency="true" type="text" name="mha_monthly_debts" id="mha_monthly_debts" value="' . ((!empty($wpmc_five_mhamd_initial)) ? $wpmc_five_mhamd_initial : '2,000') . '" />
								</div>
								<div class="mcalc-half">
									<label for="mha_estimated_annual_property_taxes">' . ((!empty($wpmc_five_eapt)) ? $wpmc_five_eapt : 'Estimated annual property taxes') . '</label>
									<i>$</i>
									<input class="mha_input_data" autocomplete="off" currency="true" type="text" name="mha_estimated_annual_property_taxes" id="mha_estimated_annual_property_taxes" value="' . ((!empty($wpmc_five_mhaeapt_initial)) ? $wpmc_five_mhaeapt_initial : '1,500') . '" />
								</div>
								<div class="mcalc-half">
									<label for="mha_estimated_annual_home_insurance">' . ((!empty($wpmc_five_eahi)) ? $wpmc_five_eahi : 'Estimated Annual Home Insurance') . '</label>
									<i>$</i>
									<input class="mha_input_data" autocomplete="off" currency="true" type="text" name="mha_estimated_annual_home_insurance" id="mha_estimated_annual_home_insurance" value="' . ((!empty($wpmc_five_mhaeahi_initial)) ? $wpmc_five_mhaeahi_initial : '1,000') . '" />
								</div>
								<div class="mcalc-half">
									<label for="mha_down_payment">' . ((!empty($wpmc_five_dp)) ? $wpmc_five_dp : 'Down Payment') . '</label>
									<i>$</i>
									<input class="mha_input_data" autocomplete="off" currency="true" type="text" name="mha_down_payment" id="mha_down_payment" value="' . ((!empty($wpmc_five_mhadp_initial)) ? $wpmc_five_mhadp_initial : '10,000') . '" />
								</div>
								<div class="mcalc-half mha_main_wrapper">
									<label for="mha_interest_rate">' . ((!empty($wpmc_five_ir)) ? $wpmc_five_ir : 'Interest Rate') . '</label>
									<input class="mha_input_data_right" autocomplete="off" currency="true" type="text" name="mha_interest_rate" id="mha_interest_rate" value="' . ((!empty($wpmc_five_mhair_initial)) ? $wpmc_five_mhair_initial : '4') . '" />
									<i>%</i>
								</div>
								<div class="mcalc-full">
									<label for="mha_loan_term">' . ((!empty($wpmc_five_lt)) ? $wpmc_five_lt : 'Loan Term') . '</label>
									<select id="mha_loan_term" name="mha_loan_term">
										<option value="">Choose loan term</option>
										<option value="30">30 year fixed</option>
										<option value="15">15 year fixed</option>
									</select>
								</div>
								<div class="mcalc-full mcwp-results">
									<label for="mha_cal3_email">Want a Copy of the Results?</label>
									<input type="email" id="mha_cal3_email" placeholder="Enter your email address" value="" name="mha_cal3_email" />
									<input type="button" id="mha_wpmc3_send_mail" class="mcwp-submit bg mha_submit mcalc-color" value="Send Results!">
								</div>
							</div>
							<div class="mcalc-values">
								<div class="mcalc-results">
									<h4>You can afford up to</h4>
									<h2 class="mcalc-value">$<span id="mha_afford_house_div_span"></span></h2>
									<p class="mcwp-pi">Monthly Payment <strong>$<span id="mha_emmp_div_span"></span></strong></p>
									<p class="mcwp-pi">Principal & Interest <strong>$<span id="mha_pi_div_span"></span></strong></p>
									<p class="mcwp-pi">Tax <strong>$<span id="mha_taxes_div_span"></span></strong></p>
									<p class="mcwp-pi">Insurance <strong>$<span id="mha_insurance_div_span"></span></strong></p>
									 <small>' . ((!empty($wpmc_five_disclaimer)) ? $wpmc_five_disclaimer : '') . '</small>
								</div>
							</div>
						 </div><script type="text/javascript">
						 		function addCommas(intNum) {
									val = intNum;
									var parts = val.toString().split(".");
									parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ",");
									return parts.join(".");
								}
								function removeco(val){
									return val.replace(/,/g, "");
								}
						 		jQuery( document ).ready(function() {
									var mha_annual_income = removeco( jQuery("#mha_annual_income").val() );
									jQuery("#mha_annual_income").val( addCommas( mha_annual_income ) );
									var mha_interest_rate = removeco( jQuery("#mha_interest_rate").val() );
									jQuery("#mha_interest_rate").val( addCommas( mha_interest_rate ) );
									var mha_monthly_debts = removeco( jQuery("#mha_monthly_debts").val() );
									jQuery("#mha_monthly_debts").val( addCommas( mha_monthly_debts ) );
									
									var mha_estimated_annual_home_insurance = removeco( jQuery("#mha_estimated_annual_home_insurance").val() );
									jQuery("#mha_estimated_annual_home_insurance").val( addCommas( mha_estimated_annual_home_insurance ) );
									var mha_estimated_annual_property_taxes = removeco( jQuery("#mha_estimated_annual_property_taxes").val() );
									jQuery("#mha_estimated_annual_property_taxes").val( addCommas( mha_estimated_annual_property_taxes ) );
									var mha_down_payment = removeco( jQuery("#mha_down_payment").val() );
									jQuery("#mha_down_payment").val( addCommas( mha_down_payment ) );
									var mha_loan_term = removeco( jQuery("#mha_loan_term").val() );
									jQuery("#mha_loan_term").val( addCommas( mha_loan_term ) );
									if( mha_loan_term == ""){
										mha_loan_term = 30;
									}
									
									if( mha_annual_income != "" && mha_interest_rate != "" ){
		var minimum_monthly_debts = ((mha_annual_income*8)/100)/12;
		if( mha_monthly_debts == \'\' || mha_monthly_debts < minimum_monthly_debts ){
			var mha_est_monthly_payment = ((mha_annual_income/12)*0.40)-minimum_monthly_debts;
			
			if( mha_estimated_annual_property_taxes != \'\' ){
				var mha_taxes = mha_estimated_annual_property_taxes/12;
			}
			else{
				var mha_taxes = 0;
			}
			if( mha_estimated_annual_home_insurance != \'\' ){
				var mha_insurance = mha_estimated_annual_home_insurance/12;
			}
			else
			{
				var mha_insurance = 0;
			}
			var mha_P_I = mha_est_monthly_payment-(mha_taxes+mha_insurance);
			
			var aaaaa = (mha_interest_rate/100/12);
			var mpowerdata = Math.pow((1+(mha_interest_rate/100/12)), (-mha_loan_term*12));
			var bbbbb = (1-(mpowerdata));
			var ab = bbbbb/aaaaa;
			
			if( ab < 0 ){
				var temp =ab*mha_P_I;
				var ab_total = temp-parseInt(mha_down_payment);
			}
			else{
				var temp =ab*mha_P_I;
				var ab_total = temp + parseInt(mha_down_payment);
			}
			if( ab_total <= 0 ){
				jQuery("#mha_afford_house_div_span").html( \'0\' );
			}
			else{
				jQuery("#mha_afford_house_div_span").html( Number(Math.round( ab_total )).toLocaleString(\'en\') );
			}
			if( mha_est_monthly_payment <= 0 ){
				jQuery("#mha_emmp_div_span").html( \'0\' );
			}
			else{
				jQuery("#mha_emmp_div_span").html( Number(Math.round( mha_est_monthly_payment )).toLocaleString(\'en\') );
			}
			if( mha_P_I <= 0 ){
				jQuery("#mha_pi_div_span").html( \'0\' );
			}
			else{
				jQuery("#mha_pi_div_span").html( Number(Math.round( mha_P_I )).toLocaleString(\'en\') );
			}
			if( mha_taxes <= 0 ){
				jQuery("#mha_taxes_div_span").html( \'0\' );
			}
			else{
				jQuery("#mha_taxes_div_span").html( Number(Math.round( mha_taxes )).toLocaleString(\'en\') );
			}
			if( mha_insurance <= 0 ){
				jQuery("#mha_insurance_div_span").html( \'0\' );
			}
			else{
				jQuery("#mha_insurance_div_span").html( Number(Math.round( mha_insurance )).toLocaleString(\'en\') );
			}
		}
		else{
			var mha_est_monthly_payment = ((mha_annual_income/12)*0.40)-mha_monthly_debts;
			if( mha_estimated_annual_property_taxes != \'\' ){
				var mha_taxes = mha_estimated_annual_property_taxes/12;
			}
			else{
				var mha_taxes = 0;
			}
			if( mha_estimated_annual_home_insurance != \'\' ){
				var mha_insurance = mha_estimated_annual_home_insurance/12;
			}
			else
			{
				var mha_insurance = 0;
			}
			var mha_P_I = mha_est_monthly_payment-(mha_taxes+mha_insurance);
			
			var aaaaa = (mha_interest_rate/100/12);
			var mpowerdata = Math.pow((1+(mha_interest_rate/100/12)), (-mha_loan_term*12));
			var bbbbb = (1-(mpowerdata));
			var ab = bbbbb/aaaaa;
			
			if( ab < 0 ){
				var temp =ab*mha_P_I;
				var ab_total = temp-parseInt(mha_down_payment);
			}
			else{
				var temp =ab*mha_P_I;
				var ab_total = temp + parseInt(mha_down_payment);
			}
			
			if( ab_total <= 0 ){
				jQuery("#mha_afford_house_div_span").html( \'0\' );
			}
			else{
				jQuery("#mha_afford_house_div_span").html( Number(Math.round( ab_total )).toLocaleString(\'en\') );
			}
			if( mha_est_monthly_payment <= 0 ){
				jQuery("#mha_emmp_div_span").html( \'0\' );
			}
			else{
				jQuery("#mha_emmp_div_span").html( Number(Math.round( mha_est_monthly_payment )).toLocaleString(\'en\') );
			}
			if( mha_P_I <= 0 ){
				jQuery("#mha_pi_div_span").html( \'0\' );
			}
			else{
				jQuery("#mha_pi_div_span").html( Number(Math.round( mha_P_I )).toLocaleString(\'en\') );
			}
			if( mha_taxes <= 0 ){
				jQuery("#mha_taxes_div_span").html( \'0\' );
			}
			else{
				jQuery("#mha_taxes_div_span").html( Number(Math.round( mha_taxes )).toLocaleString(\'en\') );
			}
			if( mha_insurance <= 0 ){
				jQuery("#mha_insurance_div_span").html( \'0\' );
			}
			else{
				jQuery("#mha_insurance_div_span").html( Number(Math.round( mha_insurance )).toLocaleString(\'en\') );
			}
		}
	}
							 	});
	 						</script>';



					if ($status !== false && $status == 'valid' && $license !== false) {

						echo $calTemplate2;
					} else {

						echo '<p style="color: red; border: 2px solid red; padding: 40px; margin: 100px auto; text-align: center; width: 50%;"><strong>Warning: You are NOT subscribed to view this calculator</strong></p>';
					}
				}




				function wpmc_calculator_template6()
				{

					$uns = get_option('wpmc_six_use_network_settings');

					$option_func = (($uns === false) ? 'get_site_option' : (($uns == 1) ? 'get_site_option' : 'get_option'));



					$license = $option_func('edd_wpmc_license_key');

					$status  = $option_func('edd_wpmc_license_status');


					$wpmc_six_la = $option_func('wpmc_six_la');
					$wpmc_six_la_initial = $option_func('wpmc_six_la_initial');
					$wpmc_six_ir = $option_func('wpmc_six_ir');
					$wpmc_six_ir_initial = $option_func('wpmc_six_ir_initial');
					$wpmc_six_ct = $option_func('wpmc_six_ct');
					$wpmc_six_ct_initial = $option_func('wpmc_six_ct_initial');
					$wpmc_six_oy = $option_func('wpmc_six_oy');
					$wpmc_six_oy_initial = $option_func('wpmc_six_oy_initial');
					$wpmc_six_nla = $option_func('wpmc_six_nla');
					$wpmc_six_nla_initial = $option_func('wpmc_six_nla_initial');
					$wpmc_six_nir = $option_func('wpmc_six_nir');
					$wpmc_six_nir_initial = $option_func('wpmc_six_nir_initial');
					$wpmc_six_nlt = $option_func('wpmc_six_nlt');
					$wpmc_six_nlt_initial = $option_func('wpmc_six_nlt_initial');
					$wpmc_six_nrf = $option_func('wpmc_six_nrf');
					$wpmc_six_nrf_initial = $option_func('wpmc_six_nrf_initial');
					$wpmc_six_disclaimer = $option_func('wpmc_six_disclaimer');


					$wpmc_email = $option_func('wpmc_five_email');

					$admin_email = $option_func('admin_email');

					$wpmc_three_email = (!empty($wpmc_email) && $wpmc_email === '[email]') ? $admin_email : ((!empty($wpmc_email) && $wpmc_email !== '[email]') ? $wpmc_email : $admin_email);



					$calTemplate2 .= '<div class="mcalc mcalc-refi mcalc-color">
		<div class="mcalc-main">
			<div class="mcalc-half">
				<label for="rc_annual_income">' . ((!empty($wpmc_six_la)) ? $wpmc_six_la : 'Current Loan Amount') . '</label>
				<i>$</i>
				<input class="mha_input_data" autocomplete="off" currency="true" type="text" name="rc_original_loan_amount" id="rc_original_loan_amount" value="' . ((!empty($wpmc_six_la_initial)) ? $wpmc_six_la_initial : '300,000') . '" />
			</div>
			<div class="mcalc-half mha_main_wrapper">
				<label for="rc_interest_rate">' . ((!empty($wpmc_six_ir)) ? $wpmc_six_ir : 'Interest Rate') . '</label>
				<input autocomplete="off" currency="true" type="text" name="rc_interest_rate" id="rc_interest_rate" value="' . ((!empty($wpmc_six_ir_initial)) ? $wpmc_six_ir_initial : '5') . '" />
				<i>%</i>
			</div>
			<div class="mcalc-half mha_main_wrapper">
				<label for="rc_current_term">' . ((!empty($wpmc_six_ct)) ? $wpmc_six_ct : 'Current Term') . '</label>
				<input autocomplete="off" currency="true" type="text" name="rc_current_term" id="rc_current_term" value="' . ((!empty($wpmc_six_ct_initial)) ? $wpmc_six_ct_initial : '360') . '" />
				<i>months</i>
			</div>
			<div class="mcalc-half">
				<label for="rc_origination_year">' . ((!empty($wpmc_six_oy)) ? $wpmc_six_oy : 'Origination Year') . '</label>
				<input autocomplete="off" currency="true" type="text" name="rc_origination_year" id="rc_origination_year" value="' . ((!empty($wpmc_six_oy_initial)) ? $wpmc_six_oy_initial : '2015') . '" />
			</div>
			<div class="mcalc-half">
				<label for="rc_annual_income">' . ((!empty($wpmc_six_nla)) ? $wpmc_six_nla : 'New Loan Amount') . '</label>
				<i>$</i>
				<input class="mha_input_data" autocomplete="off" currency="true" type="text" name="rc_new_loan_amount" id="rc_new_loan_amount" value="' . ((!empty($wpmc_six_nla_initial)) ? $wpmc_six_nla_initial : '250,000') . '" />
			</div>
			<div class="mcalc-half mha_main_wrapper">
				<label for="rc_new_interest_rate">' . ((!empty($wpmc_six_nir)) ? $wpmc_six_nir : 'New Interest Rate') . '</label>
				<input autocomplete="off" currency="true" type="text" name="rc_new_interest_rate" id="rc_new_interest_rate" value="' . ((!empty($wpmc_six_nir_initial)) ? $wpmc_six_nir_initial : '4') . '" />
				<i>%</i>
			</div>
			<div class="mcalc-half mha_main_wrapper">
				<label for="rc_new_loan_term">' . ((!empty($wpmc_six_nlt)) ? $wpmc_six_nlt : 'New Loan Term') . '</label>
				<input autocomplete="off" currency="true" type="text" name="rc_new_loan_term" id="rc_new_loan_term" value="' . ((!empty($wpmc_six_nlt_initial)) ? $wpmc_six_nlt_initial : '360') . '" />
				<i>months</i>
			</div>
			<div class="mcalc-half">
				<label for="rc_new_refinance_fees">' . ((!empty($wpmc_six_nrf)) ? $wpmc_six_nrf : 'Refinance Fees') . '</label>
				<i>$</i>
				<input class="mha_input_data" autocomplete="off" currency="true" type="text" name="rc_new_refinance_fees" id="rc_new_refinance_fees" value="' . ((!empty($wpmc_six_nrf_initial)) ? $wpmc_six_nrf_initial : '1,000') . '" />
			</div>
			<div class="mcalc-full mcwp-results">
				<label for="rc_cal3_email">Want a Copy of the Results?</label>
				<input type="email" id="rc_cal3_email" placeholder="Enter your email address" value="" name="rc_cal3_email" />
				<input type="button" id="rc_wpmc3_send_mail" class="mcwp-submit bg rc_submit mcalc-color" value="Send Results!">
			</div>
		</div>
		<div class="mcalc-values">
			<div class="mcalc-results">
				<h4>Refinancing could save you</h4>
				<h2 class="mcalc-value">' . $curr_symbol . '<span id="rc_afford_house_div_span"></span><em>/month</em></h2>
				<p class="mcwp-pi">New monthly payment <strong>' . $curr_symbol . '<span id="rc_emmp_div_span"></span></strong></p>
				<p class="mcwp-pi">Refinance fees <strong>' . $curr_symbol . '<span id="rc_pi_div_span"></span></strong></p>
				<p class="mcwp-pi">Lifetime Savings <strong>' . $curr_symbol . '<span id="rc_lifetime_div_span"></span></strong></p>
				 <small>' . ((!empty($wpmc_six_disclaimer)) ? $wpmc_six_disclaimer : '') . '</small>
			</div>
		</div>
	 </div><script type="text/javascript">
	 	function addCommas(intNum) {
			val = intNum;
			var parts = val.toString().split(".");
			parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ",");
			return parts.join(".");
		}
	 	function removeco(val){
			return val.replace(/,/g, "");
		}
		jQuery( document ).ready(function() {
			var rc_original_loan_amount = removeco( jQuery("#rc_original_loan_amount").val() );
			jQuery("#rc_original_loan_amount").val( addCommas( rc_original_loan_amount ) );
			var rc_interest_rate = removeco( jQuery("#rc_interest_rate").val() );
			jQuery("#rc_interest_rate").val( addCommas( rc_interest_rate ) );
			var rc_current_term = removeco( jQuery("#rc_current_term").val() );
			jQuery("#rc_current_term").val( addCommas( rc_current_term ) );
			
			var rc_new_loan_amount = removeco( jQuery("#rc_new_loan_amount").val() );
			jQuery("#rc_new_loan_amount").val( addCommas( rc_new_loan_amount ) );
			var rc_new_interest_rate = removeco( jQuery("#rc_new_interest_rate").val() );
			jQuery("#rc_new_interest_rate").val( addCommas( rc_new_interest_rate ) );
			var rc_new_loan_term = removeco( jQuery("#rc_new_loan_term").val() );
			jQuery("#rc_new_loan_term").val( addCommas( rc_new_loan_term ) );
			var rc_new_refinance_fees = removeco( jQuery("#rc_new_refinance_fees").val() );
			jQuery("#rc_new_refinance_fees").val( addCommas( rc_new_refinance_fees ) );
			var rc_origination_year = jQuery("#rc_origination_year").val();
			if( rc_new_loan_amount == \'\' ){
				rc_new_loan_amount = 0;
			}
			
			if( rc_original_loan_amount == \'\' ){
				rc_original_loan_amount = 0;
			}
			
			if( rc_new_loan_amount == 0 && rc_original_loan_amount == 0 ){
				jQuery("#rc_pi_div_span").html(rc_new_refinance_fees);
				jQuery("#rc_afford_house_div_span").html(\'0\');
				jQuery("#rc_emmp_div_span").html( \'0\' );
				jQuery("#rc_lifetime_div_span").html(\'0\');
			}
			else if( parseInt( rc_new_loan_amount ) > parseInt( rc_original_loan_amount ) ){
				var nleft_data = (rc_new_interest_rate / 100 / 12) * rc_new_loan_amount;
				var nmpowerdata = Math.pow((1+(rc_new_interest_rate/100/12)), (-rc_new_loan_term) );
				var nright_data = (1-(nmpowerdata));
				newloanpayment  = nleft_data/ nright_data;
				jQuery("#rc_afford_house_div_span").html(\'0\');
				jQuery("#rc_emmp_div_span").html( Number(Math.round( newloanpayment )).toLocaleString(\'en\') );
				jQuery("#rc_pi_div_span").html(\'0\');
				jQuery("#rc_lifetime_div_span").html(\'0\');
			}
			else
			{
				var cleft_data = (rc_interest_rate / 100 / 12) * rc_original_loan_amount;
				var mpowerdata = Math.pow((1+(rc_interest_rate/100/12)), (-rc_current_term) );
				var cright_data = (1-(mpowerdata));
				currentloanpayment  = cleft_data/ cright_data;
				
				var nleft_data = (rc_new_interest_rate / 100 / 12) * rc_new_loan_amount;
				var nmpowerdata = Math.pow((1+(rc_new_interest_rate/100/12)), (-rc_new_loan_term) );
				var nright_data = (1-(nmpowerdata));
				newloanpayment  = nleft_data/ nright_data;
				monthlysavings = currentloanpayment-newloanpayment;
				jQuery("#rc_afford_house_div_span").html( Number(Math.round( monthlysavings )).toLocaleString(\'en\') );
				if( Number(Math.round( monthlysavings )).toLocaleString(\'en\') == 0 ){
					jQuery("#rc_emmp_div_span").html( \'0\' );
					jQuery("#rc_pi_div_span").html( \'0\' );
					jQuery("#rc_lifetime_div_span").html(\'0\')
				}
				else{
					jQuery("#rc_emmp_div_span").html( Number(Math.round( newloanpayment )).toLocaleString(\'en\') );
					jQuery("#rc_pi_div_span").html( Number(Math.round( rc_new_refinance_fees )).toLocaleString(\'en\') );
					var current_year = new Date().getFullYear();
					if( rc_origination_year <= current_year ){
						lifetimesavings = currentloanpayment * (rc_current_term - ((current_year - rc_origination_year) * 12)) - (parseInt((newloanpayment * rc_new_loan_term)) + parseInt(rc_new_refinance_fees));
						jQuery("#rc_lifetime_div_span").html( Number(Math.round( lifetimesavings )).toLocaleString(\'en\') );
					}
				}
				
			}
		});
	 </script>';



					if ($status !== false && $status == 'valid' && $license !== false) {

						echo $calTemplate2;
					} else {

						echo '<p style="color: red; border: 2px solid red; padding: 40px; margin: 100px auto; text-align: center; width: 50%;"><strong>Warning: You are NOT subscribed to view this calculator</strong></p>';
					}
				}
