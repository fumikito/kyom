<?php
/**
 * User related functions
 *
 * @package kyom
 */


/**
 * Get site representativ
 *
 * @return null|WP_User
 */
function kyom_get_owner() {
	$users = new WP_User_Query( [
		'role'   => 'administrator',
		'number' => 1,
	] );
	$users = $users->get_results();
	if ( $users ) {
		$user = current( $users );
	} else {
		$user = null;
	}

	/**
	 * kyom_admin_user
	 *
	 * Get the representative of this blog.
	 *
	 * @param WP_User|null $user
	 */
	return apply_filters( 'kyom_admin_user', $user );
}

/**
 * Get site representative user's contact methods.
 *
 * @param null|WP_User $user
 * @param bool         $with_label
 * @return array
 */
function kyom_get_social_links( $user = null, $with_label = false ) {
	if ( ! $user ) {
		$user = kyom_get_owner();
	}
	if ( ! $user ) {
		return [];
	}
	$methods = [];
	$page    = get_post( get_option( 'newsletter_page', 0 ) );
	if ( $page ) {
		$methods['mail'] = [
			'label' => __( 'Newsletter', 'kyom' ),
			'url'   => get_permalink( $page ),
		];
	}
	foreach ( wp_get_user_contact_methods( $user ) as $key => $label ) {
		$meta = get_user_meta( $user->ID, $key, true );
		if ( $meta ) {
			$methods[ $key ] = $with_label ? [
				'label' => preg_replace( '/ URL$/u', '', $label ),
				'url'   => $meta,
			] : $meta;
		}
	}

	return $methods;
}

/**
 * Get contact page URL.
 *
 * @return string Empty string if not set.
 */
function kyom_get_contact_url() {
	$page_id = get_option( 'kyom_contact_page_id', 0 );
	if ( ! $page_id ) {
		return '';
	}
	return get_permalink( $page_id );
}

/**
 * Detect if comment is primary.
 *
 * @param WP_Comment $comment
 *
 * @return bool
 */
function kyom_is_primary_comment( $comment ) {
	$primary = false;
	$post    = get_post( $comment->comment_post_ID );
	if ( (int) $comment->user_id === (int) $post->post_author ) {
		$primary = true;
	} else {
		$user_id = email_exists( $comment->comment_author_email );
		if ( $user_id && $user_id === (int) $post->post_author ) {
			$primary = true;
		}
	}

	/**
	 * kyom_is_primary_comment
	 *
	 * Detect if comment is primary.
	 *
	 * @param bool       $primary
	 * @param WP_Comment $comment
	 * @param WP_Post    $post
	 */
	return apply_filters( 'kyom_is_primary_comment', $primary, $comment, $post );
}

/**
 * Get last logged in time.
 *
 * @param int|false $user_id
 * @return string
 */
function kyom_get_last_login( $user_id = false ) {
	if ( ! $user_id ) {
		$user_id = get_current_user_id();
	}
	$last_login     = get_user_meta( $user_id, 'last_login', true );
	$date_format    = get_option( 'date_format' ) . ' ' . get_option( 'time_format' );
	$the_last_login = mysql2date( $date_format, $last_login, false );
	return $the_last_login;
}

/**
 * Get user role
 *
 * @return string
 */
function kyom_get_user_role() {
	if ( current_user_can( 'edit_others_posts' ) ) {
		$role = 'editor';
	} elseif ( current_user_can( 'edit_posts' ) ) {
		$role = 'author';
	} elseif ( is_user_logged_in() ) {
		$role = 'subscriber';
	} else {
		$role = 'anonymous';
	}
	return apply_filters( 'kyom_analytics_user_role', $role );
}
