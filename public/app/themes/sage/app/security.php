<?php

declare(strict_types=1);

/**
 * Disables XML-RPC and Application Passwords, and stops the login and lost password
 * forms revealing which usernames/emails exist.
 */

/**
 * XML-RPC - xmlrpc_enabled only covers authenticated methods, so the method list is
 * emptied too to kill pingbacks. xmlrpc.php is also denied in public/.htaccess.
 */
add_filter('xmlrpc_enabled', '__return_false');
add_filter('xmlrpc_methods', fn(): array => []);
add_filter('wp_headers', function (array $headers): array {
    unset($headers['X-Pingback']);

    return $headers;
});

/**
 * Application Passwords would let REST/XML-RPC clients authenticate around the hidden login
 */
add_filter('wp_is_application_passwords_available', '__return_false');

/**
 * Replace "unknown username" / "wrong password for X" with one generic login error
 */
add_filter('authenticate', function ($user) {
    $codes = ['invalid_username', 'invalid_email', 'incorrect_password'];

    if (!is_wp_error($user) || !array_intersect($user->get_error_codes(), $codes)) {
        return $user;
    }

    return new \WP_Error('invalid_credentials', __('<strong>Error:</strong> Incorrect username or password.'));
}, 100);

add_filter('shake_error_codes', fn(array $codes): array => [...$codes, 'invalid_credentials']);

/**
 * Show the same "check your email" confirmation whether or not the account exists
 */
add_action('lostpassword_post', function (\WP_Error $errors, $user_data): void {
    if ($user_data || array_diff($errors->get_error_codes(), ['invalid_email'])) {
        return;
    }

    wp_safe_redirect(add_query_arg('checkemail', 'confirm', wp_login_url()));
    exit;
}, 10, 2);
