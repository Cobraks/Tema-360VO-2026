<?php
// classes/Optimizaciones.php

if (! defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

class E360VO_Optimizaciones
{
    public function __construct()
    {
        // Limpieza de cabeceras y assets
        add_action('init', [$this, 'optimize_head']);

        // Eliminar versión de WP
        add_filter('the_generator', [$this, 'remove_wp_version']);

        // Filtrar assets de FacetWP
        add_filter('facetwp_assets', [$this, 'filter_facetwp_assets']);

        // NUEVAS:
        add_action('init',            [$this, 'disable_xmlrpc']);
        add_action('wp_head',         [$this, 'remove_rest_api_links'], 0);
        add_action('wp_head',         [$this, 'remove_adjacent_posts_links'], 1);

        // Gutenberg / bloques
        add_action('wp_enqueue_scripts', [$this, 'deregister_block_library_css'], 100);

        /**
         * CAMBIO (OPTIMIZACIÓN COCHES):
         * Quitamos assets del addon Drag&Drop de CF7 (CSS + JS) SOLO en contexto coche.
         * (Al quitar el handle del script, desaparecen también sus inline asociados.)
         */
        add_action('wp_enqueue_scripts', [$this, 'dequeue_cf7_dnd_assets_on_coche'], 999);

        /**
         * CAMBIO (OPTIMIZACIÓN COCHES):
         * Quitamos assets del plugin "Widget Google Reviews" SOLO en contexto coche:
         * - single coche
         * - archive coche
         * - tax marca / modelo / carroceria
         *
         * Objetivo: evitar carga de:
         * <script id="grw-public-main-js-js" ... public-main.js ...>
         */
        add_action('wp_enqueue_scripts', [$this, 'dequeue_google_reviews_assets_on_coche'], 999);

        // wp-embed
        add_action('wp_footer',       [$this, 'deregister_wp_embed'], 100);
    }

    /**
     * Quita scripts, estilos y enlaces innecesarios del head
     */
    public function optimize_head()
    {
        // Eliminar emojis
        remove_action('wp_head', 'print_emoji_detection_script', 7);
        remove_action('admin_print_scripts', 'print_emoji_detection_script');
        remove_action('wp_print_styles', 'print_emoji_styles');
        remove_action('admin_print_styles', 'print_emoji_styles');

        // Eliminar feeds RSS
        remove_action('wp_head', 'feed_links_extra', 3);
        remove_action('wp_head', 'feed_links', 2);

        // Eliminar wlwmanifest y RSD
        remove_action('wp_head', 'wlwmanifest_link');
        remove_action('wp_head', 'rsd_link');

        // Eliminar shortlink
        remove_action('wp_head', 'wp_shortlink_wp_head');

        // Eliminar oEmbed discovery links y host JS
        remove_action('wp_head', 'wp_oembed_add_discovery_links');
        remove_action('wp_head', 'wp_oembed_add_host_js');

        // Filtrar estilos globales (global-styles / classic-theme-styles)
        add_filter('print_styles_array', [$this, 'filter_print_styles_array']);
    }

    /**
     * Determina si estamos en una vista "coche" (sin bloques).
     *
     * NOTA: Aquí centralizamos la condición para no repetir lógica en varios sitios.
     */
    private function is_coche_context()
    {
        return (
            is_singular('coche')
            || is_post_type_archive('coche')
            || is_tax(['marca', 'carroceria', 'modelo'])
        );
    }

    /**
     * Callback para filtrar el array de estilos
     */
    public function filter_print_styles_array($styles)
    {
        /**
         * CAMBIO (ORGANIZACIÓN DE ESTILOS):
         * - Quitamos 'global-styles' y 'classic-theme-styles' en contexto coche:
         *   - single coche
         *   - archive coche
         *   - tax marca / modelo / carroceria
         */
        if ($this->is_coche_context()) {
            // Quitar global-styles
            $key = array_search('global-styles', $styles, true);
            if (false !== $key) {
                unset($styles[$key]);
            }

            // Quitar classic-theme-styles
            $key = array_search('classic-theme-styles', $styles, true);
            if (false !== $key) {
                unset($styles[$key]);
            }
        }

        return $styles;
    }

    /**
     * CAMBIO (OPTIMIZACIÓN COCHES):
     * Dequeue/deregister del addon Drag&Drop de CF7 SOLO en contexto coche.
     * Esto elimina:
     * - <link id="dnd-upload-cf7-css" ...>
     * - <script id="codedropz-uploader-js" ...>
     * - y sus inline asociados (codedropz-uploader-js-extra / codedropz-uploader-js-after)
     */
    public function dequeue_cf7_dnd_assets_on_coche()
    {
        if (! $this->is_coche_context()) {
            return;
        }

        // CSS
        wp_dequeue_style('dnd-upload-cf7');
        wp_deregister_style('dnd-upload-cf7');

        // JS
        wp_dequeue_script('codedropz-uploader');
        wp_deregister_script('codedropz-uploader');
    }

    /**
     * CAMBIO (OPTIMIZACIÓN COCHES):
     * Dequeue/deregister del plugin "Widget Google Reviews" SOLO en contexto coche.
     *
     * Nota:
     * El handle típico (según tu HTML) es: grw-public-main-js-js
     * WordPress genera el ID del tag como "{$handle}-js", por eso ves "grw-public-main-js-js".
     *
     * Para asegurar compatibilidad, hacemos:
     * - dequeue + deregister por handle conocido
     * - fallback: buscamos scripts encolados cuyo src contenga "/widget-google-reviews/" o "public-main.js"
     */
    public function dequeue_google_reviews_assets_on_coche()
    {
        if (! $this->is_coche_context()) {
            return;
        }

        // 1) Handle conocido (el id del tag era grw-public-main-js-js => handle grw-public-main-js)
        wp_dequeue_script('grw-public-main-js');
        wp_deregister_script('grw-public-main-js');

        // (por si el plugin usa otro handle parecido en tu instalación)
        wp_dequeue_script('grw-public-main-js-js');
        wp_deregister_script('grw-public-main-js-js');

        // 2) Fallback robusto: eliminar cualquier script del plugin de reseñas
        global $wp_scripts;

        if (! isset($wp_scripts) || empty($wp_scripts->queue)) {
            return;
        }

        foreach ((array) $wp_scripts->queue as $handle) {
            $registered = $wp_scripts->registered[$handle] ?? null;
            if (! $registered || empty($registered->src)) {
                continue;
            }

            $src = (string) $registered->src;

            // Match por ruta del plugin o por nombre de archivo
            if (strpos($src, '/widget-google-reviews/') !== false || strpos($src, 'public-main.js') !== false) {
                wp_dequeue_script($handle);
                wp_deregister_script($handle);
            }
        }
    }

    /**
     * Elimina la versión de WordPress
     */
    public function remove_wp_version()
    {
        return '';
    }

    /**
     * Elimina el CSS frontal de FacetWP
     */
    public function filter_facetwp_assets($assets)
    {
        if (isset($assets['front.css'])) {
            unset($assets['front.css']);
        }
        return $assets;
    }

    /* NUEVAS: */

    /**
     * Desactiva XML-RPC completamente
     */
    public function disable_xmlrpc()
    {
        add_filter('xmlrpc_enabled', '__return_false');
    }

    /**
     * Elimina los enlaces del REST API del head
     */
    public function remove_rest_api_links()
    {
        remove_action('wp_head', 'rest_output_link_wp_head', 10);
        remove_action('template_redirect', 'rest_output_link_header', 11);
    }

    /**
     * Elimina los enlaces a posts adyacentes
     */
    public function remove_adjacent_posts_links()
    {
        remove_action('wp_head', 'adjacent_posts_rel_link_wp_head', 10);
    }

    /**
     * Desregistrar la hoja de estilos de Gutenberg (bloques)
     */
    public function deregister_block_library_css()
    {
        /**
         * NOTA:
         * Esto lo mantienes globalmente. Si en algún momento dependes de estilos
         * de bloques en páginas, deberías condicionar este dequeue.
         */
        wp_dequeue_style('wp-block-library');
        wp_dequeue_style('wp-block-library-theme');
    }

    /**
     * Desregistrar el script wp-embed.js
     */
    public function deregister_wp_embed()
    {
        wp_deregister_script('wp-embed');
    }
}
