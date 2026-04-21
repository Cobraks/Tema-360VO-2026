<?php
/*
Template Name: Header oscuro
 *
 * @package 360vo-theme
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

get_header(); ?>
<?php
$clases_entry_container = generar_clases_entry_container();
$estilo_fondo = obtener_estilo_fondo();
echo '<div class="' . esc_attr($clases_entry_container) . '" ' . $estilo_fondo . '>';
?>

<header class="entry-header">
    <div class="entry-text">
        <?php
        if (get_field('titulo_h1')) {
            $title = get_field('titulo_h1');
        } else {
            $title = get_the_title();
        }

        echo '<h1 class="entry-title">' . $title . '</h1>';

        if (get_field('parrafo_introduccion')) {
            echo '<p class="intro-paragraph">' . get_field('parrafo_introduccion') . '</p>';
        }
        ?>
    </div>
</header>

<?php th360_render_page_cta(get_queried_object_id()); ?>

<?php
if (has_post_thumbnail()) {
    echo '<div class="entry-image">';

    $srcset = wp_get_attachment_image_srcset(get_post_thumbnail_id(), 'full');
    $sizes = '(max-width: 640px) 640px, (max-width: 768px) 768px, (max-width: 1024px) 1024px, 3000px';
    $img_url = get_the_post_thumbnail_url();
    $alt = get_the_title();

    echo '<img src="' . $img_url . '" srcset="' . $srcset . '" sizes="' . $sizes . '" alt="' . $alt . '">';

    echo '</div>';
}

th360_render_page_scroll_button(get_queried_object_id());
?>

</div>

<div class="content-wrapper custom-page custom-page--black">
    <main class="custom-page__content">
        <?php
        while (have_posts()) :
            the_post();
            get_template_part('template-parts/content', 'page-black-header');
        endwhile;
        ?>
    </main>

    <aside class="custom-page__related">
        <!-- Los enlaces relacionados irán aquí -->
    </aside>
</div>

<?php get_footer(); ?>
