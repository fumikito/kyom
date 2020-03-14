<?php
/**
 * Class SampleTest
 *
 * @package Kyom
 */

/**
 * Sample test case.
 */
class PathFunctionTest extends WP_UnitTestCase {

	/**
	 * A single example test.
	 */
	function test_path() {
		$root_dir = get_template_directory();
		$this->assertEqualSets( $root_dir, kyom_root_dir() );
	}
}
