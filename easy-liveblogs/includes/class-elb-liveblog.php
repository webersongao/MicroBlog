<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ELB_Liveblog {

	/**
	 * The endpoint URL.
	 *
	 * @var null|string
	 */
	public $endpoint = null;

	/**
	 * @deprecated 2.0.0
	 */
	private $liveblog_post_id = null;

	/**
	 * @deprecated 2.0.0
	 */
	private $showposts;

	/**
	 * Create instance from liveblog id.
	 *
	 * @param int $id
	 * @return ELB_Liveblog
	 */
	public static function fromId( $id ) {
		$instance = new self();

		$instance->liveblog_post_id = $id;
		$instance->endpoint         = elb_get_liveblog_api_endpoint( $id );

		return $instance;
	}

	/**
	 * Create instance from endpoint url.
	 *
	 * @param string $url
	 * @return ELB_Liveblog
	 */
	public static function fromEndpoint( $url ) {
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
	 * @deprecated 2.0.0
	 */
	public function init() {
	}

	/**
	 * @deprecated 2.0.0
	 */
	public function theme_body_class( $classes ) {
	}

	/**
	 * @deprecated 2.0.0
	 */
	public function liveblog( $content ) {
	}

	/**
	 * Get the liveblog id if available.
	 *
	 * @return int|null
	 */
	private function get_liveblog_id() {
		return elb_is_liveblog() ? get_the_ID() : $this->liveblog_post_id;
	}

	/**
	 * Render the liveblog.
	 *
	 * @return string
	 */
	public function render() {
        $content = '';

		// AMP is not supported at this moment
		if ( function_exists( 'amp_is_request' ) && amp_is_request() ) {
            $content .= '<p>' . sprintf( __( '<a rel="noamphtml" class="elb-view-liveblog-link button" href="%s">View the liveblog</a>', ELB_TEXT_DOMAIN ), esc_url( amp_remove_paired_endpoint( amp_get_current_url() ) ) . '#elb-liveblog' ) . '</p>';

			return apply_filters( 'elb_liveblog_html', $content );
		}

        wp_enqueue_script( 'wp-embed' );

		$classes = array( 'elb-liveblog', 'elb-theme-' . elb_get_theme() );

		if ( current_user_can( 'edit_post', $this->get_liveblog_id() ) ) {
			$classes[] = 'elb-is-editor';
		}

		$content .= do_action( 'elb_before_liveblog', $this->get_liveblog_id(), array() );

		$content .= '<div id="elb-liveblog" class="' . implode( ' ', $classes ) . '" data-append-timestamp="' . elb_get_option( 'append_timestamp', false ) . '" data-status="' . elb_get_liveblog_status() . '" data-highlighted-entry="' . elb_get_highlighted_entry_id() . '" data-show-entries="' . elb_get_show_entries() . '" data-endpoint="' . $this->endpoint . '">';

		$content .= '<div class="elb-liveblog-closed-message" style="display: none;">' . __( 'The liveblog has ended.', ELB_TEXT_DOMAIN ) . '</div>';

		$content .= '<button id="elb-show-new-posts" class="elb-button button" style="display: none;"></button>';

		$content .= '<div class="elb-no-liveblog-entries-message" style="display: none;">' . __( 'No liveblog updates yet.', ELB_TEXT_DOMAIN ) . '</div>';

		$content .= '<ul class="elb-liveblog-list"></ul>';

		$content .= '<div class="elb-loader"><!-- By Sam Herbert (@sherb) -->
			<svg width="45" height="45" viewBox="0 0 45 45" xmlns="http://www.w3.org/2000/svg" stroke="currentColor">
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
				</g>
			</svg></div>';

		$content .= '<button id="elb-load-more" style="display: none;" class="elb-button button">' . __( 'Load more', ELB_TEXT_DOMAIN ) . '</button>';

		$content .= '</div>';

		$content .= do_action( 'elb_after_liveblog', $this->get_liveblog_id(), array() );

		return apply_filters( 'elb_liveblog_html', $content );
	}

	/**
	 * @deprecated 2.0.0
	 */
	public function get_posts( $custom_args = array() ) {
	}

	/**
	 * @deprecated 2.0.0
	 */
	public function post( $post, $args = array() ) {
	}
}
