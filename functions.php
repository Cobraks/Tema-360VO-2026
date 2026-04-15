<?php

/**
 * Funciones para 360vo-theme
 *
 * @package 360vo-theme
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Constantes del tema
 */
define('THEME_VERSION', '3.0.23');
define('THEME_DIR', get_template_directory());
define('THEME_URI', get_template_directory_uri());

/**
 * Autoloader
 */
require_once THEME_DIR . '/classes/Autoloader.php';
E360VO_Autoloader::register();

/**
 * Generar minificados al activar el tema
 */
add_action('after_switch_theme', 'th360_generar_min_assets');
function th360_generar_min_assets()
{
    if (class_exists('E360VO_AssetMinifier')) {
        E360VO_AssetMinifier::maybe_minify_all();
    }
}

/**
 * Debug (manual)
 * Descomenta el hook si necesitas inspeccionar scripts/estilos.
 */
// add_action('wp_print_scripts', 'debug_list_scripts_styles', 999);
function debug_list_scripts_styles()
{
    global $wp_scripts, $wp_styles;

    echo '<pre style="background:#fff;color:#000;font-size:14px;">';
    echo "==== REGISTERED SCRIPTS ====\n";
    foreach ($wp_scripts->registered as $handle => $data) {
        echo $handle . ' -> ' . $data->src . "\n";
    }

    echo "\n==== ENQUEUED SCRIPTS (QUEUE) ====\n";
    foreach ($wp_scripts->queue as $handle) {
        echo $handle . "\n";
    }

    echo "\n==== REGISTERED STYLES ====\n";
    foreach ($wp_styles->registered as $handle => $data) {
        echo $handle . ' -> ' . $data->src . "\n";
    }

    echo "\n==== ENQUEUED STYLES (QUEUE) ====\n";
    foreach ($wp_styles->queue as $handle) {
        echo $handle . "\n";
    }
    echo '</pre>';
}

/**
 * ---------------------------------------------------------
 * Helpers (templates)
 * ---------------------------------------------------------
 */

/**
 * Genera clases del contenedor de la entrada
 */
function generar_clases_entry_container()
{
    $clases = ['entry__container'];

    if (function_exists('get_field') && get_field('cabecera_imagen_de_fondo')) {
        $imagen_diferente_id = get_field('cabecera_imagen_diferente');
        $background_image = '';

        if ($imagen_diferente_id) {
            $background_image = wp_get_attachment_url($imagen_diferente_id);
        } elseif (has_post_thumbnail()) {
            $background_image = get_the_post_thumbnail_url();
        }

        if ($background_image) {
            $clases[] = 'entry__container--image-background';
        }
    }

    if (function_exists('get_field') && get_field('cabecera_hero_pantalla_completa')) {
        $clases[] = 'entry__container--full_screen';
    }

    if (function_exists('get_field') && get_field('cabecera_claramente_visible')) {
        $clases[] = 'entry__container--visible';
    }

    if (function_exists('get_field')) {
        $color_hero = get_field('cabecera_color_hero');
        if ($color_hero) {
            $clases[] = 'entry__container--color-' . sanitize_html_class($color_hero);
        }

        $formato_imagen = get_field('imagen_destacada_formato_imagen');
        if ($formato_imagen) {
            $clases[] = 'entry__container--image-' . sanitize_html_class($formato_imagen);
        }

        // Esquinas redondeadas (grupo imagen_destacada)
        $imagen_destacada = get_field('imagen_destacada');
        if (is_array($imagen_destacada) && !empty($imagen_destacada['esquinas_redondeadas'])) {
            $clases[] = 'entry__container--image-border-radius';
        }
    }

    return implode(' ', $clases);
}

/**
 * Obtiene el style inline del background-image
 */
function obtener_estilo_fondo()
{
    if (!function_exists('get_field') || !get_field('cabecera_imagen_de_fondo')) {
        return '';
    }

    $imagen_diferente_id = get_field('cabecera_imagen_diferente');
    $background_image = '';

    if ($imagen_diferente_id) {
        $background_image = wp_get_attachment_url($imagen_diferente_id);
    } elseif (has_post_thumbnail()) {
        $background_image = get_the_post_thumbnail_url();
    }

    if ($background_image) {
        return 'style="--background-image: url(' . esc_url($background_image) . ');"';
    }

    return '';
}

/**
 * Render compartido de la tabla de contenidos.
 */
