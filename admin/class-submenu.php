<?php
/* Creates the submenu item for the plugin.
*
* @package Custom_Admin_Settings
*/

class Submenu {

    private $page;

    public function __construct($page) {
        $this->page = $page;
    }

    public function init() {
        add_action('admin_menu', [$this, 'test_plugin_setup_menu']);
    }

    public function test_plugin_setup_menu(){
        add_menu_page( 'MONEO API Page', 'MONEO', 'manage_options', 'moneo-sync-plugin', [$this->page, 'render'] );
    }
}