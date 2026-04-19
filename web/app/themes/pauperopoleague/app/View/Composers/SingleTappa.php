<?php

namespace App\View\Composers;

use Roots\Acorn\View\Composer;

class SingleTappa extends Composer
{
    protected static $views = ['single-tappa'];

    public function with(): array
    {
        return [
            'tappa_id'        => $this->tappa_id(),
            'titolo'          => $this->titolo(),
            'data_inizio_tappa' => $this->data_inizio_tappa(),
            'data_inizio_iso' => $this->data_inizio_iso(),
            'tappa_aperta'    => $this->tappa_aperta(),
            'tappa_conclusa'  => $this->tappa_conclusa(),
            'mazzi_top8'      => $this->mazzi_top8(),
            'archetype_stats' => $this->archetype_stats(),
            'card_stats'      => $this->card_stats(),
        ];
    }

    public function tappa_id(): int
    {
        return get_the_ID();
    }

    public function titolo(): string
    {
        return get_the_title();
    }

    public function data_inizio_tappa(): ?string
    {
        $data = get_field('data_inizio_tappa');
        if (!$data) return null;

        $dt = \DateTime::createFromFormat('Y-m-d H:i:s', $data, wp_timezone());
        return $dt ? $dt->format('d/m/Y \a\l\l\e H:i') : null;
    }

    public function data_inizio_iso(): ?string
    {
        $data = get_field('data_inizio_tappa');
        if (!$data) return null;

        $dt = \DateTime::createFromFormat('Y-m-d H:i:s', $data, wp_timezone());
        return $dt ? $dt->format('c') : null;
    }

    public function tappa_aperta(): bool
    {
        $data = get_field('data_inizio_tappa', null, false);
        if (!$data) return false;

        $ts = strtotime($data);
        return $ts !== false && time() <= $ts;
    }

    public function tappa_conclusa(): bool
    {
        $val = get_field('tappa_conclusa', get_the_ID());
        return $val === true || $val === 1 || $val === '1';
    }

    public function mazzi_top8(): array
    {
        $mazzi = get_field('mazzi');
        if (empty($mazzi)) return [];
        return array_values(array_filter($mazzi, fn($m) => !empty($m['top_8'])));
    }

    public function archetype_stats(): array
    {
        $mazzi = get_field('mazzi');
        if (empty($mazzi)) return [];

        $stats = [];
        foreach ($mazzi as $m) {
            $arch = trim($m['archetipo'] ?? '') ?: 'Sconosciuto';
            $stats[$arch] = ($stats[$arch] ?? 0) + 1;
        }
        arsort($stats);
        return $stats;
    }

    public function card_stats(): array
    {
        $mazzi = get_field('mazzi');
        if (empty($mazzi)) return [];

        $total = count($mazzi);
        $counts = [];
        foreach ($mazzi as $m) {
            $in_side = false;
            foreach (preg_split('/\r?\n/', $m['mazzo'] ?? '') as $line) {
                $line = trim($line);
                if (stripos($line, 'Sideboard') !== false) { $in_side = true; continue; }
                if ($in_side || $line === '') continue;
                if (preg_match('/^(\d+)\s+(.+)$/', $line, $match)) {
                    $card = trim($match[2]);
                    $counts[$card] = ($counts[$card] ?? 0) + (int) $match[1];
                }
            }
        }

        $averages = [];
        foreach ($counts as $card => $sum) {
            $averages[$card] = round($sum / $total, 2);
        }

        arsort($averages);
        return \array_slice($averages, 0, 10, true);
    }

    public static function parseMazzoLines(string $mazzo): array
    {
        $result = [];
        foreach (preg_split('/\r?\n/', trim($mazzo)) as $line) {
            $line = trim($line);
            if ($line === '') {
                $result[] = ['type' => 'blank'];
            } elseif (preg_match('/^(\d+)\s+(.+)$/', $line, $m)) {
                $result[] = ['type' => 'card', 'qty' => (int) $m[1], 'name' => trim($m[2])];
            } else {
                $result[] = ['type' => 'section', 'text' => $line];
            }
        }
        return $result;
    }
}
