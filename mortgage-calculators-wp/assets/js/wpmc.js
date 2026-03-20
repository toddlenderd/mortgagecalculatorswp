var $mcwp = jQuery.noConflict();
$mcwp(function ($) {

	$(document).on('click', '.mcwp-submit', function (e) {
		//$('.wpmc-submit').on('click',function(e) {
		e.preventDefault();

		if (typeof navigator != 'undefined' && navigator.webdriver) {
			alert(mcwp_ajax.calc_res);
			return false;
		}

		grecaptcha.execute().then((token) => {
			var forma = $(this).closest('form');
			$(forma).find('#g-recaptcha-response').val(token);
			sendForm(forma);
		});

	});

	function sendForm (forma) {
		var serializaFrom = $(forma).serializeArray();
		var post_data = {};
		$.each($(forma).serializeArray(), function () {
			post_data[this.name] = this.value;
		});
		console.log(post_data);
		currentFormEmail = $('input[type="email"]', forma).val();
		if (!validateEmail(currentFormEmail)) {
			alert('Your Email is not valid!');
			return false;
		}

		$.post(mcwp_ajax.ajaxurl, post_data, function (response) {
			alert(mcwp_ajax.calc_res);
		});
	}

	function validateEmail (email) {
		var re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
		return re.test(String(email).toLowerCase());
	}

	$(".ex1").bootstrapSlider();

	function addCommas (intNum) {
		val = intNum;
		var parts = val.toString().split(".");
		parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ",");
		return parts.join(".");
	}

	function removeco (val) {
		/*replace string replace to global string replace function to global replacement*/
		/*return val.replace(',','');	*/
		return val.replace(/,/g, "");
	}
	/****************************************************************************************************************************************
	Conventional Calculator
	**************************************************************************************************************************************/
	/*
	if ($('#inp_purchase_price').val()) {
	};
	*/
	$('form.mcalc-conventional').each(function () {

		var $parent = $(this);
		//mortgage_calc(purchase_price, down_payment, interest_rate, mortgage_term, monthly_tax, monthly_insurance, monthly_hoa, annual_tax_percent);
		var purchase_price = removeco($("#inp_purchase_price", $parent).val());
		var down_payment_percent = $(".down_payment_scrl", $parent).val();
		var down_payment = (purchase_price * down_payment_percent) / 100;
		var interest_rate = $(".interest_rate_scrl", $parent).val();
		var insurance_rate = $(".insurance_rate_scrl", $parent).val();
		var mortgage_term = $("#mortgage_term_yr", $parent).val();
		var annual_tax_percent = $(".annual_tax_scrl", $parent).val();
		var annual_tax = (purchase_price * annual_tax_percent) / 100;
		var monthly_tax = (annual_tax / 12);
		var monthly_insurance = ($("#annual_insurance_inp", $parent).val() / 12);
		var mountly_pmi = (purchase_price * insurance_rate) / 12;
		var monthly_hoa = $("#monthly_hoa_inp", $parent).val();
		$("#down_payment_inp", $parent).val(addCommas(down_payment));
		$("#annual_tax_inp", $parent).val(addCommas(annual_tax));
		mortgage_calc($parent);

		$parent.find('#mortgage_term_yr').on('change', function (event) {
			//$(document).on('change', '#mortgage_term_yr', function () {
			//purchase * down payment
			if (this.value == 15) {
				mortgage_term_yr = .0045;
			} else {
				mortgage_term_yr = .0085;
			}
			//mortgage_calc(purchase_price, down_payment, interest_rate, this.value, monthly_tax, monthly_insurance, monthly_hoa, annual_tax_percent);
			mortgage_calc($parent);
		});

		//mortgage_calc($parent);
		$parent.find('#monthly_hoa_inp').on('keyup', function (event) {
			//hideShowLayer(markerLayer);
			monthly_hoa = $(this).val() == "" ? 0 : $(this).val();
			$(this).val(function (index, value) {
				return value
					.replace(/\D/g, "")
					.replace(/\B(?=(\d{3})+(?!\d))/g, ",");
			});
			//mortgage_calc(purchase_price, down_payment, interest_rate, mortgage_term, monthly_tax, monthly_insurance, monthly_hoa, annual_tax_percent);
			mortgage_calc($parent);
		});

		$parent.find(".ex1").on("change", function (slideEvt) {
			if ($(this).hasClass("down_payment_scrl")) {
				down_payment_percent = slideEvt.value.newValue;
				$(this).next("p").text(roundOff(down_payment_percent) + "%");
				down_payment = (purchase_price * down_payment_percent) / 100;
				$("#down_payment_inp", $parent).val(addCommas(down_payment));
				//mortgage_calc(purchase_price, down_payment, interest_rate, mortgage_term, monthly_tax, monthly_insurance, monthly_hoa, annual_tax_percent);
			}
			if ($(this).hasClass("annual_tax_scrl")) {
				annual_tax_percent = slideEvt.value.newValue;
				$(this).next("p").text(annual_tax_percent + "%");
				annual_tax = (purchase_price * annual_tax_percent) / 100;
				monthly_tax = (annual_tax / 12);
				$("#annual_tax_inp", $parent).val(addCommas(roundOff(annual_tax)));
				//mortgage_calc(purchase_price, down_payment, interest_rate, mortgage_term, monthly_tax, monthly_insurance, monthly_hoa, annual_tax_percent);
			}
			if ($(this).hasClass("interest_rate_scrl")) {
				interest_rate = slideEvt.value.newValue;
				$(this).next("p").text(interest_rate + "%");
				//mortgage_calc(purchase_price, down_payment, interest_rate, mortgage_term, monthly_tax, monthly_insurance, monthly_hoa, annual_tax_percent);
			}
			if ($(this).hasClass("insurance_rate_scrl")) {
				insurance_rate = slideEvt.value.newValue;
				$(this).next("p").text(insurance_rate + "%");
				//mortgage_calc(purchase_price, down_payment, interest_rate, mortgage_term, monthly_tax, monthly_insurance, monthly_hoa, annual_tax_percent);
			}
			mortgage_calc($parent);
		});

		$parent.find("#inp_purchase_price").keyup(function () {
			purchase_price = $(this).val() == "" ? 0 : $(this).val();
			purchase_price = removeco(purchase_price);
			down_payment = (purchase_price * down_payment_percent) / 100;
			$("#down_payment_inp", $parent).val(addCommas(down_payment));
			// $(this).val(purchase_price);
			$(this).val(function (index, value) {
				return value
					.replace(/\D/g, "")
					.replace(/\B(?=(\d{3})+(?!\d))/g, ",");
			});
			mortgage_calc($parent);
		});

		$parent.find("#down_payment_inp").keyup(function () {
			down_payment = removeco($(this).val()) == "" ? 0 : removeco($(this).val());
			down_payment_percent = (down_payment / purchase_price) * 100;
			$(".down_payment_scrl", $parent).bootstrapSlider('setValue', down_payment_percent).next("p").text(roundOff(down_payment_percent) + "%");
			$(this).val(function (index, value) {
				return value
					.replace(/\D/g, "")
					.replace(/\B(?=(\d{3})+(?!\d))/g, ",");
			});
			mortgage_calc($parent);
		});

		$parent.find("#annual_tax_inp").keyup(function () {
			annual_tax = $(this).val() == "" ? 0 : removeco($(this).val());
			annual_tax_percent = (annual_tax / purchase_price) * 100;
			$(".annual_tax_scrl", $parent).bootstrapSlider('setValue', annual_tax_percent).next("p").text(roundOff(annual_tax_percent) + "%");
			$(this).val(function (index, value) {
				return value
					.replace(/\D/g, "")
					.replace(/\B(?=(\d{3})+(?!\d))/g, ",");
			});
			$("#annual_tax_inp", $parent).val(addCommas(annual_tax));
			monthly_tax = (annual_tax / 12);
			mortgage_calc($parent);
		});
		$parent.find("#annual_insurance_inp").keyup(function () {
			monthly_insurance = $(this).val() == "" ? 0 : (removeco($(this).val()) / 12);
			$(this).val(function (index, value) {
				return value
					.replace(/\D/g, "")
					.replace(/\B(?=(\d{3})+(?!\d))/g, ",");
			});
			mortgage_calc($parent);
		});


	});
	//function mortgage_calc(price, down, rate, term, tax, insurance, hoa) {
	function mortgage_calc ($parent) {
		price = removeco($("#inp_purchase_price", $parent).val());
		down_payment = removeco($("#down_payment_inp", $parent).val()) == "" ? 0 : removeco($("#down_payment_inp", $parent).val());
		down_payment_percent = (down_payment / price) * 100;
		down = (price * down_payment_percent) / 100;
		rate = $(".interest_rate_scrl", $parent).val();
		inrate = $(".insurance_rate_scrl", $parent).val();
		term = $("#mortgage_term_yr", $parent).val();
		//annual_tax_percent = $(".annual_tax_scrl", $parent).val();
		annual_tax_percentNew = $(".annual_tax_scrl", $parent).next("p").text();
		annual_tax_percentNew = annual_tax_percentNew.replace("%", "");
		annual_tax = (price * annual_tax_percentNew) / 100;
		tax = (annual_tax / 12);
		mpmi = ((inrate / 100) * (price - down)) / 12;

		insurance = removeco($("#annual_insurance_inp", $parent).val()) / 12;
		hoa = removeco($("#monthly_hoa_inp").val()) == "" ? 0 : removeco($("#monthly_hoa_inp", $parent).val());
		var n = parseInt(term) * 12;
		var c = parseFloat(rate) / 1200;
		var L = parseInt(price) - parseFloat(down);
		var p = Math.round((L * (c * Math.pow(1 + c, n))) / (Math.pow(1 + c, n) - 1));
		var emmp = parseFloat(p) + parseFloat(tax) + parseFloat(insurance) + parseFloat(hoa);
		changethis = roundOff(emmp, 2);
		$("#emmp_div_span", $parent).text(addCommas(roundOff(emmp, 2)));
		$("#pi_div_span", $parent).text(addCommas(p));
		$("#mtax_div_span", $parent).text(addCommas(roundOff(tax, 2)));
		$("#minsure_div_span", $parent).text(addCommas(roundOff(insurance, 2)));
		$("#hoa_div_span", $parent).text(addCommas(hoa));

		if (down_payment_percent >= 20) {
			$(".mcwp-insurance-rate").hide();
			$(".mcwp-insurance-rate-disabled").show();
			mpmi = 0;
		} else {
			$(".mcwp-insurance-rate").show();
			$(".mcwp-insurance-rate-disabled").hide();
		}
		$("#mpmi", $parent).text(addCommas(roundOff(mpmi, 2)));

		$(".emmp_div_span", $parent).val(addCommas(roundOff(emmp, 2)));
		$(".pi_div_span", $parent).val(addCommas(p));
		$(".mtax_div_span", $parent).val(addCommas(roundOff(tax, 2)));
		$(".minsure_div_span", $parent).val(addCommas(roundOff(insurance, 2)));
		$(".mpmi_div_span", $parent).val(addCommas(roundOff(mpmi, 2)));
		$(".hoa_div_span", $parent).val(addCommas(hoa));
		//$("#down_payment_inp", $parent).val(addCommas(down));
		//$("#annual_tax_inp", $parent).val(addCommas(annual_tax));
	}
	/****************************************************************************************************************************************
	FHA Calculator
	**************************************************************************************************************************************/

	if ($('#fha_inp_purchase_price').val()) {
		var fha_purchase_price = removeco($("#fha_inp_purchase_price").val());
		var fha_down_payment_percent = $(".fha_down_payment_scrl").val();
		var fha_down_payment = (fha_purchase_price * fha_down_payment_percent) / 100;
		var fha_interest_rate = $(".fha_interest_rate_scrl").val();
		var fha_mortgage_term = $("#fha_mortgage_term_yr").val();
		var fha_annual_tax_percent = $(".fha_annual_tax_scrl").val();
		var fha_annual_tax = (fha_purchase_price * fha_annual_tax_percent) / 100;
		var fha_monthly_tax = (fha_annual_tax / 12);
		var fha_monthly_insurance = ($("#fha_annual_insurance_inp").val() / 12);
		var monthly_mortgage_insurance = ($("#fha_mmi_div_span").val() / 200);
		var fha_monthly_hoa = $("#fha_monthly_hoa_inp").val();
		$("#fha_down_payment_inp").val(addCommas(fha_down_payment));
		$("#fha_annual_tax_inp").val(addCommas(fha_annual_tax));
		fha_mortgage_calc(fha_purchase_price, fha_down_payment, fha_interest_rate, fha_mortgage_term, fha_monthly_tax, fha_annual_tax_percent, fha_monthly_insurance, fha_monthly_hoa);
	};
	$(document).on('change', '#fha_mortgage_term_yr', function () {
		fha_mortgage_calc(fha_purchase_price, fha_down_payment, fha_interest_rate, this.value, fha_monthly_tax, fha_annual_tax_percent, fha_monthly_insurance, fha_monthly_hoa);
	});
	$(".ex1").on("change", function (slideEvt) {
		if ($(this).hasClass("fha_down_payment_scrl")) {
			fha_down_payment_percent = slideEvt.value.newValue;
			$(this).next("p").text(roundOff(fha_down_payment_percent) + "%");
			fha_down_payment = (fha_purchase_price * fha_down_payment_percent) / 100;
			$("#fha_down_payment_inp").val(addCommas(fha_down_payment));
			fha_mortgage_calc(fha_purchase_price, fha_down_payment, fha_interest_rate, fha_mortgage_term, fha_monthly_tax, fha_annual_tax_percent, fha_monthly_insurance, fha_monthly_hoa);
		}
		if ($(this).hasClass("fha_annual_tax_scrl")) {
			fha_annual_tax_percent = slideEvt.value.newValue;
			$(this).next("p").text(fha_annual_tax_percent + "%");
			fha_annual_tax = (fha_purchase_price * fha_annual_tax_percent) / 100;
			fha_monthly_tax = (fha_annual_tax / 12);
			$("#fha_annual_tax_inp").val(addCommas(roundOff(fha_annual_tax)));
			fha_mortgage_calc(fha_purchase_price, fha_down_payment, fha_interest_rate, fha_mortgage_term, fha_monthly_tax, fha_annual_tax_percent, fha_monthly_insurance, fha_monthly_hoa, 'no');
		}
		if ($(this).hasClass("fha_interest_rate_scrl")) {
			fha_interest_rate = slideEvt.value.newValue;
			$(this).next("p").text(fha_interest_rate) + "%";
			fha_mortgage_calc(fha_purchase_price, fha_down_payment, fha_interest_rate, fha_mortgage_term, fha_monthly_tax, fha_annual_tax_percent, fha_monthly_insurance, fha_monthly_hoa);
		}
	});
	$("#fha_inp_purchase_price").keyup(function () {
		fha_down_payment_percent = Number($(".fha_down_payment_scrl").val());
		fha_purchase_price = $(this).val() == "" ? 0 : removeco($(this).val());
		fha_down_payment = (fha_purchase_price * fha_down_payment_percent) / 100;
		fha_down_payment = fha_down_payment;
		$("#fha_down_payment_inp").val(addCommas(fha_down_payment));
		$(this).val(function (index, value) {
			return value
				.replace(/\D/g, "")
				.replace(/\B(?=(\d{3})+(?!\d))/g, ",");
		});
		fha_mortgage_calc(fha_purchase_price, fha_down_payment, fha_interest_rate, fha_mortgage_term, fha_monthly_tax, fha_annual_tax_percent, fha_monthly_insurance, fha_monthly_hoa);
	});
	$("#fha_down_payment_inp").keyup(function () {
		fha_down_payment = $(this).val() == "" ? 0 : removeco($(this).val());
		fha_down_payment = parseInt(fha_down_payment);
		fha_down_payment_percent = (fha_down_payment / fha_purchase_price) * 100;
		$(".fha_down_payment_scrl").bootstrapSlider('setValue', fha_down_payment_percent).next("p").text(roundOff(fha_down_payment_percent) + "%");
		$(this).val(function (index, value) {
			return value
				.replace(/\D/g, "")
				.replace(/\B(?=(\d{3})+(?!\d))/g, ",");
		});
		fha_mortgage_calc(fha_purchase_price, fha_down_payment, fha_interest_rate, fha_mortgage_term, fha_monthly_tax, fha_annual_tax_percent, fha_monthly_insurance, fha_monthly_hoa);
	});
	$("#fha_annual_tax_inp").keyup(function () {
		fha_annual_tax = $(this).val() == "" ? 0 : removeco($(this).val());
		fha_annual_tax = Number(fha_annual_tax);
		fha_annual_tax = roundOff(fha_annual_tax);
		fha_annual_tax_percent = (fha_annual_tax / fha_purchase_price) * 100;
		$(".fha_annual_tax_scrl").bootstrapSlider('setValue', fha_annual_tax_percent).next("p").text(roundOff(fha_annual_tax_percent) + "%");
		$(this).val(function (index, value) {
			newval = value.replace(/\D/g, "").replace(/\B(?=(\d{3})+(?!\d))/g, ",");
			return newval;
		});
		fha_mortgage_calc(fha_purchase_price, fha_down_payment, fha_interest_rate, fha_mortgage_term, fha_monthly_tax, fha_annual_tax_percent, fha_monthly_insurance, fha_monthly_hoa);
		fha_monthly_tax = (fha_annual_tax / 12);
		$(".mtchange").empty();
		$(".mtchange").empty().text(addCommas(roundOff(fha_monthly_tax, 2)));
	});
	$("#fha_annual_insurance_inp").keyup(function () {
		fha_monthly_insurance = $(this).val() == "" ? 0 : ($(this).val() / 12);
		$(this).val(function (index, value) {
			return value
				.replace(/\D/g, "")
				.replace(/\B(?=(\d{3})+(?!\d))/g, ",");
		});
		fha_mortgage_calc(fha_purchase_price, fha_down_payment, fha_interest_rate, fha_mortgage_term, fha_monthly_tax, fha_annual_tax_percent, fha_monthly_insurance, fha_monthly_hoa);
	});
	$("#fha_mmi_div_span").keyup(function () {
		monthly_mortgage_insurance = $(this).val() == "" ? 0 : ($(this).val() / 0);
		$(this).val(function (index, value) {
			return value
				.replace(/\D/g, "")
				.replace(/\B(?=(\d{3})+(?!\d))/g, ",");
		});
		fha_mortgage_calc(fha_purchase_price, fha_down_payment, fha_interest_rate, fha_mortgage_term, fha_monthly_tax, fha_annual_tax_percent, fha_monthly_insurance, monthly_mortgage_insurance, fha_monthly_hoa);
	});
	$("#fha_monthly_hoa_inp").keyup(function () {
		fha_monthly_hoa = $(this).val() == "" ? 0 : $(this).val();
		$(this).val(function (index, value) {
			return value
				.replace(/\D/g, "")
				.replace(/\B(?=(\d{3})+(?!\d))/g, ",");
		});
		fha_mortgage_calc(fha_purchase_price, fha_down_payment, fha_interest_rate, fha_mortgage_term, fha_monthly_tax, fha_annual_tax_percent, fha_monthly_insurance, fha_monthly_hoa);
	});

	function fha_mortgage_calc (price, down, rate, term, tax, tax_prcnt, insurance, hoa, show) {

		price = removeco($("#fha_inp_purchase_price").val());
		fha_down_payment_percent = $(".fha_down_payment_scrl").val();
		down = (price * fha_down_payment_percent) / 100;
		rate = $(".fha_interest_rate_scrl").val();
		term = $("#fha_mortgage_term_yr").val();

		//tax_prcnt = $(".fha_annual_tax_scrl").val();
		//fha_annual_tax = (price * tax_prcnt)/100;
		//tax = (fha_annual_tax/12);

		insurance = removeco($("#fha_annual_insurance_inp").val()) / 12;
		//monthly_mortgage_insurance = ($("#fha_mmi_div_span").val()/200);
		hoa = removeco($("#fha_monthly_hoa_inp").val()) == "" ? 0 : removeco($("#fha_monthly_hoa_inp").val());

		var n = parseInt(term) * 12;
		var c = parseFloat(rate) / 1200;
		var L = parseInt(price) - parseFloat(down);
		var p = Math.round((L * (c * Math.pow(1 + c, n))) / (Math.pow(1 + c, n) - 1));
		//var arr = {PI:p, EMMP:parseInt(p)+parseInt(tax)+parseInt(insurance)+parseInt(hoa)};
		if (term == 15) {
			tax_prcnt = 0.45;
		} else if (term == 30) {
			tax_prcnt = 0.85;
		}
		var mmi = Math.round(((parseInt(L) + parseInt(L * (rate / 100))) * (tax_prcnt / 100)) / 12);
		var emmp = parseFloat(p) + parseFloat(tax) + parseFloat(insurance) + parseFloat(hoa) + parseFloat(mmi);
		$("#fha_emmp_div_span").text(addCommas(roundOff(emmp, 2)));
		$("#fha_pi_div_span").text(addCommas(p));
		$("#fha_mtax_div_span").text(addCommas(roundOff(tax, 2)));
		$("#fha_minsure_div_span").text(addCommas(roundOff(insurance, 2)));
		if (show != 'no') {
			$("#fha_mmi_div_span").text(addCommas(mmi));
		}
		$("#fha_hoa_div_span").text(addCommas(hoa));


		$(".fha_emmp_div_span").val(addCommas(roundOff(emmp, 2)));
		$(".fha_pi_div_span").val(addCommas(p));
		$(".fha_mtax_div_span").val(addCommas(roundOff(tax, 2)));
		$(".fha_minsure_div_span").val(addCommas(roundOff(insurance, 2)));
		if (show != 'no') {
			$(".fha_mmi_div_span").val(addCommas(mmi));
		}
		$(".fha_hoa_div_span").val(addCommas(hoa));



	}
	/****************************************************************************************************************************************
	VA Calculator
	**************************************************************************************************************************************/
	if ($('#va_inp_purchase_price').val()) {
		var va_purchase_price = $("#va_inp_purchase_price").val();
		va_purchase_price = removeco(va_purchase_price);
		var va_down_payment_percent = $(".va_down_payment_scrl").val();
		var va_down_payment = (va_purchase_price * va_down_payment_percent) / 100;
		var va_interest_rate = $(".va_interest_rate_scrl").val();
		var va_mortgage_term = $("#va_mortgage_term_yr").val();
		var va_annual_tax_percent = $(".va_annual_tax_scrl").val();
		var va_annual_tax = (va_purchase_price * va_annual_tax_percent) / 100;
		var va_monthly_tax = (va_annual_tax / 12);
		var va_monthly_insurance = (removeco($("#va_annual_insurance_inp").val()) / 12);
		var va_monthly_hoa = $("#va_monthly_hoa_inp").val();
		$("#va_down_payment_inp").val(addCommas(va_down_payment));
		$("#va_annual_tax_inp").val(addCommas(va_annual_tax));
		va_mortgage_calc(va_purchase_price, va_down_payment, va_interest_rate, va_mortgage_term, va_monthly_tax, va_down_payment_percent, va_monthly_insurance, va_monthly_hoa);
	};
	$(document).on('change', '#va_mortgage_term_yr', function () {
		va_mortgage_calc(va_purchase_price, va_down_payment, va_interest_rate, this.value, va_monthly_tax, va_down_payment_percent, va_monthly_insurance, va_monthly_hoa);
	});
	$(".ex1").on("change", function (slideEvt) {
		if ($(this).hasClass("va_down_payment_scrl")) {
			va_down_payment_percent = slideEvt.value.newValue;
			$(this).next("p").text(va_down_payment_percent + "%");
			va_down_payment = (va_purchase_price * va_down_payment_percent) / 100;
			$("#va_down_payment_inp").val(addCommas(va_down_payment));
			va_mortgage_calc(va_purchase_price, va_down_payment, va_interest_rate, va_mortgage_term, va_monthly_tax, va_down_payment_percent, va_monthly_insurance, va_monthly_hoa);
		}
		if ($(this).hasClass("va_annual_tax_scrl")) {
			va_annual_tax_percent = slideEvt.value.newValue;
			$(this).next("p").text(roundOff(va_annual_tax_percent) + "%");
			va_annual_tax = (va_purchase_price * va_annual_tax_percent) / 100;
			va_monthly_tax = (va_annual_tax / 12);
			$("#va_annual_tax_inp").val(addCommas(roundOff(va_annual_tax)));
			va_mortgage_calc(va_purchase_price, va_down_payment, va_interest_rate, va_mortgage_term, va_monthly_tax, va_down_payment_percent, va_monthly_insurance, va_monthly_hoa);
		}
		if ($(this).hasClass("va_interest_rate_scrl")) {
			va_interest_rate = slideEvt.value.newValue;
			$(this).next("p").text(va_interest_rate) + "%";
			va_mortgage_calc(va_purchase_price, va_down_payment, va_interest_rate, va_mortgage_term, va_monthly_tax, va_down_payment_percent, va_monthly_insurance, va_monthly_hoa);
		}
	});

	$("#va_inp_purchase_price").keyup(function () {
		va_purchase_price = $(this).val() == "" ? 0 : $(this).val();
		va_purchase_price = removeco(va_purchase_price);
		va_down_payment = (va_purchase_price * va_down_payment_percent) / 100;
		$("#va_down_payment_inp").val(addCommas(va_down_payment));
		$(this).val(function (index, value) {
			return value
				.replace(/\D/g, "")
				.replace(/\B(?=(\d{3})+(?!\d))/g, ",");
		});
		va_mortgage_calc(va_purchase_price, va_down_payment, va_interest_rate, va_mortgage_term, va_monthly_tax, va_down_payment_percent, va_monthly_insurance, va_monthly_hoa);
	});
	$("#va_down_payment_inp").keyup(function () {
		va_down_payment = $(this).val() == "" ? 0 : $(this).val();
		va_down_payment = removeco(va_down_payment);
		va_down_payment_percent = (va_down_payment / va_purchase_price) * 100;
		$(".va_down_payment_scrl").bootstrapSlider('setValue', va_down_payment_percent).next("p").text(roundOff(va_down_payment_percent) + "%");
		$(this).val(function (index, value) {
			return value
				.replace(/\D/g, "")
				.replace(/\B(?=(\d{3})+(?!\d))/g, ",");
		});
		va_mortgage_calc(va_purchase_price, va_down_payment, va_interest_rate, va_mortgage_term, va_monthly_tax, va_down_payment_percent, va_monthly_insurance, va_monthly_hoa);
	});



	$("#va_annual_tax_inp").keyup(function () {
		va_annual_tax = $(this).val() == "" ? 0 : $(this).val();
		va_annual_tax = removeco(va_annual_tax);
		va_annual_tax_percent = (va_annual_tax / va_purchase_price) * 100;
		$(this).val(function (index, value) {
			return value
				.replace(/\D/g, "")
				.replace(/\B(?=(\d{3})+(?!\d))/g, ",");
		});
		va_monthly_tax = (va_annual_tax / 12);
		$(".va_annual_tax_scrl").bootstrapSlider('setValue', va_annual_tax_percent).next("p").text(roundOff(va_annual_tax_percent) + "%");
		va_mortgage_calc(va_purchase_price, va_down_payment, va_interest_rate, va_mortgage_term, va_monthly_tax, va_down_payment_percent, va_monthly_insurance, va_monthly_hoa);
	});
	$("#va_annual_insurance_inp").keyup(function () {
		va_monthly_insurance = $(this).val() == "" ? 0 : (removeco($(this).val()) / 12);
		$(this).val(function (index, value) {
			return value
				.replace(/\D/g, "").replace(/\B(?=(\d{3})+(?!\d))/g, ",");
		});
		va_mortgage_calc(va_purchase_price, va_down_payment, va_interest_rate, va_mortgage_term, va_monthly_tax, va_down_payment_percent, va_monthly_insurance, va_monthly_hoa);

	});
	$("#va_monthly_hoa_inp").keyup(function () {
		va_monthly_hoa = $(this).val() == "" ? 0 : $(this).val();
		$(this).val(function (index, value) {
			return value
				.replace(/\D/g, "").replace(/\B(?=(\d{3})+(?!\d))/g, ",");
		});
		va_mortgage_calc(va_purchase_price, va_down_payment, va_interest_rate, va_mortgage_term, va_monthly_tax, va_down_payment_percent, va_monthly_insurance, va_monthly_hoa);

	});
	$(document).on('change', '#va_service_type', function () {
		va_monthly_insurance = (removeco($("#va_annual_insurance_inp").val()) / 12);
		va_mortgage_calc(va_purchase_price, va_down_payment, va_interest_rate, va_mortgage_term, va_monthly_tax, va_down_payment_percent, va_monthly_insurance, va_monthly_hoa);
	});
	$(document).on('change', '#va_frist_time', function () {
		va_monthly_insurance = (removeco($("#va_annual_insurance_inp").val()) / 12);
		va_mortgage_calc(va_purchase_price, va_down_payment, va_interest_rate, va_mortgage_term, va_monthly_tax, va_down_payment_percent, va_monthly_insurance, va_monthly_hoa);
	});


	function va_mortgage_calc (price, down, rate, term, tax, down_prcnt, insurance, hoa) {

		var va_purchase_price = $("#va_inp_purchase_price").val();
		price = removeco(va_purchase_price);

		va_down_payment_percent = $(".va_down_payment_scrl").val();
		down = (price * va_down_payment_percent) / 100;

		rate = $(".va_interest_rate_scrl").val();
		term = $("#va_mortgage_term_yr").val();
		a_annual_tax_percent = $(".va_annual_tax_scrl").val();
		va_annual_tax = (price * va_annual_tax_percent) / 100;
		tax = (va_annual_tax / 12);
		insurance = (removeco($("#va_annual_insurance_inp").val()) / 12);
		hoa = removeco($("#va_monthly_hoa_inp").val()) == "" ? 0 : removeco($("#va_monthly_hoa_inp").val());

		//console.warn("purchase_price "+purchase_price+", down_payment "+down_payment+", interest_rate "+interest_rate+", mortgage_term "+mortgage_term)
		//e_rate = 0;
		//console.log(down_prcnt);
		if ($("#va_frist_time").val() == "yes") {
			if ($("#va_service_type").val() == "regular_military") {
				if (down_prcnt == 0) {
					e_rate = 2.15;
				}
				if (down_prcnt >= 5 && down_prcnt < 10) {
					e_rate = 1.50;
				}
				if (down_prcnt >= 10) {
					e_rate = 1.25;
				}
			}
			if ($("#va_service_type").val() == "reserves_national") {
				if (down_prcnt == 0) {
					e_rate = 2.4;
				}
				if (down_prcnt >= 5 && down_prcnt < 10) {
					e_rate = 1.75;
				}
				if (down_prcnt >= 10) {
					e_rate = 1.5;
				}
			}
		} else if ($("#va_frist_time").val() == "no") {
			if ($("#va_service_type").val() == "regular_military") {
				if (down_prcnt == 0) {
					e_rate = 3.3;
				}
				if (down_prcnt >= 5 && down_prcnt < 10) {
					e_rate = 1.50;
				}
				if (down_prcnt >= 10) {
					e_rate = 1.25;
				}
			}
			if ($("#va_service_type").val() == "reserves_national") {
				if (down_prcnt == 0) {
					e_rate = 3.3;
				}
				if (down_prcnt >= 5 && down_prcnt < 10) {
					e_rate = 1.75;
				}
				if (down_prcnt >= 10) {
					e_rate = 1.5;
				}
			}
		}
		//price, down, rate, term, tax, down_prcnt, insurance, hoa
		var n = parseInt(term) * 12;
		var c = parseFloat(rate) / 1200;
		var L = parseInt(price) - parseFloat(down);
		var p = Math.round((L * (c * Math.pow(1 + c, n))) / (Math.pow(1 + c, n) - 1));
		var vaff = Math.round((L * e_rate) / 18000);
		//var arr = {PI:p, EMMP:parseInt(p)+parseInt(tax)+parseInt(insurance)+parseInt(hoa)};
		var emmp = parseFloat(p) + parseFloat(tax) + parseFloat(insurance) + parseFloat(hoa) + parseFloat(vaff);
		$("#va_emmp_div_span").text(addCommas(roundOff(emmp, 2)));
		$("#va_pi_div_span").text(addCommas(p));
		$("#va_mtax_div_span").text(addCommas(roundOff(tax, 2)));
		$("#va_minsure_div_span").text(addCommas(roundOff(insurance, 2)));
		$("#va_hoa_div_span").text(addCommas(hoa));
		//$("#va_funding_fee_div_span").text(addCommas(hoa));
		$("#va_purchase_p_span").text(addCommas(price));
		$("#va_funding_fee_p_span").html(addCommas((L * e_rate) / 100));
		va_funding_fee_p_span = removeco($("#va_funding_fee_p_span").text());
		va_funding_fee_p_span = Number(va_funding_fee_p_span);
		//(Purchase Price - Down Payment) + VA Funding Fee
		va_purchase_price = removeco($("#va_inp_purchase_price").val());
		va_purchase_price = Number(va_purchase_price);
		va_down_payment_percent = $(".va_down_payment_scrl").val();
		va_down_payment_percent = Number(va_down_payment_percent);
		va_down_payment = (va_purchase_price * va_down_payment_percent) / 100;
		va_amount_finance_p_span = (va_purchase_price - va_down_payment) + va_funding_fee_p_span;
		$("#va_amount_finance_p_span").html(addCommas(roundOff(Number(va_amount_finance_p_span))));

		$(".va_emmp_div_span").val($("#va_emmp_div_span").text());
		$(".va_pi_div_span").val($("#va_pi_div_span").text());
		$(".va_mtax_div_span").val($("#va_mtax_div_span").text());
		$(".va_minsure_div_span").val($("#va_minsure_div_span").text());
		$(".va_hoa_div_span").val($("#va_hoa_div_span").text());
		//$("#va_funding_fee_div_span").val($("#va_funding_fee_div_span").text());.text());
		//$(".va_purchase_p_span").val($("#va_purchase_p_span").text());
		$(".va_funding_fee_p_span").val($("#va_funding_fee_p_span").text());
		$(".va_amount_finance_p_span").val($("#va_amount_finance_p_span").text());
		//$("#va_amount_finance_p_span").html(addCommas(parseInt(L)+((L*e_rate)/100)));
	}

	function roundOff (number, precision) {
		num = number;
		return num.toFixed(2);
	}
	/****************************************************************************************************************************************
	Home Affordability Calculator
	**************************************************************************************************************************************/
	function mha_mortgage_calc () {
		var mha_annual_income = removeco($("#mha_annual_income").val());
		$("#mha_annual_income").val(addCommas(mha_annual_income));
		var mha_interest_rate = removeco($("#mha_interest_rate").val());
		$("#mha_interest_rate").val(addCommas(mha_interest_rate));
		var mha_monthly_debts = removeco($("#mha_monthly_debts").val());
		$("#mha_monthly_debts").val(addCommas(mha_monthly_debts));

		var mha_estimated_annual_home_insurance = removeco($("#mha_estimated_annual_home_insurance").val());
		$("#mha_estimated_annual_home_insurance").val(addCommas(mha_estimated_annual_home_insurance));
		var mha_estimated_annual_property_taxes = removeco($("#mha_estimated_annual_property_taxes").val());
		$("#mha_estimated_annual_property_taxes").val(addCommas(mha_estimated_annual_property_taxes));
		var mha_down_payment = removeco($("#mha_down_payment").val());
		$("#mha_down_payment").val(addCommas(mha_down_payment));
		var mha_loan_term = removeco($("#mha_loan_term").val());
		$("#mha_loan_term").val(addCommas(mha_loan_term));
		if (mha_loan_term == "") {
			mha_loan_term = 30;
		}

		if (mha_annual_income != "" && mha_interest_rate != "") {
			var minimum_monthly_debts = ((mha_annual_income * 8) / 100) / 12;
			if (mha_monthly_debts == '' || mha_monthly_debts < minimum_monthly_debts) {
				var mha_est_monthly_payment = ((mha_annual_income / 12) * 0.40) - minimum_monthly_debts;

				if (mha_estimated_annual_property_taxes != '') {
					var mha_taxes = mha_estimated_annual_property_taxes / 12;
				} else {
					var mha_taxes = 0;
				}
				if (mha_estimated_annual_home_insurance != '') {
					var mha_insurance = mha_estimated_annual_home_insurance / 12;
				} else {
					var mha_insurance = 0;
				}
				var mha_P_I = mha_est_monthly_payment - (mha_taxes + mha_insurance);

				var aaaaa = (mha_interest_rate / 100 / 12);
				var mpowerdata = Math.pow((1 + (mha_interest_rate / 100 / 12)), (-mha_loan_term * 12));
				var bbbbb = (1 - (mpowerdata));
				var ab = bbbbb / aaaaa;

				if (ab < 0) {
					var temp = ab * mha_P_I;
					var ab_total = temp - parseInt(mha_down_payment);
				} else {
					var temp = ab * mha_P_I;
					var ab_total = temp + parseInt(mha_down_payment);
				}
				if (ab_total <= 0) {
					$("#mha_afford_house_div_span").html('0');
				} else {
					$("#mha_afford_house_div_span").html(Number(Math.round(ab_total)).toLocaleString('en'));
				}
				if (mha_est_monthly_payment <= 0) {
					$("#mha_emmp_div_span").html('0');
				} else {
					$("#mha_emmp_div_span").html(Number(Math.round(mha_est_monthly_payment)).toLocaleString('en'));
				}
				if (mha_P_I <= 0) {
					$("#mha_pi_div_span").html('0');
				} else {
					$("#mha_pi_div_span").html(Number(Math.round(mha_P_I)).toLocaleString('en'));
				}
				if (mha_taxes <= 0) {
					$("#mha_taxes_div_span").html('0');
				} else {
					$("#mha_taxes_div_span").html(Number(Math.round(mha_taxes)).toLocaleString('en'));
				}
				if (mha_insurance <= 0) {
					$("#mha_insurance_div_span").html('0');
				} else {
					$("#mha_insurance_div_span").html(Number(Math.round(mha_insurance)).toLocaleString('en'));
				}
			} else {
				var mha_est_monthly_payment = ((mha_annual_income / 12) * 0.40) - mha_monthly_debts;
				if (mha_estimated_annual_property_taxes != '') {
					var mha_taxes = mha_estimated_annual_property_taxes / 12;
				} else {
					var mha_taxes = 0;
				}
				if (mha_estimated_annual_home_insurance != '') {
					var mha_insurance = mha_estimated_annual_home_insurance / 12;
				} else {
					var mha_insurance = 0;
				}
				var mha_P_I = mha_est_monthly_payment - (mha_taxes + mha_insurance);

				var aaaaa = (mha_interest_rate / 100 / 12);
				var mpowerdata = Math.pow((1 + (mha_interest_rate / 100 / 12)), (-mha_loan_term * 12));
				var bbbbb = (1 - (mpowerdata));
				var ab = bbbbb / aaaaa;

				if (ab < 0) {
					var temp = ab * mha_P_I;
					var ab_total = temp - parseInt(mha_down_payment);
				} else {
					var temp = ab * mha_P_I;
					var ab_total = temp + parseInt(mha_down_payment);
				}

				if (ab_total <= 0) {
					$("#mha_afford_house_div_span").html('0');
				} else {
					$("#mha_afford_house_div_span").html(Number(Math.round(ab_total)).toLocaleString('en'));
				}
				if (mha_est_monthly_payment <= 0) {
					$("#mha_emmp_div_span").html('0');
				} else {
					$("#mha_emmp_div_span").html(Number(Math.round(mha_est_monthly_payment)).toLocaleString('en'));
				}
				if (mha_P_I <= 0) {
					$("#mha_pi_div_span").html('0');
				} else {
					$("#mha_pi_div_span").html(Number(Math.round(mha_P_I)).toLocaleString('en'));
				}
				if (mha_taxes <= 0) {
					$("#mha_taxes_div_span").html('0');
				} else {
					$("#mha_taxes_div_span").html(Number(Math.round(mha_taxes)).toLocaleString('en'));
				}
				if (mha_insurance <= 0) {
					$("#mha_insurance_div_span").html('0');
				} else {
					$("#mha_insurance_div_span").html(Number(Math.round(mha_insurance)).toLocaleString('en'));
				}
			}
		}
	}
	$('form.mcalc-ha').each(function () {
		var este = $(this);

		mha_mortgage_calc();
		mha_input_vals(este);

		$('input', este).each(function () {
			$(this).on('keyup', function () {
				mha_mortgage_calc();
				mha_input_vals(este);
			});
		});
		$('select', este).each(function () {
			$(this).on('change', function () {
				mha_mortgage_calc();
				mha_input_vals(este);
			});
		});

	});

	function mha_input_vals (este) {
		$(".mha_insurance_div_span", este).val($("#mha_insurance_div_span", este).text());
		$(".mha_taxes_div_span", este).val($("#mha_taxes_div_span", este).text());
		$(".mha_pi_div_span", este).val($("#mha_pi_div_span", este).text());
		$(".mha_emmp_div_span", este).val($("#mha_emmp_div_span", este).text());
		$(".mha_afford_house_div_span", este).val($("#mha_afford_house_div_span", este).text());
	}
	/****************************************************************************************************************************************
	Refinance Calculator
	**************************************************************************************************************************************/
	function rc_mortgage_calc () {

		var rc_original_loan_amount = removeco($("#rc_original_loan_amount").val());
		$("#rc_original_loan_amount").val(addCommas(rc_original_loan_amount));
		var rc_interest_rate = removeco($("#rc_interest_rate").val());
		$("#rc_interest_rate").val(addCommas(rc_interest_rate));
		var rc_current_term = removeco($("#rc_current_term").val());
		$("#rc_current_term").val(addCommas(rc_current_term));

		var rc_new_loan_amount = removeco($("#rc_new_loan_amount").val());
		$("#rc_new_loan_amount").val(addCommas(rc_new_loan_amount));
		var rc_new_interest_rate = removeco($("#rc_new_interest_rate").val());
		$("#rc_new_interest_rate").val(addCommas(rc_new_interest_rate));
		var rc_new_loan_term = removeco($("#rc_new_loan_term").val());
		$("#rc_new_loan_term").val(addCommas(rc_new_loan_term));
		var rc_new_refinance_fees = removeco($("#rc_new_refinance_fees").val());
		$("#rc_new_refinance_fees").val(addCommas(rc_new_refinance_fees));
		var rc_origination_year = $("#rc_origination_year").val();
		if (rc_new_loan_amount == '') {
			rc_new_loan_amount = 0;
		}

		if (rc_original_loan_amount == '') {
			rc_original_loan_amount = 0;
		}

		if (rc_new_loan_amount == 0 && rc_original_loan_amount == 0) {
			$("#rc_pi_div_span").html(rc_new_refinance_fees);
			$("#rc_afford_house_div_span").html('0');
			$("#rc_emmp_div_span").html('0');
			$("#rc_lifetime_div_span").html('0');
		} else if (parseInt(rc_new_loan_amount) > parseInt(rc_original_loan_amount)) {
			var nleft_data = (rc_new_interest_rate / 100 / 12) * rc_new_loan_amount;
			var nmpowerdata = Math.pow((1 + (rc_new_interest_rate / 100 / 12)), (-rc_new_loan_term));
			var nright_data = (1 - (nmpowerdata));
			newloanpayment = nleft_data / nright_data;
			$("#rc_afford_house_div_span").html('0');
			$("#rc_emmp_div_span").html(Number(Math.round(newloanpayment)).toLocaleString('en'));
			$("#rc_pi_div_span").html('0');
			$("#rc_lifetime_div_span").html('0');
		} else {


			var cleft_data = (rc_interest_rate / 100 / 12) * rc_original_loan_amount;
			var mpowerdata = Math.pow((1 + (rc_interest_rate / 100 / 12)), (-rc_current_term));

			var cright_data = (1 - (mpowerdata));
			currentloanpayment = cleft_data / cright_data;

			var nleft_data = (rc_new_interest_rate / 100 / 12) * rc_new_loan_amount;
			var nmpowerdata = Math.pow((1 + (rc_new_interest_rate / 100 / 12)), (-rc_new_loan_term));
			var nright_data = (1 - (nmpowerdata));
			newloanpayment = nleft_data / nright_data;
			monthlysavings = currentloanpayment - newloanpayment;
			$("#rc_afford_house_div_span").html(Number(Math.round(monthlysavings)).toLocaleString('en'));

			if (Number(Math.round(monthlysavings)).toLocaleString('en') == 0) {
				$("#rc_emmp_div_span").html('0');
				$("#rc_pi_div_span").html('0');
				$("#rc_lifetime_div_span").html('0');
			} else {

				$("#rc_emmp_div_span").html(Number(Math.round(newloanpayment)).toLocaleString('en'));
				$("#rc_pi_div_span").html(Number(Math.round(rc_new_refinance_fees)).toLocaleString('en'));

				var current_year = new Date().getFullYear();
				if (rc_origination_year <= current_year) {
					rc_origination_year = parseInt(rc_origination_year);
					monthlysavings = Number(Math.round(monthlysavings)).toLocaleString('en');
					monthlysavings = parseInt(monthlysavings);
					newloanpayment = parseInt(newloanpayment);
					rc_new_loan_term = parseInt(rc_new_loan_term);
					rc_new_refinance_fees = parseInt(rc_new_refinance_fees);
					rc_current_term = parseInt(rc_current_term);

					var newcurrentloanpayment = removeco($("#rc_original_loan_amount").val());
					newcurrentloanpayment = parseInt(newcurrentloanpayment);

					lifetimesavings = currentloanpayment * (rc_current_term - (((current_year - rc_origination_year) * 12)) - 6) - ((newloanpayment * rc_new_loan_term) + rc_new_refinance_fees);

					$("#rc_lifetime_div_span").html(Number(Math.round(lifetimesavings)).toLocaleString('en'));
				}
			}
		}
	} // end function

	$('form.mcalc-refi').each(function () {
		var este = $(this);
		rc_mortgage_calc();
		rc_input_vals(este);

		$('input', este).each(function () {
			$(this).on('keyup', function () {
				rc_mortgage_calc();
				rc_input_vals(este);
			});
		});
	});

	function rc_input_vals (este) {
		$(".rc_afford_house_div_span", este).val($("#rc_afford_house_div_span", este).text());
		$(".rc_emmp_div_span", este).val($("#rc_emmp_div_span", este).text());
		$(".rc_pi_div_span", este).val($("#rc_pi_div_span", este).text());
		$(".rc_lifetime_div_span", este).val($("#rc_lifetime_div_span", este).text());
	}
	/* Refinance Calculator Short Code Ended Here*/
});
