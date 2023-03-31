<?php
/**
 * BuddyExtender Loader
 *
 * @package BuddyExtender
 * @subpackage Loader
 * @author WebDevStudios
 * @since 1.0.0
 */

/**
 * Plugin Name: BuddyExtender
 * Plugin URI:  https://michaelbox.net
 * Description: Extend BuddyPress with extra settings and options.
 * Version:	 1.0.2
 * Author: Michael Beckwith
 * Author URI:  https://michaelbox.net
 * License:	 GPLv2
 * Text Domain: wds
 * Domain Path: /languages
 */

/**
 * Copyright (c) 2016 WebDevStudios (email : contact@pluginize.com)
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License, version 2 or, at
 * your discretion, any later version, as published by the Free
 * Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */


/**
 * Autoloads files with classes when needed
 *
 * @since  1.0.0
 * @param  string $class_name Name of the class being requested.
 * @return void
 */
function bpextender_autoload_classes( $class_name ) {

	if ( 0 !== strpos( $class_name, 'BuddyExtender_' ) ) {
		return;
	}

	$filename = strtolower( str_replace(
		'_', '-',
		substr( $class_name, strlen( 'BuddyExtender_' ) )
	) );

	BuddyExtender::include_file( $filename );
}
spl_autoload_register( 'bpextender_autoload_classes' );

/**
 * Main initiation class.
 *
 * @since 1.0.0
 *
 * @var string $version  Plugin version.
 * @var string $basename Plugin basename.
 * @var string $url      Plugin URL.
 * @var string $path     Plugin Path.
 */
class BuddyExtender {

	/**
	 * Current version.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	const VERSION = '1.0.2';

	/**
	 * URL of plugin directory.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	protected $url = '';

	/**
	 * Path of plugin directory.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	protected $path = '';

	/**
	 * Plugin basename.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	protected $basename = '';

	/**
	 * Singleton instance of plugin.
	 *
	 * @since 1.0.0
	 * @var BuddyExtender
	 */
	protected static $single_instance = null;

	/**
	 * Creates or returns an instance of this class.
	 *
	 * @since 1.0.0
	 *
	 * @return BuddyExtender A single instance of this class.
	 */
	public static function get_instance() {
		if ( null === self::$single_instance ) {
			self::$single_instance = new self();
		}

		return self::$single_instance;
	}

	/**
	 * Sets up our plugin.
	 *
	 * @since 1.0.0
	 */
	protected function __construct() {
		$this->basename = plugin_basename( __FILE__ );
		$this->url      = plugin_dir_url( __FILE__ );
		$this->path     = plugin_dir_path( __FILE__ );
	}

	/**
	 * Add hooks and filters.
	 *
	 * @since 1.0.0
	 */
	public function hooks() {
		add_action( 'init', array( $this, 'init' ) );
		add_action( 'init', array( $this, 'load_libs' ) );
		$this->includes();
		add_action( 'init', array( $this, 'late_loaded' ), 999 );
		add_action( 'admin_enqueue_scripts', array( $this, 'scripts' ) );
	}

	/**
	 * Activate the plugin.
	 *
	 * @since 1.0.0
	 */
	function _activate() {
		// Make sure any rewrite functionality has been loaded.
		flush_rewrite_rules();
	}

	/**
	 * Init hooks.
	 *
	 * @since 1.0.0
	 */
	public function init() {
		if ( $this->check_requirements() ) {
			load_plugin_textdomain( 'bpextended', false, dirname( $this->basename ) . '/languages/' );
		}
	}

	/**
	 * Register scripts.
	 *
	 * @since 1.0.0
	 */
	public function scripts() {
		wp_register_style( 'ad-sidebar', $this->url . 'assets/css/style.css' );
	}

	/**
	 * Load libraries.
	 *
	 * @since 1.0.0
	 */
	public function load_libs() {

		// Load cmb2.
		if ( ! function_exists( 'cmb2_bootstrap' ) ) {
			require_once  __DIR__ . '/vendor/CMB2/init.php';
		}
	}

	/**
	 * Load includes.
	 *
	 * @since 1.0.0
	 */
	public function includes() {

		if ( file_exists( __DIR__ . '/classes/class-admin.php' ) ) {
			require_once  __DIR__ . '/classes/class-admin.php';
		}
	}

	/**
	 * Includes loaded late.
	 *
	 * @since 1.0.0
	 */
	public function late_loaded() {
		if ( ! class_exists( 'Helpscout_Customer' ) ) {
			require_once  __DIR__ . '/vendor/helpscout/helpscout-dashboard-widget.php';
		}
	}

