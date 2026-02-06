<?php
if (! defined('ABSPATH')) {
	die;
}

/*
* Declaring Class
*/

class AEH_Main
{
	public $settings;
	public $external_settings;
	public $external_cache_root_dir = null;
	public $external_cache_root_url = null;
	public function __construct()
	{
		$this->settings = AEH_Settings::get_instance();
		add_action('admin_notices', array($this, 'add_review_request'));
		add_action('wp_ajax_hide_review_notice', array($this, 'hide_review_request'));
		add_action('wp_ajax_nopriv_hide_review_notice', array($this, 'hide_review_request'));
		add_action('refresh_cache', array($this, 'refresh_browser_cache'));
		add_action('wp_ajax_purge_cache', array($this, 'refresh_browser_cache'));
		add_action('wp_ajax_nopriv_purge_cache', array($this, 'refresh_browser_cache'));
	}
	public function refresh_browser_cache()
	{
		update_option('aeh_expires_headers_last_modified_time', date(DATE_RFC822));
		$this->write_to_htaccess();
		wp_die();
	}
	public function add_review_request()
	{
		$review_request_time = get_option('review_request_time');
		if ($review_request_time) {
			$current_time = time();
			if ($current_time > $review_request_time) {
				echo '<div class="notice notice-success is-dismissible">
				<p>
					<img style="float:left;margin-right:27px;width: 50px;padding: 0.25em;" src="' . AEH_URL . 'assests/images/AddExpiresHeaders.png">
					<strong>
						Hi there! You\'ve been using AEH Speed Optimization: Browser Cache, Optimized Minify, Lazy Loading & Image Optimization Plugin. We hope it\'s been helpful. Would you mind rating it 5-stars to help spread the word?
					</strong>	
				</p>
				<p>
					<a class="button button-primary" target="_blank" href="https://wordpress.org/support/plugin/add-expires-headers/reviews/?rate=5#rate-response>" data-reason="am_now">
						<strong>Ok, you deserve it</strong>
					</a>
					<a class="button-secondary aeh-dismiss-maybelater" data-reason="maybe_later">
						Nope, maybe later
					</a>
					<a class="button-secondary aeh-dismiss-alreadydid" data-reason="already_did">
						I already did
					</a>
				</p>
			</div>';
			}
		} else {
			update_option('review_request_time', strtotime(date('d-m-Y H:i:s') . "+ 48 hours"));
		}
	}

	public function hide_review_request()
	{
		$nonce = $_REQUEST['security'];
		if (wp_verify_nonce($nonce, 'maybelater-nonce')) {
			update_option('review_request_time', strtotime(date('d-m-Y H:i:s') . "+ 240 hours"));
		}
		if (wp_verify_nonce($nonce, 'alreadydid-nonce')) {
			update_option('review_request_time', strtotime(date('d-m-Y H:i:s') . "+ 2400 hours"));
		}
	}

	public function remove_settings()
	{
		$this->delete_from_htaccess();
		if (get_option('aeh_scanned_urls')) {
			delete_option('aeh_scanned_urls');
		}
		if (get_option('aeh_extracted_urls')) {
			delete_option('aeh_extracted_urls');
		}
		if (get_option('aeh_expires_headers_external_cache_settings')) {
			delete_option('aeh_expires_headers_external_cache_settings');
		}
		if (get_option('aeh_expires_headers_advance_settings')) {
			delete_option('aeh_expires_headers_advance_settings');
		}
		if (get_option('aeh_expires_headers_minify_settings')) {
			delete_option('aeh_expires_headers_minify_settings');
		}
		if (get_option('aeh_expires_headers_settings')) {
			delete_option('aeh_expires_headers_settings');
		}
		if (is_dir($this->external_cache_root_dir)) {
			AEH_Pro::get_instance()->minify()->aeh_rrmdir($this->external_cache_root_dir);
		}
	}

