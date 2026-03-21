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
    $style = E360VO_AssetHelper::get_asset_info('/admin/assets/css/admin_styles', 'css');
    wp_enqueue_style('admin_styles', $style['url'], [], $style['version']);


}
add_action( 'admin_enqueue_scripts', 'agregar_estilos_admin' );
