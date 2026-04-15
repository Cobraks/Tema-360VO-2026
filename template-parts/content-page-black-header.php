<?php
/* Header oscuro content */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}
?>
<article id="post-<?php the_ID(); ?>" <?php post_class(); ?> data-toc-enabled="1">

<?php
// Obtener el tiempo de lectura para el post actual
echo obtener_tiempo_lectura(get_the_ID()); ?>

    <?php th360_render_table_of_contents([
        'content_id' => 'toc-content-' . get_the_ID(),
    ]); ?>

    <div id="entry-content--start" class="entry-content">

        <?php
        // Mostramos el contenido de la pagina
        the_content();

        wp_link_pages(array(
            'before' => '<div class="page-links">' . esc_html__('Pages:', 'theme_text_domain'),
            'after'  => '</div>',
        ));
        ?>
    </div>
</article>
