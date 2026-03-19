<?php

/**
 * @package 360vo-theme
 */
if (!defined('ABSPATH')) {
  exit;
}

/**
 * Helper de contexto seguro (plugin primero, fallback ACF).
 * Si ya existe en otro template, no se redeclara.
 */
if (!function_exists('theme360_ctx_get')) {
  function theme360_ctx_get($key, $default = '')
  {
    if (function_exists('gv360_get_variable')) {
      return gv360_get_variable($key, $default);
    }

    if (function_exists('get_field')) {
      $map = array(
        'email'                  => 'correo_y_telefono_correo_principal',
        'phone_primary'          => 'correo_y_telefono_telefono_principal',
        'phone_display'          => 'correo_y_telefono_telefono_principal',
        'instagram_url'          => 'social_insta',
        'facebook_url'           => 'social_face',
        'twitter_url'            => 'social_twitter',
        'youtube_url'            => 'social_youtube',
        'tiktok_url'             => 'social_tiktok',
        'show_address'           => 'direccion_del_concesionario_mostrar_direccion',
        'direccion'              => 'direccion_del_concesionario_direccion',
        'postal_code'            => 'direccion_del_concesionario_codigo_postal',
        'localidad'              => 'direccion_del_concesionario_localidad',
        'provincia'              => 'direccion_del_concesionario_provincia',
        'maps_name'              => 'direccion_del_concesionario_nombre_en_maps',
        'show_footer_social'     => 'opciones_footer',
        'kit_digital_enabled'    => 'kit_digital_tiene_kit',
        'kit_digital_background' => 'kit_digital_fondo_kit',
      );

      if (isset($map[$key])) {
        $raw = get_field($map[$key], 'option');

        if ($key === 'show_footer_social' && is_array($raw)) {
          return !empty($raw['redes_sociales']) ? '1' : '0';
        }

        if (is_scalar($raw)) {
          $v = trim((string) $raw);
          if ($v !== '') {
            if (
              in_array($key, array('instagram_url', 'facebook_url', 'twitter_url', 'youtube_url', 'tiktok_url'), true)
              && !preg_match('#^https?://#i', $v)
            ) {
              $base = array(
                'instagram_url' => 'https://www.instagram.com/%s',
                'facebook_url'  => 'https://www.facebook.com/%s',
                'twitter_url'   => 'https://www.twitter.com/%s',
                'youtube_url'   => 'https://www.youtube.com/%s',
                'tiktok_url'    => 'https://www.tiktok.com/%s',
              );
              $v = sprintf($base[$key], ltrim($v, '@/'));
            }

            return $v;
          }
        }
      }
    }

    return $default;
  }
}

/**
 * API key Maps:
 * 1) Contexto plugin (si existe maps_api_key)
 * 2) ACF grupo 'api_keys' => 'api_key_maps'
 * 3) ACF campo directo 'api_key_maps' (compat)
 */
if (!function_exists('theme360_get_maps_api_key')) {
  function theme360_get_maps_api_key()
  {
    $ctx_value = trim((string) theme360_ctx_get('maps_api_key', ''));
    if ($ctx_value !== '') {
      return preg_replace('/[^A-Za-z0-9\-_]/', '', $ctx_value);
    }

    if (function_exists('get_field')) {
      $api_keys = get_field('api_keys', 'option');
      if (is_array($api_keys) && !empty($api_keys['api_key_maps'])) {
        return preg_replace('/[^A-Za-z0-9\-_]/', '', (string) $api_keys['api_key_maps']);
      }
    }

    return '';
  }
}

if (!function_exists('theme360_social_repeater_name')) {
  function theme360_social_repeater_name()
  {
    if (function_exists('have_rows')) {
      if (have_rows('social_add_social', 'option')) return 'social_add_social';
      if (have_rows('add_social', 'option')) return 'add_social';
    }
    return '';
  }
}