	/**
	 * Check if the plugin meets requirements and disable it if they are not present.
	 *
	 * @since 1.0.0
	 *
	 * @return boolean result of meets_requirements.
	 */
	public function check_requirements() {
		if ( ! $this->meets_requirements() ) {

			// Add a dashboard notice.
			add_action( 'all_admin_notices', array( $this, 'requirements_not_met_notice' ) );

			// Deactivate our plugin.
			add_action( 'admin_init', array( $this, 'deactivate_me' ) );

			return false;
		}

		return true;
	}

	/**
	 * Deactivates this plugin, hook this function on admin_init.
	 *
	 * @since 1.0.0
	 */
	public function deactivate_me() {
		deactivate_plugins( $this->basename );
	}

	/**
	 * Check that all plugin requirements are met.
	 *
	 * @since 1.0.0
	 *
	 * @return boolean True if requirements are met.
	 */
	public static function meets_requirements() {
		// We have met all requirements.
		return class_exists( 'BuddyPress' );
	}

	/**
	 * Adds a notice to the dashboard if the plugin requirements are not met.
	 *
	 * @since 1.0.0
	 */
	public function requirements_not_met_notice() {
		// Output our error.
		$error_text = sprintf( __( 'BuddyExtender is missing requirements and has been <a href="%s">deactivated</a>. Please make sure BuddyPress is installed and activated.', 'bpextended' ), admin_url( 'plugins.php' ) );

		echo '<div id="message" class="error">';
		echo '<p>' . $error_text . '</p>';
		echo '</div>';
	}

	/**
	 * Magic getter for our object.
	 *
	 * @since 1.0.0
	 *
	 * @throws Exception Throws an exception if the field is invalid.
	 *
	 * @param string $field Field to get.
	 * @return mixed
	 */
	public function __get( $field ) {
		switch ( $field ) {
			case 'version':
				return self::VERSION;
			case 'basename':
			case 'url':
			case 'path':
				return $this->$field;
			default:
				throw new Exception( 'Invalid '. __CLASS__ .' property: ' . $field );
		}
	}

	/**
	 * Include a file from the includes directory.
	 *
	 * @since 1.0.0
	 *
	 * @param string $filename Name of the file to be included.
	 * @return bool Result of include call.
	 */
	public static function include_file( $filename ) {
		$file = self::dir( 'classes/class-'. $filename .'.php' );
		if ( file_exists( $file ) ) {
			return include_once( $file );
		}
		return false;
	}

	/**
	 * This plugin's directory.
	 *
	 * @since 1.0.0
	 *
	 * @param string $path (optional) appended path.
	 * @return string Directory and path.
	 */
	public static function dir( $path = '' ) {
		static $dir;
		$dir = $dir ? $dir : trailingslashit( dirname( __FILE__ ) );
		return $dir . $path;
	}

	/**
	 * This plugin's url.
	 *
	 * @since 1.0.0
	 *
	 * @param string $path (optional) appended path.
	 * @return string URL and path.
	 */
	public static function url( $path = '' ) {
		static $url;
		$url = $url ? $url : trailingslashit( plugin_dir_url( __FILE__ ) );
		return $url . $path;
	}
}

/**
 * Grab the BP_Extender object and return it.
 *
 * Wrapper for BP_Extender::get_instance().
 *
 * @since 1.0.0
 *
 * @return BuddyExtender Singleton instance of plugin class.
 */
function buddyextender() {
	return BuddyExtender::get_instance();
}

// Kick it off.
add_action( 'plugins_loaded', array( buddyextender(), 'hooks' ) );

register_activation_hook( __FILE__, array( buddyextender(), '_activate' ) );

/**
 * Sets BuddyPress defines. The BP_ prefix are from internal BuddyPress defines.
 * https://codex.buddypress.org/getting-started/customizing/changing-internal-configuration-settings/
 *
 * BuddyPress defines hooked to init before BP is loaded
 *
 * @since 1.0.0
 */
