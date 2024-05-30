<?php
/*
Plugin Name: WP Magic Link Auth
Plugin URI: https://github.com/codingaddicted/wp-magic-link-auth
Description: A secure and user-friendly WordPress plugin for passwordless authentication using magic links.
Version: 1.0.0
Author: Daniel Maran
Author URI: https://www.linkedin.com/in/danielmaran
*/


// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

// Include the form template file from the assets folder.
require_once plugin_dir_path(__FILE__) . 'assets/passwordless-login-form.php';

// Enqueue the form stylesheet and JavaScript.
function wp_magic_link_auth_enqueue_styles() {
    wp_enqueue_script(
        'wp-magic-link-auth-script',
        plugin_dir_url(__FILE__) . 'assets/wp-magic-link-auth.js',
        array(), // Dependencies
        '1.0', // Version
        true // Load in footer
    );
}
add_action('wp_enqueue_scripts', 'wp_magic_link_auth_enqueue_styles');

// Shortcode for the magic link login form.
function wp_magic_link_auth_form_shortcode($atts = []) {
    // Set default returnUrl.
    $atts = shortcode_atts(
        array(
            'returnUrl' => home_url('/'), 
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

    // Pass the token and session ID to the form template.
    return passwordless_login_form($atts['returnUrl'], $singleUseToken, session_id()); 
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

    $email = sanitize_email($_POST['email']);
    if (!is_email($email)) {
        wp_send_json_error(['message' => 'Invalid email address.']);
    }

    $returnUrl = isset($_POST['returnUrl']) ? esc_url_raw($_POST['returnUrl']) : home_url('/');

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
    $message = sprintf('Click this link to login: %s', $magic_link);
    wp_mail($email, 'Your Magic Link', $message);

    wp_send_json_success(['message' => 'Check your email for a magic link!']);
}
add_action('wp_ajax_send_magic_link', 'send_magic_link');
add_action('wp_ajax_nopriv_send_magic_link', 'send_magic_link');

// Handle the authentication process.
function authenticate_passwordless_login() {
    if (isset($_GET['token'])) {
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
add_action('template_redirect', 'authenticate_passwordless_login');
?>