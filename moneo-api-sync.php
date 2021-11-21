<?php
/**
* Plugin Name: Moneo API sync
* Description: Moneo API sync with Woocommerce products.
* Version: 1.0
* Author: Aleksandr Gilarov
**/

if (! defined('WPINC')) {
    die;
}

foreach(glob(plugin_dir_path(__FILE__) . 'admin/*.php') as $file) {
    include_once $file;
}

foreach(glob(plugin_dir_path(__FILE__) . 'src/*.php') as $file) {
    include_once $file;
}

register_activation_hook(__FILE__, 'activate');

register_deactivation_hook( __FILE__, 'deactivate' );

function activate()
{
	wp_schedule_event(time(), 'daily', 'update_all_leftovers_from_moneo');
}

function deactivate() {
	wp_clear_scheduled_hook( 'update_all_leftovers_from_moneo' );
}

add_action( 'update_all_leftovers_from_moneo', 'update_leftovers' );

function update_leftovers() {
    $api = new Moneo();
    $api->sync_residue();
}

add_action('plugins_loaded', 'moneo_api_sync_admin_settings');

function moneo_api_sync_admin_settings() {
    $plugin = new Submenu( new Submenu_Page());
    $plugin->init();
}

function moneo_update_prices() {
    $api = new Moneo();ā
    $api->sync_prices();
    wp_redirect( admin_url( '/admin.php?page=moneo-sync-plugin' ) );
}
add_action( 'admin_post_moneo_price_update', 'moneo_update_prices' );