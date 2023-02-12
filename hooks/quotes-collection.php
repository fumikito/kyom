<?php
/**
 * Related to quotes collection.
 *
 * @package kyom
 */

if ( defined( 'WP_CLI' ) && WP_CLI ) {
	// If CLI register command.
	WP_CLI::add_command( 'quotes', \Fumikito\Kyom\Commands\QuotesCommand::class );
}

// Enable quotes collection.
\Fumikito\Kyom\Service\QuotesCollection::get_instance();
