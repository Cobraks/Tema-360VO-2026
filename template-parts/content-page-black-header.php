<?php
/* Header oscuro content */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}
?>
<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>





<?php
// Obtener el tiempo de lectura para el post actual
echo obtener_tiempo_lectura(get_the_ID());?>

    <aside id="toc-container">
        <div id="menu-placeholder">
            <button class="toc-container__toggle">
                <span class="toc-container__icon toc-container__icon--toc">
                    <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px">
                        <path d="M120-280v-80h560v80H120Zm0-160v-80h560v80H120Zm0-160v-80h560v80H120Zm680 320q-17 0-28.5-11.5T760-320q0-17 11.5-28.5T800-360q17 0 28.5 11.5T840-320q0 17-11.5 28.5T800-280Zm0-160q-17 0-28.5-11.5T760-480q0-17 11.5-28.5T800-520q17 0 28.5 11.5T840-480q0 17-11.5 28.5T800-440Zm0-160q-17 0-28.5-11.5T760-640q0-17 11.5-28.5T800-680q17 0 28.5 11.5T840-640q0 17-11.5 28.5T800-600Z" />
                    </svg>
                </span>
                <span class="toc-container__text">Mostrar tabla de contenidos</span><span class="toc-container__icon toc-container__icon--expand"><svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px">
                        <path d="M200-200v-240h80v160h160v80H200Zm480-320v-160H520v-80h240v240h-80Z" />
                    </svg></span><span class="toc-container__icon toc-container__icon--collapse">
                    <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#e8eaed">
                        <path d="m256-200-56-56 224-224-224-224 56-56 224 224 224-224 56 56-224 224 224 224-56 56-224-224-224 224Z" />
                    </svg>

                </span></button>
            <div class="toc-container__content hidden">
                <!-- La tabla de contenidos se generará aquí -->
            </div>
        </div>
    </aside>


    <div id="entry-content--start" class="entry-content">

        <?php
        // Mostramos el contenido de la página
        the_content();

        wp_link_pages(array(
            'before' => '<div class="page-links">' . esc_html__('Pages:', 'theme_text_domain'),
            'after'  => '</div>',
        ));
        ?>
    </div>
</article>