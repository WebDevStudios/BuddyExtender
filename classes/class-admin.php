<?php
/**
 * BuddyExtender_Admin class
 *
 * @package BuddyExtenderAdmin
 * @subpackage BuddyExtender
 * @author WebDevStudios
 * @since 1.0.0
 */

/**
 * Class BuddyExtender_Admin
 */
class BuddyExtender_Admin {

	/**
	 * Option key, and option page slug.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	public $key = 'bpext_options';

	/**
	 * Options page metabox id.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	private $metabox_id = 'bbpext_option_metabox';

	/**
	 * Options Page title.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	protected $title = '';

	/**
	 * Options Page hook.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	protected $options_page = '';

	/**
	 * Holds an instance of the object.
	 *
	 * @since 1.0.0
	 * @var BuddyExtender_Admin
	 */
	private static $instance = null;

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		// Set our title.
		$this->title = __( 'BuddyExtender', 'bpextended' );
	}

	/**
	 * Returns the running object
	 *
	 * @since 1.0.0
	 *
	 * @return BuddyExtender_Admin
	 */
	public static function get_instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new BuddyExtender_Admin();
			self::$instance->hooks();
		}
		return self::$instance;
	}

	/**
	 * Initiate our hooks.
	 *
	 * @since 1.0.0
	 */
	public function hooks() {
		add_action( 'admin_init', array( $this, 'init' ) );
		add_action( 'admin_menu', array( $this, 'add_options_page' ) );
		add_action( 'cmb2_admin_init', array( $this, 'add_options_page_metabox' ) );
	}


	/**
	 * Register our setting to WP.
	 *
	 * @since 1.0.0
	 */
	public function init() {
		register_setting( $this->key, $this->key );
	}

	/**
	 * Add menu options page.
	 *
	 * @since 1.0.0
	 */
	public function add_options_page() {

		$this->options_page = add_submenu_page(
			'options-general.php',
			$this->title,
			$this->title,
			'manage_options',
			$this->key,
			array( $this, 'admin_page_display' )
		);

		// Include CMB CSS in the head to avoid FOUC.
		add_action( "admin_print_styles-{$this->options_page}", array( 'CMB2_hookup', 'enqueue_cmb_css' ) );
	}

	/**
	 * Admin page markup. Mostly handled by CMB2.
	 *
	 * @since 1.0.0
	 */
	public function admin_page_display() {
		wp_enqueue_style( 'ad-sidebar' );
		?>
		<div class="wrap cmb2-options-page <?php echo esc_attr( $this->key ); ?>">
			<h2><?php esc_attr_e( 'BuddyExtender', 'bpextended' ); ?></h2>
			<div id="options-wrap">
				<?php cmb2_metabox_form( $this->metabox_id, $this->key ); ?>
			</div>
		</div>
		<?php
	}

	/**
	 * Add the options metabox to the array of metaboxes.
	 *
	 * @since 1.0.0
	 */
	function add_options_page_metabox() {

		// Hook in our save notices.
		add_action( "cmb2_save_options-page_fields_{$this->metabox_id}", array( $this, 'settings_notices' ), 10, 2 );

		$cmb = new_cmb2_box( array(
			'id'         => $this->metabox_id,
			'hookup'     => false,
			'cmb_styles' => false,
			'show_on'    => array(
			// These are important don't remove.
				'key'   => 'options-page',
				'value' => array( $this->key ),
			),
		) );

		// ************* Avatar settings ***********************************************
		$cmb->add_field( array(
			'name' => __( 'Avatar Settings', 'bpextended' ),
			'desc' => __( 'Customize user avatar dimentions and defaults', 'bpextended' ),
			'type' => 'title',
			'id'   => 'avatar_title',
		) );

		// Set our CMB2 fields.
		$cmb->add_field( array(
			'name'             => __( 'Avatar Thumb Size', 'bpextended' ),
			'desc'             => __( 'Changes user and group avatar to selected dimensions in activity, members and group lists.', 'bpextended' ),
			'id'               => 'avatar_thumb_size_select',
			'type'             => 'select',
			'show_option_none' => false,
			'default'          => '50',
			'options'          => 'bpextender_get_avatar_sizes',
		) );

		$cmb->add_field( array(
			'name'             => __( 'Avatar Full Size', 'bpextended' ),
			'desc'             => __( 'Changes user and group avatar to dimensions in user and group header.', 'bpextended' ),
			'id'               => 'avatar_full_size_select',
			'type'             => 'select',
			'show_option_none' => false,
			'default'          => '150',
			'options'          => 'bpextender_get_avatar_sizes',
		) );

		$cmb->add_field( array(
			'name'             => __( 'Avatar Max Size', 'bpextended' ),
			'desc'             => __( 'Changes maximum image size a user can uplaod for avatars.', 'bpextended' ),
			'id'               => 'avatar_max_size_select',
			'type'             => 'select',
			'show_option_none' => false,
			'default'          => '640',
			'options'          => 'bpextender_get_avatar_sizes',
		) );

		$cmb->add_field( array(
			'name'    => __( 'Default User Avatar', 'bpextended' ),
			'desc'    => __( 'Upload an image that displays before a user has added a custom image.', 'bpextended' ),
			'id'      => 'avatar_default_image',
			'type'    => 'file',
			'options' => array(
				'url'                  => false,
				'add_upload_file_text' => 'Add image',
			),
		) );

		// ************* Advanced settings ***********************************************
		$cmb->add_field( array(
			'name' => __( 'Advanced Settings', 'bpextended' ),
			'desc' => __( 'Internal configuration settings. Please make sure to check site after changing these options.', 'bpextended' ),
			'type' => 'title',
			'id'   => 'advanced_title',
		) );

		$cmb->add_field( array(
			'name' => __( 'Root Profiles', 'bpextended' ),
			'desc' => __( 'Remove members slug from profile url.', 'bpextended' ),
			'id'   => 'root_profiles_checkbox',
			'type' => 'checkbox',
		) );

		$cmb->add_field( array(
			'name' => __( 'Auto Group Join', 'bpextended' ),
			'desc' => __( 'disable auto join when posting in a group.', 'bpextended' ),
			'id'   => 'group_auto_join_checkbox',
			'type' => 'checkbox',
		) );

		$cmb->add_field( array(
			'name' => __( 'LDAP Usernames', 'bpextended' ),
			'desc' => __( 'Enable support for LDAP usernames that include dots.', 'bpextended' ),
			'id'   => 'ldap_username_checkbox',
			'type' => 'checkbox',
		) );

		$cmb->add_field( array(
			'name' => __( 'WYSIWYG Textarea', 'bpextended' ),
			'desc' => __( 'Removes text editor from textarea profile field.', 'bpextended' ),
			'id'   => 'wysiwyg_editor_checkbox',
			'type' => 'checkbox',
		) );

		$cmb->add_field( array(
			'name' => __( 'All Members Auto Complete', 'bpextended' ),
			'desc' => __( 'Auto-complete all members instead of just friends in messages.', 'bpextended' ),
			'id'   => 'all_autocomplete_checkbox',
			'type' => 'checkbox',
		) );

		$cmb->add_field( array(
			'name' => __( 'Profile Fields Auto Link', 'bpextended' ),
			'desc' => __( 'Disable autolinking in profile fields.', 'bpextended' ),
			'id'   => 'profile_autolink_checkbox',
			'type' => 'checkbox',
		) );

		$cmb->add_field( array(
			'name' => __( 'User @ Mentions', 'bpextended' ),
			'desc' => __( 'Disable User @ mentions.', 'bpextended' ),
			'id'   => 'user_mentions_checkbox',
			'type' => 'checkbox',
		) );

		$cmb->add_field( array(
			'name' => __( 'Ignore Depricated Code', 'bpextended' ),
			'desc' => __( 'Do not load depricated code', 'bpextended' ),
			'id'   => 'depricated_code_checkbox',
			'type' => 'checkbox',
		) );

		// Multisite settings here.
		if ( is_multisite() ) {

			$cmb->add_field( array(
				'name' => __( 'Multisite Settings', 'bpextended' ),
				'desc' => __( 'These options display when BuddyPress is active on multisite', 'bpextended' ),
				'type' => 'title',
				'id'   => 'network_title',
			) );

			$cmb->add_field( array(
				'name' => 'Enable Multiblog',
				'desc' => __( 'Allow BuddyPress to function on multiple blogs of a WPMU installation, not just on one root blog', 'bpextended' ),
				'id'   => 'enable_multiblog_checkbox',
				'type' => 'checkbox',
			) );

			$cmb->add_field( array(
				'name' => __( 'Root Blog ID', 'bpextended' ),
				'desc' => __( 'Enter blog ID BuddyPress will run on. Default ID is 1', 'bpextended' ),
				'id'   => 'root_blog_select',
				'type' => 'text',
			) );

		}

		$cmb->add_field( array(
			'name' => __( 'General Settings', 'bpextended' ),
			'desc' => __( '', 'bpextended' ),
			'type' => 'title',
			'id'   => 'general_title',
		) );

		$cmb->add_field( array(
			'name'            => __( 'Newsletter', 'bpextended' ),
			'desc'            => __( 'Add email address to get updates from pluginize.com about BuddyExtender.', 'bpextended' ),
			'id'              => 'pluginize_newsletter',
			'type'            => 'text',
			'sanitization_cb' => 'bpextender_newsletter_signup',
		) );

		$cmb->add_field( array(
			'name' => __( ' ', 'bpextended' ),
			'desc' => __( '', 'bpextended' ),
			'type' => 'title',
			'id'   => 'sidebar',
		) );

	}

	/**
	 * Register settings notices for display.
	 *
	 * @since 1.0.0
	 *
	 * @param int   $object_id Option key.
	 * @param array $updated Array of updated fields.
	 */
	public function settings_notices( $object_id, $updated ) {
		if ( $object_id !== $this->key || empty( $updated ) ) {
			return;
		}

		add_settings_error( $this->key . '-notices', '', __( 'Settings updated.', 'bpextended' ), 'updated' );
		settings_errors( $this->key . '-notices' );
	}

	/**
	 * Public getter method for retrieving protected/private variables.
	 *
	 * @since 1.0.0
	 *
	 * @throws Exception Throws an exception if the field is invalid.
	 *
	 * @param string $field Field to retrieve.
	 * @return mixed Field value or exception is thrown.
	 */
	public function __get( $field ) {
		// Allowed fields to retrieve.
		if ( in_array( $field, array( 'key', 'metabox_id', 'title', 'options_page' ), true ) ) {
			return $this->{$field};
		}

		throw new Exception( 'Invalid property: ' . $field );
	}
}

