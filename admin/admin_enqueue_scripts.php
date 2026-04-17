<?php
/**
 * Funciones para añadir las dependencias al panel de administración: Iconos, js, css y colores.
 *
 * @package 360vo-theme
 */

 if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function agregar_estilos_admin() {

    wp_enqueue_style( 'admin_styles', get_template_directory_uri() . '/admin/assets/css/admin_styles.css' );


}
add_action( 'admin_enqueue_scripts', 'agregar_estilos_admin' );

