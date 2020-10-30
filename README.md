# WordPress Commercial Plugin Updater

An abstract class for use within WordPress plugins that hooks into the WordPress update check to define an alternate package URL. With this library you can seamlessly update your commercial plugin (that doesn't live in the WordPress.org repository) in the backendend alongside plugins in the repository.

## Usage
Install this library with composer
```sh
composer require tarecord/wp_commercial_updater
```

Within your plugin, extend the class and implement the required methods (`get_latest_version()`, `get_url()`, and `get_package_url()`)

Example:
```php
<?php
use Tarecord\WP_Commercial_Updater;

class TAR_Updater extends WP_Commercial_Updater {

	/**
	 * Return the latest version number of the plugin.
	 *
	 * @return string
	 */
	protected function get_latest_version()
	{
        // Do anything you need to provide the latest version number.
        // for example: perform a wp_remote_get() request to a Github repository retrieving the latest release or call out to your own server that hosts the plugin zip.
		return '1.0.0';
	}

	/**
	 * Get the plugin url.
	 *
	 * @return string The URL.
	 */
	protected function get_url()
	{
		return 'https://github.com/tarecord/my-plugin';
	}

	/**
	 * Get the package url.
	 *
	 * @return string The package URL.
	 */
	protected function get_package_url()
	{
		return 'https://github.com/tarecord/my-plugin/archive/v1.0.0.zip';
	}

}
```

Once you have implemented the required methods, initialize the class in your plugin using the `init()` method.
```php
// Initialize your extension of the class passing in the current plugin version and slug.
$updater = new TAR_Updater( '1.0.0', 'my-super-cool-plugin' );

// Initialize the class which sets up the filters for `transient_update_plugins` and `site_transient_update_plugins`
$updater->init();
```

## Using a private server
If you're planning to use your own server to host your plugin, here's an example of how you might achieve this.
```php
<?php
use Tarecord\WP_Commercial_Updater;

class TAR_Updater extends WP_Commercial_Updater {
    /**
     * Get plugin version from distribution server.
     * 
     * @return string|WP_Error The plugin version or an instance of WP_Error.
     */
    protected function get_latest_version() {
        $response = wp_remote_get(
            'https://plugins.example.com/my-plugin/details.json',
            array(
                'headers' => array(
                    'Authorization' => 'Basic '. base64_encode( 'your-username' . ':' . 'your-password')
                )
            )
        );

        if ( ! is_wp_error( $response ) ) {
            $data = json_decode( wp_remote_retrieve_body( $response ) );
            return 
        } else {
            error_log( $response->get_error_message );
        }

        return $response;
    }

    /**
	 * Get the plugin url.
	 *
	 * @return string The URL.
	 */
	protected function get_url()
	{
		return 'https://example.com/my-plugin';
	}

	/**
	 * Get the package url.
	 *
	 * @return string The package URL.
	 */
	protected function get_package_url()
	{
		return 'https://plugins.example.com/my-plugin/latest.zip';
	}
}
```
