<?php

/**
 * Header template
 * 360vo-theme
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Context helper seguro:
 * - Si existe plugin (helper global), usarlo.
 * - Si no, fallback a get_field('option') para no romper staging.
 */
if (!function_exists('theme360_ctx_get')) {
    function theme360_ctx_get($key, $default = '')
    {
        if (function_exists('gv360_get_variable')) {
            return gv360_get_variable($key, $default);
        }

        if (function_exists('get_field')) {
            $map = array(
                'email'         => 'correo_y_telefono_correo_principal',
                'phone_primary' => 'correo_y_telefono_telefono_principal',
                'phone_display' => 'correo_y_telefono_telefono_principal',
                'instagram_url' => 'social_insta',
                'facebook_url'  => 'social_face',
                'twitter_url'   => 'social_twitter',
                'youtube_url'   => 'social_youtube',
                'tiktok_url'    => 'social_tiktok',
                'show_address'  => 'direccion_del_concesionario_mostrar_direccion',
                'direccion'     => 'direccion_del_concesionario_direccion',
                'postal_code'   => 'direccion_del_concesionario_codigo_postal',
                'localidad'     => 'direccion_del_concesionario_localidad',
                'provincia'     => 'direccion_del_concesionario_provincia',
                'maps_name'     => 'direccion_del_concesionario_nombre_en_maps',
            );

            if (isset($map[$key])) {
                $v = get_field($map[$key], 'option');
                if (is_scalar($v) && trim((string) $v) !== '') {
                    $v = trim((string) $v);

                    // Normalización ligera en fallback (usuario -> URL)
                    if (in_array($key, array('instagram_url', 'facebook_url', 'twitter_url', 'youtube_url', 'tiktok_url'), true)) {
                        if (!preg_match('#^https?://#i', $v)) {
                            $base = array(
                                'instagram_url' => 'https://www.instagram.com/%s',
                                'facebook_url'  => 'https://www.facebook.com/%s',
                                'twitter_url'   => 'https://www.twitter.com/%s',
                                'youtube_url'   => 'https://www.youtube.com/%s',
                                'tiktok_url'    => 'https://www.tiktok.com/%s',
                            );
                            $v = sprintf($base[$key], ltrim($v, '@/'));
                        }
                    }

                    return $v;
                }
            }
        }

        return $default;
    }
}

/**
 * Repeater social legacy helper
 */
if (!function_exists('theme360_social_repeater_name')) {
    function theme360_social_repeater_name()
    {
        if (function_exists('have_rows')) {
            if (have_rows('social_add_social', 'option')) {
                return 'social_add_social';
            }
            if (have_rows('add_social', 'option')) {
                return 'add_social';
            }
        }
        return '';
    }
}

if (!function_exists('theme360_header_schedule_lines')) {
    function theme360_header_schedule_lines(array $horario): array
    {
        if (empty($horario)) {
            return array();
        }

        $conf_horario = isset($horario['conf_horario']) && is_array($horario['conf_horario']) ? $horario['conf_horario'] : array();
        if (!empty($conf_horario['horario_alt'])) {
            return array();
        }

        $format_range = static function (array $data, string $start_key, string $end_key, string $split_key = '', string $start_pm_key = '', string $end_pm_key = ''): string {
            $start = isset($data[$start_key]) ? trim((string) $data[$start_key]) : '';
            $end = isset($data[$end_key]) ? trim((string) $data[$end_key]) : '';
            if ($start === '' || $end === '') {
                return '';
            }

            $range = $start . ' - ' . $end;
            if ($split_key !== '' && !empty($data[$split_key])) {
                $start_pm = isset($data[$start_pm_key]) ? trim((string) $data[$start_pm_key]) : '';
                $end_pm = isset($data[$end_pm_key]) ? trim((string) $data[$end_pm_key]) : '';
                if ($start_pm !== '' && $end_pm !== '') {
                    $range .= ' y ' . $start_pm . ' - ' . $end_pm;
                }
            }

            return $range;
        };

        $weekdays = isset($horario['lunes_a_viernes']) && is_array($horario['lunes_a_viernes']) ? $horario['lunes_a_viernes'] : array();
        $saturday = isset($horario['horario_sabado']) && is_array($horario['horario_sabado']) ? $horario['horario_sabado'] : array();

        $lines = array();
        $weekday_range = $format_range($weekdays, 'entrada_lun_vier', 'salida_lun_vier', 'horario_tarde', 'entrada_lun_vier_tarde', 'salida_lun_vier_tarde');
        if ($weekday_range !== '') {
            $lines[] = sprintf(__('Lunes a viernes: %s', '360vo-theme'), $weekday_range);
        }

        if (!empty($saturday['abierto_sabados'])) {
            $saturday_range = $format_range($saturday, 'entrada_sab', 'salida_sab', 'horario_tarde_sab', 'entrada_sab_tarde', 'salida_sab_tarde');
            if ($saturday_range !== '') {
                $lines[] = sprintf(__('Sábado: %s', '360vo-theme'), $saturday_range);
            }
        }

        return $lines;
    }
}

