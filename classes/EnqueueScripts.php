<?php
// classes/EnqueueScripts.php

if (!defined('ABSPATH')) {
    exit;
}

class E360VO_EnqueueScripts
{
    public function __construct()
    {
        add_action('wp_enqueue_scripts', [$this, 'enqueue_scripts'], 100);

        // Preload de las fuentes crÃ­ticas que se usan above-the-fold
        add_action('wp_head', [$this, 'print_font_preloads'], 5);

        // Async preload para style.css
        add_filter('style_loader_tag', [$this, 'filter_style_loader_tag'], 10, 4);

        // ✅ Critical inline lo más pronto posible
        add_action('wp_head', [$this, 'print_inline_critical_css'], 6);
    }

    /**
     * Inline critical.min.css para eliminar request del critical path.
     * - No se encola como stylesheet.
     * - Se imprime antes de style.css.
     */
    public function print_inline_critical_css()
    {
        static $printed = false;
        if ($printed) return;
        $printed = true;

        $file = THEME_DIR . '/public/assets/css/critical.min.css';
        if (!is_readable($file)) return;

        echo "<style id='360vo-critical-inline'>\n" . file_get_contents($file) . "\n</style>\n";
    }

    public function print_font_preloads()
    {
        static $printed = false;
        if ($printed) return;
        $printed = true;

        $fonts = [
            '/public/assets/fonts/source-sans-3-latin-400-normal.woff2',
            '/public/assets/fonts/source-sans-3-latin-600-normal.woff2',
        ];

        foreach ($fonts as $font) {
            $file = THEME_DIR . $font;
            if (!is_readable($file)) {
                continue;
            }

            echo '<link rel="preload" href="' . esc_url(THEME_URI . $font) . '" as="font" type="font/woff2" crossorigin>' . "\n";
        }
    }

