<?php

/**
 * Theme filters.
 */

namespace App;

/**
 * Add "… Continued" to the excerpt.
 *
 * @return string
 */
add_filter('excerpt_more', function () {
    return sprintf(' &hellip; <a href="%s">%s</a>', get_permalink(), __('Continued', 'sage'));
});

// Rewrite rule: /lega/{lega-slug}/{tappa-slug}
add_action('init', function () {
    add_rewrite_rule(
        '^lega/([^/]+)/([^/]+)/?$',
        'index.php?post_type=tappa&name=$matches[2]',
        'top'
    );
});

// Build tappa permalink as /lega/{lega-slug}/{tappa-slug}
add_filter('post_type_link', function (string $url, \WP_Post $post): string {
    if ($post->post_type !== 'tappa') {
        return $url;
    }

    $terms = get_the_terms($post->ID, 'lega');
    if (empty($terms) || is_wp_error($terms)) {
        return $url;
    }

    return home_url("/lega/{$terms[0]->slug}/{$post->post_name}/");
}, 10, 2);