if (!function_exists('theme360_build_schedule_html')) {
  function theme360_build_schedule_html(array $horario)
  {
    if (empty($horario)) return '';

    $conf_horario = isset($horario['conf_horario']) && is_array($horario['conf_horario']) ? $horario['conf_horario'] : array();
    if (!empty($conf_horario['horario_alt'])) return '';

    $lunes_a_viernes = isset($horario['lunes_a_viernes']) && is_array($horario['lunes_a_viernes']) ? $horario['lunes_a_viernes'] : array();
    $horario_sabado  = isset($horario['horario_sabado']) && is_array($horario['horario_sabado']) ? $horario['horario_sabado'] : array();
    $horario_domingo = isset($horario['horario_domingo']) && is_array($horario['horario_domingo']) ? $horario['horario_domingo'] : array();

    $entrada_lun_vier       = isset($lunes_a_viernes['entrada_lun_vier']) ? (string) $lunes_a_viernes['entrada_lun_vier'] : '';
    $salida_lun_vier        = isset($lunes_a_viernes['salida_lun_vier']) ? (string) $lunes_a_viernes['salida_lun_vier'] : '';
    $horario_tarde          = !empty($lunes_a_viernes['horario_tarde']);
    $entrada_lun_vier_tarde = isset($lunes_a_viernes['entrada_lun_vier_tarde']) ? (string) $lunes_a_viernes['entrada_lun_vier_tarde'] : '';
    $salida_lun_vier_tarde  = isset($lunes_a_viernes['salida_lun_vier_tarde']) ? (string) $lunes_a_viernes['salida_lun_vier_tarde'] : '';

    $abierto_sabados   = !empty($horario_sabado['abierto_sabados']);
    $entrada_sab       = isset($horario_sabado['entrada_sab']) ? (string) $horario_sabado['entrada_sab'] : '';
    $salida_sab        = isset($horario_sabado['salida_sab']) ? (string) $horario_sabado['salida_sab'] : '';
    $horario_tarde_sab = !empty($horario_sabado['horario_tarde_sab']);
    $entrada_sab_tarde = isset($horario_sabado['entrada_sab_tarde']) ? (string) $horario_sabado['entrada_sab_tarde'] : '';
    $salida_sab_tarde  = isset($horario_sabado['salida_sab_tarde']) ? (string) $horario_sabado['salida_sab_tarde'] : '';

    $abierto_domingos  = !empty($horario_domingo['abierto_domingos']);
    $entrada_dom       = isset($horario_domingo['entrada_dom']) ? (string) $horario_domingo['entrada_dom'] : '';
    $salida_dom        = isset($horario_domingo['salida_dom']) ? (string) $horario_domingo['salida_dom'] : '';
    $horario_tarde_dom = !empty($horario_domingo['horario_tarde_dom']);
    $entrada_dom_tarde = isset($horario_domingo['entrada_dom_tarde']) ? (string) $horario_domingo['entrada_dom_tarde'] : '';
    $salida_dom_tarde  = isset($horario_domingo['salida_dom_tarde']) ? (string) $horario_domingo['salida_dom_tarde'] : '';

    $lunes_viernes_horarios = '<strong>lunes a viernes:</strong> ' . esc_html($entrada_lun_vier) . ' - ' . esc_html($salida_lun_vier);
    if ($horario_tarde && $entrada_lun_vier_tarde !== '' && $salida_lun_vier_tarde !== '') {
      $lunes_viernes_horarios .= ' y ' . esc_html($entrada_lun_vier_tarde) . ' - ' . esc_html($salida_lun_vier_tarde);
    }

    $sabado_horarios = $abierto_sabados
      ? '<strong>sábado:</strong> ' . esc_html($entrada_sab) . ' - ' . esc_html($salida_sab) . (($horario_tarde_sab && $entrada_sab_tarde !== '' && $salida_sab_tarde !== '') ? ' y ' . esc_html($entrada_sab_tarde) . ' - ' . esc_html($salida_sab_tarde) : '')
      : '<strong>sábado:</strong> cerrado';

    $domingo_horarios = $abierto_domingos
      ? '<strong>domingo:</strong> ' . esc_html($entrada_dom) . ' - ' . esc_html($salida_dom) . (($horario_tarde_dom && $entrada_dom_tarde !== '' && $salida_dom_tarde !== '') ? ' y ' . esc_html($entrada_dom_tarde) . ' - ' . esc_html($salida_dom_tarde) : '')
      : '<strong>domingo:</strong> cerrado';

    $html = '';
    foreach (array($lunes_viernes_horarios, $sabado_horarios, $domingo_horarios) as $linea) {
      if ($linea !== '') $html .= '<p class="horario__dias">' . $linea . '</p>';
    }
    return $html;
  }
}

