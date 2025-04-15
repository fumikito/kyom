<?php do_action( 'kyom_before_sidebar' ); ?>
<?php if ( is_active_sidebar( 'normal-sidebar' ) ) : ?>
	<div class="sidebar-container">
		<aside class="sidebar normal-widget-wrapper">
			<?php dynamic_sidebar( 'normal-sidebar' ); ?>
		</aside>
	</div>
<?php endif; ?>
<?php do_action( 'kyom_after_sidebar' ); ?>
