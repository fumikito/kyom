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
		$this->assertTrue( is_dir( kyom_namespace_root_dir() ) );
	}
}