$email_principal    = trim((string) theme360_ctx_get('email', ''));
$telefono_principal = trim((string) theme360_ctx_get('phone_primary', ''));
$telefono_e164      = trim((string) theme360_ctx_get('phone_e164', ''));
$telefono_display   = trim((string) theme360_ctx_get('phone_display', $telefono_principal));
$telefono_href      = $telefono_principal !== '' ? preg_replace('/\s+/', '', ($telefono_e164 !== '' ? $telefono_e164 : $telefono_principal)) : '';
$whatsapp_href      = $telefono_principal !== '' ? 'https://wa.me/' . preg_replace('/\D+/', '', ltrim($telefono_e164 !== '' ? $telefono_e164 : $telefono_principal, '+')) . '?text=' . rawurlencode('Estoy buscando un coche en ' . get_bloginfo('name')) : '';

$mostrar_direccion = in_array(strtolower((string) theme360_ctx_get('show_address', '1')), array('1', 'true', 'yes', 'on'), true);
$direccion         = trim((string) theme360_ctx_get('direccion', ''));
$codigo_postal     = trim((string) theme360_ctx_get('postal_code', ''));
$localidad         = trim((string) theme360_ctx_get('localidad', ''));
$provincia         = trim((string) theme360_ctx_get('provincia', ''));
$nombre_en_maps    = trim((string) theme360_ctx_get('maps_name', ''));
$direccion_visible = $mostrar_direccion ? implode(', ', array_filter(array($direccion, trim($codigo_postal . ' ' . $localidad), $provincia))) : '';
$direccion_maps    = implode(', ', array_filter(array($nombre_en_maps, $direccion, $codigo_postal, $localidad, $provincia)));

$horario = array();
$schedule_json = (string) theme360_ctx_get('schedule_json', '');
if ($schedule_json !== '') {
    $decoded_schedule = json_decode($schedule_json, true);
    if (is_array($decoded_schedule)) {
        $horario = $decoded_schedule;
    }
}
if (empty($horario) && function_exists('get_field')) {
    $raw_horario = get_field('horario', 'option');
    if (is_array($raw_horario)) {
        $horario = $raw_horario;
    }
}
$header_schedule_lines = theme360_header_schedule_lines($horario);
$has_header_contact_panel = ($email_principal !== '' || $telefono_principal !== '' || $direccion_visible !== '' || !empty($header_schedule_lines));

$insta_url   = trim((string) theme360_ctx_get('instagram_url', ''));
$face_url    = trim((string) theme360_ctx_get('facebook_url', ''));
$twitter_url = trim((string) theme360_ctx_get('twitter_url', ''));
$youtube_url = trim((string) theme360_ctx_get('youtube_url', ''));
$tiktok_url  = trim((string) theme360_ctx_get('tiktok_url', ''));

$social_repeater = theme360_social_repeater_name();

$has_social = (
    $insta_url !== '' ||
    $face_url !== '' ||
    $twitter_url !== '' ||
    $youtube_url !== '' ||
    $tiktok_url !== '' ||
    ($social_repeater !== '')
);

