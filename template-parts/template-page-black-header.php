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






    </div>


</header>




<div class="share">
    <button class="share__button share__button--copy" id="copy-button" onclick="copyLink()">
        <!--<i class="icon-link"></i>-->
        <span class="share__button-svg">
            <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px">
                <path d="M360-240q-33 0-56.5-23.5T280-320v-480q0-33 23.5-56.5T360-880h360q33 0 56.5 23.5T800-800v480q0 33-23.5 56.5T720-240H360Zm0-80h360v-480H360v480ZM200-80q-33 0-56.5-23.5T120-160v-560h80v560h440v80H200Zm160-240v-480 480Z" />
            </svg>
        </span>
        <span class="share__button-text">Copiar link</span>
    </button>
    <button class="share__button share__button--share" id="share-button">
        <span class="share__button-svg">
            <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#e8eaed">
                <path d="M720-80q-50 0-85-35t-35-85q0-7 1-14.5t3-13.5L322-392q-17 15-38 23.5t-44 8.5q-50 0-85-35t-35-85q0-50 35-85t85-35q23 0 44 8.5t38 23.5l282-164q-2-6-3-13.5t-1-14.5q0-50 35-85t85-35q50 0 85 35t35 85q0 50-35 85t-85 35q-23 0-44-8.5T638-672L356-508q2 6 3 13.5t1 14.5q0 7-1 14.5t-3 13.5l282 164q17-15 38-23.5t44-8.5q50 0 85 35t35 85q0 50-35 85t-85 35Zm0-640q17 0 28.5-11.5T760-760q0-17-11.5-28.5T720-800q-17 0-28.5 11.5T680-760q0 17 11.5 28.5T720-720ZM240-440q17 0 28.5-11.5T280-480q0-17-11.5-28.5T240-520q-17 0-28.5 11.5T200-480q0 17 11.5 28.5T240-440Zm480 280q17 0 28.5-11.5T760-200q0-17-11.5-28.5T720-240q-17 0-28.5 11.5T680-200q0 17 11.5 28.5T720-160Zm0-600ZM240-480Zm480 280Z" />
            </svg>
        </span>
        Compartir
    </button>
</div>



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

?>





</div>

<div class="content-wrapper custom-page custom-page--black">

    <main class="custom-page__content">
        <?php
        while (have_posts()) : the_post();
            get_template_part('template-parts/content', 'page-black-header');
        endwhile;
        ?>
    </main>


    <aside class="custom-page__related">
        <!-- Los enlaces relacionados irán aquí -->
    </aside>
</div>

<?php get_footer(); ?>