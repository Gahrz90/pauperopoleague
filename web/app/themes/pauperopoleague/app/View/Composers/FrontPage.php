<?php

namespace App\View\Composers;

use Roots\Acorn\View\Composer;

class FrontPage extends Composer
{
    protected static $views = ['front-page'];

    public function with(): array
    {
        return [
            'tappeCount'         => $this->tappeCount(),
            'giocatoriCount'     => $this->giocatoriCount(),
            'giocatoriMedi'      => $this->giocatoriMediATappa(),
            'prossimaTappa'      => $this->prossimaTappa(),
            'tappeRecenti'       => $this->tappeRecenti(),
            'classificaLega'     => $this->classificaLega(),
            'legaUrl'            => $this->legaUrl(),
        ];
    }

    public function giocatoriCount(): int
    {
        return (int) count_users()['total_users'];
    }

    public function giocatoriMediATappa(): int
    {
        $tappe = get_posts([
            'post_type'      => 'tappa',
            'posts_per_page' => -1,
            'meta_query'     => [[
                'key'     => 'tappa_conclusa',
                'value'   => '1',
                'compare' => '=',
            ]],
        ]);

        if (empty($tappe)) {
            return 0;
        }

        $totale = array_sum(array_map(function ($post) {
            $mazzi = get_field('mazzi', $post->ID) ?: [];
            return \count($mazzi);
        }, $tappe));

        return (int) round($totale / \count($tappe));
    }

    public function tappeCount(): int
    {
        return (int) wp_count_posts('tappa')->publish;
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
                ? ((int) $dt->format('j') . ' ' . $mesi[(int) $dt->format('n') - 1] . ' · ' . $dt->format('H:i'))
                : null;

            $n = get_field('numero_partecipanti', $post->ID);

            return [
                'titolo'      => get_the_title($post->ID),
                'data_label'  => $data_label,
                'n_giocatori' => $n !== null && $n !== '' ? (int) $n : null,
                'permalink'   => get_permalink($post->ID),
            ];
        }, $tappe);
    }

    public function legaUrl(): string
    {
        $leghe = get_terms([
            'taxonomy'   => 'lega',
            'hide_empty' => true,
            'orderby'    => 'term_id',
            'order'      => 'DESC',
            'number'     => 1,
        ]);

        if (is_wp_error($leghe) || empty($leghe)) {
            return home_url('/');
        }

        $url = get_term_link($leghe[0]);

        return is_wp_error($url) ? home_url('/') : $url;
    }

    public function classificaLega(): array
    {
        $leghe = get_terms([
            'taxonomy'   => 'lega',
            'hide_empty' => true,
            'orderby'    => 'term_id',
            'order'      => 'DESC',
            'number'     => 1,
        ]);

        if (is_wp_error($leghe) || empty($leghe)) {
            return [];
        }

        $tappe = get_posts([
            'post_type'      => 'tappa',
            'posts_per_page' => -1,
            'tax_query'      => [[
                'taxonomy' => 'lega',
                'field'    => 'term_id',
                'terms'    => $leghe[0]->term_id,
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
                    $players[$nome] = ['punti' => 0, 'v' => 0, 's' => 0, 'p' => 0];
                }

                $players[$nome]['punti'] += (int) ($row['punti']    ?? 0);
                $players[$nome]['v']     += (int) ($row['vittorie']  ?? 0);
                $players[$nome]['s']     += (int) ($row['sconfitte'] ?? 0);
                $players[$nome]['p']     += (int) ($row['pareggi']   ?? 0);
            }
        }

        uasort($players, fn($a, $b) => $b['punti'] <=> $a['punti']);

        $rank = 0;
        $result = [];

        foreach (\array_slice($players, 0, 10, true) as $nome => $data) {
            $parts    = preg_split('/\s+/', trim($nome));
            $initials = mb_strtoupper(mb_substr($parts[0], 0, 1));
            if (isset($parts[1])) {
                $initials .= mb_strtoupper(mb_substr($parts[1], 0, 1));
            }

            $result[] = [
                'posizione' => ++$rank,
                'nome'      => $nome,
                'iniziali'  => $initials,
                'punti'     => $data['punti'],
                'record'    => "{$data['v']}V · {$data['s']}S · {$data['p']}P",
            ];
        }

        return $result;
    }
}
