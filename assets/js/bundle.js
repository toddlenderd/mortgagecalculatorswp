(function ($) {
	"use strict";
	function getElementSelection(that) {
		var position = {};
		if (that.selectionStart === undefined) {
			that.focus();
			var select = document.selection.createRange();
			position.length = select.text.length;
			select.moveStart("character", -that.value.length);
			position.end = select.text.length;
			position.start = position.end - position.length;
		} else {
			position.start = that.selectionStart;
			position.end = that.selectionEnd;
			position.length = position.end - position.start;
		}
		return position;
	}
	function setElementSelection(that, start, end) {
		if (that.selectionStart === undefined) {
			that.focus();
			var r = that.createTextRange();
			r.collapse(true);
			r.moveEnd("character", end);
			r.moveStart("character", start);
			r.select();
		} else {
			that.selectionStart = start;
			that.selectionEnd = end;
		}
	}
	function runCallbacks($this, settings) {
		$.each(settings, function (k, val) {
			if (typeof val === "function") {
				settings[k] = val($this, settings, k);
			} else if (typeof $this.autoNumeric[val] === "function") {
				settings[k] = $this.autoNumeric[val]($this, settings, k);
			}
		});
	}
	function convertKeyToNumber(settings, key) {
		if (typeof settings[key] === "string") {
			settings[key] *= 1;
		}
	}
	function autoCode($this, settings) {
		runCallbacks($this, settings);
		settings.oEvent = null;
		settings.tagList = ["b", "caption", "cite", "code", "dd", "del", "div", "dfn", "dt", "em", "h1", "h2", "h3", "h4", "h5", "h6", "ins", "kdb", "label", "li", "output", "p", "q", "s", "sample", "span", "strong", "td", "th", "u", "var"];
		var vmax = settings.vMax.toString().split("."),
			vmin = !settings.vMin && settings.vMin !== 0 ? [] : settings.vMin.toString().split(".");
		convertKeyToNumber(settings, "vMax");
		convertKeyToNumber(settings, "vMin");
		convertKeyToNumber(settings, "mDec");
		settings.mDec = settings.mRound === "CHF" ? "2" : settings.mDec;
		settings.allowLeading = true;
		settings.aNeg = settings.vMin < 0 ? "-" : "";
		vmax[0] = vmax[0].replace("-", "");
		vmin[0] = vmin[0].replace("-", "");
		settings.mInt = Math.max(vmax[0].length, vmin[0].length, 1);
		if (settings.mDec === null) {
			var vmaxLength = 0,
				vminLength = 0;
			if (vmax[1]) {
				vmaxLength = vmax[1].length;
			}
			if (vmin[1]) {
				vminLength = vmin[1].length;
			}
			settings.mDec = Math.max(vmaxLength, vminLength);
		}
		if (settings.altDec === null && settings.mDec > 0) {
			if (settings.aDec === "." && settings.aSep !== ",") {
				settings.altDec = ",";
			} else if (settings.aDec === "," && settings.aSep !== ".") {
				settings.altDec = ".";
			}
		}
		var aNegReg = settings.aNeg ? "([-\\" + settings.aNeg + "]?)" : "(-?)";
		settings.aNegRegAutoStrip = aNegReg;
		settings.skipFirstAutoStrip = new RegExp(aNegReg + "[^-" + (settings.aNeg ? "\\" + settings.aNeg : "") + "\\" + settings.aDec + "\\d]" + ".*?(\\d|\\" + settings.aDec + "\\d)");
		settings.skipLastAutoStrip = new RegExp("(\\d\\" + settings.aDec + "?)[^\\" + settings.aDec + "\\d]\\D*$");
		var allowed = "-" + settings.aNum + "\\" + settings.aDec;
		settings.allowedAutoStrip = new RegExp("[^" + allowed + "]", "gi");
		settings.numRegAutoStrip = new RegExp(aNegReg + "(?:\\" + settings.aDec + "?(\\d+\\" + settings.aDec + "\\d+)|(\\d*(?:\\" + settings.aDec + "\\d*)?))");
		return settings;
	}
	function autoStrip(s, settings, strip_zero) {
		if (settings.aSign) {
			while (s.indexOf(settings.aSign) > -1) {
				s = s.replace(settings.aSign, "");
			}
		}
		s = s.replace(settings.skipFirstAutoStrip, "$1$2");
		s = s.replace(settings.skipLastAutoStrip, "$1");
		s = s.replace(settings.allowedAutoStrip, "");
		if (settings.altDec) {
			s = s.replace(settings.altDec, settings.aDec);
		}
		var m = s.match(settings.numRegAutoStrip);
		s = m ? [m[1], m[2], m[3]].join("") : "";
		if ((settings.lZero === "allow" || settings.lZero === "keep") && strip_zero !== "strip") {
			var parts = [],
				nSign = "";
			parts = s.split(settings.aDec);
			if (parts[0].indexOf("-") !== -1) {
				nSign = "-";
				parts[0] = parts[0].replace("-", "");
			}
			if (parts[0].length > settings.mInt && parts[0].charAt(0) === "0") {
				parts[0] = parts[0].slice(1);
			}
			s = nSign + parts.join(settings.aDec);
		}
		if ((strip_zero && settings.lZero === "deny") || (strip_zero && settings.lZero === "allow" && settings.allowLeading === false)) {
			var strip_reg = "^" + settings.aNegRegAutoStrip + "0*(\\d" + (strip_zero === "leading" ? ")" : "|$)");
			strip_reg = new RegExp(strip_reg);
			s = s.replace(strip_reg, "$1$2");
		}
		return s;
	}
	function negativeBracket(s, nBracket, oEvent) {
		nBracket = nBracket.split(",");
		if (oEvent === "set" || oEvent === "focusout") {
			s = s.replace("-", "");
			s = nBracket[0] + s + nBracket[1];
		} else if ((oEvent === "get" || oEvent === "focusin" || oEvent === "pageLoad") && s.charAt(0) === nBracket[0]) {
			s = s.replace(nBracket[0], "-");
			s = s.replace(nBracket[1], "");
		}
		return s;
	}
	function truncateDecimal(s, aDec, mDec) {
		if (aDec && mDec) {
			var parts = s.split(aDec);
			if (parts[1] && parts[1].length > mDec) {
				if (mDec > 0) {
					parts[1] = parts[1].substring(0, mDec);
					s = parts.join(aDec);
				} else {
					s = parts[0];
				}
			}
		}
		return s;
	}
	function fixNumber(s, aDec, aNeg) {
		if (aDec && aDec !== ".") {
			s = s.replace(aDec, ".");
		}
		if (aNeg && aNeg !== "-") {
			s = s.replace(aNeg, "-");
		}
		if (!s.match(/\d/)) {
			s += "0";
		}
		return s;
	}
	function checkValue(value, settings) {
		if (value) {
			var checkSmall = +value;
			if (checkSmall < 1e-6 && checkSmall > -1) {
				value = +value;
				if (value < 1e-6 && value > 0) {
					value = (value + 10).toString();
					value = value.substring(1);
				}
				if (value < 0 && value > -1) {
					value = (value - 10).toString();
					value = "-" + value.substring(2);
				}
				value = value.toString();
			} else {
				var parts = value.split(".");
				if (parts[1] !== undefined) {
					if (+parts[1] === 0) {
						value = parts[0];
					} else {
						parts[1] = parts[1].replace(/0*$/, "");
						value = parts.join(".");
					}
				}
			}
		}
		return settings.lZero === "keep" ? value : value.replace(/^0*(\d)/, "$1");
	}
	function presentNumber(s, aDec, aNeg) {
		if (aNeg && aNeg !== "-") {
			s = s.replace("-", aNeg);
		}
		if (aDec && aDec !== ".") {
			s = s.replace(".", aDec);
		}
		return s;
	}
	function autoCheck(s, settings) {
		s = autoStrip(s, settings);
		s = truncateDecimal(s, settings.aDec, settings.mDec);
		s = fixNumber(s, settings.aDec, settings.aNeg);
		var value = +s;
		if (settings.oEvent === "set" && (value < settings.vMin || value > settings.vMax)) {
			$.error("The value (" + value + ") from the 'set' method falls outside of the vMin / vMax range");
		}
		return value >= settings.vMin && value <= settings.vMax;
	}
	function checkEmpty(iv, settings, signOnEmpty) {
		if (iv === "" || iv === settings.aNeg) {
			if (settings.wEmpty === "zero") {
				return iv + "0";
			}
			if (settings.wEmpty === "sign" || signOnEmpty) {
				return iv + settings.aSign;
			}
			return iv;
		}
		return null;
	}
	function autoGroup(iv, settings) {
		iv = autoStrip(iv, settings);
		var testNeg = iv.replace(",", "."),
			empty = checkEmpty(iv, settings, true);
		if (empty !== null) {
			return empty;
		}
		var digitalGroup = "";
		if (settings.dGroup === 2) {
			digitalGroup = /(\d)((\d)(\d{2}?)+)$/;
		} else if (settings.dGroup === 4) {
			digitalGroup = /(\d)((\d{4}?)+)$/;
		} else {
			digitalGroup = /(\d)((\d{3}?)+)$/;
		}
		var ivSplit = iv.split(settings.aDec);
		if (settings.altDec && ivSplit.length === 1) {
			ivSplit = iv.split(settings.altDec);
		}
		var s = ivSplit[0];
		if (settings.aSep) {
			while (digitalGroup.test(s)) {
				s = s.replace(digitalGroup, "$1" + settings.aSep + "$2");
			}
		}
		if (settings.mDec !== 0 && ivSplit.length > 1) {
			if (ivSplit[1].length > settings.mDec) {
				ivSplit[1] = ivSplit[1].substring(0, settings.mDec);
			}
			iv = s + settings.aDec + ivSplit[1];
		} else {
			iv = s;
		}
		if (settings.aSign) {
			var has_aNeg = iv.indexOf(settings.aNeg) !== -1;
			iv = iv.replace(settings.aNeg, "");
			iv = settings.pSign === "p" ? settings.aSign + iv : iv + settings.aSign;
			if (has_aNeg) {
				iv = settings.aNeg + iv;
			}
		}
		if (settings.oEvent === "set" && testNeg < 0 && settings.nBracket !== null) {
			iv = negativeBracket(iv, settings.nBracket, settings.oEvent);
		}
		return iv;
	}
	function autoRound(iv, settings) {
		iv = iv === "" ? "0" : iv.toString();
		convertKeyToNumber(settings, "mDec");
		if (settings.mRound === "CHF") {
			iv = (Math.round(iv * 20) / 20).toString();
		}
		var ivRounded = "",
			i = 0,
			nSign = "",
			rDec = typeof settings.aPad === "boolean" || settings.aPad === null ? (settings.aPad ? settings.mDec : 0) : +settings.aPad;
		var truncateZeros = function (ivRounded) {
			var regex = rDec === 0 ? /(\.(?:\d*[1-9])?)0*$/ : rDec === 1 ? /(\.\d(?:\d*[1-9])?)0*$/ : new RegExp("(\\.\\d{" + rDec + "}(?:\\d*[1-9])?)0*$");
			ivRounded = ivRounded.replace(regex, "$1");
			if (rDec === 0) {
				ivRounded = ivRounded.replace(/\.$/, "");
			}
			return ivRounded;
		};
		if (iv.charAt(0) === "-") {
			nSign = "-";
			iv = iv.replace("-", "");
		}
		if (!iv.match(/^\d/)) {
			iv = "0" + iv;
		}
		if (nSign === "-" && +iv === 0) {
			nSign = "";
		}
		if ((+iv > 0 && settings.lZero !== "keep") || (iv.length > 0 && settings.lZero === "allow")) {
			iv = iv.replace(/^0*(\d)/, "$1");
		}
		var dPos = iv.lastIndexOf("."),
			vdPos = dPos === -1 ? iv.length - 1 : dPos,
			cDec = iv.length - 1 - vdPos;
		if (cDec <= settings.mDec) {
			ivRounded = iv;
			if (cDec < rDec) {
				if (dPos === -1) {
					ivRounded += ".";
				}
				var zeros = "000000";
				while (cDec < rDec) {
					zeros = zeros.substring(0, rDec - cDec);
					ivRounded += zeros;
					cDec += zeros.length;
				}
			} else if (cDec > rDec) {
				ivRounded = truncateZeros(ivRounded);
			} else if (cDec === 0 && rDec === 0) {
				ivRounded = ivRounded.replace(/\.$/, "");
			}
			if (settings.mRound !== "CHF") {
				return +ivRounded === 0 ? ivRounded : nSign + ivRounded;
			}
			if (settings.mRound === "CHF") {
				dPos = ivRounded.lastIndexOf(".");
				iv = ivRounded;
			}
		}
		var rLength = dPos + settings.mDec,
			tRound = +iv.charAt(rLength + 1),
			ivArray = iv.substring(0, rLength + 1).split(""),
			odd = iv.charAt(rLength) === "." ? iv.charAt(rLength - 1) % 2 : iv.charAt(rLength) % 2,
			onePass = true;
		if (odd !== 1) {
			odd = odd === 0 && iv.substring(rLength + 2, iv.length) > 0 ? 1 : 0;
		}
		if (
			(tRound > 4 && settings.mRound === "S") ||
			(tRound > 4 && settings.mRound === "A" && nSign === "") ||
			(tRound > 5 && settings.mRound === "A" && nSign === "-") ||
			(tRound > 5 && settings.mRound === "s") ||
			(tRound > 5 && settings.mRound === "a" && nSign === "") ||
			(tRound > 4 && settings.mRound === "a" && nSign === "-") ||
			(tRound > 5 && settings.mRound === "B") ||
			(tRound === 5 && settings.mRound === "B" && odd === 1) ||
			(tRound > 0 && settings.mRound === "C" && nSign === "") ||
			(tRound > 0 && settings.mRound === "F" && nSign === "-") ||
			(tRound > 0 && settings.mRound === "U") ||
			settings.mRound === "CHF"
		) {
			for (i = ivArray.length - 1; i >= 0; i -= 1) {
				if (ivArray[i] !== ".") {
					if (settings.mRound === "CHF" && ivArray[i] <= 2 && onePass) {
						ivArray[i] = 0;
						onePass = false;
						break;
					}
					if (settings.mRound === "CHF" && ivArray[i] <= 7 && onePass) {
						ivArray[i] = 5;
						onePass = false;
						break;
					}
					if (settings.mRound === "CHF" && onePass) {
						ivArray[i] = 10;
						onePass = false;
					} else {
						ivArray[i] = +ivArray[i] + 1;
					}
					if (ivArray[i] < 10) {
						break;
					}
					if (i > 0) {
						ivArray[i] = "0";
					}
				}
			}
		}
		ivArray = ivArray.slice(0, rLength + 1);
		ivRounded = truncateZeros(ivArray.join(""));
		return +ivRounded === 0 ? ivRounded : nSign + ivRounded;
	}
	function AutoNumericHolder(that, settings) {
		this.settings = settings;
		this.that = that;
		this.$that = $(that);
		this.formatted = false;
		this.settingsClone = autoCode(this.$that, this.settings);
		this.value = that.value;
	}
	AutoNumericHolder.prototype = {
		init: function (e) {
			this.value = this.that.value;
			this.settingsClone = autoCode(this.$that, this.settings);
			this.ctrlKey = e.ctrlKey;
			this.cmdKey = e.metaKey;
			this.shiftKey = e.shiftKey;
			this.selection = getElementSelection(this.that);
			if (e.type === "keydown" || e.type === "keyup") {
				this.kdCode = e.keyCode;
			}
			this.which = e.which;
			this.processed = false;
			this.formatted = false;
		},
		setSelection: function (start, end, setReal) {
			start = Math.max(start, 0);
			end = Math.min(end, this.that.value.length);
			this.selection = { start: start, end: end, length: end - start };
			if (setReal === undefined || setReal) {
				setElementSelection(this.that, start, end);
			}
		},
		setPosition: function (pos, setReal) {
			this.setSelection(pos, pos, setReal);
		},
		getBeforeAfter: function () {
			var value = this.value,
				left = value.substring(0, this.selection.start),
				right = value.substring(this.selection.end, value.length);
			return [left, right];
		},
		getBeforeAfterStriped: function () {
			var parts = this.getBeforeAfter();
			parts[0] = autoStrip(parts[0], this.settingsClone);
			parts[1] = autoStrip(parts[1], this.settingsClone);
			return parts;
		},
		normalizeParts: function (left, right) {
			var settingsClone = this.settingsClone;
			right = autoStrip(right, settingsClone);
			var strip = right.match(/^\d/) ? true : "leading";
			left = autoStrip(left, settingsClone, strip);
			if ((left === "" || left === settingsClone.aNeg) && settingsClone.lZero === "deny") {
				if (right > "") {
					right = right.replace(/^0*(\d)/, "$1");
				}
			}
			var new_value = left + right;
			if (settingsClone.aDec) {
				var m = new_value.match(new RegExp("^" + settingsClone.aNegRegAutoStrip + "\\" + settingsClone.aDec));
				if (m) {
					left = left.replace(m[1], m[1] + "0");
					new_value = left + right;
				}
			}
			if (settingsClone.wEmpty === "zero" && (new_value === settingsClone.aNeg || new_value === "")) {
				left += "0";
			}
			return [left, right];
		},
		setValueParts: function (left, right) {
			var settingsClone = this.settingsClone,
				parts = this.normalizeParts(left, right),
				new_value = parts.join(""),
				position = parts[0].length;
			if (autoCheck(new_value, settingsClone)) {
				new_value = truncateDecimal(new_value, settingsClone.aDec, settingsClone.mDec);
				if (position > new_value.length) {
					position = new_value.length;
				}
				this.value = new_value;
				this.setPosition(position, false);
				return true;
			}
			return false;
		},
		signPosition: function () {
			var settingsClone = this.settingsClone,
				aSign = settingsClone.aSign,
				that = this.that;
			if (aSign) {
				var aSignLen = aSign.length;
				if (settingsClone.pSign === "p") {
					var hasNeg = settingsClone.aNeg && that.value && that.value.charAt(0) === settingsClone.aNeg;
					return hasNeg ? [1, aSignLen + 1] : [0, aSignLen];
				}
				var valueLen = that.value.length;
				return [valueLen - aSignLen, valueLen];
			}
			return [1e3, -1];
		},
		expandSelectionOnSign: function (setReal) {
			var sign_position = this.signPosition(),
				selection = this.selection;
			if (selection.start < sign_position[1] && selection.end > sign_position[0]) {
				if ((selection.start < sign_position[0] || selection.end > sign_position[1]) && this.value.substring(Math.max(selection.start, sign_position[0]), Math.min(selection.end, sign_position[1])).match(/^\s*$/)) {
					if (selection.start < sign_position[0]) {
						this.setSelection(selection.start, sign_position[0], setReal);
					} else {
						this.setSelection(sign_position[1], selection.end, setReal);
					}
				} else {
					this.setSelection(Math.min(selection.start, sign_position[0]), Math.max(selection.end, sign_position[1]), setReal);
				}
			}
		},
		checkPaste: function () {
			if (this.valuePartsBeforePaste !== undefined) {
				var parts = this.getBeforeAfter(),
					oldParts = this.valuePartsBeforePaste;
				delete this.valuePartsBeforePaste;
				parts[0] = parts[0].substr(0, oldParts[0].length) + autoStrip(parts[0].substr(oldParts[0].length), this.settingsClone);
				if (!this.setValueParts(parts[0], parts[1])) {
					this.value = oldParts.join("");
					this.setPosition(oldParts[0].length, false);
				}
			}
		},
		skipAllways: function (e) {
			var kdCode = this.kdCode,
				which = this.which,
				ctrlKey = this.ctrlKey,
				cmdKey = this.cmdKey,
				shiftKey = this.shiftKey;
			if (((ctrlKey || cmdKey) && e.type === "keyup" && this.valuePartsBeforePaste !== undefined) || (shiftKey && kdCode === 45)) {
				this.checkPaste();
				return false;
			}
			if ((kdCode >= 112 && kdCode <= 123) || (kdCode >= 91 && kdCode <= 93) || (kdCode >= 9 && kdCode <= 31) || (kdCode < 8 && (which === 0 || which === kdCode)) || kdCode === 144 || kdCode === 145 || kdCode === 45) {
				return true;
			}
			if ((ctrlKey || cmdKey) && kdCode === 65) {
				return true;
			}
			if ((ctrlKey || cmdKey) && (kdCode === 67 || kdCode === 86 || kdCode === 88)) {
				if (e.type === "keydown") {
					this.expandSelectionOnSign();
				}
				if (kdCode === 86 || kdCode === 45) {
					if (e.type === "keydown" || e.type === "keypress") {
						if (this.valuePartsBeforePaste === undefined) {
							this.valuePartsBeforePaste = this.getBeforeAfter();
						}
					} else {
						this.checkPaste();
					}
				}
				return e.type === "keydown" || e.type === "keypress" || kdCode === 67;
			}
			if (ctrlKey || cmdKey) {
				return true;
			}
			if (kdCode === 37 || kdCode === 39) {
				var aSep = this.settingsClone.aSep,
					start = this.selection.start,
					value = this.that.value;
				if (e.type === "keydown" && aSep && !this.shiftKey) {
					if (kdCode === 37 && value.charAt(start - 2) === aSep) {
						this.setPosition(start - 1);
					} else if (kdCode === 39 && value.charAt(start + 1) === aSep) {
						this.setPosition(start + 1);
					}
				}
				return true;
			}
			if (kdCode >= 34 && kdCode <= 40) {
				return true;
			}
			return false;
		},
		processAllways: function () {
			var parts;
			if (this.kdCode === 8 || this.kdCode === 46) {
				if (!this.selection.length) {
					parts = this.getBeforeAfterStriped();
					if (this.kdCode === 8) {
						parts[0] = parts[0].substring(0, parts[0].length - 1);
					} else {
						parts[1] = parts[1].substring(1, parts[1].length);
					}
					this.setValueParts(parts[0], parts[1]);
				} else {
					this.expandSelectionOnSign(false);
					parts = this.getBeforeAfterStriped();
					this.setValueParts(parts[0], parts[1]);
				}
				return true;
			}
			return false;
		},
		processKeypress: function () {
			var settingsClone = this.settingsClone,
				cCode = String.fromCharCode(this.which),
				parts = this.getBeforeAfterStriped(),
				left = parts[0],
				right = parts[1];
			if (cCode === settingsClone.aDec || (settingsClone.altDec && cCode === settingsClone.altDec) || ((cCode === "." || cCode === ",") && this.kdCode === 110)) {
				if (!settingsClone.mDec || !settingsClone.aDec) {
					return true;
				}
				if (settingsClone.aNeg && right.indexOf(settingsClone.aNeg) > -1) {
					return true;
				}
				if (left.indexOf(settingsClone.aDec) > -1) {
					return true;
				}
				if (right.indexOf(settingsClone.aDec) > 0) {
					return true;
				}
				if (right.indexOf(settingsClone.aDec) === 0) {
					right = right.substr(1);
				}
				this.setValueParts(left + settingsClone.aDec, right);
				return true;
			}
			if (cCode === "-" || cCode === "+") {
				if (!settingsClone.aNeg) {
					return true;
				}
				if (left === "" && right.indexOf(settingsClone.aNeg) > -1) {
					left = settingsClone.aNeg;
					right = right.substring(1, right.length);
				}
				if (left.charAt(0) === settingsClone.aNeg) {
					left = left.substring(1, left.length);
				} else {
					left = cCode === "-" ? settingsClone.aNeg + left : left;
				}
				this.setValueParts(left, right);
				return true;
			}
			if (cCode >= "0" && cCode <= "9") {
				if (settingsClone.aNeg && left === "" && right.indexOf(settingsClone.aNeg) > -1) {
					left = settingsClone.aNeg;
					right = right.substring(1, right.length);
				}
				if (settingsClone.vMax <= 0 && settingsClone.vMin < settingsClone.vMax && this.value.indexOf(settingsClone.aNeg) === -1 && cCode !== "0") {
					left = settingsClone.aNeg + left;
				}
				this.setValueParts(left + cCode, right);
				return true;
			}
			return true;
		},
		formatQuick: function () {
			var settingsClone = this.settingsClone,
				parts = this.getBeforeAfterStriped(),
				leftLength = this.value;
			if ((settingsClone.aSep === "" || (settingsClone.aSep !== "" && leftLength.indexOf(settingsClone.aSep) === -1)) && (settingsClone.aSign === "" || (settingsClone.aSign !== "" && leftLength.indexOf(settingsClone.aSign) === -1))) {
				var subParts = [],
					nSign = "";
				subParts = leftLength.split(settingsClone.aDec);
				if (subParts[0].indexOf("-") > -1) {
					nSign = "-";
					subParts[0] = subParts[0].replace("-", "");
					parts[0] = parts[0].replace("-", "");
				}
				if (subParts[0].length > settingsClone.mInt && parts[0].charAt(0) === "0") {
					parts[0] = parts[0].slice(1);
				}
				parts[0] = nSign + parts[0];
			}
			var value = autoGroup(this.value, this.settingsClone),
				position = value.length;
			if (value) {
				var left_ar = parts[0].split(""),
					i = 0;
				for (i; i < left_ar.length; i += 1) {
					if (!left_ar[i].match("\\d")) {
						left_ar[i] = "\\" + left_ar[i];
					}
				}
				var leftReg = new RegExp("^.*?" + left_ar.join(".*?"));
				var newLeft = value.match(leftReg);
				if (newLeft) {
					position = newLeft[0].length;
					if (((position === 0 && value.charAt(0) !== settingsClone.aNeg) || (position === 1 && value.charAt(0) === settingsClone.aNeg)) && settingsClone.aSign && settingsClone.pSign === "p") {
						position = this.settingsClone.aSign.length + (value.charAt(0) === "-" ? 1 : 0);
					}
				} else if (settingsClone.aSign && settingsClone.pSign === "s") {
					position -= settingsClone.aSign.length;
				}
			}
			this.that.value = value;
			this.setPosition(position);
			this.formatted = true;
		},
	};
	function autoGet(obj) {
		if (typeof obj === "string") {
			obj = obj.replace(/\[/g, "\\[").replace(/\]/g, "\\]");
			obj = "#" + obj.replace(/(:|\.)/g, "\\$1");
		}
		return $(obj);
	}
	function getHolder($that, settings, update) {
		var data = $that.data("autoNumeric");
		if (!data) {
			data = {};
			$that.data("autoNumeric", data);
		}
		var holder = data.holder;
		if ((holder === undefined && settings) || update) {
			holder = new AutoNumericHolder($that.get(0), settings);
			data.holder = holder;
		}
		return holder;
	}
	var methods = {
		init: function (options) {
			return this.each(function () {
				var $this = $(this),
					settings = $this.data("autoNumeric"),
					tagData = $this.data();
				if (typeof settings !== "object") {
					var defaults = {
						aNum: "0123456789",
						aSep: ",",
						dGroup: "3",
						aDec: ".",
						altDec: null,
						aSign: "",
						pSign: "p",
						vMax: "9999999999999.99",
						vMin: "0.00",
						mDec: null,
						mRound: "S",
						aPad: true,
						nBracket: null,
						wEmpty: "empty",
						lZero: "allow",
						aForm: true,
						onSomeEvent: function () {},
					};
					settings = $.extend({}, defaults, tagData, options);
					if (settings.aDec === settings.aSep) {
						$.error("autoNumeric will not function properly when the decimal character aDec: '" + settings.aDec + "' and thousand separator aSep: '" + settings.aSep + "' are the same character");
						return this;
					}
					$this.data("autoNumeric", settings);
				} else {
					return this;
				}
				settings.runOnce = false;
				var holder = getHolder($this, settings);
				if ($.inArray($this.prop("tagName").toLowerCase(), settings.tagList) === -1 && $this.prop("tagName").toLowerCase() !== "input") {
					$.error("The <" + $this.prop("tagName").toLowerCase() + "> is not supported by autoNumeric()");
					return this;
				}
				if (settings.runOnce === false && settings.aForm) {
					if ($this.is("input[type=text], input[type=hidden], input[type=tel], input:not([type])")) {
						var setValue = true;
						if ($this[0].value === "" && settings.wEmpty === "empty") {
							$this[0].value = "";
							setValue = false;
						}
						if ($this[0].value === "" && settings.wEmpty === "sign") {
							$this[0].value = settings.aSign;
							setValue = false;
						}
						if (setValue) {
							$this.autoNumeric("set", $this.val());
						}
					}
					if ($.inArray($this.prop("tagName").toLowerCase(), settings.tagList) !== -1 && $this.text() !== "") {
						$this.autoNumeric("set", $this.text());
					}
				}
				settings.runOnce = true;
				if ($this.is("input[type=text], input[type=hidden], input[type=tel], input:not([type])")) {
					$this.on("keydown.autoNumeric", function (e) {
						holder = getHolder($this);
						if (holder.settings.aDec === holder.settings.aSep) {
							$.error("autoNumeric will not function properly when the decimal character aDec: '" + holder.settings.aDec + "' and thousand separator aSep: '" + holder.settings.aSep + "' are the same character");
							return this;
						}
						if (holder.that.readOnly) {
							holder.processed = true;
							return true;
						}
						holder.init(e);
						holder.settings.oEvent = "keydown";
						if (holder.skipAllways(e)) {
							holder.processed = true;
							return true;
						}
						if (holder.processAllways()) {
							holder.processed = true;
							holder.formatQuick();
							e.preventDefault();
							return false;
						}
						holder.formatted = false;
						return true;
					});
					$this.on("keypress.autoNumeric", function (e) {
						var holder = getHolder($this),
							processed = holder.processed;
						holder.init(e);
						holder.settings.oEvent = "keypress";
						if (holder.skipAllways(e)) {
							return true;
						}
						if (processed) {
							e.preventDefault();
							return false;
						}
						if (holder.processAllways() || holder.processKeypress()) {
							holder.formatQuick();
							e.preventDefault();
							return false;
						}
						holder.formatted = false;
					});
					$this.on("keyup.autoNumeric", function (e) {
						var holder = getHolder($this);
						holder.init(e);
						holder.settings.oEvent = "keyup";
						var skip = holder.skipAllways(e);
						holder.kdCode = 0;
						delete holder.valuePartsBeforePaste;
						if ($this[0].value === holder.settings.aSign) {
							if (holder.settings.pSign === "s") {
								setElementSelection(this, 0, 0);
							} else {
								setElementSelection(this, holder.settings.aSign.length, holder.settings.aSign.length);
							}
						}
						if (skip) {
							return true;
						}
						if (this.value === "") {
							return true;
						}
						if (!holder.formatted) {
							holder.formatQuick();
						}
					});
					$this.on("focusin.autoNumeric", function () {
						var holder = getHolder($this);
						holder.settingsClone.oEvent = "focusin";
						if (holder.settingsClone.nBracket !== null) {
							var checkVal = $this.val();
							$this.val(negativeBracket(checkVal, holder.settingsClone.nBracket, holder.settingsClone.oEvent));
						}
						holder.inVal = $this.val();
						var onempty = checkEmpty(holder.inVal, holder.settingsClone, true);
						if (onempty !== null) {
							$this.val(onempty);
							if (holder.settings.pSign === "s") {
								setElementSelection(this, 0, 0);
							} else {
								setElementSelection(this, holder.settings.aSign.length, holder.settings.aSign.length);
							}
						}
					});
					$this.on("focusout.autoNumeric", function () {
						var holder = getHolder($this),
							settingsClone = holder.settingsClone,
							value = $this.val(),
							origValue = value;
						holder.settingsClone.oEvent = "focusout";
						var strip_zero = "";
						if (settingsClone.lZero === "allow") {
							settingsClone.allowLeading = false;
							strip_zero = "leading";
						}
						if (value !== "") {
							value = autoStrip(value, settingsClone, strip_zero);
							if (checkEmpty(value, settingsClone) === null && autoCheck(value, settingsClone, $this[0])) {
								value = fixNumber(value, settingsClone.aDec, settingsClone.aNeg);
								value = autoRound(value, settingsClone);
								value = presentNumber(value, settingsClone.aDec, settingsClone.aNeg);
							} else {
								value = "";
							}
						}
						var groupedValue = checkEmpty(value, settingsClone, false);
						if (groupedValue === null) {
							groupedValue = autoGroup(value, settingsClone);
						}
						if (groupedValue !== origValue) {
							$this.val(groupedValue);
						}
						if (groupedValue !== holder.inVal) {
							$this.change();
							delete holder.inVal;
						}
						if (settingsClone.nBracket !== null && $this.autoNumeric("get") < 0) {
							holder.settingsClone.oEvent = "focusout";
							$this.val(negativeBracket($this.val(), settingsClone.nBracket, settingsClone.oEvent));
						}
					});
				}
			});
		},
		destroy: function () {
			return $(this).each(function () {
				var $this = $(this);
				$this.off(".autoNumeric");
				$this.removeData("autoNumeric");
			});
		},
		update: function (options) {
			return $(this).each(function () {
				var $this = autoGet($(this)),
					settings = $this.data("autoNumeric");
				if (typeof settings !== "object") {
					$.error("You must initialize autoNumeric('init', {options}) prior to calling the 'update' method");
					return this;
				}
				var strip = $this.autoNumeric("get");
				settings = $.extend(settings, options);
				getHolder($this, settings, true);
				if (settings.aDec === settings.aSep) {
					$.error("autoNumeric will not function properly when the decimal character aDec: '" + settings.aDec + "' and thousand separator aSep: '" + settings.aSep + "' are the same character");
					return this;
				}
				$this.data("autoNumeric", settings);
				if ($this.val() !== "" || $this.text() !== "") {
					return $this.autoNumeric("set", strip);
				}
				return;
			});
		},
		set: function (valueIn) {
			if (valueIn === null) {
				return;
			}
			return $(this).each(function () {
				var $this = autoGet($(this)),
					settings = $this.data("autoNumeric"),
					value = valueIn.toString(),
					testValue = valueIn.toString();
				if (typeof settings !== "object") {
					$.error("You must initialize autoNumeric('init', {options}) prior to calling the 'set' method");
					return this;
				}
				if (testValue !== $this.attr("value") && $this.prop("tagName").toLowerCase() === "input" && settings.runOnce === false) {
					value = settings.nBracket !== null ? negativeBracket($this.val(), settings.nBracket, "pageLoad") : value;
					value = autoStrip(value, settings);
				}
				if ((testValue === $this.attr("value") || testValue === $this.text()) && settings.runOnce === false) {
					value = value.replace(",", ".");
				}
				if (!$.isNumeric(+value)) {
					return "";
				}
				value = checkValue(value, settings);
				settings.oEvent = "set";
				value.toString();
				if (value !== "") {
					value = autoRound(value, settings);
				}
				value = presentNumber(value, settings.aDec, settings.aNeg);
				if (!autoCheck(value, settings)) {
					value = autoRound("", settings);
				}
				value = autoGroup(value, settings);
				if ($this.is("input[type=text], input[type=hidden], input[type=tel], input:not([type])")) {
					return $this.val(value);
				}
				if ($.inArray($this.prop("tagName").toLowerCase(), settings.tagList) !== -1) {
					return $this.text(value);
				}
				$.error("The <" + $this.prop("tagName").toLowerCase() + "> is not supported by autoNumeric()");
				return false;
			});
		},
		get: function () {
			var $this = autoGet($(this)),
				settings = $this.data("autoNumeric");
			if (typeof settings !== "object") {
				$.error("You must initialize autoNumeric('init', {options}) prior to calling the 'get' method");
				return this;
			}
			settings.oEvent = "get";
			var getValue = "";
			if ($this.is("input[type=text], input[type=hidden], input[type=tel], input:not([type])")) {
				getValue = $this.eq(0).val();
			} else if ($.inArray($this.prop("tagName").toLowerCase(), settings.tagList) !== -1) {
				getValue = $this.eq(0).text();
			} else {
				$.error("The <" + $this.prop("tagName").toLowerCase() + "> is not supported by autoNumeric()");
				return false;
			}
			if ((getValue === "" && settings.wEmpty === "empty") || (getValue === settings.aSign && (settings.wEmpty === "sign" || settings.wEmpty === "empty"))) {
				return "";
			}
			if (settings.nBracket !== null && getValue !== "") {
				getValue = negativeBracket(getValue, settings.nBracket, settings.oEvent);
			}
			if (settings.runOnce || settings.aForm === false) {
				getValue = autoStrip(getValue, settings);
			}
			getValue = fixNumber(getValue, settings.aDec, settings.aNeg);
			if (+getValue === 0 && settings.lZero !== "keep") {
				getValue = "0";
			}
			if (settings.lZero === "keep") {
				return getValue;
			}
			getValue = checkValue(getValue, settings);
			return getValue;
		},
		getString: function () {
			var isAutoNumeric = false,
				$this = autoGet($(this)),
				str = $this.serialize(),
				parts = str.split("&"),
				formIndex = $("form").index($this),
				i = 0;
			for (i; i < parts.length; i += 1) {
				var miniParts = parts[i].split("="),
					$field = $("form:eq(" + formIndex + ') input[name="' + decodeURIComponent(miniParts[0]) + '"]'),
					settings = $field.data("autoNumeric");
				if (typeof settings === "object") {
					if (miniParts[1] !== null) {
						miniParts[1] = $field.autoNumeric("get");
						parts[i] = miniParts.join("=");
						isAutoNumeric = true;
					}
				}
			}
			if (isAutoNumeric === true) {
				return parts.join("&");
			}
			return str;
		},
		getArray: function () {
			var isAutoNumeric = false,
				$this = autoGet($(this)),
				formFields = $this.serializeArray(),
				formIndex = $("form").index($this);
			$.each(formFields, function (i, field) {
				var $field = $("form:eq(" + formIndex + ') input[name="' + decodeURIComponent(field.name) + '"]'),
					settings = $field.data("autoNumeric");
				if (typeof settings === "object") {
					if (field.value !== "") {
						field.value = $field.autoNumeric("get").toString();
					}
					isAutoNumeric = true;
				}
			});
			if (isAutoNumeric === true) {
				return formFields;
			}
			return this;
		},
		getSettings: function () {
			var $this = autoGet($(this));
			return $this.eq(0).data("autoNumeric");
		},
	};
	$.fn.autoNumeric = function (method) {
		if (methods[method]) {
			return methods[method].apply(this, Array.prototype.slice.call(arguments, 1));
		}
		if (typeof method === "object" || !method) {
			return methods.init.apply(this, arguments);
		}
		$.error('Method "' + method + '" is not supported by autoNumeric()');
	};
})(jQuery);
var _typeof =
	typeof Symbol === "function" && typeof Symbol.iterator === "symbol"
		? function (obj) {
				return typeof obj;
		  }
		: function (obj) {
				return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj;
		  };
