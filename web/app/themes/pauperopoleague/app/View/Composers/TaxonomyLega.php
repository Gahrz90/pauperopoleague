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

            $n = count(get_field('mazzi', $post->ID) ?: []);

            return [
                'titolo'      => get_the_title($post->ID),
                'data_label'  => $data_label,
                'n_giocatori' => $n ?: null,
                'permalink'   => get_permalink($post->ID),
            ];
        }, $tappe);
    }
}
