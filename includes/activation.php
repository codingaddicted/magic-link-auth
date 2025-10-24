<?php

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

global $wpdb;
define('MAGIC_LINK_AUTH_REQUESTS_TABLE', $wpdb->prefix . 'magic_link_auth_requests');

function create_auth_requests_table() {
    global $wpdb;

    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE " . MAGIC_LINK_AUTH_REQUESTS_TABLE . " (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        user_id bigint(20) NOT NULL,
        token varchar(100) NOT NULL,
        created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
        usage_count int(11) DEFAULT 0 NOT NULL,
        PRIMARY KEY (id),
        UNIQUE KEY token (token)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}

// Function to migrate existing tables by adding the usage_count column
function magic_link_auth_maybe_upgrade_db() {
    global $wpdb;
    
    // Check if the table exists
    $table_name = MAGIC_LINK_AUTH_REQUESTS_TABLE;
    if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
        return; // Table doesn't exist, no migration needed
    }
    
    // Check if usage_count column exists
    $column_exists = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT * FROM INFORMATION_SCHEMA.COLUMNS 
            WHERE TABLE_SCHEMA = %s 
            AND TABLE_NAME = %s 
            AND COLUMN_NAME = 'usage_count'",
            DB_NAME,
            $table_name
        )
    );
    
    // Add the column if it doesn't exist
    if(empty($column_exists)) {
        $wpdb->query("ALTER TABLE $table_name ADD COLUMN usage_count int(11) DEFAULT 0 NOT NULL AFTER created_at");
    }
}

function drop_auth_requests_table() {
    global $wpdb;
    $wpdb->query("DROP TABLE IF EXISTS " . MAGIC_LINK_AUTH_REQUESTS_TABLE);
}

function magic_link_auth_activate() {
    create_auth_requests_table();
    magic_link_auth_maybe_upgrade_db();
}

function magic_link_auth_deactivate() {
    drop_auth_requests_table();
}

?>