	/* delete previous plugin lines from file */
	private function delete_from_htaccess($section = 'AEH: Speed Optimization Plugin')
	{
		$htaccess = ABSPATH . '.htaccess';
		@ini_set('auto_detect_line_endings', true);
		if (!file_exists($htaccess)) {
			$ht = @fopen($htaccess, 'a+');
			@fclose($ht);
		}
		$ht_contents = explode(PHP_EOL, implode('', file($htaccess)));
		if ($ht_contents) {
			$state = true;
			if (!$f = @fopen($htaccess, 'w+')) {
				@chmod($htaccess, 0644);
				if (!$f = @fopen($htaccess, 'w+')) {
					return -1;
				}
			}
			foreach ($ht_contents as $n => $markerline) {
				if (strpos($markerline, '# BEGIN ' . $section) !== false) {
					$state = false;
				}
				if ($state == true) {
					fwrite($f, trim($markerline) . PHP_EOL);
				}
				if (strpos($markerline, '# END ' . $section) !== false) {
					$state = true;
				}
			}
			@fclose($f);
			return 1;
		}
		return 1;
	}

	/* main function which updates lines to .htaccess file according to plugin settings */
	public function write_to_htaccess()
	{
		if ($this->delete_from_htaccess() == -1) {
			return -1;
		}
		$htaccess = ABSPATH . '.htaccess';
		$siteurl = explode('/', get_option('siteurl'));
		if (isset($siteurl[3])) {
			$dir = '/' . $siteurl[3] . '/';
		} else {
			$dir = '/';
		}
		if (!$f = @fopen($htaccess, 'a+')) {
			@chmod($htaccess, 0644);
			if (!$f = @fopen($htaccess, 'a+')) {
				return -1;
			}
		}

		@ini_set('auto_detect_line_endings', true);
		$ht = explode(PHP_EOL, implode('', file($htaccess))); //parse each line of file into array

		$rules = $this->getrules();
		if ($rules == -1) {
			return -1;
		}
		$rulesarray = explode(PHP_EOL, $rules);
		$contents = array_merge($rulesarray, $ht);

		if (!$f = @fopen($htaccess, 'w+')) {
			return -1;
		}
		$blank = false;
		foreach ($contents as $insertline) {
			if (trim($insertline) == '') {
				if ($blank == false) {
					fwrite($f, PHP_EOL . trim($insertline));
				}
				$blank = true;
			} else {
				$blank = false;
				fwrite($f, PHP_EOL . trim($insertline));
			}
		}
		@fclose($f);
		return 1;
	}

