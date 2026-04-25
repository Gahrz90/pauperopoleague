<?php

namespace App\View\Composers;

use Roots\Acorn\View\Composer;

class FrontPage extends Composer
{
    protected static $views = ['front-page'];

    public function with(): array
    {
        return [
            'legheCount'    => $this->legheCount(),
            'prossimaTappa' => $this->prossimaTappa(),
            'tappeRecenti'  => $this->tappeRecenti(),
        ];
    }

    public function legheCount(): int
    {
        $terms = get_terms([
            'taxonomy'   => 'lega',
            'hide_empty' => true,
        ]);

        if (is_wp_error($terms)) {
            return 0;
        }

        return \count($terms);
    }

    public function prossimaTappa(): ?array
    {
        // Latest lega = highest term_id (most recently created)
        $leghe = get_terms([
            'taxonomy'   => 'lega',
            'hide_empty' => true,
            'orderby'    => 'term_id',
            'order'      => 'DESC',
            'number'     => 1,
        ]);

        if (is_wp_error($leghe) || empty($leghe)) {
            return null;
        }

        $lega = $leghe[0];
        $now  = current_time('Y-m-d H:i:s');

        $tappe = get_posts([
            'post_type'      => 'tappa',
            'posts_per_page' => 1,
            'orderby'        => 'meta_value',
            'order'          => 'ASC',
            'meta_key'       => 'data_inizio_tappa',
            'tax_query'      => [[
                'taxonomy' => 'lega',
                'field'    => 'term_id',
                'terms'    => $lega->term_id,
            ]],
            'meta_query'     => [[
                'key'     => 'data_inizio_tappa',
                'value'   => $now,
                'compare' => '>=',
                'type'    => 'DATETIME',
            ]],
        ]);

        if (empty($tappe)) {
            return null;
        }

        $post    = $tappe[0];
        $raw     = get_field('data_inizio_tappa', $post->ID, false);

        if (!$raw) {
            return null;
        }

        $dt = \DateTime::createFromFormat('Y-m-d H:i:s', $raw, wp_timezone());

        if (!$dt) {
            return null;
        }

        $giorni = ['Lun', 'Mar', 'Mer', 'Gio', 'Ven', 'Sab', 'Dom'];
        $mesi   = ['Gen', 'Feb', 'Mar', 'Apr', 'Mag', 'Giu', 'Lug', 'Ago', 'Set', 'Ott', 'Nov', 'Dic'];

        $label = \sprintf(
            '%s %d %s · %s',
            $giorni[(int) $dt->format('N') - 1],
            (int) $dt->format('j'),
            $mesi[(int) $dt->format('n') - 1],
            $dt->format('H:i')
        );

        return [
            'id'        => $post->ID,
            'titolo'    => get_the_title($post->ID),
            'data_iso'  => $dt->format('c'),
            'data_label'=> $label,
            'permalink' => get_permalink($post->ID),
            'lega_name' => $lega->name,
        ];
    }

    public function tappeRecenti(): array
    {
        $tappe = get_posts([
            'post_type'      => 'tappa',
            'posts_per_page' => 3,
            'orderby'        => 'meta_value',
            'order'          => 'DESC',
            'meta_key'       => 'data_inizio_tappa',
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
                ? ((int) $dt->format('j') . ' ' . $mesi[(int) $dt->format('n') - 1])
                : null;

            $mazzi = get_field('mazzi', $post->ID) ?: [];

            return [
                'titolo'      => get_the_title($post->ID),
                'data_label'  => $data_label,
                'n_giocatori' => \count($mazzi),
                'permalink'   => get_permalink($post->ID),
            ];
        }, $tappe);
    }
}
