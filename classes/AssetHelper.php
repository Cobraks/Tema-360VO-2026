<?php
// classes/AssetHelper.php
if (!defined('ABSPATH')) exit;

class E360VO_AssetHelper
{
    protected static function should_use_minified_assets(): bool
    {
        if (defined('SCRIPT_DEBUG') && SCRIPT_DEBUG) {
            return false;
        }

        return true;
    }

    protected static function is_minified_asset_fresh(string $orig_file, string $min_file): bool
    {
        if (!file_exists($min_file)) {
            return false;
        }

        if (!file_exists($orig_file)) {
            return true;
        }

        return filemtime($min_file) >= filemtime($orig_file);
    }

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

        $min_file = $dir . '.min.' . $type;
        $orig_file = $dir . '.' . $type;

        if (self::should_use_minified_assets() && self::is_minified_asset_fresh($orig_file, $min_file)) {
            return [
                'url'     => $uri . '.min.' . $type,
                'version' => filemtime($min_file)
            ];
        }

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
