<?php
namespace WPEloquent;

/**
 * Class ActionScheduler_Versions
 */
class Versions {
	/**
	 * Class instance.
	 *
	 * @var self
	 */
	private static $instance = null;

	/**
	 * Versions.
	 *
	 * @var array<string, callable>
	 */
	private $versions = array();

	/**
	 * Registered sources.
	 *
	 * @var array<string, string>
	 */
	private $sources = array();

	/**
	 * Register version's callback.
	 *
	 * @param string   $version_string          Eloquent version.
	 * @param callable $initialization_callback Callback to initialize the version.
	 */
	public function register( $version_string, $initialization_callback ) {
		if ( isset( $this->versions[ $version_string ] ) ) {
			return false;
		}

		// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_debug_backtrace
		$backtrace = debug_backtrace( DEBUG_BACKTRACE_IGNORE_ARGS );
		$source    = $backtrace[0]['file'];

		$this->versions[ $version_string ] = $initialization_callback;
		$this->sources[ $source ]          = $version_string;
		return true;
	}

	/**
	 * Get all versions.
	 */
	public function get_versions() {
		return $this->versions;
	}

	/**
	 * Get registered sources.
	 *
	 * @return array<string, string>
	 */
	public function get_sources() {
		return $this->sources;
	}

	/**
	 * Get latest version registered.
	 */
	public function latest_version() {
		$keys = array_keys( $this->versions );
		if ( empty( $keys ) ) {
			return false;
		}
		uasort( $keys, 'version_compare' );
		return end( $keys );
	}

	/**
	 * Get callback for latest registered version.
	 */
	public function latest_version_callback() {
		$latest = $this->latest_version();

		if ( empty( $latest ) || ! isset( $this->versions[ $latest ] ) ) {
			return '__return_null';
		}

		return $this->versions[ $latest ];
	}

	/**
	 * Get instance.
	 *
	 * @return self
	 */
	public static function instance() {
		if ( empty( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Initialize.
	 */
	public static function initialize_latest_version() {
		$self = self::instance();
		call_user_func( $self->latest_version_callback() );
	}
}