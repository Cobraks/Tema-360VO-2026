<?php
// classes/AssetHelper.php
if (!defined('ABSPATH')) exit;

class E360VO_AssetHelper
{

    /**
     * Devuelve la URL y versión (filemtime) de un asset, priorizando .min si existe.
     *
     * @param string $base_path Ruta base del archivo (sin extensión). Ej: /public/assets/js/footer-map-lazy
     * @param string $type 'js' o 'css'
     * @return array { url: string, version: int|string }
     */
    public static function get_asset_info($base_path, $type)
    {
        $dir = THEME_DIR . $base_path;
        $uri = THEME_URI . $base_path;

        // Intentar con .min primero
        $min_file = $dir . '.min.' . $type;
        if (file_exists($min_file)) {
            return [
                'url'     => $uri . '.min.' . $type,
                'version' => filemtime($min_file)
            ];
        }

        // Fallback al original
        $orig_file = $dir . '.' . $type;
        if (file_exists($orig_file)) {
            return [
                'url'     => $uri . '.' . $type,
                'version' => filemtime($orig_file)
            ];
        }

        // Último recurso: versión del tema
        return [
            'url'     => $uri . '.' . $type,
            'version' => THEME_VERSION
        ];
    }
}
