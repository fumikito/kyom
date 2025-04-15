<?php

/**
 * Filter wp_die rendering function
 * @param string $function
 * @return string
 */
add_filter( 'wp_die_handler', function () {
	return function ( $message, $title = '', $args = [] ) {
		/** @var WP_Error|string $message */
		$defaults = array( 'response' => 500 );
		$r        = wp_parse_args($args, [
			'response'  => 500,
			'back_link' => '',
		] );

		if ( is_wp_error( $message ) ) {
			if ( empty( $title ) ) {
				$error_data = $message->get_error_data();
				if ( is_array( $error_data ) && isset( $error_data['title'] ) ) {
					$title = $error_data['title'];
				}
			}
			$errors = $message->get_error_messages();
			switch ( count( $errors ) ) :
				case 0:
					$message = '';
					break;
				case 1:
					$message = "<p class=\"message warning\">{$errors[0]}</p>";
					break;
				default:
					$message = "<ul class=\"message warning\">\n\t\t<li>" . join( "</li>\n\t\t<li>", $errors ) . "</li>\n\t</ul>";
					break;
			endswitch;
		} elseif ( is_string( $message ) ) {
			$message = sprintf( '<p class="message warning">%s</p>', wp_kses_post( $message ) );
		}

		if ( $r['back_link'] ) {
			$message .= sprintf( "\n<p><a class=\"uk-button uk-button-secondary uk-button-large\" href='javascript:history.back()'>%s</a></p>", esc_html__( 'Back to Previous Page', 'kyom' ) );
		}

		$admin_dir = 'wp-admin/';

		if ( ! did_action( 'admin_head' ) ) :
			if ( ! headers_sent() ) {
				status_header( $r['response'] );
				nocache_headers();
				header( 'Content-Type: text/html; charset=utf-8' );
			}

			if ( empty( $title ) ) {
				$title = sprintf( '%s %s', $r['response'], get_status_header_desc( $r['response'] ) );
			}
			add_filter( 'document_title_parts', function () use ( $r ) {
				return [
					'status' => $r['response'],
					'title'  => get_status_header_desc( $r['response'] ),
					'site'   => get_bloginfo( 'title' ),
				];
			} );
			?>
			<!DOCTYPE html>
			<!-- Ticket #11289, IE bug fix: always pad the error page with enough characters such that it is greater than 512 bytes, even after gzip compression abcdefghijklmnopqrstuvwxyz1234567890aabbccddeeffgghhiijjkkllmmnnooppqqrrssttuuvvwwxxyyzz11223344556677889900abacbcbdcdcededfefegfgfhghgihihjijikjkjlklkmlmlnmnmononpopoqpqprqrqsrsrtstsubcbcdcdedefefgfabcadefbghicjkldmnoepqrfstugvwxhyz1i234j567k890laabmbccnddeoeffpgghqhiirjjksklltmmnunoovppqwqrrxsstytuuzvvw0wxx1yyz2z113223434455666777889890091abc2def3ghi4jkl5mno6pqr7stu8vwx9yz11aab2bcc3dd4ee5ff6gg7hh8ii9j0jk1kl2lmm3nnoo4p5pq6qrr7ss8tt9uuvv0wwx1x2yyzz13aba4cbcb5dcdc6dedfef8egf9gfh0ghg1ihi2hji3jik4jkj5lkl6kml7mln8mnm9ono -->
			<html lang="<?php echo kyom_get_content_locale(); ?>">
				<head>
					<meta charset="<?php bloginfo( 'charset' ); ?>" />
					<meta http-equiv="X-UA-Compatible" content="IE=edge" />
					<meta name="viewport" content="width=device-width,initial-scale=1.0,minimum-scale=1.0,shrink-to-fit=no" />
					<?php wp_head(); ?>
				</head>
				<body <?php body_class(); ?>>
				<header class="site-header uk-sticky uk-sticky-fixed" uk-sticky>

				<nav class="uk-navbar-container uk-navbar" uk-navbar="boundary-align: true; align: center;">

				<div class="uk-navbar-center">
					<a href="<?php echo home_url(); ?>" class="custom-logo-link">
						<?php if ( has_custom_logo() ) : ?>
							<?php echo strip_tags( get_custom_logo(), '<img>' ); ?>
						<?php else : ?>
							<span class="site-header-title"><?php bloginfo( 'name' ); ?></span>
						<?php endif; ?>
					</a>
				</div>
				</nav>
		
				</header>
			<?php endif; ?>
			
			<main class="kyom-die">
				<div class="uk-container">
					<div class="kyom-die-inner">
						<h1 class="uk-heading-line uk-text-center">
							<span><?php echo esc_html( $title ); ?></span>
						</h1>
						<div class="kyom-die-body">
							<?php echo $message; ?>
						</div>
					</div>
				</div>
			</main>
		
			<?php if ( ! did_action( 'admin_head' ) ) : ?>
			<footer class="site-footer">
				<div class="uk-container">
					<p class="site-footer-copy">
						&copy; <?php echo kyom_oldest_date(); ?> <a href="<?php echo home_url(); ?>" rel="home"><?php bloginfo( 'name' ); ?></a>
					</p>
				</div>
			</footer>
				<!-- .error404 ends -->
				<?php wp_footer(); ?>
			<?php else : ?>
				<?php do_action( 'admin_footer' ); ?>
			<?php endif; ?>
			</body>
		</html>
		<?php
		exit;
	};
}, 1000 );
