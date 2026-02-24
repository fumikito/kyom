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
