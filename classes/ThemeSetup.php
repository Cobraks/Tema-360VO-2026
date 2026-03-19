<?php
// classes/ThemeSetup.php

if (!defined('ABSPATH')) {
    exit;
}

class E360VO_ThemeSetup
{
    /**
     * @var string|null Slug de la página de blog/noticias
     */
    private static $blog_slug = null;

    public function __construct()
    {
        add_action('after_setup_theme', [$this, 'theme_supports_and_menus']);
        add_action('init', [$this, 'init_blog_slug'], 5);
        add_action('init', [$this, 'setup_dynamic_rewrites'], 20);
        add_action('init', [$this, 'add_post_link_filters'], 10);

        add_action('after_switch_theme', [$this, 'create_default_home_page']);
        add_action('after_switch_theme', [$this, 'mark_rewrites_to_flush']);

        // Si cambia la página de entradas, marcamos flush diferido
        add_action('update_option_page_for_posts', [$this, 'mark_rewrites_to_flush'], 10, 0);

        add_filter('nav_menu_css_class', [$this, 'add_current_class_to_menu'], 10, 3);
        add_filter('upload_mimes', [$this, 'allow_svg_uploads']);
        add_filter('wp_nav_menu_objects', [$this, 'capitalize_menu_titles'], 10, 2);
        add_filter('category_link', [$this, 'filter_category_link'], 10, 2);
        add_filter('nav_menu_item_title', [$this, 'add_dropdown_icon_to_menu'], 10, 4);
    }

    public function init_blog_slug()
    {
        self::$blog_slug = $this->get_blog_page_slug();
    }

    private function get_blog_page_slug()
    {
        $page_for_posts = (int) get_option('page_for_posts');

        if ($page_for_posts > 0) {
            $page = get_post($page_for_posts);
            if ($page && !empty($page->post_name)) {
                return sanitize_title($page->post_name);
            }
        }

        return 'noticias';
    }

    public static function get_blog_slug()
    {
        if (self::$blog_slug === null) {
            $page_for_posts = (int) get_option('page_for_posts');

            if ($page_for_posts > 0) {
                $page = get_post($page_for_posts);
                if ($page && !empty($page->post_name)) {
                    self::$blog_slug = sanitize_title($page->post_name);
                } else {
                    self::$blog_slug = 'noticias';
                }
            } else {
                self::$blog_slug = 'noticias';
            }
        }

        return self::$blog_slug;
    }

    public function mark_rewrites_to_flush()
    {
        set_transient('th360_flush_rewrite_rules', 1, DAY_IN_SECONDS);
    }

    public function setup_dynamic_rewrites()
    {
        $blog_slug = self::get_blog_slug();

        if (!$blog_slug) {
            return;
        }

        // Blog home
        add_rewrite_rule(
            '^' . preg_quote($blog_slug, '/') . '/?$',
            'index.php?pagename=' . $blog_slug,
            'top'
        );

        // Blog home paginado
        add_rewrite_rule(
            '^' . preg_quote($blog_slug, '/') . '/page/([0-9]+)/?$',
            'index.php?pagename=' . $blog_slug . '&paged=$matches[1]',
            'top'
        );

        // Categorías simples
        add_rewrite_rule(
            '^' . preg_quote($blog_slug, '/') . '/([^/]+)/?$',
            'index.php?category_name=$matches[1]',
            'top'
        );

        // Categorías paginadas
        add_rewrite_rule(
            '^' . preg_quote($blog_slug, '/') . '/([^/]+)/page/([0-9]+)/?$',
            'index.php?category_name=$matches[1]&paged=$matches[2]',
            'top'
        );

        // Posts individuales: /blog-slug/categoria/post-slug/
        add_rewrite_rule(
            '^' . preg_quote($blog_slug, '/') . '/([^/]+)/([^/]+)/?$',
            'index.php?name=$matches[2]',
            'top'
        );

        if (get_transient('th360_flush_rewrite_rules')) {
            flush_rewrite_rules(true);
            delete_transient('th360_flush_rewrite_rules');
        }
    }

    public function add_post_link_filters()
    {
        add_filter('post_link', [$this, 'filter_post_link'], 10, 2);
        add_filter('post_type_link', [$this, 'filter_post_link'], 10, 2);
    }

    public function filter_category_link($link, $category)
    {
        $blog_slug = self::get_blog_slug();

        $cat_obj = null;
        if ($category instanceof WP_Term) {
            $cat_obj = $category;
        } elseif (is_numeric($category)) {
            $cat_obj = get_term((int) $category, 'category');
        }

        if ($cat_obj instanceof WP_Term && !is_wp_error($cat_obj)) {
            return home_url("/{$blog_slug}/{$cat_obj->slug}/");
        }

        return $link;
    }