function th360_render_table_of_contents(array $args = []): void
{
    $args = wp_parse_args($args, [
        'classes' => [],
        'content_id' => 'toc-content',
        'label' => 'Tabla de contenidos',
        'toggle_text' => 'Mostrar tabla de contenidos',
        'toggle_aria_label' => 'Mostrar tabla de contenidos',
    ]);

    $classes = array_map(
        'sanitize_html_class',
        array_filter(array_merge(['toc-container'], (array) $args['classes']))
    );
?>
    <aside id="toc-container" class="<?php echo esc_attr(implode(' ', $classes)); ?>" aria-label="<?php echo esc_attr($args['label']); ?>">
        <div id="menu-placeholder">
            <button class="toc-container__toggle" type="button" aria-expanded="false" aria-controls="<?php echo esc_attr($args['content_id']); ?>" aria-label="<?php echo esc_attr($args['toggle_aria_label']); ?>">
                <span class="toc-container__icon toc-container__icon--toc"><?php echo E360VO_Icon::get('toc_menu', ['aria-hidden' => 'true']); ?></span>
                <span class="toc-container__text"><?php echo esc_html($args['toggle_text']); ?></span>
                <span class="toc-container__icon toc-container__icon--expand"><?php echo E360VO_Icon::get('toc_expand', ['aria-hidden' => 'true']); ?></span>
                <span class="toc-container__icon toc-container__icon--collapse"><?php echo E360VO_Icon::get('toc_collapse', ['aria-hidden' => 'true']); ?></span>
            </button>
            <div id="<?php echo esc_attr($args['content_id']); ?>" class="toc-container__content hidden">
                <!-- La tabla de contenidos se genera dinamicamente -->
            </div>
        </div>
    </aside>
<?php
}

/**
 * Tiempo de lectura
 */
function calcular_tiempo_lectura($content)
{
    $words_per_minute = 200;
    $word_count = str_word_count(strip_tags((string) $content));
    return (int) ceil($word_count / $words_per_minute);
}

function obtener_tiempo_lectura($post_id)
{
    if (!function_exists('get_field')) {
        return '';
    }

    $opciones       = get_field('opciones_paginas', 'option');
    $mostrar_tiempo = (is_array($opciones) ? ($opciones['tiempo_estimado_lectura'] ?? false) : false);

    if (!$mostrar_tiempo) {
        return '';
    }

    $content        = get_post_field('post_content', $post_id);
    $tiempo_lectura = calcular_tiempo_lectura($content);

    if ($tiempo_lectura <= 0) {
        return '';
    }

    return sprintf(
        '<div class="tiempo-lectura">%s minutos de lectura</div>',
        esc_html((string) $tiempo_lectura)
    );
}

/**
 * Últimos posts (utility)
 */
function get_latest_posts($num_posts = 5)
{
    $args = [
        'numberposts' => max(1, (int) $num_posts),
        'post_status' => 'publish',
    ];

    return wp_get_recent_posts($args, OBJECT);
}

/**
 * ---------------------------------------------------------
 * Minificador y registro de assets
 * ---------------------------------------------------------
 */
if (class_exists('E360VO_AssetMinifier')) {
    E360VO_AssetMinifier::register_defaults(THEME_DIR);
}

/**
 * ---------------------------------------------------------
 * Inicialización de clases del tema
 * ---------------------------------------------------------
 */
new E360VO_CustomLogin();
new E360VO_Customizer();
new E360VO_ThemeSetup();
new E360VO_ColorPalette();
new E360VO_Optimizaciones();

// Carga la clase de avatar personalizado
require_once THEME_DIR . '/classes/UserAvatar.php';
new E360VO_UserAvatar();

/**
 * Encolar scripts y estilos frontend
 */
new E360VO_EnqueueScripts();

/**
 * Contact Form 7: quitar <p> automáticos
 */
add_filter('wpcf7_autop_or_not', '__return_false');

/**
 * Shortcode por defecto del formulario de newsletter (CF7).
 * Se puede sobrescribir desde child theme/plugin con el mismo filtro.
 */
add_filter('th360_newsletter_shortcode', function (string $shortcode): string {
    if (trim($shortcode) !== '') {
        return $shortcode;
    }

    return '[contact-form-7 id="04d14f1" title="Newsletter"]';
});

/**
 * Defer selectivo para scripts del tema
 */



add_action('wp_enqueue_scripts', function () {
    if (is_admin()) {
        return;
    }

    // Eliminar jQuery y jQuery Migrate en frontend público
    wp_deregister_script('jquery');
    wp_deregister_script('jquery-core');
    wp_deregister_script('jquery-migrate');
}, 1);

add_action('wp_default_scripts', function ($scripts) {
    if (is_admin()) return;

    if (isset($scripts->registered['jquery'])) {
        $scripts->registered['jquery']->deps = [];
    }
    if (isset($scripts->registered['jquery-core'])) {
        $scripts->registered['jquery-core']->deps = [];
    }
    if (isset($scripts->registered['jquery-migrate'])) {
        unset($scripts->registered['jquery-migrate']);
    }
});




