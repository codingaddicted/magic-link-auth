<?php
/*
Plugin Name: WP Magic Link Auth
Plugin URI: https://github.com/codingaddicted/wp-magic-link-auth
Description: A secure and user-friendly WordPress plugin for passwordless authentication using magic links.
Version: 0.2.1
Author: Daniel Maran
Author URI: https://www.linkedin.com/in/danielmaran
*/


// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

// Include the admin class.
require_once plugin_dir_path(__FILE__) . 'admin/class-wp-magic-link-auth-admin.php';

// Initialize the admin class.
if (is_admin()) {
    $wp_magic_link_auth_admin = new WP_Magic_Link_Auth_Admin();
    $wp_magic_link_auth_admin->init(); 
}

// Include the form template file from the assets folder.
require_once plugin_dir_path(__FILE__) . 'assets/passwordless-login-form.php';

// Enqueue the form stylesheet and JavaScript (front-end only).
function wp_magic_link_auth_enqueue_scripts() {
    if (!is_admin()) { 
        // Pass settings to JavaScript
        wp_localize_script( 'wp-magic-link-auth-script', 'wpMagicLinkAuthSettings', array(
            'enableLogging' => get_option('wp_magic_link_auth_enable_logging', 'yes') === 'yes'
        ));

        wp_enqueue_script(
            'wp-magic-link-auth-script',
            plugin_dir_url(__FILE__) . 'assets/wp-magic-link-auth.js',
            array(), 
            '1.0',
            true 
        );
    }
}
add_action('wp_enqueue_scripts', 'wp_magic_link_auth_enqueue_scripts');

// Shortcode for the magic link login form.
function wp_magic_link_auth_form_shortcode($atts = []) {
    // Set default returnUrl.
    $atts = shortcode_atts(
        array(
            'return-url' => home_url('/'), 
        ), 
        $atts, 
        'wp_magic_link_auth' 
    );

    // Start a session if one doesn't exist
    if (session_id() === '') {
        session_start();
    }

    // Generate a unique, single-use token
    $singleUseToken = bin2hex(random_bytes(32));

    // Store the token in the session
    $_SESSION['magic_link_single_use_token'] = $singleUseToken;

    // Override the return URL if it's set in the querystring
    if (isset($_GET['returnUrl'])) {
        $atts['return-url'] = esc_url_raw($_GET['returnUrl']);
    }  

    // Pass the token and session ID to the form template.
    return passwordless_login_form($atts['return-url'], $singleUseToken, session_id()); 
}
add_shortcode('wp_magic_link_auth', 'wp_magic_link_auth_form_shortcode');

// AJAX handler for sending the magic link.
function send_magic_link() {
    // Resume the session using the provided session ID
    if (isset($_POST['sessionId']) && session_id() === '') {
        session_id($_POST['sessionId']);
        session_start();
    }

    // Verify the token from the session
    if (!isset($_SESSION['magic_link_single_use_token']) || 
        $_POST['security'] !== $_SESSION['magic_link_single_use_token']) {
        wp_send_json_error(['message' => 'Invalid security token.']);
    }

    // Invalidate the token by removing it from the session
    unset($_SESSION['magic_link_single_use_token']);

    // Get email and return URL from POST data 
    $email = sanitize_email($_POST['email']);
    if (!is_email($email)) {
        wp_send_json_error(['message' => 'Invalid email address.']);
    }

    $returnUrl = isset($_POST['returnUrl']) ? esc_url_raw($_POST['returnUrl']) : home_url('/');

    // Convert relative URL to absolute URL
    $returnUrl = wp_make_link_relative($returnUrl);

    $user = get_user_by('email', $email);
    if (!$user) {
        wp_send_json_error(['message' => 'User not found.']);
    }

    // Generate a unique token.
    $token = bin2hex(random_bytes(32));

    // Calculate expiration time (5 minutes from now).
    $expiration = time() + (5 * 60);

    // Store the token and expiration time in user meta data.
    update_user_meta($user->ID, 'passwordless_login_token', $token);
    update_user_meta($user->ID, 'passwordless_login_token_expiration', $expiration);

    // Send the magic link email.
    $magic_link = add_query_arg('token', $token, home_url('/authenticate-passwordless-login/'));
    $magic_link = add_query_arg('returnUrl', $returnUrl, $magic_link);

    // Get custom email settings
    $subject = get_option('wp_magic_link_auth_email_subject', 'Your Magic Link');
    $messageTemplate = get_option(
        'wp_magic_link_auth_email_message',
        'Click this link to login: {magic_link}'
    );

    // Replace placeholders in email message
    $placeholders = array(
        '{magic_link}' => $magic_link,
        '{user_email}' => $user->user_email,
        '{user_login}' => $user->user_login,
        '{display_name}' => $user->display_name,
    );
    $message = strtr($messageTemplate, $placeholders); // Use strtr for better replacement

    wp_mail($email, $subject, $message);

    wp_send_json_success(['message' => 'Check your email for a magic link!']);
}
add_action('wp_ajax_send_magic_link', 'send_magic_link');
add_action('wp_ajax_nopriv_send_magic_link', 'send_magic_link');

// Handle the authentication process.
function authenticate_passwordless_login() {
    if (isset($_GET['token'])) {
        // Prevent email link scanners from invalidating the token
        if ($_SERVER['REQUEST_METHOD'] === 'HEAD') {
            status_header(200);
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $token = sanitize_text_field($_GET['token']);
            $returnUrl = isset($_GET['returnUrl']) ? esc_url_raw($_GET['returnUrl']) : home_url('/');
    
            // Get the user by token.
            $users = get_users(array('meta_key' => 'passwordless_login_token', 'meta_value' => $token));
    
            if (!empty($users)) {
                $user = $users[0];
    
                // Check if the token is expired.
                $expiration = get_user_meta($user->ID, 'passwordless_login_token_expiration', true);
                if (time() > $expiration) {
                    wp_redirect($returnUrl . '?token_expired=1'); // Redirect with error
                    exit;
                }
    
                // Delete the token to prevent reuse.
                delete_user_meta($user->ID, 'passwordless_login_token');
                delete_user_meta($user->ID, 'passwordless_login_token_expiration');
    
                // Log the user in.
                wp_set_auth_cookie($user->ID, true);
                wp_redirect($returnUrl); // Redirect to the intended page after login.
                exit;
            } else {
                // Token not found or invalid
                wp_redirect($returnUrl . '?token_invalid=1'); // Redirect with error
                exit;
            }
        }
    }
}
add_action('template_redirect', 'authenticate_passwordless_login');
?>
