=== Mortgage Calculators WP ===
Contributors: (8blocks)
Donate link: http://wordpress.org
Tags: mortgage, mortgage calculator, loan calculator, real estate, mortgage calc, responsive mortgage calculator, mortgage calculators
Requires at least: 4.6
Stable tag: 2.3.1
Tested up to: 6.4.1
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Mortgage Calculators WP provides users with a simple, elegant and responsive solution for users to calculate mortgage values.

== Description ==

<strong>Introducing Affordability and Refinance Calculators for Premium Users</strong>

Mortgage Calculators WP is a very easy-to-use wordpress plugin built with the purpose of providing mortgage and other finance professionals with a mortgage calculator that not only delivers accurate home loan estimates, but also the ability for the users to instantly receive a free copy of those estimates via email while also providing the website owner with an email lead everytime a user requests a copy of their estimated values.

You can view a live demo of our mortgage calculators as seen on our <a href="https://lenderd.com/loan-officer-website-themes/" target="_blank">loan officer website themes</a>. Simply preview any of the sites provided and scroll down to view the calculator as it would appear on your website.

== Installation ==

1. Upload the plugin folder to the `/wp-content/plugins/` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress.
3. Wordpress MU Network administrators can use the Network Settings > Calculator screen to configure the plugin. Standard WP installations have the settings available under the "Calculator" menu item.
4. Configure color, reCAPTCHA keys, and outbound email settings here from the General Settings tab.
5. Next you can configure each of the calculator's settings individually, save them and make sure to test to ensure everything is coming through as you desire.
6. Once all the settings are configured you will copy the shortcode from the top of each calculator settings screen and place it on a page of your choice to show the calculator (ex. [mcwp type="cv"])

== Frequently Asked Questions ==

= Is this plugin free forever? =

Yes you will get the conventional calculator absolutely free forever and receive our killer support and updates on a regular basis to ensure it stays compatible with the latest version of WP.

= Is the plugin available in Spanish? =

Yes. As of version 1.50 we have fully translated the plugin into Spanish for all calculators.

= Can I edit the look of the calculator? =

Yes you can. We have provided a color picker that will change the look/feel of your calculator simply by choosing a color or placing your HEX code in and saving the settings.

= How do I get the additional calculators? =

1. Please visit the <a href="https://mortgagecalculatorsplugin.com/" target="_blank">Mortgage Calculators WP</a> offical site and choose your desired license and upon completion of payment you will receive a key that you can input under the premium tab. Once activated you will receive access to the additional calculators to implement in your website.

== Screenshots ==

1. Admin Settings View

2. Sample Frontend View of the Calculator

== Upgrade Notice ==

= 2.3 =
Down payment sliders now move in 0.5% increments on all calculators. VA calculator now includes a Veteran Status dropdown — Surviving Spouse and Disabled Veteran selections automatically remove the VA funding fee.

= 2.2 =
Calculator Color now supports shortcodes, text values, and CSS color functions (rgba, rgb, hsl, hsla). GitHub releases fallback for auto-updates. Email templates use .bg and .color CSS classes.

= 2.1 =
New styled email templates, built-in reCAPTCHA keys, EDD auto-updater fix, and settings cleanup.

= 2.0 =
Major security and code quality update. reCAPTCHA keys are no longer hardcoded and must be configured via the admin settings after upgrading.

= 1.0 20180930 =
First Version! Enjoy!

== Changelog ==

= 2.3.1 20260604 =

* Fix: Removed the mislabeled "Total Monthly Payment" line from the Refinance calculator email, which was duplicating the Monthly Savings value

= 2.3 20260414 =

* New: Down payment sliders on Conventional, FHA, and VA calculators now increment in 0.5% steps for finer control
* New: VA calculator now includes a Veteran Status dropdown at the top (Regular Military, Reserves/National Guard, Surviving Spouse, Disabled Veteran)
* New: Selecting Surviving Spouse or Disabled Veteran automatically zeroes out the VA funding fee
* Improvement: First Time VA Loan field auto-hides when funding-fee-exempt veteran status is selected

= 2.2 20260320 =

* New: Calculator Color field now accepts text values and shortcodes (e.g. [brand_color]) in addition to hex colors
* New: Color picker syncs to text field but no longer restricts input
* New: Centralized mcwp_get_color() helper resolves shortcodes, validates values, and provides fallback
* New: Color field supports rgba(), rgb(), hsl(), and hsla() CSS functions (e.g. rgba([brand_color],1))
* New: GitHub releases as fallback download source — auto-updates work even without EDD license
* New: Added .bg class to all background-color elements in email templates
* New: Added .color class to all branded color elements in email templates
* Improvement: All color values in emails escaped with esc_attr() for security

