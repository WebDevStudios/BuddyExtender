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
		$this->title = esc_html__( 'BuddyExtender', 'buddyextender' );
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
		add_action( 'admin_init', [ $this, 'init' ] );
		add_action( 'admin_menu', [ $this, 'add_options_page' ] );
		add_action( 'cmb2_admin_init', [ $this, 'add_options_page_metabox' ] );
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
			[ $this, 'admin_page_display' ]
		);

		// Include CMB CSS in the head to avoid FOUC.
		add_action( "admin_print_styles-{$this->options_page}", [ 'CMB2_hookup', 'enqueue_cmb_css' ] );
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
			<h2><?php esc_attr_e( 'BuddyExtender', 'buddyextender' ); ?></h2>
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

		add_action( "cmb2_save_options-page_fields_{$this->metabox_id}", [ $this, 'settings_notices' ], 10, 2 );

		$cmb = new_cmb2_box( [
			'id'         => $this->metabox_id,
			'hookup'     => false,
			'cmb_styles' => false,
			'show_on'    => [
				'key'   => 'options-page',
				'value' => [ $this->key ],
			],
		] );

		// ************* Avatar settings ***********************************************
		$cmb->add_field( [
			'name' => esc_html__( 'Avatar Settings', 'buddyextender' ),
			'desc' => esc_html__( 'Customize user avatar dimentions and defaults', 'buddyextender' ),
			'type' => 'title',
			'id'   => 'avatar_title',
		] );

		// Set our CMB2 fields.
		$cmb->add_field( [
			'name'             => esc_html__( 'Avatar Thumb Size', 'buddyextender' ),
			'desc'             => esc_html__( 'Changes user and group avatar to selected dimensions in activity, members and group lists.', 'buddyextender' ),
			'id'               => 'avatar_thumb_size_select',
			'type'             => 'select',
			'show_option_none' => false,
			'default'          => '50',
			'options'          => 'bpextender_get_avatar_sizes',
		] );

		$cmb->add_field( [
			'name'             => esc_html__( 'Avatar Full Size', 'buddyextender' ),
			'desc'             => esc_html__( 'Changes user and group avatar to dimensions in user and group header.', 'buddyextender' ),
			'id'               => 'avatar_full_size_select',
			'type'             => 'select',
			'show_option_none' => false,
			'default'          => '150',
			'options'          => 'bpextender_get_avatar_sizes',
		] );

		$cmb->add_field( [
			'name'             => esc_html__( 'Avatar Max Size', 'buddyextender' ),
			'desc'             => esc_html__( 'Changes maximum image size a user can uplaod for avatars.', 'buddyextender' ),
			'id'               => 'avatar_max_size_select',
			'type'             => 'select',
			'show_option_none' => false,
			'default'          => '640',
			'options'          => 'bpextender_get_avatar_sizes',
		] );

		$cmb->add_field( [
			'name'    => esc_html__( 'Default User Avatar', 'buddyextender' ),
			'desc'    => esc_html__( 'Upload an image that displays before a user has added a custom image.', 'buddyextender' ),
			'id'      => 'avatar_default_image',
			'type'    => 'file',
			'options' => [
				'url'                  => false,
				'add_upload_file_text' => 'Add image',
			],
		] );

		// ************* Advanced settings ***********************************************
		$cmb->add_field( [
			'name' => esc_html__( 'Advanced Settings', 'buddyextender' ),
			'desc' => esc_html__( 'Internal configuration settings. Please make sure to check site after changing these options.', 'buddyextender' ),
			'type' => 'title',
			'id'   => 'advanced_title',
		] );

		$cmb->add_field( [
			'name' => esc_html__( 'Root Profiles', 'buddyextender' ),
			'desc' => esc_html__( 'Remove members slug from profile url.', 'buddyextender' ),
			'id'   => 'root_profiles_checkbox',
			'type' => 'checkbox',
		] );

		$cmb->add_field( [
			'name' => esc_html__( 'Auto Group Join', 'buddyextender' ),
			'desc' => esc_html__( 'disable auto join when posting in a group.', 'buddyextender' ),
			'id'   => 'group_auto_join_checkbox',
			'type' => 'checkbox',
		] );

		$cmb->add_field( [
			'name' => esc_html__( 'LDAP Usernames', 'buddyextender' ),
			'desc' => esc_html__( 'Enable support for LDAP usernames that include dots.', 'buddyextender' ),
			'id'   => 'ldap_username_checkbox',
			'type' => 'checkbox',
		] );

		$cmb->add_field( [
			'name' => esc_html__( 'WYSIWYG Textarea', 'buddyextender' ),
			'desc' => esc_html__( 'Removes text editor from textarea profile field.', 'buddyextender' ),
			'id'   => 'wysiwyg_editor_checkbox',
			'type' => 'checkbox',
		] );

		$cmb->add_field( [
			'name' => esc_html__( 'All Members Auto Complete', 'buddyextender' ),
			'desc' => esc_html__( 'Auto-complete all members instead of just friends in messages.', 'buddyextender' ),
			'id'   => 'all_autocomplete_checkbox',
			'type' => 'checkbox',
		] );

		$cmb->add_field( [
			'name' => esc_html__( 'Profile Fields Auto Link', 'buddyextender' ),
			'desc' => esc_html__( 'Disable autolinking in profile fields.', 'buddyextender' ),
			'id'   => 'profile_autolink_checkbox',
			'type' => 'checkbox',
		] );

		$cmb->add_field( [
			'name' => esc_html__( 'User @ Mentions', 'buddyextender' ),
			'desc' => esc_html__( 'Disable User @ mentions.', 'buddyextender' ),
			'id'   => 'user_mentions_checkbox',
			'type' => 'checkbox',
		] );

		$cmb->add_field( [
			'name' => esc_html__( 'Ignore Depricated Code', 'buddyextender' ),
			'desc' => esc_html__( 'Do not load depricated code', 'buddyextender' ),
			'id'   => 'depricated_code_checkbox',
			'type' => 'checkbox',
		] );

		// Multisite settings here.
		if ( is_multisite() ) {

			$cmb->add_field( [
				'name' => esc_html__( 'Multisite Settings', 'buddyextender' ),
				'desc' => esc_html__( 'These options display when BuddyPress is active on multisite', 'buddyextender' ),
				'type' => 'title',
				'id'   => 'network_title',
			] );

			$cmb->add_field( [
				'name' => 'Enable Multiblog',
				'desc' => esc_html__( 'Allow BuddyPress to function on multiple blogs of a WPMU installation, not just on one root blog', 'buddyextender' ),
				'id'   => 'enable_multiblog_checkbox',
				'type' => 'checkbox',
			] );

			$cmb->add_field( [
				'name' => esc_html__( 'Root Blog ID', 'buddyextender' ),
				'desc' => esc_html__( 'Enter blog ID BuddyPress will run on. Default ID is 1', 'buddyextender' ),
				'id'   => 'root_blog_select',
				'type' => 'text',
			] );

		}
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

		add_settings_error( $this->key . '-notices', '', esc_html__( 'Settings updated.', 'buddyextender' ), 'updated' );
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
		if ( in_array( $field, [ 'key', 'metabox_id', 'title', 'options_page' ], true ) ) {
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

			$sizes = [
				'25'  => esc_html__( '25 x 25 px', 'buddyextender' ),
				'50'  => esc_html__( '50 x 50 px', 'buddyextender' ),
				'75'  => esc_html__( '75 x 75 px', 'buddyextender' ),
				'100' => esc_html__( '100 x 100 px', 'buddyextender' ),
				'125' => esc_html__( '125 x 125 px', 'buddyextender' ),
				'150' => esc_html__( '150 x 150 px', 'buddyextender' ),
				'175' => esc_html__( '175 x 175 px', 'buddyextender' ),
				'200' => esc_html__( '200 x 200 px', 'buddyextender' ),
				'225' => esc_html__( '225 x 225 px', 'buddyextender' ),
				'250' => esc_html__( '250 x 250 px', 'buddyextender' ),
				'275' => esc_html__( '275 x 275 px', 'buddyextender' ),
				'300' => esc_html__( '300 x 300 px', 'buddyextender' ),
			];

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

			$sizes = [
				'100' => esc_html__( '100 x 100 px', 'buddyextender' ),
				'125' => esc_html__( '125 x 125 px', 'buddyextender' ),
				'150' => esc_html__( '150 x 150 px', 'buddyextender' ),
				'175' => esc_html__( '175 x 175 px', 'buddyextender' ),
				'200' => esc_html__( '200 x 200 px', 'buddyextender' ),
				'225' => esc_html__( '225 x 225 px', 'buddyextender' ),
				'250' => esc_html__( '250 x 250 px', 'buddyextender' ),
				'275' => esc_html__( '275 x 275 px', 'buddyextender' ),
				'300' => esc_html__( '300 x 300 px', 'buddyextender' ),
				'325' => esc_html__( '325 x 325 px', 'buddyextender' ),
				'350' => esc_html__( '350 x 350 px', 'buddyextender' ),
				'375' => esc_html__( '375 x 375 px', 'buddyextender' ),
			];

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

			$sizes = [
				'320'  => esc_html__( '320 px', 'buddyextender' ),
				'640'  => esc_html__( '640 px', 'buddyextender' ),
				'960'  => esc_html__( '960 px', 'buddyextender' ),
				'1280' => esc_html__( '1280 px', 'buddyextender' ),
			];

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

	return [];
}
