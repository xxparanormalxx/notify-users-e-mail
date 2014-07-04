<?php
/**
 * Notify Users E-Mail.
 *
 * @package   Notify_Users_EMail_Admin
 * @author    Valerio Souza <eu@valeriosouza.com.br>
 * @license   GPL-2.0+
 * @link      http://wordpress.org/plugins/notify-users-e-mail/
 * @copyright 2013 CodeHost
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Notify Users E-Mail admin class.
 *
 * @package Notify_Users_EMail_Admin
 * @author  Valerio Souza <eu@valeriosouza.com.br>
 */
class Notify_Users_EMail_Admin {

	/**
	 * Initialize the plugin by loading admin scripts & styles and adding a
	 * settings page and menu.
	 */
	public function __construct() {
		$plugin              = Notify_Users_EMail::get_instance();
		$this->settings_name = $plugin->get_settings_name();

		// Add the options page and menu item.
		add_action( 'admin_menu', array( $this, 'add_plugin_admin_menu' ) );

		// Init plugin options.
		add_action( 'admin_init', array( $this, 'plugin_settings' ) );

		// Admin scripts.
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts' ) );

		// Add an action link pointing to the options page.
		$plugin_basename = plugin_basename( plugin_dir_path( __DIR__ ) . 'notify-users-e-mail' . '.php' );
		add_filter( 'plugin_action_links_' . $plugin_basename, array( $this, 'add_action_links' ) );
	}

	/**
	 * Load admin scripts.
	 *
	 * @return void
	 */
	public function admin_scripts( $hook ) {
		// Checks if is the settings page.
		if ( 'toplevel_page_notify-users-e-mail' == $hook ) {
			// Media Upload.
			wp_enqueue_media();

			// Theme Options.
			wp_enqueue_style( 'notify-users-e-mail-admin', plugins_url( 'assets/css/admin.css', plugin_dir_path( __FILE__ ) ), array(), Notify_Users_EMail::VERSION, 'all' );
			wp_enqueue_script( 'notify-users-e-mail-admin', plugins_url( 'assets/js/admin.js', plugin_dir_path( __FILE__ ) ), array( 'jquery' ), Notify_Users_EMail::VERSION, true );

			// Localize strings.
			wp_localize_script(
				'notify-users-e-mail-admin',
				'notify_users_e_mail_params',
				array(
					'uploadTitle'   => __( 'Choose a file', 'notify-users-e-mail' ),
					'uploadButton'  => __( 'Add file', 'notify-users-e-mail' ),
				)
			);
		}
	}

	/**
	 * Register the administration menu for this plugin into the WordPress Dashboard menu.
	 *
	 * @return   void
	 */
	public function add_plugin_admin_menu() {
		add_menu_page(
			__( 'Post Notification by Email', 'notify-users-e-mail' ),
			__( 'Post Notification by Email', 'notify-users-e-mail' ),
			'manage_options',
			'notify-users-e-mail',
			array( $this, 'display_plugin_admin_page' )
		);
	}

	/**
	 * Render the settings page for this plugin.
	 *
	 * @return void
	 */
	public function display_plugin_admin_page() {
		$settings_name = $this->settings_name;

		include_once 'views/admin.php';
	}

