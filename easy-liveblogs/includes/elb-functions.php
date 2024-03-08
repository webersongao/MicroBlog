<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Get template part
 *
 * @see https://pippinsplugins.com/template-file-loaders-plugins/
 *
 * @param  string $view
 * @return string
 */
function elb_get_template_part( $slug, $name = null, $load = true ) {
	// Execute code for this part
	do_action( 'get_template_part_' . $slug, $slug, $name );

	// Setup possible parts
	$templates = array();
	if ( isset( $name ) ) {
		$templates[] = $slug . '-' . $name . '.php';
	}
	$templates[] = $slug . '.php';

	// Allow template parts to be filtered
	$templates = apply_filters( 'elb_get_template_part', $templates, $slug, $name );

	// Return the part that is found
	return elb_locate_template( $templates, $load, false );
}

/**
 * Locate template
 *
 * @param  string|array $template_names
 * @param  boolean      $load
 * @param  boolean      $require_once
 */
function elb_locate_template( $template_names, $load = false, $require_once = true ) {
	// No file found yet
	$located = false;

	// Try to find a template file
	foreach ( (array) $template_names as $template_name ) {

		// Continue if template is empty
		if ( empty( $template_name ) ) {
			continue;
		}

		// Trim off any slashes from the template name
		$template_name = ltrim( $template_name, '/' );

		// Check child theme first
		if ( file_exists( trailingslashit( get_stylesheet_directory() ) . 'elb/' . $template_name ) ) {
			$located = trailingslashit( get_stylesheet_directory() ) . 'elb/' . $template_name;
			break;

			// Check parent theme next
		} elseif ( file_exists( trailingslashit( get_template_directory() ) . 'elb/' . $template_name ) ) {
			$located = trailingslashit( get_template_directory() ) . 'elb/' . $template_name;
			break;

			// Check theme compatibility last
		} elseif ( file_exists( trailingslashit( elb_get_templates_dir() ) . $template_name ) ) {
			$located = trailingslashit( elb_get_templates_dir() ) . $template_name;
			break;
		}
	}

	if ( ( true == $load ) && ! empty( $located ) ) {
		load_template( $located, $require_once );

	} else {

		return $located;
	}
}

/**
 * Get templates dir
 *
 * @return string
 */
function elb_get_templates_dir() {
	return ELB_PATH . 'templates';
}

/**
 * Get liveblogs by status.
 *
 * @param string $status
 * @return array
 */
function elb_get_liveblogs_by_status( $status ) {
	$meta_query = array(
		array(
			'key'     => '_elb_is_liveblog',
			'compare' => 'EXISTS',
		),
	);

	if ( $status === 'closed' ) {
		$meta_query[] = array(
			'key'     => '_elb_status',
			'compare' => '=',
			'value'   => 'closed',
		);
	} elseif ( $status === 'open' ) {
		$meta_query[] = array(
			'relation' => 'OR',
			array(
				'key'     => '_elb_status',
				'compare' => 'NOT EXISTS',
			),
			array(
				'key'     => '_elb_status',
				'compare' => 'IS',
				'value'   => 'open',
			),
		);
	}

	$args = array(
		'post_type'   => elb_get_supported_post_types(),
		'post_status' => 'publish',
		'showposts'   => -1,
		'meta_query'  => $meta_query,
	);

	if ( $status === 'all' ) {
		$args = apply_filters( 'elb_get_liveblogs_args', $args );
	} else {
		$args = apply_filters( "elb_get_{$status}_liveblogs_args", $args );
	}

	$liveblogs = get_posts( $args );

	$result = array();

	foreach ( $liveblogs as $liveblog ) {
		$result[ $liveblog->ID ] = $liveblog->post_title;
	}

	if ( $status === 'all' ) {
		return apply_filters( 'elb_get_liveblogs', $result );
	}

	return apply_filters( "elb_get_{$status}_liveblogs", $result );
}

/**
 * Get liveblogs
 *
 * Get all published liveblogs.
 *
 * @return array
 */
function elb_get_liveblogs() {
	return elb_get_liveblogs_by_status( 'all' );
}

/**
 * Get liveblogs count
 *
 * @return int
 */
function elb_get_liveblogs_count( $args = array() ) {
	$default_args = apply_filters(
		'elb_get_liveblogs_count_args',
		array(
			'post_status' => array( 'publish', 'draft', 'future', 'trash' ),
			'all_posts'   => 1,
			'post_type'   => elb_get_supported_post_types(),
			'meta_query'  => array(
				array(
					'key'     => '_elb_is_liveblog',
					'compare' => 'EXISTS',
				),
			),
		)
	);

	$args = wp_parse_args( $args, $default_args );

	$result = new WP_Query( $args );

	return $result->found_posts;
}

/**
 * Is liveblog
 *
 * Checks if post is liveblog.
 *
 * @return boolean
 */
function elb_is_liveblog() {
	global $post;

	$liveblog = false;

	if ( ! empty( $post->ID ) ) {
		if ( get_post_meta( $post->ID, '_elb_is_liveblog', true ) ) {
			$liveblog = true;
		}
	}

	return apply_filters( 'elb_is_liveblog', $liveblog );
}

