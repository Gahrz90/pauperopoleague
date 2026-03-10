<?php
/**
 * Plugin Name: Lega Pauper
 */

if (!defined('ABSPATH')) {
    exit;
}

require_once __DIR__ . '/post-types.php';
require_once __DIR__ . '/meta-boxes.php';

/*
|--------------------------------------------------------------------------
| Filtro admin per decklist → filtra per tappa
|--------------------------------------------------------------------------
*/

add_action('restrict_manage_posts', function ($post_type) {

    if ($post_type !== 'decklist') return;

    $tappe = get_posts([
        'post_type' => 'tappa',
        'numberposts' => -1
    ]);

    $selected = $_GET['tappa_filter'] ?? '';

    echo '<select name="tappa_filter">';
    echo '<option value="">Filtra per Tappa</option>';

    foreach ($tappe as $tappa) {

        echo '<option value="' . $tappa->ID . '" ' . selected($selected, $tappa->ID, false) . '>';
        echo esc_html($tappa->post_title);
        echo '</option>';
    }

    echo '</select>';
});

/*
|--------------------------------------------------------------------------
| Applica filtro alla query admin
|--------------------------------------------------------------------------
*/

add_filter('pre_get_posts', function ($query) {

    if (!is_admin()) return;

    if ($query->get('post_type') !== 'decklist') return;

    if (!empty($_GET['tappa_filter'])) {

        $query->set('meta_query', [
            [
                'key' => 'tappa_id',
                'value' => intval($_GET['tappa_filter'])
            ]
        ]);
    }
});