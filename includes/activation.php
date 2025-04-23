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
        PRIMARY KEY (id),
        UNIQUE KEY token (token)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}

function drop_auth_requests_table() {
    global $wpdb;
    $wpdb->query("DROP TABLE IF EXISTS " . MAGIC_LINK_AUTH_REQUESTS_TABLE);
}

function magic_link_auth_activate() {
    create_auth_requests_table();
}

function magic_link_auth_deactivate() {
    drop_auth_requests_table();
}

?>