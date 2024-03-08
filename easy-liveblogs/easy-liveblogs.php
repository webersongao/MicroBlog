<?php
/*
Plugin Name: Easy Liveblogs
Plugin URI: https://vanrossum.dev
Description: Live blogging made easy with the Easy Liveblogs plugin from vanrossum.dev.
Version: 2.3.5
Author: Jeffrey van Rossum
Author URI: https://www.vanrossum.dev
Text Domain: easy-liveblogs
Domain Path: /languages
License: MIT

------------------------------------------------------------------------
Copyright 2023 vanrossum.dev, The Netherlands.
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'Easy_Liveblogs' ) ) {

	class Easy_Liveblogs {
		private static $instance = null;
		private $plugin_path;
		private $plugin_url;
		private $plugin_name    = 'Easy Liveblogs';
		private $plugin_version = '2.3.5';
		private $text_domain    = 'easy-liveblogs';

		/**
		 * @deprecated 2.0.0
		 */
		public $liveblog = null;

		/**
		 * Creates or returns an instance of this class.
		 */
		public static function instance() {
			if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Easy_Liveblogs ) ) {
				self::$instance = new Easy_Liveblogs();

				self::$instance->plugin_path = plugin_dir_path( __FILE__ );
				self::$instance->plugin_url  = plugin_dir_url( __FILE__ );

				self::$instance->define_constants();
				self::$instance->includes();

				add_action( 'plugins_loaded', array( self::$instance, 'load_textdomain' ) );
				add_action( 'wp_enqueue_scripts', array( self::$instance, 'register_styles' ) );
				add_action( 'admin_enqueue_scripts', array( self::$instance, 'register_styles' ) );
				add_action( 'wp_enqueue_scripts', array( self::$instance, 'register_scripts' ) );
				add_action( 'admin_enqueue_scripts', array( self::$instance, 'register_scripts' ) );
				add_action( 'init', array( self::$instance, 'setup_caching' ) );
				add_action( 'init', array( self::$instance, 'setup_api' ) );
			}

			return self::$instance;
		}

		/**
		 * Includes
		 */
		public function includes() {
			global $elb_options;

			require_once $this->get_plugin_path() . 'includes/admin/elb-register-settings.php';

			$elb_options = elb_get_settings();

			require_once $this->get_plugin_path() . 'includes/class-elb-liveblog.php';
			require_once $this->get_plugin_path() . 'includes/elb-post-types.php';
			require_once $this->get_plugin_path() . 'includes/elb-metabox.php';
			require_once $this->get_plugin_path() . 'includes/elb-functions.php';
			require_once $this->get_plugin_path() . 'includes/elb-shortcodes.php';
			require_once $this->get_plugin_path() . 'includes/elb-filters.php';
			require_once $this->get_plugin_path() . 'includes/elb-social-logos.php';
			require_once $this->get_plugin_path() . 'includes/admin/elb-pages.php';
			require_once $this->get_plugin_path() . 'includes/api/class-elb-feed-factory.php';
			require_once $this->get_plugin_path() . 'includes/api/class-elb-entry.php';
			require_once $this->get_plugin_path() . 'includes/api/class-elb-feed.php';
			require_once $this->get_plugin_path() . 'includes/caching/class-elb-transient.php';
			require_once $this->get_plugin_path() . 'includes/caching/class-elb-object.php';
		}

		/**
		 * Get plugin URL
		 */
		public function get_plugin_url() {
			return $this->plugin_url;
		}

		/**
		 * Get plugin path
		 */
		public function get_plugin_path() {
			return $this->plugin_path;
		}

		/**
		 * Get plugin version.
		 *
		 * @return string
		 */
		public function get_plugin_version() {
			if ( function_exists( 'wp_get_environment_type' ) && wp_get_environment_type() === 'development' ) {
				return time();
			}

			return $this->plugin_version;
		}

		/**
		 * Enqueue and register JavaScript files here.
		 */
		public function register_scripts() {
			if ( is_admin() ) {
				wp_register_script( 'selectize', $this->get_plugin_url() . 'assets/selectize/selectize.min.js', array( 'jquery' ), '0.12.4' );
				wp_register_script( 'elb-admin', $this->get_plugin_url() . 'assets/js/easy-liveblogs-admin.js', array( 'jquery', 'selectize' ), $this->get_plugin_version() );
			}

			if ( ! is_admin() ) {
				wp_register_script( 'elb', $this->get_plugin_url() . 'assets/js/easy-liveblogs.js', array( 'jquery' ), $this->get_plugin_version() );
				wp_localize_script(
					'elb',
					'elb',
					array(
						'datetime_format' => elb_get_option( 'entry_date_format', 'human' ),
						'locale'          => get_locale(),
						'interval'        => elb_get_update_interval(),
						'new_post_msg'    => __( 'There is %s update.', ELB_TEXT_DOMAIN ),
						'new_posts_msg'   => __( 'There are %s updates.', ELB_TEXT_DOMAIN ),
						'now_more_posts'  => __( "That's it.", ELB_TEXT_DOMAIN ),
					)
				);

				wp_enqueue_script( 'elb' );
			}
		}

		/**
		 * Enqueue and register CSS files here.
		 */
		public function register_styles() {

			if ( is_admin() ) {

				wp_register_style( 'selectize', $this->get_plugin_url() . 'assets/selectize/selectize.default.css', null, '0.12.4' );
				wp_register_style( 'elb-admin', $this->get_plugin_url() . 'assets/css/easy-liveblogs-admin.css', null, $this->get_plugin_version() );

				wp_enqueue_style( 'elb-admin' );

			} else {

				$theme = elb_get_theme();

				if ( $theme !== 'none' ) {
					wp_register_style( 'elb-theme-' . $theme, $this->get_plugin_url() . 'assets/css/themes/' . $theme . '.css', null, $this->get_plugin_version() );
				}

				wp_enqueue_style( 'elb-theme-' . $theme );
			}
		}

		/**
		 * Load textdomain
		 */
		public function load_textdomain() {
			$mofile = sprintf( '%1$s-%2$s.mo', ELB_TEXT_DOMAIN, get_locale() );

			// Check wp-content/languages/plugins/easy-liveblogs
			$mofile_global = WP_LANG_DIR . '/plugins/easy-liveblogs/' . $mofile;

			if ( file_exists( $mofile_global ) ) {
				load_textdomain( ELB_TEXT_DOMAIN, $mofile_global );
			} else {
				load_plugin_textdomain( ELB_TEXT_DOMAIN, false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
			}

		}

		/**
		 * Define Constants
		 */
		private function define_constants() {
			if ( ! defined( 'ELB_NAME' ) ) {
				define( 'ELB_NAME', $this->plugin_name );
			}
			if ( ! defined( 'ELB_PATH' ) ) {
				define( 'ELB_PATH', $this->get_plugin_path() );
			}
			if ( ! defined( 'ELB_URL' ) ) {
				define( 'ELB_URL', $this->get_plugin_url() );
			}
			if ( ! defined( 'ELB_VERSION' ) ) {
				define( 'ELB_VERSION', $this->plugin_version );
			}
			if ( ! defined( 'ELB_TEXT_DOMAIN' ) ) {
				define( 'ELB_TEXT_DOMAIN', $this->text_domain );
			}
		}

		/**
		 * Settings
		 */
		public function settings() {
			return elb_get_settings();
		}

		public function setup_api() {
			new EasyLiveblogs\API\Feed();
		}

		public function setup_caching() {
			$cache = elb_get_option( 'cache_enabled', false );

			if ( $cache == 'object' ) {
				EasyLiveblogs\Caching\ObjectCaching::init();
			} elseif ( $cache == 'transient' ) {
				EasyLiveblogs\Caching\TransientCaching::init();
			}
		}
	}
}

function ELB() {
	return Easy_Liveblogs::instance();
}

ELB();