/**
 * Helper function to get/return the BPExtender_Admin object.
 *
 * @since 1.0.0
 *
 * @return BuddyExtender_Admin object.
 */
function buddyextender_admin() {
	return BuddyExtender_Admin::get_instance();
}

/**
 * Wrapper function around cmb2_get_option.
 *
 * @since 1.0.0
 *
 * @param string $key Options array key.
 * @return mixed Option value.
 */
function bpextender_get_option( $key = '' ) {
	return cmb2_get_option( buddyextender_admin()->key, $key );
}

// Get it started.
buddyextender_admin();

/**
 * Returns various select options for avatar sizes.
 *
 * @since 1.0.0
 *
 * @param object $field cmb2 filed data.
 * @return array
 */
function bpextender_get_avatar_sizes( $field ) {

	$field_id = $field->args['id'];

	switch ( $field_id ) {
		case 'avatar_thumb_size_select' :

			$sizes = array(
				'25'  => __( '25 x 25 px', 'bpextended' ),
				'50'  => __( '50 x 50 px', 'bpextended' ),
				'75'  => __( '75 x 75 px', 'bpextended' ),
				'100' => __( '100 x 100 px', 'bpextended' ),
				'125' => __( '125 x 125 px', 'bpextended' ),
				'150' => __( '150 x 150 px', 'bpextended' ),
				'175' => __( '175 x 175 px', 'bpextended' ),
				'200' => __( '200 x 200 px', 'bpextended' ),
				'225' => __( '225 x 225 px', 'bpextended' ),
				'250' => __( '250 x 250 px', 'bpextended' ),
				'275' => __( '275 x 275 px', 'bpextended' ),
				'300' => __( '300 x 300 px', 'bpextended' ),
			);

			/**
			 * Filters the avatar_thumb_sizes select values.
			 *
			 * @since 1.0.0
			 *
			 * @param array $sizes Array of sizes to display in select field.
			 */
			return apply_filters( 'get_avatar_thumb_sizes', $sizes );

		break;
		case 'avatar_full_size_select' :

			$sizes = array(
				'100' => __( '100 x 100 px', 'bpextended' ),
				'125' => __( '125 x 125 px', 'bpextended' ),
				'150' => __( '150 x 150 px', 'bpextended' ),
				'175' => __( '175 x 175 px', 'bpextended' ),
				'200' => __( '200 x 200 px', 'bpextended' ),
				'225' => __( '225 x 225 px', 'bpextended' ),
				'250' => __( '250 x 250 px', 'bpextended' ),
				'275' => __( '275 x 275 px', 'bpextended' ),
				'300' => __( '300 x 300 px', 'bpextended' ),
				'325' => __( '325 x 325 px', 'bpextended' ),
				'350' => __( '350 x 350 px', 'bpextended' ),
				'375' => __( '375 x 375 px', 'bpextended' ),
			);

			/**
			 * Filters the avatar_full_sizes select values.
			 *
			 * @since 1.0.0
			 *
			 * @param array $sizes Array of sizes to display in select field.
			 */
			return apply_filters( 'get_avatar_full_sizes', $sizes );

		break;
		case 'avatar_max_size_select' :

			$sizes = array(
				'320'  => __( '320 px', 'bpextended' ),
				'640'  => __( '640 px', 'bpextended' ),
				'960'  => __( '960 px', 'bpextended' ),
				'1280' => __( '1280 px', 'bpextended' ),
			);

			/**
			 * Filters the max_full_sizes select values.
			 *
			 * @since 1.0.0
			 *
			 * @param array $sizes Array of sizes to display in select field.
			 */
			return apply_filters( 'get_max_full_sizes', $sizes );

		break;

	}

	return array();
}

/**
 * Checks for valid email and signs user to newsletter.
 *
 * @since 1.0.0
 *
 * @param string $email Email.
 * @return bool
 */
function bpextender_newsletter_signup( $email ) {
	if ( is_email( $email ) ) {
		wp_remote_post( 'http://webdevstudios.us1.list-manage.com/subscribe/post?u=67169b098c99de702c897d63e&amp;id=9cb1c7472e&EMAIL=' . $email );
		return true;
	}
	return false;
}
