<?php

namespace Hametuha;

use Hametuha\StringUtility\NamingConventions;
use Hametuha\StringUtility\Path;

/**
 * WordPress' assets manager
 *
 * @package wp-enqueue-manager
 */
class WpEnqueueManager {

	use NamingConventions;
	use Path;

	/**
	 * Constructor.
	 */
	private function __construct() {
		// No constructor.
	}

	/**
	 * Bulk register JavaScripts.
	 *
	 * @param string $path    Path to JS dir.
	 * @param string $prefix  Prefix. Default empty.
	 * @param null   $version Version.
	 * @param bool   $footer  Should render in footer. Default true.
	 */
	public static function register_js( $path, $prefix = '', $version = null, $footer = true ) {
		$function = function () use ( $path, $prefix, $version, $footer ) {
			foreach ( self::parse_dir( $path, 'js', $prefix ) as $handle => $data ) {
				if ( is_null( $version ) ) {
					$this_version = md5_file( $data['path'] );
				} else {
					$this_version = $version;
				}
				wp_register_script( $handle, $data['url'], $data['deps'], $this_version, $footer );
			}
		};
		if ( did_action( 'init' ) ) {
			$function();
		} else {
			add_action( 'init', $function );
		}
	}

	/**
	 * Register all style.
	 *
	 * @param string $path    Path to directory.
	 * @param string $prefix  Prefix.
	 * @param null   $version Version number. Defualt, file modified time.
	 * @param string $screen  Screen. Default all.
	 */
	public static function register_styles( $path, $prefix = '', $version = null, $screen = 'all' ) {
		$function = function () use ( $path, $prefix, $version, $screen ) {
			foreach ( self::parse_dir( $path, 'css', $prefix ) as $handle => $data ) {
				if ( is_null( $version ) ) {
					$this_version = md5_file( $data['path'] );
				} else {
					$this_version = $version;
				}
				wp_register_style( $handle, $data['url'], $data['deps'], $this_version, $screen );
			}
		};
		if ( did_action( 'init' ) ) {
			$function();
		} else {
			add_action( 'init', $function );
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
		$fp          = fopen( $file, 'r' );
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
			$found                = [];
			foreach ( $deps as $dep ) {
				foreach ( array_map( 'trim', explode( ',', $dep ) ) as $d ) {
					if ( $d ) {
						$found[] = $d;
					}
				}
			}
			return $found;
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
		$regexp    = '#([^._][^/]*)\.' . $extension . '$#u';
		if ( ! is_dir( $path ) ) {
			return [];
		}
		$files = [];
		foreach ( self::recursive_parse( $path, $regexp ) as $file_path ) {
			$base_name = basename( $file_path );
			if ( ! preg_match( $regexp, $base_name, $match ) ) {
				continue;
			}
			$handle           = $prefix . $match[1];
			$deps             = self::grab_deps( $file_path );
			$url              = self::url( $file_path );
			$files[ $handle ] = [
				'path' => $file_path,
				'deps' => $deps,
				'url'  => $url,
			];
		}
		return $files;
	}

	/**
	 * Parse directory and fetch vars.
	 *
	 * @param string $dir Directory name.
	 *
	 * @return array
	 */
	public static function register_js_var_files( $dir ) {
		if ( ! is_dir( $dir ) ) {
			return [];
		}
		$registered = [];
		$files      =  self::recursive_parse( $dir, '#^[^_.].*\.php$#u' );
		foreach ( $files as $path ) {
			$file_name = basename( $path );
			$handle    = str_replace( '.php', '', $file_name );
			$var_name  = self::camelize( $handle );
			$vars      = include $path;
			if ( ! is_array( $vars ) ) {
				continue;
			}
			$vars = apply_filters( 'wp_enqueue_manager_vars', $vars, $handle );
			wp_localize_script( $handle, $var_name, $vars );
			$registered[ $handle ] = [
				'name' => $var_name,
				'vars' => $vars,
			];
		}
		return $registered;
	}

	/**
	 * Parse directory and returns file names matchin preg.
	 *
	 * @param string   $dir   Directory name.
	 * @param string   $preg  PCRE regexp to filter files.
	 * @param string[] $files File list.
	 *
	 * @return string[]
	 */
	public static function recursive_parse( $dir, $preg, $files = [] ) {
		if ( ! is_dir( $dir ) ) {
			return $files;
		}
		$dir = rtrim( $dir, DIRECTORY_SEPARATOR );
		foreach ( scandir( $dir ) as $file ) {
			if ( in_array( $file, [ '.', '..' ], true ) ) {
				continue;
			}
			$path = $dir . DIRECTORY_SEPARATOR . $file;
			if ( is_dir( $path ) ) {
				$files = self::recursive_parse( $path, $preg, $files );
			} elseif ( preg_match( $preg, $file ) ) {
				$files[] = $path;
			}
		}
		return $files;
	}

	/**
	 * Make kebab case and snake case to camel case.
	 *
	 * @param string $text String to be cameled.
	 *
	 * @return string
	 */
	public static function camelize( $text ) {
		$self = new self();
		return $self->kebab_to_camel( $self->snake_to_kebab( $text ), true );
	}

	/**
	 * Convert file path to URL.
	 *
	 * @param string $file_path Original file path.
	 * @return string
	 */
	public static function url( $file_path ) {
		$self = new self();
		return $self->path_to_url( $file_path );
	}
}
