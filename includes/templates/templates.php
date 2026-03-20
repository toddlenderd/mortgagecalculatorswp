<?php
function wpmc_main_template()
{
	$chek = get_site_option('edd_wpmc_license_key') . '<br />';
	if (!empty($chek)) {
		$license = get_site_option('edd_wpmc_license_key');
		$status  = get_site_option('edd_wpmc_license_status');
	} else {
		$license = get_option('edd_wpmc_license_key');
		$status  = get_option('edd_wpmc_license_status');
	}

	$cal_one_screen = (isset($_GET['action']) && 'cal-one' == $_GET['action']) ? true : false;
	$cal_two_Screen = (isset($_GET['action']) && 'cal-two' == $_GET['action']) ? true : false;
	$cal_three_Screen = (isset($_GET['action']) && 'cal-three' == $_GET['action']) ? true : false;
	$cal_four_Screen = (isset($_GET['action']) && 'cal-four' == $_GET['action']) ? true : false;
	$cal_five_Screen = (isset($_GET['action']) && 'cal-five' == $_GET['action']) ? true : false;
	$cal_six_Screen = (isset($_GET['action']) && 'cal-six' == $_GET['action']) ? true : false;
	$license_Screen = (isset($_GET['action']) && 'license' == $_GET['action']) ? true : false;
	$admin_url = (is_network_admin() ? 'network/admin.php?page=wpmc' : 'admin.php?page=wpmc');
	// show error/update messages
	settings_errors('wporg_messages'); ?>

	<div class="wrap">

		<h1 id="header_tag">
			<?php
			if ($cal_one_screen) {
				echo __('Conventional Mortgage Calculator', 'wpmc');
			} elseif ($cal_two_Screen) {
				echo __('FHA Mortgage Calculator', 'wpmc');
			} elseif ($cal_three_Screen) {
				echo __('VA Mortgage Calculator', 'wpmc');
			} elseif ($cal_five_Screen) {
				echo __('Affordability Calculator', 'wpmc');
			} elseif ($cal_six_Screen) {
				echo __('Refinance Calculator', 'wpmc');
			} elseif ($license_Screen) {
				echo __('License Options', 'wpmc');
			} else {
				echo __('General Settings', 'wpmc');
			} ?>
		</h1>

		<h2 class="nav-tab-wrapper">

			<a href="<?php echo admin_url($admin_url); ?>" class="nav-tab<?php if (!isset($_GET['action']) || isset($_GET['action']) && 'cal-one' != $_GET['action']  && 'cal-two' != $_GET['action']  && 'cal-three' != $_GET['action']  && 'cal-four' != $_GET['action'] && 'cal-five' != $_GET['action'] && 'cal-six' != $_GET['action'] && 'license' != $_GET['action']) {
																																			echo ' nav-tab-active';
																																		} ?>"><?php esc_html_e('General Settings', 'wpmc'); ?></a>

			<a href="<?php echo esc_url(add_query_arg(array('action' => 'cal-one'), admin_url($admin_url))); ?>" class="nav-tab<?php if ($cal_one_screen) {
																																																														echo ' nav-tab-active';
																																																													} ?>"><?php esc_html_e('Conventional', 'wpmc'); ?></a>


			<a href="<?php echo esc_url(add_query_arg(array('action' => 'cal-two'), admin_url($admin_url))); ?>" class="nav-tab<?php if ($cal_two_Screen) {
																																																														echo ' nav-tab-active';
																																																													} ?>"><?php esc_html_e('FHA', 'wpmc'); ?></a>



			<a href="<?php echo esc_url(add_query_arg(array('action' => 'cal-three'), admin_url($admin_url))); ?>" class="nav-tab<?php if ($cal_three_Screen) {
																																																															echo ' nav-tab-active';
																																																														} ?>"><?php esc_html_e('VA', 'wpmc'); ?></a>



			<a href="<?php echo esc_url(add_query_arg(array('action' => 'cal-five'), admin_url($admin_url))); ?>" class="nav-tab<?php if ($cal_five_Screen) {
																																																														echo ' nav-tab-active';
																																																													} ?>"><?php esc_html_e('Affordability', 'wpmc'); ?></a>

			<a href="<?php echo esc_url(add_query_arg(array('action' => 'cal-six'), admin_url($admin_url))); ?>" class="nav-tab<?php if ($cal_six_Screen) {
																																																														echo ' nav-tab-active';
																																																													} ?>"><?php esc_html_e('Refinance', 'wpmc'); ?></a>


			<?php if (!is_multisite()) { ?>



				<a href="<?php echo esc_url(add_query_arg(array('action' => 'license'), admin_url($admin_url))); ?>" class="nav-tab<?php if ($license_Screen) {
																																																															echo ' nav-tab-active';
																																																														} ?>"><?php esc_html_e('Premium License', 'wpmc'); ?></a>



				<?php } else {
				if (!is_plugin_active_for_network('mortgage-calculators-wp/mortgage-calculators-wp.php')) {	?>
					<a href="<?php echo esc_url(add_query_arg(array('action' => 'license'), admin_url($admin_url))); ?>" class="nav-tab<?php if ($license_Screen) {
																																																																echo ' nav-tab-active';
																																																															} ?>"><?php esc_html_e('Premium License', 'wpmc'); ?></a>

				<?php } elseif (is_network_admin()) { ?>

					<a href="<?php echo esc_url(add_query_arg(array('action' => 'license'), admin_url($admin_url))); ?>" class="nav-tab<?php if ($license_Screen) {
																																																																echo ' nav-tab-active';
																																																															} ?>"><?php esc_html_e('Premium License', 'wpmc'); ?></a>

			<?php }
			} ?>
		</h2>
		<div class="wrap">
			<p id="settings_errors"><?php settings_errors(); ?></p>

			<form method="post" action="<?php echo (is_network_admin() ?  'edit.php?action=wpmc_update_network_options' : 'options.php') ?>">
				<?php
				$upgradeText = __('To upgrade or get plugin support please visit', 'wpmc');

				if ($cal_one_screen) {
					settings_fields('wpmc_one');
					do_settings_sections('wpmc-settings-one');
					submit_button();
				} elseif ($cal_two_Screen) {
					if ($status !== false && $status == 'valid' && $license !== false) {
						settings_fields('wpmc_two');
						do_settings_sections('wpmc-settings-two');
						submit_button();
					} else {
						echo '<p style="background: #3e50b4; padding:10px 15px; border-radius: 3px; font-size: 16px; color:#fff">' . $upgradeText . ' <a href="https://mortgagecalculatorsplugin.com" target="_blank" style="color:#fff;">MortgageCalculatorsPlugin.com</a></p>';
					}
				} elseif ($cal_three_Screen) {
					if ($status !== false && $status == 'valid' && $license !== false) {
						settings_fields('wpmc_three');

						do_settings_sections('wpmc-settings-three');

						submit_button();
					} else {
						echo '<p style="background: #3e50b4; padding:10px 15px; border-radius: 3px; font-size: 16px; color:#fff">' . $upgradeText . ' <a href="https://mortgagecalculatorsplugin.com" target="_blank" style="color:#fff;">MortgageCalculatorsPlugin.com</a></p>';
					}
				} elseif ($cal_four_Screen) {
					if ($status !== false && $status == 'valid' && $license !== false) {
						echo '<p><strong>Coming Soon</strong></p>';
					} else {
						echo '<p style="background: #3e50b4; padding:10px 15px; border-radius: 3px; font-size: 16px; color:#fff">' . $upgradeText . ' <a href="https://mortgagecalculatorsplugin.com" target="_blank" style="color:#fff;">MortgageCalculatorsPlugin.com</a></p>';
					}
				} elseif ($cal_five_Screen) {
					if ($status !== false && $status == 'valid' && $license !== false) {
						settings_fields('wpmc_five');

						do_settings_sections('wpmc-settings-five');

						submit_button();
					} else {
						echo '<p style="background: #3e50b4; padding:10px 15px; border-radius: 3px; font-size: 16px; color:#fff">' . $upgradeText . ' <a href="https://mortgagecalculatorsplugin.com" target="_blank" style="color:#fff;">MortgageCalculatorsPlugin.com</a></p>';
					}
				} elseif ($cal_six_Screen) {
					if ($status !== false && $status == 'valid' && $license !== false) {
						settings_fields('wpmc_six');

						do_settings_sections('wpmc-settings-six');

						submit_button();
					} else {
						echo '<p style="background: #3e50b4; padding:10px 15px; border-radius: 3px; font-size: 16px; color:#fff">' . $upgradeText . ' <a href="https://mortgagecalculatorsplugin.com" target="_blank" style="color:#fff;">MortgageCalculatorsPlugin.com</a></p>';
					}
				} elseif ($license_Screen) {
					settings_fields('wpmc_license');

					do_settings_sections(WPMC_EDD_LICENSE_PAGE);

					WPMC_EDD_LICENSE_PAGE();
				} else {
					echo '<p style="background: #3e50b4; padding:10px 15px; border-radius: 3px; font-size: 16px; color:#fff">' . $upgradeText . ' <a href="https://mortgagecalculatorsplugin.com" target="_blank" style="color:#fff;">MortgageCalculatorsPlugin.com</a></p>';

					settings_fields('wpmc_mail');

					do_settings_sections('wpmc-settings-mail');

					submit_button();
				} ?>

			</form>
			<script>
				var $mcwp = jQuery.noConflict();
				$mcwp(function($) {
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
						<?php
						} else { ?>
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
						jQuery('input[name="wpmc_one_pp"]').parents('tr').wrap("<div class='label_one_heading'></div>");
						jQuery('.label_one_heading').prepend('<h2 class="wpmc_one_label">Customize Field Labels</h2>');
						jQuery('input[name="wpmc_one_pp"]').parents('tr').unwrap();
						jQuery('input[name="wpmc_one_pp_initial"]').parents('tr').wrap("<div class='value_one_heading'></div>");
						jQuery('.value_one_heading').prepend('<h2 class="wpmc_one_label" style="padding-top:30px">Customize Values</h2>');
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
						<?php
						} else { ?>


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
						jQuery('input[name="wpmc_two_pp"]').parents('tr').wrap("<div class='label_two_heading'></div>");
						jQuery('.label_two_heading').prepend('<h2 class="wpmc_two_label">Customize Field Labels</h2>');
						jQuery('input[name="wpmc_two_pp"]').parents('tr').unwrap();
						jQuery('input[name="wpmc_two_pp_initial"]').parents('tr').wrap("<div class='value_two_heading'></div>");
						jQuery('.value_two_heading').prepend('<h2 class="wpmc_two_label" style="padding-top:30px">Customize Values</h2>');
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
						<?php
						} else { ?>
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
						jQuery('input[name="wpmc_three_ftvl"]').parents('tr').wrap("<div class='label_three_heading'></div>");
						jQuery('.label_three_heading').prepend('<h2 class="wpmc_three_label">Customize Field Labels</h2>');
						jQuery('input[name="wpmc_three_ftvl"]').parents('tr').unwrap();
						jQuery('input[name="wpmc_three_pp_initial"]').parents('tr').wrap("<div class='value_three_heading'></div>");
						jQuery('.value_three_heading').prepend('<h2 class="wpmc_three_label" style="padding-top: 30px;">Customize Values</h2>');
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
						<?php
						} else { ?>
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
					<?php if ($cal_five_Screen) { ?>
						jQuery('input[name="wpmc_five_ai"]').parents('tr').wrap("<div class='label_five_heading'></div>");
						jQuery('.label_five_heading').prepend('<h2 class="wpmc_five_label">Customize Field Labels</h2>');
						jQuery('input[name="wpmc_five_ai"]').parents('tr').unwrap();
						jQuery('input[name="wpmc_five_mhaai_initial"]').parents('tr').wrap("<div class='value_five_heading'></div>");
						jQuery('.value_five_heading').prepend('<h2 class="wpmc_five_label" style="padding-top: 30px;">Customize Values</h2>');
						jQuery('input[name="wpmc_five_mhaai_initial"]').parents('tr').unwrap();
						<?php
						$options = get_wpmc_option('wpmc_five_use_network_settings');
						$val = ($options == '0') ? '0' : '1';
						?>
						var wpmc_five_use_network_settings = '<?php echo $val; ?>';
						<?php
						$wpmc_five_use_network_settings = get_wpmc_option('wpmc_five_use_network_settings');
						if ($wpmc_five_use_network_settings !== false) {
						?>
							if (is_multisite && !is_network_admin && wpmc_five_use_network_settings == '0') {
								jQuery('.wpmc_five').not(':first').parents('tr').hide();
								jQuery('.wpmc_five_label').hide();
							}
						<?php
						} else { ?>
							if (is_multisite && !is_network_admin) {
								jQuery('.wpmc_five').not(':first').parents('tr').hide();
								jQuery('.wpmc_five_label').hide();
							}
						<?php } ?>
						jQuery('input[name="wpmc_five_use_network_settings"]').click(function() {
							if (jQuery(this).is(':checked')) {
								jQuery(this).val('0');
							} else {
								jQuery(this).val('1');
							}
							jQuery('.wpmc_five').not(':first').parents('tr').toggle();
							jQuery('.wpmc_five_label').toggle();
						});

					<?php } ?>

					<?php if ($cal_six_Screen) { ?>
						jQuery('input[name="wpmc_six_first_heading"]').parents('tr').wrap("<div class='label_six_heading'></div>");
						jQuery('.label_six_heading').prepend('<h2 class="wpmc_six_label">Customize Field Labels</h2>');
						jQuery('input[name="wpmc_six_first_heading"]').parents('tr').unwrap();
						jQuery('input[name="wpmc_six_la_initial"]').parents('tr').wrap("<div class='value_six_heading'></div>");
						jQuery('.value_six_heading').prepend('<h2 class="wpmc_six_label" style="padding-top: 30px;">Customize Values</h2>');
						jQuery('input[name="wpmc_six_la_initial"]').parents('tr').unwrap();
						<?php
						$options = get_wpmc_option('wpmc_six_use_network_settings');
						$val = ($options == '0') ? '0' : '1';
						?>
						var wpmc_six_use_network_settings = '<?php echo $val; ?>';
						<?php
						$wpmc_six_use_network_settings = get_wpmc_option('wpmc_six_use_network_settings');
						if ($wpmc_six_use_network_settings !== false) {
						?>
							if (is_multisite && !is_network_admin && wpmc_six_use_network_settings == '0') {
								jQuery('.wpmc_six').not(':first').parents('tr').hide();
								jQuery('.wpmc_six_label').hide();
							}
						<?php
						} else { ?>
							if (is_multisite && !is_network_admin) {
								jQuery('.wpmc_six').not(':first').parents('tr').hide();
								jQuery('.wpmc_six_label').hide();
							}
						<?php } ?>
						jQuery('input[name="wpmc_six_use_network_settings"]').click(function() {
							if (jQuery(this).is(':checked')) {
								jQuery(this).val('0');
							} else {
								jQuery(this).val('1');
							}
							jQuery('.wpmc_six').not(':first').parents('tr').toggle();
							jQuery('.wpmc_six_label').toggle();
						});
					<?php } ?>
				});
			</script>
		</div>
	</div> <?php
				}
