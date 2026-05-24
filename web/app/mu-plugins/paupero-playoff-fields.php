<?php
/**
 * Registers ACF field group for the playoff bracket on `lega` taxonomy terms.
 *
 * Fields are attached to the term edit screen so admins can fill in each round
 * (quarti, semifinali, finale) and toggle bracket visibility.
 */

add_action('acf/init', function () {
    if (!function_exists('acf_add_local_field_group')) {
        return;
    }

    acf_add_local_field_group([
        'key'      => 'group_playoff_lega',
        'title'    => 'Playoff Top 8',
        'location' => [[
            ['param' => 'taxonomy', 'operator' => '==', 'value' => 'lega'],
        ]],
        'menu_order'  => 10,
        'position'    => 'normal',
        'fields' => [

            // ── Toggle ────────────────────────────────────────────────────
            [
                'key'           => 'field_playoff_attivo',
                'name'          => 'playoff_attivo',
                'label'         => 'Mostra bracket playoff',
                'type'          => 'true_false',
                'default_value' => 0,
                'ui'            => 1,
            ],

            // ── Quarti di finale (4 partite) ─────────────────────────────
            [
                'key'        => 'field_playoff_quarti',
                'name'       => 'playoff_quarti',
                'label'      => 'Quarti di finale',
                'type'       => 'repeater',
                'min'        => 0,
                'max'        => 4,
                'layout'     => 'table',
                'button_label' => 'Aggiungi partita',
                'conditional_logic' => [[
                    ['field' => 'field_playoff_attivo', 'operator' => '==', 'value' => '1'],
                ]],
                'sub_fields' => [
                    [
                        'key'   => 'field_qf_p1',
                        'name'  => 'p1',
                        'label' => 'Giocatore 1',
                        'type'  => 'text',
                    ],
                    [
                        'key'   => 'field_qf_p2',
                        'name'  => 'p2',
                        'label' => 'Giocatore 2',
                        'type'  => 'text',
                    ],
                    [
                        'key'   => 'field_qf_vincitore',
                        'name'  => 'vincitore',
                        'label' => 'Vincitore',
                        'type'  => 'text',
                        'instructions' => 'Lascia vuoto se non ancora disputata',
                    ],
                ],
            ],

            // ── Semifinali (2 partite) ────────────────────────────────────
            [
                'key'        => 'field_playoff_semifinali',
                'name'       => 'playoff_semifinali',
                'label'      => 'Semifinali',
                'type'       => 'repeater',
                'min'        => 0,
                'max'        => 2,
                'layout'     => 'table',
                'button_label' => 'Aggiungi partita',
                'conditional_logic' => [[
                    ['field' => 'field_playoff_attivo', 'operator' => '==', 'value' => '1'],
                ]],
                'sub_fields' => [
                    [
                        'key'   => 'field_sf_p1',
                        'name'  => 'p1',
                        'label' => 'Giocatore 1',
                        'type'  => 'text',
                    ],
                    [
                        'key'   => 'field_sf_p2',
                        'name'  => 'p2',
                        'label' => 'Giocatore 2',
                        'type'  => 'text',
                    ],
                    [
                        'key'   => 'field_sf_vincitore',
                        'name'  => 'vincitore',
                        'label' => 'Vincitore',
                        'type'  => 'text',
                        'instructions' => 'Lascia vuoto se non ancora disputata',
                    ],
                ],
            ],

            // ── Finale ────────────────────────────────────────────────────
            [
                'key'   => 'field_playoff_finale_p1',
                'name'  => 'playoff_finale_p1',
                'label' => 'Finale — Giocatore 1',
                'type'  => 'text',
                'conditional_logic' => [[
                    ['field' => 'field_playoff_attivo', 'operator' => '==', 'value' => '1'],
                ]],
            ],
            [
                'key'   => 'field_playoff_finale_p2',
                'name'  => 'playoff_finale_p2',
                'label' => 'Finale — Giocatore 2',
                'type'  => 'text',
                'conditional_logic' => [[
                    ['field' => 'field_playoff_attivo', 'operator' => '==', 'value' => '1'],
                ]],
            ],
            [
                'key'          => 'field_playoff_finale_vincitore',
                'name'         => 'playoff_finale_vincitore',
                'label'        => 'Finale — Vincitore (Campione)',
                'type'         => 'text',
                'instructions' => 'Lascia vuoto se non ancora disputata',
                'conditional_logic' => [[
                    ['field' => 'field_playoff_attivo', 'operator' => '==', 'value' => '1'],
                ]],
            ],
        ],
    ]);
});
