<?php 
class WP_Magic_Link_Auth_Admin {

    public function init() {
        add_action('admin_menu', array($this, 'add_settings_page'));
    }

    public function add_settings_page() {
        add_options_page(
            'WP Magic Link Auth Settings',
            'WP Magic Link Auth',
            'manage_options',
            'wp-magic-link-auth-settings',
            array($this, 'render_settings_page') 
        );
    }

    public function render_settings_page() {
        // Include the settings page template
        require_once plugin_dir_path(__FILE__) . 'settings-page.php'; 
    }
}