function bpextender_run_extended_settings() {

	if ( ! $options = get_option( 'bpext_options' ) ) {
		return;
	}

	foreach ( $options as $key => $value ) {
		switch ( $key ) {
			case 'avatar_thumb_size_select' :
				if ( ! defined( 'BP_AVATAR_THUMB_WIDTH' ) ) {
					define( 'BP_AVATAR_THUMB_WIDTH', (int) $options[ $key ] );
				}
				if ( ! defined( 'BP_AVATAR_THUMB_HEIGHT' ) ) {
					define( 'BP_AVATAR_THUMB_HEIGHT', (int) $options[ $key ] );
				}
			break;
			case 'avatar_full_size_select' :
				if ( ! defined( 'BP_AVATAR_FULL_WIDTH' ) ) {
					define( 'BP_AVATAR_FULL_WIDTH', (int) $options[ $key ] );
				}
				if ( ! defined( 'BP_AVATAR_FULL_HEIGHT' ) ) {
					define( 'BP_AVATAR_FULL_HEIGHT', (int) $options[ $key ] );
				}
			break;
			case 'avatar_max_size_select' :
				if ( ! defined( 'BP_AVATAR_ORIGINAL_MAX_WIDTH' ) ) {
					define( 'BP_AVATAR_ORIGINAL_MAX_WIDTH', (int) $options[ $key ] );
				}
			break;
			case 'avatar_default_image' :
				add_filter( 'bp_core_fetch_avatar_no_grav', '__return_true' );
				if ( ! defined( 'BP_AVATAR_DEFAULT' ) ) {
					define( 'BP_AVATAR_DEFAULT', $options[ $key ] );
				}
				if ( ! defined( 'BP_AVATAR_DEFAULT_THUMB' ) ) {
					define( 'BP_AVATAR_DEFAULT_THUMB', $options[ $key ] );
				}
			break;

			case 'cover_image_checkbox' :
				if ( 'on' === $options[ $key ] && ! defined( 'BP_DTHEME_DISABLE_CUSTOM_HEADER' ) ) {
					define( 'BP_DTHEME_DISABLE_CUSTOM_HEADER', true );
				}
			break;
			case 'group_auto_join_checkbox' :
				if ( 'on' === $options[ $key ] && ! defined( 'BP_DISABLE_AUTO_GROUP_JOIN' ) ) {
					define( 'BP_DISABLE_AUTO_GROUP_JOIN', true );
				}
			break;

			case 'all_autocomplete_checkbox' :
				if ( 'on' === $options[ $key ] && ! defined( 'BP_MESSAGES_AUTOCOMPLETE_ALL' ) ) {
					define( 'BP_MESSAGES_AUTOCOMPLETE_ALL', true );
				}
			break;
		}
	}

}
add_action( 'init', 'bpextender_run_extended_settings' );

/**
 * Returns root blog id on wpmu.
 *
 * @since 1.0.0
 *
 * @param integer $root_blog blog id.
 * @return integer blog id
 */
function bpextender_filter_root_blog_id( $root_blog ) {
	$options = get_option( 'bpext_options' );
	if ( isset( $options['root_blog_select'] ) ) {
		return $options['root_blog_select'];
	}
	return $root_blog;
}

/**
 * Runs BP configuration filters on bp_include.
 *
 * @since 1.0.0
 */
function bpextender_run_bp_included_settings() {

	if ( ! $options = get_option( 'bpext_options' ) ) {
		return;
	}

	foreach ( $options as $key => $value ) {
		switch ( $key ) {
			case 'profile_autolink_checkbox' :
				if ( 'on' === $options[ $key ] ) {
					remove_filter( 'bp_get_the_profile_field_value', 'xprofile_filter_link_profile_data', 9 );
				}
			break;
			case 'user_mentions_checkbox' :
				if ( 'on' === $options[ $key ] ) {
					add_filter( 'bp_activity_do_mentions', '__return_false' );
					add_filter( 'bp_activity_maybe_load_mentions_scripts', '__return_false' );
				}
			break;
			case 'root_profiles_checkbox' :
				if ( 'on' === $options[ $key ] ) {
					add_filter( 'bp_core_enable_root_profiles', '__return_true' );
				}
			break;
			case 'ldap_username_checkbox' :
				if ( 'on' === $options[ $key ] ) {
					add_filter( 'bp_is_username_compatibility_mode', '__return_true' );
				}
			break;
			case 'wysiwyg_editor_checkbox' :
				if ( 'on' === $options[ $key ] ) {
					add_filter( 'bp_xprofile_is_richtext_enabled_for_field', '__return_false' );
				}
			break;
			case 'depricated_code_checkbox' :
				if ( 'on' === $options[ $key ] ) {
					add_filter( 'bp_ignore_deprecated', '__return_true' );
				}
			break;
			// Multisite options.
			case 'enable_multiblog_checkbox' :
				if ( 'on' === $options[ $key ] ) {
					add_filter( 'bp_is_multiblog_mode', '__return_true' );
				}
			break;
			case 'root_blog_select' :
				add_filter( 'bp_get_root_blog_id', 'bpextender_filter_root_blog_id' );
			break;
		}
	}

}
add_action( 'bp_include', 'bpextender_run_bp_included_settings' );
