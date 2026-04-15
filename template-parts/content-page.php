<?php
/* Header oscuro content */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

$activar_toc = get_field('tabla_de_contenidos_activar_desactivar_tabla');
?>
<article id="post-<?php the_ID(); ?>" <?php post_class(); ?> <?php echo $activar_toc ? 'data-toc-enabled="1"' : ''; ?>>

    <?php if ($activar_toc) : ?>
        <?php th360_render_table_of_contents([
            'content_id' => 'toc-content-' . get_the_ID(),
        ]); ?>
    <?php endif; ?>

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
