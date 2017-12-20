<?php

namespace Hametuha;
use Symfony\Component\Finder\Finder;

/**
 * Manager
 *
 * @package hametuha
 */
class WpEnqueueManager {

	/**
	 * Constructor.
	 */
	private function __construct() {
		// No constructor.
	}

	/**
	 * Bulk register javascripts.
	 *
	 * @param string $path   Path to JS dir.
	 * @param string $prefix Prefix. Default empty.
	 * @param null $version  Version.
	 * @param bool $footer   Should render in footer. Default true.
	 */
	public static function register_js( $path, $prefix = '', $version = null, $footer = true ) {
		foreach ( self::parse_dir( $path, 'js', $prefix ) as $handle => $data ) {
			if ( is_null( $version ) ) {
				$this_version = filemtime( $data['path'] );
			} else {
				$this_version = $version;
			}
			wp_register_script( $handle, $data['url'], $data['deps'], $this_version, $footer );
		}
	}

	/**
	 * Regsiter all style.
	 *
	 * @param string $path    Path to directory.
	 * @param string $prefix  Prefix.
	 * @param null   $version Version number. Defualt, file modified time.
	 * @param string $screen  Screen. Default all.
	 */
	public static function register_styles( $path, $prefix = '', $version = null, $screen = 'all' ) {
		foreach ( self::parse_dir( $path, 'css', $prefix ) as $handle => $data ) {
			if ( is_null( $version ) ) {
				$this_version = filemtime( $data['path'] );
			} else {
				$this_version = $version;
			}
			wp_register_style( $handle, $data['url'], $data['deps'], $this_version, $screen );
		}
	}

	/**
	 * Path to file.
	 *
	 * @param string $file File to parse dependencies.
	 *
	 * @return array
	 */
	public static function grab_deps( $file ) {
		if ( ! file_exists( $file ) ) {
			return [];
		}
		// @see {wp-includes/functions.php}
		$fp = fopen( $file, 'r' );
		$file_header = fread( $fp, 8192 );
		fclose( $fp );
		// Make sure we catch CR-only line endings.
		$file_header = str_replace( "\r", "\n", $file_header );
		// Grab dependencies.
		$regexp = '#wpdeps=(.*)$#um';
		if ( ! preg_match_all( $regexp, $file_header, $matches ) ) {
			return [];
		} else {
			list( $match, $deps ) = $matches;
			$deps = [];
			foreach ( $matches[1] as $dep ) {
				foreach ( array_map( 'trim', explode( ',', $dep ) ) as $d ) {
					if ( $d ) {
						$deps[] = $d;
					}
				}
			}
			return $deps;
		}
	}

	/**
	 * Grab directory and retrieve file.
	 *
	 * @param string $path       Directory path.
	 * @param string $extension 'css' or 'js'.
	 * @param string $prefix    Handle prefix. Default empty.
	 *
	 * @return array
	 */
	public static function parse_dir( $path, $extension, $prefix = '' ) {
		$extension = ltrim( $extension, '.' );
		$regexp = '#/([^._][^/]*)\.' . $extension . '$#u';
		if ( ! is_dir( $path ) ) {
			return [];
		}
		$files = [];
		$finder = new Finder();
		foreach ( $finder->in( $path )->name( "*.{$extension}" )->files() as $file ) {
			$file_path = $file->getPathname();
			if ( ! preg_match( $regexp, $file_path, $match ) ) {
				continue;
			}
			$handle = $prefix . $match[1];
			$deps = self::grab_deps( $file_path );
			$url = str_replace( ABSPATH, home_url( '/' ), $file_path );
			$files[ $handle ] = [
				'path' => $file_path,
				'deps' => $deps,
				'url'  => $url,
			];
		}
		return $files;
	}


}
