<?php
// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

function passwordless_login_form($returnUrl, $nonce, $sessionId) {
    ob_start();

    // Get form label and button text from settings
    $formLabel = get_option('wp_magic_link_auth_form_label', 'Email:');
    $formButton = get_option('wp_magic_link_auth_form_button', 'Login');
    ?>
    <div class="wp-magic-link-auth-container">
        <form id="wp-magic-link-auth-form" method="post">
            <label for="email"><?php echo esc_html($formLabel); ?></label> 
            <input type="email" name="email" id="email" required>
            <input type="hidden" name="returnUrl" value="<?php echo esc_url($returnUrl); ?>" />
            <input type="hidden" name="security" value="<?php echo esc_attr($nonce); ?>" />
            <input type="hidden" name="sessionId" value="<?php echo esc_attr($sessionId); ?>" />
            <button type="submit"><?php echo esc_html($formButton); ?></button> 
        </form>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            new MagicLinkAuth('wp-magic-link-auth-form', {
                ajaxUrl: '<?php echo esc_url(admin_url('admin-ajax.php')); ?>',
                enableLogging: <?php echo get_option('wp_magic_link_auth_enable_logging', 'yes') === 'yes' ? 'true' : 'false'; ?> // Pass enableLogging 
            });
        });
    </script>
    <?php
    return ob_get_clean();
}
?>