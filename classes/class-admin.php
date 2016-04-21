<?php
/**
 * BPExtender_Admin class
 *
 * @package BPExtenderAdmin
 * @subpackage BP_Extender
 * @author WebDevStudios
 * @since 1.0.0
 */

class BPExtender_Admin {

	/**
	 * Option key, and option page slug
	 *
	 * @var string
	 */
	private $key = 'bpext_options';

	/**
	 * Options page metabox id
	 *
	 * @var string
	 */
	private $metabox_id = 'bbpext_option_metabox';

	/**
	 * Options Page title
	 *
	 * @var string
	 */
	protected $title = '';

	/**
	 * Options Page hook
	 *
	 * @var string
	 */
	protected $options_page = '';

	/**
	 * Holds an instance of the object
	 *
	 * @var bpext_Admin
	 **/
	private static $instance = null;

	/**
	 * Constructor
	 *
	 * @since 1.0.0
	 */
	private function __construct() {
		// Set our title
		$this->title = __( 'BuddyExtender', 'bpextended' );
	}

	/**
	 * Returns the running object
	 *
	 * @return bpext_Admin
	 **/
	public static function get_instance() {
		if( is_null( self::$instance ) ) {
			self::$instance = new BPExtender_Admin();
			self::$instance->hooks();
		}
		return self::$instance;
	}

	/**
	 * Initiate our hooks
	 *
	 * @since 1.0.0
	 */
	public function hooks() {
		add_action( 'admin_init', array( $this, 'init' ) );
		add_action( 'admin_menu', array( $this, 'add_options_page' ) );
		add_action( 'cmb2_admin_init', array( $this, 'add_options_page_metabox' ) );
	}


	/**
	 * Register our setting to WP
	 *
	 * @since	1.0.0
	 */
	public function init() {
		register_setting( $this->key, $this->key );
	}

	/**
	 * Add menu options page
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
	 * Admin page markup. Mostly handled by CMB2
	 *
	 * @since	1.0.0
	 */
	public function admin_page_display() {
		wp_enqueue_style('ad-sidebar');
		?>
		<div class="wrap cmb2-options-page <?php echo $this->key; ?>">
			<h2><?php _e( 'BuddyExtender', 'bpextender' ); ?></h2>
			<div id="options-wrap">
				<?php bpext_products_sidebar(); ?>
				<?php cmb2_metabox_form( $this->metabox_id, $this->key ); ?>

			</div>

		</div>
		<?php
	}

