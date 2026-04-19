<?php

namespace App\View\Composers;

use Roots\Acorn\View\Composer;

class SingleTappa extends Composer
{
    protected static $views = ['single-tappa'];

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

    /**
     * True only before the event starts — after that, submissions are closed.
     */
    public function tappa_aperta(): bool
    {
        $data = get_field('data_inizio_tappa', null, false); // raw Y-m-d H:i:s from DB
        if (!$data) return false;

        $ts = strtotime($data);
        return $ts !== false && time() <= $ts;
    }
}
