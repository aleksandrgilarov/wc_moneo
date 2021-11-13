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
	wp_schedule_event(time(), 'hourly', 'update_all_leftovers_from_moneo'); // or daily
}

function deactivate() {
	wp_clear_scheduled_hook( 'update_all_leftovers_from_moneo' );
}

add_action( 'update_all_leftovers_from_moneo', 'update_leftovers' );

function update_leftovers() {
    $api = new Moneo();
    $api->sync();
}

add_action('plugins_loaded', 'moneo_api_sync_admin_settings');

function moneo_api_sync_admin_settings() {
    $plugin = new Submenu( new Submenu_Page());
    $plugin->init();
}