	/**
	 * Add the options metabox to the array of metaboxes
	 *
	 * @since	1.0.0
	 */
	function add_options_page_metabox() {

		// hook in our save notices
		add_action( "cmb2_save_options-page_fields_{$this->metabox_id}", array( $this, 'settings_notices' ), 10, 2 );

		$cmb = new_cmb2_box( array(
			'id'			=> $this->metabox_id,
			'hookup'		=> false,
			'cmb_styles' 	=> false,
			'show_on'		=> array(
			// These are important don't remove.
				'key'	=> 'options-page',
				'value' => array( $this->key, ),
			),
		) );

		// ************* Avatar settings ***********************************************

		$cmb->add_field( array(
			'name' 	=> 'Avatar Settings',
			'desc' 	=> 'customize user avatar dimentions and defaults',
			'type' 	=> 'title',
			'id'	=> 'avatar_title',
		) );

		// Set our CMB2 fields.

		$cmb->add_field( array(
			'name'				=> 'Avatar Thumb Size',
			'desc'				=> 'Select an option',
			'id'				=> 'avatar_thumb_size_select',
			'type'				=> 'select',
			'show_option_none' 	=> false,
			'default'			=> '50',
			'options'			=> 'bpext_get_avatar_sizes',
		) );

		$cmb->add_field( array(
			'name'				=> 'Avatar Full Size',
			'desc'				=> 'Select an option',
			'id'				=> 'avatar_full_size_select',
			'type'				=> 'select',
			'show_option_none' 	=> false,
			'default'			=> '150',
			'options'			=> 'bpext_get_avatar_sizes',
		) );

		$cmb->add_field( array(
			'name'				=> 'Avatar Max Size',
			'desc'				=> 'Select an option',
			'id'				=> 'avatar_max_size_select',
			'type'				=> 'select',
			'show_option_none' 	=> false,
			'default'			=> '640',
			'options'			=> 'bpext_get_avatar_sizes',
		) );

		$cmb->add_field( array(
			'name'	=> 'Default User Avatar',
			'desc'	=> 'Upload an image that displays before a user has added a custom image.',
			'id'	=> 'avatar_default_image',
			'type'	=> 'file',
			'options' => array(
				'url' => false,
				'add_upload_file_text' => 'Add image',
			),
		) );

		// ************* Advanced settings ***********************************************

		$cmb->add_field( array(
			'name' 	=> 'Advanced Settings',
			'desc' 	=> 'Internal configuration settings. These settings can break your site.',
			'type' 	=> 'title',
			'id'	=> 'advanced_title',
		) );

		$cmb->add_field( array(
			'name' => 'Root Profiles',
			'desc' => 'Remove members slug from profile url.',
			'id'   => 'root_profiles_checkbox',
			'type' => 'checkbox',
		) );

		$cmb->add_field( array(
			'name' => 'Auto Group Join',
			'desc' => 'disable auto join when posting in a group.',
			'id'   => 'group_auto_join_checkbox',
			'type' => 'checkbox',
		) );

		$cmb->add_field( array(
			'name' => 'LDAP Usernames',
			'desc' => 'Enable support for LDAP usernames that include dots.',
			'id'   => 'ldap_username_checkbox',
			'type' => 'checkbox',
		) );

		$cmb->add_field( array(
			'name' => 'WYSIWYG Textarea',
			'desc' => 'Removes text editor from textarea profile field.',
			'id'   => 'wysiwyg_editor_checkbox',
			'type' => 'checkbox',
		) );

		$cmb->add_field( array(
			'name' => 'All Members Auto Complete',
			'desc' => 'All members instead of friends in messages auto complete.',
			'id'   => 'all_autocomplete_checkbox',
			'type' => 'checkbox',
		) );

		$cmb->add_field( array(
			'name' => 'Profile Fields Auto Link',
			'desc' => 'Disable autolinking in profile fields.',
			'id'   => 'profile_autolink_checkbox',
			'type' => 'checkbox',
		) );

		$cmb->add_field( array(
			'name' => 'User @ Mentions',
			'desc' => 'Disable User @ mentions.',
			'id'   => 'user_mentions_checkbox',
			'type' => 'checkbox',
		) );

		$cmb->add_field( array(
			'name' => 'Ignore Depricated Code',
			'desc' => 'Do not load depricated code',
			'id'   => 'depricated_code_checkbox',
			'type' => 'checkbox',
		) );


		// multisite settings here
		if( is_multisite() ) {

			$cmb->add_field( array(
				'name' 	=> 'Multisite Settings',
				'desc' 	=> 'These options display when BuddyPress is active on multisite',
				'type' 	=> 'title',
				'id'	=> 'network_title',
			) );

			$cmb->add_field( array(
				'name' => 'Enable Multiblog',
				'desc' => 'Allow BuddyPress to function on multiple blogs of a WPMU installation, not just on one root blog',
				'id'   => 'enable_multiblog_checkbox',
				'type' => 'checkbox',
			) );

			$cmb->add_field( array(
				'name'	=> 'Root Blog ID',
				'desc'	=> 'Enter blog ID BuddyPress will run on. Default ID is 1',
				'id'	=> 'root_blog_select',
				'type'	=> 'text',
			) );

		}

		$cmb->add_field( array(
			'name' 	=> 'General Settings',
			'desc' 	=> '',
			'type' 	=> 'title',
			'id'	=> 'general_title',
		) );

		$cmb->add_field( array(
			'name'	=> 'Pluginize Newsletter',
			'desc'	=> 'Enter an email to subscibe to the Pluginize newsletter',
			'id'	=> 'pluginize_newsletter',
			'type'	=> 'text',
			'sanitization_cb' => 'bpext_newsletter_signup',
		) );


	}