$email_principal    = trim((string) theme360_ctx_get('email', ''));
$telefono_principal = trim((string) theme360_ctx_get('phone_primary', ''));
$telefono_e164      = trim((string) theme360_ctx_get('phone_e164', ''));
$telefono_display   = trim((string) theme360_ctx_get('phone_display', $telefono_principal));

$mostrar_direccion = in_array(strtolower((string) theme360_ctx_get('show_address', '1')), array('1', 'true', 'yes', 'on'), true);
$direccion         = trim((string) theme360_ctx_get('direccion', ''));
$codigo_postal     = trim((string) theme360_ctx_get('postal_code', ''));
$localidad         = trim((string) theme360_ctx_get('localidad', ''));
$provincia         = trim((string) theme360_ctx_get('provincia', ''));
$nombre_en_maps    = trim((string) theme360_ctx_get('maps_name', ''));

$direccion_completa = implode(', ', array_filter(array(
  $nombre_en_maps,
  $direccion,
  $codigo_postal,
  $localidad,
  $provincia,
)));

$tiene_kit = in_array(strtolower((string) theme360_ctx_get('kit_digital_enabled', '0')), array('1', 'true', 'yes', 'on'), true);
$fondo_kit = (string) theme360_ctx_get('kit_digital_background', 'claro');

$clase_kit = in_array($fondo_kit, array('claro', 'oscuro', 'color_tema'), true) ? $fondo_kit : 'claro';

$ruta_logos = trailingslashit(get_template_directory_uri() . '/public/assets/images/patrocinadores');

$horario       = array();
$schedule_json = (string) theme360_ctx_get('schedule_json', '');
if ($schedule_json !== '') {
  $decoded = json_decode($schedule_json, true);
  if (is_array($decoded)) $horario = $decoded;
}
if (empty($horario) && function_exists('get_field')) {
  $raw_horario = get_field('horario', 'option');
  if (is_array($raw_horario)) $horario = $raw_horario;
}
$horario_comercio = theme360_build_schedule_html($horario);

$insta_url   = trim((string) theme360_ctx_get('instagram_url', ''));
$face_url    = trim((string) theme360_ctx_get('facebook_url', ''));
$twitter_url = trim((string) theme360_ctx_get('twitter_url', ''));
$youtube_url = trim((string) theme360_ctx_get('youtube_url', ''));
$tiktok_url  = trim((string) theme360_ctx_get('tiktok_url', ''));

$social_repeater = theme360_social_repeater_name();

$mostrar_redes_sociales = in_array(strtolower((string) theme360_ctx_get('show_footer_social', '1')), array('1', 'true', 'yes', 'on'), true);

$social_links = array();

$add_social_link = static function (array &$items, string $url, string $label, string $type, string $icon = ''): void {
  $clean_url = trim($url);
  if ($clean_url === '') {
    return;
  }

  $items[] = array(
    'label' => $label,
    'url'   => $clean_url,
    'type'  => $type,
    'icon'  => $icon,
  );
};

