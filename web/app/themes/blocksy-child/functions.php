<?php
// Evita accesso diretto
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Enqueue script JS e CSS
 */
function lega_enqueue_scripts() {

    // JS autocomplete carte Magic (vanilla JS)
    wp_enqueue_script(
        'decklist-autocomplete',
        get_stylesheet_directory_uri() . '/js/decklist-autocomplete.js',
        [],
        null,
        true
    );

    // CSS minimo autocomplete
    wp_enqueue_style(
        'decklist-autocomplete-css',
        get_stylesheet_directory_uri() . '/css/decklist-autocomplete.css',
        [],
        null
    );
}
add_action('wp_enqueue_scripts', 'lega_enqueue_scripts');


/**
 * Rewrite per pagina “virtuale” inserisci deck
 */
function lega_add_rewrite_rule() {
    add_rewrite_rule(
        '^inserisci-deck/?$',            // URL virtuale
        'index.php?pagename=inserisci-deck', // punta al template
        'top'
    );
}
add_action('init', 'lega_add_rewrite_rule');


/**
 * Permette a WordPress di trovare il template corretto
 */
function lega_insert_deck_template( $template ) {
    if ( get_query_var('pagename') === 'inserisci-deck' ) {
        $new_template = get_stylesheet_directory() . '/page-inserisci-deck.php';
        if ( file_exists( $new_template ) ) {
            return $new_template;
        }
    }
    return $template;
}
add_filter('template_include', 'lega_insert_deck_template');


/**
 * Attiva query_var pagename
 */
function lega_add_query_vars( $vars ){
    $vars[] = 'pagename';
    return $vars;
}
add_filter( 'query_vars', 'lega_add_query_vars' );

function crea_tassonomia_tappe() {

    $labels = array(
        'name'              => 'Tipologie Tappa',
        'singular_name'     => 'Tipologia Tappa',
        'search_items'      => 'Cerca Tipologia',
        'all_items'         => 'Tutte le Tipologie',
        'edit_item'         => 'Modifica Tipologia',
        'update_item'       => 'Aggiorna Tipologia',
        'add_new_item'      => 'Aggiungi Tipologia',
        'new_item_name'     => 'Nuova Tipologia',
        'menu_name'         => 'Tipologie',
    );

    register_taxonomy(
        'lega', // slug tassonomia
        array('tappa'), // associata al CPT tappe
        array(
            'hierarchical' => true, // come categorie (false = tag)
            'labels' => $labels,
            'show_ui' => true,
            'show_admin_column' => true,
            'query_var' => true,
            'rewrite' => array('slug' => 'tipologia-tappa'),
        )
    );
}

add_action('init', 'crea_tassonomia_tappe');