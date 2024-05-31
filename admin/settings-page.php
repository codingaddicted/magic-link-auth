<?php
// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

if (!current_user_can('manage_options')) {
    return;
}

// Save settings
if (isset($_POST['wp_magic_link_auth_submit'])) {
    check_admin_referer('wp_magic_link_auth_settings_nonce');
    update_option('wp_magic_link_auth_email_subject', sanitize_text_field($_POST['wp_magic_link_auth_email_subject']));
    update_option('wp_magic_link_auth_email_message', wp_kses_post($_POST['wp_magic_link_auth_email_message']));
    update_option('wp_magic_link_auth_enable_logging', isset($_POST['wp_magic_link_auth_enable_logging']) ? 'yes' : 'no');
    update_option('wp_magic_link_auth_form_label', sanitize_text_field($_POST['wp_magic_link_auth_form_label']));
    update_option('wp_magic_link_auth_form_button', sanitize_text_field($_POST['wp_magic_link_auth_form_button'])); 
}

// Get current settings
$emailSubject = get_option('wp_magic_link_auth_email_subject', 'Your Magic Link');
$emailMessage = get_option(
    'wp_magic_link_auth_email_message',
    'Click this link to login: {magic_link}'
);
$enableLogging = get_option('wp_magic_link_auth_enable_logging', 'yes');
$formLabel = get_option('wp_magic_link_auth_form_label', 'Email:');
$formButton = get_option('wp_magic_link_auth_form_button', 'Login');

?>
<div class="wrap">
    <h1>WP Magic Link Auth Settings</h1>
    <form method="post" action="">
        <?php wp_nonce_field('wp_magic_link_auth_settings_nonce'); ?>
        <h2>Email Settings</h2>
        <table class="form-table">
            <tbody>
                <tr>
                    <th scope="row"><label for="wp_magic_link_auth_email_subject">Email Subject:</label></th>
                    <td><input type="text" name="wp_magic_link_auth_email_subject" id="wp_magic_link_auth_email_subject" value="<?php echo esc_attr($emailSubject); ?>" class="regular-text"></td>
                </tr>
                <tr>
                    <th scope="row"><label for="wp_magic_link_auth_email_message">Email Message:</label></th>
                    <td>
                        <textarea name="wp_magic_link_auth_email_message" id="wp_magic_link_auth_email_message" rows="5" cols="50" class="large-text code"><?php echo esc_textarea($emailMessage); ?></textarea>
                        <p class="description">
                            You can use the following placeholders:
                            <code>{magic_link}</code>, 
                            <code>{user_email}</code>, 
                            <code>{user_login}</code>, 
                            <code>{display_name}</code> 
                        </p>
                    </td>
                </tr>
            </tbody>
        </table>
        <h2>Logging Settings</h2>
        <table class="form-table">
            <tbody>
                <tr>
                    <th scope="row">Enable Console Logging for AJAX Calls:</th>
                    <td>
                        <input type="checkbox" name="wp_magic_link_auth_enable_logging" id="wp_magic_link_auth_enable_logging" value="yes" <?php checked($enableLogging, 'yes'); ?>>
                    </td>
                </tr>
            </tbody>
        </table>
        <h2>Form Settings</h2>
        <table class="form-table">
            <tbody>
                <tr>
                    <th scope="row"><label for="wp_magic_link_auth_form_label">Email Label:</label></th>
                    <td>
                        <input type="text" name="wp_magic_link_auth_form_label" id="wp_magic_link_auth_form_label" value="<?php echo esc_attr($formLabel); ?>" class="regular-text">
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="wp_magic_link_auth_form_button">Button Text:</label></th>
                    <td>
                        <input type="text" name="wp_magic_link_auth_form_button" id="wp_magic_link_auth_form_button" value="<?php echo esc_attr($formButton); ?>" class="regular-text">
                    </td>
                </tr>
            </tbody>
        </table>
        <?php submit_button('Save Settings', 'primary', 'wp_magic_link_auth_submit'); ?>
    </form>
</div>
<?php