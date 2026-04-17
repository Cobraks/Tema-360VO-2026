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

    $styles = [
        'th360-editor-theme-style' => [
            'min'  => '/style.min.css',
            'src'  => '/style.css',
            'deps' => [],
        ],
        'th360-editor-pages-style' => [
            'min'  => '/public/assets/css/pages.min.css',
            'src'  => '/public/assets/css/pages.css',
            'deps' => [ 'th360-editor-theme-style' ],
        ],
        'th360-editor-blog-style' => [
            'min'  => '/public/assets/css/blog.min.css',
            'src'  => '/public/assets/css/blog.css',
            'deps' => [ 'th360-editor-theme-style', 'th360-editor-pages-style' ],
        ],
    ];

    foreach ( $styles as $handle => $config ) {
        $relative = file_exists( get_template_directory() . $config['min'] ) ? $config['min'] : $config['src'];
        $path     = get_template_directory() . $relative;
        $uri      = get_template_directory_uri() . $relative;

        wp_enqueue_style(
            $handle,
            $uri,
            $config['deps'],
            file_exists( $path ) ? (string) filemtime( $path ) : null
        );
    }
}
add_action( 'enqueue_block_assets', 'th360_enqueue_post_editor_theme_styles', 20 );

