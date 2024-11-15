<?php
// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;
define( 'MY_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );

if ( ! function_exists( 'WPSCORE' ) ) {
	return;
}

add_theme_support( 'title-tag' );
add_theme_support( 'post-thumbnails' );
add_image_size( 'ms-thumb', 400 );
add_image_size( 'ms-large', 800 );
add_theme_support( 'customize-selective-refresh-widgets' );

add_filter( 'wp_editor_set_quality', 'wpst_image_quality' );
add_filter( 'jpeg_quality', 'wpst_image_quality' );
function wpst_image_quality( $quality ) {
	return get_theme_mod( 'wpst_image_quality', 80 );
}

eval( WPSCORE()->eval_product_data( WPSCORE()->get_installed_theme( 'sku' ), 'functions_eval_1' ) );

add_filter( 'is_protected_meta', '__return_false' );

eval( WPSCORE()->eval_product_data( WPSCORE()->get_installed_theme( 'sku' ), 'functions_eval_2' ) );

/**
 * Fire the wp_body_open action.
 *
 * Added for backwards compatibility to support pre 5.2.0 WordPress versions.
 *
 * @since v2.2
 */
if ( ! function_exists( 'wp_body_open' ) ) :
	function wp_body_open() {
		/**
		 * Triggered after the opening <body> tag.
		 *
		 * @since v2.2
		 */
		do_action( 'wp_body_open' );
	}
endif;

if ( ! function_exists( 'is_plugin_active' ) ) {
	require_once ABSPATH . 'wp-admin/includes/plugin.php';
}

$wpst_includes = array( '/customizer.php', '/enqueue.php', '/theme-activation.php', '/class-tgm-plugin-activation.php', '/tgm-functions.php', '/ajax/ajax-add-content.php', '/ajax/ajax-custom.php', '/ajax/ajax-user-settings.php', '/ajax/ajax-login-register.php', '/ajax/ajax-load-more.php', '/ajax/ajax-load-more-swipe.php', '/ajax/ajax-comments.php' );

foreach ( $wpst_includes as $file ) {
	$filepath = locate_template( 'inc' . $file );
	if ( $filepath ) {
		require_once $filepath;
	}
}

$wpst_admin_includes = array( '/admin-posts-columns.php', '/admin-metabox.php', '/admin-options.php' );

if ( is_admin() ) {
	foreach ( $wpst_admin_includes as $admin_file ) {
		$admin_filepath = locate_template( 'inc/admin' . $admin_file );
		if ( $admin_filepath ) {
			require_once $admin_filepath;
		}
	}
}

eval( WPSCORE()->eval_product_data( WPSCORE()->get_installed_theme( 'sku' ), 'functions_eval_5' ) );

function wpst_remove_kirki_loader() {
	?>
	<style>
	.kirki-customizer-loading-wrapper {
		background-image: none;
	}
	</style>
	<?php
}
add_action( 'wp_head', 'wpst_remove_kirki_loader', 100 );

add_action( 'after_setup_theme', 'wpst_remove_admin_bar' );
function wpst_remove_admin_bar() {
	if ( ! wpst_is_admin() ) {
		show_admin_bar( false );
	}
}

add_action( 'admin_init', 'wpst_redirect_non_admin_user' );
function wpst_redirect_non_admin_user() {
	if ( is_user_logged_in() ) {
		if ( ! defined( 'DOING_AJAX' ) && ! current_user_can( 'administrator' ) ) {
			wp_redirect( site_url() );
			exit;
		}
	}
}

add_action( 'pre_get_posts', 'wpst_pre_get_posts', 1 );
function wpst_pre_get_posts( $query ) {

	$random_posts = get_theme_mod( 'wpst_random_posts', false );

	if ( is_admin() || ! $random_posts  /* || ! $query->is_main_query() */ ) {
		return;
	}

	if ( ! is_home() && ! is_front_page() && ! is_page_template( array( 'template-vids.php', 'template-pics.php' ) ) ) {
		return;
	}

	$query->set( 'orderby', 'rand' );
	$query->set( 'order', 'DESC' );

	return;
}


/**
 * Add login logout menu item in the main menu.
 * ===========================================
 */
add_filter( 'wp_nav_menu_items', 'wpst_add_loginout_link', 10, 2 );
function wpst_add_loginout_link( $items, $args ) {
	/**
	 * If menu primary menu is set & user is logged in.
	 */
	// if ( get_theme_mod( 'wpst_enable_creators', '' ) === true && is_user_logged_in() && $args->theme_location == 'wpst-header-menu' ) {
	// $items .= '<li class="wpst-logout"><a href="' . wp_logout_url( home_url( '/' ) ) . '">' . esc_html('Logout', 'wpst') . '</a></li>';
	// $items .= '';
	// }
	/**
	 * Else display login menu item.
	 */
	if ( get_theme_mod( 'wpst_enable_creators', '' ) === true && ! is_user_logged_in() && $args->theme_location == 'wpst-header-menu' ) {
		$items .= '<li class="wpst-login"><a href="#wpst-login">' . esc_html( 'Login', 'wpst' ) . '</a></li>';
	}
	return $items;
}

function wpst_check_plugin_installed( $plugin_slug ): bool {
	$installed_plugins = get_plugins();
	return array_key_exists( $plugin_slug, $installed_plugins ) || in_array( $plugin_slug, $installed_plugins, true );
}

function wpst_send_password_registration_mail( $user_id ) {
	$blogname    = get_bloginfo( 'name' );
	$site_domain = parse_url( get_site_url(), PHP_URL_HOST );
	$user        = get_user_by( 'id', $user_id );
	$reset_key   = get_password_reset_key( $user );
	$user_email  = $user->user_email;
	$user_login  = $user->user_login;
	$rp_link     = '<a href="' . home_url( 'wp-login.php?action=rp&key=' . $reset_key . '&login=' . rawurlencode( $user_login ), 'login' ) . '" target="_blank">' . esc_html__( 'Set your password', 'wpst' ) . '</a>';

	$message  = esc_html__( 'Hi', 'wpst' ) . '<br>';
	$message .= sprintf( __( 'Your account has been created on %s.', 'wpst' ), $blogname ) . '<br>';
	$message .= esc_html__( 'To set your password, please visit the following link:', 'wpst' ) . '<br>';
	$message .= $rp_link . '<br>';

	$subject = sprintf( __( 'Your account on %s', 'wpst' ), $blogname );
	$headers = array();

	add_filter(
		'wp_mail_content_type',
		function ( $content_type ) {
			return 'text/html';
		}
	);
	$headers[] = 'From: ' . $blogname . ' <noreply@' . $site_domain . '>' . "\r\n";
	wp_mail( $user_email, $subject, $message, $headers );

	remove_filter( 'wp_mail_content_type', 'set_html_content_type' );
}

function wpst_send_password_reset_mail( $user_id ) {
	$blogname    = get_bloginfo( 'name' );
	$site_domain = parse_url( get_site_url(), PHP_URL_HOST );
	$user        = get_user_by( 'id', $user_id );
	$user_email  = $user->user_email;
	$reset_key   = get_password_reset_key( $user );
	$user_login  = $user->user_login;
	$rp_link     = '<a href="' . home_url( 'wp-login.php?action=rp&key=' . $reset_key . '&login=' . rawurlencode( $user_login ), 'login' ) . '" target="_blank">' . esc_html__( 'Reset your password', 'wpst' ) . '</a>';

	$message  = esc_html__( 'Someone requested that the password be reset for the following account:', 'wpst' ) . '<br>';
	$message .= sprintf( __( 'Username: %s', 'wpst' ), $user_login ) . '<br>';
	$message .= esc_html__( 'If this was a mistake, just ignore this email and nothing will happen.', 'wpst' ) . '<br>';
	$message .= esc_html__( 'To reset your password, please visit the following link:', 'wpst' ) . '<br>';
	$message .= $rp_link . '<br>';

	$subject = esc_html__( 'Reset your password', 'wpst' );
	$headers = array();

	add_filter(
		'wp_mail_content_type',
		function ( $content_type ) {
			return 'text/html';
		}
	);
	$headers[] = 'From: ' . $blogname . ' <noreply@' . $site_domain . '>' . "\r\n";
	wp_mail( $user_email, $subject, $message, $headers );

	remove_filter( 'wp_mail_content_type', 'set_html_content_type' );
}

add_action( 'validate_password_reset', 'wpst_redirect_after_rest', 10, 2 );
function wpst_redirect_after_rest( $errors, $user ) {
	global $rp_cookie, $rp_path;
	if ( ( ! $errors->get_error_code() ) && isset( $_POST['pass1'] ) && ! empty( $_POST['pass1'] ) ) {
		reset_password( $user, $_POST['pass1'] );
		setcookie( $rp_cookie, ' ', time() - YEAR_IN_SECONDS, $rp_path, COOKIE_DOMAIN, is_ssl(), true );
		wp_set_current_user( $user->ID );
		wp_set_auth_cookie( $user->ID );
		do_action( 'wp_login', $user->user_login );
		wp_redirect( home_url() );
		exit;
	}
}

function wpst_is_author() {
	if ( ! is_user_logged_in() ) {
		return;
	}
	$user = wp_get_current_user();
	if ( in_array( 'author', (array) $user->roles ) ) {
		return true;
	}
}

function wpst_is_admin() {
	if ( ! is_user_logged_in() ) {
		return;
	}
	$user = wp_get_current_user();
	if ( in_array( 'administrator', (array) $user->roles ) ) {
		return true;
	}
}

function wpst_get_page_url( $template_name ) {
	$pages = get_posts(
		array(
			'post_type'   => 'page',
			'post_status' => 'publish',
			'meta_query'  => array(
				array(
					'key'     => '_wp_page_template',
					'value'   => 'template-' . $template_name . '.php',
					'compare' => '=',
				),
			),
		)
	);
	if ( ! empty( $pages ) ) {
		foreach ( $pages as $pages__value ) {
			return get_permalink( $pages__value->ID );
		}
	}
	return get_bloginfo( 'url' );
}

add_filter('author_rewrite_rules', 'wpst_no_author_base_rewrite_rules');
function wpst_no_author_base_rewrite_rules($author_rewrite) {
    global $wpdb;
    $author_rewrite = array();
    $authors = $wpdb->get_results("SELECT user_nicename AS nicename from $wpdb->users");
    foreach($authors as $author) {
        $author_rewrite["({$author->nicename})/page/?([0-9]+)/?$"] = 'index.php?author_name=$matches[1]&paged=$matches[2]';
        $author_rewrite["({$author->nicename})/?$"] = 'index.php?author_name=$matches[1]';
    }
    return $author_rewrite;
}

add_filter('author_link', 'wpst_no_author_base', 1000, 2);
function wpst_no_author_base($link, $author_id) {
    $link_base = trailingslashit(get_option('home'));
    $link = preg_replace("|^{$link_base}author/|", '', $link);
    return $link_base . $link;
}

function wpst_user_registration( $user_id ) {
	global $wp_rewrite;
	// Write the rule
	$wp_rewrite->set_permalink_structure( '/%category%/%postname%/' );
	// Set the option
	update_option( 'rewrite_rules', false );
	// Flush the rules and tell it to write htaccess
	$wp_rewrite->flush_rules( true );
}
add_action( 'user_register', 'wpst_user_registration' );

add_filter( 'pre_user_login', 'wpst_lower_user_login' );
function wpst_lower_user_login( $login ) {
	return strtolower( $login );
}

// Disable generated image sizes
function wpst_disable_image_sizes( $sizes ) {
	unset( $sizes['thumbnail'] );
	unset( $sizes['medium'] );
	unset( $sizes['medium_large'] );
	unset( $sizes['large'] );
	unset( $sizes['1536x1536'] );
	unset( $sizes['2048x2048'] );
	unset( $sizes['alm-thumbnail'] );
	unset( $sizes['alm-cta'] );
	unset( $sizes['alm-gallery'] );

	return $sizes;
}
add_action( 'intermediate_image_sizes_advanced', 'wpst_disable_image_sizes' );

// Disable scaled image size
add_filter( 'big_image_size_threshold', '__return_false' );

// Disable other image sizes
function wpst_disable_other_image_sizes() {
	remove_image_size( 'post-thumbnail' ); // disable images added via set_post_thumbnail_size()
	remove_image_size( 'another-size' );   // disable any other added image sizes
}
add_action( 'init', 'wpst_disable_other_image_sizes' );

// Créer une class body avec le slug de la page
function wpst_add_slug_body_class( $classes ) {
	global $post;
	if ( isset( $post ) ) {
		$classes[] = $post->post_type . '-' . $post->post_name;
	}
	return $classes;
}
eval( WPSCORE()->eval_product_data( WPSCORE()->get_installed_theme( 'sku' ), 'functions_eval_9' ) );

function wpst_format_bytes( $bytes ) {
	if ( $bytes > 0 ) {
		$i = floor( $bytes / ( 1024 * 1024 ) );
		return $i;
	} else {
		return 0;
	}
}



if ( ! function_exists( 'wpst_get_video_duration' ) ) {
	function wpst_get_video_duration( $type_length = '' ) {
		global $post;
		$duration = intval( get_post_meta( $post->ID, 'duration', true ) );
		if ( ! empty( get_post_meta( $post->ID, 'duration', true ) ) ) {
			$duration = intval( get_post_meta( $post->ID, 'duration', true ) );
		}

		if ( $duration > 0 ) {
			if ( $duration >= 3600 ) {
				return date( 'H:i:s', $duration );
			} else {
				return date( 'i:s', $duration );
			}
		} else {
			return false;
		}
	}
}

if ( ! function_exists( 'wpst_get_post_views' ) ) {
	function wpst_get_post_views( $postID ) {
		$count_key = 'post_views_count';
		$count     = get_post_meta( $postID, $count_key, true );
		if ( $count == '' ) {
			delete_post_meta( $postID, $count_key );
			add_post_meta( $postID, $count_key, '0' );
			return '0';
		}
		return $count;
	}
}

if ( ! function_exists( ' wpst_get_human_number' ) ) {
	function wpst_get_human_number( $input = 0 ) {
		$input = intval( $input );

		if ( $input >= 0 && $input < 1000 ) {
			// 1 - 999
			$get_floor = floor( $input );
			$suffix    = '';
		} elseif ( $input >= 1000 && $input < 1000000 ) {
			// 1k-999k
			$get_floor = floor( $input / 1000 );
			$suffix    = 'K';
		} elseif ( $input >= 1000000 && $input < 1000000000 ) {
			// 1m-999m
			$get_floor = floor( $input / 1000000 );
			$suffix    = 'M';
		} elseif ( $input >= 1000000000 && $input < 1000000000000 ) {
			// 1b-999b
			$get_floor = floor( $input / 1000000000 );
			$suffix    = 'B';
		} elseif ( $input >= 1000000000000 ) {
			// 1t+
			$get_floor = floor( $input / 1000000000000 );
			$suffix    = 'T';
		}
		return ! empty( $get_floor . $suffix ) ? number_format( $get_floor ) . $suffix : 0;
	}
}

function wpst_custom_comment_list( $comment, $args, $depth ) {

	?>
	<?php
	// if unapproved comment is not commented by this user, exit...
	if ( ! $comment->comment_approved && $_SERVER['HTTP_USER_AGENT'] != $comment->comment_agent ) {
		return;
	}
	?>
	<li <?php comment_class(); ?> id="li-comment-<?php comment_ID(); ?>">
		<div id="div-comment-<?php comment_ID(); ?>" class="comment-body">
			<?php
			if ( 0 != $args['avatar_size'] ) :
				$avatar     = get_avatar( $comment, $args['avatar_size'] );
				$avatar_url = get_avatar_url( $comment->comment_author_email );
				if ( $avatar ) {
					?>
				<div class="avatar">
					<?php echo get_avatar( $comment, $args['avatar_size'] ); ?>
				</div>
					<?php
				}
			endif;
			?>
			<div class="comment-author">
				<div class="comment-bubble">
					<strong><?php echo $comment->comment_author; ?></strong>
					<?php comment_text(); ?> <?php
					if ( wpst_is_admin() ) :
						?>
						<?php edit_comment_link( esc_html__( 'Edit', 'wpst' ), '<span class="edit-link">', '</span>' ); ?><?php endif; ?>
				</div>
				<div class="comment-date">
					<?php printf( _x( '%s ago', '%s = human-readable time difference', 'wpst' ), human_time_diff( get_comment_time( 'U' ), current_time( 'timestamp' ) ) ); ?>
					<span class="reply"> -
					<?php
					comment_reply_link(
						array_merge(
							$args,
							array(
								'add_below' => 'div-comment',
								'class'     => '',
								'depth'     => $depth,
								'max_depth' => $args['max_depth'],
							)
						)
					);
					?>
											</span>
				</div>
			</div>
		</div>
		<div class="comment-footer">
			<?php if ( '0' == $comment->comment_approved ) : ?>
				<p class="comment-awaiting-moderation"><?php esc_html_e( 'Your comment is awaiting moderation.', 'wpst' ); ?></p>
			<?php endif; ?>
		</div>
	</li>
	<?php
}

// Unset URL from comment form
function wpst_move_comment_form_below( $fields ) {
	$comment_field = $fields['comment'];
	unset( $fields['comment'] );
	$fields['comment'] = $comment_field;
	unset( $fields['cookies'] );
	return $fields;
}

eval( WPSCORE()->eval_product_data( WPSCORE()->get_installed_theme( 'sku' ), 'functions_eval_10' ) );

function wpst_set_comment_author_avatar( $content, $comment_id, $size = '96', $avatar_class = '', $default = '', $alt = '' ) {
	$comment_author_username     = get_comment_author( $comment_id );
	$comment_author_first_letter = strtoupper( substr( $comment_author_username, 0, 1 ) );
	$comment_author_email        = get_comment_author_email( $comment_id );
	$comment_user_role           = get_user_by( 'email', $comment_author_email );
	$profile_avatar_basename     = '';
	if ( $comment_user_role ) {
		$profile_avatar_basename = esc_html( get_user_meta( $comment_user_role->ID, '_author_profile_avatar_basename', true ) );
	}
	if ( ! empty( $profile_avatar_basename ) ) {
		$upload_dir   = wp_upload_dir();
		$gravatar_url = $upload_dir['baseurl'] . '/' . $profile_avatar_basename;
		return '<img src="' . $gravatar_url . '" width="' . $size . '" height="' . $size . '" class="' . $avatar_class . '" alt="' . $alt . '" />';
	} else {
		return '<span class="author-first-letter">' . $comment_author_first_letter . '</span>';
	}
}
add_filter( 'get_avatar', 'wpst_set_comment_author_avatar', 10, 5 );
add_action(
	'admin_bar_menu',
	function () {
		remove_filter( 'get_avatar', 'wpst_set_comment_author_avatar' );
	},
	0
);

add_filter( 'comment_class', 'wpst_comment_classes', 10, 5 );
function wpst_comment_classes( $classes, $class, $comment_ID, $comment, $post_id ) {
	if ( 'unapproved' === wp_get_comment_status( $comment ) && is_array( $classes ) ) {
		$classes[] = 'ms-comment-awaiting-moderation';
	}
	return $classes;
}

add_filter(
	'comment_form_logged_in',
	function ( $logged_in_as = '', $commenter = '', $user_identity ) {
		return sprintf(
			'<p class="logged-in-as">%s</p>',
			sprintf(
				__( 'Logged in as <strong>%1$s</strong>. <a class="log-out-link" href="%2$s">Log out?</a>' ),
				$user_identity,
				wp_logout_url( apply_filters( 'the_permalink', get_permalink( get_the_ID() ), get_the_ID() ) )
			)
		);
	},
	10,
	3
);