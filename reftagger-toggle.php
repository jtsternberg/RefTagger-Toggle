<?php
/**
 * Plugin Name: RefTagger Toggle
 * Plugin URI:  http://dsgnwrks.pro
 * Description: Allows disabling Reftagger on a per-page/post basis
 * Version:     0.1.0
 * Author:      Justin Sternberg
 * Author URI:  http://dsgnwrks.pro
 * Donate link: http://dsgnwrks.pro
 * License:     GPLv2
 * Text Domain: reftagger-toggle
 * Domain Path: /languages
 *
 * @link http://dsgnwrks.pro
 *
 * @package RefTagger Toggle
 * @version 0.1.0
 */

/**
 * Copyright (c) 2016 Justin Sternberg (email : justin@dsgnwrks.pro)
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
 * Main initiation class
 *
 * @since  0.1.0
 */
final class RefTagger_Toggle {

	/**
	 * Current version
	 *
	 * @var  string
	 * @since  0.1.0
	 */
	const VERSION = '0.1.0';

	/**
	 * URL of plugin directory
	 *
	 * @var string
	 * @since  0.1.0
	 */
	protected $url = '';

	/**
	 * Path of plugin directory
	 *
	 * @var string
	 * @since  0.1.0
	 */
	protected $path = '';

	/**
	 * Plugin basename
	 *
	 * @var string
	 * @since  0.1.0
	 */
	protected $basename = '';

	/**
	 * Singleton instance of plugin
	 *
	 * @var RefTagger_Toggle
	 * @since  0.1.0
	 */
	protected static $single_instance = null;

	/**
	 * Creates or returns an instance of this class.
	 *
	 * @since  0.1.0
	 * @return RefTagger_Toggle A single instance of this class.
	 */
	public static function get_instance() {
		if ( null === self::$single_instance ) {
			self::$single_instance = new self();
		}

		return self::$single_instance;
	}

	/**
	 * Sets up our plugin
	 *
	 * @since  0.1.0
	 */
	protected function __construct() {
		$this->basename = plugin_basename( __FILE__ );
		$this->url      = plugin_dir_url( __FILE__ );
		$this->path     = plugin_dir_path( __FILE__ );
	}

	/**
	 * Attach other plugin classes to the base plugin class.
	 *
	 * @since  0.1.0
	 * @return void
	 */
	public function plugin_classes() {
		// Attach other plugin classes to the base plugin class.
		// $this->plugin_class = new RTT_Plugin_Class( $this );
	} // END OF PLUGIN CLASSES FUNCTION

	/**
	 * Add hooks and filters
	 *
	 * @since  0.1.0
	 * @return void
	 */
	public function hooks() {

		add_action( 'init', array( $this, 'init' ) );
	}

	/**
	 * Activate the plugin
	 *
	 * @since  0.1.0
	 * @return void
	 */
	public function _activate() {
		// Make sure any rewrite functionality has been loaded.
		flush_rewrite_rules();
	}

	/**
	 * Deactivate the plugin
	 * Uninstall routines should be in uninstall.php
	 *
	 * @since  0.1.0
	 * @return void
	 */
	public function _deactivate() {}

	/**
	 * Init hooks
	 *
	 * @since  0.1.0
	 * @return void
	 */
	public function init() {
		if ( $this->check_requirements() ) {
			load_plugin_textdomain( 'reftagger-toggle', false, dirname( $this->basename ) . '/languages/' );
			add_action( 'cmb2_init', array( $this, 'add_option_to_maybe_disable_reftagger_option' ) );
		}
	}

	/**
	 * If refTagger is installed, add option to disable it.
	 *
	 * @since 0.1.0
	 *
	 * @return void
	 */
	public function add_option_to_maybe_disable_reftagger_option() {
		// only if reftagger plugin is installed.
		if ( function_exists( 'lbsFooter' ) ) {
			if ( is_admin() ) {
				$this->add_maybe_disable_reftagger_option();
			} else {
				add_action( 'wp_footer', array( $this, 'maybe_disable_reftagger' ), 8 );
			}
		}
	}