/**
 * Get liveblog status
 *
 * @param  int $post_id
 * @return string
 */
function elb_get_liveblog_status( $post_id = null ) {
	if ( ! $post_id ) {
		global $post;
		$post_id = $post->ID;
	}

	return get_post_meta( $post_id, '_elb_status', true );
}

/**
 * Get liveblog
 *
 * @param int|null $post_id
 * @return mixed
 */
function elb_get_liveblog( $post_id = null ) {
	if ( ! $post_id ) {
		global $post;

		return $post;
	}

	return get_post( $post_id );
}

/**
 * Get liveblog API endpoint URL.
 *
 * @param int $id
 * @return string
 */
function elb_get_liveblog_api_endpoint( $id ) {
	return apply_filters( 'elb_liveblog_api_endpoint', get_rest_url( null, "easy-liveblogs/v1/liveblog/{$id}" ), $id );
}

/**
 * Liveblog status options
 *
 * @return array
 */
function elb_get_liveblog_status_options() {
	return apply_filters(
		'elb_liveblog_status_options',
		array(
			'open'   => __( 'Open', ELB_TEXT_DOMAIN ),
			'closed' => __( 'Closed', ELB_TEXT_DOMAIN ),
		)
	);
}

/**
 * Get supported post types
 *
 * @return array
 */
function elb_get_supported_post_types() {
	global $elb_options;

	$post_types = ! empty( $elb_options['post_types'] ) ? $elb_options['post_types'] : array( 'post' );

	return apply_filters( 'elb_post_types', $post_types );
}

/**
 * Get update interval
 *
 * @return string
 */
function elb_get_update_interval() {
	global $elb_options;

	$update_interval = ! empty( $elb_options['update_interval'] ) ? $elb_options['update_interval'] : 30;

	return apply_filters( 'elb_update_interval', $update_interval );
}

/**
 * Display author name.
 *
 * @return boolean
 */
function elb_display_author_name() {
	global $elb_options;

	$display_author = ! empty( $elb_options['display_author'] ) ? true : false;

	return apply_filters( 'elb_display_author', $display_author );
}

/**
 * Display social sharing.
 *
 * @return boolean
 */
function elb_display_social_sharing() {
	global $elb_options;

	$display_social_sharing = ! empty( $elb_options['display_social_sharing'] ) ? true : false;

	return apply_filters( 'elb_display_social_sharing', $display_social_sharing );
}

/**
 * Get show entries
 *
 * @return string
 */
function elb_get_show_entries() {
	global $elb_options;

	$show_entries = ! empty( $elb_options['show_entries'] ) ? $elb_options['show_entries'] : 10;

	return apply_filters( 'elb_show_entries', $show_entries );
}

/**
 * Get theme
 *
 * @return string
 */
function elb_get_theme() {
	global $elb_options;

	$theme = ! empty( $elb_options['theme'] ) ? $elb_options['theme'] : 'light';

	return apply_filters( 'elb_theme', $theme );
}

/**
 * Title prefix
 *
 * @return string
 */
function elb_get_liveblog_title_prefix() {
	return apply_filters( 'elb_liveblog_title_prefix', __( 'Liveblog', ELB_TEXT_DOMAIN ) . ' - ' );
}

/**
 * Custom the_content alternative
 *
 * @return string
 */
function elb_get_entry_content() {
	global $post, $wp_embed;

	$content = apply_filters( 'elb_entry_content', $post->post_content );

	return apply_filters( 'the_content', $content );
}

/**
 * Entry content
 *
 * @return void
 */
function elb_entry_content() {
	echo elb_get_entry_content();
}

/**
 * Get entry title
 *
 * @return string
 */
function elb_get_entry_title() {
	global $post;

	return apply_filters( 'elb_entry_title', $post->post_title );
}

/**
 * Entry title
 *
 * @return void
 */
function elb_entry_title() {
	echo elb_get_entry_title();
}

/**
 * Get highlited entry id.
 *
 * @return mixed
 */
function elb_get_highlighted_entry_id() {
    $entry_id = apply_filters( 'elb_highlighted_entry_id', filter_input( INPUT_GET, 'entry', FILTER_SANITIZE_NUMBER_INT ) );

	return $entry_id;
}

/**
 * Get entry URL.
 *
 * @param WP_Post|int|null $post
 * @return string
 */
function elb_get_entry_url( $post = null ) {
    if ( is_null( $post ) ) {
	    global $post;
    } elseif( is_numeric( $post ) ) {
        $post = get_post( $post );
    }

	$liveblog_id = get_post_meta( $post->ID, '_elb_liveblog', true );

	$url = add_query_arg( 'entry', $post->ID, get_permalink( $liveblog_id ) );

    return apply_filters( 'elb_entry_url', $url, $liveblog_id, $post );
}

/**
 * Get edit entry url.
 *
 * @param int $post_id
 * @return string
 */
