# Magic Link Auth

## Description

Magic Link Auth is a WordPress plugin that enables passwordless login via email with enhanced security. It uses session-based single-use tokens to prevent replay attacks and brute-force attempts. 

## Features

- Passwordless login using email.
- Single-use security tokens to prevent unauthorized access.
- Rate limiting to protect against brute-force attacks.
- Customizable redirect URL after successful login.
- Easy integration with custom login pages using a shortcode.
- JavaScript events for custom success and error handling.
- Extensibility through WordPress filters.
- Handles `HEAD` requests to prevent token invalidation by email link scanners.
- Configurable token reusability to handle email scanners and security tools that pre-click links.
- Flexible expiration settings (minutes, hours, or days).

## Installation

1. Upload the `wp-magic-link-auth` folder to your `/wp-content/plugins/` directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.

## Configuration

The plugin provides an admin settings page under **Settings > WP Magic Link Auth** where you can configure various options:

### Email Settings

- **Email Subject:** Customize the subject line of the magic link email.
- **Email Message:** Customize the email body. You can use the following placeholders:
  - `{magic_link}` - The magic link URL
  - `{user_email}` - The user's email address
  - `{user_login}` - The user's login username
  - `{display_name}` - The user's display name

### Token Reusability Settings

Configure how magic links can be reused to handle email scanners and other automated tools that may click links before the user:

- **Maximum Usage Count:** Set the maximum number of times a magic link can be used (default: 1). 
  - Set to `1` for traditional single-use links
  - Set to higher values (e.g., `3-5`) to handle email scanners that follow links before the user
  - Maximum allowed: `100`

- **Validity Duration:** Set how long the magic link remains valid before expiring (default: 5 minutes).
  - Choose the time unit: Minutes, Hours, or Days
  - After this period, the link expires regardless of usage count

**Configuration Examples:**

- **Single-use, 5 minutes** (default): Usage count = `1`, Duration = `5 minutes` - Traditional behavior, link deleted after first use
- **Allow 3 uses within 10 minutes**: Usage count = `3`, Duration = `10 minutes` - Ideal for handling email scanners
- **Unlimited uses for 1 hour**: Usage count = `100`, Duration = `1 hour` - Link can be reused freely within the time window
- **Single-use, valid for 1 day**: Usage count = `1`, Duration = `1 day` - One-time use but user has 24 hours to click it
- **5 uses in 30 minutes**: Usage count = `5`, Duration = `30 minutes` - Multiple attempts allowed within time limit

The plugin tracks usage count internally and automatically deletes tokens when they reach the maximum usage count or expire.

### Logging Settings

- **Enable Console Logging for AJAX Calls:** Enable/disable JavaScript console logging for debugging purposes.

### Form Settings

- **Email Label:** Customize the label for the email input field (default: "Email:")
- **Button Text:** Customize the submit button text (default: "Login")

## Usage

1. **Create a custom login page** in WordPress.
2. **Add the shortcode `[wp_magic_link_auth]`** to your login page. 
3. **(Optional) Customize the redirect URL** by adding the `return-url` attribute to the shortcode:

```php
// using default return url
[wp_magic_link_auth]

// using custom return url
[wp_magic_link_auth return-url="/members-area/"]
```

4. **(Optional) Override the return URL dynamically** by appending a `returnUrl` query string parameter to the page URL. This will take precedence over the `return-url` attribute in the shortcode (must be relative).

```php
// Example: Override return URL via query string
https://example.com/login-page/?returnUrl=/custom-redirect/
```

**How it works:**

1. When a user enters its email address in the login form and submits it, the plugin generates a unique token and sends it to the user's email address in a magic link.
2. When the user clicks the magic link, the plugin verifies the token and automatically logs them in. 
3. The user is then redirected to the specified `return-url` (or the `returnUrl` query string parameter if provided).
4. The plugin handles `HEAD` requests (commonly sent by email link scanners) to prevent token invalidation. These requests are ignored, and the token remains valid.
5. Token reusability is managed according to your configuration settings:
   - The token expires after the configured validity duration
   - The token is deleted after reaching the maximum usage count
   - Each use increments the usage counter

### Event Message Handling

The plugin's JavaScript code sends custom events using `window.postMessage` for success and error scenarios:

- **`wpMagicLinkAuthSuccess`:** Sent when the magic link is successfully sent.
- **`wpMagicLinkAuthError`:** Sent when an error occurs during the magic link sending process.

**Example:**

```javascript
window.addEventListener('message', (event) => {
 if (event.origin !== window.location.origin) {
     return; // Ignore messages from other origins
 }

 if (event.data.type === 'wpMagicLinkAuthSuccess') {
     // Handle success (e.g., display a success message)
     console.log("Success!", event.data.data); 
 } else if (event.data.type === 'wpMagicLinkAuthError') {
     // Handle error (e.g., display an error message)
     console.error("Error!", event.data.data);
 }
});
```

## Form Styling
The plugin outputs the following (visible) HTML structure for the login form:
```html
<div class="wp-magic-link-auth-container"> 
    <form id="wp-magic-link-auth-form" method="post">
        <label for="email">Email:</label>
        <input type="email" name="email" id="email" required>
        <button type="submit">Login</button>
    </form>
</div>
```
You can use the `wp-magic-link-auth-container` class to apply custom CSS styles to the form.

## Extensibility

The plugin provides a filter `wp_magic_link_auth_pre_login_check` to allow other plugins to perform additional checks before logging in the user. 

### Example Usage:

```php
add_filter('wp_magic_link_auth_pre_login_check', function($user) {
    // Perform custom validation
    if ($user->user_email === 'blocked@example.com') {
        return new WP_Error('blocked_user', 'This user is blocked.');
    }

    // Return an array with a state and optional message
    return ['state' => false, 'message' => 'Custom validation failed.'];
});
```

### Behavior:

1. If the filter returns a `WP_Error`, the user will be redirected to the return URL with an error message based on the `WP_Error`'s message.
2. If the filter returns an array with `state` set to `false`, the user will be redirected to the return URL with the provided `message` as the error.
3. If the filter returns an array with `state` set to `true`, the login process will proceed as normal.

## Security

- This plugin uses session-based tokens, which are generated randomly using cryptographically secure functions.
- Tokens can be configured for single-use or limited reusability to balance security with usability.
- Token usage is tracked and enforced, with automatic deletion after reaching the maximum usage count.
- Time-based expiration ensures tokens cannot be used indefinitely.
- The plugin implements basic rate limiting to prevent brute-force attacks.
- `HEAD` requests are handled to prevent email link scanners from invalidating tokens.
- All tokens are stored securely in the WordPress database with proper sanitization.

## Contributing

Contributions are welcome! Feel free to open issues or pull requests on the GitHub repository.

## License

This plugin is licensed under the GPLv3 license.
