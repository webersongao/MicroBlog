<?php
/*
------------------------------------------------------------------------
Copyright 2023 WebersonGao.com, The Doumao Team.
*/

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

if (!class_exists('Micro_Liveblog')) {

    class Micro_Liveblog
    {
        private static $instance = null;
        // public $liveblog = null;

        public static function instance()
        {
            if (!isset(self::$instance) && !(self::$instance instanceof Micro_Liveblog)) {
                self::$instance = new Micro_Liveblog();

                global $mlb_options;
                $mlb_options = mbfun_get_liveblog_settings();
                
                self::$instance->includes();

                add_action('plugins_loaded', [self::$instance, 'load_textdomain']);
                add_action('wp_enqueue_scripts', [self::$instance, 'enqueue_assets']);
                add_action('admin_enqueue_scripts', [self::$instance, 'enqueue_assets']);
                add_action('init', [self::$instance, 'setup_caching']);
                add_action('init', [self::$instance, 'setup_api']);
            }

            return self::$instance;
        }

        public function includes()
        {
            $files = [
                'live-class-liveblog.php',
                'live-post-types.php',
                'live-metabox.php',
                'live-functions.php',
                'live-shortcodes.php',
                'live-filters.php',
                'live-social-logos.php',
                'api/class-feedfactory.php',
                'api/class-liveentry.php',
                'api/class-livefeed.php',
                'caching/class-live-transient.php',
                'caching/class-live-object.php'
            ];

            foreach ($files as $file) {
                require_once mbfun_get_plugin_path() . 'liveblog/includes/' . $file;
            }
        }

        public function load_textdomain()
        {
            $mofile = sprintf('%1$s-%2$s.mo', MICROBLOG_DOMAIN, get_locale());
            $mofile_global = WP_LANG_DIR . '/plugins/microblogs/' . $mofile;
            if (file_exists($mofile_global)) {
                load_textdomain(MICROBLOG_DOMAIN, $mofile_global);
            } else {
                load_plugin_textdomain(MICROBLOG_DOMAIN, false, mbfun_get_plugin_url() . '/languages/');
            }
        }

        public function enqueue_assets()
        {
            if (is_admin()) {
                wp_register_script('selectize', mbfun_get_plugin_url() . 'assets/selectize/selectize.min.js', ['jquery'], '0.12.4');
                wp_register_script('mlb-admin', mbfun_get_plugin_url() . 'assets/js/liveblog-admin.js', ['jquery', 'selectize'], mbfun_get_plugin_version());
                wp_register_style('selectize', mbfun_get_plugin_url() . 'assets/selectize/selectize.default.css', null, '0.12.4');
            } else {
                if (in_array(get_post_type(), mbfun_get_live_supported_post_types())) {
                    wp_register_script('micro_lb_script', mbfun_get_plugin_url() . 'assets/js/liveblog.js', ['jquery'], mbfun_get_plugin_version());
                    wp_localize_script('micro_lb_script', 'micro_lb_data', [
                        'datetime_format' => mbfun_get_live_option('ml_entry_date_format', 'human'),
                        'locale'          => get_locale(),
                        'interval'        => mbfun_get_live_update_interval(),
                        'autoPolling'     => mbfun_get_live_update_autoPolling(),
                        'new_post_msg'    => __('There is %s update.', MICROBLOG_DOMAIN),
                        'new_posts_msg'   => __('There are %s updates.', MICROBLOG_DOMAIN),
                        'now_more_posts'  => __("That's All.", MICROBLOG_DOMAIN)
                    ]);
                    wp_enqueue_script('micro_lb_script');

                    $this->mlb_enqueue_theme_styles(mbfun_get_live_theme());
                }
            }
        }

        private function mlb_enqueue_theme_styles($theme) {
            $liveblog_css = mbfun_get_plugin_url() . 'assets/css/liveblog.css';
            $theme_css = mbfun_get_plugin_url() . 'assets/css/themes/' . $theme . '.css';

            wp_enqueue_style(
                'mlb-theme-liveblog',
                $liveblog_css,
                null,
                mbfun_get_plugin_version()
            );

            wp_enqueue_style(
                'mlb-theme-' . $theme,
                $theme_css,
                ['mlb-theme-liveblog'],
                mbfun_get_plugin_version()
            );
        }

        public function setup_api()
        {
            new MicroLiveblogs\API\LiveFeed();
        }

        public function setup_caching()
        {
            $cache = mbfun_get_live_option('ml_cache_enabled', false);
            if ($cache == 'object') {
                MicroLiveblogs\Caching\ObjectCaching::init();
            } elseif ($cache == 'transient') {
                MicroLiveblogs\Caching\TransientCaching::init();
            }
        }
    }
}

function MicroLiveBlog_GO()
{
    return Micro_Liveblog::instance();
}

MicroLiveBlog_GO();
