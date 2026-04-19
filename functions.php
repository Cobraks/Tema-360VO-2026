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
define('THEME_VERSION', '3.2.14');
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
