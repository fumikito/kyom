<?php
/**
 * Author box.
 *
 * 中身はブロック kyom/follow と同じ共有レンダラー（kyom_get_follow_html）で組む。
 * 記事ごとにブロックを挿入しなくても、記事末尾に自動で表示される。
 *
 * @package kyom
 * @var WP_User $author
 */
$author = apply_filters( 'kyom_author_of_post', get_userdata( get_the_author_meta( 'ID' ) ) );
if ( ! $author ) {
	return;
}
kyom_the_follow( [
	'user'  => $author,
	'class' => 'kyom-follow-author-box',
] );
