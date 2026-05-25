<?php

namespace App\View\Composers;

use Roots\Acorn\View\Composer;

class TaxonomyLega extends Composer
{
    protected static $views = ['taxonomy-lega'];

    public function with(): array
    {
        return [
            'classificaLega' => $this->classificaLega(),
            'tappeChiuse'    => $this->tappeChiuse(),
            'playoffBracket' => $this->playoffBracket(),
            'podio'          => $this->podio(),
        ];
    }

    private function currentTerm(): ?\WP_Term
    {
        $term = get_queried_object();
        return ($term instanceof \WP_Term) ? $term : null;
    }

    public function classificaLega(): array
    {
        $term = $this->currentTerm();
        if (!$term) {
            return [];
        }

        $tappe = get_posts([
            'post_type'      => 'tappa',
            'posts_per_page' => -1,
            'tax_query'      => [[
                'taxonomy' => 'lega',
                'field'    => 'term_id',
                'terms'    => $term->term_id,
            ]],
            'meta_query'     => [[
                'key'     => 'tappa_conclusa',
                'value'   => '1',
                'compare' => '=',
            ]],
            'fields' => 'ids',
        ]);

        if (empty($tappe)) {
            return [];
        }

        $players = [];

        foreach ($tappe as $tappaId) {
            $rows = get_field('classifica_finale', $tappaId);
            if (empty($rows)) {
                continue;
            }

            foreach ($rows as $row) {
                $nome = trim($row['nome'] ?? '');
                if ($nome === '') {
                    continue;
                }

                if (!isset($players[$nome])) {
                    $players[$nome] = [
                        'punti'   => 0,
                        'v'       => 0,
                        's'       => 0,
                        'p'       => 0,
                        'tappe'   => 0,
                    ];
                }

                $players[$nome]['punti']   += (int)   ($row['punti']    ?? 0);
                $players[$nome]['v']        += (int)   ($row['vittorie']  ?? 0);
                $players[$nome]['s']        += (int)   ($row['sconfitte'] ?? 0);
                $players[$nome]['p']        += (int)   ($row['pareggi']   ?? 0);
                $players[$nome]['tappe']    += 1;
            }
        }

        uasort($players, fn($a, $b) => $b['punti'] <=> $a['punti']);

        $rank = 0;
        $result = [];

        foreach ($players as $nome => $data) {
            $n = $data['tappe'];
            $result[] = [
                'posizione' => ++$rank,
                'nome'      => $nome,
                'punti'     => $data['punti'],
                'vsp'       => "{$data['v']}-{$data['s']}-{$data['p']}",
                'tappe'     => $n,
            ];
        }

        return $result;
    }

    public function tappeChiuse(): array
    {
        $term = $this->currentTerm();
        if (!$term) {
            return [];
        }

        $tappe = get_posts([
            'post_type'      => 'tappa',
            'posts_per_page' => -1,
            'orderby'        => 'meta_value',
            'order'          => 'DESC',
            'meta_key'       => 'data_inizio_tappa',
            'tax_query'      => [[
                'taxonomy' => 'lega',
                'field'    => 'term_id',
                'terms'    => $term->term_id,
            ]],
            'meta_query'     => [[
                'key'     => 'tappa_conclusa',
                'value'   => '1',
                'compare' => '=',
            ]],
        ]);

        if (empty($tappe)) {
            return [];
        }

        $mesi = ['Gen', 'Feb', 'Mar', 'Apr', 'Mag', 'Giu', 'Lug', 'Ago', 'Set', 'Ott', 'Nov', 'Dic'];

        return array_map(function ($post) use ($mesi) {
            $raw = get_field('data_inizio_tappa', $post->ID, false);
            $dt  = $raw ? \DateTime::createFromFormat('Y-m-d H:i:s', $raw, wp_timezone()) : null;

            $data_label = $dt
                ? ($dt->format('d') . ' ' . $mesi[(int) $dt->format('n') - 1] . ' · ' . $dt->format('H:i'))
                : null;

            $n = (int) get_field('numero_partecipanti', $post->ID);

            return [
                'titolo'      => get_the_title($post->ID),
                'data_label'  => $data_label,
                'n_giocatori' => $n ?: null,
                'permalink'   => get_permalink($post->ID),
            ];
        }, $tappe);
    }

    public function podio(): ?array
    {
        $term = $this->currentTerm();
        if (!$term) {
            return null;
        }

        $tid = 'term_' . $term->term_id;
        $p1  = trim(get_field('playoff_podio_1', $tid) ?? '');
        $p2  = trim(get_field('playoff_podio_2', $tid) ?? '');
        $p3  = trim(get_field('playoff_podio_3', $tid) ?? '');

        if ($p1 === '' && $p2 === '' && $p3 === '') {
            return null;
        }

        return ['primo' => $p1, 'secondo' => $p2, 'terzo' => $p3];
    }

    public function playoffBracket(): ?array
    {
        $term = $this->currentTerm();
        if (!$term) {
            return null;
        }

        $classifica = $this->classificaLega();
        if (count($classifica) < 8) {
            return null;
        }

        $tid = 'term_' . $term->term_id;
        $w = fn(string $key): string => trim(get_field($key, $tid) ?? '');

        // Standard top-8 seeding: 1v8, 4v5, 2v7, 3v6
        $s = $classifica; // already sorted by points desc
        $qf = [
            ['p1' => $s[0]['nome'], 'p2' => $s[7]['nome'], 'vincitore' => $w('playoff_qf1_vincitore')],
            ['p1' => $s[3]['nome'], 'p2' => $s[4]['nome'], 'vincitore' => $w('playoff_qf2_vincitore')],
            ['p1' => $s[1]['nome'], 'p2' => $s[6]['nome'], 'vincitore' => $w('playoff_qf3_vincitore')],
            ['p1' => $s[2]['nome'], 'p2' => $s[5]['nome'], 'vincitore' => $w('playoff_qf4_vincitore')],
        ];

        $sf1w = $w('playoff_sf1_vincitore');
        $sf2w = $w('playoff_sf2_vincitore');
        $finw = $w('playoff_finale_vincitore');

        $sf = [
            ['p1' => $qf[0]['vincitore'] ?: '?', 'p2' => $qf[1]['vincitore'] ?: '?', 'vincitore' => $sf1w],
            ['p1' => $qf[2]['vincitore'] ?: '?', 'p2' => $qf[3]['vincitore'] ?: '?', 'vincitore' => $sf2w],
        ];

        return [
            'quarti'     => $qf,
            'semifinali' => $sf,
            'finale'     => [
                'p1'        => $sf[0]['vincitore'] ?: '?',
                'p2'        => $sf[1]['vincitore'] ?: '?',
                'vincitore' => $finw,
            ],
            'campione' => $finw,
        ];
    }
}
