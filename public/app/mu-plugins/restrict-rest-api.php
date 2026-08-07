<?php

/**
 * Plugin Name: Restrict REST API
 * Description: Requires REST API requests to be authenticated by default. Specific namespaces/routes can be allow-listed for logged-out access below, or from another (mu-)plugin/theme via the filters this exposes.
 */

namespace Bigfork\RestrictRestApi;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * REST namespaces that should remain public for logged-out requests.
 * Matches by prefix, so 'contact-form-7/v1' allows every route under that namespace.
 *
 * Add to this list here, or from another plugin/theme via:
 *
 * add_filter('bigfork/rest_api_public_namespaces', function (array $namespaces) {
 *     $namespaces[] = 'contact-form-7/v1';
 *     return $namespaces;
 * });
 */
function get_public_namespaces(): array
{
    $namespaces = [
        // 'oembed/1.0',        // allow other sites to embed this site's content
        // 'contact-form-7/v1', // allow logged-out contact form submissions
        // 'wc/store/v1',       // allow the WooCommerce Store API (headless cart etc.)
    ];

    return apply_filters('bigfork/rest_api_public_namespaces', $namespaces);
}

/**
 * Decides whether a given request should be allowed without authentication.
 *
 * For anything more granular than a namespace prefix (eg. only one route, or
 * only certain HTTP methods within a namespace), hook the request-level filter
 * instead:
 *
 * add_filter('bigfork/rest_api_is_public_request', function (bool $is_public, \WP_REST_Request $request) {
 *     if ($request->get_route() === '/my-plugin/v1/webhook' && $request->get_method() === 'POST') {
 *         return true;
 *     }
 *     return $is_public;
 * }, 10, 2);
 */
function is_public_request(\WP_REST_Request $request): bool
{
    $route = ltrim($request->get_route(), '/');

    foreach (get_public_namespaces() as $namespace) {
        if (str_starts_with($route, ltrim($namespace, '/'))) {
            return true;
        }
    }

    return apply_filters('bigfork/rest_api_is_public_request', false, $request);
}

add_filter('rest_pre_dispatch', function ($result, \WP_REST_Server $server, \WP_REST_Request $request) {
    // Respect any earlier error (eg. bad Application Password credentials)
    if (is_wp_error($result)) {
        return $result;
    }

    if (is_user_logged_in() || is_public_request($request)) {
        return $result;
    }

    return new \WP_Error(
        'rest_not_logged_in',
        __('You must be logged in to access the REST API.'),
        ['status' => rest_authorization_required_code()]
    );
}, 10, 3);
