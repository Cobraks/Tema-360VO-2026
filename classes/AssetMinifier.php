<?php
// classes/class-e360vo-asset-minifier.php

if (! defined('ABSPATH')) {
    exit;
}

class E360VO_AssetMinifier
{
    protected static $assets = [];

    public static function init()
    {
        // La minificación ya no se ejecuta en runtime.
        // Se lanza desde herramientas locales (VS Code / scripts)
        // o manualmente al activar el tema.
    }

    public static function register($handle, $orig, $min, $type = 'css')
    {
        if (in_array($type, ['css', 'js'], true) && file_exists($orig)) {
            self::$assets[$handle] = compact('orig', 'min', 'type');
        }
    }

    public static function register_defaults($theme_dir)
    {
        $theme_dir = rtrim($theme_dir, '/\\');

        $assets = [
            ['360vo-theme-style', '/style.css', '/style.min.css', 'css'],
            ['360vo-funciones-tema', '/public/assets/js/funciones_tema.js', '/public/assets/js/funciones_tema.min.js', 'js'],
            ['360vo-critical', '/public/assets/css/critical.css', '/public/assets/css/critical.min.css', 'css'],
            ['360vo-pages', '/public/assets/css/pages.css', '/public/assets/css/pages.min.css', 'css'],
            ['360vo-logged-in', '/public/assets/css/logged-in.css', '/public/assets/css/logged-in.min.css', 'css'],
            ['360vo-blog', '/public/assets/css/blog.css', '/public/assets/css/blog.min.css', 'css'],
            ['360vo-blog-js', '/public/assets/js/blog.js', '/public/assets/js/blog.min.js', 'js'],
            ['360vo-blog-reading', '/public/assets/css/blog-reading.css', '/public/assets/css/blog-reading.min.css', 'css'],
            ['360vo-blog-reading-js', '/public/assets/js/blog-reading.js', '/public/assets/js/blog-reading.min.js', 'js'],
            ['360vo-header-context', '/public/assets/css/header-context.css', '/public/assets/css/header-context.min.css', 'css'],
            ['360vo-header-context-js', '/public/assets/js/header-context.js', '/public/assets/js/header-context.min.js', 'js'],
        ];

        foreach ($assets as [$handle, $orig, $min, $type]) {
            self::register(
                $handle,
                $theme_dir . $orig,
                $theme_dir . $min,
                $type
            );
        }
    }

    public static function maybe_minify_all()
    {
        foreach (self::$assets as $info) {
            self::maybe_minify($info['orig'], $info['min'], $info['type']);
        }
    }

    protected static function maybe_minify($orig, $min, $type)
    {
        $mtO = filemtime($orig);
        $mtM = file_exists($min) ? filemtime($min) : 0;

        if ($mtO <= $mtM) {
            return;
        }

        $code = file_get_contents($orig);

        if ($type === 'css') {

            // Quitar comentarios
            $code = preg_replace('!/\*.*?\*/!s', '', $code);

            // Reducir espacios
            $code = preg_replace('/\s+/', ' ', $code);

            // Limpieza básica
            $code = str_replace(
                [' {', '{ ', ' }', '} ', ' ;', '; '],
                ['{', '{', '}', '}', ';', ';'],
                $code
            );

            $code = trim($code);
        } elseif ($type === 'js') {

            /**
             * IMPORTANTE:
             * No intentamos eliminar comentarios tipo //
             * porque rompería URLs y strings.
             */

            // Solo quitamos comentarios multilínea
            $code = preg_replace('!/\*.*?\*/!s', '', $code);

            // Opcional: reducir espacios múltiples
            $code = preg_replace('/\n\s*\n/', "\n", $code);

            $code = trim($code);
        }

        file_put_contents($min, $code);
        @chmod($min, 0644);
    }
}