var windowIsDefined = (typeof window === "undefined" ? "undefined" : _typeof(window)) === "object";
(function (factory) {
	if (typeof define === "function" && define.amd) {
		define(["jquery"], factory);
	} else if ((typeof module === "undefined" ? "undefined" : _typeof(module)) === "object" && module.exports) {
		var jQuery;
		try {
			jQuery = require("jquery");
		} catch (err) {
			jQuery = null;
		}
		module.exports = factory(jQuery);
	} else if (window) {
		window.Slider = factory(window.jQuery);
	}
})(function ($) {
	var NAMESPACE_MAIN = "slider";
	var NAMESPACE_ALTERNATE = "bootstrapSlider";
	if (windowIsDefined && !window.console) {
		window.console = {};
	}
	if (windowIsDefined && !window.console.log) {
		window.console.log = function () {};
	}
	if (windowIsDefined && !window.console.warn) {
		window.console.warn = function () {};
	}
	var Slider;
	(function ($) {
		"use strict";
		var slice = Array.prototype.slice;
		function noop() {}
		function defineBridget($) {
			if (!$) {
				return;
			}
			function addOptionMethod(PluginClass) {
				if (PluginClass.prototype.option) {
					return;
				}
				PluginClass.prototype.option = function (opts) {
					if (!$.isPlainObject(opts)) {
						return;
					}
					this.options = $.extend(true, this.options, opts);
				};
			}
			var logError =
				typeof console === "undefined"
					? noop
					: function (message) {
							console.error(message);
					  };
			function bridge(namespace, PluginClass) {
				$.fn[namespace] = function (options) {
					if (typeof options === "string") {
						var args = slice.call(arguments, 1);
						for (var i = 0, len = this.length; i < len; i++) {
							var elem = this[i];
							var instance = $.data(elem, namespace);
							if (!instance) {
								logError("cannot call methods on " + namespace + " prior to initialization; " + "attempted to call '" + options + "'");
								continue;
							}
							if (!$.isFunction(instance[options]) || options.charAt(0) === "_") {
								logError("no such method '" + options + "' for " + namespace + " instance");
								continue;
							}
							var returnValue = instance[options].apply(instance, args);
							if (returnValue !== undefined && returnValue !== instance) {
								return returnValue;
							}
						}
						return this;
					} else {
						var objects = this.map(function () {
							var instance = $.data(this, namespace);
							if (instance) {
								instance.option(options);
								instance._init();
							} else {
								instance = new PluginClass(this, options);
								$.data(this, namespace, instance);
							}
							return $(this);
						});
						if (!objects || objects.length > 1) {
							return objects;
						} else {
							return objects[0];
						}
					}
				};
			}
			$.bridget = function (namespace, PluginClass) {
				addOptionMethod(PluginClass);
				bridge(namespace, PluginClass);
			};
			return $.bridget;
		}
		defineBridget($);
	})($);
	(function ($) {
		var ErrorMsgs = {
			formatInvalidInputErrorMsg: function formatInvalidInputErrorMsg(input) {
				return "Invalid input value '" + input + "' passed in";
			},
			callingContextNotSliderInstance: "Calling context element does not have instance of Slider bound to it. Check your code to make sure the JQuery object returned from the call to the slider() initializer is calling the method",
		};
		var SliderScale = {
			linear: {
				toValue: function toValue(percentage) {
					var rawValue = (percentage / 100) * (this.options.max - this.options.min);
					var shouldAdjustWithBase = true;
					if (this.options.ticks_positions.length > 0) {
						var minv,
							maxv,
							minp,
							maxp = 0;
						for (var i = 1; i < this.options.ticks_positions.length; i++) {
							if (percentage <= this.options.ticks_positions[i]) {
								minv = this.options.ticks[i - 1];
								minp = this.options.ticks_positions[i - 1];
								maxv = this.options.ticks[i];
								maxp = this.options.ticks_positions[i];
								break;
							}
						}
						var partialPercentage = (percentage - minp) / (maxp - minp);
						rawValue = minv + partialPercentage * (maxv - minv);
						shouldAdjustWithBase = false;
					}
					var adjustment = shouldAdjustWithBase ? this.options.min : 0;
					var value = adjustment + Math.round(rawValue / this.options.step) * this.options.step;
					if (value < this.options.min) {
						return this.options.min;
					} else if (value > this.options.max) {
						return this.options.max;
					} else {
						return value;
					}
				},
				toPercentage: function toPercentage(value) {
					if (this.options.max === this.options.min) {
						return 0;
					}
					if (this.options.ticks_positions.length > 0) {
						var minv,
							maxv,
							minp,
							maxp = 0;
						for (var i = 0; i < this.options.ticks.length; i++) {
							if (value <= this.options.ticks[i]) {
								minv = i > 0 ? this.options.ticks[i - 1] : 0;
								minp = i > 0 ? this.options.ticks_positions[i - 1] : 0;
								maxv = this.options.ticks[i];
								maxp = this.options.ticks_positions[i];
								break;
							}
						}
						if (i > 0) {
							var partialPercentage = (value - minv) / (maxv - minv);
							return minp + partialPercentage * (maxp - minp);
						}
					}
					return (100 * (value - this.options.min)) / (this.options.max - this.options.min);
				},
			},
			logarithmic: {
				toValue: function toValue(percentage) {
					var min = this.options.min === 0 ? 0 : Math.log(this.options.min);
					var max = Math.log(this.options.max);
					var value = Math.exp(min + ((max - min) * percentage) / 100);
					if (Math.round(value) === this.options.max) {
						return this.options.max;
					}
					value = this.options.min + Math.round((value - this.options.min) / this.options.step) * this.options.step;
					if (value < this.options.min) {
						return this.options.min;
					} else if (value > this.options.max) {
						return this.options.max;
					} else {
						return value;
					}
				},
				toPercentage: function toPercentage(value) {
					if (this.options.max === this.options.min) {
						return 0;
					} else {
						var max = Math.log(this.options.max);
						var min = this.options.min === 0 ? 0 : Math.log(this.options.min);
						var v = value === 0 ? 0 : Math.log(value);
						return (100 * (v - min)) / (max - min);
					}
				},
			},
		};
		Slider = function Slider(element, options) {
			createNewSlider.call(this, element, options);
			return this;
		};
		function createNewSlider(element, options) {
			this._state = {
				value: null,
				enabled: null,
				offset: null,
				size: null,
				percentage: null,
				inDrag: false,
				over: false,
			};
			this.ticksCallbackMap = {};
			this.handleCallbackMap = {};
			if (typeof element === "string") {
				this.element = document.querySelector(element);
			} else if (element instanceof HTMLElement) {
				this.element = element;
			}
			options = options ? options : {};
			var optionTypes = Object.keys(this.defaultOptions);
			for (var i = 0; i < optionTypes.length; i++) {
				var optName = optionTypes[i];
				var val = options[optName];
				val = typeof val !== "undefined" ? val : getDataAttrib(this.element, optName);
				val = val !== null ? val : this.defaultOptions[optName];
				if (!this.options) {
					this.options = {};
				}
				this.options[optName] = val;
			}
			if (this.options.rtl === "auto") {
				this.options.rtl = window.getComputedStyle(this.element).direction === "rtl";
			}
			if (this.options.orientation === "vertical" && (this.options.tooltip_position === "top" || this.options.tooltip_position === "bottom")) {
				if (this.options.rtl) {
					this.options.tooltip_position = "left";
				} else {
					this.options.tooltip_position = "right";
				}
			} else if (this.options.orientation === "horizontal" && (this.options.tooltip_position === "left" || this.options.tooltip_position === "right")) {
				this.options.tooltip_position = "top";
			}
			function getDataAttrib(element, optName) {
				var dataName = "data-slider-" + optName.replace(/_/g, "-");
				var dataValString = element.getAttribute(dataName);
				try {
					return JSON.parse(dataValString);
				} catch (err) {
					return dataValString;
				}
			}
			var origWidth = this.element.style.width;
			var updateSlider = false;
			var parent = this.element.parentNode;
			var sliderTrackSelection;
			var sliderTrackLow, sliderTrackHigh;
			var sliderMinHandle;
			var sliderMaxHandle;
			if (this.sliderElem) {
				updateSlider = true;
			} else {
				this.sliderElem = document.createElement("div");
				this.sliderElem.className = "slider";
				var sliderTrack = document.createElement("div");
				sliderTrack.className = "slider-track";
				sliderTrackLow = document.createElement("div");
				sliderTrackLow.className = "slider-track-low";
				sliderTrackSelection = document.createElement("div");
				sliderTrackSelection.className = "slider-selection";
				sliderTrackHigh = document.createElement("div");
				sliderTrackHigh.className = "slider-track-high";
				sliderMinHandle = document.createElement("div");
				sliderMinHandle.className = "slider-handle min-slider-handle";
				sliderMinHandle.setAttribute("role", "slider");
				sliderMinHandle.setAttribute("aria-valuemin", "0");
				sliderMinHandle.setAttribute("aria-valuemax", this.options.max);
				sliderMinHandle.setAttribute("aria-label", this.options.arialabel);
				sliderMaxHandle = document.createElement("div");
				sliderMaxHandle.className = "slider-handle max-slider-handle";
				sliderMaxHandle.setAttribute("role", "slider");
				sliderMaxHandle.setAttribute("aria-valuemin", this.options.min);
				sliderMaxHandle.setAttribute("aria-valuemax", this.options.max);
				sliderTrack.appendChild(sliderTrackLow);
				sliderTrack.appendChild(sliderTrackSelection);
				sliderTrack.appendChild(sliderTrackHigh);
				this.rangeHighlightElements = [];
				var rangeHighlightsOpts = this.options.rangeHighlights;
				if (Array.isArray(rangeHighlightsOpts) && rangeHighlightsOpts.length > 0) {
					for (var j = 0; j < rangeHighlightsOpts.length; j++) {
						var rangeHighlightElement = document.createElement("div");
						var customClassString = rangeHighlightsOpts[j].class || "";
						rangeHighlightElement.className = "slider-rangeHighlight slider-selection " + customClassString;
						this.rangeHighlightElements.push(rangeHighlightElement);
						sliderTrack.appendChild(rangeHighlightElement);
					}
				}
				var isLabelledbyArray = Array.isArray(this.options.labelledby);
				if (isLabelledbyArray && this.options.labelledby[0]) {
					sliderMinHandle.setAttribute("aria-labelledby", this.options.labelledby[0]);
				}
				if (isLabelledbyArray && this.options.labelledby[1]) {
					sliderMaxHandle.setAttribute("aria-labelledby", this.options.labelledby[1]);
				}
				if (!isLabelledbyArray && this.options.labelledby) {
					sliderMinHandle.setAttribute("aria-labelledby", this.options.labelledby);
					sliderMaxHandle.setAttribute("aria-labelledby", this.options.labelledby);
				}
				this.ticks = [];
				if (Array.isArray(this.options.ticks) && this.options.ticks.length > 0) {
					this.ticksContainer = document.createElement("div");
					this.ticksContainer.className = "slider-tick-container";
					for (i = 0; i < this.options.ticks.length; i++) {
						var tick = document.createElement("div");
						tick.className = "slider-tick";
						if (this.options.ticks_tooltip) {
							var tickListenerReference = this._addTickListener();
							var enterCallback = tickListenerReference.addMouseEnter(this, tick, i);
							var leaveCallback = tickListenerReference.addMouseLeave(this, tick);
							this.ticksCallbackMap[i] = {
								mouseEnter: enterCallback,
								mouseLeave: leaveCallback,
							};
						}
						this.ticks.push(tick);
						this.ticksContainer.appendChild(tick);
					}
					sliderTrackSelection.className += " tick-slider-selection";
				}
				this.tickLabels = [];
				if (Array.isArray(this.options.ticks_labels) && this.options.ticks_labels.length > 0) {
					this.tickLabelContainer = document.createElement("div");
					this.tickLabelContainer.className = "slider-tick-label-container";
					for (i = 0; i < this.options.ticks_labels.length; i++) {
						var label = document.createElement("div");
						var noTickPositionsSpecified = this.options.ticks_positions.length === 0;
						var tickLabelsIndex = this.options.reversed && noTickPositionsSpecified ? this.options.ticks_labels.length - (i + 1) : i;
						label.className = "slider-tick-label";
						label.innerHTML = this.options.ticks_labels[tickLabelsIndex];
						this.tickLabels.push(label);
						this.tickLabelContainer.appendChild(label);
					}
				}
				var createAndAppendTooltipSubElements = function createAndAppendTooltipSubElements(tooltipElem) {
					var arrow = document.createElement("div");
					arrow.className = "tooltip-arrow";
					var inner = document.createElement("div");
					inner.className = "tooltip-inner";
					tooltipElem.appendChild(arrow);
					tooltipElem.appendChild(inner);
				};
				var sliderTooltip = document.createElement("div");
				sliderTooltip.className = "tooltip tooltip-main";
				sliderTooltip.setAttribute("role", "presentation");
				createAndAppendTooltipSubElements(sliderTooltip);
				var sliderTooltipMin = document.createElement("div");
				sliderTooltipMin.className = "tooltip tooltip-min";
				sliderTooltipMin.setAttribute("role", "presentation");
				createAndAppendTooltipSubElements(sliderTooltipMin);
				var sliderTooltipMax = document.createElement("div");
				sliderTooltipMax.className = "tooltip tooltip-max";
				sliderTooltipMax.setAttribute("role", "presentation");
				createAndAppendTooltipSubElements(sliderTooltipMax);
				this.sliderElem.appendChild(sliderTrack);
				this.sliderElem.appendChild(sliderTooltip);
				this.sliderElem.appendChild(sliderTooltipMin);
				this.sliderElem.appendChild(sliderTooltipMax);
				if (this.tickLabelContainer) {
					this.sliderElem.appendChild(this.tickLabelContainer);
				}
				if (this.ticksContainer) {
					this.sliderElem.appendChild(this.ticksContainer);
				}
				this.sliderElem.appendChild(sliderMinHandle);
				this.sliderElem.appendChild(sliderMaxHandle);
				parent.insertBefore(this.sliderElem, this.element);
				this.element.style.display = "none";
			}
			if ($) {
				this.$element = $(this.element);
				this.$sliderElem = $(this.sliderElem);
			}
			this.eventToCallbackMap = {};
			this.sliderElem.id = this.options.id;
			this.touchCapable = "ontouchstart" in window || (window.DocumentTouch && document instanceof window.DocumentTouch);
			this.touchX = 0;
			this.touchY = 0;
			this.tooltip = this.sliderElem.querySelector(".tooltip-main");
			this.tooltipInner = this.tooltip.querySelector(".tooltip-inner");
			this.tooltip_min = this.sliderElem.querySelector(".tooltip-min");
			this.tooltipInner_min = this.tooltip_min.querySelector(".tooltip-inner");
			this.tooltip_max = this.sliderElem.querySelector(".tooltip-max");
			this.tooltipInner_max = this.tooltip_max.querySelector(".tooltip-inner");
			if (SliderScale[this.options.scale]) {
				this.options.scale = SliderScale[this.options.scale];
			}
			if (updateSlider === true) {
				this._removeClass(this.sliderElem, "slider-horizontal");
				this._removeClass(this.sliderElem, "slider-vertical");
				this._removeClass(this.sliderElem, "slider-rtl");
				this._removeClass(this.tooltip, "hide");
				this._removeClass(this.tooltip_min, "hide");
				this._removeClass(this.tooltip_max, "hide");
				["left", "right", "top", "width", "height"].forEach(function (prop) {
					this._removeProperty(this.trackLow, prop);
					this._removeProperty(this.trackSelection, prop);
					this._removeProperty(this.trackHigh, prop);
				}, this);
				[this.handle1, this.handle2].forEach(function (handle) {
					this._removeProperty(handle, "left");
					this._removeProperty(handle, "right");
					this._removeProperty(handle, "top");
				}, this);
				[this.tooltip, this.tooltip_min, this.tooltip_max].forEach(function (tooltip) {
					this._removeProperty(tooltip, "left");
					this._removeProperty(tooltip, "right");
					this._removeProperty(tooltip, "top");
					this._removeClass(tooltip, "right");
					this._removeClass(tooltip, "left");
					this._removeClass(tooltip, "top");
				}, this);
			}
			if (this.options.orientation === "vertical") {
				this._addClass(this.sliderElem, "slider-vertical");
				this.stylePos = "top";
				this.mousePos = "pageY";
				this.sizePos = "offsetHeight";
			} else {
				this._addClass(this.sliderElem, "slider-horizontal");
				this.sliderElem.style.width = origWidth;
				this.options.orientation = "horizontal";
				if (this.options.rtl) {
					this.stylePos = "right";
				} else {
					this.stylePos = "left";
				}
				this.mousePos = "pageX";
				this.sizePos = "offsetWidth";
			}
			if (this.options.rtl) {
				this._addClass(this.sliderElem, "slider-rtl");
			}
			this._setTooltipPosition();
			if (Array.isArray(this.options.ticks) && this.options.ticks.length > 0) {
				this.options.max = Math.max.apply(Math, this.options.ticks);
				this.options.min = Math.min.apply(Math, this.options.ticks);
			}
			if (Array.isArray(this.options.value)) {
				this.options.range = true;
				this._state.value = this.options.value;
			} else if (this.options.range) {
				this._state.value = [this.options.value, this.options.max];
			} else {
				this._state.value = this.options.value;
			}
			this.trackLow = sliderTrackLow || this.trackLow;
			this.trackSelection = sliderTrackSelection || this.trackSelection;
			this.trackHigh = sliderTrackHigh || this.trackHigh;
			if (this.options.selection === "none") {
				this._addClass(this.trackLow, "hide");
				this._addClass(this.trackSelection, "hide");
				this._addClass(this.trackHigh, "hide");
			} else if (this.options.selection === "after" || this.options.selection === "before") {
				this._removeClass(this.trackLow, "hide");
				this._removeClass(this.trackSelection, "hide");
				this._removeClass(this.trackHigh, "hide");
			}
			this.handle1 = sliderMinHandle || this.handle1;
			this.handle2 = sliderMaxHandle || this.handle2;
			if (updateSlider === true) {
				this._removeClass(this.handle1, "round triangle");
				this._removeClass(this.handle2, "round triangle hide");
				for (i = 0; i < this.ticks.length; i++) {
					this._removeClass(this.ticks[i], "round triangle hide");
				}
			}
			var availableHandleModifiers = ["round", "triangle", "custom"];
			var isValidHandleType = availableHandleModifiers.indexOf(this.options.handle) !== -1;
			if (isValidHandleType) {
				this._addClass(this.handle1, this.options.handle);
				this._addClass(this.handle2, this.options.handle);
				for (i = 0; i < this.ticks.length; i++) {
					this._addClass(this.ticks[i], this.options.handle);
				}
			}
			this._state.offset = this._offset(this.sliderElem);
			this._state.size = this.sliderElem[this.sizePos];
			this.setValue(this._state.value);
			this.handle1Keydown = this._keydown.bind(this, 0);
			this.handle1.addEventListener("keydown", this.handle1Keydown, false);
			this.handle2Keydown = this._keydown.bind(this, 1);
			this.handle2.addEventListener("keydown", this.handle2Keydown, false);
			this.mousedown = this._mousedown.bind(this);
			this.touchstart = this._touchstart.bind(this);
			this.touchmove = this._touchmove.bind(this);
			if (this.touchCapable) {
				var supportsPassive = false;
				try {
					var opts = Object.defineProperty({}, "passive", {
						get: function get() {
							supportsPassive = true;
						},
					});
					window.addEventListener("test", null, opts);
				} catch (e) {}
				var eventOptions = supportsPassive ? { passive: true } : false;
				this.sliderElem.addEventListener("touchstart", this.touchstart, eventOptions);
				this.sliderElem.addEventListener("touchmove", this.touchmove, eventOptions);
			}
			this.sliderElem.addEventListener("mousedown", this.mousedown, false);
			this.resize = this._resize.bind(this);
			window.addEventListener("resize", this.resize, false);
			if (this.options.tooltip === "hide") {
				this._addClass(this.tooltip, "hide");
				this._addClass(this.tooltip_min, "hide");
				this._addClass(this.tooltip_max, "hide");
			} else if (this.options.tooltip === "always") {
				this._showTooltip();
				this._alwaysShowTooltip = true;
			} else {
				this.showTooltip = this._showTooltip.bind(this);
				this.hideTooltip = this._hideTooltip.bind(this);
				if (this.options.ticks_tooltip) {
					var callbackHandle = this._addTickListener();
					var mouseEnter = callbackHandle.addMouseEnter(this, this.handle1);
					var mouseLeave = callbackHandle.addMouseLeave(this, this.handle1);
					this.handleCallbackMap.handle1 = {
						mouseEnter: mouseEnter,
						mouseLeave: mouseLeave,
					};
					mouseEnter = callbackHandle.addMouseEnter(this, this.handle2);
					mouseLeave = callbackHandle.addMouseLeave(this, this.handle2);
					this.handleCallbackMap.handle2 = {
						mouseEnter: mouseEnter,
						mouseLeave: mouseLeave,
					};
				} else {
					this.sliderElem.addEventListener("mouseenter", this.showTooltip, false);
					this.sliderElem.addEventListener("mouseleave", this.hideTooltip, false);
				}
				this.handle1.addEventListener("focus", this.showTooltip, false);
				this.handle1.addEventListener("blur", this.hideTooltip, false);
				this.handle2.addEventListener("focus", this.showTooltip, false);
				this.handle2.addEventListener("blur", this.hideTooltip, false);
			}
			if (this.options.enabled) {
				this.enable();
			} else {
				this.disable();
			}
		}
		Slider.prototype = {
			_init: function _init() {},
			constructor: Slider,
			defaultOptions: {
				id: "",
				min: 0,
				max: 10,
				step: 1,
				precision: 0,
				orientation: "horizontal",
				value: 5,
				range: false,
				selection: "before",
				tooltip: "show",
				tooltip_split: false,
				handle: "round",
				reversed: false,
				rtl: "auto",
				enabled: true,
				formatter: function formatter(val) {
					if (Array.isArray(val)) {
						return val[0] + " : " + val[1];
					} else {
						return val;
					}
				},
				natural_arrow_keys: false,
				ticks: [],
				ticks_positions: [],
				ticks_labels: [],
				ticks_snap_bounds: 0,
				ticks_tooltip: false,
				scale: "linear",
				focus: false,
				tooltip_position: null,
				labelledby: null,
				rangeHighlights: [],
			},
			getElement: function getElement() {
				return this.sliderElem;
			},
			getValue: function getValue() {
				if (this.options.range) {
					return this._state.value;
				} else {
					return this._state.value[0];
				}
			},
			setValue: function setValue(val, triggerSlideEvent, triggerChangeEvent) {
				if (!val) {
					val = 0;
				}
				var oldValue = this.getValue();
				this._state.value = this._validateInputValue(val);
				var applyPrecision = this._applyPrecision.bind(this);
				if (this.options.range) {
					this._state.value[0] = applyPrecision(this._state.value[0]);
					this._state.value[1] = applyPrecision(this._state.value[1]);
					this._state.value[0] = Math.max(this.options.min, Math.min(this.options.max, this._state.value[0]));
					this._state.value[1] = Math.max(this.options.min, Math.min(this.options.max, this._state.value[1]));
				} else {
					this._state.value = applyPrecision(this._state.value);
					this._state.value = [Math.max(this.options.min, Math.min(this.options.max, this._state.value))];
					this._addClass(this.handle2, "hide");
					if (this.options.selection === "after") {
						this._state.value[1] = this.options.max;
					} else {
						this._state.value[1] = this.options.min;
					}
				}
				if (this.options.max > this.options.min) {
					this._state.percentage = [this._toPercentage(this._state.value[0]), this._toPercentage(this._state.value[1]), (this.options.step * 100) / (this.options.max - this.options.min)];
				} else {
					this._state.percentage = [0, 0, 100];
				}
				this._layout();
				var newValue = this.options.range ? this._state.value : this._state.value[0];
				this._setDataVal(newValue);
				if (triggerSlideEvent === true) {
					this._trigger("slide", newValue);
				}
				if (oldValue !== newValue && triggerChangeEvent === true) {
					this._trigger("change", { oldValue: oldValue, newValue: newValue });
				}
				return this;
			},
			destroy: function destroy() {
				this._removeSliderEventHandlers();
				this.sliderElem.parentNode.removeChild(this.sliderElem);
				this.element.style.display = "";
				this._cleanUpEventCallbacksMap();
				this.element.removeAttribute("data");
				if ($) {
					this._unbindJQueryEventHandlers();
					this.$element.removeData("slider");
				}
			},
			disable: function disable() {
				this._state.enabled = false;
				this.handle1.removeAttribute("tabindex");
				this.handle2.removeAttribute("tabindex");
				this._addClass(this.sliderElem, "slider-disabled");
				this._trigger("slideDisabled");
				return this;
			},
			enable: function enable() {
				this._state.enabled = true;
				this.handle1.setAttribute("tabindex", 0);
				this.handle2.setAttribute("tabindex", 0);
				this._removeClass(this.sliderElem, "slider-disabled");
				this._trigger("slideEnabled");
				return this;
			},
			toggle: function toggle() {
				if (this._state.enabled) {
					this.disable();
				} else {
					this.enable();
				}
				return this;
			},
			isEnabled: function isEnabled() {
				return this._state.enabled;
			},
			on: function on(evt, callback) {
				this._bindNonQueryEventHandler(evt, callback);
				return this;
			},
			off: function off(evt, callback) {
				if ($) {
					this.$element.off(evt, callback);
					this.$sliderElem.off(evt, callback);
				} else {
					this._unbindNonQueryEventHandler(evt, callback);
				}
			},
			getAttribute: function getAttribute(attribute) {
				if (attribute) {
					return this.options[attribute];
				} else {
					return this.options;
				}
			},
			setAttribute: function setAttribute(attribute, value) {
				this.options[attribute] = value;
				return this;
			},
			refresh: function refresh() {
				this._removeSliderEventHandlers();
				createNewSlider.call(this, this.element, this.options);
				if ($) {
					$.data(this.element, "slider", this);
				}
				return this;
			},
			relayout: function relayout() {
				this._resize();
				this._layout();
				return this;
			},
			_removeSliderEventHandlers: function _removeSliderEventHandlers() {
				this.handle1.removeEventListener("keydown", this.handle1Keydown, false);
				this.handle2.removeEventListener("keydown", this.handle2Keydown, false);
				if (this.options.ticks_tooltip) {
					var ticks = this.ticksContainer.getElementsByClassName("slider-tick");
					for (var i = 0; i < ticks.length; i++) {
						ticks[i].removeEventListener("mouseenter", this.ticksCallbackMap[i].mouseEnter, false);
						ticks[i].removeEventListener("mouseleave", this.ticksCallbackMap[i].mouseLeave, false);
					}
					this.handle1.removeEventListener("mouseenter", this.handleCallbackMap.handle1.mouseEnter, false);
					this.handle2.removeEventListener("mouseenter", this.handleCallbackMap.handle2.mouseEnter, false);
					this.handle1.removeEventListener("mouseleave", this.handleCallbackMap.handle1.mouseLeave, false);
					this.handle2.removeEventListener("mouseleave", this.handleCallbackMap.handle2.mouseLeave, false);
				}
				this.handleCallbackMap = null;
				this.ticksCallbackMap = null;
				if (this.showTooltip) {
					this.handle1.removeEventListener("focus", this.showTooltip, false);
					this.handle2.removeEventListener("focus", this.showTooltip, false);
				}
				if (this.hideTooltip) {
					this.handle1.removeEventListener("blur", this.hideTooltip, false);
					this.handle2.removeEventListener("blur", this.hideTooltip, false);
				}
				if (this.showTooltip) {
					this.sliderElem.removeEventListener("mouseenter", this.showTooltip, false);
				}
				if (this.hideTooltip) {
					this.sliderElem.removeEventListener("mouseleave", this.hideTooltip, false);
				}
				this.sliderElem.removeEventListener("touchstart", this.touchstart, false);
				this.sliderElem.removeEventListener("touchmove", this.touchmove, false);
				this.sliderElem.removeEventListener("mousedown", this.mousedown, false);
				window.removeEventListener("resize", this.resize, false);
			},
			_bindNonQueryEventHandler: function _bindNonQueryEventHandler(evt, callback) {
				if (this.eventToCallbackMap[evt] === undefined) {
					this.eventToCallbackMap[evt] = [];
				}
				this.eventToCallbackMap[evt].push(callback);
			},
			_unbindNonQueryEventHandler: function _unbindNonQueryEventHandler(evt, callback) {
				var callbacks = this.eventToCallbackMap[evt];
				if (callbacks !== undefined) {
					for (var i = 0; i < callbacks.length; i++) {
						if (callbacks[i] === callback) {
							callbacks.splice(i, 1);
							break;
						}
					}
				}
			},
			_cleanUpEventCallbacksMap: function _cleanUpEventCallbacksMap() {
				var eventNames = Object.keys(this.eventToCallbackMap);
				for (var i = 0; i < eventNames.length; i++) {
					var eventName = eventNames[i];
					delete this.eventToCallbackMap[eventName];
				}
			},
			_showTooltip: function _showTooltip() {
				if (this.options.tooltip_split === false) {
					this._addClass(this.tooltip, "in");
					this.tooltip_min.style.display = "none";
					this.tooltip_max.style.display = "none";
				} else {
					this._addClass(this.tooltip_min, "in");
					this._addClass(this.tooltip_max, "in");
					this.tooltip.style.display = "none";
				}
				this._state.over = true;
			},
			_hideTooltip: function _hideTooltip() {
				if (this._state.inDrag === false && this.alwaysShowTooltip !== true) {
					this._removeClass(this.tooltip, "in");
					this._removeClass(this.tooltip_min, "in");
					this._removeClass(this.tooltip_max, "in");
				}
				this._state.over = false;
			},
			_setToolTipOnMouseOver: function _setToolTipOnMouseOver(tempState) {
				var formattedTooltipVal = this.options.formatter(!tempState ? this._state.value[0] : tempState.value[0]);
				var positionPercentages = !tempState ? getPositionPercentages(this._state, this.options.reversed) : getPositionPercentages(tempState, this.options.reversed);
				this._setText(this.tooltipInner, formattedTooltipVal);
				this.tooltip.style[this.stylePos] = positionPercentages[0] + "%";
				function getPositionPercentages(state, reversed) {
					if (reversed) {
						return [100 - state.percentage[0], this.options.range ? 100 - state.percentage[1] : state.percentage[1]];
					}
					return [state.percentage[0], state.percentage[1]];
				}
			},
			_addTickListener: function _addTickListener() {
				return {
					addMouseEnter: function addMouseEnter(reference, tick, index) {
						var enter = function enter() {
							var tempState = reference._state;
							var idString = index >= 0 ? index : this.attributes["aria-valuenow"].value;
							var hoverIndex = parseInt(idString, 10);
							tempState.value[0] = hoverIndex;
							tempState.percentage[0] = reference.options.ticks_positions[hoverIndex];
							reference._setToolTipOnMouseOver(tempState);
							reference._showTooltip();
						};
						tick.addEventListener("mouseenter", enter, false);
						return enter;
					},
					addMouseLeave: function addMouseLeave(reference, tick) {
						var leave = function leave() {
							reference._hideTooltip();
						};
						tick.addEventListener("mouseleave", leave, false);
						return leave;
					},
				};
			},
			_layout: function _layout() {
				var positionPercentages;
				if (this.options.reversed) {
					positionPercentages = [100 - this._state.percentage[0], this.options.range ? 100 - this._state.percentage[1] : this._state.percentage[1]];
				} else {
					positionPercentages = [this._state.percentage[0], this._state.percentage[1]];
				}
				this.handle1.style[this.stylePos] = positionPercentages[0] + "%";
				this.handle1.setAttribute("aria-valuenow", this._state.value[0]);
				if (isNaN(this.options.formatter(this._state.value[0]))) {
					this.handle1.setAttribute("aria-valuetext", this.options.formatter(this._state.value[0]));
				}
				this.handle2.style[this.stylePos] = positionPercentages[1] + "%";
				this.handle2.setAttribute("aria-valuenow", this._state.value[1]);
				if (isNaN(this.options.formatter(this._state.value[1]))) {
					this.handle2.setAttribute("aria-valuetext", this.options.formatter(this._state.value[1]));
				}
				if (this.rangeHighlightElements.length > 0 && Array.isArray(this.options.rangeHighlights) && this.options.rangeHighlights.length > 0) {
					for (var _i = 0; _i < this.options.rangeHighlights.length; _i++) {
						var startPercent = this._toPercentage(this.options.rangeHighlights[_i].start);
						var endPercent = this._toPercentage(this.options.rangeHighlights[_i].end);
						if (this.options.reversed) {
							var sp = 100 - endPercent;
							endPercent = 100 - startPercent;
							startPercent = sp;
						}
						var currentRange = this._createHighlightRange(startPercent, endPercent);
						if (currentRange) {
							if (this.options.orientation === "vertical") {
								this.rangeHighlightElements[_i].style.top = currentRange.start + "%";
								this.rangeHighlightElements[_i].style.height = currentRange.size + "%";
							} else {
								if (this.options.rtl) {
									this.rangeHighlightElements[_i].style.right = currentRange.start + "%";
								} else {
									this.rangeHighlightElements[_i].style.left = currentRange.start + "%";
								}
								this.rangeHighlightElements[_i].style.width = currentRange.size + "%";
							}
						} else {
							this.rangeHighlightElements[_i].style.display = "none";
						}
					}
				}
				if (Array.isArray(this.options.ticks) && this.options.ticks.length > 0) {
					var styleSize = this.options.orientation === "vertical" ? "height" : "width";
					var styleMargin;
					if (this.options.orientation === "vertical") {
						styleMargin = "marginTop";
					} else {
						if (this.options.rtl) {
							styleMargin = "marginRight";
						} else {
							styleMargin = "marginLeft";
						}
					}
					var labelSize = this._state.size / (this.options.ticks.length - 1);
					if (this.tickLabelContainer) {
						var extraMargin = 0;
						if (this.options.ticks_positions.length === 0) {
							if (this.options.orientation !== "vertical") {
								this.tickLabelContainer.style[styleMargin] = -labelSize / 2 + "px";
							}
							extraMargin = this.tickLabelContainer.offsetHeight;
						} else {
							for (i = 0; i < this.tickLabelContainer.childNodes.length; i++) {
								if (this.tickLabelContainer.childNodes[i].offsetHeight > extraMargin) {
									extraMargin = this.tickLabelContainer.childNodes[i].offsetHeight;
								}
							}
						}
						if (this.options.orientation === "horizontal") {
							this.sliderElem.style.marginBottom = extraMargin + "px";
						}
					}
					for (var i = 0; i < this.options.ticks.length; i++) {
						var percentage = this.options.ticks_positions[i] || this._toPercentage(this.options.ticks[i]);
						if (this.options.reversed) {
							percentage = 100 - percentage;
						}
						this.ticks[i].style[this.stylePos] = percentage + "%";
						this._removeClass(this.ticks[i], "in-selection");
						if (!this.options.range) {
							if (this.options.selection === "after" && percentage >= positionPercentages[0]) {
								this._addClass(this.ticks[i], "in-selection");
							} else if (this.options.selection === "before" && percentage <= positionPercentages[0]) {
								this._addClass(this.ticks[i], "in-selection");
							}
						} else if (percentage >= positionPercentages[0] && percentage <= positionPercentages[1]) {
							this._addClass(this.ticks[i], "in-selection");
						}
						if (this.tickLabels[i]) {
							this.tickLabels[i].style[styleSize] = labelSize + "px";
							if (this.options.orientation !== "vertical" && this.options.ticks_positions[i] !== undefined) {
								this.tickLabels[i].style.position = "absolute";
								this.tickLabels[i].style[this.stylePos] = percentage + "%";
								this.tickLabels[i].style[styleMargin] = -labelSize / 2 + "px";
							} else if (this.options.orientation === "vertical") {
								if (this.options.rtl) {
									this.tickLabels[i].style["marginRight"] = this.sliderElem.offsetWidth + "px";
								} else {
									this.tickLabels[i].style["marginLeft"] = this.sliderElem.offsetWidth + "px";
								}
								this.tickLabelContainer.style[styleMargin] = (this.sliderElem.offsetWidth / 2) * -1 + "px";
							}
						}
					}
				}
				var formattedTooltipVal;
				if (this.options.range) {
					formattedTooltipVal = this.options.formatter(this._state.value);
					this._setText(this.tooltipInner, formattedTooltipVal);
					this.tooltip.style[this.stylePos] = (positionPercentages[1] + positionPercentages[0]) / 2 + "%";
					var innerTooltipMinText = this.options.formatter(this._state.value[0]);
					this._setText(this.tooltipInner_min, innerTooltipMinText);
					var innerTooltipMaxText = this.options.formatter(this._state.value[1]);
					this._setText(this.tooltipInner_max, innerTooltipMaxText);
					this.tooltip_min.style[this.stylePos] = positionPercentages[0] + "%";
					this.tooltip_max.style[this.stylePos] = positionPercentages[1] + "%";
				} else {
					formattedTooltipVal = this.options.formatter(this._state.value[0]);
					this._setText(this.tooltipInner, formattedTooltipVal);
					this.tooltip.style[this.stylePos] = positionPercentages[0] + "%";
				}
				if (this.options.orientation === "vertical") {
					this.trackLow.style.top = "0";
					this.trackLow.style.height = Math.min(positionPercentages[0], positionPercentages[1]) + "%";
					this.trackSelection.style.top = Math.min(positionPercentages[0], positionPercentages[1]) + "%";
					this.trackSelection.style.height = Math.abs(positionPercentages[0] - positionPercentages[1]) + "%";
					this.trackHigh.style.bottom = "0";
					this.trackHigh.style.height = 100 - Math.min(positionPercentages[0], positionPercentages[1]) - Math.abs(positionPercentages[0] - positionPercentages[1]) + "%";
				} else {
					if (this.stylePos === "right") {
						this.trackLow.style.right = "0";
					} else {
						this.trackLow.style.left = "0";
					}
					this.trackLow.style.width = Math.min(positionPercentages[0], positionPercentages[1]) + "%";
					if (this.stylePos === "right") {
						this.trackSelection.style.right = Math.min(positionPercentages[0], positionPercentages[1]) + "%";
					} else {
						this.trackSelection.style.left = Math.min(positionPercentages[0], positionPercentages[1]) + "%";
					}
					this.trackSelection.style.width = Math.abs(positionPercentages[0] - positionPercentages[1]) + "%";
					if (this.stylePos === "right") {
						this.trackHigh.style.left = "0";
					} else {
						this.trackHigh.style.right = "0";
					}
					this.trackHigh.style.width = 100 - Math.min(positionPercentages[0], positionPercentages[1]) - Math.abs(positionPercentages[0] - positionPercentages[1]) + "%";
					var offset_min = this.tooltip_min.getBoundingClientRect();
					var offset_max = this.tooltip_max.getBoundingClientRect();
					if (this.options.tooltip_position === "bottom") {
						if (offset_min.right > offset_max.left) {
							this._removeClass(this.tooltip_max, "bottom");
							this._addClass(this.tooltip_max, "top");
							this.tooltip_max.style.top = "";
							this.tooltip_max.style.bottom = 22 + "px";
						} else {
							this._removeClass(this.tooltip_max, "top");
							this._addClass(this.tooltip_max, "bottom");
							this.tooltip_max.style.top = this.tooltip_min.style.top;
							this.tooltip_max.style.bottom = "";
						}
					} else {
						if (offset_min.right > offset_max.left) {
							this._removeClass(this.tooltip_max, "top");
							this._addClass(this.tooltip_max, "bottom");
							this.tooltip_max.style.top = 18 + "px";
						} else {
							this._removeClass(this.tooltip_max, "bottom");
							this._addClass(this.tooltip_max, "top");
							this.tooltip_max.style.top = this.tooltip_min.style.top;
						}
					}
				}
			},
			_createHighlightRange: function _createHighlightRange(start, end) {
				if (this._isHighlightRange(start, end)) {
					if (start > end) {
						return { start: end, size: start - end };
					}
					return { start: start, size: end - start };
				}
				return null;
			},
			_isHighlightRange: function _isHighlightRange(start, end) {
				if (0 <= start && start <= 100 && 0 <= end && end <= 100) {
					return true;
				} else {
					return false;
				}
			},
			_resize: function _resize(ev) {
				this._state.offset = this._offset(this.sliderElem);
				this._state.size = this.sliderElem[this.sizePos];
				this._layout();
			},
			_removeProperty: function _removeProperty(element, prop) {
				if (element.style.removeProperty) {
					element.style.removeProperty(prop);
				} else {
					element.style.removeAttribute(prop);
				}
			},
			_mousedown: function _mousedown(ev) {
				if (!this._state.enabled) {
					return false;
				}
				this._state.offset = this._offset(this.sliderElem);
				this._state.size = this.sliderElem[this.sizePos];
				var percentage = this._getPercentage(ev);
				if (this.options.range) {
					var diff1 = Math.abs(this._state.percentage[0] - percentage);
					var diff2 = Math.abs(this._state.percentage[1] - percentage);
					this._state.dragged = diff1 < diff2 ? 0 : 1;
					this._adjustPercentageForRangeSliders(percentage);
				} else {
					this._state.dragged = 0;
				}
				this._state.percentage[this._state.dragged] = percentage;
				this._layout();
				if (this.touchCapable) {
					document.removeEventListener("touchmove", this.mousemove, false);
					document.removeEventListener("touchend", this.mouseup, false);
				}
				if (this.mousemove) {
					document.removeEventListener("mousemove", this.mousemove, false);
				}
				if (this.mouseup) {
					document.removeEventListener("mouseup", this.mouseup, false);
				}
				this.mousemove = this._mousemove.bind(this);
				this.mouseup = this._mouseup.bind(this);
				if (this.touchCapable) {
					document.addEventListener("touchmove", this.mousemove, false);
					document.addEventListener("touchend", this.mouseup, false);
				}
				document.addEventListener("mousemove", this.mousemove, false);
				document.addEventListener("mouseup", this.mouseup, false);
				this._state.inDrag = true;
				var newValue = this._calculateValue();
				this._trigger("slideStart", newValue);
				this._setDataVal(newValue);
				this.setValue(newValue, false, true);
				ev.returnValue = false;
				if (this.options.focus) {
					this._triggerFocusOnHandle(this._state.dragged);
				}
				return true;
			},
			_touchstart: function _touchstart(ev) {
				if (ev.changedTouches === undefined) {
					this._mousedown(ev);
					return;
				}
				var touch = ev.changedTouches[0];
				this.touchX = touch.pageX;
				this.touchY = touch.pageY;
			},
			_triggerFocusOnHandle: function _triggerFocusOnHandle(handleIdx) {
				if (handleIdx === 0) {
					this.handle1.focus();
				}
				if (handleIdx === 1) {
					this.handle2.focus();
				}
			},
			_keydown: function _keydown(handleIdx, ev) {
				if (!this._state.enabled) {
					return false;
				}
				var dir;
				switch (ev.keyCode) {
					case 37:
					case 40:
						dir = -1;
						break;
					case 39:
					case 38:
						dir = 1;
						break;
				}
				if (!dir) {
					return;
				}
				if (this.options.natural_arrow_keys) {
					var ifVerticalAndNotReversed = this.options.orientation === "vertical" && !this.options.reversed;
					var ifHorizontalAndReversed = this.options.orientation === "horizontal" && this.options.reversed;
					if (ifVerticalAndNotReversed || ifHorizontalAndReversed) {
						dir = -dir;
					}
				}
				var val = this._state.value[handleIdx] + dir * this.options.step;
				var percentage = (val / this.options.max) * 100;
				this._state.keyCtrl = handleIdx;
				if (this.options.range) {
					this._adjustPercentageForRangeSliders(percentage);
					var val1 = !this._state.keyCtrl ? val : this._state.value[0];
					var val2 = this._state.keyCtrl ? val : this._state.value[1];
					val = [val1, val2];
				}
				this._trigger("slideStart", val);
				this._setDataVal(val);
				this.setValue(val, true, true);
				this._setDataVal(val);
				this._trigger("slideStop", val);
				this._layout();
				this._pauseEvent(ev);
				delete this._state.keyCtrl;
				return false;
			},
			_pauseEvent: function _pauseEvent(ev) {
				if (ev.stopPropagation) {
					ev.stopPropagation();
				}
				if (ev.preventDefault) {
					ev.preventDefault();
				}
				ev.cancelBubble = true;
				ev.returnValue = false;
			},
			_mousemove: function _mousemove(ev) {
				if (!this._state.enabled) {
					return false;
				}
				var percentage = this._getPercentage(ev);
				this._adjustPercentageForRangeSliders(percentage);
				this._state.percentage[this._state.dragged] = percentage;
				this._layout();
				var val = this._calculateValue(true);
				this.setValue(val, true, true);
				return false;
			},
			_touchmove: function _touchmove(ev) {
				if (ev.changedTouches === undefined) {
					return;
				}
				var touch = ev.changedTouches[0];
				var xDiff = touch.pageX - this.touchX;
				var yDiff = touch.pageY - this.touchY;
				if (!this._state.inDrag) {
					if (this.options.orientation === "vertical" && xDiff <= 5 && xDiff >= -5 && (yDiff >= 15 || yDiff <= -15)) {
						this._mousedown(ev);
					} else if (yDiff <= 5 && yDiff >= -5 && (xDiff >= 15 || xDiff <= -15)) {
						this._mousedown(ev);
					}
				}
			},
			_adjustPercentageForRangeSliders: function _adjustPercentageForRangeSliders(percentage) {
				if (this.options.range) {
					var precision = this._getNumDigitsAfterDecimalPlace(percentage);
					precision = precision ? precision - 1 : 0;
					var percentageWithAdjustedPrecision = this._applyToFixedAndParseFloat(percentage, precision);
					if (this._state.dragged === 0 && this._applyToFixedAndParseFloat(this._state.percentage[1], precision) < percentageWithAdjustedPrecision) {
						this._state.percentage[0] = this._state.percentage[1];
						this._state.dragged = 1;
					} else if (this._state.dragged === 1 && this._applyToFixedAndParseFloat(this._state.percentage[0], precision) > percentageWithAdjustedPrecision) {
						this._state.percentage[1] = this._state.percentage[0];
						this._state.dragged = 0;
					} else if (this._state.keyCtrl === 0 && (this._state.value[1] / this.options.max) * 100 < percentage) {
						this._state.percentage[0] = this._state.percentage[1];
						this._state.keyCtrl = 1;
						this.handle2.focus();
					} else if (this._state.keyCtrl === 1 && (this._state.value[0] / this.options.max) * 100 > percentage) {
						this._state.percentage[1] = this._state.percentage[0];
						this._state.keyCtrl = 0;
						this.handle1.focus();
					}
				}
			},
			_mouseup: function _mouseup() {
				if (!this._state.enabled) {
					return false;
				}
				if (this.touchCapable) {
					document.removeEventListener("touchmove", this.mousemove, false);
					document.removeEventListener("touchend", this.mouseup, false);
				}
				document.removeEventListener("mousemove", this.mousemove, false);
				document.removeEventListener("mouseup", this.mouseup, false);
				this._state.inDrag = false;
				if (this._state.over === false) {
					this._hideTooltip();
				}
				var val = this._calculateValue(true);
				this._layout();
				this._setDataVal(val);
				this._trigger("slideStop", val);
				return false;
			},
			_calculateValue: function _calculateValue(snapToClosestTick) {
				var val;
				if (this.options.range) {
					val = [this.options.min, this.options.max];
					if (this._state.percentage[0] !== 0) {
						val[0] = this._toValue(this._state.percentage[0]);
						val[0] = this._applyPrecision(val[0]);
					}
					if (this._state.percentage[1] !== 100) {
						val[1] = this._toValue(this._state.percentage[1]);
						val[1] = this._applyPrecision(val[1]);
					}
				} else {
					val = this._toValue(this._state.percentage[0]);
					val = parseFloat(val);
					val = this._applyPrecision(val);
				}
				if (snapToClosestTick) {
					var min = [val, Infinity];
					for (var i = 0; i < this.options.ticks.length; i++) {
						var diff = Math.abs(this.options.ticks[i] - val);
						if (diff <= min[1]) {
							min = [this.options.ticks[i], diff];
						}
					}
					if (min[1] <= this.options.ticks_snap_bounds) {
						return min[0];
					}
				}
				return val;
			},
			_applyPrecision: function _applyPrecision(val) {
				var precision = this.options.precision || this._getNumDigitsAfterDecimalPlace(this.options.step);
				return this._applyToFixedAndParseFloat(val, precision);
			},
			_getNumDigitsAfterDecimalPlace: function _getNumDigitsAfterDecimalPlace(num) {
				var match = ("" + num).match(/(?:\.(\d+))?(?:[eE]([+-]?\d+))?$/);
				if (!match) {
					return 0;
				}
				return Math.max(0, (match[1] ? match[1].length : 0) - (match[2] ? +match[2] : 0));
			},
			_applyToFixedAndParseFloat: function _applyToFixedAndParseFloat(num, toFixedInput) {
				var truncatedNum = num.toFixed(toFixedInput);
				return parseFloat(truncatedNum);
			},
			_getPercentage: function _getPercentage(ev) {
				if (this.touchCapable && (ev.type === "touchstart" || ev.type === "touchmove")) {
					ev = ev.touches[0];
				}
				var eventPosition = ev[this.mousePos];
				var sliderOffset = this._state.offset[this.stylePos];
				var distanceToSlide = eventPosition - sliderOffset;
				if (this.stylePos === "right") {
					distanceToSlide = -distanceToSlide;
				}
				var percentage = (distanceToSlide / this._state.size) * 100;
				percentage = Math.round(percentage / this._state.percentage[2]) * this._state.percentage[2];
				if (this.options.reversed) {
					percentage = 100 - percentage;
				}
				return Math.max(0, Math.min(100, percentage));
			},
			_validateInputValue: function _validateInputValue(val) {
				if (!isNaN(+val)) {
					return +val;
				} else if (Array.isArray(val)) {
					this._validateArray(val);
					return val;
				} else {
					throw new Error(ErrorMsgs.formatInvalidInputErrorMsg(val));
				}
			},
			_validateArray: function _validateArray(val) {
				for (var i = 0; i < val.length; i++) {
					var input = val[i];
					if (typeof input !== "number") {
						throw new Error(ErrorMsgs.formatInvalidInputErrorMsg(input));
					}
				}
			},
			_setDataVal: function _setDataVal(val) {
				this.element.setAttribute("data-value", val);
				this.element.setAttribute("value", val);
				this.element.value = val;
			},
			_trigger: function _trigger(evt, val) {
				val = val || val === 0 ? val : undefined;
				var callbackFnArray = this.eventToCallbackMap[evt];
				if (callbackFnArray && callbackFnArray.length) {
					for (var i = 0; i < callbackFnArray.length; i++) {
						var callbackFn = callbackFnArray[i];
						callbackFn(val);
					}
				}
				if ($) {
					this._triggerJQueryEvent(evt, val);
				}
			},
			_triggerJQueryEvent: function _triggerJQueryEvent(evt, val) {
				var eventData = { type: evt, value: val };
				this.$element.trigger(eventData);
				this.$sliderElem.trigger(eventData);
			},
			_unbindJQueryEventHandlers: function _unbindJQueryEventHandlers() {
				this.$element.off();
				this.$sliderElem.off();
			},
			_setText: function _setText(element, text) {
				if (typeof element.textContent !== "undefined") {
					element.textContent = text;
				} else if (typeof element.innerText !== "undefined") {
					element.innerText = text;
				}
			},
			_removeClass: function _removeClass(element, classString) {
				var classes = classString.split(" ");
				var newClasses = element.className;
				for (var i = 0; i < classes.length; i++) {
					var classTag = classes[i];
					var regex = new RegExp("(?:\\s|^)" + classTag + "(?:\\s|$)");
					newClasses = newClasses.replace(regex, " ");
				}
				element.className = newClasses.trim();
			},
			_addClass: function _addClass(element, classString) {
				var classes = classString.split(" ");
				var newClasses = element.className;
				for (var i = 0; i < classes.length; i++) {
					var classTag = classes[i];
					var regex = new RegExp("(?:\\s|^)" + classTag + "(?:\\s|$)");
					var ifClassExists = regex.test(newClasses);
					if (!ifClassExists) {
						newClasses += " " + classTag;
					}
				}
				element.className = newClasses.trim();
			},
			_offsetLeft: function _offsetLeft(obj) {
				return obj.getBoundingClientRect().left;
			},
			_offsetRight: function _offsetRight(obj) {
				return obj.getBoundingClientRect().right;
			},
			_offsetTop: function _offsetTop(obj) {
				var offsetTop = obj.offsetTop;
				while ((obj = obj.offsetParent) && !isNaN(obj.offsetTop)) {
					offsetTop += obj.offsetTop;
					if (obj.tagName !== "BODY") {
						offsetTop -= obj.scrollTop;
					}
				}
				return offsetTop;
			},
			_offset: function _offset(obj) {
				return {
					left: this._offsetLeft(obj),
					right: this._offsetRight(obj),
					top: this._offsetTop(obj),
				};
			},
			_css: function _css(elementRef, styleName, value) {
				if ($) {
					$.style(elementRef, styleName, value);
				} else {
					var style = styleName.replace(/^-ms-/, "ms-").replace(/-([\da-z])/gi, function (all, letter) {
						return letter.toUpperCase();
					});
					elementRef.style[style] = value;
				}
			},
			_toValue: function _toValue(percentage) {
				return this.options.scale.toValue.apply(this, [percentage]);
			},
			_toPercentage: function _toPercentage(value) {
				return this.options.scale.toPercentage.apply(this, [value]);
			},
			_setTooltipPosition: function _setTooltipPosition() {
				var tooltips = [this.tooltip, this.tooltip_min, this.tooltip_max];
				if (this.options.orientation === "vertical") {
					var tooltipPos;
					if (this.options.tooltip_position) {
						tooltipPos = this.options.tooltip_position;
					} else {
						if (this.options.rtl) {
							tooltipPos = "left";
						} else {
							tooltipPos = "right";
						}
					}
					var oppositeSide = tooltipPos === "left" ? "right" : "left";
					tooltips.forEach(
						function (tooltip) {
							this._addClass(tooltip, tooltipPos);
							tooltip.style[oppositeSide] = "100%";
						}.bind(this)
					);
				} else if (this.options.tooltip_position === "bottom") {
					tooltips.forEach(
						function (tooltip) {
							this._addClass(tooltip, "bottom");
							tooltip.style.top = 22 + "px";
						}.bind(this)
					);
				} else {
					tooltips.forEach(
						function (tooltip) {
							this._addClass(tooltip, "top");
							tooltip.style.top = -this.tooltip.outerHeight - 14 + "px";
						}.bind(this)
					);
				}
			},
		};
		if ($ && $.fn) {
			var autoRegisterNamespace = void 0;
			if (!$.fn.slider) {
				$.bridget(NAMESPACE_MAIN, Slider);
				autoRegisterNamespace = NAMESPACE_MAIN;
			} else {
				if (windowIsDefined) {
					window.console.warn("bootstrap-slider.js - WARNING: $.fn.slider namespace is already bound. Use the $.fn.bootstrapSlider namespace instead.");
				}
				autoRegisterNamespace = NAMESPACE_ALTERNATE;
			}
			$.bridget(NAMESPACE_ALTERNATE, Slider);
			$(function () {
				$("input[data-provide=slider]")[autoRegisterNamespace]();
			});
		}
	})($);
	return Slider;
});
var $mcwp = jQuery.noConflict();
$mcwp(function ($) {
	$(document).on("click", ".mcwp-submit", function (e) {
		e.preventDefault();
		if (typeof navigator != "undefined" && navigator.webdriver) {
			alert(mcwp_ajax.calc_res);
			return false;
		}
		grecaptcha.execute().then((token) => {
			var forma = $(this).closest("form");
			$(forma).find("#g-recaptcha-response").val(token);
			sendForm(forma);
		});
	});
	function sendForm(forma) {
		var serializaFrom = $(forma).serializeArray();
		var post_data = {};
		$.each($(forma).serializeArray(), function () {
			post_data[this.name] = this.value;
		});
		console.log(post_data);
		currentFormEmail = $('input[type="email"]', forma).val();
		if (!validateEmail(currentFormEmail)) {
			alert("Your Email is not valid!");
			return false;
		}
		$.post(mcwp_ajax.ajaxurl, post_data, function (response) {
			if (response.status === "captcha_required") {
				if (forma.find('input[name="adc_1"]').length === 0) {
					forma.append('<input type="hidden" name="adc_1" value="' + response.adc_1 + '">');
				} else {
					forma.find('input[name="adc_1"]').val(response.adc_1);
				}

				if (forma.find('input[name="adc_2"]').length === 0) {
					forma.append('<input type="hidden" name="adc_2" value="' + response.adc_2 + '">');
				} else {
					forma.find('input[name="adc_2"]').val(response.adc_2);
				}

				var label = forma.find('label[for="cal1_email"]');
				if (label.length !== 0) {
					label.text(response.question).css("color", "red").css("font-weight", "bold");
				}

				if (forma.find('input[name="adc_3"]').length === 0) {
					forma.find('input[type="email"]').before('<input type="number" name="adc_3" placeholder="Your answer here.">');
				}

				forma.find('input[type="email"]').hide();
			} else {
				alert(response.message);
			}
			if (response.status === "success") {
				alert(response.message);
			}
			console.log(response);
			// alert(mcwp_ajax.calc_res);
		});
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
	$("#fha_mmi_div_span").keyup(function () {
		monthly_mortgage_insurance = $(this).val() == "" ? 0 : $(this).val() / 0;
		$(this).val(function (index, value) {
			return value.replace(/\D/g, "").replace(/\B(?=(\d{3})+(?!\d))/g, ",");
		});
		fha_mortgage_calc(fha_purchase_price, fha_down_payment, fha_interest_rate, fha_mortgage_term, fha_monthly_tax, fha_annual_tax_percent, fha_monthly_insurance, monthly_mortgage_insurance, fha_monthly_hoa);
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
		a_annual_tax_percent = $(".va_annual_tax_scrl").val();
		va_annual_tax = (price * va_annual_tax_percent) / 100;
		tax = va_annual_tax / 12;
		insurance = removeco($("#va_annual_insurance_inp").val()) / 12;
		hoa = removeco($("#va_monthly_hoa_inp").val()) == "" ? 0 : removeco($("#va_monthly_hoa_inp").val());
		var va_service = $("#va_service_type").val();
		if (va_service === "surviving_spouse" || va_service === "disabled_veteran") {
			e_rate = 0;
		} else if ($("#va_frist_time").val() == "yes") {
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
});