$add_social_link($social_links, $insta_url, 'Instagram', 'icon-font', 'icon-instagram');
$add_social_link($social_links, $face_url, 'Facebook', 'theme-icon', 'facebook');
$add_social_link($social_links, $twitter_url, 'Twitter', 'svg', '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" aria-hidden="true"><path opacity="1" fill="currentColor" d="M389.2 48h70.6L305.6 224.2 487 464H345L233.7 318.6 106.5 464H35.8L200.7 275.5 26.8 48H172.4L272.9 180.9 389.2 48zM364.4 421.8h39.1L151.1 88h-42L364.4 421.8z"></path></svg>');
$add_social_link($social_links, $youtube_url, 'YouTube', 'svg', '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 18 18" aria-hidden="true"><path fill="currentColor" d="M7.2,11.6V6.4L12,9.1L7.2,11.6z M17.8,5.3c0,0-0.2-1.2-0.7-1.8c-0.7-0.7-1.4-0.7-1.8-0.8C12.8,2.6,9,2.6,9,2.6 s-3.8,0-6.3,0.2c-0.3,0-1.1,0-1.8,0.8C0.4,4.1,0.2,5.3,0.2,5.3S0,6.8,0,8.2v1.5c0,1.5,0.2,2.9,0.2,2.9s0.2,1.2,0.7,1.8 c0.7,0.7,1.6,0.7,2,0.8c1.4,0.1,5.9,0.2,6.1,0.2c0,0,3.8,0,6.3-0.2c0.3,0,1.1,0,1.8-0.8c0.5-0.5,0.7-1.8,0.7-1.8S18,11.2,18,9.8V8.2 C18,6.8,17.8,5.3,17.8,5.3z"></path></svg>');
$add_social_link($social_links, $tiktok_url, 'TikTok', 'svg', '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 50 50" aria-hidden="true"><path fill="currentColor" d="M41,4H9C6.243,4,4,6.243,4,9v32c0,2.757,2.243,5,5,5h32c2.757,0,5-2.243,5-5V9C46,6.243,43.757,4,41,4z M37.006,22.323 c-0.227,0.021-0.457,0.035-0.69,0.035c-2.623,0-4.928-1.349-6.269-3.388c0,5.349,0,11.435,0,11.537c0,4.709-3.818,8.527-8.527,8.527 s-8.527-3.818-8.527-8.527s3.818-8.527,8.527-8.527c0.178,0,0.352,0.016,0.527,0.027v4.202c-0.175-0.021-0.347-0.053-0.527-0.053 c-2.404,0-4.352,1.948-4.352,4.352s1.948,4.352,4.352,4.352s4.527-1.894,4.527-4.298c0-0.095,0.042-19.594,0.042-19.594h4.016 c0.378,3.591,3.277,6.425,6.901,6.685V22.323z"></path></svg>');

if ($social_repeater !== '' && function_exists('have_rows') && have_rows($social_repeater, 'option')) {
  while (have_rows($social_repeater, 'option')) {
    the_row();

    $social_network_name = trim((string) get_sub_field('nombre_rs'));
    $social_network_link = trim((string) get_sub_field('link_red_social'));
    $social_network_icon = get_sub_field('icono_red_social');

    if ($social_network_link === '') {
      continue;
    }

    if (!empty($social_network_icon)) {
      $social_links[] = array(
        'label' => $social_network_name !== '' ? $social_network_name : __('Red social', '360vo-theme'),
        'url'   => $social_network_link,
        'type'  => 'image',
        'icon'  => (string) $social_network_icon,
      );
      continue;
    }

    $social_links[] = array(
      'label' => $social_network_name !== '' ? $social_network_name : __('Red social', '360vo-theme'),
      'url'   => $social_network_link,
      'type'  => 'text',
      'icon'  => $social_network_name,
    );
  }
}

$render_social_icon = static function (array $social_item): string {
  switch ($social_item['type']) {
    case 'icon-font':
      return '<i class="' . esc_attr($social_item['icon']) . '" aria-hidden="true"></i>';

    case 'theme-icon':
      return E360VO_Icon::get($social_item['icon'], array('aria-hidden' => 'true'));

    case 'svg':
      return $social_item['icon'];

    case 'image':
      return '<img src="' . esc_url($social_item['icon']) . '" alt="" loading="lazy" width="20" height="20">';

    case 'text':
    default:
      return '<span class="ft-social__text">' . esc_html($social_item['icon'] !== '' ? $social_item['icon'] : $social_item['label']) . '</span>';
  }
};

$api_key_maps = theme360_get_maps_api_key();
$url_mapa = '';
if ($direccion_completa !== '' && $api_key_maps !== '') {
  $url_mapa = 'https://www.google.com/maps/embed/v1/place?key=' . rawurlencode($api_key_maps) . '&q=' . rawurlencode($direccion_completa) . '&zoom=10';
}
?>

<button id="open-popup" class="boton-telefono" aria-label="<?php echo esc_attr(sprintf(__('Contacta con %s', '360vo-theme'), get_bloginfo('name'))); ?>">
  <?php
  echo E360VO_Icon::get('contacto_centralita', array(
    'aria-label' => __('Contacta con nosotros', '360vo-theme'),
    'width'      => 72,
    'height'     => 48,
  ));
  ?>
</button>

