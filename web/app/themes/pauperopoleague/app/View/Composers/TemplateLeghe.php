<?php

namespace App\View\Composers;

use Roots\Acorn\View\Composer;

class TemplateLeghe extends Composer
{
    protected static $views = ['template-leghe'];

    public function leghe(): array
    {
        $terms = get_terms([
            'taxonomy'   => 'lega',
            'hide_empty' => false,
            'orderby'    => 'name',
            'order'      => 'ASC',
        ]);

        if (is_wp_error($terms) || empty($terms)) {
            return [];
        }

        return array_map(function ($term) {
            $tappe = get_posts([
                'post_type'      => 'tappa',
                'posts_per_page' => -1,
                'orderby'        => 'date',
                'order'          => 'DESC',
                'tax_query'      => [[
                    'taxonomy' => 'lega',
                    'field'    => 'term_id',
                    'terms'    => $term->term_id,
                ]],
            ]);

            return [
                'term'  => $term,
                'tappe' => $tappe,
            ];
        }, $terms);
    }
}