	/**
	 * Using CMB2, allow them to disable reftagger on certain pages/posts/etc.
	 *
	 * @since 0.1.0
	 *
	 * @return void
	 */
	function add_maybe_disable_reftagger_option() {

		$post_types = get_post_types( array( 'public' => true ) );
		unset( $post_types['attachment'] );

		$cmb = new_cmb2_box( array(
			'title'        => __( 'Disable RefTagger?', 'reftagger-toggle' ),
			'id'           => 'disable_reftagger',
			'object_types' => $post_types,
			'show_names'   => false,
			'context'      => 'side',
		) );

		$cmb->add_field( array(
			'desc' => '
			' . __( 'Click to disable on this page/post/etc.', 'reftagger-toggle' ) . '
			<br><br>
			<div style="margin-bottom: -15px;"><a href="'. admin_url( 'options-general.php?page=reftagger%2FRefTagger.php' ) .'">' . __( 'RefTagger options', 'reftagger-toggle' ) . '</a> | <a href="https://reftagger.com/wordpress-tutorial/">' . __( 'RefTagger website', 'reftagger-toggle' ) . '</a></div>
			',
			'id'   => '_disable_reftagger',
			'type' => 'checkbox',
		) );
	}

	/**
	 * Hooked to wp_footer, will disable refTagger if requested.
	 *
	 * @since  0.1.0
	 *
	 * @return void
	 */
	public function maybe_disable_reftagger() {
		if ( get_the_id() && get_post_meta( get_the_id(), '_disable_reftagger', 1 ) ) {
			remove_action( 'wp_footer', 'lbsFooter' );
		}
	}

	/**
	 * Check if the plugin meets requirements and
	 * disable it if they are not present.
	 *
	 * @since  0.1.0
	 * @return boolean result of meets_requirements
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
	 * @since  0.1.0
	 * @return void
	 */
	public function deactivate_me() {
		deactivate_plugins( $this->basename );
	}

	/**
	 * Check that all plugin requirements are met
	 *
	 * @since  0.1.0
	 * @return boolean True if requirements are met.
	 */
	public static function meets_requirements() {
		return defined( 'CMB2_LOADED' ) && function_exists( 'lbsFooter' );
	}

	/**
	 * Adds a notice to the dashboard if the plugin requirements are not met
	 *
	 * @since  0.1.0
	 * @return void
	 */
	public function requirements_not_met_notice() {
		// Output our error.
		echo '<div id="message" class="error">';
		echo '<p>' . sprintf( __( 'RefTagger Toggle requires the <a href="https://wordpress.org/plugins/cmb2/">CMB2 plugin</a>, and the <a href="https://wordpress.org/plugins/reftagger/">Reftagger plugin</a>, so it has been <a href="%s">deactivated</a>.', 'reftagger-toggle' ), admin_url( 'plugins.php' ) ) . '</p>';
		echo '</div>';
	}

	/**
	 * Magic getter for our object.
	 *
	 * @since  0.1.0
	 * @param string $field Field to get.
	 * @throws Exception Throws an exception if the field is invalid.
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
}

/**
 * Grab the RefTagger_Toggle object and return it.
 * Wrapper for RefTagger_Toggle::get_instance()
 *
 * @since  0.1.0
 * @return RefTagger_Toggle  Singleton instance of plugin class.
 */
function reftagger_toggle() {
	return RefTagger_Toggle::get_instance();
}

// Kick it off.
add_action( 'plugins_loaded', array( reftagger_toggle(), 'hooks' ) );

register_activation_hook( __FILE__, array( reftagger_toggle(), '_activate' ) );
register_deactivation_hook( __FILE__, array( reftagger_toggle(), '_deactivate' ) );
