<?php
/**
 * Function test
 *
 * @package makibishi
 */

use Hametuha\WpEnqueueManager;

/**
 * Sample test case.
 */
class WpEnqueueManager_Basic_Test extends WP_UnitTestCase {

	/**
	 * A single example test
	 */
	function test_deps() {
		// Check grabber.
		$js = __DIR__ .'/assets/dependency-check.js';
		$deps = WpEnqueueManager::grab_deps( $js );
		$this->assertEquals( [ 'jquery', 'jquery-ui', 'thickbox' ], $deps );
		// Check no deps.
		$no_deps = __DIR__ . '/assets/nodep.js';
		$this->assertEmpty( WpEnqueueManager::grab_deps( $no_deps ) );
		// Check CSS.
		$css = __DIR__ . '/assets/style.css';
		$deps = WpEnqueueManager::grab_deps( $css );
		$this->assertEquals( [ 'wordpress', 'materialize', 'bootstrap' ], $deps );
	}

	/**
	 * JS test
	 */
	function test_parser() {
		// Check CSS.
		$asset_path = __DIR__ . '/assets';
		$parsed = WpEnqueueManager::parse_dir( $asset_path, 'css', 'test-' );
		$this->assertEquals( $parsed['test-style']['url'], $asset_path . '/style.css' );
		$this->assertEquals( count( $parsed['test-style']['deps'] ), 3 );
		// Does sub directory exists?
		$this->assertEquals( $parsed['test-sub']['url'], $asset_path . '/sub/sub.css' );
		// Check JS.
		$parsed = WpEnqueueManager::parse_dir( $asset_path, 'js', 'test-' );
		$this->assertEmpty( $parsed['test-nodep']['deps'] );
	}

	/**
	 * Test string helpers.
	 */
	function test_string() {
		$this->assertEquals( 'MyClassName', WpEnqueueManager::camelize( 'my-class_name' ) );
	}

	/**
	 * Test wp_localize_script
	 */
	function test_vars() {
		$normal_vars = WpEnqueueManager::register_js_var_files( __DIR__ . '/vars' );
		$this->assertTrue( isset( $normal_vars['my-js-vars'] ) );
		$this->assertEquals( 'MyJsVars', $normal_vars['my-js-vars']['name'] );
		$this->assertEquals( 'B', $normal_vars['my-js-vars']['vars']['a'] );
	}
}
