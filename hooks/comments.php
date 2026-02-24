<?php
/**
 * Comment related hooks.
 *
 * @package kyom
 */

// Force only registered users can comment.
add_filter( 'pre_option_comment_registration', '__return_true' );

// Force email not required.
add_filter( 'pre_option_require_name_email', '__return_zero' );

/**
 * Hold comments from non-logged-in users.
 *
 * @param int|string|WP_Error $approved    The approval status.
 * @param array               $commentdata Comment data.
 * @return int|string|WP_Error
 */
add_filter( 'pre_comment_approved', function ( $approved, $commentdata ) {
	if ( empty( $commentdata['user_id'] ) ) {
		return 0; // hold
	}
	return $approved;
}, 10, 2 );

/**
 * Add avatar URLs and author info to REST API comment response.
 *
 * @param WP_REST_Response $response The response object.
 * @param WP_Comment       $comment  The comment object.
 * @return WP_REST_Response
 */
add_filter( 'rest_prepare_comment', function ( WP_REST_Response $response, WP_Comment $comment ) {
	$data                       = $response->get_data();
	$data['author_avatar_urls'] = rest_get_avatar_urls( $comment );
	$data['author_name']        = $comment->comment_author;
	$data['is_primary']         = kyom_is_primary_comment( $comment );
	$response->set_data( $data );
	return $response;
}, 10, 2 );

/**
 * REST endpoint to get comment auth info (nonce + user).
 *
 * WordPress REST API requires X-WP-Nonce for cookie auth, but cached pages
 * can't embed a nonce. So we validate the logged_in cookie directly.
 */
add_action( 'rest_api_init', function () {
	register_rest_route( 'kyom/v1', '/comment-auth', [
		'methods'             => 'GET',
		'callback'            => function () {
			// Validate the WordPress logged_in cookie directly
			// because REST API won't recognize cookie auth without a nonce.
			$user_id = wp_validate_auth_cookie( '', 'logged_in' );
			if ( ! $user_id ) {
				return new WP_REST_Response( [ 'logged_in' => false ], 200 );
			}
			wp_set_current_user( $user_id );
			$user = wp_get_current_user();
			return new WP_REST_Response( [
				'logged_in'   => true,
				'nonce'       => wp_create_nonce( 'wp_rest' ),
				'user_name'   => $user->display_name,
				'user_avatar' => get_avatar_url( $user->ID, [ 'size' => 80 ] ),
			], 200 );
		},
		'permission_callback' => '__return_true',
	] );
} );

/**
 * Enqueue comment script and pass config data.
 */
add_action( 'wp_enqueue_scripts', function () {
	if ( ! is_singular() || ! comments_open() ) {
		return;
	}
	wp_enqueue_script( 'comments' );
	wp_localize_script( 'comments', 'KyomComments', [
		'restUrl'    => rest_url( '/' ),
		'postId'     => get_the_ID(),
		'loginUrl'   => wp_login_url( get_permalink() ),
		'contactUrl' => kyom_get_contact_url(),
		'i18n'       => [
			'login'       => __( 'Log in to comment', 'kyom' ),
			'submit'      => __( 'Submit Comment', 'kyom' ),
			'placeholder' => __( 'Write a comment...', 'kyom' ),
			'sending'     => __( 'Sending...', 'kyom' ),
			'success'     => __( 'Comment posted.', 'kyom' ),
			'moderation'  => __( 'Your comment is awaiting moderation.', 'kyom' ),
			'error'       => __( 'Failed to post comment.', 'kyom' ),
			'contact'     => __( 'For private messages, use the contact form.', 'kyom' ),
			'cancel'      => __( 'Cancel', 'kyom' ),
			'replyTo'     => __( 'Replying to %s', 'kyom' ),
		],
	] );
} );
