<?php

/**
 * Sidebar del tema 360vo-theme
 *
 * @package 360vo-theme
 */
?>

<div class="sidebar__widget sidebar__widget--search">
    <h3 class="sidebar__widget-title">Buscar</h3>
    <?php get_search_form(); ?>
</div>

<div class="sidebar__widget sidebar__widget--categories">
    <h3 class="sidebar__widget-title">Categorías</h3>
    <ul class="sidebar__widget-list">
        <?php wp_list_categories(array(
            'title_li'   => '',
            'show_count' => true,
        )); ?>
    </ul>
</div>

<div class="sidebar__widget sidebar__widget--recent-posts">
    <h3 class="sidebar__widget-title">Entradas Recientes</h3>
    <ul class="sidebar__widget-list">
        <?php
        $recent_posts = wp_get_recent_posts(array(
            'numberposts' => 5,
            'post_status' => 'publish',
        ));
        foreach ($recent_posts as $post) : ?>
            <li class="sidebar__widget-item">
                <a href="<?php echo get_permalink($post['ID']); ?>">
                    <?php echo esc_html($post['post_title']); ?>
                </a>
            </li>
        <?php endforeach; ?>
    </ul>
</div>

