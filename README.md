# WP Magic Link Auth

## Description

WP Magic Link Auth is a WordPress plugin that enables passwordless login via email with enhanced security. It uses session-based single-use tokens to prevent replay attacks and brute-force attempts. 

## Features

- Passwordless login using email.
- Single-use security tokens to prevent unauthorized access.
- Rate limiting to protect against brute-force attacks.
- Customizable redirect URL after successful login.
- Easy integration with custom login pages using a shortcode.

## Installation

1. Upload the `wp-magic-link-auth` folder to your `/wp-content/plugins/` directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.

## Usage

1. **Create a custom login page** in WordPress.
2. **Add the shortcode `[wp_magic_link_auth]`** to your login page. 
3. **(Optional) Customize the redirect URL** by adding the `returnurl` attribute to the shortcode:

```php
// using default return url
[wp_magic_link_auth]

// using custom return url
[wp_magic_link_auth returnUrl="/members-area/"]
```

[wp_magic_link_auth returnurl="/members-area/"]

**How it works:**

1. When a user enters its email address in the login form and submits it, the plugin generates a unique, single-use token and sends it to the user's email address in a magic link.
2. When the user clicks the magic link, the plugin verifies the token and automatically logs them in. 
3. The user is then redirected to the specified `returnurl` (or the default site home if not provided).

## Security

- This plugin uses session-based single-use tokens, which are generated randomly and invalidated immediately after a single use.
- The plugin also implements basic rate limiting to prevent brute-force attacks.

## Contributing

Contributions are welcome! Feel free to open issues or pull requests on the GitHub repository.

## License

This plugin is licensed under the GPLv3 license.
