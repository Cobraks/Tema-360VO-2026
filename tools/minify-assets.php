<?php

declare(strict_types=1);

$theme_dir = dirname(__DIR__);

if (!defined('ABSPATH')) {
    define('ABSPATH', $theme_dir);
}

require_once $theme_dir . '/classes/AssetMinifier.php';

E360VO_AssetMinifier::register_defaults($theme_dir);
E360VO_AssetMinifier::maybe_minify_all();

fwrite(STDOUT, "Assets minificados correctamente.\n");
