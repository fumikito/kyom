<?php

namespace Fumikito\Kyom\Service;


use Hametuha\SingletonPattern\Singleton;

/**
 * JSON-LD provide.
 *
 * サイトの主（kyom_get_owner）を Person とし、ページの種類ごとに出し分ける。
 *
 * - トップ: Person（全項目）と WebSite（publisher がその Person）
 * - プロフィールページ: ProfilePage（mainEntity がその Person）
 * - 投稿: BlogPosting（author がその Person）
 *
 * Person の @id はどこでも同じにして同一人物だと示す。ただし Google は
 * 他のページの @id を取りに行かないので、参照側にも name と url を必ず添える。
 * 値はすべてユーザープロフィールから取り、テーマには個人の情報を持たない。
 *
 * @package kyom
 */
class JsonLdProvider extends Singleton {

	/**
	 * Register hooks.
	 *
	 * @return void
	 */
	protected function init() {
		add_action( 'wp_head', [ $this, 'do_json_ld' ] );
	}

	/**
	 * Get JSON-LD.
	 *
	 * @return array
	 */
	public function get_json_ld() {
		$jsons = [];
		$owner = kyom_get_owner();
		if ( $owner ) {
			if ( is_front_page() ) {
				$jsons[] = $this->with_context( $this->get_person( $owner ) );
				$jsons[] = $this->get_website();
			} elseif ( $this->is_profile_page() ) {
				$jsons[] = $this->get_profile_page( get_queried_object(), $owner );
			} elseif ( is_singular( 'post' ) ) {
				$jsons[] = $this->get_blog_posting( get_queried_object(), $owner );
			}
		}
		/**
		 * kyom_json_ld
		 *
		 * @param array[] $jsons JSON-LD objects to render.
		 */
		return apply_filters( 'kyom_json_ld', $jsons );
	}

	/**
	 * Person の @id。
	 *
	 * @return string
	 */
	public function person_id() {
		return home_url( '/#person' );
	}

	/**
	 * プロフィールページを表示中か。
	 *
	 * @return bool
	 */
	public function is_profile_page() {
		$page_id = (int) get_option( 'kyom_profile_page_id', 0 );
		return $page_id && is_page( $page_id );
	}

	/**
	 * サイトの主を表す Person。
	 *
	 * @param \WP_User $user 対象ユーザー。
	 * @return array
	 */
	public function get_person( $user ) {
		$person   = $this->get_person_ref( $user );
		$optional = [
			'image'         => get_avatar_url( $user->ID, [ 'size' => 512 ] ),
			'alternateName' => get_user_meta( $user->ID, KYOM_USER_ALTERNATE_NAME_KEY, true ),
			'jobTitle'      => get_user_meta( $user->ID, KYOM_USER_ROLE_TITLE_KEY, true ),
			'description'   => wp_strip_all_tags( get_user_meta( $user->ID, 'description', true ) ),
		];
		foreach ( $optional as $key => $value ) {
			if ( $value ) {
				$person[ $key ] = $value;
			}
		}
		$works_for_name = get_user_meta( $user->ID, KYOM_USER_WORKS_FOR_NAME_KEY, true );
		if ( $works_for_name ) {
			$person['worksFor'] = [
				'@type' => 'Organization',
				'name'  => $works_for_name,
			];
			$works_for_url = get_user_meta( $user->ID, KYOM_USER_WORKS_FOR_URL_KEY, true );
			if ( $works_for_url ) {
				$person['worksFor']['url'] = $works_for_url;
			}
		}
		$same_as = $this->get_same_as( $user );
		if ( $same_as ) {
			$person['sameAs'] = $same_as;
		}
		return $person;
	}

	/**
	 * 他のページから Person を指すときの最小形。
	 *
	 * @param \WP_User $user 対象ユーザー。
	 * @return array
	 */
	public function get_person_ref( $user ) {
		return [
			'@type' => 'Person',
			'@id'   => $this->person_id(),
			'name'  => $user->display_name,
			'url'   => home_url( '/' ),
		];
	}

