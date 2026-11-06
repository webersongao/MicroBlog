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

                self::$instance->includes();

                add_action('init', [self::$instance, 'load_textdomain'], 1);
                add_action('wp_enqueue_scripts', [self::$instance, 'enqueue_assets']);
                add_action('admin_enqueue_scripts', [self::$instance, 'enqueue_assets']);
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
                'live-parent-picker.php',
                'live-shortcodes.php',
                'live-filters.php',
                'live-social-logos.php',
                'api/class-feedfactory.php',
                'api/class-liveentry.php',
                'api/class-livefeed.php'
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
                load_plugin_textdomain(MICROBLOG_DOMAIN, false, dirname(MICROBLOG_PLUGIN_BASENAME) . '/languages/');
            }
        }

        public function enqueue_assets()
        {
            if (is_admin()) {
                wp_register_script('selectize', mbfun_get_plugin_url() . 'assets/selectize/selectize.min.js', ['jquery'], '0.12.4');
                wp_register_script('mlb-admin', mbfun_get_plugin_url() . 'assets/js/liveblog-admin.js', ['jquery', 'selectize'], mbfun_get_plugin_version());
                wp_register_style('selectize', mbfun_get_plugin_url() . 'assets/selectize/selectize.default.css', null, '0.12.4');

                // 只有连载条目编辑页有「选择原文」metabox，普通文章页不再需要 selectize。
                $screen = function_exists('get_current_screen') ? get_current_screen() : null;

                if ($screen && 'post' === $screen->base && 'microlive' === $screen->post_type) {
                    wp_enqueue_script('mlb-admin');
                    wp_enqueue_style('selectize');

                    wp_localize_script('mlb-admin', 'mlbAdminL10n', [
                        'selectLiveblog' => __('Please select a liveblog before saving.', MICROBLOG_DOMAIN),
                    ]);
                }
            } else {
                if (in_array(get_post_type(), mbfun_get_live_supported_post_types())) {
                    wp_register_script('micro_lb_script', mbfun_get_plugin_url() . 'assets/js/liveblog.js', ['jquery'], mbfun_get_plugin_version());
                    wp_localize_script('micro_lb_script', 'micro_lb_data', [
                        'now_more_posts'  => __("That's All.", MICROBLOG_DOMAIN),
                        'show_new_posts'  => __('Show %s new updates', MICROBLOG_DOMAIN),
                    ]);
                    wp_enqueue_script('micro_lb_script');

                    $this->mlb_enqueue_theme_styles();
                }
            }
        }

        /**
         * 连载前台样式。
         *
         * 1.9.1 起不再按 light / dark 加载两份文件：颜色统一由 liveblog.css
         * 里的 --mlb-* 令牌提供，模式来自插件「颜色模式」设置。
         */
        private function mlb_enqueue_theme_styles()
        {
            wp_enqueue_style(
                'mlb-liveblog',
                mbfun_get_plugin_url() . 'assets/css/liveblog.css',
                null,
                mbfun_get_plugin_version()
            );
        }

        public function setup_api()
        {
            new MicroLiveblogs\API\LiveFeed();
        }
    }
}

function MicroLiveBlog_GO()
{
    return Micro_Liveblog::instance();
}

MicroLiveBlog_GO();
