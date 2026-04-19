<?php
/**
 * Plugin Name: Paupero Decklist API
 * Description: REST endpoint per la sottomissione delle decklist alle tappe
 */

defined('ABSPATH') || exit;

add_action('rest_api_init', function () {
    register_rest_route('paupero/v1', '/verify-code', [
        'methods'             => 'POST',
        'callback'            => 'paupero_verify_code',
        'permission_callback' => '__return_true',
        'args'                => [
            'tappa_id' => [
                'required'          => true,
                'validate_callback' => fn($v) => is_numeric($v) && get_post($v) && get_post_type($v) === 'tappa',
                'sanitize_callback' => 'absint',
            ],
            'codice_tappa' => [
                'required'          => true,
                'sanitize_callback' => 'sanitize_text_field',
            ],
        ],
    ]);

    register_rest_route('paupero/v1', '/metagame', [
        'methods'             => 'GET',
        'callback'            => 'paupero_get_metagame',
        'permission_callback' => '__return_true',
    ]);

    register_rest_route('paupero/v1', '/decklist', [
        'methods'             => 'POST',
        'callback'            => 'paupero_submit_decklist',
        'permission_callback' => '__return_true', // pubblico, la validazione è nel callback
        'args'                => [
            'tappa_id' => [
                'required'          => true,
                'validate_callback' => fn($v) => is_numeric($v) && get_post($v) && get_post_type($v) === 'tappa',
                'sanitize_callback' => 'absint',
            ],
            'codice_tappa' => [
                'required'          => true,
                'sanitize_callback' => 'sanitize_text_field',
            ],
            'nome_giocatore' => [
                'required'          => true,
                'sanitize_callback' => 'sanitize_text_field',
            ],
            'archetipo' => [
                'required'          => true,
                'sanitize_callback' => 'sanitize_text_field',
            ],
            'titolo' => [
                'required'          => true,
                'sanitize_callback' => 'sanitize_text_field',
            ],
            'mazzo' => [
                'required'          => true,
                'sanitize_callback' => 'sanitize_textarea_field',
            ],
        ],
    ]);
});

function paupero_verify_code(WP_REST_Request $request): WP_REST_Response|WP_Error {
    $tappa_id     = $request->get_param('tappa_id');
    $codice_input = $request->get_param('codice_tappa');

    $codice_acf = get_field('codice_tappa', $tappa_id);
    if (empty($codice_acf) || !hash_equals((string) $codice_acf, $codice_input)) {
        return new WP_Error('codice_non_valido', 'Il codice tappa non è corretto.', ['status' => 403]);
    }

    $data_inizio = get_field('data_inizio_tappa', $tappa_id, false);
    $ts_inizio   = $data_inizio ? strtotime($data_inizio) : false;
    if ($ts_inizio === false || time() > $ts_inizio) {
        return new WP_Error('tappa_chiusa', 'Non puoi inserire la decklist.', ['status' => 403]);
    }

    return new WP_REST_Response(['success' => true], 200);
}

function paupero_get_metagame(): WP_REST_Response|WP_Error {
    $cached = get_transient('paupero_metagame');
    if ($cached !== false) {
        return new WP_REST_Response($cached, 200);
    }

    $response = wp_remote_get('https://www.mtggoldfish.com/metagame/pauper/full', [
        'timeout' => 15,
        'headers' => ['User-Agent' => 'Mozilla/5.0 (compatible; WordPress)'],
    ]);

    if (is_wp_error($response)) {
        return new WP_Error('fetch_failed', 'Impossibile recuperare il metagame.', ['status' => 502]);
    }

    $body = wp_remote_retrieve_body($response);

    preg_match_all(
        '/<a[^>]+href="\/archetype\/pauper-[^"]*"[^>]*>\s*([^<]+)\s*<\/a>/i',
        $body,
        $matches
    );

    $archetypes = array_values(array_unique(array_map('trim', $matches[1])));
    $archetypes = array_filter($archetypes, fn($n) => $n !== '');
    $archetypes = array_values($archetypes);

    if (empty($archetypes)) {
        return new WP_Error('parse_failed', 'Nessun archetipo trovato.', ['status' => 502]);
    }

    set_transient('paupero_metagame', $archetypes, DAY_IN_SECONDS);

    return new WP_REST_Response($archetypes, 200);
}

function paupero_submit_decklist(WP_REST_Request $request): WP_REST_Response|WP_Error {
    $tappa_id       = $request->get_param('tappa_id');
    $codice_input   = $request->get_param('codice_tappa');
    $nome_giocatore = $request->get_param('nome_giocatore');
    $archetipo      = $request->get_param('archetipo');
    $titolo         = $request->get_param('titolo');
    $mazzo          = $request->get_param('mazzo');

    // 1. Verifica codice tappa
    $codice_acf = get_field('codice_tappa', $tappa_id);
    if (empty($codice_acf) || !hash_equals((string) $codice_acf, $codice_input)) {
        return new WP_Error(
            'codice_non_valido',
            'Il codice tappa non è corretto.',
            ['status' => 403]
        );
    }

    // 2. Verifica finestra temporale (now <= data_inizio_tappa)
    $data_inizio = get_field('data_inizio_tappa', $tappa_id, false); // raw Y-m-d H:i:s from DB
    if (empty($data_inizio)) {
        return new WP_Error(
            'tappa_non_configurata',
            'La tappa non ha una data di inizio configurata.',
            ['status' => 500]
        );
    }

    $ts_inizio = strtotime($data_inizio);
    if ($ts_inizio === false || time() > $ts_inizio) {
        return new WP_Error(
            'tappa_chiusa',
            'Non puoi inserire la decklist.',
            ['status' => 403]
        );
    }

    // 3. Aggiunge la riga al ripetitore ACF
    $nuova_riga = [
        'nome_giocatore' => $nome_giocatore,
        'archetipo'      => $archetipo,
        'titolo'         => $titolo,
        'mazzo'          => $mazzo,
        'top_8'     => false,
    ];

    $result = add_row('mazzi', $nuova_riga, $tappa_id);

    if ($result === false) {
        return new WP_Error(
            'errore_salvataggio',
            'Errore durante il salvataggio della decklist.',
            ['status' => 500]
        );
    }

    return new WP_REST_Response([
        'success' => true,
        'message' => 'Decklist inviata con successo!',
    ], 201);
}