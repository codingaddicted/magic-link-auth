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
    
    // Token reusability settings
    update_option('wp_magic_link_auth_max_usage_count', intval($_POST['wp_magic_link_auth_max_usage_count']));
    update_option('wp_magic_link_auth_validity_duration', intval($_POST['wp_magic_link_auth_validity_duration']));
    update_option('wp_magic_link_auth_validity_unit', sanitize_text_field($_POST['wp_magic_link_auth_validity_unit'])); 
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

// Token reusability settings
$maxUsageCount = get_option('wp_magic_link_auth_max_usage_count', 1);
$validityDuration = get_option('wp_magic_link_auth_validity_duration', 5);
$validityUnit = get_option('wp_magic_link_auth_validity_unit', 'minutes');

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
        <h2>Token Reusability Settings</h2>
        <table class="form-table">
            <tbody>
                <tr>
                    <th scope="row"><label for="wp_magic_link_auth_max_usage_count">Maximum Usage Count:</label></th>
                    <td>
                        <input type="number" name="wp_magic_link_auth_max_usage_count" id="wp_magic_link_auth_max_usage_count" value="<?php echo esc_attr($maxUsageCount); ?>" min="1" max="100" class="small-text">
                        <p class="description">
                            Maximum number of times a magic link can be used. Set to 1 for single-use links (default). 
                            Higher values help with email scanners that click links before the user.
                        </p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="wp_magic_link_auth_validity_duration">Validity Duration:</label></th>
                    <td>
                        <input type="number" name="wp_magic_link_auth_validity_duration" id="wp_magic_link_auth_validity_duration" value="<?php echo esc_attr($validityDuration); ?>" min="1" class="small-text">
                        <select name="wp_magic_link_auth_validity_unit" id="wp_magic_link_auth_validity_unit">
                            <option value="minutes" <?php selected($validityUnit, 'minutes'); ?>>Minutes</option>
                            <option value="hours" <?php selected($validityUnit, 'hours'); ?>>Hours</option>
                            <option value="days" <?php selected($validityUnit, 'days'); ?>>Days</option>
                        </select>
                        <p class="description">
                            How long the magic link remains valid. After this period, the link expires regardless of usage count.
                            Default is 5 minutes.
                        </p>
                    </td>
                </tr>
            </tbody>
        </table>
        <p class="description">
            <strong>Examples:</strong><br>
            • Single-use, 5 minutes: Set usage count to 1 and duration to 5 minutes (default behavior)<br>
            • Allow 3 uses within 10 minutes: Set usage count to 3 and duration to 10 minutes<br>
            • Unlimited uses for 1 hour: Set usage count to 100 and duration to 1 hour<br>
            • Single-use, valid for 1 day: Set usage count to 1 and duration to 1 day
        </p>
        <?php submit_button('Save Settings', 'primary', 'wp_magic_link_auth_submit'); ?>
    </form>
</div>
<?php