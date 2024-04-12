<?php
/*
------------------------------------------------------------------------
Copyright 2023 WebersonGao.com, The Doumao Team.
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'Micro_Liveblog' ) ) {

	class Micro_Liveblog {
		private static $instance = null;
		private $plugin_path;
		private $plugin_url;
		private $plugin_version = '2.3.5';

		/**
		 * @deprecated 2.0.0
		 */
		public $liveblog = null;

		/**
		 * Creates or returns an instance of this class.
		 */
		public static function instance() {
			if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Micro_Liveblog ) ) {
				self::$instance = new Micro_Liveblog();

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
			global $mlb_options;

			require_once mbfun_get_plugin_path() . 'admin/live-settings-section.php';

			$mlb_options = mlb_get_settings();

			require_once $this->get_plugin_path() . 'includes/class-liveblog.php';
			require_once $this->get_plugin_path() . 'includes/live-post-types.php';
			require_once $this->get_plugin_path() . 'includes/live-metabox.php';
			require_once $this->get_plugin_path() . 'includes/live-functions.php';
			require_once $this->get_plugin_path() . 'includes/live-shortcodes.php';
			require_once $this->get_plugin_path() . 'includes/live-filters.php';
			require_once $this->get_plugin_path() . 'includes/live-social-logos.php';
			require_once $this->get_plugin_path() . 'includes/api/class-feedfactory.php';
			require_once $this->get_plugin_path() . 'includes/api/class-liveentry.php';
			require_once $this->get_plugin_path() . 'includes/api/class-livefeed.php';
			require_once $this->get_plugin_path() . 'includes/caching/class-live-transient.php';
			require_once $this->get_plugin_path() . 'includes/caching/class-live-object.php';
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
				wp_register_script( 'mlb-admin', $this->get_plugin_url() . 'assets/js/liveblog-admin.js', array( 'jquery', 'selectize' ), $this->get_plugin_version() );
			}

			if ( ! is_admin() ) {
				wp_register_script( 'micro_lb', $this->get_plugin_url() . 'assets/js/liveblog.js', array( 'jquery' ), $this->get_plugin_version() );
				wp_localize_script(
					'micro_lb',
					'micro_lb',
					array(
						'datetime_format' => mlb_get_option( 'entry_date_format', 'human' ),
						'locale'          => get_locale(),
						'interval'        => mlb_get_update_interval(),
						'new_post_msg'    => __( 'There is %s update.', MICROBLOG_DOMAIN ),
						'new_posts_msg'   => __( 'There are %s updates.', MICROBLOG_DOMAIN ),
						'now_more_posts'  => __( "That's All.", MICROBLOG_DOMAIN ),
					)
				);

				wp_enqueue_script( 'micro_lb' );
			}
		}

		/**
		 * Enqueue and register CSS files here.
		 */
		public function register_styles() {

			if ( is_admin() ) {

				wp_register_style( 'selectize', $this->get_plugin_url() . 'assets/selectize/selectize.default.css', null, '0.12.4' );
				wp_register_style( 'mlb-admin', $this->get_plugin_url() . 'assets/css/liveblog-admin.css', null, $this->get_plugin_version() );

				wp_enqueue_style( 'mlb-admin' );

			} else {

				$theme = mlb_get_theme();

				if ( $theme !== 'none' ) {
					wp_register_style( 'mlb-theme-' . $theme, $this->get_plugin_url() . 'assets/css/themes/' . $theme . '.css', null, $this->get_plugin_version() );
				}

				wp_enqueue_style( 'mlb-theme-' . $theme );
			}
		}

		/**
		 * Load textdomain
		 */
		public function load_textdomain() {
			$mofile = sprintf( '%1$s-%2$s.mo', MICROBLOG_DOMAIN, get_locale() );

			$mofile_global = WP_LANG_DIR . '/plugins/micro-liveblogs/' . $mofile;

			if ( file_exists( $mofile_global ) ) {
				load_textdomain( MICROBLOG_DOMAIN, $mofile_global );
			} else {
				load_plugin_textdomain( MICROBLOG_DOMAIN, false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
			}
		}

		/**
		 * Define Constants
		 */
		private function define_constants() {
			if ( ! defined( 'MLB_PATH' ) ) {
				define( 'MLB_PATH', $this->get_plugin_path() );
			}
			if ( ! defined( 'MLB_URL' ) ) {
				define( 'MLB_URL', $this->get_plugin_url() );
			}
			if ( ! defined( 'MLB_VERSION' ) ) {
				define( 'MLB_VERSION', $this->plugin_version );
			}
		}

		/**
		 * Settings
		 */
		public function settings() {
			return mlb_get_settings();
		}

		public function setup_api() {
			new MicroLiveblogs\API\LiveFeed();
		}

		public function setup_caching() {
			$cache = mlb_get_option( 'cache_enabled', false );

			if ( $cache == 'object' ) {
				MicroLiveblogs\Caching\ObjectCaching::init();
			} elseif ( $cache == 'transient' ) {
				MicroLiveblogs\Caching\TransientCaching::init();
			}
		}
	}
}

function MicroLiveBlog_GO() {
	return Micro_Liveblog::instance();
}

MicroLiveBlog_GO();