<footer>
  <section class="footer-section flex">

    <div class="footer-section__item footer-section__item--horario">
      <h3 class="footer-section__title"><?php echo E360VO_Icon::get('reloj'); ?><?php esc_html_e('Horario', '360vo-theme'); ?></h3>
      <?php echo wp_kses($horario_comercio, array('p' => array('class' => array()), 'strong' => array())); ?>
    </div>

    <div class="footer-section__item footer-section__item--ubicacion">
      <h3 class="footer-section__title"><?php echo E360VO_Icon::get('location'); ?><?php esc_html_e('Dónde estamos', '360vo-theme'); ?></h3>
      <?php if ($mostrar_direccion) : ?>
        <div class="footer-section__direccion">
          <address class="footer__direccion">
            <p><strong><?php echo esc_html(get_bloginfo('name')); ?></strong></p>
            <?php if ($direccion !== '') : ?><p><?php echo esc_html($direccion); ?></p><?php endif; ?>
            <?php
            $linea_cp_localidad = trim($codigo_postal . ' ' . $localidad);
            if ($provincia !== '' && mb_strtolower($localidad) !== mb_strtolower($provincia)) {
              $linea_cp_localidad .= ', ' . $provincia;
            }
            ?>
            <?php if ($linea_cp_localidad !== '') : ?><p><?php echo esc_html($linea_cp_localidad); ?></p><?php endif; ?>
          </address>
        </div>
      <?php endif; ?>
    </div>

    <div class="footer-section__item footer-section__item--contacto">
      <h3 class="footer-section__title">
        <?php echo E360VO_Icon::get('contacto_centralita', array('width' => 24, 'height' => 24)); ?>
        <?php esc_html_e('Contacto', '360vo-theme'); ?>
      </h3>

      <div class="button--footer-wrapper flex">
        <?php if ($telefono_principal !== '') : ?>
          <a class="button button--footer button--telefono"
            href="<?php echo esc_url('tel:' . preg_replace('/\s+/', '', ($telefono_e164 !== '' ? $telefono_e164 : $telefono_principal))); ?>"
            aria-label="<?php echo esc_attr(sprintf(__('Llamar a %s', '360vo-theme'), get_bloginfo('name'))); ?>">
            <span class="button--footer__icon"><?php echo E360VO_Icon::get('call'); ?></span>
            <?php echo esc_html($telefono_display !== '' ? $telefono_display : $telefono_principal); ?>
          </a>
        <?php endif; ?>

        <?php if ($email_principal !== '') : ?>
          <a class="button button--footer button--correo"
            href="<?php echo esc_url('mailto:' . antispambot($email_principal)); ?>"
            aria-label="<?php esc_attr_e('Enviar correo electrónico', '360vo-theme'); ?>">
            <span class="button--footer__icon"><?php echo E360VO_Icon::get('email'); ?></span>
            <?php echo esc_html($email_principal); ?>
          </a>
        <?php endif; ?>

        <?php if ($telefono_principal !== '') : ?>
          <a class="button button--footer button--whatsapp"
            href="<?php echo esc_url('https://wa.me/' . preg_replace('/\D+/', '', ltrim($telefono_e164 !== '' ? $telefono_e164 : $telefono_principal, '+')) . '?text=' . rawurlencode('Hola ' . get_bloginfo('name'))); ?>"
            target="_blank" rel="noopener noreferrer"
            aria-label="<?php echo esc_attr(sprintf(__('Contacta con %s a través de WhatsApp', '360vo-theme'), get_bloginfo('name'))); ?>"
            title="<?php echo esc_attr(sprintf(__('Contacta con %s a través de WhatsApp', '360vo-theme'), get_bloginfo('name'))); ?>">
            <span class="button--footer__icon"><?php echo E360VO_Icon::get('whatsapp'); ?></span>
            <span class="boton__flotante-whatsapp__texto"><?php esc_html_e('Envíanos un WhatsApp', '360vo-theme'); ?></span>
          </a>
        <?php endif; ?>
      </div>
    </div>

    <div class="footer-section__item footer-section__item--mapa">
      <div class="map-container" data-map>
        <div class="map-placeholder flex items-center justify-center" aria-hidden="true">
          <div class="spinner"></div>
        </div>

        <?php if ($url_mapa) : ?>
          <iframe
            id="mapFrame"
            title="<?php echo esc_attr(sprintf(__('Localización de %s', '360vo-theme'), get_bloginfo('name'))); ?>"
            width="400" height="300"
            frameborder="0"
            style="border:0;"
            allowfullscreen
            referrerpolicy="no-referrer-when-downgrade"
            fetchpriority="low"
            data-src="<?php echo esc_url($url_mapa); ?>"></iframe>

        <?php endif; ?>
      </div>
    </div>


  </section>

  <?php
  $menu_ids           = array('footer_menu_1', 'footer_menu_2', 'footer_menu_3');
  $locations          = get_nav_menu_locations();
  $footer_menu_blocks = array();

  foreach ($menu_ids as $menu_id) {
    if (empty($locations[$menu_id])) {
      continue;
    }

    $menu_obj = wp_get_nav_menu_object($locations[$menu_id]);
    if (!$menu_obj) {
      continue;
    }

    $menu_items = wp_get_nav_menu_items($menu_obj->term_id);
    if (empty($menu_items)) {
      continue;
    }

    $footer_menu_blocks[] = array(
      'location' => $menu_id,
      'title'    => $menu_obj->name,
    );
  }

  $blog_page_id     = (int) get_option('page_for_posts');
  $blog_archive_url = $blog_page_id ? get_permalink($blog_page_id) : get_post_type_archive_link('post');
  $latest_posts     = array_filter(get_latest_posts(3), static function ($post) {
    return $post instanceof stdClass && !empty($post->ID);
  });
  $show_footer_top  = !empty($footer_menu_blocks) || (!empty($latest_posts)) || ($mostrar_redes_sociales && !empty($social_links));
  ?>

  <?php if ($show_footer_top) : ?>
    <section class="ft-top" aria-labelledby="footer-discover-title">
      <div class="ft-top__inner">
        <div class="ft-top__menus">
          <div class="ft-top__heading">
            <span class="ft-top__eyebrow"><?php esc_html_e('Explora', '360vo-theme'); ?></span>
            <h3 id="footer-discover-title" class="ft-top__title"><?php esc_html_e('Accede rápido a las páginas clave y al contenido más reciente.', '360vo-theme'); ?></h3>
            <p class="ft-top__description"><?php esc_html_e('Hemos reorganizado esta zona para que navegar por localidades, servicios y novedades del blog sea mucho más ágil.', '360vo-theme'); ?></p>
          </div>

          <?php if (!empty($footer_menu_blocks)) : ?>
            <nav class="ft-menus" aria-label="<?php esc_attr_e('Navegación destacada del footer', '360vo-theme'); ?>">
              <div class="ft-menus__grid">
                <?php foreach ($footer_menu_blocks as $menu_block) : ?>
                  <section class="ft-menus__block">
                    <h4 class="ft-menus__title"><?php echo esc_html($menu_block['title']); ?></h4>
                    <?php
                    wp_nav_menu(array(
                      'theme_location' => $menu_block['location'],
                      'menu_class'     => 'ft-menus__list',
                      'container'      => false,
                      'items_wrap'     => '<ul class="ft-menus__list">%3$s</ul>',
                      'fallback_cb'    => false,
                    ));
                    ?>
                  </section>
                <?php endforeach; ?>
              </div>
            </nav>
          <?php endif; ?>

          <?php if ($mostrar_redes_sociales && !empty($social_links)) : ?>
            <div class="ft-social" aria-label="<?php esc_attr_e('Redes sociales', '360vo-theme'); ?>">
              <div class="ft-social__intro">
                <span class="ft-social__eyebrow"><?php esc_html_e('Conecta', '360vo-theme'); ?></span>
                <p class="ft-social__title"><?php esc_html_e('Síguenos en nuestros canales.', '360vo-theme'); ?></p>
              </div>

              <ul class="ft-social__list" role="list">
                <?php foreach ($social_links as $social_item) : ?>
                  <li class="ft-social__item">
                    <a
                      class="ft-social__link"
                      href="<?php echo esc_url($social_item['url']); ?>"
                      target="_blank"
                      rel="noopener noreferrer"
                      aria-label="<?php echo esc_attr($social_item['label']); ?>">
                      <span class="ft-social__icon"><?php echo $render_social_icon($social_item); ?></span>
                      <span class="ft-social__label"><?php echo esc_html($social_item['label']); ?></span>
                    </a>
                  </li>
                <?php endforeach; ?>
              </ul>
            </div>
          <?php endif; ?>
        </div>

        <?php if (!empty($latest_posts)) : ?>
          <aside class="ft-latest" aria-labelledby="footer-latest-title">
            <div class="ft-latest__header">
              <span class="ft-latest__eyebrow"><?php esc_html_e('Blog', '360vo-theme'); ?></span>
              <h3 id="footer-latest-title" class="ft-latest__title"><?php esc_html_e('Últimas noticias', '360vo-theme'); ?></h3>
              <p class="ft-latest__description"><?php esc_html_e('Contenido fresco para ayudar a tus usuarios a descubrir consejos, tendencias y oportunidades de compra.', '360vo-theme'); ?></p>
            </div>

            <div class="ft-latest__grid">
              <?php foreach ($latest_posts as $recent_post) : ?>
                <?php
                $post_id          = (int) $recent_post->ID;
                $post_permalink   = get_permalink($post_id);
                $post_title       = get_the_title($post_id);
                $post_excerpt     = get_the_excerpt($post_id);
                $excerpt_text     = $post_excerpt !== '' ? $post_excerpt : wp_trim_words(wp_strip_all_tags((string) get_post_field('post_content', $post_id)), 22, '…');
                $thumbnail_id     = (int) get_post_thumbnail_id($post_id);
                $thumbnail_alt    = trim((string) get_post_meta($thumbnail_id, '_wp_attachment_image_alt', true));
                $reading_time     = max(1, (int) calcular_tiempo_lectura((string) get_post_field('post_content', $post_id)));
                $post_categories  = get_the_category($post_id);
                $primary_category = !empty($post_categories) ? $post_categories[0]->name : '';
                ?>
                <article class="ft-latest__card">
                  <a href="<?php echo esc_url($post_permalink); ?>" class="ft-latest__link">
                    <div class="ft-latest__media">
                      <?php if ($thumbnail_id) : ?>
                        <?php echo wp_get_attachment_image($thumbnail_id, 'medium_large', false, array('class' => 'ft-latest__image', 'loading' => 'lazy', 'alt' => $thumbnail_alt !== '' ? $thumbnail_alt : $post_title)); ?>
                      <?php else : ?>
                        <div class="ft-latest__image ft-latest__image--placeholder" aria-hidden="true">
                          <span><?php echo esc_html(mb_substr($post_title, 0, 1)); ?></span>
                        </div>
                      <?php endif; ?>
                    </div>

                    <div class="ft-latest__content">
                      <div class="ft-latest__meta">
                        <span><?php echo esc_html(get_the_date(get_option('date_format'), $post_id)); ?></span>
                        <?php if ($primary_category !== '') : ?>
                          <span><?php echo esc_html($primary_category); ?></span>
                        <?php endif; ?>
                        <span><?php echo esc_html(sprintf(_n('%s min de lectura', '%s min de lectura', $reading_time, '360vo-theme'), number_format_i18n($reading_time))); ?></span>
                      </div>

                      <h4 class="ft-latest__card-title"><?php echo esc_html($post_title); ?></h4>
                      <p class="ft-latest__excerpt"><?php echo esc_html(wp_trim_words($excerpt_text, 18, '…')); ?></p>
                      <span class="ft-latest__cta"><?php esc_html_e('Leer artículo', '360vo-theme'); ?></span>
                    </div>
                  </a>
                </article>
              <?php endforeach; ?>
            </div>

            <?php if ($blog_archive_url) : ?>
              <div class="ft-latest__footer">
                <a href="<?php echo esc_url($blog_archive_url); ?>" class="ft-latest__more">
                  <span><?php esc_html_e('Ver todas las noticias', '360vo-theme'); ?></span>
                  <span aria-hidden="true">→</span>
                </a>
              </div>
            <?php endif; ?>
          </aside>
        <?php endif; ?>
      </div>
    </section>
  <?php endif; ?>

  <?php if ($tiene_kit) : ?>
    <section class="footer__middle <?php echo esc_attr($clase_kit); ?>">
      <div class="footer__patrocinadores">
        <div class="patrocinadores__logos">
          <p><?php esc_html_e('Programa Kit digital cofinanciado por los fondos Next Generation (EU) del Mecanismo de Recuperación y Resiliencia.', '360vo-theme'); ?></p>
          <ul>
            <li><img src="<?php echo esc_url($ruta_logos . 'ue_color_azul.png'); ?>" alt="Financiado por la Unión Europea" width="275" height="72" loading="lazy" /></li>
            <li><img src="<?php echo esc_url($ruta_logos . 'kit_digital.png'); ?>" alt="Kit Digital" width="262" height="72" loading="lazy" /></li>
            <li><img src="<?php echo esc_url($ruta_logos . 'red.png'); ?>" alt="Red" width="209" height="72" loading="lazy" /></li>
            <li><img src="<?php echo esc_url($ruta_logos . 'ptr_color.png'); ?>" alt="Plan de Recuperación, Transformación y Resiliencia" width="352" height="72" loading="lazy" /></li>
            <li><img src="<?php echo esc_url($ruta_logos . 'gob_espana.png'); ?>" alt="Gobierno de España" width="378" height="72" loading="lazy" /></li>
          </ul>
        </div>
      </div>
    </section>
  <?php endif; ?>

  <section class="footer__bottom flex justify-between">
    <?php
    $menu_name = 'terminos_condiciones';
    if (has_nav_menu($menu_name)) {
      $locations  = get_nav_menu_locations();
      $menu_id    = isset($locations[$menu_name]) ? (int) $locations[$menu_name] : 0;
      $menu       = $menu_id ? wp_get_nav_menu_object($menu_id) : null;
      $menu_items = $menu ? wp_get_nav_menu_items($menu->term_id) : array();

      if (!empty($menu_items)) {
        echo '<div class="footer__legal">';
        wp_nav_menu(array(
          'theme_location'  => $menu_name,
          'container_class' => 'custom-menu-class',
        ));
        echo '</div>';
      }
    } elseif (is_user_logged_in()) {
      echo '<button style="width:100%;margin-bottom:1.4rem" onclick="window.location.href=\'' . esc_url(admin_url('nav-menus.php')) . '\'">' . esc_html__('Crear menú política de privacidad, cookies y aviso legal', '360vo-theme') . '</button>';
    }
    ?>

    <div class="footer__copyright flex justify-between">
      <p>&copy; <?php echo esc_html(date_i18n('Y')); ?> <b><?php echo esc_html(get_bloginfo('name')); ?></b></p>
      <p class="footer__copyright-developer"><?php esc_html_e('Desarrollado por', '360vo-theme'); ?> <a href="https://www.360vo.es" target="_blank" rel="noopener noreferrer">360vo</a></p>
    </div>
  </section>

  <?php if ($telefono_principal !== '') : ?>
    <div id="popup" class="popup" style="display: none;">
      <div class="popup-content--whatsapp">
        <span id="close-popup" class="close">×</span>
        <h3><?php esc_html_e('¿Estás buscando un coche?', '360vo-theme'); ?></h3>

        <a class="popup-contact__button popup-contact__button--whatsapp flex items-center justify-center" href="<?php echo esc_url('https://wa.me/' . preg_replace('/\D+/', '', ltrim($telefono_e164 !== '' ? $telefono_e164 : $telefono_principal, '+')) . '?text=' . rawurlencode('Estoy buscando un coche en ' . get_bloginfo('name'))); ?>" target="_blank" rel="noopener noreferrer">
          <?php echo E360VO_Icon::get('whatsapp'); ?>
          <span class="phone-contact"><?php esc_html_e('Mándanos un WhatsApp', '360vo-theme'); ?></span>
        </a>

        <p><?php esc_html_e('O llámanos por teléfono:', '360vo-theme'); ?></p>

        <a class="popup-contact__button flex items-center justify-center" href="<?php echo esc_url('tel:' . preg_replace('/\s+/', '', ($telefono_e164 !== '' ? $telefono_e164 : $telefono_principal))); ?>">
          <?php echo E360VO_Icon::get('call'); ?>
          <span class="phone-contact"><?php echo esc_html($telefono_display !== '' ? $telefono_display : $telefono_principal); ?></span>
        </a>
      </div>
    </div>
  <?php endif; ?>
</footer>

<?php wp_footer(); ?>
</body>

</html>
