<?php
/**
 * Filters the 'update_plugins' transient to define when there's an update and where the package can be downloaded.
 *
 * @package TARecord\WP_Commercial_Updater
 * @since 0.1.0
 */
namespace Tarecord\WP_Commercial_Updater;

abstract class WP_Commercial_Updater {

	/**
	 * The current version of the plugin.
	 *
	 * @var string
	 */
	private $current_version;

	/**
	 * The plugin slug.
	 *
	 * @var string
	 */
	private $slug;

	/**
	 * The constructor.
	 *
	 * @param string $current_version The current plugin version.
	 * @param string $slug            The slug of the plugin.
	 */
	public function __construct( string $current_version, string $slug )
	{
		$this->current_version = $current_version;
		$this->slug = $slug;
	}

	/**
	 * Returns the latest version of the plugin.
	 *
	 * @return string|WP_Error The version number or instance of WP_Error.
	 */
	abstract protected function get_latest_version();

	/**
	 * Returns the url for the plugin.
	 *
	 * @return string The url.
	 */
	abstract protected function get_url();

	/**
	 * Returns the package url for the plugin.
	 *
	 * @return string The package url.
	 */
	abstract protected function get_package_url();

	/**
	 * Hook into the 'update_plugins' transients.
	 */
	public function init()
	{
		add_filter( 'transient_update_plugins', array( $this,  'filter_plugin_update_data' ) );
		add_filter( 'site_transient_update_plugins', array( $this,  'filter_plugin_update_data' ) );
	}

	/**
	 * Filters the 'update_plugins' transients so that the plugin can be updated without using the WordPress.org repository.
	 *
	 * @param object $update_plugins The object detailing which plugins have updates available.
	 */
	public function filter_plugin_update_data( $update_plugins )
	{
		if ( ! is_object( $update_plugins ) ) {
			return $update_plugins;
		}

		// Exit if the plugin is not contained in the 'checked' array.
		if ( ! isset( $update_plugins->checked[ $this->slug . '/' . $this->slug . '.php' ] ) ) {
			return $update_plugins;
		}

		if ( ! isset( $update_plugins->response ) || ! is_array( $update_plugins->response ) ) {
			$update_plugins->response = array();
		}

		// Only set the response if the plugin has a new release.
        if ( version_compare( $this->current_version, $this->get_latest_version() ) ) {
			$update_plugins->response[ $this->slug . '/' . $this->slug . '.php'] = $this->get_plugin_response_data();
        }

		return $update_plugins;
	}

	/**
	 * Gets the plugin response data to use if there is a new version of the plugin.
	 *
	 * @return object The response object providing the plugin details: slug, version, url, and package location.
	 */
	protected function get_plugin_response_data()
	{
		return (object) array(
			'slug'         => $this->slug,
			'new_version'  => $this->latest_version,
			'url'          => $this->get_url(),
			'package'      => $this->get_package_url(),
		);
	}
}
