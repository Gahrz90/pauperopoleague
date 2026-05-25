<?php
/**
 * ACF fields for playoff bracket results on `lega` taxonomy terms.
 * Admin enters only the vincitore for each match; players are auto-seeded
 * from the leaderboard by the TaxonomyLega composer.
 */

add_action('acf/init', function () {
    if (!function_exists('acf_add_local_field_group')) {
        return;
    }

    acf_add_local_field_group([
        'key'      => 'group_playoff_lega',
        'title'    => 'Playoff Top 8 — Risultati',
        'location' => [[
            ['param' => 'taxonomy', 'operator' => '==', 'value' => 'lega'],
        ]],
        'menu_order' => 10,
        'fields' => [

            // ── Quarti di finale ──────────────────────────────────────────
            [
                'key'          => 'field_qf1_vincitore',
                'name'         => 'playoff_qf1_vincitore',
                'label'        => 'QF1 Vincitore (Seed 1 vs Seed 8)',
                'type'         => 'text',
                'instructions' => 'Lascia vuoto se non ancora disputata',
            ],
            [
                'key'   => 'field_qf2_vincitore',
                'name'  => 'playoff_qf2_vincitore',
                'label' => 'QF2 Vincitore (Seed 4 vs Seed 5)',
                'type'  => 'text',
            ],
            [
                'key'   => 'field_qf3_vincitore',
                'name'  => 'playoff_qf3_vincitore',
                'label' => 'QF3 Vincitore (Seed 2 vs Seed 7)',
                'type'  => 'text',
            ],
            [
                'key'   => 'field_qf4_vincitore',
                'name'  => 'playoff_qf4_vincitore',
                'label' => 'QF4 Vincitore (Seed 3 vs Seed 6)',
                'type'  => 'text',
            ],

            // ── Semifinali ────────────────────────────────────────────────
            [
                'key'   => 'field_sf1_vincitore',
                'name'  => 'playoff_sf1_vincitore',
                'label' => 'SF1 Vincitore (QF1W vs QF2W)',
                'type'  => 'text',
            ],
            [
                'key'   => 'field_sf2_vincitore',
                'name'  => 'playoff_sf2_vincitore',
                'label' => 'SF2 Vincitore (QF3W vs QF4W)',
                'type'  => 'text',
            ],

            // ── Finale ────────────────────────────────────────────────────
            [
                'key'   => 'field_finale_vincitore',
                'name'  => 'playoff_finale_vincitore',
                'label' => 'Finale Vincitore (SF1W vs SF2W)',
                'type'  => 'text',
            ],

            // ── Podio ─────────────────────────────────────────────────────
            [
                'key'          => 'field_podio_1',
                'name'         => 'playoff_podio_1',
                'label'        => 'Podio — Primo Classificato',
                'type'         => 'text',
                'instructions' => 'Nome del vincitore del torneo',
            ],
            [
                'key'   => 'field_podio_2',
                'name'  => 'playoff_podio_2',
                'label' => 'Podio — Secondo Classificato',
                'type'  => 'text',
            ],
            [
                'key'   => 'field_podio_3',
                'name'  => 'playoff_podio_3',
                'label' => 'Podio — Terzo Classificato',
                'type'  => 'text',
            ],
        ],
    ]);
});
