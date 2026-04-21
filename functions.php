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
define('THEME_VERSION', '3.2.20');
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

add_action('after_setup_theme', 'th360_maybe_flush_rewrites_on_version_change', 20);
function th360_maybe_flush_rewrites_on_version_change()
{
    $stored_version = (string) get_option('th360_theme_version', '');
    if ($stored_version === THEME_VERSION) {
        return;
    }

    set_transient('th360_flush_rewrite_rules', 1, DAY_IN_SECONDS);
    update_option('th360_theme_version', THEME_VERSION, false);
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
    $post_id = get_queried_object_id();
    $clases = ['entry__container'];
    $header_settings = th360_get_page_header_settings($post_id);
    $image_settings = th360_get_page_featured_image_settings($post_id);
    $has_featured_image = th360_should_render_page_featured_image($post_id);

    if ($header_settings['image_background'] && th360_get_page_background_image_url($post_id) !== '') {
        $clases[] = 'entry__container--image-background';
    }

    if ($header_settings['full_screen']) {
        $clases[] = 'entry__container--full_screen';
    }

    if ($header_settings['visible']) {
        $clases[] = 'entry__container--visible';
    }

    if ($header_settings['color'] !== '') {
        $clases[] = 'entry__container--color-' . sanitize_html_class($header_settings['color']);
    }

    if ($image_settings['format'] !== '') {
        $clases[] = 'entry__container--image-' . sanitize_html_class($image_settings['format']);
    }

    $clases[] = $has_featured_image ? 'entry__container--with-image' : 'entry__container--without-image';
    $clases[] = 'entry__container--justify-' . sanitize_html_class($header_settings['justification']);

    if ($image_settings['radius'] !== '') {
        $clases[] = 'entry__container--image-radius-' . sanitize_html_class($image_settings['radius']);
        if ($image_settings['radius'] !== '0') {
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
    $post_id = get_queried_object_id();
    $header_settings = th360_get_page_header_settings($post_id);

    if (!$header_settings['image_background']) {
        return '';
    }

    $background_image = th360_get_page_background_image_url($post_id);

    if ($background_image) {
        return 'style="--background-image: url(' . esc_url($background_image) . ');"';
    }

    return '';
}

/**
 * Normaliza un valor choice de ACF con return format "Both" o variantes.
 */
function th360_get_acf_choice_value($value): string
{
    if (is_array($value)) {
        foreach (['value', 'key', 'slug', 'name'] as $candidate_key) {
            if (isset($value[$candidate_key]) && is_scalar($value[$candidate_key])) {
                return trim((string) $value[$candidate_key]);
            }
        }

        if (isset($value[0]) && is_scalar($value[0])) {
            return trim((string) $value[0]);
        }
    }

    if (is_bool($value)) {
        return $value ? '1' : '0';
    }

    if (is_scalar($value)) {
        return trim((string) $value);
    }

    return '';
}

/**
 * Normaliza un valor ACF de imagen a attachment ID.
 */
function th360_get_acf_attachment_id($value): int
{
    if (is_array($value)) {
        foreach (['ID', 'id'] as $candidate_key) {
            if (isset($value[$candidate_key]) && is_numeric($value[$candidate_key])) {
                return (int) $value[$candidate_key];
            }
        }
    }

    return is_numeric($value) ? (int) $value : 0;
}

/**
 * Devuelve la configuración actual del grupo cabecera para páginas.
 */
function th360_get_page_header_settings(int $post_id): array
{
    $group = [];

    if ($post_id > 0 && function_exists('get_field')) {
        $group_value = get_field('cabecera', $post_id);
        if (is_array($group_value)) {
            $group = $group_value;
        }
    }

    $image_background = array_key_exists('imagen_de_fondo', $group)
        ? !empty($group['imagen_de_fondo'])
        : (function_exists('get_field') ? !empty(get_field('cabecera_imagen_de_fondo', $post_id)) : false);

    $visible = array_key_exists('claramente_visible', $group)
        ? !empty($group['claramente_visible'])
        : (function_exists('get_field') ? !empty(get_field('cabecera_claramente_visible', $post_id)) : false);

    $full_screen = array_key_exists('hero_pantalla_completa', $group)
        ? !empty($group['hero_pantalla_completa'])
        : (function_exists('get_field') ? !empty(get_field('cabecera_hero_pantalla_completa', $post_id)) : false);

    $image_different_id = array_key_exists('imagen_diferente', $group)
        ? th360_get_acf_attachment_id($group['imagen_diferente'])
        : (function_exists('get_field') ? th360_get_acf_attachment_id(get_field('cabecera_imagen_diferente', $post_id)) : 0);

    $color = array_key_exists('color_hero', $group)
        ? th360_get_acf_choice_value($group['color_hero'])
        : (function_exists('get_field') ? th360_get_acf_choice_value(get_field('cabecera_color_hero', $post_id)) : '');

    $justification = array_key_exists('justificacion', $group)
        ? th360_get_acf_choice_value($group['justificacion'])
        : (function_exists('get_field') ? th360_get_acf_choice_value(get_field('cabecera_justificacion', $post_id)) : '');

    $justification = in_array($justification, ['centro', 'izquierda', 'derecha'], true)
        ? $justification
        : 'centro';

    return [
        'image_background'   => $image_background,
        'visible'            => $visible,
        'full_screen'        => $full_screen,
        'image_different_id' => $image_different_id,
        'color'              => $color,
        'justification'      => $justification,
    ];
}

/**
 * Devuelve la configuración de imagen destacada para páginas.
 */
function th360_get_page_featured_image_settings(int $post_id): array
{
    $group = [];

    if ($post_id > 0 && function_exists('get_field')) {
        $group_value = get_field('imagen_destacada', $post_id);
        if (is_array($group_value)) {
            $group = $group_value;
        }
    }

    $format = array_key_exists('formato_imagen', $group)
        ? th360_get_acf_choice_value($group['formato_imagen'])
        : (function_exists('get_field') ? th360_get_acf_choice_value(get_field('imagen_destacada_formato_imagen', $post_id)) : '');

    if (!in_array($format, ['horizontal', 'vertical', 'cuadrado'], true)) {
        $format = 'horizontal';
    }

    $show_image = array_key_exists('mostrar_imagen', $group)
        ? !empty($group['mostrar_imagen'])
        : (function_exists('get_field')
            ? !empty(get_field('imagen_destacada_mostrar_imagen', $post_id)) || !empty(get_field('mostrar_imagen', $post_id))
            : false);

    $radius_value = array_key_exists('esquinas_redondeadas', $group)
        ? $group['esquinas_redondeadas']
        : (function_exists('get_field')
            ? (get_field('imagen_destacada_esquinas_redondeadas', $post_id) ?? get_field('esquinas_redondeadas', $post_id))
            : '');

    $radius = th360_get_acf_choice_value($radius_value);
    if ($radius === '1') {
        $radius = '25';
    } elseif ($radius === '') {
        $radius = '0';
    }

    if (!in_array($radius, ['0', '25', '50', '100'], true)) {
        $radius = '25';
    }

    return [
        'format'     => $format,
        'show_image' => $show_image,
        'radius'     => $radius,
    ];
}

/**
 * Indica si la tabla de contenidos de la página está activada.
 */
function th360_is_page_toc_enabled(int $post_id): bool
{
    if ($post_id <= 0 || !function_exists('get_field')) {
        return false;
    }

    $group = get_field('tabla_de_contenidos', $post_id);
    if (is_array($group) && array_key_exists('activar_desactivar_tabla', $group)) {
        return !empty($group['activar_desactivar_tabla']);
    }

    return !empty(get_field('tabla_de_contenidos_activar_desactivar_tabla', $post_id));
}

/**
 * Determina si la imagen destacada de la página debe renderizarse.
 */
function th360_should_render_page_featured_image(int $post_id): bool
{
    if ($post_id <= 0 || !has_post_thumbnail($post_id)) {
        return false;
    }

    $image_settings = th360_get_page_featured_image_settings($post_id);
    return !empty($image_settings['show_image']);
}

/**
 * URL de imagen de fondo del hero para páginas.
 */
function th360_get_page_background_image_url(int $post_id): string
{
    if ($post_id <= 0) {
        return '';
    }

    $header_settings = th360_get_page_header_settings($post_id);
    if (!$header_settings['image_background']) {
        return '';
    }

    if ($header_settings['image_different_id'] > 0) {
        $image_url = wp_get_attachment_url($header_settings['image_different_id']);
        if (is_string($image_url) && $image_url !== '') {
            return $image_url;
        }
    }

    $thumbnail_url = get_the_post_thumbnail_url($post_id);
    return is_string($thumbnail_url) ? $thumbnail_url : '';
}

/**
 * Clases BEM del bloque visual de imagen destacada de la página.
 */
function th360_get_page_featured_image_classes(int $post_id): string
{
    $image_settings = th360_get_page_featured_image_settings($post_id);

    $classes = [
        'entry-image',
        'entry-image--' . sanitize_html_class($image_settings['format']),
        'entry-image--radius-' . sanitize_html_class($image_settings['radius']),
    ];

    return implode(' ', $classes);
}

/**
 * Devuelve el titulo visible de la cabecera de pagina.
 * De momento mantiene compatibilidad con el override ACF.
 */
function th360_get_page_header_title(int $post_id): string
{
    if ($post_id > 0 && function_exists('get_field')) {
        $custom_title = trim((string) get_field('titulo_h1', $post_id));
        if ($custom_title !== '') {
            return $custom_title;
        }
    }

    return get_the_title($post_id);
}

/**
 * Devuelve el titulo de cabecera listo para imprimir con un allowlist minimo.
 */
function th360_get_page_header_title_html(int $post_id): string
{
    $allowed_title_tags = [
        'span'   => ['class' => true],
        'br'     => true,
        'em'     => true,
        'strong' => true,
    ];

    return wp_kses(th360_get_page_header_title($post_id), $allowed_title_tags);
}

/**
 * Renderiza la introduccion de pagina admitiendo texto plano o contenido WYSIWYG.
 */
function th360_get_page_intro_html(int $post_id): string
{
    if ($post_id <= 0 || !function_exists('get_field')) {
        return '';
    }

    $intro = get_field('parrafo_introduccion', $post_id);
    if (!is_string($intro)) {
        return '';
    }

    $intro = trim($intro);
    if ($intro === '') {
        return '';
    }

    $has_html = (bool) preg_match('/<[^>]+>/', $intro) || str_contains($intro, '<!-- wp:');
    $intro_markup = $has_html
        ? wp_kses_post($intro)
        : wp_kses_post(wpautop($intro));

    if (trim($intro_markup) === '') {
        return '';
    }

    return sprintf('<div class="intro-paragraph">%s</div>', $intro_markup);
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
    <aside id="toc-container" class="<?php echo esc_attr(implode(' ', $classes)); ?>" aria-label="<?php echo esc_attr($args['label']); ?>" hidden>
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
 * Normaliza valores de campos ACF de taxonomía a un WP_Term.
 */
function th360_resolve_acf_term($value): ?WP_Term
{
    if ($value instanceof WP_Term) {
        return $value;
    }

    if (is_array($value)) {
        if (isset($value['term_id'])) {
            $term = get_term((int) $value['term_id']);
            return ($term instanceof WP_Term && !is_wp_error($term)) ? $term : null;
        }

        if (isset($value[0])) {
            return th360_resolve_acf_term($value[0]);
        }
    }

    if (is_numeric($value)) {
        $term = get_term((int) $value);
        return ($term instanceof WP_Term && !is_wp_error($term)) ? $term : null;
    }

    return null;
}

/**
 * Devuelve la URL base del blog actual.
 */
function th360_get_blog_home_url(): string
{
    $blog_slug = class_exists('E360VO_ThemeSetup')
        ? (string) E360VO_ThemeSetup::get_blog_slug()
        : 'noticias';

    return home_url('/' . trim($blog_slug, '/') . '/');
}

/**
 * Normaliza valores ACF de post object a un WP_Post.
 */
function th360_resolve_acf_post($value): ?WP_Post
{
    if ($value instanceof WP_Post) {
        return $value;
    }

    if (is_array($value)) {
        if (isset($value['ID'])) {
            $post = get_post((int) $value['ID']);
            return ($post instanceof WP_Post) ? $post : null;
        }

        if (isset($value[0])) {
            return th360_resolve_acf_post($value[0]);
        }
    }

    if (is_numeric($value)) {
        $post = get_post((int) $value);
        return ($post instanceof WP_Post) ? $post : null;
    }

    return null;
}

/**
 * Devuelve la configuracion CTA de una pagina.
 */
function th360_get_page_cta_group(int $post_id): array
{
    if (!function_exists('get_field')) {
        return [];
    }

    $group = get_field('llamada_a_la_accion', $post_id);
    if (is_array($group) && !empty($group)) {
        return $group;
    }

    if (!function_exists('get_fields')) {
        return [];
    }

    $all_fields = get_fields($post_id);
    if (!is_array($all_fields) || empty($all_fields)) {
        return [];
    }

    $candidate_keys = [
        'mostrar_cta',
        'acciones',
        'opciones_compartir',
        'opciones_copiar_enlace',
        'opciones_enlace_stock',
        'opciones_enlace_a_marca',
        'opciones_enlace_a_modelo',
        'opciones_enlace_a_carroceria',
        'opciones_enlace_a_categoria_blog',
        'opciones_enlace_a_vehiculo',
        'opciones_enlace_a_entrada_blog',
        'opciones_enlace_a_pagina',
        'opciones_enlace_externo',
        'opciones_enlace_home',
        'opciones_enlace_a_home',
        'opciones_enlace_blog',
        'opciones_enlace_a_blog',
        'opciones_enlace_post',
        'opciones_enlace_a_post',
        'opciones_enlace_categoria',
        'opciones_enlace_a_categoria',
    ];

    $resolved = [];
    foreach ($candidate_keys as $candidate_key) {
        if (array_key_exists($candidate_key, $all_fields)) {
            $resolved[$candidate_key] = $all_fields[$candidate_key];
        }
    }

    return $resolved;
}

/**
 * Resuelve un campo CTA desde un grupo o por nombre directo.
 */
function th360_get_page_cta_field(array $cta_group, string $field_name, int $post_id, array $fallback_names = [])
{
    $field_names = array_values(array_unique(array_filter(array_merge([$field_name], $fallback_names), static function ($name) {
        return is_string($name) && $name !== '';
    })));

    foreach ($field_names as $candidate_name) {
        if (array_key_exists($candidate_name, $cta_group)) {
            return $cta_group[$candidate_name];
        }
    }

    if (!function_exists('get_field')) {
        return null;
    }

    foreach ($field_names as $candidate_name) {
        $value = get_field($candidate_name, $post_id);
        if ($value !== null) {
            return $value;
        }
    }

    return null;
}

/**
 * Etiquetas fallback para las acciones CTA.
 */
function th360_get_page_cta_action_default_label(string $action): string
{
    $labels = [
        'copiar' => 'Copiar link',
        'compartir' => 'Compartir',
        'stock' => 'Stock',
        'marca' => 'Marca',
        'carroceria' => 'Carroceria',
        'modelo' => 'Modelo',
        'coche' => 'Coche',
        'home' => 'Inicio',
        'post' => 'Entrada',
        'categoria' => 'Categoria',
        'blog' => 'Blog',
        'pagina' => 'Pagina',
        'externo' => 'Enlace externo',
    ];

    return $labels[$action] ?? 'Enlace';
}

/**
 * Devuelve los posibles nombres de grupo ACF por accion.
 */
function th360_get_page_cta_action_group_names(string $action): array
{
    switch ($action) {
        case 'copiar':
            return ['opciones_copiar_enlace'];

        case 'compartir':
            return ['opciones_compartir'];

        case 'stock':
            return ['opciones_enlace_stock'];

        case 'marca':
            return ['opciones_enlace_a_marca'];

        case 'modelo':
            return ['opciones_enlace_a_modelo'];

        case 'carroceria':
            return ['opciones_enlace_a_carroceria'];

        case 'coche':
            return ['opciones_enlace_a_vehiculo'];

        case 'home':
            return ['opciones_enlace_home', 'opciones_enlace_a_home'];

        case 'post':
            return ['opciones_enlace_a_entrada_blog', 'opciones_enlace_post', 'opciones_enlace_a_post'];

        case 'categoria':
            return ['opciones_enlace_a_categoria_blog', 'opciones_enlace_categoria', 'opciones_enlace_a_categoria'];

        case 'blog':
            return ['opciones_enlace_blog', 'opciones_enlace_a_blog'];

        case 'pagina':
            return ['opciones_enlace_a_pagina'];

        case 'externo':
            return ['opciones_enlace_externo'];
    }

    return [];
}

/**
 * Recupera el grupo ACF de una accion concreta.
 */
function th360_get_page_cta_action_group(string $action, array $cta_group, int $post_id): array
{
    foreach (th360_get_page_cta_action_group_names($action) as $group_name) {
        $value = th360_get_page_cta_field($cta_group, $group_name, $post_id);
        if (is_array($value)) {
            return $value;
        }
    }

    return [];
}

/**
 * Normaliza valores de campos choice de ACF con return format "Both".
 */
function th360_get_page_cta_choice_value($value): string
{
    if (is_array($value)) {
        foreach (['value', 'key', 'slug', 'name'] as $candidate_key) {
            if (isset($value[$candidate_key]) && is_scalar($value[$candidate_key])) {
                return trim((string) $value[$candidate_key]);
            }
        }

        if (isset($value[0]) && is_scalar($value[0])) {
            return trim((string) $value[0]);
        }
    }

    if (is_scalar($value)) {
        return trim((string) $value);
    }

    return '';
}

/**
 * Devuelve la clase semantica del tamano de icono configurado.
 */
function th360_get_page_cta_icon_size($value): string
{
    switch (sanitize_key(th360_get_page_cta_choice_value($value))) {
        case 'pequeno':
        case 'small':
            return 'small';

        case 'grande':
        case 'large':
            return 'large';

        default:
            return 'medium';
    }
}

/**
 * Devuelve el radio CSS a partir del choice de ACF.
 */
function th360_get_page_cta_radius_css($value): string
{
    $radius_key = preg_replace('/[^0-9]/', '', th360_get_page_cta_choice_value($value));

    switch ($radius_key) {
        case '0':
            return '0px';

        case '25':
            return '.85rem';

        case '50':
            return '1.25rem';

        case '75':
            return '1.85rem';

        case '100':
        default:
            return '999px';
    }
}

/**
 * Devuelve la configuracion visual de una accion CTA.
 */
function th360_get_page_cta_action_config(string $action, array $cta_group, int $post_id): array
{
    $action_group = th360_get_page_cta_action_group($action, $cta_group, $post_id);

    return [
        'group'       => $action_group,
        'text'        => trim((string) th360_get_page_cta_field($action_group, 'texto_del_boton', $post_id)),
        'description' => trim((string) th360_get_page_cta_field($action_group, 'descripcion_boton', $post_id, ['descripcion_noton'])),
        'highlighted' => (bool) th360_get_page_cta_field($action_group, 'destacado', $post_id),
        'radius_css'  => th360_get_page_cta_radius_css(
            th360_get_page_cta_field($action_group, 'border_radius', $post_id, ['border-radius'])
        ),
        'left_icon'   => th360_get_page_cta_choice_value(
            th360_get_page_cta_field($action_group, 'icono_izquierda', $post_id)
        ),
        'right_icon'  => th360_get_page_cta_choice_value(
            th360_get_page_cta_field($action_group, 'icono_derecha', $post_id)
        ),
        'icon_size'   => th360_get_page_cta_icon_size(
            th360_get_page_cta_field($action_group, 'tamano_iconos', $post_id)
        ),
    ];
}

/**
 * Resuelve la etiqueta final del boton CTA.
 */
function th360_get_page_cta_resolved_label(string $configured_label, string $target_label, string $fallback_label): string
{
    if ($configured_label !== '') {
        return $configured_label;
    }

    if ($target_label !== '') {
        return $target_label;
    }

    return $fallback_label;
}

/**
 * Intenta obtener una marca asociada a un termino o post destino.
 */
function th360_get_page_cta_brand_term($target): ?WP_Term
{
    if ($target instanceof WP_Term && $target->taxonomy === 'marca') {
        return $target;
    }

    if (!($target instanceof WP_Post)) {
        return null;
    }

    $brand_terms = get_the_terms($target, 'marca');
    if (!is_array($brand_terms) || empty($brand_terms)) {
        return null;
    }

    $brand_term = reset($brand_terms);
    return ($brand_term instanceof WP_Term) ? $brand_term : null;
}

/**
 * Traduce el choice de icono a una clave del registro centralizado.
 */
function th360_get_page_cta_icon_name(string $icon_choice, string $action, string $side): string
{
    $normalized_choice = sanitize_key($icon_choice);

    if ($normalized_choice === '' || $normalized_choice === 'ninguno' || $normalized_choice === 'none') {
        if ($side === 'left' && $action === 'copiar') {
            return 'blog_copy';
        }

        if ($side === 'left' && $action === 'compartir') {
            return 'blog_share';
        }

        return '';
    }

    switch ($normalized_choice) {
        case 'copiar':
            return 'blog_copy';

        case 'compartir':
            return 'blog_share';

        case 'atras':
            return 'back_arrow';

        case 'adelante':
            return 'foward_arrow';

        case 'nueva_pestana':
            return 'open_new';

        case 'logo_marca':
            return 'brand_logo';

        case 'escudo':
            return 'shield';

        case 'persona':
            return 'person';

        case 'email':
            return 'email_new';

        case 'casa':
            return 'home_new';

        case 'phone':
            return 'phone_new';

        case 'car':
            return 'car';

        case '360vo_new':
            return 'vo360_horizontal';

        case '360vo':
            return 'vo360';

        case 'soporte':
            return 'support';

        case 'agente':
            return 'agent';
    }

    return '';
}

/**
 * Construye el HTML visual de un CTA (icono o logo de marca).
 */
function th360_get_page_cta_visual_html(string $icon_choice, string $action, ?WP_Term $brand_term, string $side, bool $prefer_white_brand = false): string
{
    $icon_name = th360_get_page_cta_icon_name($icon_choice, $action, $side);
    if ($icon_name === '') {
        return '';
    }

    if ($icon_name === 'brand_logo') {
        if (!($brand_term instanceof WP_Term)) {
            return '';
        }

        $brand_data = th360_get_brand_visual_data($brand_term, $prefer_white_brand);
        $logo_id = (int) ($brand_data['logo_id'] ?? 0);
        if ($logo_id <= 0) {
            return '';
        }

        $shape = sanitize_html_class((string) ($brand_data['shape'] ?? 'circular'));
        $image = wp_get_attachment_image(
            $logo_id,
            'thumbnail',
            false,
            [
                'class'    => 'share__button-brand-logo',
                'alt'      => (string) ($brand_data['alt'] ?? $brand_term->name),
                'title'    => (string) ($brand_data['title'] ?? $brand_term->name),
                'decoding' => 'async',
            ]
        );

        if ($image === '') {
            return '';
        }

        return sprintf(
            '<span class="share__button-visual share__button-visual--brand share__button-visual--%1$s share__button-visual--%2$s" aria-hidden="true">%3$s</span>',
            esc_attr($side),
            esc_attr($shape),
            $image
        );
    }

    $icon_markup = E360VO_Icon::get($icon_name, [
        'class'        => 'share__button-icon',
        'aria-hidden'  => 'true',
        'focusable'    => 'false',
    ]);

    if ($icon_markup === '') {
        return '';
    }

    return sprintf(
        '<span class="share__button-visual share__button-visual--icon share__button-visual--%1$s" aria-hidden="true">%2$s</span>',
        esc_attr($side),
        $icon_markup
    );
}

/**
 * Normaliza el valor ACF de acciones (checkbox con return format "Both").
 */
function th360_normalize_page_cta_actions($value): array
{
    if (!is_array($value)) {
        return [];
    }

    $actions = [];

    foreach ($value as $item) {
        $action_value = '';
        $label = '';

        if (is_array($item)) {
            if (isset($item['value'])) {
                $action_value = (string) $item['value'];
            } elseif (isset($item['key'])) {
                $action_value = (string) $item['key'];
            } elseif (isset($item[0]) && is_scalar($item[0])) {
                $action_value = (string) $item[0];
            }

            if (isset($item['label'])) {
                $label = (string) $item['label'];
            } elseif (isset($item[1]) && is_scalar($item[1])) {
                $label = (string) $item[1];
            }
        } elseif (is_scalar($item)) {
            $action_value = (string) $item;
        }

        $action_value = sanitize_key($action_value);
        if ($action_value === '') {
            continue;
        }

        $actions[$action_value] = [
            'value' => $action_value,
            'label' => $label !== '' ? $label : th360_get_page_cta_action_default_label($action_value),
        ];
    }

    return array_values($actions);
}

/**
 * URL del archivo publico de stock.
 */
function th360_get_stock_archive_url(): string
{
    if (function_exists('gv360_get_variable')) {
        $stock_url = gv360_get_variable('stock_archive_url', '');
        if (is_scalar($stock_url) && trim((string) $stock_url) !== '') {
            return trim((string) $stock_url);
        }
    }

    $archive_url = get_post_type_archive_link('coche');
    if (is_string($archive_url) && $archive_url !== '') {
        return $archive_url;
    }

    return '';
}

/**
 * Devuelve los items CTA renderizables para paginas.
 */
function th360_get_page_cta_items(?int $post_id = null): array
{
    $post_id = $post_id ?: get_queried_object_id();
    if ($post_id <= 0) {
        return [];
    }

    $cta_group = th360_get_page_cta_group($post_id);
    $show_value = array_key_exists('mostrar_cta', $cta_group) ? $cta_group['mostrar_cta'] : null;
    if ($show_value === null && function_exists('get_field_object')) {
        $show_field = get_field_object('mostrar_cta', $post_id, false, false);
        if (is_array($show_field) && array_key_exists('value', $show_field)) {
            $show_value = $show_field['value'];
        }
    }
    $show_cta = ($show_value === null || $show_value === '') ? true : (bool) $show_value;

    if (!$show_cta) {
        return [];
    }

    $actions_value = array_key_exists('acciones', $cta_group)
        ? $cta_group['acciones']
        : (function_exists('get_field') ? get_field('acciones', $post_id) : null);

    $actions = th360_normalize_page_cta_actions($actions_value);

    if (empty($actions)) {
        $actions = [
            ['value' => 'copiar', 'label' => th360_get_page_cta_action_default_label('copiar')],
            ['value' => 'compartir', 'label' => th360_get_page_cta_action_default_label('compartir')],
        ];
    }

    $page_url = get_permalink($post_id) ?: home_url('/');
    $page_title = get_the_title($post_id) ?: get_bloginfo('name');
    $items = [];

    foreach ($actions as $action_config) {
        $action = sanitize_key((string) ($action_config['value'] ?? ''));
        if ($action === '') {
            continue;
        }

        $visual_config = th360_get_page_cta_action_config($action, $cta_group, $post_id);
        $action_group = $visual_config['group'];
        $configured_label = trim((string) ($visual_config['text'] ?? ''));
        $checkbox_label = trim((string) ($action_config['label'] ?? ''));
        $default_label = $checkbox_label !== '' ? $checkbox_label : th360_get_page_cta_action_default_label($action);
        $target_label = '';
        $brand_term = null;

        switch ($action) {
            case 'copiar':
                $items[] = [
                    'type' => 'button',
                    'action' => $action,
                    'label' => th360_get_page_cta_resolved_label($configured_label, '', $default_label),
                    'default_label' => th360_get_page_cta_resolved_label($configured_label, '', $default_label),
                    'success_label' => 'Enlace copiado',
                    'url' => $page_url,
                    'description' => $visual_config['description'],
                    'highlighted' => $visual_config['highlighted'],
                    'radius' => $visual_config['radius_css'],
                    'icon_size' => $visual_config['icon_size'],
                    'left_visual_html' => th360_get_page_cta_visual_html((string) $visual_config['left_icon'], $action, null, 'left', (bool) $visual_config['highlighted']),
                    'right_visual_html' => th360_get_page_cta_visual_html((string) $visual_config['right_icon'], $action, null, 'right', (bool) $visual_config['highlighted']),
                ];
                break;

            case 'compartir':
                $items[] = [
                    'type' => 'button',
                    'action' => $action,
                    'label' => th360_get_page_cta_resolved_label($configured_label, '', $default_label),
                    'url' => $page_url,
                    'title' => $page_title,
                    'description' => $visual_config['description'],
                    'highlighted' => $visual_config['highlighted'],
                    'radius' => $visual_config['radius_css'],
                    'icon_size' => $visual_config['icon_size'],
                    'left_visual_html' => th360_get_page_cta_visual_html((string) $visual_config['left_icon'], $action, null, 'left', (bool) $visual_config['highlighted']),
                    'right_visual_html' => th360_get_page_cta_visual_html((string) $visual_config['right_icon'], $action, null, 'right', (bool) $visual_config['highlighted']),
                ];
                break;

            case 'stock':
                $url = th360_get_stock_archive_url();
                if ($url !== '') {
                    $post_type = get_post_type_object('coche');
                    $target_label = $post_type && isset($post_type->labels->name)
                        ? (string) $post_type->labels->name
                        : '';

                    $items[] = [
                        'type' => 'link',
                        'action' => $action,
                        'label' => th360_get_page_cta_resolved_label($configured_label, $target_label, $default_label),
                        'url' => $url,
                        'description' => $visual_config['description'],
                        'highlighted' => $visual_config['highlighted'],
                        'radius' => $visual_config['radius_css'],
                        'icon_size' => $visual_config['icon_size'],
                        'left_visual_html' => th360_get_page_cta_visual_html((string) $visual_config['left_icon'], $action, null, 'left', (bool) $visual_config['highlighted']),
                        'right_visual_html' => th360_get_page_cta_visual_html((string) $visual_config['right_icon'], $action, null, 'right', (bool) $visual_config['highlighted']),
                    ];
                }
                break;

            case 'marca':
            case 'carroceria':
            case 'modelo':
                $field_name = 'taxonomy_' . $action;
                $term = th360_resolve_acf_term(th360_get_page_cta_field($action_group, $field_name, $post_id));
                if ($term instanceof WP_Term && $term->taxonomy === $action) {
                    $url = get_term_link($term);
                    if (!is_wp_error($url)) {
                        $target_label = (string) $term->name;
                        $brand_term = th360_get_page_cta_brand_term($term);
                        $items[] = [
                            'type' => 'link',
                            'action' => $action,
                            'label' => th360_get_page_cta_resolved_label($configured_label, $target_label, $default_label),
                            'url' => (string) $url,
                            'description' => $visual_config['description'],
                            'highlighted' => $visual_config['highlighted'],
                            'radius' => $visual_config['radius_css'],
                            'icon_size' => $visual_config['icon_size'],
                            'left_visual_html' => th360_get_page_cta_visual_html((string) $visual_config['left_icon'], $action, $brand_term, 'left', (bool) $visual_config['highlighted']),
                            'right_visual_html' => th360_get_page_cta_visual_html((string) $visual_config['right_icon'], $action, $brand_term, 'right', (bool) $visual_config['highlighted']),
                        ];
                    }
                }
                break;

            case 'coche':
                $vehicle = th360_resolve_acf_post(th360_get_page_cta_field($action_group, 'seleccion_vehiculo', $post_id));
                if ($vehicle instanceof WP_Post && get_post_status($vehicle) === 'publish') {
                    $url = get_permalink($vehicle);
                    if (is_string($url) && $url !== '') {
                        $target_label = (string) get_the_title($vehicle);
                        $brand_term = th360_get_page_cta_brand_term($vehicle);
                        $items[] = [
                            'type' => 'link',
                            'action' => $action,
                            'label' => th360_get_page_cta_resolved_label($configured_label, $target_label, $default_label),
                            'url' => $url,
                            'description' => $visual_config['description'],
                            'highlighted' => $visual_config['highlighted'],
                            'radius' => $visual_config['radius_css'],
                            'icon_size' => $visual_config['icon_size'],
                            'left_visual_html' => th360_get_page_cta_visual_html((string) $visual_config['left_icon'], $action, $brand_term, 'left', (bool) $visual_config['highlighted']),
                            'right_visual_html' => th360_get_page_cta_visual_html((string) $visual_config['right_icon'], $action, $brand_term, 'right', (bool) $visual_config['highlighted']),
                        ];
                    }
                }
                break;

            case 'home':
                $front_page_id = (int) get_option('page_on_front');
                $url = $front_page_id > 0 ? get_permalink($front_page_id) : home_url('/');
                if (is_string($url) && $url !== '') {
                    $target_label = $front_page_id > 0 ? (string) get_the_title($front_page_id) : '';
                    $items[] = [
                        'type' => 'link',
                        'action' => $action,
                        'label' => th360_get_page_cta_resolved_label($configured_label, $target_label, $default_label),
                        'url' => $url,
                        'description' => $visual_config['description'],
                        'highlighted' => $visual_config['highlighted'],
                        'radius' => $visual_config['radius_css'],
                        'icon_size' => $visual_config['icon_size'],
                        'left_visual_html' => th360_get_page_cta_visual_html((string) $visual_config['left_icon'], $action, null, 'left', (bool) $visual_config['highlighted']),
                        'right_visual_html' => th360_get_page_cta_visual_html((string) $visual_config['right_icon'], $action, null, 'right', (bool) $visual_config['highlighted']),
                    ];
                }
                break;

            case 'blog':
                $posts_page_id = (int) get_option('page_for_posts');
                $url = th360_get_blog_home_url();
                if ($url !== '') {
                    $target_label = $posts_page_id > 0 ? (string) get_the_title($posts_page_id) : '';
                    $items[] = [
                        'type' => 'link',
                        'action' => $action,
                        'label' => th360_get_page_cta_resolved_label($configured_label, $target_label, $default_label),
                        'url' => $url,
                        'description' => $visual_config['description'],
                        'highlighted' => $visual_config['highlighted'],
                        'radius' => $visual_config['radius_css'],
                        'icon_size' => $visual_config['icon_size'],
                        'left_visual_html' => th360_get_page_cta_visual_html((string) $visual_config['left_icon'], $action, null, 'left', (bool) $visual_config['highlighted']),
                        'right_visual_html' => th360_get_page_cta_visual_html((string) $visual_config['right_icon'], $action, null, 'right', (bool) $visual_config['highlighted']),
                    ];
                }
                break;

            case 'post':
                $selected_post = th360_resolve_acf_post(th360_get_page_cta_field($action_group, 'seleccion_entrada', $post_id));
                if ($selected_post instanceof WP_Post && get_post_status($selected_post) === 'publish') {
                    $url = get_permalink($selected_post);
                    if (is_string($url) && $url !== '') {
                        $target_label = (string) get_the_title($selected_post);
                        $items[] = [
                            'type' => 'link',
                            'action' => $action,
                            'label' => th360_get_page_cta_resolved_label($configured_label, $target_label, $default_label),
                            'url' => $url,
                            'description' => $visual_config['description'],
                            'highlighted' => $visual_config['highlighted'],
                            'radius' => $visual_config['radius_css'],
                            'icon_size' => $visual_config['icon_size'],
                            'left_visual_html' => th360_get_page_cta_visual_html((string) $visual_config['left_icon'], $action, null, 'left', (bool) $visual_config['highlighted']),
                            'right_visual_html' => th360_get_page_cta_visual_html((string) $visual_config['right_icon'], $action, null, 'right', (bool) $visual_config['highlighted']),
                        ];
                    }
                }
                break;

            case 'pagina':
                $selected_page = th360_resolve_acf_post(th360_get_page_cta_field($action_group, 'seleccion_pagina', $post_id));
                if ($selected_page instanceof WP_Post && get_post_status($selected_page) === 'publish') {
                    $url = get_permalink($selected_page);
                    if (is_string($url) && $url !== '') {
                        $target_label = (string) get_the_title($selected_page);
                        $items[] = [
                            'type' => 'link',
                            'action' => $action,
                            'label' => th360_get_page_cta_resolved_label($configured_label, $target_label, $default_label),
                            'url' => $url,
                            'description' => $visual_config['description'],
                            'highlighted' => $visual_config['highlighted'],
                            'radius' => $visual_config['radius_css'],
                            'icon_size' => $visual_config['icon_size'],
                            'left_visual_html' => th360_get_page_cta_visual_html((string) $visual_config['left_icon'], $action, null, 'left', (bool) $visual_config['highlighted']),
                            'right_visual_html' => th360_get_page_cta_visual_html((string) $visual_config['right_icon'], $action, null, 'right', (bool) $visual_config['highlighted']),
                        ];
                    }
                }
                break;

            case 'categoria':
                $term = th360_resolve_acf_term(th360_get_page_cta_field($action_group, 'elegir_categoria', $post_id));
                if ($term instanceof WP_Term && $term->taxonomy === 'category') {
                    $url = get_term_link($term);
                    if (!is_wp_error($url)) {
                        $target_label = (string) $term->name;
                        $items[] = [
                            'type' => 'link',
                            'action' => $action,
                            'label' => th360_get_page_cta_resolved_label($configured_label, $target_label, $default_label),
                            'url' => (string) $url,
                            'description' => $visual_config['description'],
                            'highlighted' => $visual_config['highlighted'],
                            'radius' => $visual_config['radius_css'],
                            'icon_size' => $visual_config['icon_size'],
                            'left_visual_html' => th360_get_page_cta_visual_html((string) $visual_config['left_icon'], $action, null, 'left', (bool) $visual_config['highlighted']),
                            'right_visual_html' => th360_get_page_cta_visual_html((string) $visual_config['right_icon'], $action, null, 'right', (bool) $visual_config['highlighted']),
                        ];
                    }
                }
                break;

            case 'externo':
                $url = trim((string) th360_get_page_cta_field($action_group, 'enlace_externo', $post_id));
                $url = $url !== '' ? esc_url_raw($url) : '';
                if ($url !== '') {
                    $host = wp_parse_url($url, PHP_URL_HOST);
                    $target_label = is_string($host) ? $host : '';
                    $items[] = [
                        'type' => 'link',
                        'action' => $action,
                        'label' => th360_get_page_cta_resolved_label($configured_label, $target_label, $default_label),
                        'url' => $url,
                        'external' => true,
                        'description' => $visual_config['description'],
                        'highlighted' => $visual_config['highlighted'],
                        'radius' => $visual_config['radius_css'],
                        'icon_size' => $visual_config['icon_size'],
                        'left_visual_html' => th360_get_page_cta_visual_html((string) $visual_config['left_icon'], $action, null, 'left', (bool) $visual_config['highlighted']),
                        'right_visual_html' => th360_get_page_cta_visual_html((string) $visual_config['right_icon'], $action, null, 'right', (bool) $visual_config['highlighted']),
                    ];
                }
                break;
        }
    }

    return $items;
}

/**
 * Render compartido de CTA para paginas.
 */
function th360_render_page_cta(?int $post_id = null): void
{
    $items = th360_get_page_cta_items($post_id);
    if (empty($items)) {
        return;
    }
?>
    <div class="share" data-page-cta>
        <?php foreach ($items as $item) : ?>
            <?php
            $button_classes = [
                'share__button',
                'share__button--' . sanitize_html_class((string) ($item['action'] ?? 'default')),
                !empty($item['highlighted']) ? 'share__button--highlighted' : 'share__button--neutral',
                !empty($item['description']) ? 'share__button--with-description' : '',
                'share__button--icon-' . sanitize_html_class((string) ($item['icon_size'] ?? 'medium')),
            ];
            $button_classes = array_values(array_filter($button_classes));
            $button_style = '--share-button-radius:' . esc_attr((string) ($item['radius'] ?? '999px')) . ';';
            ?>
            <?php if (($item['type'] ?? '') === 'button') : ?>
                <button
                    type="button"
                    class="<?php echo esc_attr(implode(' ', $button_classes)); ?>"
                    style="<?php echo esc_attr($button_style); ?>"
                    <?php if (($item['action'] ?? '') === 'copiar') : ?>
                        data-copy-link="<?php echo esc_url($item['url']); ?>"
                        data-default-label="<?php echo esc_attr($item['default_label'] ?? $item['label']); ?>"
                        data-copy-success-label="<?php echo esc_attr($item['success_label'] ?? 'Enlace copiado'); ?>"
                    <?php elseif (($item['action'] ?? '') === 'compartir') : ?>
                        data-share-link="<?php echo esc_url($item['url']); ?>"
                        data-share-title="<?php echo esc_attr($item['title'] ?? get_bloginfo('name')); ?>"
                    <?php endif; ?>>
                    <span class="share__button-main">
                        <?php echo (string) ($item['left_visual_html'] ?? ''); ?>
                        <span class="share__button-copy">
                            <span class="share__button-text"><?php echo esc_html((string) $item['label']); ?></span>
                            <?php if (!empty($item['description'])) : ?>
                                <span class="share__button-description"><?php echo esc_html((string) $item['description']); ?></span>
                            <?php endif; ?>
                        </span>
                        <?php echo (string) ($item['right_visual_html'] ?? ''); ?>
                    </span>
                </button>
            <?php else : ?>
                <a
                    class="<?php echo esc_attr(implode(' ', array_merge($button_classes, ['share__button--link']))); ?>"
                    href="<?php echo esc_url((string) ($item['url'] ?? '')); ?>"
                    style="<?php echo esc_attr($button_style); ?>"
                    <?php if (!empty($item['external'])) : ?>target="_blank" rel="noopener noreferrer"<?php endif; ?>>
                    <span class="share__button-main">
                        <?php echo (string) ($item['left_visual_html'] ?? ''); ?>
                        <span class="share__button-copy">
                            <span class="share__button-text"><?php echo esc_html((string) ($item['label'] ?? 'Enlace')); ?></span>
                            <?php if (!empty($item['description'])) : ?>
                                <span class="share__button-description"><?php echo esc_html((string) $item['description']); ?></span>
                            <?php endif; ?>
                        </span>
                        <?php echo (string) ($item['right_visual_html'] ?? ''); ?>
                    </span>
                </a>
            <?php endif; ?>
        <?php endforeach; ?>
    </div>
<?php
}

/**
 * Renderiza el boton de scroll del hero de pagina.
 */
function th360_render_page_scroll_button(?int $post_id = null): void
{
    $post_id = $post_id ?: get_queried_object_id();
    if ($post_id <= 0) {
        return;
    }

    $header_settings = th360_get_page_header_settings($post_id);
    if (!$header_settings['full_screen']) {
        return;
    }
?>
    <div class="button_scroll_home button_scroll--page">
        <button
            id="scrollButtonPage"
            class="scroll-button-page"
            type="button"
            data-scroll-target="#entry-content--start"
            aria-label="<?php echo esc_attr__('Ir al contenido', '360vo-theme'); ?>">
            <span class="scroll-button-page__label"><?php echo esc_html__('Ver contenido', '360vo-theme'); ?></span>
            <span class="scroll-button-page__icon" aria-hidden="true">
                <?php echo E360VO_Icon::get('scroll_up', [
                    'class' => 'scroll-button-page__icon-svg',
                    'aria-hidden' => 'true',
                    'focusable' => 'false',
                ]); ?>
            </span>
        </button>
    </div>
<?php
}

/**
 * Devuelve la categoría editorial principal del post.
 */
function th360_get_post_primary_category(int $post_id): ?WP_Term
{
    if (function_exists('get_field')) {
        $selected_category = th360_resolve_acf_term(get_field('seleccione_categoria', $post_id));
        if ($selected_category instanceof WP_Term && $selected_category->taxonomy === 'category') {
            return $selected_category;
        }
    }

    $categories = get_the_category($post_id);
    if (is_array($categories) && !empty($categories)) {
        $category = reset($categories);
        if ($category instanceof WP_Term) {
            return $category;
        }
    }

    return null;
}

/**
 * Devuelve la marca editorial principal del post.
 */
function th360_get_post_brand_term(int $post_id): ?WP_Term
{
    if (function_exists('get_field')) {
        $selected_brand = th360_resolve_acf_term(get_field('seleccione_marca', $post_id));
        if ($selected_brand instanceof WP_Term && $selected_brand->taxonomy === 'marca') {
            return $selected_brand;
        }
    }

    $brand_terms = get_the_terms($post_id, 'marca');
    if (is_array($brand_terms) && !empty($brand_terms)) {
        $brand = reset($brand_terms);
        if ($brand instanceof WP_Term) {
            return $brand;
        }
    }

    return null;
}

/**
 * Devuelve el complemento editorial del stock.
 */
function th360_get_stock_complement(): string
{
    if (function_exists('gv360_get_variable')) {
        $value = gv360_get_variable('stock_complement', '');
        if (is_scalar($value) && trim((string) $value) !== '') {
            return trim((string) $value);
        }
    }

    if (function_exists('get_field')) {
        foreach (['complemento_nombre', 'complemento-nombre'] as $field_name) {
            $value = get_field($field_name, 'option');
            if (is_scalar($value) && trim((string) $value) !== '') {
                return trim((string) $value);
            }
        }
    }

    return '';
}

/**
 * Construye la URL del archivo de blog por marca.
 */
function th360_get_blog_brand_archive_url($brand): string
{
    $brand_term = $brand instanceof WP_Term ? $brand : th360_resolve_acf_term($brand);
    if (!$brand_term instanceof WP_Term || $brand_term->taxonomy !== 'marca') {
        return '';
    }

    $blog_slug = class_exists('E360VO_ThemeSetup')
        ? (string) E360VO_ThemeSetup::get_blog_slug()
        : 'noticias';

    return home_url('/' . trim($blog_slug, '/') . '/marca/' . $brand_term->slug . '/');
}

/**
 * Devuelve la URL canónica pública de una marca.
 */
function th360_get_brand_term_url($brand): string
{
    $brand_term = $brand instanceof WP_Term ? $brand : th360_resolve_acf_term($brand);
    if (!$brand_term instanceof WP_Term || $brand_term->taxonomy !== 'marca') {
        return '';
    }

    $url = get_term_link($brand_term, 'marca');
    return is_wp_error($url) ? '' : (string) $url;
}

/**
 * Meta query tolerante con formatos legacy/ACF para la marca del post.
 */
function th360_get_blog_brand_meta_query($brand): array
{
    $brand_term = $brand instanceof WP_Term ? $brand : th360_resolve_acf_term($brand);
    if (!$brand_term instanceof WP_Term) {
        return [];
    }

    $brand_id = (string) $brand_term->term_id;

    return [
        'relation' => 'OR',
        [
            'key'     => 'seleccione_marca',
            'value'   => $brand_id,
            'compare' => '=',
        ],
        [
            'key'     => 'seleccione_marca',
            'value'   => '"' . $brand_id . '"',
            'compare' => 'LIKE',
        ],
    ];
}

/**
 * Devuelve la marca del archivo de blog por marca actual.
 */
function th360_get_current_blog_brand_term(): ?WP_Term
{
    $brand_slug = get_query_var('th360_post_brand');
    if (!is_scalar($brand_slug) || trim((string) $brand_slug) === '') {
        return null;
    }

    $brand = get_term_by('slug', sanitize_title((string) $brand_slug), 'marca');
    return ($brand instanceof WP_Term && !is_wp_error($brand)) ? $brand : null;
}

/**
 * Indica si estamos en un archivo editorial de marca del blog.
 */
function th360_is_blog_brand_archive(): bool
{
    return th360_get_current_blog_brand_term() instanceof WP_Term;
}

/**
 * Lee un valor ACF de un termino usando varios contextos compatibles.
 */
function th360_get_term_acf_value(string $field_name, ?WP_Term $term)
{
    if (!$term instanceof WP_Term || !function_exists('get_field')) {
        return null;
    }

    $contexts = [
        $term,
        'term_' . $term->term_id,
        $term->taxonomy . '_' . $term->term_id,
    ];

    foreach ($contexts as $context) {
        $value = get_field($field_name, $context);

        if (is_string($value) && trim($value) !== '') {
            return $value;
        }

        if (!is_string($value) && !empty($value)) {
            return $value;
        }
    }

    return null;
}

/**
 * Devuelve el contenido editorial del archivo de blog de una marca.
 */
function th360_get_brand_blog_archive_content(?WP_Term $brand_term): array
{
    $brand_name = $brand_term instanceof WP_Term ? (string) $brand_term->name : '';
    $site_name  = trim((string) get_bloginfo('name'));

    $custom_h1 = th360_get_term_acf_value('h1', $brand_term);
    $custom_intro = th360_get_term_acf_value('parrafo_intro', $brand_term);

    $default_intro = '';
    if ($brand_name !== '') {
        $default_intro = sprintf(
            'Todas las novedades sobre %s en %s.',
            $brand_name,
            $site_name !== '' ? $site_name : 'el sitio'
        );
    }

    return [
        'h1'    => is_string($custom_h1) && trim($custom_h1) !== '' ? trim($custom_h1) : $brand_name,
        'intro' => is_string($custom_intro) && trim($custom_intro) !== '' ? (string) $custom_intro : $default_intro,
    ];
}

/**
 * Normaliza distintos formatos de imagen a attachment ID.
 */
function th360_resolve_attachment_id($value): int
{
    if (is_numeric($value)) {
        return max(0, (int) $value);
    }

    if (is_array($value)) {
        foreach (['ID', 'id'] as $key) {
            if (isset($value[$key]) && is_numeric($value[$key])) {
                return max(0, (int) $value[$key]);
            }
        }

        if (!empty($value['url']) && is_string($value['url'])) {
            return max(0, (int) attachment_url_to_postid($value['url']));
        }
    }

    if (is_string($value) && filter_var($value, FILTER_VALIDATE_URL)) {
        return max(0, (int) attachment_url_to_postid($value));
    }

    return 0;
}

/**
 * Devuelve los datos visuales de una marca para el front del blog.
 */
function th360_get_brand_visual_data(?WP_Term $brand_term, bool $prefer_white = false): array
{
    $data = [
        'logo_id' => 0,
        'shape'   => 'circular',
        'alt'     => '',
        'title'   => '',
    ];

    if (!$brand_term instanceof WP_Term) {
        return $data;
    }

    $contexts = [
        $brand_term,
        'term_' . $brand_term->term_id,
        $brand_term->taxonomy . '_' . $brand_term->term_id,
    ];

    $shape = '';
    if (function_exists('get_field')) {
        foreach ($contexts as $context) {
            $maybe_shape = get_field('forma_del_logo', $context);
            if (is_scalar($maybe_shape) && trim((string) $maybe_shape) !== '') {
                $shape = trim((string) $maybe_shape);
                break;
            }
        }
    }

    if ($shape === '') {
        $shape = (string) get_term_meta($brand_term->term_id, 'forma_del_logo', true);
    }

    $allowed_shapes = ['circular', 'horizontal', 'horizontal_corto', 'horizontal_largo', 'vertical'];
    if (!in_array($shape, $allowed_shapes, true)) {
        $shape = 'circular';
    }

    $logo_id = 0;
    $logo_fields = $prefer_white
        ? ['logo_marca_white', 'logo_marca_png', 'logo_marca', 'imagen_marca']
        : ['logo_marca', 'logo_marca_png', 'logo_marca_white', 'imagen_marca'];

    if (function_exists('get_field')) {
        foreach ($logo_fields as $field_name) {
            foreach ($contexts as $context) {
                $candidate = get_field($field_name, $context);
                $logo_id = th360_resolve_attachment_id($candidate);
                if ($logo_id > 0) {
                    break 2;
                }
            }
        }
    }

    if ($logo_id <= 0) {
        foreach ($logo_fields as $field_name) {
            $candidate = get_term_meta($brand_term->term_id, $field_name, true);
            $logo_id = th360_resolve_attachment_id($candidate);
            if ($logo_id > 0) {
                break;
            }
        }
    }

    $stock_complement = th360_get_stock_complement();
    $alt = trim(implode(' ', array_filter([(string) $brand_term->name, $stock_complement])));

    $data['logo_id'] = $logo_id;
    $data['shape']   = $shape;
    $data['alt']     = $alt !== '' ? $alt : (string) $brand_term->name;
    $data['title']   = (string) $brand_term->name;

    return $data;
}

/**
 * Devuelve el contexto editorial compartido de un post del blog.
 */
function th360_get_post_blog_context(int $post_id): array
{
    $category = th360_get_post_primary_category($post_id);
    $brand    = th360_get_post_brand_term($post_id);
    $cat_url  = $category instanceof WP_Term ? get_category_link($category->term_id) : '';

    if (is_wp_error($cat_url)) {
        $cat_url = '';
    }

    return [
        'is_brand_mode' => th360_is_brand_mode_post($post_id),
        'category'      => $category,
        'category_name' => $category instanceof WP_Term ? (string) $category->name : '',
        'category_url'  => (string) $cat_url,
        'brand'         => $brand,
        'brand_name'    => $brand instanceof WP_Term ? (string) $brand->name : '',
        'brand_url'     => th360_get_blog_brand_archive_url($brand),
    ];
}

/**
 * Detecta si una entrada usa el modo especial "marca".
 */
function th360_is_brand_mode_post(int $post_id): bool
{
    if (!function_exists('get_field')) {
        return false;
    }

    $mode = get_field('tipo_de_categoria', $post_id);

    if (!is_scalar($mode) || trim((string) $mode) === '') {
        $mode = get_field('seleccione_categoria', $post_id);
    }

    if (!is_scalar($mode)) {
        return false;
    }

    $normalized_mode = sanitize_title((string) $mode);
    return str_contains($normalized_mode, 'marca');
}

/**
 * Indica si debe mostrarse la selección automática de vehículos de marca.
 */
function th360_should_show_brand_vehicles(int $post_id): bool
{
    if (!th360_is_brand_mode_post($post_id)) {
        return false;
    }

    if (!(th360_get_post_brand_term($post_id) instanceof WP_Term)) {
        return false;
    }

    if (!function_exists('get_field')) {
        return false;
    }

    $group_value = get_field('control_marca', $post_id);
    if (is_array($group_value) && array_key_exists('mostrar_vehiculos_marca', $group_value)) {
        return !empty($group_value['mostrar_vehiculos_marca']);
    }

    foreach (['control_marca_mostrar_vehiculos_marca', 'mostrar_vehiculos_marca'] as $field_name) {
        $field_value = get_field($field_name, $post_id);
        if ($field_value !== null && $field_value !== '') {
            return !empty($field_value);
        }
    }

    return false;
}

/**
 * Encola los estilos mínimos del plugin para las tarjetas de vehículo.
 */
function th360_enqueue_brand_vehicle_assets(): void
{
    if (
        !defined('GV360_PLUGIN_URL')
        || !defined('GV360_PLUGIN_DIR')
        || !file_exists(GV360_PLUGIN_DIR . 'public/assets/css/gv360_seleccion_coches_styles.css')
    ) {
        return;
    }

    wp_enqueue_style(
        'th360-brand-vehicle-cards',
        GV360_PLUGIN_URL . 'public/assets/css/gv360_seleccion_coches_styles.css',
        [],
        (string) filemtime(GV360_PLUGIN_DIR . 'public/assets/css/gv360_seleccion_coches_styles.css')
    );
}

add_action('wp_enqueue_scripts', function (): void {
    if (!is_singular('post')) {
        return;
    }

    $post_id = (int) get_queried_object_id();
    if ($post_id <= 0 || !th360_should_show_brand_vehicles($post_id)) {
        return;
    }

    th360_enqueue_brand_vehicle_assets();
}, 30);

/**
 * Construye la query base de coches para una marca concreta.
 */
function th360_get_brand_vehicle_query_args(WP_Term $brand_term, array $args = []): array
{
    $defaults = [
        'post_type'           => 'coche',
        'post_status'         => 'publish',
        'posts_per_page'      => max(1, (int) apply_filters('th360_brand_vehicle_posts_per_page', 3, $brand_term)),
        'no_found_rows'       => true,
        'ignore_sticky_posts' => true,
        'orderby'             => 'date',
        'order'               => 'DESC',
        'tax_query'           => [
            [
                'taxonomy' => 'marca',
                'field'    => 'term_id',
                'terms'    => [(int) $brand_term->term_id],
            ],
        ],
    ];

    return wp_parse_args($args, $defaults);
}

/**
 * Renderiza una selección automática de vehículos de la marca del post.
 */
function th360_normalize_vehicle_card_icons(string $html): string
{
    if ($html === '' || !str_contains($html, 'icon-photo_camera')) {
        return $html;
    }

    $camera_icon = class_exists('E360VO_Icon')
        ? E360VO_Icon::get('camera', [
            'class'       => 'vehicle-card__overlay-icon',
            'aria-hidden' => 'true',
        ])
        : '';

    if ($camera_icon === '') {
        return $html;
    }

    return str_replace('<i class="icon-photo_camera"></i>', $camera_icon, $html);
}

add_filter('render_block', function (string $block_content, array $block): string {
    if ($block_content === '') {
        return $block_content;
    }

    $block_name = (string) ($block['blockName'] ?? '');
    if (!in_array($block_name, ['acf/seleccion-coches', 'acf/catalogo-coches'], true)) {
        return $block_content;
    }

    return th360_normalize_vehicle_card_icons($block_content);
}, 10, 2);

function th360_render_brand_vehicle_section(int $post_id, array $args = []): void
{
    if (!th360_should_show_brand_vehicles($post_id)) {
        return;
    }

    if (!defined('GV360_PLUGIN_DIR') || !defined('GV360_PLUGIN_URL')) {
        return;
    }

    $brand_term = th360_get_post_brand_term($post_id);
    if (!$brand_term instanceof WP_Term) {
        return;
    }

    $template_path = GV360_PLUGIN_DIR . 'includes/blocks/templates/template-part-coche.php';
    if (!file_exists($template_path)) {
        return;
    }

    $query = new WP_Query(th360_get_brand_vehicle_query_args($brand_term, $args));
    if (!$query->have_posts()) {
        wp_reset_postdata();
        return;
    }

    $brand_name = (string) $brand_term->name;
    $brand_term_url = th360_get_brand_term_url($brand_term);
    $section_title = sprintf('Vehículos %s disponibles', $brand_name);
    $section_text = sprintf('Una selección de unidades %s que ya puedes consultar dentro del stock actual.', $brand_name);
?>
    <section class="single-brand-vehicles" aria-label="<?php echo esc_attr($section_title); ?>">
        <div class="single-brand-vehicles__inner">
            <div class="single-brand-vehicles__header">
                <div class="single-brand-vehicles__copy">
                    <h2 class="single-brand-vehicles__title"><?php echo esc_html($section_title); ?></h2>
                    <p class="single-brand-vehicles__text"><?php echo esc_html($section_text); ?></p>
                </div>

                <?php if ($brand_term_url !== '') : ?>
                    <a class="single-brand-vehicles__link" href="<?php echo esc_url($brand_term_url); ?>">
                        <?php echo esc_html(sprintf('Ver nuestros %s', $brand_name)); ?>
                    </a>
                <?php endif; ?>
            </div>

            <div class="vehicle-card__container--global single-brand-vehicles__listing">
                <div class="vehicle-card__container single-brand-vehicles__grid">
                    <?php
                    while ($query->have_posts()) :
                        $query->the_post();
                        ob_start();
                        include $template_path;
                        echo th360_normalize_vehicle_card_icons((string) ob_get_clean());
                    endwhile;
                    ?>
                </div>
            </div>
        </div>
    </section>
<?php
    wp_reset_postdata();
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
        '360vo-header-context',
        '360vo-scroll-top',
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