    public function filter_post_link($permalink, $post)
    {
        if (!is_object($post) || !isset($post->post_type, $post->post_status)) {
            return $permalink;
        }

        if ($post->post_type !== 'post' || $post->post_status !== 'publish') {
            return $permalink;
        }

        $blog_slug  = self::get_blog_slug();
        $categories = get_the_category($post->ID);

        if (empty($categories) || !is_array($categories)) {
            return home_url("/{$blog_slug}/sin-categoria/{$post->post_name}/");
        }

        $category = reset($categories);
        if (!$category instanceof WP_Term) {
            return $permalink;
        }

        return home_url("/{$blog_slug}/{$category->slug}/{$post->post_name}/");
    }

    public function theme_supports_and_menus()
    {
        add_theme_support('post-thumbnails');
        add_theme_support('wp-block-styles');
        add_theme_support('html5', ['search-form', 'gallery', 'caption', 'script', 'style']);
        add_theme_support('title-tag');
        add_theme_support('yoast-seo-breadcrumbs');
        add_editor_style('style.css');

        add_theme_support('custom-logo', [
            'height'      => 68,
            'width'       => 400,
            'flex-height' => true,
            'flex-width'  => true,
        ]);

        // add_image_size('custom-base', 400, 0, false);
        // add_image_size('mid-430', 430, 0, false);
        // add_image_size('mid-500', 500, 0, false);
        // add_image_size('custom-grande', 650, 0, false);
        // add_image_size('retina-base', 800, 0, false);
        // add_image_size('retina-grande', 1300, 0, false);

        register_nav_menus([
            'primary'              => __('Menú header', '360vo-theme'),
            'terminos_condiciones' => __('Privacidad / Cookies', '360vo-theme'),
            'footer'               => __('Menú footer', '360vo-theme'),
            'footer_menu_1'        => __('Footer 1', '360vo-theme'),
            'footer_menu_2'        => __('Footer 2', '360vo-theme'),
            'footer_menu_3'        => __('Footer 3', '360vo-theme'),
        ]);
    }

    public function create_default_home_page()
    {
        if ('page' === get_option('show_on_front')) {
            return;
        }

        $home = get_page_by_path('home');

        if ($home) {
            update_post_meta($home->ID, '_wp_page_template', 'front-page.php');
            update_option('page_on_front', $home->ID);
            update_option('show_on_front', 'page');
            return;
        }

        $id = wp_insert_post([
            'post_title'    => 'Home',
            'post_name'     => 'home',
            'post_status'   => 'publish',
            'post_type'     => 'page',
            'page_template' => 'front-page.php',
        ]);

        if (!is_wp_error($id)) {
            update_option('page_on_front', $id);
            update_option('show_on_front', 'page');
        }
    }

    public function add_current_class_to_menu($classes, $item, $args)
    {
        $theme_location = (is_object($args) && isset($args->theme_location)) ? $args->theme_location : '';

        if (!in_array($theme_location, ['primary', 'terminos_condiciones'], true)) {
            return $classes;
        }

        if (in_array('current-menu-item', $classes, true) || in_array('current_page_item', $classes, true)) {
            $classes[] = 'current-menu-item';
        }

        if (
            (is_tax(['marca', 'carroceria', 'modelo']) || is_singular('coche'))
            && in_array('menu-item-object-coche', $classes, true)
        ) {
            $classes[] = 'current-menu-item';
        }

        return $classes;
    }

    public function allow_svg_uploads($mimes)
    {
        $mimes['svg'] = 'image/svg+xml';
        return $mimes;
    }

    public function capitalize_menu_titles($items, $args)
    {
        $theme_location = (is_object($args) && isset($args->theme_location)) ? $args->theme_location : '';

        if (in_array($theme_location, ['primary', 'terminos_condiciones'], true)) {
            foreach ($items as $item) {
                $item->title = ucfirst($item->title);
            }
        }

        return $items;
    }

    public function add_dropdown_icon_to_menu($title, $item, $args, $depth)
    {
        $theme_location = (is_object($args) && isset($args->theme_location)) ? $args->theme_location : '';
        $item_classes   = isset($item->classes) && is_array($item->classes) ? $item->classes : array();

        if ($theme_location === 'primary' && in_array('menu-item-has-children', $item_classes, true)) {
            $icon_html = E360VO_Icon::get('chevron_down', ['class' => 'menu-dropdown-icon']);
            return $title . $icon_html;
        }

        return $title;
    }
}