	/**
	 * Plugin settings form fields.
	 *
	 * @return void
	 */
	public function plugin_settings() {
		$settings_section = 'settings_section';
		$placeholders_description = sprintf(
			__( '%s You can use the following placeholders:%s %s', 'notify-users-e-mail' ),
			'<p>',
			'</p>',
			sprintf(
				'<ul><li><p><code>{title}</code> %s</p></li><li><p><code>{link}</code> %s</p></li><li><p><code>{date}</code> %s</p></li></ul>',
				__( 'to display the title', 'notify-users-e-mail' ),
				__( 'to display the URL', 'notify-users-e-mail' ),
				__( 'to display the date of publication', 'notify-users-e-mail' )
			)
		);

		// Set the settings section.
		add_settings_section(
			$settings_section,
			__( 'Email Settings', 'notify-users-e-mail' ),
			'__return_false',
			$this->settings_name
		);

		// Sent to.
		add_settings_field(
			'send_to',
			__( 'Sent to', 'notify-users-e-mail' ),
			array( $this, 'text_callback' ),
			$this->settings_name,
			$settings_section,
			array(
				'id'          => 'send_to',
				'description' => sprintf( '<p>' . __( 'Enter with the recipients for the email (separated by commas).', 'notify-users-e-mail' ) . '</p>' ),
				'default'     => ''
			)
		);

		// Send to users.
		add_settings_field(
			'send_to_users',
			__( 'Send to users', 'notify-users-e-mail' ),
			array( $this, 'users_callback' ),
			$this->settings_name,
			$settings_section,
			array(
				'id'          => 'send_to_users',
				'description' => '<p>' . __( 'Select the type of user that will receive notifications.', 'notify-users-e-mail' ) . '</p>',
				'default'     => array()
			)
		);

		// Email Subject Post.
		add_settings_field(
			'subject_post',
			__( 'Subject to Post', 'notify-users-e-mail' ),
			array( $this, 'text_callback' ),
			$this->settings_name,
			$settings_section,
			array(
				'id'          => 'subject_post',
				'description' => $placeholders_description,
				'default'     => ''
			)
		);

		// Email Body Prefix Post.
		add_settings_field(
			'body_post',
			__( 'Body to Post', 'notify-users-e-mail' ),
			array( $this, 'editor_callback' ),
			$this->settings_name,
			$settings_section,
			array(
				'id'          => 'body_post',
				'description' => $placeholders_description,
				'default'     => ''
			)
		);

		// Email Subject Page.
		add_settings_field(
			'subject_page',
			__( 'Subject to Page', 'notify-users-e-mail' ),
			array( $this, 'text_callback' ),
			$this->settings_name,
			$settings_section,
			array(
				'id'          => 'subject_page',
				'description' => $placeholders_description,
				'default'     => ''
			)
		);

		// Email Body Prefix Page.
		add_settings_field(
			'body_page',
			__( 'Body to Page', 'notify-users-e-mail' ),
			array( $this, 'editor_callback' ),
			$this->settings_name,
			$settings_section,
			array(
				'id'          => 'body_page',
				'description' => $placeholders_description,
				'default'     => ''
			)
		);

		// Email Subject Comment.
		add_settings_field(
			'subject_comment',
			__( 'Subject to comment', 'notify-users-e-mail' ),
			array( $this, 'text_callback' ),
			$this->settings_name,
			$settings_section,
			array(
				'id'          => 'subject_comment',
				'description' => $placeholders_description,
				'default'     => ''
			)
		);

		// Email Body Prefix Comment.
		add_settings_field(
			'body_comment',
			__( 'Body to Comment', 'notify-users-e-mail' ),
			array( $this, 'editor_callback' ),
			$this->settings_name,
			$settings_section,
			array(
				'id'          => 'body_comment',
				'description' => $placeholders_description,
				'default'     => ''
			)
		);

		add_settings_field(
			'txt_footer',
			__( 'Text Footer', 'notify-users-e-mail' ),
			array( $this, 'editor_callback' ),
			$this->settings_name,
			$settings_section,
			array(
				'id'          => 'txt_footer',
				'description' => $placeholders_description,
				'default'     => ''
			)
		);

		// Register settings.
		register_setting( $this->settings_name, $this->settings_name, array( $this, 'validate_options' ) );
	}

	/**
	 * Get option value.
	 *
	 * @param  string $id      Option ID.
	 * @param  string $default Default option.
	 *
	 * @return array           Option value.
	 */
	protected function get_option_value( $id, $default = '' ) {
		$options = get_option( $this->settings_name );

		if ( isset( $options[ $id ] ) ) {
			$default = $options[ $id ];
		}

		return $default;
	}

	/**
	 * Users field callback.
	 *
	 * @param  array $args Arguments from the option.
	 *
	 * @return string      Input field HTML.
	 */
	public function users_callback( $args ) {
		$id       = $args['id'];
		$wp_roles = new WP_Roles();
		$roles    = $wp_roles->get_names();

		// Sets current option.
		$current = $this->get_option_value( $id, $args['default'] );

		$html = sprintf( '<select id="%1$s" name="%2$s[%1$s][]" multiple="multiple">', $id, $this->settings_name );
		foreach ( $roles as $role_value => $role_name ) {
			$current_item = in_array( $role_value, $current ) ? ' selected="selected"' : '';
			$html .= sprintf( '<option value="%s"%s>%s</option>', $role_value, $current_item, $role_name );
		}

		$html .= '</select>';

		// Displays the description.
		if ( $args['description'] ) {
			$html .= sprintf( '<div class="description">%s</div>', $args['description'] );
		}

		echo $html;
	}

