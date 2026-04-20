<?php
/*
Template Name: CTA FORMULARIO
template: page-form.php
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
        // Si existe un título personalizado, lo mostramos. Si no, mostramos el título de la página.
        if (get_field('titulo_h1')) {
            $title = get_field('titulo_h1');
        } else {
            $title = get_the_title();
        }

        // Mostramos el título
        echo '<h1 class="entry-title">' . $title . '</h1>';

        // Si existe un párrafo de introducción, lo mostramos.
        if (get_field('parrafo_introduccion')) {
            echo '<p class="intro-paragraph">' . get_field('parrafo_introduccion') . '</p>';
        }



        // Obtén la URL y el título de la página actual
        /* $url = urlencode(get_permalink());
            $title = urlencode(get_the_title()); */

        ?>




        <?php
        // Obtener el tiempo de lectura para el post actual
        echo obtener_tiempo_lectura(get_the_ID());

        ?>

    </div>
    <?php th360_render_page_cta(get_queried_object_id()); ?>

</header>








<?php
// Si existe una imagen destacada, la mostramos
if (has_post_thumbnail()) {
    echo '<div class="entry-image">';

    $srcset = wp_get_attachment_image_srcset(get_post_thumbnail_id(), 'full');
    $sizes = '(max-width: 640px) 640px, (max-width: 768px) 768px, (max-width: 1024px) 1024px, 3000px';
    $img_url = get_the_post_thumbnail_url();
    $alt = get_the_title();

    echo '<img src="' . $img_url . '" srcset="' . $srcset . '" sizes="' . $sizes . '" alt="' . $alt . '">';

    echo '</div>';
}




// Verificar si la opción cabecera_hero_pantalla_completa está seleccionada 
if (get_field('cabecera_hero_pantalla_completa')) {
    echo '<div class="button_scroll_home button_scroll--page">
        <button id="scrollButtonPage">
            <span class="material-symbols-outlined">
                <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="white">
                    <path d="M440-800v487L216-537l-56 57 320 320 320-320-56-57-224 224v-487h-80Z"></path>
                </svg></span>
        </button>

    </div>';
}

echo '</div>' //Entry header;
?>

<div class="content-wrapper custom-page">

    <main class="custom-page__content">
        <?php
        while (have_posts()) : the_post();
            get_template_part('template-parts/content', 'page');
        endwhile;
        ?>
    </main>


    <aside class="custom-page__related">
        <!-- Los enlaces relacionados irán aquí -->
    </aside>
</div>

<?php get_footer(); ?>