= 2.1 20260320 =

* New: Modern styled email templates with card-based layout using Calculator Color from settings
* New: Email templates pull Message from General Settings and Disclaimer from each calculator's settings
* New: Admin CC email shows lead info with embedded copy of the lead's email
* New: Reply-To headers set dynamically — lead emails reply to site owner, CC emails reply to lead
* New: Built-in reCAPTCHA v3 keys as defaults — plugin works out of the box without configuration
* New: Affordability email highlights "You Can Afford Up To" as the primary value
* New: Refinance email highlights "Monthly Savings" as the primary value
* Fix: EDD auto-updater — hooked missing http_request_args filter for SSL during downloads
* Fix: EDD auto-updater — added download_link to package mapping for WordPress update mechanism
* Fix: EDD auto-updater — corrected broken strpos comparison for package download URL detection
* Fix: Backslash accumulation on apostrophes when saving settings (stripslashes on POST data)
* Fix: Escaped apostrophe in network options error message
* Improvement: Replaced deprecated $new_whitelist_options in network options handler
* Removed: Message Body fields from individual calculator settings (replaced by new email template system)

= 2.0 20260319 =

* Security: Fixed XSS vulnerability in calculator color output
* Security: Removed hardcoded reCAPTCHA keys — now configurable via admin settings
* Security: Added CSRF protection (nonce verification) on email AJAX handler
* Security: Sanitized calculator type input against allowlist
* Security: Fixed email header injection by stripping newlines and sanitizing email addresses
* Security: Fixed IP spoofing — only REMOTE_ADDR is used now
* Security: Escaped all admin form field outputs with esc_attr()/esc_html()
* Fix: Version mismatch between plugin header and MC_VERSION constant
* Fix: "Mortage" typo corrected to "Mortgage" in admin menu
* Fix: Escaped apostrophe in error message ("don\t" → "don't")
* Fix: Initialized undefined $body_part_dynamic variable
* Fix: Changed require_once to include for calculator views, allowing multiple shortcodes per page
* Improvement: Replaced deprecated $new_whitelist_options with $new_allowed_options (WP 5.5+)
* Improvement: Admin CSS/JS now only loads on the plugin settings page
* Improvement: settings-updated check wrapped in is_admin() to avoid frontend execution
* Improvement: jQuery Migrate removal limited to frontend only to avoid breaking other plugins

= 1.68 20231207 =

* Ajax fix

= 1.67 20231127 =

* Fixed auto-update issue on Wordpress MU

= 1.66 20230824 =

* Email bug fixed along with MI hidden fix

= 1.65 20230428 =

* VA & FHA fees updated, added upfront MIP 

= 1.64 20230223 =

* VA fees updated

= 1.63 20230206 =

* PMI fix

= 1.62 20230201 =

* Annual tax fix

= 1.61 20230105 =

* Child site settings issue fix

= 1.60 20230105 =

* Aria ADA label fixes

= 1.59 20220912 =

* Firefox results fix

= 1.58 20220827 =

* Mountly PMI and Insurance Rate added

= 1.57 20220729 =

* Bot protection performance fixed

= 1.56 20220729 =

* Security improvements have been made. Added bot protection.

= 1.50 2020019 =

* Added new refi and affordability calculators along with an overall code cleanup and consolidation of functions and Spanish translation

= 1.45 2020107 =

* Fix FHA field interest rate field

= 1.44 20191210 =

* Field tweaks and subsite(Wordpress MU) ajax errors.

= 1.40 20191210 =

* Add Accessibility Features

= 1.39 20191031 =

* Fix percentage values

= 1.38 20190729 =

* Bring label changes to results side

= 1.37 20190729 =

* VA calculation fixes

= 1.36.2 20190312 =

* Fix additional numberformatter instance in functions

= 1.36 20190312 =

* Fix NaN value for properties in the millions

= 1.35 20190227 =

* Fixing currency dependency issue with NumberFormatter

= 1.34 20190202 =

* Minor css class fixes for styling consistency

= 1.33 20190102 =

* Added additional currencies and made insurance/hoa fields optional

= 1.32 20190102 =

* Fixed CSS issues with Visual Composer

= 1.2 20181102 =

* Added color picker. Cleaned up settings panel. Added custom disclaimers to be configured per calculator.

= 1.1 20181025 =

* Fixed CSS Issue w/ Bootstrap Tooltips

= 1.0 20180930 =

* First Version of Mortgage Calculators WP