// Lógica logo/header
$custom_logo_id    = (int) get_theme_mod('custom_logo');
$logo              = $custom_logo_id ? wp_get_attachment_image_src($custom_logo_id, 'full') : false;
$logo_shape        = (string) get_theme_mod('th360_logo_shape', 'square');
$header_logo_class = ' site-header--logo-' . sanitize_html_class($logo_shape);

// Theme color dinámico (seguro)
$color_scheme = (string) get_theme_mod('th360_color_scheme', 'azul');
$theme_color_map = [
    'azul'        => '#fafafa',
    'rojo'        => '#d13b3b',
    'verde'       => '#258a57',
    'morado'      => '#7d43b6',
    'naranja'     => '#e57a1f',
    'cian'        => '#006579',
    'lima'        => '#7aa62e',
    'amarillo'    => '#ffd709',
    'personalizado' => sanitize_hex_color((string) get_theme_mod('th360_custom_color', '')),
];

$theme_color = $theme_color_map[$color_scheme] ?? '#006579';
if (!$theme_color) {
    $theme_color = '#006579';
}

$site_name = (string) get_bloginfo('name');
$header_context = array(
    'title'      => '',
    'subtitle'   => '',
    'logo_id'    => 0,
    'logo_url'   => '',
    'logo_alt'   => '',
    'logo_shape' => '',
    'variant'    => '',
);

if (is_page() || is_singular('post')) {
    $header_context['title'] = trim(wp_strip_all_tags(get_the_title()));
    if ($header_context['title'] === '') {
        $header_context['title'] = $site_name;
    }
} elseif (is_singular('coche')) {
    $post_id = get_queried_object_id();
    $brand_name = '';
    $model_name = '';
    $version_name = function_exists('get_field') ? trim((string) get_field('datos_generales_version', $post_id)) : '';
    $brand_logo = null;

    $brands = get_the_terms($post_id, 'marca');
    if ($brands && !is_wp_error($brands)) {
        $brand = reset($brands);
        if ($brand instanceof WP_Term) {
            $brand_name = trim((string) $brand->name);

            if (function_exists('get_field')) {
                $brand_logo = get_field('logo_marca', $brand);
                $header_context['logo_shape'] = sanitize_html_class((string) get_field('forma_del_logo', $brand));
            }
        }
    }

    $models = get_the_terms($post_id, 'modelo');
    if ($models && !is_wp_error($models)) {
        $model = reset($models);
        if ($model instanceof WP_Term) {
            $model_name = trim((string) $model->name);
        }
    }

    $vehicle_title = trim($brand_name . ' ' . $model_name);
    $header_context['title'] = $vehicle_title !== '' ? $vehicle_title : trim(wp_strip_all_tags(get_the_title($post_id)));
    $header_context['subtitle'] = $version_name;
    $header_context['logo_alt'] = $brand_name !== '' ? $brand_name : $header_context['title'];
    $header_context['variant'] = 'vehicle';

    if (is_numeric($brand_logo)) {
        $header_context['logo_id'] = (int) $brand_logo;
    } elseif (is_array($brand_logo)) {
        if (!empty($brand_logo['ID'])) {
            $header_context['logo_id'] = (int) $brand_logo['ID'];
        } elseif (!empty($brand_logo['id'])) {
            $header_context['logo_id'] = (int) $brand_logo['id'];
        } elseif (!empty($brand_logo['url'])) {
            $header_context['logo_url'] = (string) $brand_logo['url'];
        }
    } elseif (is_string($brand_logo) && trim($brand_logo) !== '') {
        $header_context['logo_url'] = trim($brand_logo);
    }
}

$header_context = apply_filters('th360_header_context', $header_context, get_queried_object_id());

/**
 * ---------------------------------------------------------
 * Logo SVG inline desde ACF Options (logo_svg)
 * Prioridad: custom_logo (WP) > logo_svg (ACF) > texto
 * ---------------------------------------------------------
 */
