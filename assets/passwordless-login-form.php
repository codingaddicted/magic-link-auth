<?php
// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

function passwordless_login_form($returnUrl, $nonce, $sessionId) {
    ob_start();
    ?>
    <div class="wp-magic-link-auth-container">
        <form id="wp-magic-link-auth-form" method="post">
            <label for="email">Email</label>
            <input type="email" name="email" id="email" required>
            <input type="hidden" name="returnUrl" value="<?php echo esc_url($returnUrl); ?>" />
            <input type="hidden" name="security" value="<?php echo $nonce; ?>" />
            <input type="hidden" name="sessionId" value="<?php echo $sessionId; ?>" />
            <button type="submit">Conferma</button>
        </form>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            new MagicLinkAuth('wp-magic-link-auth-form', {
                ajaxUrl: '<?php echo admin_url('admin-ajax.php'); ?>' 
            });
        });
    </script>
    <?php
    return ob_get_clean();
}
?>