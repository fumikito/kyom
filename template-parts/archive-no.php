<?php
$image       = kyom_not_found_image();
$text_color  = get_option( 'kyom_not_found_text_color', '' );
$color_style = $text_color ? sprintf( 'color: %s;', esc_attr( $text_color ) ) : '';
?>

<header class="no-content-header">
	<?php if ( $image ) : ?>
	<div class="no-content-cover" title="<?php echo esc_attr( $image['credit'] ); ?>" style="<?php echo $image ? sprintf( 'background-image: url(\'%s?%s\')', esc_url( $image['url'] ), esc_attr( $image['version'] ) ) : ''; ?>"  uk-parallax="bgy:-300"></div>
	<?php endif; ?>
	<div class="no-content-box" <?php echo $color_style ? 'style="' . $color_style . '"' : ''; ?>>
		<div class="entry-container">
			<h1 class="no-content-title"><?php esc_html_e( 'Sorry, but nothing found for your criteria.', 'kyom' ); ?></h1>
			<p class="no-content-lead">
				<?php echo wp_kses_post( __( 'Please try <a href="#no-content">search</a> or other options.', 'kyom' ) ); ?>
			</p>
		</div>
	</div>
</header>


<div class="entry-container no-content" id="no-content">
	
	<?php
	$todo = kyom_todo_when_404();
	if ( $todo ) :
		?>
	<ul class="uk-list uk-list-large uk-list-bullet no-content-todo">
		<?php foreach ( $todo as $key => $description ) : ?>
		<li><?php echo wp_kses_post( $description ); ?></li>
		<?php endforeach; ?>
	</ul>
	<?php endif; ?>

	<h2 class="no-content-subtitle"><?php esc_html_e( 'Search Form', 'kyom' ); ?></h2>
	<?php get_search_form(); ?>

	<?php do_action( 'kyom_after_search_form' ); ?>
	
</div>