    public function enqueue_scripts()
    {
        // ---------------------------------------------------------
        // 1) CSS global (style.css) -> async via filter
        // ---------------------------------------------------------
        $style = E360VO_AssetHelper::get_asset_info('/style', 'css');
        wp_enqueue_style('360vo-theme-style', $style['url'], [], $style['version']);

        // ---------------------------------------------------------
        // 2) JS principal del tema
        // ---------------------------------------------------------
        $funciones = E360VO_AssetHelper::get_asset_info('/public/assets/js/funciones_tema', 'js');
        wp_enqueue_script('funciones_tema', $funciones['url'], [], $funciones['version'], true);

        // ---------------------------------------------------------
        // 3) Footer mapa lazy
        // ---------------------------------------------------------
        if ($this->should_load_footer_map_lazy()) {
            $map = E360VO_AssetHelper::get_asset_info('/public/assets/js/footer-map-lazy', 'js');
            wp_enqueue_script('360vo-footer-map-lazy', $map['url'], [], $map['version'], true);
        }

        // ---------------------------------------------------------
        // 4) Front page
        // ---------------------------------------------------------
        if (is_front_page()) {
            $fp_css = E360VO_AssetHelper::get_asset_info('/public/assets/css/front-page', 'css');
            wp_enqueue_style('360vo-front-page', $fp_css['url'], ['360vo-theme-style'], $fp_css['version']);

            $fp_js = E360VO_AssetHelper::get_asset_info('/public/assets/js/front-page', 'js');
            wp_enqueue_script('360vo-front-page', $fp_js['url'], [], $fp_js['version'], true);
        }

        // ---------------------------------------------------------
        // 5) 404
        // ---------------------------------------------------------
        if (is_404()) {
            $css_404 = E360VO_AssetHelper::get_asset_info('/public/assets/css/custom-404', 'css');
            wp_enqueue_style('360vo-404', $css_404['url'], ['360vo-theme-style'], $css_404['version']);
        }

        // ---------------------------------------------------------
        // 6) Pages/blog archives
        // ---------------------------------------------------------
        if (
            is_page()
            || is_singular('post')
            || is_home()
            || is_category()
            || is_tag()
            || is_author()
            || is_date()
            || (function_exists('th360_is_blog_brand_archive') && th360_is_blog_brand_archive())
        ) {
            $pages = E360VO_AssetHelper::get_asset_info('/public/assets/css/pages', 'css');
            wp_enqueue_style('360vo-pages', $pages['url'], ['360vo-theme-style'], $pages['version']);
        }

        // ---------------------------------------------------------
        // 7) CSS específico para usuarios logueados (logged-in.css)
        // ---------------------------------------------------------
        if (is_user_logged_in()) {
            $logged_in = E360VO_AssetHelper::get_asset_info('/public/assets/css/logged-in', 'css');
            wp_enqueue_style('360vo-logged-in', $logged_in['url'], ['360vo-theme-style'], $logged_in['version']);
        }


        // --- Blog CSS/JS (archive + single post) ---
        if ($this->is_blog_context()) {

            $blog_css = E360VO_AssetHelper::get_asset_info('/public/assets/css/blog', 'css');
            wp_enqueue_style(
                '360vo-blog',
                $blog_css['url'],
                ['360vo-theme-style', '360vo-pages'], // pages.css debe cargar antes para que blog pueda modularlo
                $blog_css['version']
            );

            $blog_js = E360VO_AssetHelper::get_asset_info('/public/assets/js/blog', 'js');
            wp_enqueue_script(
                '360vo-blog-js',
                $blog_js['url'],
                [],
                $blog_js['version'],
                true
            );
        }

        if (is_singular('post')) {
            $reading_css = E360VO_AssetHelper::get_asset_info('/public/assets/css/blog-reading', 'css');
            wp_enqueue_style(
                '360vo-blog-reading',
                $reading_css['url'],
                ['360vo-theme-style', '360vo-blog'],
                $reading_css['version']
            );

            $reading_js = E360VO_AssetHelper::get_asset_info('/public/assets/js/blog-reading', 'js');
            wp_enqueue_script(
                '360vo-blog-reading',
                $reading_js['url'],
                [],
                $reading_js['version'],
                true
            );
        }

        $should_load_header_context_assets = !is_admin();

        if ($should_load_header_context_assets) {
            $header_context_css = E360VO_AssetHelper::get_asset_info('/public/assets/css/header-context', 'css');
            wp_enqueue_style(
                '360vo-header-context',
                $header_context_css['url'],
                ['360vo-theme-style'],
                $header_context_css['version']
            );

            $header_context_js = E360VO_AssetHelper::get_asset_info('/public/assets/js/header-context', 'js');
            wp_enqueue_script(
                '360vo-header-context',
                $header_context_js['url'],
                [],
                $header_context_js['version'],
                true
            );
        }

        if (is_page() || is_singular('post')) {
            $scroll_top_css = E360VO_AssetHelper::get_asset_info('/public/assets/css/scroll-top', 'css');
            wp_enqueue_style(
                '360vo-scroll-top',
                $scroll_top_css['url'],
                ['360vo-theme-style'],
                $scroll_top_css['version']
            );

            $scroll_top_js = E360VO_AssetHelper::get_asset_info('/public/assets/js/scroll-top', 'js');
            wp_enqueue_script(
                '360vo-scroll-top',
                $scroll_top_js['url'],
                [],
                $scroll_top_js['version'],
                true
            );
        }
    }


    private function is_blog_context(): bool
    {
        if (function_exists('th360_is_blog_brand_archive') && th360_is_blog_brand_archive()) return true;

        // Blog listing como Page Template
        if (is_page_template('home.php')) return true;

        // Blog “real” de WP
        if (is_home()) return true;

        // Archivos de blog
        if (is_category() || is_tag() || is_author() || is_date()) return true;

        // Single de post
        if (is_singular('post')) return true;

        return false;
    }

    public function filter_style_loader_tag($html, $handle, $href, $media)
    {
        if ($handle !== '360vo-theme-style') {
            return $html;
        }

        $media_attr = ($media && $media !== 'all') ? ' media="' . esc_attr($media) . '"' : '';
        return '<link rel="stylesheet" id="' . esc_attr($handle) . '-css" href="' . esc_url($href) . '"' . $media_attr . '>' . "\n";
    }

    private function should_load_footer_map_lazy(): bool
    {
        return true;
    }
}
