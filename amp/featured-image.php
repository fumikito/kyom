<?php
/**
 * Post featured image template part.
 *
 * @package kyom
 */

/**
 * Context.
 *
 * @var AMP_Post_Template $this
 */

$featured_image = $this->get( 'featured_image' );

if ( empty( $featured_image ) ) {
	return;
}

$amp_html = $featured_image['amp_html'];

$amp_html = preg_replace( '#<img #u', '<img layout="fill" ', $amp_html );

?>
<figure class="amp-wp-article-featured-image wp-caption">
	<?php echo $amp_html; ?>
</figure>