add_filter('script_loader_tag', function ($tag, $handle) {
    $defer_handles = [
        'funciones_tema',
        '360vo-front-page',
        '360vo-footer-map-lazy', // opcional, si quieres defer también aquí
        '360vo-blog-js',
    ];


    if (in_array($handle, $defer_handles, true)) {
        return str_replace('<script ', '<script defer ', $tag);
    }

    return $tag;
}, 10, 2);


/**
 * Admin: encolar estilos/funciones admin
 */
if (is_admin()) {
    require_once THEME_DIR . '/admin/admin_functions.php';
    require_once THEME_DIR . '/admin/admin_enqueue_scripts.php';
}

/**
 * Quitar CSS sizes placeholder de WP (img)
 */
add_action('init', function () {
    remove_action('wp_head', '_wp_add_sizes_placeholder_css', 1);
});

/**
 * EWWW: forzar binarios del sistema (pendiente mover a clase/config)
 */
add_filter('ewww_use_server_binaries', '__return_true');






// Permitir SVG en administración (Revisar si solo par aun campo específico) Mover a una clase

/**
 * ---------------------------------------------------------
 * SVG inline seguro para ACF (logo_svg)
 * ---------------------------------------------------------
 */

/**
 * Allowlist mínima de SVG (sin <style>, sin <script>, sin foreignObject).
 * Ajusta solo si realmente necesitas más tags/attrs.
 */
function th360_allowed_svg_html(): array
{
    return [
        'svg' => [
            'xmlns' => true,
            'viewbox' => true,        // KSES usa lowercase
            'version' => true,
            'id' => true,
            'class' => true,
            'width' => true,
            'height' => true,
            'role' => true,
            'aria-hidden' => true,
            'aria-labelledby' => true,
            'focusable' => true,
            'data-name' => true,
        ],
        'path' => [
            'd' => true,
            'class' => true,
            'fill' => true,
            'stroke' => true,
            'stroke-width' => true,
            'stroke-linecap' => true,
            'stroke-linejoin' => true,
            'transform' => true,
        ],
        'g' => [
            'class' => true,
            'transform' => true,
            'fill' => true,
            'stroke' => true,
            'stroke-width' => true,
        ],
        'title' => [],
        'desc'  => [],
        'defs'  => [],
    ];
}

/**
 * Detecta si la request actual parece un guardado de ACF con payload que incluye SVG.
 * Esto evita abrir la mano globalmente en 'post' para todo WordPress.
 */
function th360_request_is_acf_save_with_svg(): bool
{
    if (!is_admin()) return false;

    // En validaciones/guardados ACF suele haber POST['acf'] con field_keys
    if (!isset($_POST['acf']) || !is_array($_POST['acf'])) return false;

    // Opciones ACF suelen marcar post_id/options de alguna forma (varía según pantalla)
    $post_id =
        (string)($_POST['post_id'] ?? '') .
        (string)($_POST['acf_post_id'] ?? '') .
        (string)($_POST['_acf_post_id'] ?? '');

    $looks_like_options = (stripos($post_id, 'options') !== false);

    // Buscamos si algún valor contiene "<svg"
    $has_svg = false;
    foreach ($_POST['acf'] as $v) {
        if (is_string($v) && stripos($v, '<svg') !== false) {
            $has_svg = true;
            break;
        }
    }

    // Permitimos si es options o si al menos hay SVG en el payload
    return $has_svg && ($looks_like_options || wp_doing_ajax() || isset($_POST['acf']));
}

/**
 * Amplía el allowlist de KSES solo cuando ACF está guardando y hay SVG.
 * Importante: cubrimos contextos 'acf' y 'post' porque ACF puede usar wp_kses_post internamente.
 */
add_filter('wp_kses_allowed_html', function ($tags, $context) {

    if (!in_array($context, ['acf', 'post'], true)) {
        return $tags;
    }

    if (!th360_request_is_acf_save_with_svg()) {
        return $tags;
    }

    $svg = th360_allowed_svg_html();

    foreach ($svg as $tag => $attrs) {
        if (!isset($tags[$tag])) {
            $tags[$tag] = $attrs;
            continue;
        }

        // Merge de atributos
        if (is_array($tags[$tag]) && is_array($attrs)) {
            $tags[$tag] = array_merge($tags[$tag], $attrs);
        }
    }

    return $tags;
}, 10, 2);

/**
 * Sanitiza específicamente el campo ACF "logo_svg" al guardar.
 * Esto asegura que, aunque alguien intente colar cosas raras, se guarda solo SVG permitido.
 */
add_filter('acf/update_value/name=logo_svg', function ($value, $post_id, $field) {

    if (!is_string($value)) {
        return $value;
    }

    $value = trim($value);
    if ($value === '') {
        return $value;
    }

    // Normaliza: algunos pegan con espacios raros / saltos
    // (no tocamos el contenido salvo trim)
    return wp_kses($value, th360_allowed_svg_html());
}, 10, 3);