	/**
	 * Text field callback.
	 *
	 * @param  array $args Arguments from the option.
	 *
	 * @return string      Text input field HTML.
	 */
	public function text_callback( $args ) {
		$id = $args['id'];

		// Sets current option.
		$current = esc_html( $this->get_option_value( $id, $args['default'] ) );
		$html   = sprintf( '<input type="text" id="%1$s" name="%2$s[%1$s]" value="%3$s" class="regular-text" />', $id, $this->settings_name, $current );

		// Displays the description.
		if ( $args['description'] ) {
			$html .= sprintf( '<div class="description">%s</div>', $args['description'] );
		}

		echo $html;
	}

	/**
	 * Editor field callback.
	 *
	 * @param  array $args Arguments from the option.
	 *
	 * @return string      Editor field HTML.
	 */
	public function editor_callback( $args ) {
		$id = $args['id'];

		// Sets current option.
		$current = $this->get_option_value( $id, $args['default'] );

		echo '<div style="width: 600px;">';

			wp_editor( $current, $id, array( 'textarea_name' => $this->settings_name . '[' . $id . ']', 'textarea_rows' => 10 ) );

		echo '</div>';

		// Displays the description.
		if ( $args['description'] ) {
			echo sprintf( '<div class="description">%s</div>', $args['description'] );
		}
	}

	/**
	 * Image field callback.
	 *
	 * @param array $args Arguments from the option.
	 *
	 * @return string Image field HTML.
	 */
	public function image_callback( $args ) {
		$id = $args['id'];

		// Sets current option.
		$current = esc_html( $this->get_option_value( $id, $args['default'] ) );

		// Gets placeholder image.
		$image = 'data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7';
		$html  = '<div class="notify-users-e-mail-upload-image">';
		$html .= '<span class="notify-users-e-mail-default-image">' . $image . '</span>';

		if ( $current ) {
			$image = wp_get_attachment_image_src( $current, 'medium' );
			$image = $image[0];
		}

		$html .= sprintf( '<input id="%1$s" name="%2$s[%1$s]" type="hidden" class="notify-users-e-mail-image" value="%3$s" /><div class="notify-users-e-mail-preview-wrap"><img src="%4$s" class="notify-users-e-mail-preview" style="max-height: 150px; width: auto;" alt="" /><ul class="notify-users-e-mail-actions"><li><a href="#" class="notify-users-e-mail-delete" title="%6$s"><span class="dashicons dashicons-no"></span></a></li></ul></div><input id="%1$s-button" class="button" type="button" value="%5$s" />', $id, $this->settings_name, $current, $image, __( 'Select image', 'notify-users-e-mail' ), __( 'Remove image', 'notify-users-e-mail' ) );

		$html .= '<br class="clear" />';
		$html .= '</div>';

		// Displays the description.
		if ( $args['description'] ) {
			$html .= sprintf( '<p class="description">%s</p>', $args['description'] );
		}

		echo $html;
	}

	/**
	 * Valid options.
	 *
	 * @param  array $input Options to valid.
	 *
	 * @return array        Validated options.
	 */
	public function validate_options( $input ) {
		$output = array();

		foreach ( $input as $key => $value ) {
			if ( isset( $input[ $key ] ) ) {
				if ( 'send_to_users' == $key ) {
					$send_to_users = array();
					foreach ( $input[ $key ] as $value ) {
						$send_to_users[] = sanitize_text_field( $value );
					}
					$output[ $key ] = $send_to_users;
				} else {
					$output[ $key ] = $input[ $key ];
				}
			}
		}

		return $output;
	}

	/**
	 * Add settings action link to the plugins page.
	 */
	public function add_action_links( $links ) {
		return array_merge(
			array(
				'settings' => '<a href="' . admin_url( 'options-general.php?page=notify-users-e-mail' ) . '">' . __( 'Settings', 'notify-users-e-mail' ) . '</a>'
			),
			$links
		);
	}
}

new Notify_Users_EMail_Admin();
