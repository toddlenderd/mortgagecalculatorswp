<?php


if (!class_exists('WPMC_EDD_SL_Plugin_Updater')) {


	/**
	 * Allows plugins to use their own update API.
	 *
	 * @author Easy Digital Downloads
	 * @version 1.6.23
	 */
	class WPMC_EDD_SL_Plugin_Updater
	{

		const VERSION = '1.6.23';

		private $api_url     = '';
		private $api_data    = array();
		private $name        = '';
		private $slug        = '';
		private $version     = '';
		private $wp_override = false;
		private $beta = false;
		private $cache_key   = '';

		private $health_check_timeout = 5;

		/**
		 * Class constructor.
		 *
		 * @uses plugin_basename()
		 * @uses hook()
		 *
		 * @param string  $_api_url     The URL pointing to the custom API endpoint.
		 * @param string  $_plugin_file Path to the plugin file.
		 * @param array   $_api_data    Optional data to send with API calls.
		 */
		public function __construct($_api_url, $_plugin_file, $_api_data = null)
		{

			global $edd_plugin_data;

			$this->api_url     = trailingslashit($_api_url);
			$this->api_data    = $_api_data;
			$this->name        = plugin_basename($_plugin_file);
			$this->slug        = basename($_plugin_file, '.php');
			$this->version     = $_api_data['version'];
			$this->wp_override = isset($_api_data['wp_override']) ? (bool) $_api_data['wp_override'] : false;
			$this->beta        = !empty($this->api_data['beta']) ? true : false;
			$this->cache_key   = 'edd_sl_' . md5(serialize($this->slug . $this->api_data['license'] . $this->beta));

			$edd_plugin_data[$this->slug] = $this->api_data;

			/**
			 * Fires after the $edd_plugin_data is setup.
			 *
			 * @since x.x.x
			 *
			 * @param array $edd_plugin_data Array of EDD SL plugin data.
			 */
			do_action('post_edd_sl_plugin_updater_setup', $edd_plugin_data);

			// Set up hooks.
			$this->init();
		}

		/**
		 * Set up WordPress filters to hook into WP's update process.
		 *
		 * @uses add_filter()
		 *
		 * @return void
		 */
		public function init()
		{

			add_filter('pre_set_site_transient_update_plugins', array($this, 'check_update'));
			add_filter('plugins_api', array($this, 'plugins_api_filter'), 10, 3);
			remove_action('after_plugin_row_' . $this->name, 'wp_plugin_update_row', 10);
			add_action('after_plugin_row_' . $this->name, array($this, 'show_update_notification'), 10, 2);
			add_action('admin_init', array($this, 'show_changelog'));
			add_filter('http_request_args', array($this, 'http_request_args'), 10, 2);
			add_action('admin_init', array($this, 'maybe_force_update_check'));
			add_action('upgrader_process_complete', array($this, 'flush_version_caches_after_upgrade'), 10, 2);
		}

		/**
		 * Build the cache key used by plugins_api_filter (kept in sync with that method).
		 *
		 * @return string
		 */
		private function api_cache_key()
		{
			return 'edd_api_request_' . md5(serialize($this->slug . $this->api_data['license'] . $this->beta));
		}

		/**
		 * Delete every cache we own so the next check hits the EDD store fresh.
		 */
		public function flush_version_caches()
		{
			delete_option($this->cache_key);
			delete_option($this->api_cache_key());
			// Also flush WP's own transient so it rebuilds from our fresh data
			delete_site_transient('update_plugins');
		}

		/**
		 * Force-refresh the version cache in two situations:
		 *   1. The user clicked "Update Now" on the plugins list (ajax or classic)
		 *   2. The user visited ?wpmc-force-update-check=1 (manual escape hatch)
		 *
		 * WP core calls wp_clean_plugins_cache() right before upgrading, which rebuilds
		 * the update_plugins transient from our cached EDD response. If our EDD cache
		 * is stale, the rebuilt transient will say "no update" and the upgrader bails
		 * with "The plugin is at the latest version." Clearing our cache here forces
		 * a fresh call to the EDD store.
		 */
		public function maybe_force_update_check()
		{
			if (!is_admin()) {
				return;
			}

			// Manual escape hatch: /wp-admin/?wpmc-force-update-check=1
			if (!empty($_GET['wpmc-force-update-check']) && current_user_can('update_plugins')) {
				$this->flush_version_caches();
				return;
			}

			if (empty($_REQUEST['action'])) {
				return;
			}

			$action = sanitize_text_field(wp_unslash($_REQUEST['action']));
			if (!in_array($action, array('update-plugin', 'update-selected'), true)) {
				return;
			}

			$plugin_match = false;
			if (!empty($_REQUEST['plugin']) && sanitize_text_field(wp_unslash($_REQUEST['plugin'])) === $this->name) {
				$plugin_match = true;
			}
			if (!empty($_REQUEST['checked']) && is_array($_REQUEST['checked'])) {
				$checked = array_map('sanitize_text_field', wp_unslash($_REQUEST['checked']));
				if (in_array($this->name, $checked, true)) {
					$plugin_match = true;
				}
			}

			if ($plugin_match) {
				$this->flush_version_caches();
			}
		}

		/**
		 * Clear caches after an upgrade completes so the next page load reflects reality.
		 */
		public function flush_version_caches_after_upgrade($upgrader, $data)
		{
			if (empty($data['type']) || $data['type'] !== 'plugin') {
				return;
			}
			if (empty($data['plugins']) || !is_array($data['plugins'])) {
				return;
			}
			if (in_array($this->name, $data['plugins'], true)) {
				$this->flush_version_caches();
			}
		}

		/**
		 * Check for Updates at the defined API endpoint and modify the update array.
		 *
		 * This function dives into the update API just when WordPress creates its update array,
		 * then adds a custom API call and injects the custom plugin data retrieved from the API.
		 * It is reassembled from parts of the native WordPress plugin update code.
		 * See wp-includes/update.php line 121 for the original wp_update_plugins() function.
		 *
		 * @uses api_request()
		 *
		 * @param array   $_transient_data Update array build by WordPress.
		 * @return array Modified update array with custom plugin data.
		 */
		public function check_update($_transient_data)
		{

			global $pagenow;

			if (!is_object($_transient_data)) {
				$_transient_data = new stdClass;
			}

			if ('plugins.php' == $pagenow && is_multisite()) {
				return $_transient_data;
			}

			if (!empty($_transient_data->response) && !empty($_transient_data->response[$this->name]) && false === $this->wp_override) {
				return $_transient_data;
			}

			$version_info = $this->get_cached_version_info();

			// Self-heal stale cache: if the cached version says the latest release equals
			// (or is older than) what's installed, the cache is almost certainly stale —
			// re-fetch from the EDD store. This is what causes "plugin is at latest version"
			// errors right after a store version bump.
			if (
				false !== $version_info
				&& is_object($version_info)
				&& !empty($version_info->new_version)
				&& version_compare($version_info->new_version, $this->version, '<=')
			) {
				$version_info = false;
			}

			if (false === $version_info) {
				$version_info = $this->api_request('plugin_latest_version', array('slug' => $this->slug, 'beta' => $this->beta));

				$this->set_version_info_cache($version_info);
			}

			if (false !== $version_info && is_object($version_info) && isset($version_info->new_version)) {

				// Map download_link to package so WordPress can find the download URL
				if (isset($version_info->download_link) && !isset($version_info->package)) {
					$version_info->package = $version_info->download_link;
				}

				$used_github_fallback = false;
				// Fall back to GitHub releases if EDD didn't provide a download URL.
				// Release tags MUST be "v" + version (e.g. v2.4.0): every installed version since 2.2 builds this
				// same URL, so changing the format here would strand sites on older versions. See RELEASING.md.
				if (empty($version_info->package) && defined('WPMC_GITHUB_RELEASES_URL')) {
					$version_info->package = WPMC_GITHUB_RELEASES_URL . '/v' . $version_info->new_version . '/mortgage-calculators-wp.zip';
					$used_github_fallback = true;
				}

				// Log the chosen download URL so failures can be diagnosed from debug.log
				if (defined('WP_DEBUG') && WP_DEBUG && defined('WP_DEBUG_LOG') && WP_DEBUG_LOG) {
					error_log(sprintf(
						'[WPMC Updater] new_version=%s installed=%s source=%s package=%s',
						isset($version_info->new_version) ? $version_info->new_version : '(none)',
						$this->version,
						$used_github_fallback ? 'github-fallback' : 'edd',
						isset($version_info->package) ? $version_info->package : '(empty)'
					));
				}

				if (version_compare($this->version, $version_info->new_version, '<')) {

					$_transient_data->response[$this->name] = $version_info;

					// Make sure the plugin property is set to the plugin's name/location. See issue 1463 on Software Licensing's GitHub repo.
					$_transient_data->response[$this->name]->plugin = $this->name;
				}

				$_transient_data->last_checked           = time();
				$_transient_data->checked[$this->name] = $this->version;
			}

			return $_transient_data;
		}

		/**
		 * show update nofication row -- needed for multisite subsites, because WP won't tell you otherwise!
		 *
		 * @param string  $file
		 * @param array   $plugin
		 */
		public function show_update_notification($file, $plugin)
		{

			if (is_network_admin()) {
				return;
			}

			if (!current_user_can('update_plugins')) {
				return;
			}

			if (!is_multisite()) {
				return;
			}

			if ($this->name != $file) {
				return;
			}

			// Remove our filter on the site transient
			remove_filter('pre_set_site_transient_update_plugins', array($this, 'check_update'), 10);

			$update_cache = get_site_transient('update_plugins');

			$update_cache = is_object($update_cache) ? $update_cache : new stdClass();

			if (empty($update_cache->response) || empty($update_cache->response[$this->name])) {

				$version_info = $this->get_cached_version_info();

				if (false === $version_info) {
					$version_info = $this->api_request('plugin_latest_version', array('slug' => $this->slug, 'beta' => $this->beta));

					// Since we disabled our filter for the transient, we aren't running our object conversion on banners, sections, or icons. Do this now:
					if (isset($version_info->banners) && !is_array($version_info->banners)) {
						$version_info->banners = $this->convert_object_to_array($version_info->banners);
					}

					if (isset($version_info->sections) && !is_array($version_info->sections)) {
						$version_info->sections = $this->convert_object_to_array($version_info->sections);
					}

					if (isset($version_info->icons) && !is_array($version_info->icons)) {
						$version_info->icons = $this->convert_object_to_array($version_info->icons);
					}

					if (isset($version_info->icons) && !is_array($version_info->icons)) {
						$version_info->icons = $this->convert_object_to_array($version_info->icons);
					}

					if (isset($version_info->contributors) && !is_array($version_info->contributors)) {
						$version_info->contributors = $this->convert_object_to_array($version_info->contributors);
					}

					$this->set_version_info_cache($version_info);
				}

				if (!is_object($version_info)) {
					return;
				}

				// Map download_link to package so WordPress can find the download URL
				if (isset($version_info->download_link) && !isset($version_info->package)) {
					$version_info->package = $version_info->download_link;
				}

				// Fall back to GitHub releases if EDD didn't provide a download URL.
				// Release tags MUST be "v" + version (e.g. v2.4.0): every installed version since 2.2 builds this
				// same URL, so changing the format here would strand sites on older versions. See RELEASING.md.
				if (empty($version_info->package) && defined('WPMC_GITHUB_RELEASES_URL')) {
					$version_info->package = WPMC_GITHUB_RELEASES_URL . '/v' . $version_info->new_version . '/mortgage-calculators-wp.zip';
				}

				if (version_compare($this->version, $version_info->new_version, '<')) {

					$update_cache->response[$this->name] = $version_info;
				}

				$update_cache->last_checked = time();
				$update_cache->checked[$this->name] = $this->version;

				set_site_transient('update_plugins', $update_cache);
			} else {

				$version_info = $update_cache->response[$this->name];
			}

			// Restore our filter
			add_filter('pre_set_site_transient_update_plugins', array($this, 'check_update'));

			if (!empty($update_cache->response[$this->name]) && version_compare($this->version, $version_info->new_version, '<')) {

				// build a plugin list row, with update notification
				$wp_list_table = _get_list_table('WP_Plugins_List_Table');
				# <tr class="plugin-update-tr"><td colspan="' . $wp_list_table->get_column_count() . '" class="plugin-update colspanchange">
				echo '<tr class="plugin-update-tr" id="' . $this->slug . '-update" data-slug="' . $this->slug . '" data-plugin="' . $this->slug . '/' . $file . '">';
				echo '<td colspan="3" class="plugin-update colspanchange">';
				echo '<div class="update-message notice inline notice-warning notice-alt">';

				$changelog_link = self_admin_url('index.php?edd_sl_action=view_plugin_changelog&plugin=' . $this->name . '&slug=' . $this->slug . '&TB_iframe=true&width=772&height=911');

				if (empty($version_info->download_link)) {
					printf(
						__('There is a new version of %1$s available. %2$sView version %3$s details%4$s.', 'easy-digital-downloads'),
						esc_html($version_info->name),
						'<a target="_blank" class="thickbox" href="' . esc_url($changelog_link) . '">',
						esc_html($version_info->new_version),
						'</a>'
					);
				} else {
					printf(
						__('There is a new version of %1$s available. %2$sView version %3$s details%4$s or %5$supdate now%6$s.', 'easy-digital-downloads'),
						esc_html($version_info->name),
						'<a target="_blank" class="thickbox" href="' . esc_url($changelog_link) . '">',
						esc_html($version_info->new_version),
						'</a>',
						'<a href="' . esc_url(wp_nonce_url(self_admin_url('update.php?action=upgrade-plugin&plugin=') . $this->name, 'upgrade-plugin_' . $this->name)) . '">',
						'</a>'
					);
				}

				do_action("in_plugin_update_message-{$file}", $plugin, $version_info);

				echo '</div></td></tr>';
			}
		}

		/**
		 * Updates information on the "View version x.x details" page with custom data.
		 *
		 * @uses api_request()
		 *
		 * @param mixed   $_data
		 * @param string  $_action
		 * @param object  $_args
		 * @return object $_data
		 */
		public function plugins_api_filter($_data, $_action = '', $_args = null)
		{

			if ($_action != 'plugin_information') {

				return $_data;
			}

			if (!isset($_args->slug) || ($_args->slug != $this->slug)) {

				return $_data;
			}

			$to_send = array(
				'slug'   => $this->slug,
				'is_ssl' => is_ssl(),
				'fields' => array(
					'banners' => array(),
					'reviews' => false,
					'icons'   => array(),
				)
			);

			$cache_key = 'edd_api_request_' . md5(serialize($this->slug . $this->api_data['license'] . $this->beta));

			// Get the transient where we store the api request for this plugin for 24 hours
			$edd_api_request_transient = $this->get_cached_version_info($cache_key);

			//If we have no transient-saved value, run the API, set a fresh transient with the API value, and return that value too right now.
			if (empty($edd_api_request_transient)) {

				$api_response = $this->api_request('plugin_information', $to_send);

				// Expires in 1 hour
				$this->set_version_info_cache($api_response, $cache_key);

				if (false !== $api_response) {
					$_data = $api_response;
				}
			} else {
				$_data = $edd_api_request_transient;
			}

			if (is_bool($_data)) {
				return $_data;
			}

			// Convert sections into an associative array, since we're getting an object, but Core expects an array.
			if (isset($_data->sections) && !is_array($_data->sections)) {
				$_data->sections = $this->convert_object_to_array($_data->sections);
			}

			// Convert banners into an associative array, since we're getting an object, but Core expects an array.
			if (isset($_data->banners) && !is_array($_data->banners)) {
				$_data->banners = $this->convert_object_to_array($_data->banners);
			}

			// Convert icons into an associative array, since we're getting an object, but Core expects an array.
			if (isset($_data->icons) && !is_array($_data->icons)) {
				$_data->icons = $this->convert_object_to_array($_data->icons);
			}

			// Convert contributors into an associative array, since we're getting an object, but Core expects an array.
			if (isset($_data->contributors) && !is_array($_data->contributors)) {
				$_data->contributors = $this->convert_object_to_array($_data->contributors);
			}

			if (!isset($_data->plugin)) {
				$_data->plugin = $this->name;
			}

			// Map download_link to package so WordPress can find the download URL
			if (isset($_data->download_link) && !isset($_data->package)) {
				$_data->package = $_data->download_link;
			}

			// Fall back to GitHub releases if EDD didn't provide a download URL
			if (empty($_data->package) && defined('WPMC_GITHUB_RELEASES_URL') && isset($_data->new_version)) {
				$_data->package = WPMC_GITHUB_RELEASES_URL . '/v' . $_data->new_version . '/mortgage-calculators-wp.zip';
			}

			return $_data;
		}

		/**
		 * Convert some objects to arrays when injecting data into the update API
		 *
		 * Some data like sections, banners, and icons are expected to be an associative array, however due to the JSON
		 * decoding, they are objects. This method allows us to pass in the object and return an associative array.
		 *
		 * @since 3.6.5
		 *
		 * @param stdClass $data
		 *
		 * @return array
		 */
		private function convert_object_to_array($data)
		{
			$new_data = array();

			if (is_object($data) || is_array($data)) {
				foreach ($data as $key => $value) {
					$new_data[$key] = is_object($value) ? $this->convert_object_to_array($value) : $value;
				}
			}

			return $new_data;
		}

		/**
		 * Disable SSL verification in order to prevent download update failures
		 *
		 * @param array   $args
		 * @param string  $url
		 * @return object $array
		 */
		public function http_request_args($args, $url)
		{

			$verify_ssl = $this->verify_ssl();
			if (strpos($url, 'https://') !== false && strpos($url, 'edd_action=package_download') !== false) {
				$args['sslverify'] = $verify_ssl;
			}
			// Also handle direct download URLs from the EDD store
			if (strpos($url, $this->api_url) !== false && strpos($url, 'download') !== false) {
				$args['sslverify'] = $verify_ssl;
			}
			return $args;
		}

		/**
		 * Calls the API and, if successful, returns the object delivered by the API.
		 *
		 * @uses get_bloginfo()
		 * @uses wp_remote_post()
		 * @uses is_wp_error()
		 *
		 * @param string  $_action The requested action.
		 * @param array   $_data   Parameters for the API action.
		 * @return false|object
		 */
		private function api_request($_action, $_data)
		{

			global $wp_version, $edd_plugin_url_available;

			$verify_ssl = $this->verify_ssl();

			// Do a quick status check on this domain if we haven't already checked it.
			$store_hash = md5($this->api_url);
			if (!is_array($edd_plugin_url_available) || !isset($edd_plugin_url_available[$store_hash])) {
				$test_url_parts = parse_url($this->api_url);

				$scheme = !empty($test_url_parts['scheme']) ? $test_url_parts['scheme']     : 'http';
				$host   = !empty($test_url_parts['host'])   ? $test_url_parts['host']       : '';
				$port   = !empty($test_url_parts['port'])   ? ':' . $test_url_parts['port'] : '';

				if (empty($host)) {
					$edd_plugin_url_available[$store_hash] = false;
				} else {
					$test_url = $scheme . '://' . $host . $port;
					$response = wp_remote_get($test_url, array('timeout' => $this->health_check_timeout, 'sslverify' => $verify_ssl));
					$edd_plugin_url_available[$store_hash] = !is_wp_error($response);
				}
			}

			if (false === $edd_plugin_url_available[$store_hash]) {
				return false;
			}

			$data = array_merge($this->api_data, $_data);

			if ($data['slug'] != $this->slug) {
				return false;
			}

			if ($this->api_url == trailingslashit(home_url())) {
				return false; // Don't allow a plugin to ping itself
			}

			$api_params = array(
				'edd_action' => 'get_version',
				'license'    => !empty($data['license']) ? $data['license'] : '',
				'item_name'  => isset($data['item_name']) ? $data['item_name'] : false,
				'item_id'    => isset($data['item_id']) ? $data['item_id'] : false,
				'version'    => isset($data['version']) ? $data['version'] : false,
				'slug'       => $data['slug'],
				'author'     => $data['author'],
				'url'        => home_url(),
				'beta'       => !empty($data['beta']),
			);

			$request    = wp_remote_post($this->api_url, array('timeout' => 15, 'sslverify' => $verify_ssl, 'body' => $api_params));

			if (!is_wp_error($request)) {
				$request = json_decode(wp_remote_retrieve_body($request));
			}

			if ($request && isset($request->sections)) {
				$request->sections = maybe_unserialize($request->sections);
			} else {
				$request = false;
			}

			if ($request && isset($request->banners)) {
				$request->banners = maybe_unserialize($request->banners);
			}

			if ($request && isset($request->icons)) {
				$request->icons = maybe_unserialize($request->icons);
			}

			if (!empty($request->sections)) {
				foreach ($request->sections as $key => $section) {
					$request->$key = (array) $section;
				}
			}

			return $request;
		}

		public function show_changelog()
		{

			global $edd_plugin_data;

			if (empty($_REQUEST['edd_sl_action']) || 'view_plugin_changelog' != $_REQUEST['edd_sl_action']) {
				return;
			}

			if (empty($_REQUEST['plugin'])) {
				return;
			}

			if (empty($_REQUEST['slug'])) {
				return;
			}

			if (!current_user_can('update_plugins')) {
				wp_die(__('You do not have permission to install plugin updates', 'easy-digital-downloads'), __('Error', 'easy-digital-downloads'), array('response' => 403));
			}

			$data         = $edd_plugin_data[$_REQUEST['slug']];
			$beta         = !empty($data['beta']) ? true : false;
			$cache_key    = md5('edd_plugin_' . sanitize_key($_REQUEST['plugin']) . '_' . $beta . '_version_info');
			$version_info = $this->get_cached_version_info($cache_key);

			if (false === $version_info) {

				$api_params = array(
					'edd_action' => 'get_version',
					'item_name'  => isset($data['item_name']) ? $data['item_name'] : false,
					'item_id'    => isset($data['item_id']) ? $data['item_id'] : false,
					'slug'       => $_REQUEST['slug'],
					'author'     => $data['author'],
					'url'        => home_url(),
					'beta'       => !empty($data['beta'])
				);

				$verify_ssl = $this->verify_ssl();
				$request    = wp_remote_post($this->api_url, array('timeout' => 15, 'sslverify' => $verify_ssl, 'body' => $api_params));

				if (!is_wp_error($request)) {
					$version_info = json_decode(wp_remote_retrieve_body($request));
				}


				if (!empty($version_info) && isset($version_info->sections)) {
					$version_info->sections = maybe_unserialize($version_info->sections);
				} else {
					$version_info = false;
				}

				if (!empty($version_info)) {
					foreach ($version_info->sections as $key => $section) {
						$version_info->$key = (array) $section;
					}
				}

				$this->set_version_info_cache($version_info, $cache_key);
			}

			// Fix for sections response being an object instead of an array
			$version_info->sections = (array) $version_info->sections;

			if (!empty($version_info) && isset($version_info->sections['changelog'])) {
				echo '<div style="background:#fff;padding:10px;">' . $version_info->sections['changelog'] . '</div>';
			}

			if (!empty($version_info) && isset($version_info->sections['description'])) {
				echo '<div style="background:#fff;padding:10px;">' . $version_info->sections['description'] . '</div>';
			}

			exit;
		}

		public function get_cached_version_info($cache_key = '')
		{

			if (empty($cache_key)) {
				$cache_key = $this->cache_key;
			}

			$cache = get_option($cache_key);

			if (empty($cache['timeout']) || time() > $cache['timeout']) {
				return false; // Cache is expired
			}

			// We need to turn the icons into an array, thanks to WP Core forcing these into an object at some point.
			$cache['value'] = json_decode($cache['value']);
			if (!empty($cache['value']->icons)) {
				$cache['value']->icons = (array) $cache['value']->icons;
			}

			return $cache['value'];
		}

		public function set_version_info_cache($value = '', $cache_key = '')
		{

			if (empty($cache_key)) {
				$cache_key = $this->cache_key;
			}

			$data = array(
				'timeout' => strtotime('+1 hour', time()),
				'value'   => json_encode($value)
			);

			update_option($cache_key, $data, 'no');
		}

		/**
		 * Returns if the SSL of the store should be verified.
		 *
		 * @since  1.6.13
		 * @return bool
		 */
		private function verify_ssl()
		{
			return (bool) apply_filters('edd_sl_api_request_verify_ssl', true, $this);
		}
	}
}