	/**
	 * 同一人物を指す外部URL。
	 *
	 * SNS の連絡先情報に、プロフィールで足した URL（Wikipedia など）を加える。
	 * ニュースレターはサイト内のページなので除く。クエリ文字列は
	 * アフィリエイトタグ等の付随物なので落とす。
	 *
	 * @param \WP_User $user 対象ユーザー。
	 * @return string[]
	 */
	public function get_same_as( $user ) {
		$links = kyom_get_social_links( $user );
		unset( $links['mail'] );
		$extra = preg_split( '/\R/u', (string) get_user_meta( $user->ID, KYOM_USER_SAME_AS_KEY, true ) );
		$urls  = [];
		foreach ( array_merge( array_values( $links ), $extra ) as $url ) {
			$url = trim( strtok( (string) $url, '?' ) );
			if ( kyom_is_profile_url( $url ) ) {
				$urls[] = $url;
			}
		}
		return array_values( array_unique( $urls ) );
	}

	/**
	 * サイトを表す WebSite。
	 *
	 * @return array
	 */
	public function get_website() {
		return $this->with_context( [
			'@type'      => 'WebSite',
			'@id'        => home_url( '/#website' ),
			'name'       => get_bloginfo( 'name' ),
			'url'        => home_url( '/' ),
			'inLanguage' => get_bloginfo( 'language' ),
			'publisher'  => [
				'@id' => $this->person_id(),
			],
		] );
	}

	/**
	 * プロフィールページを表す ProfilePage。
	 *
	 * @param \WP_Post $post  プロフィールページ。
	 * @param \WP_User $owner サイトの主。
	 * @return array
	 */
	public function get_profile_page( $post, $owner ) {
		return $this->with_context( [
			'@type'        => 'ProfilePage',
			'url'          => get_permalink( $post ),
			'name'         => $this->plain_title( $post ),
			'dateModified' => get_post_modified_time( 'c', false, $post ),
			'mainEntity'   => $this->get_person( $owner ),
		] );
	}

	/**
	 * 投稿を表す BlogPosting。
	 *
	 * 著者がサイトの主なら Person を参照し、そうでなければ名前だけを出す。
	 *
	 * @param \WP_Post $post  投稿。
	 * @param \WP_User $owner サイトの主。
	 * @return array
	 */
	public function get_blog_posting( $post, $owner ) {
		$url     = get_permalink( $post );
		$posting = [
			'@type'            => 'BlogPosting',
			'headline'         => $this->plain_title( $post ),
			'url'              => $url,
			'mainEntityOfPage' => $url,
			'datePublished'    => get_post_time( 'c', false, $post ),
			'dateModified'     => get_post_modified_time( 'c', false, $post ),
			'isPartOf'         => [
				'@id' => home_url( '/#website' ),
			],
		];
		if ( (int) $post->post_author === $owner->ID ) {
			$posting['author'] = $this->get_person_ref( $owner );
		} else {
			$posting['author'] = [
				'@type' => 'Person',
				'name'  => get_the_author_meta( 'display_name', $post->post_author ),
			];
		}
		$image = get_the_post_thumbnail_url( $post, 'full' );
		if ( $image ) {
			$posting['image'] = $image;
		}
		return $this->with_context( $posting );
	}

	/**
	 * タグや実体参照を除いたタイトル。
	 *
	 * @param \WP_Post $post 投稿。
	 * @return string
	 */
	protected function plain_title( $post ) {
		return html_entity_decode( wp_strip_all_tags( get_the_title( $post ) ), ENT_QUOTES, 'UTF-8' );
	}

	/**
	 * トップレベルのオブジェクトに @context を付ける。
	 *
	 * @param array $json JSON-LD object.
	 * @return array
	 */
	protected function with_context( $json ) {
		return array_merge( [ '@context' => 'https://schema.org' ], $json );
	}

	/**
	 * Render JSON-LD in WP head.
	 *
	 * @return void
	 */
	public function do_json_ld() {
		foreach ( $this->get_json_ld() as $json ) {
			// JSON_HEX_TAG escapes "<" so "</script>" in user input can't close the tag.
			printf( "<script type=\"application/ld+json\">\n%s\n</script>\n", wp_json_encode( $json, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG ) );
		}
	}
}
