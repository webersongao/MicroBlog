<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class MLB_Liveblog {

	/**
	 * The endpoint URL.
	 *
	 * @var null|string
	 */
	public $endpoint = null;

	/**
	 * The liveblog post ID.
	 *
	 * @var int|null
	 */
	private $liveblog_post_id = null;

	/**
	 * Create instance from liveblog id.
	 *
	 * @param int $id
	 * @return MLB_Liveblog
	 */
	public static function mlb_fromId( $id ) {
		$instance = new self();

		$instance->liveblog_post_id = $id;
		$instance->endpoint         = mbfun_get_live_liveblog_api_endpoint( $id );

		return $instance;
	}

	/**
	 * Create instance from endpoint url.
	 *
	 * @param string $url
	 * @return MLB_Liveblog
	 */
	public static function mlb_fromEndpoint( $url ) {
		$instance = new self();

		$instance->endpoint = $url;

		return $instance;
	}

	/**
	 * Construct
	 */
	private function __construct() {
	}

	/**
	 * Get the liveblog id if available.
	 *
	 * @return int|null
	 */
	private function mlb_get_liveblog_id() {
		// 明确传了 ID（[micro_liveblog live_id=...]）时以它为准，
		// 否则会被当前全局 $post 覆盖，导致端点、权限与状态判断互相不一致。
		return $this->liveblog_post_id ?: ( mlb_is_liveblog() ? get_the_ID() : null );
	}

	/**
	 * Render the liveblog.
	 *
	 * @return string
	 */
	public function mlb_content_render() {
        $content = '';

		// AMP is not supported at this moment
		if ( function_exists( 'amp_is_request' ) && amp_is_request() ) {
            $content .= '<p>' . sprintf( __( '<a rel="noamphtml" class="mlb-view-liveblog-link button" href="%s">View the liveblog</a>', MICROBLOG_DOMAIN ), esc_url( amp_remove_paired_endpoint( amp_get_current_url() ) ) . '#mlb-liveblog' ) . '</p>';

			return apply_filters( 'mlb_liveblog_html', $content );
		}

        wp_enqueue_script( 'wp-embed' );

		$classes = array( 'mlb-liveblog', 'mlb-theme-' . mbfun_get_live_theme() );
		if ( current_user_can( 'edit_post', $this->mlb_get_liveblog_id() ) ) {
			$classes[] = 'mlb-is-editor';
		}

		$liveblog_id  = $this->mlb_get_liveblog_id();
		$auto_refresh = $liveblog_id ? mlb_has_auto_refresh( $liveblog_id ) : false;

		do_action( 'mlb_before_liveblog', $liveblog_id, array() );

		$content .= '<div id="mlb-liveblog" class="' . esc_attr( implode( ' ', $classes ) ) . '"' . mbfun_color_mode_attr() . ' data-highlighted-entry="' . esc_attr( mbfun_get_live_highlighted_entry_id() ) . '" data-show-entries="' . esc_attr( mbfun_get_live_show_entries() ) . '" data-endpoint="' . esc_url( $this->endpoint ) . '"'
			. ' data-autorefresh="' . ( $auto_refresh ? '1' : '0' ) . '"'
			. ' data-refresh-interval="' . esc_attr( mbfun_get_live_update_interval() ) . '">';

		$content .= '<div class="mlb-no-liveblog-entries-message" style="display: none;">' . esc_html__( 'No liveblog updates yet.', MICROBLOG_DOMAIN ) . '</div>';

		$content .= '<ul class="mlb-liveblog-list"></ul>';

		$content .= '<div class="mlb-loader">';

		$content .= '<svg width="45" height="45" viewBox="0 0 45 45" xmlns="http://www.w3.org/2000/svg" stroke="currentColor">
				<g fill="none" fill-rule="evenodd" transform="translate(1 1)" stroke-width="2">
					<circle cx="22" cy="22" r="6" stroke-opacity="0">
						<animate attributeName="r"
							 begin="1.5s" dur="3s"
							 values="6;22"
							 calcMode="linear"
							 repeatCount="indefinite" />
						<animate attributeName="stroke-opacity"
							 begin="1.5s" dur="3s"
							 values="1;0" calcMode="linear"
							 repeatCount="indefinite" />
						<animate attributeName="stroke-width"
							 begin="1.5s" dur="3s"
							 values="2;0" calcMode="linear"
							 repeatCount="indefinite" />
					</circle>
					<circle cx="22" cy="22" r="6" stroke-opacity="0">
						<animate attributeName="r"
							 begin="3s" dur="3s"
							 values="6;22"
							 calcMode="linear"
							 repeatCount="indefinite" />
						<animate attributeName="stroke-opacity"
							 begin="3s" dur="3s"
							 values="1;0" calcMode="linear"
							 repeatCount="indefinite" />
						<animate attributeName="stroke-width"
							 begin="3s" dur="3s"
							 values="2;0" calcMode="linear"
							 repeatCount="indefinite" />
					</circle>
					<circle cx="22" cy="22" r="8">
						<animate attributeName="r"
							 begin="0s" dur="1.5s"
							 values="6;1;2;3;4;5;6"
							 calcMode="linear"
							 repeatCount="indefinite" />
					</circle>
				</g>';
		$content .= '</svg></div>';

		// 只有「开启中·自动刷新」才输出按钮；「默认展示」（相当于已完结）不轮询、也不提示。
		if ( $auto_refresh ) {
			$content .= '<button id="mlb-show-new-posts" style="display: none;" class="mlb-button button">' . esc_html__( 'Show new updates', MICROBLOG_DOMAIN ) . '</button>';
		}

		$content .= '<button id="mlb-load-more" style="display: none;" class="mlb-button button">' . esc_html__( 'Load more', MICROBLOG_DOMAIN ) . '</button>';
		$content .= '</div>';

		do_action( 'mlb_after_liveblog', $liveblog_id, array() );

		return apply_filters( 'mlb_liveblog_html', $content );
	}
}
