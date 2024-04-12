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
function mlb_get_template_part( $slug, $name = null, $load = true ) {
	// Execute code for this part
	do_action( 'get_template_part_' . $slug, $slug, $name );

	// Setup possible parts
	$templates = array();
	if ( isset( $name ) ) {
		$templates[] = $slug . '-' . $name . '.php';
	}
	$templates[] = $slug . '.php';

	// Allow template parts to be filtered
	$templates = apply_filters( 'mlb_get_template_part', $templates, $slug, $name );

	// Return the part that is found
	return mlb_locate_template( $templates, $load, false );
}

/**
 * Locate template
 *
 * @param  string|array $template_names
 * @param  boolean      $load
 * @param  boolean      $require_once
 */
function mlb_locate_template( $template_names, $load = false, $require_once = true ) {
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
		if ( file_exists( trailingslashit( get_stylesheet_directory() ) . 'micro_live_theme/' . $template_name ) ) {
			$located = trailingslashit( get_stylesheet_directory() ) . 'micro_live_theme/' . $template_name;
			break;

			// Check parent theme next
		} elseif ( file_exists( trailingslashit( get_template_directory() ) . 'micro_live_theme/' . $template_name ) ) {
			$located = trailingslashit( get_template_directory() ) . 'micro_live_theme/' . $template_name;
			break;

			// Check theme compatibility last
		} elseif ( file_exists( trailingslashit( mlb_get_templates_dir() ) . $template_name ) ) {
			$located = trailingslashit( mlb_get_templates_dir() ) . $template_name;
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
 * 对应文件夹名称，慎改
 * @return string
 */
function mlb_get_templates_dir() {
	return MLB_PATH . 'templates';
}

/**
 * Get liveblogs by status.
 *
 * @param string $status
 * @return array
 */
function mlb_get_liveblogs_by_status( $status ) {
	$meta_query = array(
		array(
			'key'     => '_micro_post_live_enable',
			'compare' => 'EXISTS',
		),
	);

	if ( $status === 'closed' ) {
		$meta_query[] = array(
			'key'     => '_micro_post_live_status',
			'compare' => '=',
			'value'   => 'closed',
		);
	} elseif ( $status === 'open' ) {
		$meta_query[] = array(
			'relation' => 'OR',
			array(
				'key'     => '_micro_post_live_status',
				'compare' => 'NOT EXISTS',
			),
			array(
				'key'     => '_micro_post_live_status',
				'compare' => 'IS',
				'value'   => 'open',
			),
		);
	} elseif ( $status === 'used' ) {
        $meta_query[] = array(
            'key'     => '_micro_live_post_id',
            'compare' => 'EXISTS',
        );
    }

	$args = array(
		'post_type'   => mlb_get_supported_post_types(),
		'post_status' => 'publish',
		'showposts'   => -1,
		'meta_query'  => $meta_query,
	);

	if ( $status === 'all' ) {
		$args = apply_filters( 'mlb_get_liveblogs_args', $args );
	} else {
		$args = apply_filters( "mlb_get_{$status}_liveblogs_args", $args );
	}

	$liveblogs = get_posts( $args );

	$result = array();

	foreach ( $liveblogs as $liveblog ) {
		$result[ $liveblog->ID ] = $liveblog->post_title;
	}

	if ( $status === 'all' ) {
		return apply_filters( 'mlb_get_all_liveblogs', $result );
	}

	return apply_filters( "mlb_get_{$status}_liveblogs", $result );
}

/**
 * Get liveblogs
 *
 * Get all published liveblogs
 *
 * @return array
 */
function mlb_get_all_liveblogs() {
	return mlb_get_liveblogs_by_status( 'all' );
}

/**
 * Get liveblogs count
 *
 * @return int
 */
function mlb_get_liveblogs_count( $args = array() ) {
	$default_args = apply_filters(
		'mlb_get_liveblogs_count_args',
		array(
			'post_status' => array( 'publish', 'draft', 'future', 'trash' ),
			'all_posts'   => 1,
			'post_type'   => mlb_get_supported_post_types(),
			'meta_query'  => array(
				array(
					'key'     => '_micro_post_live_enable',
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
function mlb_is_liveblog() {
	global $post;

	$liveblog = false;

	if ( ! empty( $post->ID ) ) {
		if ( get_post_meta( $post->ID, '_micro_post_live_enable', true ) ) {
			$liveblog = true;
		}
	}

	return apply_filters( 'mlb_is_liveblog', $liveblog );
}

/**
 * Get liveblog status
 *
 * @param  int $post_id
 * @return string
 */
function mlb_get_liveblog_status( $post_id = null ) {
	if ( ! $post_id ) {
		global $post;
		$post_id = $post->ID;
	}

	return get_post_meta( $post_id, '_micro_post_live_status', true );
}

/**
 * Get liveblog
 *
 * @param int|null $post_id
 * @return mixed
 */
function mlb_get_liveblog( $post_id = null ) {
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
function mlb_get_liveblog_api_endpoint( $id ) {
	return apply_filters( 'mlb_liveblog_api_endpoint', get_rest_url( null, "microlives/v1/liveblog/{$id}" ), $id );
}

/**
 * Liveblog status options
 *
 * @return array
 */
function mlb_get_liveblog_status_options() {
	return apply_filters(
		'mlb_liveblog_status_options',
		array(
			'open'   => __( 'Open', MLB_TEXT_DOMAIN ),
			'closed' => __( 'Closed', MLB_TEXT_DOMAIN ),
		)
	);
}

// get_post_types
/**
 * Get all post types
 *
 * @return array
 */
function mlb_get_all_post_types() {

	$all_types = get_post_types( array( 'public' => true), 'names', 'and' );
	$specific_types = array( 'post', 'page', 'micropost' );   
	$intersected_post_types = array_intersect( $all_types, $specific_types );

	return apply_filters( 'mlb_intersected_post_types', $intersected_post_types );
}


/**
 * Get supported post types
 *
 * @return array
 */
function mlb_get_supported_post_types() {
	global $mlb_options;

	$post_types = ! empty( $mlb_options['post_types'] ) ? $mlb_options['post_types'] : array( 'post' );

	return apply_filters( 'mlb_post_types', $post_types );
}

/**
 * Get update interval
 *
 * @return string
 */
function mlb_get_update_interval() {
	global $mlb_options;

	$update_interval = ! empty( $mlb_options['update_interval'] ) ? $mlb_options['update_interval'] : 30;

	return apply_filters( 'mlb_update_interval', $update_interval );
}

/**
 * Display title.
 *
 * @return boolean
 */
function mlb_display_title() {
	global $mlb_options;

	$display = ! empty( $mlb_options['display_title'] ) ? true : false;

	return apply_filters( 'mlb_display_title', $display );
}

/**
 * Display author name.
 *
 * @return boolean
 */
function mlb_display_author_name() {
	global $mlb_options;

	$display_author = ! empty( $mlb_options['display_author'] ) ? true : false;

	return apply_filters( 'mlb_display_author', $display_author );
}

/**
 * Display social sharing.
 *
 * @return boolean
 */
function mlb_display_social_sharing() {
	global $mlb_options;

	$display_share = ! empty( $mlb_options['display_social_share'] ) ? true : false;

	return apply_filters( 'mlb_display_social_sharing', $display_share );
}

/**
 * Get show entries
 *
 * @return string
 */
function mlb_get_show_entries() {
	global $mlb_options;

	$show_entries = ! empty( $mlb_options['show_entries'] ) ? $mlb_options['show_entries'] : 10;

	return apply_filters( 'mlb_show_entries', $show_entries );
}

/**
 * Get theme
 *
 * @return string
 */
function mlb_get_theme() {
	global $mlb_options;

	$theme = ! empty( $mlb_options['theme_style'] ) ? $mlb_options['theme_style'] : 'light';

	return apply_filters( 'mlb_theme', $theme );
}

/**
 * Title prefix
 *
 * @return string
 */
function mlb_get_liveblog_title_prefix() {
	return apply_filters( 'mlb_liveblog_title_prefix', __( 'Liveblog', MLB_TEXT_DOMAIN ) . ' - ' );
}

/**
 * Custom the_content alternative
 *
 * @return string
 */
function mlb_get_entry_content() {
	global $post, $wp_embed;

	$content = apply_filters( 'mlb_entry_content', $post->post_content );

	return apply_filters( 'the_content', $content );
}

/**
 * Entry content
 *
 * @return void
 */
function mlb_entry_content() {
	echo mlb_get_entry_content();
}

/**
 * Get entry title
 *
 * @return string
 */
function mlb_get_entry_title() {
	global $post;

	return apply_filters( 'mlb_entry_title', $post->post_title );
}

/**
 * Entry author avatar
 *
 * @return void
 */
function mlb_get_author_avatar($size = 30) {
    global $post;
    $author_id = $post->post_author;
    $avatar_url = get_avatar_url( $author_id, array('size' => $size) ); // 获取头像的 URL
    return $avatar_url;
}


/**
 * Entry title
 *
 * @return void
 */
function mlb_entry_title() {
	echo mlb_get_entry_title();
}

/**
 * Entry author avatar
 *
 * @return void
 */
function mlb_entry_author_avatar($size = 30) {
    echo mlb_get_author_avatar($size);
}


/**
 * Get highlited entry id.
 *
 * @return mixed
 */
function mlb_get_highlighted_entry_id() {
    $entry_id = apply_filters( 'mlb_highlighted_live_id', filter_input( INPUT_GET, 'entry', FILTER_SANITIZE_NUMBER_INT ) );

	return $entry_id;
}

/**
 * Get entry URL.
 *
 * @param WP_Post|int|null $post
 * @return string
 */
function mlb_get_entry_url( $post = null ) {
    if ( is_null( $post ) ) {
	    global $post;
    } elseif( is_numeric( $post ) ) {
        $post = get_post( $post );
    }

	$liveblog_id = get_post_meta( $post->ID, '_micro_live_post_id', true );

	$url = add_query_arg( 'entry', $post->ID, get_permalink( $liveblog_id ) );

    return apply_filters( 'mlb_entry_url', $url, $liveblog_id, $post );
}

/**
 * Get edit entry url.
 *
 * @param int $post_id
 * @return string
 */
function mlb_get_edit_entry_url( $post_id ) {
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
function mlb_edit_entry_link() {
	global $post;

	if ( empty( $post->ID ) ) {
		return;
	}

	echo '<a href="' . mlb_get_edit_entry_url( $post->ID ) . '" rel="nofollow">' . __( 'Edit This' ) . '</a>';
}

/**
 * Determine if assets should be enqueued.
 *
 * @return bool
 */
function mlb_page_contains_liveblog() {
	if ( ! is_singular() ) {
		return false;
	}

	global $post;

	if ( strpos( apply_filters( 'the_content', $post->post_content ), 'mlb-liveblog' ) === false ) {
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
function mlb_add_theme_body_class( $classes ) {
	if ( mlb_is_liveblog() ) {
		$classes[] = 'mlb-theme-' . mlb_get_theme();
	}

	return $classes;
}
add_filter( 'body_class', 'mlb_add_theme_body_class' );

/**
 * Add meta data.
 *
 * @return void
 */
function mlb_add_meta_data() {
	global $post;

	if ( ! mlb_is_liveblog() ) {
		return;
	}

	$liveblog_url = get_permalink();

	$items = mlb_get_liveblog_feed( mlb_get_liveblog_api_endpoint( $post->ID ) );

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

			if ( mlb_display_author_name() ) {
                $_entry['author'] = $organization;
            }

            $entries[] = $_entry;
        }
    }

	wp_reset_postdata();

	$metadata['liveBlogUpdate'] = $entries ?? array();

	$metadata = apply_filters( 'mlb_liveblog_metadata', $metadata, $post );
	?>
	<script type="application/ld+json"><?php echo wp_json_encode( $metadata ); ?></script>
	<?php
}
add_action( 'wp_head', 'mlb_add_meta_data' );

/**
 * Get liveblog feed based on the endpoint url.
 *
 * @param string $endpoint
 * @return array
 */
function mlb_get_liveblog_feed( $endpoint ) {
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
function mlb_get_datetime_format() {
	return get_option( 'date_format' ) . ' ' . get_option( 'time_format' );
}

/**
 * Get entry display date.
 *
 * @return void
 */
function mlb_get_entry_display_date() {
	global $post;

	setup_postdata( $post );

	$display = mlb_get_option( 'entry_date_format', 'human' );

	if ( $display === 'human' ) {
		?>
			<time class="mlb-js-update-time" datetime="<?php echo get_the_time( 'Y-m-d H:i' ); ?>"><?php printf( _x( '%s ago', '%s = human-readable time difference', MLB_TEXT_DOMAIN ), human_time_diff( get_the_time( 'U' ), current_time( 'timestamp' ) ) ); ?></time>
		<?php
	} else {
		if ( $display === 'time' ) {
			$format = get_option( 'time_format' );
		} elseif ( $display === 'date' ) {
			$format = get_option( 'date_format' );
		} else {
			$format = mlb_get_datetime_format();
		}
		?>
			<time datetime="<?php echo get_the_time( 'Y-m-d H:i' ); ?>"><?php echo get_the_time( $format ); ?></time>
		<?php
	}
}

function mlb_get_liveblogs_with_post_id($post_id) {

	$meta_key = '_micro_live_post_id';
	// 构建 WP_Query 参数数组
	$args = array(
		'post_type'	=> array('post', 'micropost'),	// 文章类型
		'posts_per_page' => -1,	// 获取所有符合条件的文章
		'meta_query' => array(	// 元数据查询参数
			array(
				'key' => $meta_key, // 已知的 meta_key
				'value' => strval($post_id) // 已知的 meta_value
			)
		)
	);
	// 实例化 WP_Query 对象
	$query = new WP_Query( $args );

	// 获取查询结果中的所有文章
	$posts = $query->posts;

	// 返回文章数组
	return $posts;
}

