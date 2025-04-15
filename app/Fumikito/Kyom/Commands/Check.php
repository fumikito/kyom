<?php

namespace Fumikito\Kyom\Commands;


use cli\Table;

/**
 * Check kyom utitlies.
 */
class Check extends \WP_CLI_Command {

	/**
	 * Check if ranking works.
	 *
	 * ## OPTIONS
	 *
	 * : <filter>
	 *     Filter expression for Google Analytics Core Reporting API.
	 *
	 * : [--days=<days>]
	 *    Days to check. Optiona.
	 *
	 * @synopsis <filter> [--days=<days>]
	 * @param array $args  Command arguments.
	 * @param array $assoc Command option.
	 * @return void
	 */
	public function ranking( $args, $assoc ) {
		list( $filter ) = $args;
		$days           = $assoc['days'] ?? 30;
		$result         = kyom_get_ranking( $days, 10, $filter );
		if ( empty( $result ) ) {
			\WP_CLI::error( __( 'No result found.', 'kyom' ) );
		}
		$table = new Table();
		$table->setHeaders( [ 'Rank', 'ID', 'PV' ] );
		foreach ( $result as $row ) {
			$table->addRow( [ $row['rank'], $row['post'], $row['pv'] ] );
		}
		$table->display();
	}
}
