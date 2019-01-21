<?php
/**
 * Plugin Name:  WordPress Vue Menus
 * Plugin URI:
 * Description:  A Vue prototype for WordPress menu UI
 * Version:      1.0
 * Author:       Micah Wood
 * Author URI:   https://wpscholar.com
 * License:      GPL2
 * License URI:  https://www.gnu.org/licenses/gpl-2.0.html
 *
 * Bootstrap file for initializing WordPress Vue app for menu UI.
 *
 * @package wp-menu-ui-vue-prototype
 */

if ( file_exists( __DIR__ . '/vendor/autoload.php' ) ) {
	require __DIR__ . '/vendor/autoload.php';
}

if ( function_exists( 'add_action' ) ) {

	add_action(
		'admin_menu',
		function () {

			$title = 'Vue Menus';
			$slug  = 'vue-menus';

			add_menu_page(
				$title,
				$title,
				'manage_options',
				$slug,
				function () use ( $slug ) {

					$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

					wp_enqueue_script(
						$slug,
						plugins_url( "/assets/js/app{$suffix}.js", __FILE__ ),
						[],
						filemtime( __DIR__ . "/assets/js/app{$suffix}.js" ),
						true
					);

					wp_enqueue_style(
						$slug,
						plugins_url( "/assets/css/app{$suffix}.css", __FILE__ ),
						[],
						filemtime( __DIR__ . "/assets/css/app{$suffix}.css" )
					);

					?>
					<div class="wrap">
						<h1 class="wp-heading-inline">Menus</h1>
						<div id="vue-app" data-nonce="<?php echo esc_attr( wp_create_nonce( 'wp_rest' ) ); ?>" data-rest-url="<?php echo esc_attr( get_rest_url() ); ?>"></div>
					</div>
					<?php
				},
				'dashicons-menu'
			);

		}
	);

}