if (!function_exists('theme360_get_logo_svg_option')) {
    function theme360_get_logo_svg_option(): string
    {
        $raw = '';

        // Prefer plugin variable system if available
        if (function_exists('gv360_get_variable')) {
            $raw = (string) gv360_get_variable('logo_svg', '');
            $raw = trim($raw);
            if ($raw !== '') return $raw;
        }

        if (function_exists('get_field')) {
            // 1) Campo directo en options
            $raw = (string) get_field('logo_svg', 'option');
            $raw = trim($raw);
            if ($raw !== '') return $raw;

            // 2) Si estuviera dentro de un group "personalizacion"
            $grp = get_field('personalizacion', 'option');
            if (is_array($grp) && !empty($grp['logo_svg']) && is_string($grp['logo_svg'])) {
                $raw = trim($grp['logo_svg']);
                if ($raw !== '') return $raw;
            }

            // 3) Alternativa de nombre (por si ACF lo guarda así)
            $raw = (string) get_field('personalizacion_logo_svg', 'option');
            $raw = trim($raw);
            if ($raw !== '') return $raw;
        }

        return '';
    }
}

if (!function_exists('theme360_prepare_inline_logo_svg')) {
    function theme360_prepare_inline_logo_svg(string $svg): string
    {
        $svg = trim($svg);
        if ($svg === '') return '';

        // Defensa en profundidad: sanitizar también al imprimir
        if (function_exists('th360_allowed_svg_html')) {
            $svg = wp_kses($svg, th360_allowed_svg_html());
        } else {
            $svg = wp_kses($svg, [
                'svg' => [
                    'xmlns' => true,
                    'viewbox' => true,
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
            ]);
        }

        if ($svg === '') return '';

        // Evitar IDs duplicados (tu SVG trae id="Capa_1" y el header + mobile lo duplican)
        $svg = preg_replace('/<svg\b([^>]*)\sid=("|\')[^"\']*\2([^>]*)>/i', '<svg$1$3>', $svg, 1);

        // A11y: el <a> ya tiene label, el svg puede ir aria-hidden
        $svg = preg_replace_callback('/<svg\b([^>]*)>/i', function ($m) {
            $attrs = $m[1];

            if (!preg_match('/\baria-hidden\s*=/i', $attrs)) {
                $attrs .= ' aria-hidden="true"';
            }
            if (!preg_match('/\bfocusable\s*=/i', $attrs)) {
                $attrs .= ' focusable="false"';
            }

            return '<svg' . $attrs . '>';
        }, $svg, 1);

        return $svg;
    }
}

$logo_svg_raw  = theme360_get_logo_svg_option();
$logo_svg_safe = ($logo_svg_raw !== '') ? theme360_prepare_inline_logo_svg($logo_svg_raw) : '';

?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>

<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Theme / PWA-ish -->
    <meta name="theme-color" content="<?php echo esc_attr($theme_color); ?>">
    <meta name="color-scheme" content="light">
    <meta name="mobile-web-app-capable" content="yes">

    <!-- iOS -->
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-title" content="<?php echo esc_attr($site_name); ?>">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">

    <!-- Windows tiles -->
    <meta name="msapplication-TileColor" content="<?php echo esc_attr($theme_color); ?>">

    <?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
    <?php if (function_exists('wp_body_open')) wp_body_open(); ?>

    <header class="site-header<?php echo esc_attr($header_logo_class); ?>">
        <?php echo '<!-- additional_header_class = ' . esc_html($header_logo_class) . ' -->'; ?>

        <div class="wrapper-header flex justify-between items-center">
            <button class="menu-button" type="button" title="<?php esc_attr_e('Menú principal', '360vo-theme'); ?>">
                <span class="menu-button-line"></span>
                <span class="menu-button-line"></span>
                <span class="menu-button-line"></span>
            </button>

            <?php if (has_custom_logo() && is_array($logo) && !empty($logo[0])) : ?>
                <a
                    href="<?php echo esc_url(home_url('/')); ?>"
                    class="site-header__logo"
                    rel="home"
                    aria-label="<?php echo esc_attr($site_name); ?>">
                    <img
                        src="<?php echo esc_url(untrailingslashit($logo[0])); ?>"
                        alt="<?php echo esc_attr($site_name); ?>"
                        width="400"
                        height="70"
                        class="site-header__logo-img"
                        decoding="async"
                        loading="eager"
                        fetchpriority="high">
                </a>

            <?php elseif ($logo_svg_safe !== '') : ?>
                <a
                    href="<?php echo esc_url(home_url('/')); ?>"
                    class="site-header__logo"
                    rel="home"
                    aria-label="<?php echo esc_attr($site_name); ?>">
                    <?php echo $logo_svg_safe; ?>
                </a>

            <?php else : ?>
                <a
                    href="<?php echo esc_url(home_url('/')); ?>"
                    class="site-header__logo site-header__logo--text"
                    rel="home">
                    <?php echo esc_html($site_name); ?>
                </a>
            <?php endif; ?>

            <div class="site-header__context-switcher" data-header-context-switcher>
                <?php
                wp_nav_menu(array(
                    'theme_location'  => 'primary',
                    'container'       => 'nav',
                    'container_class' => 'site-navigation',
                    'link_before'     => '<div class="item-navegacion__container"><span class="item-navegacion">',
                    'link_after'      => '</span></div>',
                ));
                ?>

                <?php if (!empty($header_context['title'])) : ?>
                    <?php
                    $header_context_variant = !empty($header_context['variant']) ? sanitize_html_class((string) $header_context['variant']) : 'default';
                    $header_context_classes = 'site-header__context-title site-header__context-title--' . $header_context_variant;
                    $header_context_logo_shape = !empty($header_context['logo_shape']) ? sanitize_html_class((string) $header_context['logo_shape']) : 'default';
                    ?>
                    <div
                        class="<?php echo esc_attr($header_context_classes); ?>"
                        data-header-context-title
                        data-header-context-variant="<?php echo esc_attr($header_context_variant); ?>"
                        aria-hidden="true">
                        <?php if ($header_context_variant === 'vehicle' && (!empty($header_context['logo_id']) || !empty($header_context['logo_url']))) : ?>
                            <span class="site-header__context-logo site-header__context-logo--<?php echo esc_attr($header_context_logo_shape); ?>" aria-hidden="true">
                                <?php
                                if (!empty($header_context['logo_id'])) {
                                    echo wp_get_attachment_image((int) $header_context['logo_id'], 'medium', false, array(
                                        'class' => 'site-header__context-logo-img',
                                        'alt' => esc_attr((string) $header_context['logo_alt']),
                                        'loading' => 'eager',
                                        'decoding' => 'async',
                                    ));
                                } else {
                                    echo '<img class="site-header__context-logo-img" src="' . esc_url((string) $header_context['logo_url']) . '" alt="' . esc_attr((string) $header_context['logo_alt']) . '" loading="eager" decoding="async">';
                                }
                                ?>
                            </span>
                        <?php endif; ?>

                        <span class="site-header__context-copy">
                            <span class="site-header__context-title-text"><?php echo esc_html((string) $header_context['title']); ?></span>
                            <?php if (!empty($header_context['subtitle'])) : ?>
                                <span class="site-header__context-subtitle"><?php echo esc_html((string) $header_context['subtitle']); ?></span>
                            <?php endif; ?>
                        </span>
                    </div>
                <?php endif; ?>
            </div>

            <div class="site-header__contact-buttons flex">
                <?php if ($has_header_contact_panel) : ?>
                    <button
                        class="site-header__contact-button site-header__contact-button--contact button--round-s border flex items-center justify-center"
                        type="button"
                        aria-label="<?php echo esc_attr(sprintf(__('Ver opciones de contacto de %s', '360vo-theme'), $site_name)); ?>"
                        aria-expanded="false"
                        aria-controls="site-header-contact-panel"
                        data-header-contact-toggle>
                        <span class="site-header__contact-button-icon site-header__contact-button-icon--contact"><?php echo E360VO_Icon::get('contacto_centralita', array('class' => 'flex justify-center items-center')); ?></span>
                        <span class="site-header__contact-button-text"><?php esc_html_e('Contacto', '360vo-theme'); ?></span>
                    </button>
                <?php endif; ?>

                <?php if ($telefono_principal !== '') : ?>
                    <a
                        class="site-header__contact-button site-header__contact-button--phone button--round-s border flex items-center justify-center"
                        href="<?php echo esc_url('tel:' . $telefono_href); ?>"
                        aria-label="<?php echo esc_attr(sprintf(__('Llamar a %s', '360vo-theme'), $site_name)); ?>">
                        <span class="site-header__contact-button-icon site-header__contact-button-icon--phone"><?php echo E360VO_Icon::get('call_new', array('class' => 'flex justify-center items-center')); ?></span>
                        <span class="site-header__contact-button-text"><?php echo esc_html($telefono_display); ?></span>
                    </a>
                <?php endif; ?>

                <?php if ($has_header_contact_panel) : ?>
                    <div class="site-header__contact-panel" id="site-header-contact-panel" data-header-contact-panel hidden>
                        <div class="site-header__contact-panel-header">
                            <strong><?php esc_html_e('Hablemos de tu próximo coche', '360vo-theme'); ?></strong>
                            <span><?php esc_html_e('Te atendemos de forma directa y sin rodeos.', '360vo-theme'); ?></span>
                        </div>

                        <div class="site-header__contact-panel-actions" aria-label="<?php esc_attr_e('Opciones de contacto', '360vo-theme'); ?>">
                            <?php if ($telefono_principal !== '') : ?>
                                <a class="site-header__contact-panel-action" href="<?php echo esc_url('tel:' . $telefono_href); ?>">
                                    <?php echo E360VO_Icon::get('call_new', array('aria-hidden' => 'true')); ?>
                                    <span>
                                        <strong><?php esc_html_e('Llamar ahora', '360vo-theme'); ?></strong>
                                        <small><?php echo esc_html($telefono_display); ?></small>
                                    </span>
                                </a>

                                <a class="site-header__contact-panel-action" href="<?php echo esc_url($whatsapp_href); ?>" target="_blank" rel="noopener noreferrer">
                                    <?php echo E360VO_Icon::get('whatsapp', array('aria-hidden' => 'true')); ?>
                                    <span>
                                        <strong><?php esc_html_e('Enviar WhatsApp', '360vo-theme'); ?></strong>
                                        <small><?php esc_html_e('Respuesta rápida del equipo comercial', '360vo-theme'); ?></small>
                                    </span>
                                </a>
                            <?php endif; ?>

                            <?php if ($email_principal !== '') : ?>
                                <a class="site-header__contact-panel-action" href="<?php echo esc_url('mailto:' . antispambot($email_principal)); ?>">
                                    <?php echo E360VO_Icon::get('email_new', array('aria-hidden' => 'true')); ?>
                                    <span>
                                        <strong><?php esc_html_e('Enviar email', '360vo-theme'); ?></strong>
                                        <small><?php echo esc_html($email_principal); ?></small>
                                    </span>
                                </a>
                            <?php endif; ?>
                        </div>

                        <?php if ($direccion_visible !== '' || !empty($header_schedule_lines)) : ?>
                            <div class="site-header__contact-panel-info">
                                <?php if ($direccion_visible !== '') : ?>
                                    <a class="site-header__contact-panel-detail site-header__contact-panel-detail--address" href="<?php echo esc_url('https://www.google.com/maps/search/?api=1&query=' . rawurlencode($direccion_maps !== '' ? $direccion_maps : $direccion_visible)); ?>" target="_blank" rel="noopener noreferrer">
                                        <small><?php echo esc_html($direccion_visible); ?></small>
                                    </a>
                                <?php endif; ?>

                                <?php if (!empty($header_schedule_lines)) : ?>
                                    <div class="site-header__contact-panel-detail site-header__contact-panel-detail--schedule">
                                        <?php foreach ($header_schedule_lines as $schedule_line) : ?>
                                            <small><?php echo esc_html($schedule_line); ?></small>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </header>

    <nav class="mobile-nav initially-hidden">
        <div class="mobile-nav-header wrapper-padding flex items-center justify-between">
            <?php if (has_custom_logo() && is_array($logo) && !empty($logo[0])) : ?>
                <a
                    href="<?php echo esc_url(home_url('/')); ?>"
                    class="menu-mobile__logo site-header__logo--mobile"
                    rel="home"
                    aria-label="<?php echo esc_attr($site_name); ?>">
                    <img
                        src="<?php echo esc_url(untrailingslashit($logo[0])); ?>"
                        alt="<?php echo esc_attr($site_name); ?>"
                        width="400"
                        height="70"
                        class="site-header__logo-img site-header__logo-img--mobile"
                        decoding="async"
                        loading="lazy">
                </a>
            <?php elseif ($logo_svg_safe !== '') : ?>
                <a
                    href="<?php echo esc_url(home_url('/')); ?>"
                    class="menu-mobile__logo site-header__logo--mobile"
                    rel="home"
                    aria-label="<?php echo esc_attr($site_name); ?>">
                    <?php echo $logo_svg_safe; ?>
                </a>
            <?php else : ?>
                <a
                    href="<?php echo esc_url(home_url('/')); ?>"
                    class="menu-mobile__logo site-header__logo--text site-header__logo--mobile"
                    rel="home">
                    <?php echo esc_html($site_name); ?>
                </a>
            <?php endif; ?>
            <button class="close-button" type="button" title="<?php esc_attr_e('Cerrar menú', '360vo-theme'); ?>">
                <span class="close-button-line"></span>
                <span class="close-button-line"></span>
                <span class="close-button-line"></span>
            </button>
        </div>

        <div class="mobile-nav-content wrapper-padding">
            <?php
            wp_nav_menu(array(
                'theme_location' => 'primary',
                'container'      => false,
                'items_wrap'     => '<ul>%3$s</ul>',
            ));
            ?>
        </div>

        <?php if ($has_social) : ?>
            <div class="mobile-nav-footer wrapper-padding flex items-center justify-center">


                <ul class="footer__social__list">
                    <?php if ($insta_url !== '') : ?>
                        <li><a aria-label="Instagram" href="<?php echo esc_url($insta_url); ?>" target="_blank" rel="noopener noreferrer"><?php echo E360VO_Icon::get('instagram', array('aria-hidden' => 'true')); ?></a></li>
                    <?php endif; ?>

                    <?php if ($face_url !== '') : ?>
                        <li><a aria-label="Facebook" href="<?php echo esc_url($face_url); ?>" target="_blank" rel="noopener noreferrer"><?php echo E360VO_Icon::get('facebook', array('aria-hidden' => 'true')); ?></a></li>
                    <?php endif; ?>

                    <?php if ($twitter_url !== '') : ?>
                        <li><a aria-label="Twitter" href="<?php echo esc_url($twitter_url); ?>" target="_blank" rel="noopener noreferrer">
                                <svg xmlns="http://www.w3.org/2000/svg" height="16" width="16" viewBox="0 0 512 512">
                                    <path opacity="1" fill="currentColor" d="M389.2 48h70.6L305.6 224.2 487 464H345L233.7 318.6 106.5 464H35.8L200.7 275.5 26.8 48H172.4L272.9 180.9 389.2 48zM364.4 421.8h39.1L151.1 88h-42L364.4 421.8z" />
                                </svg>
                            </a></li>
                    <?php endif; ?>

                    <?php if ($youtube_url !== '') : ?>
                        <li><a aria-label="Youtube" href="<?php echo esc_url($youtube_url); ?>" target="_blank" rel="noopener noreferrer">
                                <svg aria-hidden="true" height="16" width="16" viewBox="0 0 18 18">
                                    <path d="M7.2,11.6V6.4L12,9.1L7.2,11.6z M17.8,5.3c0,0-0.2-1.2-0.7-1.8c-0.7-0.7-1.4-0.7-1.8-0.8C12.8,2.6,9,2.6,9,2.6s-3.8,0-6.3,0.2c-0.3,0-1.1,0-1.8,0.8C0.4,4.1,0.2,5.3,0.2,5.3S0,6.8,0,8.2v1.5c0,1.5,0.2,2.9,0.2,2.9s0.2,1.2,0.7,1.8c0.7,0.7,1.6,0.7,2,0.8c1.4,0.1,5.9,0.2,6.1,0.2c0,0,3.8,0,6.3-0.2c0.3,0,1.1,0,1.8-0.8c0.5-0.5,0.7-1.8,0.7-1.8S18,11.2,18,9.8V8.2C18,6.8,17.8,5.3,17.8,5.3z" />
                                </svg>
                            </a></li>
                    <?php endif; ?>

                    <?php if ($tiktok_url !== '') : ?>
                        <li><a aria-label="TikTok" href="<?php echo esc_url($tiktok_url); ?>" target="_blank" rel="noopener noreferrer">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 50 50" width="20" height="20">
                                    <path d="M41,4H9C6.243,4,4,6.243,4,9v32c0,2.757,2.243,5,5,5h32c2.757,0,5-2.243,5-5V9C46,6.243,43.757,4,41,4z M37.006,22.323 c-0.227,0.021-0.457,0.035-0.69,0.035c-2.623,0-4.928-1.349-6.269-3.388c0,5.349,0,11.435,0,11.537c0,4.709-3.818,8.527-8.527,8.527 s-8.527-3.818-8.527-8.527s3.818-8.527,8.527-8.527c0.178,0,0.352,0.016,0.527,0.027v4.202c-0.175-0.021-0.347-0.053-0.527-0.053 c-2.404,0-4.352,1.948-4.352,4.352s1.948,4.352,4.352,4.352s4.527-1.894,4.527-4.298c0-0.095,0.042-19.594,0.042-19.594h4.016 c0.378,3.591,3.277,6.425,6.901,6.685V22.323z" />
                                </svg>
                            </a></li>
                    <?php endif; ?>

                    <?php if ($social_repeater !== '' && function_exists('have_rows') && have_rows($social_repeater, 'option')) : ?>
                        <?php while (have_rows($social_repeater, 'option')) : the_row(); ?>
                            <?php
                            $social_network_name = (string) get_sub_field('nombre_rs');
                            $social_network_link = (string) get_sub_field('link_red_social');
                            $social_network_icon = get_sub_field('icono_red_social');
                            ?>
                            <li>
                                <?php if (!empty($social_network_icon)) : ?>
                                    <a href="<?php echo esc_url($social_network_link); ?>" target="_blank" rel="noopener noreferrer">
                                        <img src="<?php echo esc_url($social_network_icon); ?>" alt="<?php echo esc_attr($social_network_name); ?>" width="18" height="18">
                                    </a>
                                <?php else : ?>
                                    <a href="<?php echo esc_url($social_network_link); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html($social_network_name); ?></a>
                                <?php endif; ?>
                            </li>
                        <?php endwhile; ?>
                    <?php endif; ?>
                </ul>
            </div>
        <?php endif; ?>
    </nav>

    <div class="backdrop" aria-hidden="true"></div>

    <?php
    get_template_part('template-parts/navigation/breadcrumbs');
    theme360_breadcrumbs();

    if (is_page() || is_singular('post')) :
    ?>
        <button
            class="scroll-top-button"
            type="button"
            data-scroll-top
            aria-label="<?php esc_attr_e('Volver arriba', '360vo-theme'); ?>"
            title="<?php esc_attr_e('Volver arriba', '360vo-theme'); ?>">
            <span class="scroll-top-button__icon" aria-hidden="true"><?php echo E360VO_Icon::get('scroll_up', ['width' => 20, 'height' => 20]); ?></span>
            <span class="scroll-top-button__label">Subir</span>
        </button>
    <?php
    endif;
    ?>
