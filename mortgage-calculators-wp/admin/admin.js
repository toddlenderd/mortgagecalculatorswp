(function ($) {
	// Add Color Picker to all inputs that have 'color-field' class
	//$(function () {
	$('.color-picker').wpColorPicker();
	$("select[name^=mcwp_hide_insurance]").change(function () {
		if ($(this).val() === 'yes') {
			$(this).parent().parent().next().removeClass('show_comp');
			$("input[name*=ai_initial]").parent().parent().removeClass('show_comp');
			$(this).parent().parent().next().addClass('hide_comp');
			$("input[name*=ai_initial]").parent().parent().addClass('hide_comp');
		} else {
			$(this).parent().parent().next().removeClass('hide_comp');
			$("input[name*=ai_initial]").parent().parent().removeClass('hide_comp');
			$(this).parent().parent().next().addClass('show_comp');
			$("input[name*=ai_initial]").parent().parent().addClass('show_comp');
		}
	});

	if ($("select[name^=mcwp_hide_insurance]").val() === 'yes') {
		$("select[name^=mcwp_hide_insurance]").parent().parent().next().addClass('hide_comp');
		$("input[name*=ai_initial]").parent().parent().addClass('hide_comp');

	}

	$("select[name^=mcwp_hide_insurance_rate]").change(function () {

		if ($(this).val() === 'yes') {
			$(this).parent().parent().next().removeClass('show_comp');
			$("input[name*=wpmc_one_mpmi]").parent().parent().removeClass('show_comp');
			$("input[name*=wpmc_one_inr]").parent().parent().removeClass('show_comp');
			$(this).parent().parent().next().addClass('hide_comp');
			$("input[name*=wpmc_one_mpmi]").parent().parent().addClass('hide_comp');
			$("input[name*=wpmc_one_inr]").parent().parent().addClass('hide_comp');

		} else {

			$(this).parent().parent().next().removeClass('hide_comp');
			$("input[name*=wpmc_one_mpmi]").parent().parent().removeClass('hide_comp');
			$("input[name*=wpmc_one_inr]").parent().parent().removeClass('hide_comp');
			$(this).parent().parent().next().addClass('show_comp');
			$("input[name*=wpmc_one_mpmi]").parent().parent().addClass('show_comp');
			$("input[name*=wpmc_one_inr]").parent().parent().addClass('show_comp');
		}

	});

	if ($("select[name^=mcwp_hide_insurance_rate]").val() === 'yes') {
		$("select[name^=mcwp_hide_insurance_rate]").parent().parent().next().addClass('hide_comp');
		$("input[name*=wpmc_one_mpmi]").parent().parent().addClass('hide_comp');
		$("input[name*=wpmc_one_inr]").parent().parent().addClass('hide_comp');
	}

	$("select[name^=mcwp_hide_hoa]").change(function () {

		if ($(this).val() === 'yes') {
			$(this).parent().parent().next().removeClass('show_comp');
			$("input[name*=mhoa_initial]").parent().parent().removeClass('show_comp');
			$(this).parent().parent().next().addClass('hide_comp');
			$("input[name*=mhoa_initial]").parent().parent().addClass('hide_comp');

		} else {

			$(this).parent().parent().next().removeClass('hide_comp');
			$("input[name*=mhoa_initial]").parent().parent().removeClass('hide_comp');
			$(this).parent().parent().next().addClass('show_comp');
			$("input[name*=mhoa_initial]").parent().parent().addClass('show_comp');
		}

	});
	if ($("select[name^=mcwp_hide_hoa]").val() === 'yes') {
		$("select[name^=mcwp_hide_hoa]").parent().parent().next().addClass('hide_comp');
		$("input[name*=mhoa_initial]").parent().parent().addClass('hide_comp');
	}
	//});
})(jQuery);
