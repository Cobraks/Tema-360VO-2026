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

function th360_enqueue_post_editor_theme_styles() {
    if ( ! is_admin() || ! function_exists( 'get_current_screen' ) ) {
        return;
    }

    $screen = get_current_screen();
    if ( ! $screen || $screen->base !== 'post' || $screen->post_type !== 'post' ) {
        return;
    }

    $style_path = get_template_directory() . '/style.min.css';
    $style_uri  = get_template_directory_uri() . '/style.min.css';

    if ( ! file_exists( $style_path ) ) {
        $style_path = get_template_directory() . '/style.css';
        $style_uri  = get_template_directory_uri() . '/style.css';
    }

    wp_enqueue_style(
        'th360-editor-theme-style',
        $style_uri,
        [],
        file_exists( $style_path ) ? (string) filemtime( $style_path ) : null
    );
}
add_action( 'enqueue_block_assets', 'th360_enqueue_post_editor_theme_styles', 20 );

