<?php

add_action('init', function () {

    register_post_type('tappa', [
    'label' => 'Tappe',
    'public' => true,
    'has_archive' => true,
    'rewrite' => ['slug' => 'tappa'],
    'show_in_rest' => true,
    'supports' => ['title'],
]);

    register_post_type('decklist', [
      'label' => 'Decklists',
      'public' => false,
      'show_ui' => true,
      'supports' => ['title'],
    ]);

});