	/**
	 * Register settings notices for display
	 *
	 * @since	1.0.0
	 * @param	int	 $object_id Option key
	 * @param	array $updated	 Array of updated fields
	 * @return void
	 */
	public function settings_notices( $object_id, $updated ) {
		if ( $object_id !== $this->key || empty( $updated ) ) {
			return;
		}

		add_settings_error( $this->key . '-notices', '', __( 'Settings updated.', 'bpextender' ), 'updated' );
		settings_errors( $this->key . '-notices' );
	}

	/**
	 * Public getter method for retrieving protected/private variables
	 *
	 * @since 1.0.0
	 * @param string $field Field to retrieve
	 * @return mixed Field value or exception is thrown
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
 * Helper function to get/return the BPExtender_Admin object
 *
 * @since	1.0.0
 * @return bpext_Admin object
 */
function bpext_admin() {
	return BPExtender_Admin::get_instance();
}

/**
 * Wrapper function around cmb2_get_option
 * @since	1.0.0
 * @param	string	$key Options array key
 * @return mixed				Option value
 */
function bpext_get_option( $key = '' ) {
	return cmb2_get_option( bpext_admin()->key, $key );
}

// Get it started
bpext_admin();

/**
 * bpext_get_avatar_sizes returns various select options for avatar sizes
 * @param	array $field
 * @return array
 */
function bpext_get_avatar_sizes( $field ) {

	$field_id = $field->args['id'];

	switch ( $field_id ) {
		case 'avatar_thumb_size_select' :

			$sizes = array(
					'25' 	=> __( '25px x 25px', 'bpextender' ),
					'50' 	=> __( '50px x 50px', 'bpextender' ),
					'75'	=> __( '75px x 75px', 'bpextender' ),
					'100'	=> __( '100px x 100px', 'bpextender' ),
					'125' 	=> __( '125px x 125px', 'bpextender' ),
					'150'	=> __( '150px x 150px', 'bpextender' ),
					'175'	=> __( '175px x 175px', 'bpextender' ),
					'200' 	=> __( '200px x 200px', 'bpextender' ),
					'225'	=> __( '225px x 225px', 'bpextender' ),
					'250'	=> __( '250px x 250px', 'bpextender' ),
					'275' 	=> __( '275px x 275px', 'bpextender' ),
					'300'	=> __( '300px x 300px', 'bpextender' ),
			);

			return apply_filters( 'get_avatar_thumb_sizes', $sizes );

		break;
		case 'avatar_full_size_select' :

			$sizes = array(
					'100'	=> __( '100px x 100px', 'bpextender' ),
					'125' 	=> __( '125px x 125px', 'bpextender' ),
					'150'	=> __( '150px x 150px', 'bpextender' ),
					'175'	=> __( '175px x 175px', 'bpextender' ),
					'200' 	=> __( '200px x 200px', 'bpextender' ),
					'225'	=> __( '225px x 225px', 'bpextender' ),
					'250'	=> __( '250px x 250px', 'bpextender' ),
					'275' 	=> __( '275px x 275px', 'bpextender' ),
					'300'	=> __( '300px x 300px', 'bpextender' ),
					'325'	=> __( '300px x 300px', 'bpextender' ),
					'350'	=> __( '300px x 300px', 'bpextender' ),
					'375'	=> __( '375px x 375px', 'bpextender' ),
			);

			return apply_filters( 'get_avatar_full_sizes', $sizes );

		break;
		case 'avatar_max_size_select' :

			$sizes = array(
					'320'	 => __( '320px', 'bpextender' ),
					'640'	 => __( '640px', 'bpextender' ),
					'960'	 => __( '960px', 'bpextender' ),
					'1280'	 => __( '1280px', 'bpextender' ),
			);

			return apply_filters( 'get_max_full_sizes', $sizes );

		break;

	}

}

function bpext_newsletter_signup( $email ) {
	if ( is_email( $email ) ) {
		wp_remote_post( 'http://webdevstudios.us1.list-manage.com/subscribe/post?u=67169b098c99de702c897d63e&amp;id=9cb1c7472e&EMAIL=' . $email );
	}
	return false;
}
