<?php

namespace App\View\Composers;

use Roots\Acorn\View\Composer;

class Header extends Composer
{
    /**
     * List of views served by this composer.
     *
     * @var string[]
     */
    protected static $views = [
        'sections.header',
    ];

    public function with(){
        return [
            'topMenuItems' => $this->menuItems('top_navigation', true),
        ];
    }

    public function menuItems($menu_name = 'top_navigation', $with_name = false): array
    {
        $menu_items = [];
        $locations = get_nav_menu_locations();
        if (isset($locations[$menu_name])) {
            $menu = wp_get_nav_menu_object($locations[$menu_name]);
            $menu_items = wp_get_nav_menu_items($menu->term_id);
        }
        if ($with_name) {
            return $menu_items ? [
                'name' => $menu->name,
                'items' => $menu_items,
            ] : [];
        }

        return $menu_items;
    }
}