function elb_get_edit_entry_url( $post_id ) {
	return add_query_arg(
		array(
			'post'   => $post_id,
			'action' => 'edit',
		),
		admin_url( 'post.php' )
	);
}

/**
 * Edit entry link.
 *
 * @param int $post_id
 * @return void
 */
function elb_edit_entry_link() {
	global $post;

	if ( empty( $post->ID ) ) {
		return;
	}

	echo '<a href="' . elb_get_edit_entry_url( $post->ID ) . '" rel="nofollow">' . __( 'Edit This' ) . '</a>';
}

/**
 * Determine if assets should be enqueued.
 *
 * @return bool
 */
function elb_page_contains_liveblog() {
	if ( ! is_singular() ) {
		return false;
	}

	global $post;

	if ( strpos( apply_filters( 'the_content', $post->post_content ), 'elb-liveblog' ) === false ) {
		return false;
	}

	return true;
}

/**
 * Maybe add body class.
 *
 * @param array $classes
 * @return array
 */
function elb_add_theme_body_class( $classes ) {
	if ( elb_is_liveblog() ) {
		$classes[] = 'elb-theme-' . elb_get_theme();
	}

	return $classes;
}
add_filter( 'body_class', 'elb_add_theme_body_class' );

/**
 * Add meta data.
 *
 * @return void
 */
function elb_add_meta_data() {
	global $post;

	if ( ! elb_is_liveblog() ) {
		return;
	}

	$liveblog_url = get_permalink();

	$items = elb_get_liveblog_feed( elb_get_liveblog_api_endpoint( $post->ID ) );

	$organization = array(
		'@type' => 'Organization',
		'name'  => get_bloginfo( 'name' ),
	);

	$metadata = array(
		'@type'             => 'LiveBlogPosting',
		'@context'          => 'https://schema.org',
		'@id'               => $liveblog_url,
		'headline'          => get_the_title(),
		'description'       => trim( preg_replace( '/\s+/', ' ', strip_tags( get_the_content() ) ) ),
		'coverageStartTime' => get_the_date( 'c', $post ),
		'coverageEndTime'   => wp_date( 'c', strtotime( $items['last_update'] . ' + 1 hour' ) ),
		'dateModified'      => $items['last_update'],
		'url'               => $liveblog_url,
		'publisher'         => $organization
	);

    if ( ! empty( $items['updates'] ) ) {
        foreach ( $items['updates'] as $entry ) {
            $entry_url = add_query_arg( 'entry', $entry['id'], $liveblog_url );

            $_entry = array(
                '@type'            => 'BlogPosting',
                'headline'         => $entry['title'],
                'url'              => $entry_url,
                'mainEntityOfPage' => $entry_url,
                'datePublished'    => $entry['datetime'],
                'dateModified'     => $entry['modified'],
                'articleBody'      => trim( preg_replace( '/\s+/', ' ', strip_tags( $entry['content'] ) ) ),
            );

            if ( elb_display_author_name() ) {
                $_entry['author'] = $organization;
            }

            $entries[] = $_entry;
        }
    }

	wp_reset_postdata();

	$metadata['liveBlogUpdate'] = $entries ?? array();

	$metadata = apply_filters( 'easy_liveblogs_liveblog_metadata', $metadata, $post );
	?>
	<script type="application/ld+json"><?php echo wp_json_encode( $metadata ); ?></script>
	<?php
}
add_action( 'wp_head', 'elb_add_meta_data' );

/**
 * Get liveblog feed based on the endpoint url.
 *
 * @param string $endpoint
 * @return array
 */
function elb_get_liveblog_feed( $endpoint ) {
	$result = json_decode(
		file_get_contents(
			$endpoint,
			false,
			stream_context_create(
				array(
					'ssl' => array(
						'verify_peer'      => false,
						'verify_peer_name' => false,
					),
				)
			)
		),
		true
	);

	return $result;
}

/**
 * Return the site datetime format.
 *
 * @return void
 */
function elb_get_datetime_format() {
	return get_option( 'date_format' ) . ' ' . get_option( 'time_format' );
}

/**
 * Get entry display date.
 *
 * @return void
 */
function elb_get_entry_display_date() {
	global $post;

	setup_postdata( $post );

	$display = elb_get_option( 'entry_date_format', 'human' );

	if ( $display === 'human' ) {
		?>
			<time class="elb-js-update-time" datetime="<?php echo get_the_time( 'Y-m-d H:i' ); ?>"><?php printf( _x( '%s ago', '%s = human-readable time difference', ELB_TEXT_DOMAIN ), human_time_diff( get_the_time( 'U' ), current_time( 'timestamp' ) ) ); ?></time>
		<?php
	} else {
		if ( $display === 'time' ) {
			$format = get_option( 'time_format' );
		} elseif ( $display === 'date' ) {
			$format = get_option( 'date_format' );
		} else {
			$format = elb_get_datetime_format();
		}
		?>
			<time datetime="<?php echo get_the_time( 'Y-m-d H:i' ); ?>"><?php echo get_the_time( $format ); ?></time>
		<?php
	}
}
