/*
 * Mortgage Calculators WP front end. This file is the source of truth and is edited directly.
 *
 * Sliders are native <input type="range"> elements behind the small part of the
 * bootstrap-slider API the calculators use, so the calculation code below is unchanged:
 *   $(el).bootstrapSlider()             set up from the data-slider-min/max/step/value attributes
 *   $(el).bootstrapSlider("setValue", v) round to the step's decimals, clamp, no event
 *   jQuery "change" events carrying { value: { oldValue, newValue } } while dragging
 * The original input stays in the form (hidden) so its name/value still submits, and the
 * range is inserted before it so $(el).next("p") still finds the percentage readout.
 */
(function ($) {
	function decimalsOf(step) {
		var match = String(step).match(/(?:\.(\d+))?(?:[eE]([+-]?\d+))?$/);
		if (!match) {
			return 0;
		}
		return Math.max(0, (match[1] ? match[1].length : 0) - (match[2] ? +match[2] : 0));
	}
	function normalize(state, val) {
		if (!val) {
			val = 0;
		}
		var n = +val;
		if (isNaN(n)) {
			throw new Error("Invalid input value '" + val + "' passed in");
		}
		n = parseFloat(n.toFixed(state.decimals));
		return Math.max(state.min, Math.min(state.max, n));
	}
	function setup(input) {
		var $input = $(input);
		var attr = function (name, fallback) {
			var value = parseFloat($input.attr("data-slider-" + name));
			return isNaN(value) ? fallback : value;
		};
		var state = { min: attr("min", 0), max: attr("max", 10), step: attr("step", 1) };
		state.decimals = decimalsOf(state.step);
		state.range = $('<input type="range" class="mcalc-range">').attr({ min: state.min, max: state.max, step: state.step });
		if (input.id) {
			// Move the id so the field's <label for> points at the control people actually use
			state.range.attr("id", input.id);
			input.removeAttribute("id");
		}
		if ($input.attr("aria-label")) {
			state.range.attr("aria-label", $input.attr("aria-label"));
		}
		state.range.insertBefore(input);
		$input.css("display", "none").closest(".mcalc-half, .mcalc-full").addClass("mcalc-has-range");
		state.set = function (value) {
			state.value = value;
			input.setAttribute("data-value", value);
			input.setAttribute("value", value);
			input.value = value;
			state.range.val(value);
			// How far along the track the value sits, for the Modern slider's filled portion
			var span = state.max - state.min;
			state.range[0].style.setProperty("--mcalc-fill", (span > 0 ? ((value - state.min) / span) * 100 : 0) + "%");
		};
		state.set(normalize(state, attr("value", 5)));
		state.range.on("input", function () {
			var oldValue = state.value;
			var newValue = normalize(state, this.value);
			state.set(newValue);
			if (oldValue !== newValue) {
				$input.trigger({ type: "change", value: { oldValue: oldValue, newValue: newValue } });
			}
		});
		$input.data("mcwpSlider", state);
		return state;
	}
	$.fn.bootstrapSlider = function (method, val) {
		return this.each(function () {
			var state = $(this).data("mcwpSlider") || setup(this);
			if (method === "setValue") {
				state.set(normalize(state, val));
			}
		});
	};
})(jQuery);
var $mcwp = jQuery.noConflict();
// Started from the browser's own DOM readiness (bottom of this file) rather than jQuery's ready():
// performance plugins such as Perfmatters can hold jQuery's ready() until the visitor first
// clicks or scrolls, which left the calculators unstyled and unresponsive until then.
function mcwp_start($) {
	$(document).on("click", ".mcwp-submit", function (e) {
		e.preventDefault();
		var forma = $(this).closest("form");
		if (typeof navigator != "undefined" && navigator.webdriver) {
			showMessage(forma, mcwp_ajax.calc_res, false);
			return false;
		}
		if (!validateEmail(forma.find('input[type="email"]').val())) {
			showMessage(forma, mcwp_ajax.invalid_email, true);
			return false;
		}
		var button = $(this).prop("disabled", true);
		getRecaptchaToken(forma).then(function (token) {
			forma.find(".mcwp-recaptcha-token").val(token || "");
			sendForm(forma, button);
		});
	});
	// Resolves with a reCAPTCHA token, or "" when reCAPTCHA isn't configured, fails, or the
	// visitor is answering the fallback question (the server then decides what to do).
	function getRecaptchaToken(forma) {
		var key = mcwp_ajax.recaptcha_site_key;
		if (!key || typeof grecaptcha === "undefined" || forma.find('input[name="mcwp_answer"]').length) {
			return Promise.resolve("");
		}
		var isV3 = mcwp_ajax.recaptcha_version === "v3";
		return new Promise(function (resolve) {
			grecaptcha.ready(function () {
				try {
					var pending = isV3 ? grecaptcha.execute(key, { action: "mcwp_submit" }) : grecaptcha.execute();
					Promise.resolve(pending).then(
						function (token) {
							if (!isV3) {
								grecaptcha.reset(); // v2 tokens are single use
							}
							resolve(token);
						},
						function () {
							resolve("");
						}
					);
				} catch (err) {
					resolve("");
				}
			});
		});
	}
	function sendForm(forma, button) {
		var post_data = {};
		$.each(forma.serializeArray(), function () {
			post_data[this.name] = this.value;
		});
		$.post(mcwp_ajax.ajaxurl, post_data)
			.done(function (response) {
				if (response && response.status === "captcha_required") {
					showChallenge(forma, response);
					showMessage(forma, response.message, true);
				} else if (response && response.status === "emails_sent") {
					clearChallenge(forma);
					showMessage(forma, response.message, false);
				} else {
					showMessage(forma, (response && response.message) || mcwp_ajax.request_failed, true);
				}
			})
			.fail(function () {
				showMessage(forma, mcwp_ajax.request_failed, true);
			})
			.always(function () {
				button.prop("disabled", false);
			});
	}
	// Swap the email box for the fallback question's answer box, reusing the email label
	function showChallenge(forma, response) {
		var email = forma.find('input[type="email"]');
		var answer = forma.find('input[name="mcwp_answer"]');
		if (!answer.length) {
			answer = $('<input type="number" name="mcwp_answer" inputmode="numeric">').attr("id", email.attr("id") + "_answer");
			var label = forma.find('label[for="' + email.attr("id") + '"]');
			if (!label.length) {
				label = $("<label>").insertBefore(email);
			}
			label.data("mcwp-text", label.text()).attr("for", answer.attr("id")).addClass("mcwp-challenge-label");
			email.before(answer).hide();
			forma.append('<input type="hidden" name="mcwp_challenge">');
		}
		forma.find(".mcwp-challenge-label").text(response.question);
		forma.find('input[name="mcwp_challenge"]').val(response.challenge);
		answer.val("").trigger("focus");
	}
	function clearChallenge(forma) {
		var answer = forma.find('input[name="mcwp_answer"]');
		if (!answer.length) {
			return;
		}
		var email = forma.find('input[type="email"]');
		forma.find(".mcwp-challenge-label").each(function () {
			$(this).text($(this).data("mcwp-text")).attr("for", email.attr("id")).removeClass("mcwp-challenge-label");
		});
		answer.remove();
		forma.find('input[name="mcwp_challenge"]').remove();
		email.show();
	}
	function showMessage(forma, text, isError) {
		var box = forma.find(".mcwp-message");
		if (!box.length) {
			alert(text);
			return;
		}
		// Keep the message next to the Send button (above the reCAPTCHA notice) rather than at the bottom of the form
		var parent = forma.find(".mcwp-submit").parent();
		var note = parent.children(".mcwp-recaptcha-note");
		if (note.length) {
			box.insertBefore(note);
		} else {
			box.appendTo(parent);
		}
		box.empty().append($("<p>").toggleClass("mcwp-error", !!isError).text(text));
	}
	function validateEmail(email) {
		var re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
		return re.test(String(email).toLowerCase());
	}
	$(".ex1").bootstrapSlider();
	function addCommas(intNum) {
		val = intNum;
		var parts = val.toString().split(".");
		parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ",");
		return parts.join(".");
	}
	function removeco(val) {
		return val.replace(/,/g, "");
	}
	$("form.mcalc-conventional").each(function () {
		var $parent = $(this);
		var purchase_price = removeco($("#inp_purchase_price", $parent).val());
		var down_payment_percent = $(".down_payment_scrl", $parent).val();
		var down_payment = (purchase_price * down_payment_percent) / 100;
		var interest_rate = $(".interest_rate_scrl", $parent).val();
		var insurance_rate = $(".insurance_rate_scrl", $parent).val();
		var mortgage_term = $("#mortgage_term_yr", $parent).val();
		var annual_tax_percent = $(".annual_tax_scrl", $parent).val();
		var annual_tax = (purchase_price * annual_tax_percent) / 100;
		var monthly_tax = annual_tax / 12;
		var monthly_insurance = $("#annual_insurance_inp", $parent).val() / 12;
		var mountly_pmi = (purchase_price * insurance_rate) / 12;
		var monthly_hoa = $("#monthly_hoa_inp", $parent).val();
		$("#down_payment_inp", $parent).val(addCommas(down_payment));
		$("#annual_tax_inp", $parent).val(addCommas(annual_tax));
		mortgage_calc($parent);
		$parent.find("#mortgage_term_yr").on("change", function (event) {
			if (this.value == 15) {
				mortgage_term_yr = 0.0045;
			} else {
				mortgage_term_yr = 0.0085;
			}
			mortgage_calc($parent);
		});
		$parent.find("#monthly_hoa_inp").on("keyup", function (event) {
			monthly_hoa = $(this).val() == "" ? 0 : $(this).val();
			$(this).val(function (index, value) {
				return value.replace(/\D/g, "").replace(/\B(?=(\d{3})+(?!\d))/g, ",");
			});
			mortgage_calc($parent);
		});
		$parent.find(".ex1").on("change", function (slideEvt) {
			if ($(this).hasClass("down_payment_scrl")) {
				down_payment_percent = slideEvt.value.newValue;
				$(this)
					.next("p")
					.text(roundOff(down_payment_percent, 1) + "%");
				down_payment = (purchase_price * down_payment_percent) / 100;
				$("#down_payment_inp", $parent).val(addCommas(down_payment));
			}
			if ($(this).hasClass("annual_tax_scrl")) {
				annual_tax_percent = slideEvt.value.newValue;
				$(this)
					.next("p")
					.text(annual_tax_percent + "%");
				annual_tax = (purchase_price * annual_tax_percent) / 100;
				monthly_tax = annual_tax / 12;
				$("#annual_tax_inp", $parent).val(addCommas(roundOff(annual_tax)));
			}
			if ($(this).hasClass("interest_rate_scrl")) {
				interest_rate = slideEvt.value.newValue;
				$(this)
					.next("p")
					.text(interest_rate + "%");
			}
			if ($(this).hasClass("insurance_rate_scrl")) {
				insurance_rate = slideEvt.value.newValue;
				$(this)
					.next("p")
					.text(insurance_rate + "%");
			}
			mortgage_calc($parent);
		});
		$parent.find("#inp_purchase_price").keyup(function () {
			purchase_price = $(this).val() == "" ? 0 : $(this).val();
			purchase_price = removeco(purchase_price);
			down_payment = (purchase_price * down_payment_percent) / 100;
			$("#down_payment_inp", $parent).val(addCommas(down_payment));

			// added by bill bailey 1/30/2023
			annual_tax = (purchase_price * annual_tax_percent) / 100;
			$("#annual_tax_inp", $parent).val(addCommas(roundOff(annual_tax)));
			// end added by bill bailey 1/30/2023

			$(this).val(function (index, value) {
				return value.replace(/\D/g, "").replace(/\B(?=(\d{3})+(?!\d))/g, ",");
			});
			mortgage_calc($parent);
		});
		$parent.find("#down_payment_inp").keyup(function () {
			down_payment = removeco($(this).val()) == "" ? 0 : removeco($(this).val());
			down_payment_percent = (down_payment / purchase_price) * 100;
			$(".down_payment_scrl", $parent)
				.bootstrapSlider("setValue", down_payment_percent)
				.next("p")
				.text(roundOff(down_payment_percent, 1) + "%");
			$(this).val(function (index, value) {
				return value.replace(/\D/g, "").replace(/\B(?=(\d{3})+(?!\d))/g, ",");
			});
			mortgage_calc($parent);
		});
		$parent.find("#annual_tax_inp").keyup(function () {
			annual_tax = $(this).val() == "" ? 0 : removeco($(this).val());
			annual_tax_percent = (annual_tax / purchase_price) * 100;
			$(".annual_tax_scrl", $parent)
				.bootstrapSlider("setValue", annual_tax_percent)
				.next("p")
				.text(roundOff(annual_tax_percent) + "%");
			$(this).val(function (index, value) {
				return value.replace(/\D/g, "").replace(/\B(?=(\d{3})+(?!\d))/g, ",");
			});
			$("#annual_tax_inp", $parent).val(addCommas(annual_tax));
			monthly_tax = annual_tax / 12;
			mortgage_calc($parent);
		});
		$parent.find("#annual_insurance_inp").keyup(function () {
			monthly_insurance = $(this).val() == "" ? 0 : removeco($(this).val()) / 12;
			$(this).val(function (index, value) {
				return value.replace(/\D/g, "").replace(/\B(?=(\d{3})+(?!\d))/g, ",");
			});
			mortgage_calc($parent);
		});
	});
	function mortgage_calc($parent) {
		price = removeco($("#inp_purchase_price", $parent).val());
		down_payment = removeco($("#down_payment_inp", $parent).val()) == "" ? 0 : removeco($("#down_payment_inp", $parent).val());
		down_payment_percent = (down_payment / price) * 100;
		down = (price * down_payment_percent) / 100;
		rate = $(".interest_rate_scrl", $parent).val();
		inrate = $(".insurance_rate_scrl", $parent).val();
		term = $("#mortgage_term_yr", $parent).val();
		annual_tax_percentNew = $(".annual_tax_scrl", $parent).next("p").text();
		annual_tax_percentNew = annual_tax_percentNew.replace("%", "");
		annual_tax = (price * annual_tax_percentNew) / 100;
		tax = annual_tax / 12;
		mpmi = ((inrate / 100) * (price - down)) / 12;
		insurance = removeco($("#annual_insurance_inp", $parent).val()) / 12;
		hoa = removeco($("#monthly_hoa_inp").val()) == "" ? 0 : removeco($("#monthly_hoa_inp", $parent).val());
		var n = parseInt(term) * 12;
		var c = parseFloat(rate) / 1200;
		var L = parseInt(price) - parseFloat(down);
		var p = Math.round((L * (c * Math.pow(1 + c, n))) / (Math.pow(1 + c, n) - 1));
		var emmp = parseFloat(p) + parseFloat(tax) + parseFloat(insurance) + parseFloat(hoa);
		changethis = roundOff(emmp, 2);

		// edited by bill bailey 2/6/2023
		// moved the check for down payment percent above where value are output to html and add the pmi
		if (down_payment_percent >= 20) {
			$(".mcwp-insurance-rate").hide();
			$(".mcwp-insurance-rate-disabled").show();
			mpmi = 0;
		} else {
			$(".mcwp-insurance-rate").show();
			$(".mcwp-insurance-rate-disabled").hide();
			emmp = emmp + parseFloat(mpmi);
		}
		// end edited by bill bailey 2/6/2023

		$("#emmp_div_span", $parent).text(addCommas(roundOff(emmp, 2)));
		$("#pi_div_span", $parent).text(addCommas(p));
		$("#mtax_div_span", $parent).text(addCommas(roundOff(tax, 2)));
		$("#minsure_div_span", $parent).text(addCommas(roundOff(insurance, 2)));
		$("#hoa_div_span", $parent).text(addCommas(hoa));
		$("#mpmi", $parent).text(addCommas(roundOff(mpmi, 2)));
		$(".emmp_div_span", $parent).val(addCommas(roundOff(emmp, 2)));
		$(".pi_div_span", $parent).val(addCommas(p));
		$(".mtax_div_span", $parent).val(addCommas(roundOff(tax, 2)));
		$(".minsure_div_span", $parent).val(addCommas(roundOff(insurance, 2)));
		$(".mpmi_div_span", $parent).val(addCommas(roundOff(mpmi, 2)));
		$(".hoa_div_span", $parent).val(addCommas(hoa));
	}
	if ($("#fha_inp_purchase_price").val()) {
		var fha_purchase_price = removeco($("#fha_inp_purchase_price").val());
		var fha_down_payment_percent = $(".fha_down_payment_scrl").val();
		var fha_down_payment = (fha_purchase_price * fha_down_payment_percent) / 100;
		var fha_interest_rate = $(".fha_interest_rate_scrl").val();
		var fha_mortgage_term = $("#fha_mortgage_term_yr").val();
		var fha_annual_tax_percent = $(".fha_annual_tax_scrl").val();
		var fha_annual_tax = (fha_purchase_price * fha_annual_tax_percent) / 100;
		var fha_monthly_tax = fha_annual_tax / 12;
		var fha_monthly_insurance = $("#fha_annual_insurance_inp").val() / 12;
		var monthly_mortgage_insurance = $("#fha_mmi_div_span").val() / 200;
		var fha_monthly_hoa = $("#fha_monthly_hoa_inp").val();
		$("#fha_down_payment_inp").val(addCommas(fha_down_payment));
		$("#fha_annual_tax_inp").val(addCommas(fha_annual_tax));
		fha_mortgage_calc(fha_purchase_price, fha_down_payment, fha_interest_rate, fha_mortgage_term, fha_monthly_tax, fha_annual_tax_percent, fha_monthly_insurance, fha_monthly_hoa);
	}
	$(document).on("change", "#fha_mortgage_term_yr", function () {
		fha_mortgage_calc(fha_purchase_price, fha_down_payment, fha_interest_rate, this.value, fha_monthly_tax, fha_annual_tax_percent, fha_monthly_insurance, fha_monthly_hoa);
	});
	$(".ex1").on("change", function (slideEvt) {
		if ($(this).hasClass("fha_down_payment_scrl")) {
			fha_down_payment_percent = slideEvt.value.newValue;
			$(this)
				.next("p")
				.text(roundOff(fha_down_payment_percent, 1) + "%");
			fha_down_payment = (fha_purchase_price * fha_down_payment_percent) / 100;
			$("#fha_down_payment_inp").val(addCommas(fha_down_payment));
			fha_mortgage_calc(fha_purchase_price, fha_down_payment, fha_interest_rate, fha_mortgage_term, fha_monthly_tax, fha_annual_tax_percent, fha_monthly_insurance, fha_monthly_hoa);
		}
		if ($(this).hasClass("fha_annual_tax_scrl")) {
			fha_annual_tax_percent = slideEvt.value.newValue;
			$(this)
				.next("p")
				.text(fha_annual_tax_percent + "%");
			fha_annual_tax = (fha_purchase_price * fha_annual_tax_percent) / 100;
			fha_monthly_tax = fha_annual_tax / 12;
			$("#fha_annual_tax_inp").val(addCommas(roundOff(fha_annual_tax)));
			fha_mortgage_calc(fha_purchase_price, fha_down_payment, fha_interest_rate, fha_mortgage_term, fha_monthly_tax, fha_annual_tax_percent, fha_monthly_insurance, fha_monthly_hoa, "no");
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

		// added by bill bailey 1/30/2023
		fha_annual_tax = (fha_purchase_price * fha_annual_tax_percent) / 100;
		fha_monthly_tax = fha_annual_tax / 12;
		$("#fha_annual_tax_inp").val(addCommas(roundOff(fha_annual_tax)));
		// end added by bill bailey 1/30/2023

		$("#fha_down_payment_inp").val(addCommas(fha_down_payment));
		$(this).val(function (index, value) {
			return value.replace(/\D/g, "").replace(/\B(?=(\d{3})+(?!\d))/g, ",");
		});
		fha_mortgage_calc(fha_purchase_price, fha_down_payment, fha_interest_rate, fha_mortgage_term, fha_monthly_tax, fha_annual_tax_percent, fha_monthly_insurance, fha_monthly_hoa);
	});
	$("#fha_down_payment_inp").keyup(function () {
		fha_down_payment = $(this).val() == "" ? 0 : removeco($(this).val());
		fha_down_payment = parseInt(fha_down_payment);
		fha_down_payment_percent = (fha_down_payment / fha_purchase_price) * 100;
		$(".fha_down_payment_scrl")
			.bootstrapSlider("setValue", fha_down_payment_percent)
			.next("p")
			.text(roundOff(fha_down_payment_percent, 1) + "%");
		$(this).val(function (index, value) {
			return value.replace(/\D/g, "").replace(/\B(?=(\d{3})+(?!\d))/g, ",");
		});
		fha_mortgage_calc(fha_purchase_price, fha_down_payment, fha_interest_rate, fha_mortgage_term, fha_monthly_tax, fha_annual_tax_percent, fha_monthly_insurance, fha_monthly_hoa);
	});
	$("#fha_annual_tax_inp").keyup(function () {
		fha_annual_tax = $(this).val() == "" ? 0 : removeco($(this).val());
		fha_annual_tax = Number(fha_annual_tax);
		fha_annual_tax = roundOff(fha_annual_tax);
		fha_annual_tax_percent = (fha_annual_tax / fha_purchase_price) * 100;
		$(".fha_annual_tax_scrl")
			.bootstrapSlider("setValue", fha_annual_tax_percent)
			.next("p")
			.text(roundOff(fha_annual_tax_percent) + "%");
		$(this).val(function (index, value) {
			newval = value.replace(/\D/g, "").replace(/\B(?=(\d{3})+(?!\d))/g, ",");
			return newval;
		});
		fha_mortgage_calc(fha_purchase_price, fha_down_payment, fha_interest_rate, fha_mortgage_term, fha_monthly_tax, fha_annual_tax_percent, fha_monthly_insurance, fha_monthly_hoa);
		fha_monthly_tax = fha_annual_tax / 12;
		$(".mtchange").empty();
		$(".mtchange")
			.empty()
			.text(addCommas(roundOff(fha_monthly_tax, 2)));
	});
	$("#fha_annual_insurance_inp").keyup(function () {
		fha_monthly_insurance = $(this).val() == "" ? 0 : $(this).val() / 12;
		$(this).val(function (index, value) {
			return value.replace(/\D/g, "").replace(/\B(?=(\d{3})+(?!\d))/g, ",");
		});
		fha_mortgage_calc(fha_purchase_price, fha_down_payment, fha_interest_rate, fha_mortgage_term, fha_monthly_tax, fha_annual_tax_percent, fha_monthly_insurance, fha_monthly_hoa);
	});
	$("#fha_monthly_hoa_inp").keyup(function () {
		fha_monthly_hoa = $(this).val() == "" ? 0 : $(this).val();
		$(this).val(function (index, value) {
			return value.replace(/\D/g, "").replace(/\B(?=(\d{3})+(?!\d))/g, ",");
		});
		fha_mortgage_calc(fha_purchase_price, fha_down_payment, fha_interest_rate, fha_mortgage_term, fha_monthly_tax, fha_annual_tax_percent, fha_monthly_insurance, fha_monthly_hoa);
	});
	function calculate_mip(loan_amount, loan_to_value, term) {
		let mip_rate;

		if (term > 15) {
			if (loan_amount <= 726200) {
				if (loan_to_value <= 90) {
					mip_rate = 0.005;
				} else if (loan_to_value > 90 && loan_to_value <= 95) {
					mip_rate = 0.005;
				} else {
					mip_rate = 0.0055;
				}
			} else {
				if (loan_to_value <= 90) {
					mip_rate = 0.007;
				} else if (loan_to_value > 90 && loan_to_value <= 95) {
					mip_rate = 0.007;
				} else {
					mip_rate = 0.0075;
				}
			}
		} else {
			if (loan_amount <= 726200) {
				if (loan_to_value <= 90) {
					mip_rate = 0.0015;
				} else {
					mip_rate = 0.004;
				}
			} else {
				if (loan_to_value <= 78) {
					mip_rate = 0.0015;
				} else if (loan_to_value > 78 && loan_to_value <= 90) {
					mip_rate = 0.004;
				} else {
					mip_rate = 0.0065;
				}
			}
		}

		return mip_rate * loan_amount;
	}
	function fha_mortgage_calc(price, down, rate, term, tax, tax_prcnt, insurance, hoa, show) {
		price = removeco($("#fha_inp_purchase_price").val());
		fha_down_payment_percent = $(".fha_down_payment_scrl").val();
		down = (price * fha_down_payment_percent) / 100;
		rate = $(".fha_interest_rate_scrl").val();
		term = $("#fha_mortgage_term_yr").val();
		insurance = removeco($("#fha_annual_insurance_inp").val()) / 12;
		hoa = removeco($("#fha_monthly_hoa_inp").val()) == "" ? 0 : removeco($("#fha_monthly_hoa_inp").val());
		var n = parseInt(term) * 12;
		var c = parseFloat(rate) / 1200;
		var L = parseInt(price) - parseFloat(down);
		// Calculate UPMIP
		var upmip_rate = 0.0175;
		var upmip = L * upmip_rate;

		// Add UPMIP to the loan amount
		L = L + upmip;

		var p = Math.round((L * (c * Math.pow(1 + c, n))) / (Math.pow(1 + c, n) - 1));
		if (term == 15) {
			tax_prcnt = 0.45;
		} else if (term == 30) {
			tax_prcnt = 0.85;
		}
		var loan_to_value = ((price - down) / price) * 100;
		var mip_annual = calculate_mip(L, loan_to_value, parseInt(term));
		var mmi = Math.round(mip_annual / 12);

		var emmp = parseFloat(p) + parseFloat(tax) + parseFloat(insurance) + parseFloat(hoa) + parseFloat(mmi);
		$("#fha_emmp_div_span").text(addCommas(roundOff(emmp, 2)));
		$("#fha_pi_div_span").text(addCommas(p));
		$("#fha_mtax_div_span").text(addCommas(roundOff(tax, 2)));
		$("#fha_minsure_div_span").text(addCommas(roundOff(insurance, 2)));
		if (show != "no") {
			$("#fha_mmi_div_span").text(addCommas(roundOff(mmi)));
		}
		$("#fha_upmip_div_span").text(addCommas(roundOff(upmip)));
		$("#fha_hoa_div_span").text(addCommas(hoa));
		$(".fha_emmp_div_span").val(addCommas(roundOff(emmp, 2)));
		$(".fha_pi_div_span").val(addCommas(p));
		$(".fha_mtax_div_span").val(addCommas(roundOff(tax, 2)));
		$(".fha_minsure_div_span").val(addCommas(roundOff(insurance, 2)));
		if (show != "no") {
			$(".fha_mmi_div_span").val(addCommas(mmi));
		}
		$(".fha_hoa_div_span").val(addCommas(hoa));
	}
	if ($("#va_inp_purchase_price").val()) {
		var va_purchase_price = $("#va_inp_purchase_price").val();
		va_purchase_price = removeco(va_purchase_price);
		var va_down_payment_percent = $(".va_down_payment_scrl").val();
		var va_down_payment = (va_purchase_price * va_down_payment_percent) / 100;
		var va_interest_rate = $(".va_interest_rate_scrl").val();
		var va_mortgage_term = $("#va_mortgage_term_yr").val();
		var va_annual_tax_percent = $(".va_annual_tax_scrl").val();
		var va_annual_tax = (va_purchase_price * va_annual_tax_percent) / 100;
		var va_monthly_tax = va_annual_tax / 12;
		var va_monthly_insurance = removeco($("#va_annual_insurance_inp").val()) / 12;
		var va_monthly_hoa = $("#va_monthly_hoa_inp").val();
		$("#va_down_payment_inp").val(addCommas(va_down_payment));
		$("#va_annual_tax_inp").val(addCommas(va_annual_tax));
		va_mortgage_calc(va_purchase_price, va_down_payment, va_interest_rate, va_mortgage_term, va_monthly_tax, va_down_payment_percent, va_monthly_insurance, va_monthly_hoa);
	}
	$(document).on("change", "#va_mortgage_term_yr", function () {
		va_mortgage_calc(va_purchase_price, va_down_payment, va_interest_rate, this.value, va_monthly_tax, va_down_payment_percent, va_monthly_insurance, va_monthly_hoa);
	});
	$(".ex1").on("change", function (slideEvt) {
		if ($(this).hasClass("va_down_payment_scrl")) {
			va_down_payment_percent = slideEvt.value.newValue;
			$(this)
				.next("p")
				.text(va_down_payment_percent + "%");
			va_down_payment = (va_purchase_price * va_down_payment_percent) / 100;
			$("#va_down_payment_inp").val(addCommas(va_down_payment));
			va_mortgage_calc(va_purchase_price, va_down_payment, va_interest_rate, va_mortgage_term, va_monthly_tax, va_down_payment_percent, va_monthly_insurance, va_monthly_hoa);
		}
		if ($(this).hasClass("va_annual_tax_scrl")) {
			va_annual_tax_percent = slideEvt.value.newValue;
			$(this)
				.next("p")
				.text(roundOff(va_annual_tax_percent, 1) + "%");
			va_annual_tax = (va_purchase_price * va_annual_tax_percent) / 100;
			va_monthly_tax = va_annual_tax / 12;
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

		// added by bill bailey 1/30/2023
		va_annual_tax = (va_purchase_price * va_annual_tax_percent) / 100;
		va_monthly_tax = va_annual_tax / 12;
		$("#va_annual_tax_inp").val(addCommas(roundOff(va_annual_tax)));
		// end added by bill bailey 1/30/2023

		$(this).val(function (index, value) {
			return value.replace(/\D/g, "").replace(/\B(?=(\d{3})+(?!\d))/g, ",");
		});
		va_mortgage_calc(va_purchase_price, va_down_payment, va_interest_rate, va_mortgage_term, va_monthly_tax, va_down_payment_percent, va_monthly_insurance, va_monthly_hoa);
	});
	$("#va_down_payment_inp").keyup(function () {
		va_down_payment = $(this).val() == "" ? 0 : $(this).val();
		va_down_payment = removeco(va_down_payment);
		va_down_payment_percent = (va_down_payment / va_purchase_price) * 100;
		$(".va_down_payment_scrl")
			.bootstrapSlider("setValue", va_down_payment_percent)
			.next("p")
			.text(roundOff(va_down_payment_percent, 1) + "%");
		$(this).val(function (index, value) {
			return value.replace(/\D/g, "").replace(/\B(?=(\d{3})+(?!\d))/g, ",");
		});
		va_mortgage_calc(va_purchase_price, va_down_payment, va_interest_rate, va_mortgage_term, va_monthly_tax, va_down_payment_percent, va_monthly_insurance, va_monthly_hoa);
	});
	$("#va_annual_tax_inp").keyup(function () {
		va_annual_tax = $(this).val() == "" ? 0 : $(this).val();
		va_annual_tax = removeco(va_annual_tax);
		va_annual_tax_percent = (va_annual_tax / va_purchase_price) * 100;
		$(this).val(function (index, value) {
			return value.replace(/\D/g, "").replace(/\B(?=(\d{3})+(?!\d))/g, ",");
		});
		va_monthly_tax = va_annual_tax / 12;
		$(".va_annual_tax_scrl")
			.bootstrapSlider("setValue", va_annual_tax_percent)
			.next("p")
			.text(va_annual_tax_percent + "%");
		va_mortgage_calc(va_purchase_price, va_down_payment, va_interest_rate, va_mortgage_term, va_monthly_tax, va_down_payment_percent, va_monthly_insurance, va_monthly_hoa);
	});
	$("#va_annual_insurance_inp").keyup(function () {
		va_monthly_insurance = $(this).val() == "" ? 0 : removeco($(this).val()) / 12;
		$(this).val(function (index, value) {
			return value.replace(/\D/g, "").replace(/\B(?=(\d{3})+(?!\d))/g, ",");
		});
		va_mortgage_calc(va_purchase_price, va_down_payment, va_interest_rate, va_mortgage_term, va_monthly_tax, va_down_payment_percent, va_monthly_insurance, va_monthly_hoa);
	});
	$("#va_monthly_hoa_inp").keyup(function () {
		va_monthly_hoa = $(this).val() == "" ? 0 : $(this).val();
		$(this).val(function (index, value) {
			return value.replace(/\D/g, "").replace(/\B(?=(\d{3})+(?!\d))/g, ",");
		});
		va_mortgage_calc(va_purchase_price, va_down_payment, va_interest_rate, va_mortgage_term, va_monthly_tax, va_down_payment_percent, va_monthly_insurance, va_monthly_hoa);
	});
	$(document).on("change", "#va_service_type", function () {
		var va_service = $(this).val();
		if (va_service === "surviving_spouse" || va_service === "disabled_veteran") {
			$(".mcwp-first-time").hide();
		} else {
			$(".mcwp-first-time").show();
		}
		va_monthly_insurance = removeco($("#va_annual_insurance_inp").val()) / 12;
		va_mortgage_calc(va_purchase_price, va_down_payment, va_interest_rate, va_mortgage_term, va_monthly_tax, va_down_payment_percent, va_monthly_insurance, va_monthly_hoa);
	});
	$(document).on("change", "#va_frist_time", function () {
		va_monthly_insurance = removeco($("#va_annual_insurance_inp").val()) / 12;
		va_mortgage_calc(va_purchase_price, va_down_payment, va_interest_rate, va_mortgage_term, va_monthly_tax, va_down_payment_percent, va_monthly_insurance, va_monthly_hoa);
	});
	function va_mortgage_calc(price, down, rate, term, tax, down_prcnt, insurance, hoa) {
		var va_purchase_price = $("#va_inp_purchase_price").val();
		price = removeco(va_purchase_price);
		va_down_payment_percent = $(".va_down_payment_scrl").val();
		down = (price * va_down_payment_percent) / 100;
		rate = $(".va_interest_rate_scrl").val();
		term = $("#va_mortgage_term_yr").val();
		// The slider handlers keep va_annual_tax_percent current; read the slider if it was never set
		if (isNaN(parseFloat(va_annual_tax_percent))) {
			va_annual_tax_percent = $(".va_annual_tax_scrl").val();
		}
		va_annual_tax = (price * va_annual_tax_percent) / 100;
		tax = va_annual_tax / 12;
		insurance = removeco($("#va_annual_insurance_inp").val()) / 12;
		hoa = removeco($("#va_monthly_hoa_inp").val()) == "" ? 0 : removeco($("#va_monthly_hoa_inp").val());
		var va_service = $("#va_service_type").val();
		if (va_service === "surviving_spouse" || va_service === "disabled_veteran") {
			e_rate = 0;
		} else if ($("#va_frist_time").is(":checked")) {
			if (down_prcnt < 5) {
				e_rate = 2.15;
			}
			if (down_prcnt >= 5 && down_prcnt < 10) {
				e_rate = 1.5;
			}
			if (down_prcnt >= 10) {
				e_rate = 1.25;
			}
		} else {
			if (down_prcnt < 5) {
				e_rate = 3.3;
			}
			if (down_prcnt >= 5 && down_prcnt < 10) {
				e_rate = 1.5;
			}
			if (down_prcnt >= 10) {
				e_rate = 1.25;
			}
		}
		var n = parseInt(term) * 12;
		var c = parseFloat(rate) / 1200;
		var L = parseInt(price) - parseFloat(down);

		// var p = Math.round(
		//   (L * (c * Math.pow(1 + c, n))) / (Math.pow(1 + c, n) - 1)
		// );
		// var vaff = Math.round((L * e_rate) / 18e3);
		// updated by bill bailey 2/23/2023
		// vaff monthly calculations incorrect.

		var p = (L * (c * Math.pow(1 + c, n))) / (Math.pow(1 + c, n) - 1);
		var vaff = (((L * e_rate) / 100) * (c * Math.pow(1 + c, n))) / (Math.pow(1 + c, n) - 1);

		var emmp = parseFloat(p) + parseFloat(tax) + parseFloat(insurance) + parseFloat(hoa) + parseFloat(vaff);
		$("#va_emmp_div_span").text(addCommas(roundOff(emmp, 2)));
		$("#va_pi_div_span").text(addCommas(roundOff(p, 2)));
		$("#va_mtax_div_span").text(addCommas(roundOff(tax, 2)));
		$("#va_minsure_div_span").text(addCommas(roundOff(insurance, 2)));
		$("#va_hoa_div_span").text(addCommas(hoa));
		$("#va_purchase_p_span").text(addCommas(price));
		$("#va_funding_fee_p_span").html(addCommas(roundOff((L * e_rate) / 100, 2)));
		va_funding_fee_p_span = removeco($("#va_funding_fee_p_span").text());
		va_funding_fee_p_span = Number(va_funding_fee_p_span);
		va_purchase_price = removeco($("#va_inp_purchase_price").val());
		va_purchase_price = Number(va_purchase_price);
		va_down_payment_percent = $(".va_down_payment_scrl").val();
		va_down_payment_percent = Number(va_down_payment_percent);
		va_down_payment = (va_purchase_price * va_down_payment_percent) / 100;
		va_amount_finance_p_span = va_purchase_price - va_down_payment + va_funding_fee_p_span;
		$("#va_amount_finance_p_span").html(addCommas(roundOff(Number(va_amount_finance_p_span))));
		$(".va_emmp_div_span").val($("#va_emmp_div_span").text());
		$(".va_pi_div_span").val($("#va_pi_div_span").text());
		$(".va_mtax_div_span").val($("#va_mtax_div_span").text());
		$(".va_minsure_div_span").val($("#va_minsure_div_span").text());
		$(".va_hoa_div_span").val($("#va_hoa_div_span").text());
		$(".va_funding_fee_p_span").val($("#va_funding_fee_p_span").text());
		$(".va_amount_finance_p_span").val($("#va_amount_finance_p_span").text());
	}

	// updated by bill bailey to fix the issue with ".00" returned 1/31/2023
	function roundOff(number, precision) {
		num = number;
		return parseFloat(num.toFixed(precision));
	}
	// end update

	function mha_mortgage_calc() {
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
			var minimum_monthly_debts = (mha_annual_income * 8) / 100 / 12;
			if (mha_monthly_debts == "" || mha_monthly_debts < minimum_monthly_debts) {
				var mha_est_monthly_payment = (mha_annual_income / 12) * 0.4 - minimum_monthly_debts;
				if (mha_estimated_annual_property_taxes != "") {
					var mha_taxes = mha_estimated_annual_property_taxes / 12;
				} else {
					var mha_taxes = 0;
				}
				if (mha_estimated_annual_home_insurance != "") {
					var mha_insurance = mha_estimated_annual_home_insurance / 12;
				} else {
					var mha_insurance = 0;
				}
				var mha_P_I = mha_est_monthly_payment - (mha_taxes + mha_insurance);
				var aaaaa = mha_interest_rate / 100 / 12;
				var mpowerdata = Math.pow(1 + mha_interest_rate / 100 / 12, -mha_loan_term * 12);
				var bbbbb = 1 - mpowerdata;
				var ab = bbbbb / aaaaa;
				if (ab < 0) {
					var temp = ab * mha_P_I;
					var ab_total = temp - parseInt(mha_down_payment);
				} else {
					var temp = ab * mha_P_I;
					var ab_total = temp + parseInt(mha_down_payment);
				}
				if (ab_total <= 0) {
					$("#mha_afford_house_div_span").html("0");
				} else {
					$("#mha_afford_house_div_span").html(Number(Math.round(ab_total)).toLocaleString("en"));
				}
				if (mha_est_monthly_payment <= 0) {
					$("#mha_emmp_div_span").html("0");
				} else {
					$("#mha_emmp_div_span").html(Number(Math.round(mha_est_monthly_payment)).toLocaleString("en"));
				}
				if (mha_P_I <= 0) {
					$("#mha_pi_div_span").html("0");
				} else {
					$("#mha_pi_div_span").html(Number(Math.round(mha_P_I)).toLocaleString("en"));
				}
				if (mha_taxes <= 0) {
					$("#mha_taxes_div_span").html("0");
				} else {
					$("#mha_taxes_div_span").html(Number(Math.round(mha_taxes)).toLocaleString("en"));
				}
				if (mha_insurance <= 0) {
					$("#mha_insurance_div_span").html("0");
				} else {
					$("#mha_insurance_div_span").html(Number(Math.round(mha_insurance)).toLocaleString("en"));
				}
			} else {
				var mha_est_monthly_payment = (mha_annual_income / 12) * 0.4 - mha_monthly_debts;
				if (mha_estimated_annual_property_taxes != "") {
					var mha_taxes = mha_estimated_annual_property_taxes / 12;
				} else {
					var mha_taxes = 0;
				}
				if (mha_estimated_annual_home_insurance != "") {
					var mha_insurance = mha_estimated_annual_home_insurance / 12;
				} else {
					var mha_insurance = 0;
				}
				var mha_P_I = mha_est_monthly_payment - (mha_taxes + mha_insurance);
				var aaaaa = mha_interest_rate / 100 / 12;
				var mpowerdata = Math.pow(1 + mha_interest_rate / 100 / 12, -mha_loan_term * 12);
				var bbbbb = 1 - mpowerdata;
				var ab = bbbbb / aaaaa;
				if (ab < 0) {
					var temp = ab * mha_P_I;
					var ab_total = temp - parseInt(mha_down_payment);
				} else {
					var temp = ab * mha_P_I;
					var ab_total = temp + parseInt(mha_down_payment);
				}
				if (ab_total <= 0) {
					$("#mha_afford_house_div_span").html("0");
				} else {
					$("#mha_afford_house_div_span").html(Number(Math.round(ab_total)).toLocaleString("en"));
				}
				if (mha_est_monthly_payment <= 0) {
					$("#mha_emmp_div_span").html("0");
				} else {
					$("#mha_emmp_div_span").html(Number(Math.round(mha_est_monthly_payment)).toLocaleString("en"));
				}
				if (mha_P_I <= 0) {
					$("#mha_pi_div_span").html("0");
				} else {
					$("#mha_pi_div_span").html(Number(Math.round(mha_P_I)).toLocaleString("en"));
				}
				if (mha_taxes <= 0) {
					$("#mha_taxes_div_span").html("0");
				} else {
					$("#mha_taxes_div_span").html(Number(Math.round(mha_taxes)).toLocaleString("en"));
				}
				if (mha_insurance <= 0) {
					$("#mha_insurance_div_span").html("0");
				} else {
					$("#mha_insurance_div_span").html(Number(Math.round(mha_insurance)).toLocaleString("en"));
				}
			}
		}
	}
	$("form.mcalc-ha").each(function () {
		var este = $(this);
		mha_mortgage_calc();
		mha_input_vals(este);
		$("input", este).each(function () {
			$(this).on("keyup", function () {
				mha_mortgage_calc();
				mha_input_vals(este);
			});
		});
		$("select", este).each(function () {
			$(this).on("change", function () {
				mha_mortgage_calc();
				mha_input_vals(este);
			});
		});
	});
	function mha_input_vals(este) {
		$(".mha_insurance_div_span", este).val($("#mha_insurance_div_span", este).text());
		$(".mha_taxes_div_span", este).val($("#mha_taxes_div_span", este).text());
		$(".mha_pi_div_span", este).val($("#mha_pi_div_span", este).text());
		$(".mha_emmp_div_span", este).val($("#mha_emmp_div_span", este).text());
		$(".mha_afford_house_div_span", este).val($("#mha_afford_house_div_span", este).text());
	}
	function rc_mortgage_calc() {
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
		if (rc_new_loan_amount == "") {
			rc_new_loan_amount = 0;
		}
		if (rc_original_loan_amount == "") {
			rc_original_loan_amount = 0;
		}
		if (rc_new_loan_amount == 0 && rc_original_loan_amount == 0) {
			$("#rc_pi_div_span").html(rc_new_refinance_fees);
			$("#rc_afford_house_div_span").html("0");
			$("#rc_emmp_div_span").html("0");
			$("#rc_lifetime_div_span").html("0");
		} else if (parseInt(rc_new_loan_amount) > parseInt(rc_original_loan_amount)) {
			var nleft_data = (rc_new_interest_rate / 100 / 12) * rc_new_loan_amount;
			var nmpowerdata = Math.pow(1 + rc_new_interest_rate / 100 / 12, -rc_new_loan_term);
			var nright_data = 1 - nmpowerdata;
			newloanpayment = nleft_data / nright_data;
			$("#rc_afford_house_div_span").html("0");
			$("#rc_emmp_div_span").html(Number(Math.round(newloanpayment)).toLocaleString("en"));
			$("#rc_pi_div_span").html("0");
			$("#rc_lifetime_div_span").html("0");
		} else {
			var cleft_data = (rc_interest_rate / 100 / 12) * rc_original_loan_amount;
			var mpowerdata = Math.pow(1 + rc_interest_rate / 100 / 12, -rc_current_term);
			var cright_data = 1 - mpowerdata;
			currentloanpayment = cleft_data / cright_data;
			var nleft_data = (rc_new_interest_rate / 100 / 12) * rc_new_loan_amount;
			var nmpowerdata = Math.pow(1 + rc_new_interest_rate / 100 / 12, -rc_new_loan_term);
			var nright_data = 1 - nmpowerdata;
			newloanpayment = nleft_data / nright_data;
			monthlysavings = currentloanpayment - newloanpayment;
			$("#rc_afford_house_div_span").html(Number(Math.round(monthlysavings)).toLocaleString("en"));
			if (Number(Math.round(monthlysavings)).toLocaleString("en") == 0) {
				$("#rc_emmp_div_span").html("0");
				$("#rc_pi_div_span").html("0");
				$("#rc_lifetime_div_span").html("0");
			} else {
				$("#rc_emmp_div_span").html(Number(Math.round(newloanpayment)).toLocaleString("en"));
				$("#rc_pi_div_span").html(Number(Math.round(rc_new_refinance_fees)).toLocaleString("en"));
				var current_year = new Date().getFullYear();
				if (rc_origination_year <= current_year) {
					rc_origination_year = parseInt(rc_origination_year);
					monthlysavings = Number(Math.round(monthlysavings)).toLocaleString("en");
					monthlysavings = parseInt(monthlysavings);
					newloanpayment = parseInt(newloanpayment);
					rc_new_loan_term = parseInt(rc_new_loan_term);
					rc_new_refinance_fees = parseInt(rc_new_refinance_fees);
					rc_current_term = parseInt(rc_current_term);
					var newcurrentloanpayment = removeco($("#rc_original_loan_amount").val());
					newcurrentloanpayment = parseInt(newcurrentloanpayment);
					lifetimesavings = currentloanpayment * (rc_current_term - (current_year - rc_origination_year) * 12 - 6) - (newloanpayment * rc_new_loan_term + rc_new_refinance_fees);
					$("#rc_lifetime_div_span").html(Number(Math.round(lifetimesavings)).toLocaleString("en"));
				}
			}
		}
	}
	$("form.mcalc-refi").each(function () {
		var este = $(this);
		rc_mortgage_calc();
		rc_input_vals(este);
		$("input", este).each(function () {
			$(this).on("keyup", function () {
				rc_mortgage_calc();
				rc_input_vals(este);
			});
		});
	});
	function rc_input_vals(este) {
		$(".rc_afford_house_div_span", este).val($("#rc_afford_house_div_span", este).text());
		$(".rc_emmp_div_span", este).val($("#rc_emmp_div_span", este).text());
		$(".rc_pi_div_span", este).val($("#rc_pi_div_span", este).text());
		$(".rc_lifetime_div_span", este).val($("#rc_lifetime_div_span", este).text());
	}
	/* Buydown Calculator: math mirrors mcwp_buydown_calculate() in includes/functions/functions.php */
	var BD_TYPES = { "3-2-1": [3, 2, 1], "2-1": [2, 1], "1-0": [1], permanent: [] };
	// Round to cents the way PHP's round() does (it pre-rounds to 15 significant digits), so an
	// exact half cent like 5091.275 becomes 5091.28 in both the browser and the email
	function bd_round(n) {
		return Math.round(Number((n * 100).toPrecision(15))) / 100;
	}
	function bd_payment(principal, annualRate, months) {
		if (principal <= 0 || months <= 0) {
			return 0;
		}
		var rate = annualRate / 1200;
		if (rate <= 0) {
			return bd_round(principal / months);
		}
		return bd_round((principal * rate) / (1 - Math.pow(1 + rate, -months)));
	}
	function bd_calculate(loan, rate, years, type, points, perPoint) {
		loan = Math.max(0, loan);
		rate = Math.max(0, rate);
		var months = Math.max(0, parseInt(years, 10) || 0) * 12;
		var fullPayment = bd_payment(loan, rate, months);
		var steps = BD_TYPES.hasOwnProperty(type) ? BD_TYPES[type] : BD_TYPES["2-1"];
		if (type !== "permanent") {
			var schedule = [];
			var cost = 0;
			steps.forEach(function (reduction, index) {
				var yearRate = Math.max(0, rate - reduction);
				var payment = bd_payment(loan, yearRate, months);
				cost += 12 * (fullPayment - payment);
				schedule.push({ year: index + 1, rate: yearRate, payment: payment });
			});
			return { type: "temporary", rate: rate, full_payment: fullPayment, schedule: schedule, full_from: steps.length + 1, cost: bd_round(cost) };
		}
		points = Math.max(0, points);
		var newRate = Math.max(0, rate - points * Math.max(0, perPoint));
		var newPayment = bd_payment(loan, newRate, months);
		var savings = bd_round(fullPayment - newPayment);
		var pointsCost = bd_round((loan * points) / 100);
		return {
			type: "permanent",
			rate: rate,
			full_payment: fullPayment,
			new_rate: newRate,
			new_payment: newPayment,
			monthly_savings: savings,
			cost: pointsCost,
			breakeven_months: savings > 0 ? Math.ceil(pointsCost / savings) : 0,
		};
	}
	function bd_money(n) {
		return n.toLocaleString("en-US", { minimumFractionDigits: 2, maximumFractionDigits: 2 });
	}
	function bd_percent(n) {
		return parseFloat(n.toFixed(3)) + "%";
	}
	// Fills %s / %1$s placeholders in the translated labels printed by the view
	function bd_format(template) {
		var args = arguments;
		return template.replace(/%(\d)\$s|%s/g, function (match, position) {
			return position ? args[position] : args[1];
		});
	}
	function bd_number(input) {
		var n = parseFloat(removeco(String(input.val() || "")));
		return isNaN(n) ? 0 : Math.max(0, n);
	}
	function bd_render($form) {
		var term = $form.find(".bd-term").val();
		var result = bd_calculate(bd_number($form.find(".bd-loan")), bd_number($form.find(".bd-rate")), term, $form.find(".bd-type").val(), bd_number($form.find(".bd-points")), bd_number($form.find(".bd-per-point")));
		var rows = $form.find(".bd-rows");
		var currency = rows.attr("data-currency") || "";
		var label = function (key) {
			return rows.attr("data-label-" + key) || "";
		};
		var isTemporary = result.type === "temporary";
		var items = [];
		$form.find(".bd-permanent-only").toggle(!isTemporary);
		var headlineLabel = $form.find(".bd-headline-label");
		headlineLabel.text(headlineLabel.attr(isTemporary ? "data-temporary" : "data-permanent"));
		if (isTemporary) {
			result.schedule.forEach(function (year) {
				items.push([bd_format(label("year"), year.year, bd_percent(year.rate)), currency + bd_money(year.payment)]);
			});
			items.push([bd_format(label("rest"), result.full_from, term, bd_percent(result.rate)), currency + bd_money(result.full_payment)]);
			items.push([label("cost"), currency + bd_money(result.cost)]);
			$form.find(".bd-headline").text(bd_money(result.schedule[0].payment));
		} else {
			items.push([bd_format(label("original"), bd_percent(result.rate)), currency + bd_money(result.full_payment)]);
			items.push([label("new-rate"), bd_percent(result.new_rate)]);
			items.push([label("savings"), currency + bd_money(result.monthly_savings)]);
			items.push([label("points-cost"), currency + bd_money(result.cost)]);
			items.push([label("breakeven"), result.breakeven_months > 0 ? bd_format(label("months"), result.breakeven_months) : label("never")]);
			$form.find(".bd-headline").text(bd_money(result.new_payment));
		}
		rows.empty();
		items.forEach(function (item) {
			rows.append($("<p>").text(item[0] + " ").append($("<strong>").text(item[1])));
		});
		bd_chart($form, result, term);
	}
	// Year-by-year payment bars (temporary) or current vs. new payment (permanent)
	function bd_chart($form, result, term) {
		var chart = $form.find(".bd-chart");
		if (!chart.length) {
			return;
		}
		var bars;
		if (result.type === "temporary") {
			bars = result.schedule.map(function (year) {
				return { label: chart.attr("data-label-year").replace("%s", year.year), amount: year.payment, current: true };
			});
			bars.push({ label: chart.attr("data-label-rest").replace("%s", result.full_from), amount: result.full_payment, current: false });
		} else {
			bars = [
				{ label: bd_percent(result.rate), amount: result.full_payment, current: false },
				{ label: bd_percent(result.new_rate), amount: result.new_payment, current: true },
			];
		}
		var max = Math.max.apply(null, bars.map(function (bar) { return bar.amount; })) || 1;
		chart.empty();
		bars.forEach(function (bar) {
			var column = $('<div class="bd-bar">').toggleClass("bd-bar-muted", !bar.current);
			$("<span>").text(Math.round(bar.amount).toLocaleString("en-US")).appendTo(column);
			$("<i>").css("height", Math.max(6, Math.round((bar.amount / max) * 88)) + "px").appendTo(column);
			$("<span>").text(bar.label).appendTo(column);
			chart.append(column);
		});
	}
	$("form.mcalc-buydown").each(function () {
		var $form = $(this);
		bd_render($form);
		$form.on("input change", ".bd-loan, .bd-rate, .bd-term, .bd-type, .bd-points, .bd-per-point", function () {
			if ($(this).hasClass("bd-loan")) {
				var digits = $(this).val().replace(/[^\d]/g, "");
				$(this).val(digits ? addCommas(digits) : "");
			}
			bd_render($form);
		});
	});
	/*
	 * Modern style. The calculator code above keeps working on the original fields; everything
	 * below only adds controls that drive those fields and readouts that mirror them.
	 */
	var BREAKDOWN_SHADES = [100, 72, 52, 36, 24, 14];
	function mcwp_amount(text) {
		return parseFloat(String(text).replace(/[^0-9.]/g, "")) || 0;
	}
	function mcwp_money(symbol, amount) {
		return (symbol || "") + Math.round(amount).toLocaleString("en-US");
	}
	// Payment breakdown bar: one segment per .mcalc-part row, in shades of the accent color
	function mcwp_breakdown($form) {
		var amounts = [];
		var bar = $form.find(".mcalc-breakdown").empty();
		$form.find(".mcalc-part").each(function (index) {
			var shade = BREAKDOWN_SHADES[Math.min(index, BREAKDOWN_SHADES.length - 1)] + "%";
			var amount = mcwp_amount($(this).find("strong").text());
			amounts.push(amount);
			this.style.setProperty("--mcalc-shade", shade);
			if (bar.length && amount > 0) {
				var segment = $("<span>").appendTo(bar)[0];
				segment.style.flexGrow = amount;
				segment.style.setProperty("--mcalc-shade", shade);
			}
		});
		return amounts;
	}
	// The folded "Taxes, insurance and HOA" line shows what those costs add each month
	// (every payment part except principal and interest, which is always the first)
	function mcwp_more_total($form, amounts) {
		var more = $form.find(".mcalc-more");
		if (!more.length) {
			return;
		}
		var extra = amounts.slice(1).reduce(function (sum, amount) {
			return sum + amount;
		}, 0);
		more.find(".mcalc-more-total").text(bd_format(more.attr("data-per-month"), mcwp_money(more.attr("data-currency"), extra)));
	}
	// One line under the payment, built from the fields named in the line's data attributes
	function mcwp_summary($form) {
		var line = $form.find(".mcalc-summary");
		if (!line.length) {
			return;
		}
		var value = function (selector) {
			return selector ? String($form.find(selector).val() || "") : "";
		};
		var amount = mcwp_amount(value(line.attr("data-amount"))) - mcwp_amount(value(line.attr("data-minus")));
		var rate = mcwp_amount(value(line.attr("data-rate")));
		line.text(bd_format(line.attr("data-template"), mcwp_money(line.attr("data-currency"), Math.max(0, amount)), value(line.attr("data-term")), rate + "%"));
	}
	// Loan term and buydown type as a row of buttons; the hidden select still drives the calculator
	function mcwp_segments($form) {
		$form.find("select[data-mcalc-seg]").each(function () {
			var select = $(this);
			var unit = select.attr("data-unit");
			var group = $('<div class="mcalc-seg" role="group">').attr("aria-label", select.closest("div").children("label").first().text());
			select.find("option").each(function () {
				var option = $(this);
				var text = option.attr("data-short") || (unit ? option.val() + " " + unit : option.text());
				$('<button type="button">').text(text).attr({ "data-value": option.val(), "aria-label": option.text() }).appendTo(group);
			});
			var sync = function () {
				group.children().each(function () {
					$(this).attr("aria-pressed", String($(this).attr("data-value") === select.val()));
				});
			};
			group.on("click", "button", function () {
				var chosen = $(this).attr("data-value");
				if (select.val() !== chosen) {
					select.val(chosen).trigger("change");
				}
				sync();
			});
			select.on("change", sync).addClass("mcalc-seg-ready").after(group);
			sync();
		});
	}
	// Type-or-slide for percentage sliders that have no amount field (interest rate, PMI rate)
	function mcwp_rate_inputs($form) {
		$form.find(".mcalc-has-range").each(function () {
			var field = $(this);
			var slider = field.children("input.ex1");
			if (!slider.length || field.children('input[type="text"]').not(".ex1").length) {
				return;
			}
			var label = field.children("label").first();
			var input = $('<input type="text" class="mcalc-rate-input" inputmode="decimal" autocomplete="off">').attr("aria-label", label.text()).val(slider.val());
			$('<span class="mcalc-rate-box">').append(input, $('<span aria-hidden="true">%</span>')).insertAfter(label);
			field.addClass("mcalc-has-rate-input");
			slider.on("change", function () {
				if (document.activeElement !== input[0]) {
					input.val(slider.val());
				}
			});
			input.on("input", function () {
				var typed = parseFloat(this.value.replace(/[^0-9.]/g, ""));
				if (isNaN(typed)) {
					return;
				}
				var oldValue = parseFloat(slider.val());
				slider.bootstrapSlider("setValue", typed);
				var newValue = parseFloat(slider.val());
				if (oldValue !== newValue) {
					slider.trigger({ type: "change", value: { oldValue: oldValue, newValue: newValue } });
				}
			});
			input.on("blur", function () {
				input.val(slider.val());
			});
		});
	}
	// Phones: when the results sit below the inputs, pin the payment to the bottom of the screen
	// while the inputs are in view. Fixed elements can't live inside the form (it is a size
	// container), so the bar is appended to the body.
	function mcwp_dock($form) {
		var layout = $form.find(".mcalc-layout")[0];
		var main = $form.find(".mcalc-main")[0];
		var values = $form.find(".mcalc-values")[0];
		if (!layout || !main || !values || !("IntersectionObserver" in window)) {
			return null;
		}
		var dock = $('<button type="button" class="mcalc-dock"><span class="mcalc-dock-label"></span><b class="mcalc-dock-value"></b></button>').appendTo(document.body);
		dock[0].style.setProperty("--mcalc-accent", window.getComputedStyle($form[0]).getPropertyValue("--mcalc-accent"));
		var seen = { main: false, values: false };
		var update = function () {
			var stacked = window.getComputedStyle(layout).gridTemplateColumns.split(" ").length === 1;
			dock.toggleClass("mcalc-dock-visible", stacked && seen.main && !seen.values);
		};
		var observer = new IntersectionObserver(function (entries) {
			entries.forEach(function (entry) {
				seen[entry.target === main ? "main" : "values"] = entry.isIntersecting;
			});
			update();
		});
		observer.observe(main);
		observer.observe(values);
		$(window).on("resize", update);
		dock.on("click", function () {
			var reduce = window.matchMedia && window.matchMedia("(prefers-reduced-motion: reduce)").matches;
			values.scrollIntoView({ behavior: reduce ? "auto" : "smooth", block: "start" });
		});
		return dock;
	}
	function mcwp_refresh($form) {
		mcwp_more_total($form, mcwp_breakdown($form));
		mcwp_summary($form);
		var dock = $form.data("mcwpDock");
		if (dock) {
			var results = $form.find(".mcalc-results");
			dock.find(".mcalc-dock-label").text(results.children("h3, h4").first().text());
			dock.find(".mcalc-dock-value").text(results.children("h2").first().text().replace(/\s+/g, ""));
		}
	}
	$("form.mcalc-modern").each(function () {
		var $form = $(this);
		mcwp_segments($form);
		mcwp_rate_inputs($form);
		$form.data("mcwpDock", mcwp_dock($form));
		mcwp_refresh($form);
	});
	// Refresh after the calculators' own handlers have updated the numbers
	$(document).on("input change keyup", "form.mcalc-modern", function () {
		var $form = $(this);
		window.setTimeout(function () {
			mcwp_refresh($form);
		}, 0);
	});
	mcwp_badge();
}
// The reCAPTCHA badge shows for a couple of seconds, then hides. Google allows hiding it because
// each calculator's email form carries the reCAPTCHA notice instead (mcwp_email_block). The class
// goes on <html> because reCAPTCHA v2 replaces the badge element after every send.
function mcwp_badge() {
	if (!mcwp_ajax.recaptcha_site_key) {
		return;
	}
	var tries = 0;
	var find = window.setInterval(function () {
		var badge = document.querySelector(".grecaptcha-badge");
		if (badge || ++tries > 60) {
			window.clearInterval(find);
		}
		if (badge) {
			window.setTimeout(function () {
				document.documentElement.classList.add("mcwp-badge-hidden");
			}, 2000);
		}
	}, 250);
}
(function () {
	var started = false;
	var poll;
	function start() {
		if (!started) {
			started = true;
			window.clearInterval(poll);
			mcwp_start(jQuery);
		}
	}
	if (document.readyState !== "loading") {
		start();
		return;
	}
	document.addEventListener("DOMContentLoaded", start);
	// Some of those plugins also rename DOMContentLoaded listeners, so watch the parse state too
	poll = window.setInterval(function () {
		if (document.readyState !== "loading") {
			start();
		}
	}, 50);
})();
