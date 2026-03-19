<?php

/**
 * Mostrar migas de pan (Breadcrumbs)
 *
 * @package 360vo-theme
 */

if (! defined('ABSPATH')) {
    exit;
}

/**
 * Context helper seguro.
 */
if (! function_exists('theme360_ctx_get')) {
    function theme360_ctx_get($key, $default = '')
    {
        if (function_exists('gv360_get_variable')) {
            return gv360_get_variable($key, $default);
        }

        if (function_exists('get_field')) {
            $map = array(
                'stock_name' => 'nombre_del_stock',
            );

            if (isset($map[$key])) {
                $value = get_field($map[$key], 'option');
                if (is_scalar($value) && trim((string) $value) !== '') {
                    return trim((string) $value);
                }
            }
        }

        return $default;
    }
}

if (! function_exists('theme360_breadcrumbs')) {
    function theme360_breadcrumbs()
    {
        if (is_front_page()) {
            return;
        }

        $stock_name   = (string) theme360_ctx_get('stock_name', __('Vehículos', '360vo-theme'));
        $archive_link = get_post_type_archive_link('coche');
        $home_url     = home_url('/');

        $posts_page_id    = (int) get_option('page_for_posts');
        $posts_page_title = $posts_page_id ? get_the_title($posts_page_id) : '';
        $posts_page_title = (is_string($posts_page_title) && $posts_page_title !== '') ? $posts_page_title : __('Blog', '360vo-theme');
        $posts_page_link  = $posts_page_id ? get_permalink($posts_page_id) : home_url('/blog/');

        $render_item_link = function ($name, $url) {
            printf(
                '<li class="flex items-center"><a href="%1$s" class="flex items-center"><span>%2$s</span></a></li>',
                esc_url((string) $url),
                esc_html((string) $name)
            );
        };

        $render_item_current = function ($name) {
            printf(
                '<li class="flex items-center"><span>%1$s</span></li>',
                esc_html((string) $name)
            );
        };

        echo '<nav class="nav-breadcrumb" aria-label="' . esc_attr__('Navegación de la página', '360vo-theme') . '">';
        echo '<ol id="breadcrumb" class="flex">';

        $render_item_link(__('Inicio', '360vo-theme'), $home_url);

        if (is_home()) {
            $render_item_current($posts_page_title);
        } elseif (is_singular('coche')) {
            if (! empty($archive_link)) {
                $render_item_link(ucfirst($stock_name), $archive_link);
            }

            $marca_terms = get_the_terms(get_the_ID(), 'marca');
            if (is_array($marca_terms) && ! empty($marca_terms)) {
                $marca = reset($marca_terms);
                if ($marca instanceof WP_Term) {
                    $marca_link = get_term_link($marca);
                    if (! is_wp_error($marca_link)) {
                        $render_item_link($marca->name, $marca_link);
                    }
                }
            }

            $modelo_terms = get_the_terms(get_the_ID(), 'modelo');
            if (is_array($modelo_terms) && ! empty($modelo_terms)) {
                $modelo = reset($modelo_terms);
                if ($modelo instanceof WP_Term) {
                    $modelo_link = get_term_link($modelo);
                    if (! is_wp_error($modelo_link)) {
                        $render_item_link($modelo->name, $modelo_link);
                    }
                }
            }

            $version = function_exists('get_field') ? get_field('datos_generales_version', get_the_ID()) : '';
            $version = is_scalar($version) ? trim((string) $version) : '';
            $render_item_current($version !== '' ? $version : get_the_title());
        } elseif (is_single() && 'post' === get_post_type()) {
            $render_item_link($posts_page_title, $posts_page_link);

            $cats = get_the_category();
            if (is_array($cats) && ! empty($cats)) {
                $cat = reset($cats);
                if ($cat instanceof WP_Term) {
                    $cat_link = get_category_link($cat->term_id);
                    if (! is_wp_error($cat_link)) {
                        $render_item_link($cat->name, $cat_link);
                    }
                }
            }

            $render_item_current(get_the_title());
        } elseif (is_page()) {
            $ancestors = get_post_ancestors(get_the_ID());
            if (! empty($ancestors)) {
                $ancestors = array_reverse($ancestors);
                foreach ($ancestors as $parent_id) {
                    $render_item_link(get_the_title($parent_id), get_permalink($parent_id));
                }
            }

            $render_item_current(get_the_title());
        } elseif (is_category()) {
            $render_item_link($posts_page_title, $posts_page_link);
            $render_item_current(single_cat_title('', false));
        } elseif (is_tag()) {
            $render_item_link($posts_page_title, $posts_page_link);
            $render_item_current(single_tag_title('', false));
        } elseif (is_tax(array('marca', 'carroceria'))) {
            if (! empty($archive_link)) {
                $render_item_link(ucfirst($stock_name), $archive_link);
            }
            $render_item_current(single_term_title('', false));
        } elseif (is_tax('modelo')) {
            $term = get_queried_object();

            if (! empty($archive_link)) {
                $render_item_link(ucfirst($stock_name), $archive_link);
            }

            if ($term instanceof WP_Term && function_exists('get_field')) {
                $marca_parent = get_field('marca_en_modelo', $term);
                if ($marca_parent) {
                    $marca = get_term((int) $marca_parent);
                    if ($marca instanceof WP_Term && ! is_wp_error($marca)) {
                        $marca_link = get_term_link($marca);
                        if (! is_wp_error($marca_link)) {
                            $render_item_link($marca->name, $marca_link);
                        }
                    }
                }
            }

            $render_item_current(single_term_title('', false));
        } elseif (is_post_type_archive('coche')) {
            $render_item_current(ucfirst($stock_name));
        } elseif (is_archive()) {
            $render_item_current(get_the_archive_title());
        } else {
            $title = get_the_title();
            $render_item_current($title !== '' ? $title : __('Página', '360vo-theme'));
        }

        echo '</ol>';

        echo '<div class="btn-container btn-container--right">';
        echo '<button class="breadcrumb-btn right-btn" aria-label="' . esc_attr__('Desplazarse a la derecha', '360vo-theme') . '">' . E360VO_Icon::get('foward_arrow') . '</button>';
        echo '</div>';

        echo '<div class="btn-container btn-container--left">';
        echo '<button class="breadcrumb-btn left-btn" aria-label="' . esc_attr__('Desplazarse a la izquierda', '360vo-theme') . '">' . E360VO_Icon::get('back_arrow') . '</button>';
        echo '</div>';

        echo '</nav>';
    }
}
