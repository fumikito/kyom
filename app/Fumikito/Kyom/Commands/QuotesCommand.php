<?php

namespace Fumikito\Kyom\Commands;


use cli\Table;
use Fumikito\Kyom\Service\QuotesCollection;
use PHP_CodeSniffer\Standards\Generic\Tests\CodeAnalysis\ForLoopShouldBeWhileLoopUnitTest;

/**
 * Quotes Collection(deprecated plugin) Support
 *
 * @package kyom
 */
class QuotesCommand extends \WP_CLI_Command {

	/**
	 * Display quotes in table.
	 *
	 * @return void
	 */
	public function display() {
		$quotes = $this->query();
		if ( empty( $quotes ) ) {
			\WP_CLI::error( 'No quotes exist.' );
		}
		$table = new Table();
		$table->setHeaders( [ 'ID', 'Author', 'Source', 'public' ] );
		foreach ( $quotes as $quote ) {
			$table->addRow( [
				$quote->quote_id,
				$quote->author,
				$quote->source,
				$quote->public,
			] );
		}
		$table->display();
	}

	/**
	 * Import from quotes collection.
	 *
	 * @synopsis [--dry-run]
	 * @param array $args  Command arguments.
	 * @param array $assoc Command option
	 * @return void
	 */
	public function migrate( $args, $assoc ) {
		$dry_run = ! empty( $assoc['dry-run'] );
		$quotes = $this->query();
		if ( empty( $quotes ) ) {
			\WP_CLI::error( 'No quotes exist.' );
		}
		$imported = 0;
		$total    = count( $quotes );
		foreach ( $quotes as $quote ) {
			$query = new \WP_Query( [
				'post_type'      => QuotesCollection::get_instance()->post_type,
				'post_status'    => 'any',
				'posts_per_page' => 1,
				'meta_query' => [
					[
						'key'   => '_old_quote_id',
						'value' => $quote->quote_id,
					],
				],
			] );
			$exiting = $query->have_posts() ? $query->posts[0] : null;
			if ( $exiting ) {
				// Already exists.
				if ( $dry_run ) {
					\WP_CLI::warning( 'Quote #%d is already exists. Skip.' );
				} else {
					echo 's';
				}
				continue 1;
			}
			// Should be import.
			if ( $dry_run ) {
				$imported++;
			} else {
				$post_args = [
					'post_type'    => QuotesCollection::get_instance()->post_type,
					'post_name'    => $quote->quote_id,
					'post_content' => $quote->quote,
					'post_date'    => $quote->time_added,
					'post_status'  => ( 'yes' === $quote->public ) ? 'publish' : 'private',
				];
				if ( $quote->time_updated ) {
					$post_args['post_modified'] = $quote->time_updated;
				}
				$inserted = wp_insert_post( $post_args );
				if ( ! $inserted ) {
					echo 'x';
					continue 1;
				}
				update_post_meta( $inserted, '_old_quote_id', $quote->quote_id );
				update_post_meta( $inserted, '_quote_source', $quote->source );
				wp_set_object_terms( $inserted, $quote->author, 'author' );
				echo '.';
				$imported++;
			}
		}
		\WP_CLI::line( '' );
		if ( $dry_run ) {
			\WP_CLI::success( sprintf( '%d of %d quotes will be imported.', $imported, $total ) );
		} else {
			\WP_CLI::line( sprintf( '%d of %d quotes imported.', $imported, $total ) );
			\WP_CLI::confirm( 'Will you drop the database?' );
			// Drop table.
			global $wpdb;
			$query = <<<SQL
				DROP TABLE {$wpdb->prefix}quotescollection
SQL;
			$result = $wpdb->query( $query );
			if ( $result ) {
				\WP_CLI::success( 'Old table are dropped.' );
			} else {
				\WP_CLI::error( 'Failed to drop table.' );
			}
		}
	}

	/**
	 * Get results from quotes.
	 *
	 * @return \stdClass[]
	 */
	protected function query() {
		global $wpdb;
		$query = <<<SQL
			SELECT * FROM {$wpdb->prefix}quotescollection
SQL;
		return array_map( function( $row ) {
			$row->quote_id = (int) $row->quote_id;
			return $row;
		}, $wpdb->get_results( $query ) );

	}
}