	/* getting lines to be added in .htaccess files base on settings */
	private function getrules()
	{
		@ini_set('auto_detect_line_endings', true);
		if (strstr(strtolower(filter_var($_SERVER['SERVER_SOFTWARE'], FILTER_UNSAFE_RAW)), 'apache')) {
			$aiowps_server = 'apache';
		} else if (strstr(strtolower(filter_var($_SERVER['SERVER_SOFTWARE'], FILTER_UNSAFE_RAW)), 'nginx')) {
			$aiowps_server = 'nginx';
		} else if (strstr(strtolower(filter_var($_SERVER['SERVER_SOFTWARE'], FILTER_UNSAFE_RAW)), 'litespeed')) {
			$aiowps_server = 'litespeed';
		} else {
			return -1;
		}
		$rules = '';
		$aeh_expires_headers_settings = get_option('aeh_expires_headers_settings');
		if (dd_aeh()->is()) {
			$aeh_expires_headers_advance_settings = get_option('aeh_expires_headers_advance_settings');
			$aeh_expires_headers_last_modified_time = get_option('aeh_expires_headers_last_modified_time');
		}
		if (dd_aeh()->is()) {
			if (dd_aeh()->can_use_premium_code()) {
				if (isset($aeh_expires_headers_advance_settings['advance']['unset_entity_tag'])) {
					$rules .= '# Disable ETags' . PHP_EOL;
					$rules .= '<IfModule mod_headers.c>' . PHP_EOL;
					$rules .= 'Header unset ETag' . PHP_EOL;
					$rules .= '</IfModule>' . PHP_EOL;
					$rules .= 'FileETag None' . PHP_EOL;
					$rules .= '#' . date(DATE_RFC822) . ' Disable ETags' . PHP_EOL;
				}
				if (isset($aeh_expires_headers_advance_settings['advance']['refresh_cache'])) {
					if ($aeh_expires_headers_last_modified_time == false || empty($aeh_expires_headers_last_modified_time)) {
						update_option('aeh_expires_headers_last_modified_time', date(DATE_RFC822));
						$rules .= '# checking' . date(DATE_RFC822);
						$aeh_expires_headers_last_modified_time = date(DATE_RFC822);
					}
					$rules .= '# Adding Last-modified Headers' . PHP_EOL;
					$rules .= '<IfModule mod_headers.c>' . PHP_EOL;
					$rules .= 'Header Set Last-Modified "' . $aeh_expires_headers_last_modified_time . '" ' . PHP_EOL;
					$rules .= '</IfModule>' . PHP_EOL;
					$rules .= '# Adding Last-modified Headers' . PHP_EOL;
				}
				if (isset($aeh_expires_headers_advance_settings['advance']['enable_gzip_compression'])) {
					$rules .= '# Enabling File Compression' . PHP_EOL;
					$rules .=        '<IfModule mod_deflate.c>
											AddOutputFilterByType DEFLATE text/html
											AddOutputFilterByType DEFLATE text/css
											AddOutputFilterByType DEFLATE text/javascript
											AddOutputFilterByType DEFLATE text/xml
											AddOutputFilterByType DEFLATE text/plain
											AddOutputFilterByType DEFLATE image/x-icon
											AddOutputFilterByType DEFLATE image/svg+xml
											AddOutputFilterByType DEFLATE application/rss+xml
											AddOutputFilterByType DEFLATE application/javascript
											AddOutputFilterByType DEFLATE application/x-javascript
											AddOutputFilterByType DEFLATE application/xml
											AddOutputFilterByType DEFLATE application/xhtml+xml
											AddOutputFilterByType DEFLATE application/x-font
											AddOutputFilterByType DEFLATE application/x-font-truetype
											AddOutputFilterByType DEFLATE application/x-font-ttf
											AddOutputFilterByType DEFLATE application/x-font-otf
											AddOutputFilterByType DEFLATE application/x-font-opentype
											AddOutputFilterByType DEFLATE application/vnd.ms-fontobject
											AddOutputFilterByType DEFLATE font/otf
											AddOutputFilterByType DEFLATE font/opentype
											</IfModule>' . PHP_EOL;
					$rules .= '# Enabling File Compression' . PHP_EOL;
				}
			}
		}
		$rules .= '<IfModule mod_expires.c>' . PHP_EOL;
		$rules .= 'ExpiresActive on' . PHP_EOL;
		$general_settings = $this->settings->expires_headers_general_settings;
		foreach ($general_settings as $key => $value) {
			$rules .= "#" . $key . PHP_EOL;
			if (isset($aeh_expires_headers_settings['general'][$key])) {
				$type_key = 'expires_headers_' . $key . '_types';
				foreach ($aeh_expires_headers_settings[$key] as $key1 => $value1) {
					if (isset($aeh_expires_headers_settings[$key][$key1])) {
						$expiryDays = (isset($aeh_expires_headers_settings['expires_days'][$key]) && !empty($aeh_expires_headers_settings['expires_days'][$key])) ? $aeh_expires_headers_settings['expires_days'][$key] : 30;
						$rules .= 'ExpiresByType ' . $key . '/' . $key1 . ' A' . ($expiryDays * 86400) . PHP_EOL;
					}
				}
			}
		}
		$rules .= '</IfModule>' . PHP_EOL;
		if (dd_aeh()->is()) {
			if (dd_aeh()->can_use_premium_code()) {
				if (!empty($aeh_expires_headers_advance_settings["prevent_cache"])) {
					$prevent_string = preg_replace('/\s+/', '', $aeh_expires_headers_advance_settings["prevent_cache"]);
					$prevent_string = str_replace(",", "|", $prevent_string);
					$rules .= '<FilesMatch "^(' . $prevent_string . ')$">' . PHP_EOL;
					$rules .= 'FileETag None' . PHP_EOL;
					$rules .= '<IfModule mod_headers.c>' . PHP_EOL;
					$rules .= 'Header unset ETag' . PHP_EOL;
					$rules .= 'Header set Cache-Control "max-age=0, no-cache, no-store, must-revalidate"' . PHP_EOL;
					$rules .= 'Header set Pragma "no-cache"' . PHP_EOL;
					$rules .= ' Header set Expires "Wed, 11 Jan 1984 05:00:00 GMT"' . PHP_EOL;
					$rules .= '</IfModule>' . PHP_EOL;
					$rules .= '</FilesMatch>' . PHP_EOL;
				}
			}
		}
		if ($rules != '') {
			$rules = "# BEGIN AEH: Speed Optimization Plugin" . PHP_EOL . $rules . "# END AEH: Speed Optimization Plugin" . PHP_EOL;
		}
		return $rules;
	}
}
