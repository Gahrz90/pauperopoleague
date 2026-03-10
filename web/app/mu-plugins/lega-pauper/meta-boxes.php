<?php

add_action('add_meta_boxes', function () {

    /*
    |--------------------------------------------------------------------------
    | TAPPA
    |--------------------------------------------------------------------------
    */

    add_meta_box(
        'tappa_settings',
        'Impostazioni Tappa',
        'render_tappa_metabox',
        'tappa'
    );

    add_meta_box(
        'tappa_decklists',
        'Decklists Associate',
        'render_tappa_decklists',
        'tappa',
        'normal'
    );

    /*
    |--------------------------------------------------------------------------
    | DECKLIST
    |--------------------------------------------------------------------------
    */

    add_meta_box(
        'decklist_settings',
        'Dati Decklist',
        'render_decklist_metabox',
        'decklist'
    );
});

/*
|--------------------------------------------------------------------------
| METABOX TAPPA
|--------------------------------------------------------------------------
*/

function render_tappa_metabox($post) {

    $start_datetime = get_post_meta($post->ID, 'start_datetime', true);
    $wizard_code = get_post_meta($post->ID, 'wizard_code', true);
    ?>

    <p>
        <label>Data e ora inizio torneo:</label><br>
        <input type="datetime-local"
               name="start_datetime"
               value="<?= esc_attr($start_datetime); ?>">
    </p>

    <p>
        <label>Codice Wizard:</label><br>
        <input type="text"
               name="wizard_code"
               value="<?= esc_attr($wizard_code); ?>">
    </p>

    <?php
}

/*
|--------------------------------------------------------------------------
| LISTA DECK ASSOCIATE ALLA TAPPA
|--------------------------------------------------------------------------
*/

function render_tappa_decklists($post) {

    $decklists = get_posts([
        'post_type' => 'decklist',
        'numberposts' => -1,
        'meta_query' => [
            [
                'key' => 'tappa_id',
                'value' => $post->ID
            ]
        ]
    ]);

    echo '<ul>';

    foreach ($decklists as $deck) {

        echo '<li>';
        echo '<a href="' . get_edit_post_link($deck->ID) . '">';
        echo esc_html($deck->post_title);
        echo '</a>';
        echo '</li>';
    }

    echo '</ul>';

    echo '<a class="button"
             href="' . admin_url('post-new.php?post_type=decklist&tappa_id=' . $post->ID) . '">
             + Aggiungi Deck
          </a>';
}

/*
|--------------------------------------------------------------------------
| METABOX DECKLIST
|--------------------------------------------------------------------------
*/

function render_decklist_metabox($post) {

    $tappa_id = get_post_meta($post->ID, 'tappa_id', true);
    $player_name = get_post_meta($post->ID, 'player_name', true);
    $archetype = get_post_meta($post->ID, 'archetype', true);
    $decklist_data = get_post_meta($post->ID, 'decklist_data', true);
    $is_top8 = get_post_meta($post->ID, 'is_top8', true);
    ?>

    <p>
        <label>ID Tappa:</label><br>
        <input type="number"
               name="tappa_id"
               value="<?= esc_attr($tappa_id); ?>">
    </p>

    <p>
        <label>Nome Giocatore:</label><br>
        <input type="text"
               name="player_name"
               value="<?= esc_attr($player_name); ?>">
    </p>

    <p>
        <label>Archetipo:</label><br>
        <select name="archetype">
            <option value="aggro" <?= selected($archetype, 'aggro'); ?>>Aggro</option>
            <option value="midrange" <?= selected($archetype, 'midrange'); ?>>Midrange</option>
            <option value="control" <?= selected($archetype, 'control'); ?>>Control</option>
        </select>
    </p>

    <p>
        <label>Decklist (JSON):</label><br>
        <textarea name="decklist_data"
                  rows="10"
                  style="width:100%;"><?= esc_textarea($decklist_data); ?></textarea>
    </p>

    <p>
        <label>
            <input type="checkbox"
                   name="is_top8"
                   value="1"
                <?= checked($is_top8, 1); ?>>
            È Top8
        </label>
    </p>

    <?php
}

/*
|--------------------------------------------------------------------------
| SAVE POST
|--------------------------------------------------------------------------
*/

add_action('save_post', function ($post_id) {

    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;

    /*
    | TAPPA
    */

    if (get_post_type($post_id) === 'tappa') {

        if (isset($_POST['start_datetime'])) {
            update_post_meta($post_id, 'start_datetime',
                sanitize_text_field($_POST['start_datetime']));
        }

        if (isset($_POST['wizard_code'])) {
            update_post_meta($post_id, 'wizard_code',
                sanitize_text_field($_POST['wizard_code']));
        }
    }

    /*
    | DECKLIST
    */

    if (get_post_type($post_id) === 'decklist') {

        update_post_meta($post_id, 'tappa_id',
            intval($_POST['tappa_id'] ?? 0));

        update_post_meta($post_id, 'player_name',
            sanitize_text_field($_POST['player_name'] ?? ''));

        update_post_meta($post_id, 'archetype',
            sanitize_text_field($_POST['archetype'] ?? ''));

        update_post_meta($post_id, 'decklist_data',
            wp_kses_post($_POST['decklist_data'] ?? ''));

        update_post_meta($post_id, 'is_top8',
            isset($_POST['is_top8']) ? 1 : 0);
    }

});