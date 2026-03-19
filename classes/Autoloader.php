<?php
// classes/Autoloader.php
if (! defined('ABSPATH')) exit;

class E360VO_Autoloader
{
    public static function register()
    {
        spl_autoload_register([__CLASS__, 'autoload']);
    }

    public static function autoload($class)
    {
        // solo cargamos nuestras clases que empiecen por E360VO_
        if (0 !== strpos($class, 'E360VO_')) {
            return;
        }
        $file = __DIR__ . '/' . substr($class, 7) . '.php';
        if (file_exists($file)) {
            require $file;
        }
    }
}
