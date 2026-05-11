<?php

/**
 * Front Page
 * @package 360vo-theme
 */

if (! defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

get_header();

/**
 * HERO: últimos coches (CPT "coche")
 */
$hero_cars = [];

$hero_query = new WP_Query([
    'post_type'           => 'coche',
    'post_status'         => 'publish',
    'posts_per_page'      => 4,
    'orderby'             => 'date',
    'order'               => 'DESC',
    'no_found_rows'       => true,
    'ignore_sticky_posts' => true,
]);

if ($hero_query->have_posts()) {
    while ($hero_query->have_posts()) {
        $hero_query->the_post();

        $car_id   = get_the_ID();
        $car_link = get_permalink($car_id);

        // Taxonomías
        $marcas  = get_the_terms($car_id, 'marca');
        $modelos = get_the_terms($car_id, 'modelo');

        $marca_term  = (!empty($marcas) && !is_wp_error($marcas)) ? $marcas[0] : null;
        $modelo_term = (!empty($modelos) && !is_wp_error($modelos)) ? $modelos[0] : null;

        $nombre_marca  = $marca_term ? $marca_term->name : '';
        $nombre_modelo = $modelo_term ? $modelo_term->name : '';

        // ACF
        $version = get_field('datos_generales_version', $car_id);
        $precio  = get_field('precio_y_descuentos_precio', $car_id);
        $cuota   = get_field('financiacion_cuota_minima', $car_id);

        $precio_num = is_numeric($precio) ? (float) $precio : 0.0;
        $precio_formateado = $precio_num ? number_format($precio_num, 0, ',', '.') . ' €' : '';
        $cuota_num = is_numeric($cuota) ? (float) $cuota : 0.0;
        $cuota_formateada = $cuota_num ? number_format($cuota_num, 0, ',', '.') . '€' : '';

        // Portada
        $image_id = get_field('otros_datos_portada_coche', $car_id);
        if (!$image_id) {
            $image_id = get_field('portada_coche', $car_id);
        }
        if (!$image_id) {
            $image_id = get_post_thumbnail_id($car_id);
        }

        if (is_array($image_id)) {
            $image_id = (int) ($image_id['ID'] ?? $image_id['id'] ?? 0);
        } else {
            $image_id = (int) $image_id;
        }

        $image_url = $image_id ? wp_get_attachment_image_url($image_id, 'gv360_w_980') : '';
        if (!$image_url && $image_id) {
            $image_url = wp_get_attachment_image_url($image_id, 'large');
        }
        if (!$image_url && $image_id) {
            $image_url = wp_get_attachment_image_url($image_id, 'full');
        }
        $image_alt = $image_id ? get_post_meta($image_id, '_wp_attachment_image_alt', true) : '';

        if (empty($image_alt)) {
            $image_alt = trim(sprintf('Foto de %s %s %s', $nombre_marca, $nombre_modelo, (string) $version));
        }

        // Logo marca
        $logo_marca_white_id = $marca_term ? get_field('logo_marca_white', $marca_term) : null;
        $logo_marca_id       = $marca_term ? get_field('logo_marca', $marca_term) : null;
        $logo_marca_shape    = $marca_term ? sanitize_html_class((string) get_field('forma_del_logo', $marca_term)) : '';

        $logo_url = '';
        if (!empty($logo_marca_white_id)) {
            $logo_url = wp_get_attachment_image_url($logo_marca_white_id, 'full');
        } elseif (!empty($logo_marca_id)) {
            $logo_url = wp_get_attachment_image_url($logo_marca_id, 'full');
        }

        $hero_cars[] = [
            'id'      => $car_id,
            'link'    => $car_link,
            'marca'   => $nombre_marca,
            'modelo'  => $nombre_modelo,
            'version' => (string) $version,
            'precio'  => $precio_formateado,
            'cuota'   => $cuota_formateada,
            'img'     => $image_url,
            'imgAlt'  => $image_alt,
            'logo'    => $logo_url,
            'logoShape' => $logo_marca_shape ?: 'default',
            'imageId' => $image_id,
        ];
    }
    wp_reset_postdata();
}

$hero_cars_json = wp_json_encode($hero_cars);
$stock_url = get_post_type_archive_link('coche');
if (!$stock_url) {
    $stock_url = home_url('/coches-de-segunda-mano/');
}

$sell_car_page = get_page_by_path('vendemos-tu-coche');
$sell_car_url = $sell_car_page ? get_permalink($sell_car_page) : home_url('/vendemos-tu-coche/');
$method_page = get_page_by_path('metodo-escarpa');
$method_url = $method_page ? get_permalink($method_page) : home_url('/metodo-escarpa/');
$warranty_page = get_page_by_path('coches-de-segunda-mano-con-garantia');
$warranty_url = $warranty_page ? get_permalink($warranty_page) : home_url('/coches-de-segunda-mano-con-garantia/');
$finance_page = get_page_by_path('coches-con-financiacion-segunda-mano');
$finance_url = $finance_page ? get_permalink($finance_page) : home_url('/coches-con-financiacion-segunda-mano/');

$hero_mark_path = 'M22.7,61.8c0.1,0.1,0.2,0.2,0.4,0.2h13.6c3.1,0,5.9-1.8,7.1-4.7l4-9.3c0.1-0.2,0-0.4-0.2-0.5c0,0-0.1,0-0.1,0H24.7c-0.2,0-0.4-0.2-0.4-0.4c0-0.1,0-0.1,0-0.2l1.6-3c0.2-0.3,0.5-0.5,0.9-0.5h17.9c3.1,0,5.9-1.8,7.1-4.7l4-9.2c0.1-0.2,0-0.4-0.2-0.5c0,0-0.1,0-0.1,0H13.3c-0.2,0-0.4-0.2-0.4-0.4c0-0.1,0-0.1,0-0.2l1.6-3c0.2-0.3,0.5-0.5,0.9-0.5h37.4c3.1,0,5.9-1.9,7.1-4.7l4.2-9.7c0.1-0.2,0-0.4-0.2-0.5c0,0-0.1,0-0.1,0h-63c-0.2,0-0.4,0.2-0.4,0.4c0,0,0,0.1,0,0.1L22.7,61.8z';
?>

<main id="main" class="main">
    <section class="home-hero home-hero--front" aria-labelledby="hero-title">
        <div class="home-hero__background" aria-hidden="true">
            <span class="home-hero__wash home-hero__wash--primary"></span>
            <span class="home-hero__wash home-hero__wash--accent"></span>
            <svg class="home-hero__mark home-hero__mark--one" viewBox="0 0 65 64" focusable="false">
                <path d="<?php echo esc_attr($hero_mark_path); ?>"></path>
            </svg>
            <svg class="home-hero__mark home-hero__mark--two" viewBox="0 0 65 64" focusable="false">
                <path d="<?php echo esc_attr($hero_mark_path); ?>"></path>
            </svg>
        </div>

        <div class="home-hero__container">
            <div class="home-hero__content">
                <p class="home-hero__eyebrow">Coches de segunda mano en Madrid, revisados en detalle</p>

                <h1 id="hero-title" class="home-hero__title">
                    El coche que deseas, <span>sin sorpresas.</span>
                </h1>

                <div class="home-hero__copy">
                    <p class="home-hero__lead">
                        En Escarpa Motor hemos creado una forma más clara, cuidada y segura de comprar un coche de segunda mano en Madrid: vehículos revisados, garantía clara, financiación transparente y una entrega preparada al detalle.
                    </p>
                </div>

                <div class="home-hero__actions" aria-label="Acciones principales">
                    <a class="home-hero__button home-hero__button--secondary" href="<?php echo esc_url($sell_car_url); ?>">
                        <span>Vender tu coche</span>
                    </a>
                    <a class="home-hero__button home-hero__button--primary" href="<?php echo esc_url($stock_url); ?>">
                        <span>Ver coches disponibles</span>
                        <svg viewBox="0 0 24 24" aria-hidden="true">
                            <path d="M5 12h12.17l-4.58-4.59L14 6l7 7-7 7-1.41-1.41L17.17 14H5v-2Z"></path>
                        </svg>
                    </a>
                </div>
            </div>

            <div class="home-hero__media">
                <div class="home-hero__gallery" data-home-hero-cars='<?php echo esc_attr($hero_cars_json); ?>'>
                    <div class="home-hero__gallery-frame">
                        <div class="home-hero__viewport" aria-label="Galería de vehículos recientes" role="group" tabindex="0">
                            <?php if (!empty($hero_cars)) : ?>
                                <?php foreach ($hero_cars as $index => $car) : ?>
                                    <article class="home-hero__slide <?php echo $index === 0 ? 'is-active' : ''; ?>" data-slide-index="<?php echo esc_attr((string) $index); ?>" aria-hidden="<?php echo $index === 0 ? 'false' : 'true'; ?>">
                                        <?php
                                        $image_args = [
                                            'class'         => 'home-hero__image',
                                            'loading'       => $index === 0 ? 'eager' : 'lazy',
                                            'decoding'      => 'async',
                                            'fetchpriority' => $index === 0 ? 'high' : 'low',
                                            'sizes'         => '(max-width: 767px) 92vw, (max-width: 1199px) 84vw, 48vw',
                                            'alt'           => $car['imgAlt'],
                                        ];
                                        ?>
                                        <?php if (!empty($car['imageId'])) : ?>
                                            <?php echo wp_get_attachment_image((int) $car['imageId'], 'gv360_w_980', false, $image_args); ?>
                                        <?php elseif (!empty($car['img'])) : ?>
                                            <img class="home-hero__image" src="<?php echo esc_url($car['img']); ?>" alt="<?php echo esc_attr($car['imgAlt']); ?>" loading="<?php echo $index === 0 ? 'eager' : 'lazy'; ?>" decoding="async" fetchpriority="<?php echo $index === 0 ? 'high' : 'low'; ?>">
                                        <?php else : ?>
                                            <img class="home-hero__image" src="<?php echo esc_url(get_template_directory_uri() . '/public/assets/images/defaults/presentacion_azul.png'); ?>" alt="Coche de segunda mano preparado para entrega" loading="<?php echo $index === 0 ? 'eager' : 'lazy'; ?>" decoding="async">
                                        <?php endif; ?>
                                    </article>
                                <?php endforeach; ?>
                            <?php else : ?>
                                <article class="home-hero__slide is-active" data-slide-index="0" aria-hidden="false">
                                    <img class="home-hero__image" src="<?php echo esc_url(get_template_directory_uri() . '/public/assets/images/defaults/presentacion_azul.png'); ?>" alt="Coche de segunda mano preparado para entrega" loading="eager" decoding="async">
                                </article>
                            <?php endif; ?>
                            <div class="home-hero__image-shade" aria-hidden="true"></div>

                            <?php $active_car = $hero_cars[0] ?? null; ?>
                            <div class="home-hero__vehicle-panel" aria-live="polite" aria-atomic="true">
                                <div class="home-hero__vehicle-main">
                                    <?php $active_logo_shape = !empty($active_car['logoShape']) ? sanitize_html_class((string) $active_car['logoShape']) : 'default'; ?>
                                    <div class="home-hero__vehicle-logo home-hero__vehicle-logo--<?php echo esc_attr($active_logo_shape); ?>" aria-hidden="true">
                                        <?php if (!empty($active_car['logo'])) : ?>
                                            <img src="<?php echo esc_url($active_car['logo']); ?>" alt="" loading="lazy" decoding="async">
                                        <?php else : ?>
                                            <span><?php echo esc_html(substr((string) ($active_car['marca'] ?? 'E'), 0, 1)); ?></span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="home-hero__vehicle-copy">
                                        <strong class="home-hero__vehicle-title"><?php echo esc_html(trim(($active_car['marca'] ?? '') . ' ' . ($active_car['modelo'] ?? 'Vehículo destacado'))); ?></strong>
                                        <span class="home-hero__vehicle-subtitle"><?php echo esc_html($active_car['version'] ?? 'Stock revisado y actualizado'); ?></span>
                                    </div>
                                </div>

                                <div class="home-hero__vehicle-side">
                                    <div class="home-hero__vehicle-price-wrap">
                                        <?php if (!empty($active_car['cuota'])) : ?>
                                            <strong class="home-hero__vehicle-price"><span><?php echo esc_html($active_car['cuota']); ?></span><small>/mes</small></strong>
                                        <?php else : ?>
                                            <strong class="home-hero__vehicle-price home-hero__vehicle-price--fallback">Financiación a medida</strong>
                                        <?php endif; ?>
                                    </div>
                                    <a class="home-hero__vehicle-link" href="<?php echo esc_url($active_car['link'] ?? $stock_url); ?>">
                                        <span>Ver ficha</span>
                                        <svg viewBox="0 0 24 24" aria-hidden="true">
                                            <path d="M14 3h7v7h-2V6.41l-9.29 9.3-1.42-1.42 9.3-9.29H14V3ZM5 5h6v2H7v10h10v-4h2v6H5V5Z"></path>
                                        </svg>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="home-hero__gallery-controls" aria-label="Controles de vehículos destacados">
                        <button class="home-hero__control home-hero__control--prev" type="button" aria-label="Vehículo anterior">
                            <svg viewBox="0 0 24 24" aria-hidden="true">
                                <path d="m15.4 7.4-1.4-1.4-6 6 6 6 1.4-1.4-4.6-4.6 4.6-4.6Z"></path>
                            </svg>
                        </button>
                        <button class="home-hero__control home-hero__control--next" type="button" aria-label="Vehículo siguiente">
                            <svg viewBox="0 0 24 24" aria-hidden="true">
                                <path d="m8.6 16.6 1.4 1.4 6-6-6-6-1.4 1.4 4.6 4.6-4.6 4.6Z"></path>
                            </svg>
                        </button>
                        <button class="home-hero__control home-hero__control--toggle" type="button" aria-label="Pausar galería" aria-pressed="false">
                            <svg viewBox="0 0 24 24" aria-hidden="true">
                                <path d="M6 5h4v14H6V5Zm8 0h4v14h-4V5Z"></path>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            <div class="home-hero__trust" aria-label="Compromisos de Escarpa Motor">
                <a class="home-hero__trust-item" href="<?php echo esc_url($method_url); ?>">
                    <span class="home-hero__trust-icon" aria-hidden="true">
                        <svg viewBox="0 0 64 64">
                            <path d="M19 9h23l9 9v36H19z"></path>
                            <path d="M42 9v10h10"></path>
                            <path d="M27 30h18"></path>
                            <path d="M27 39h10"></path>
                            <path class="home-hero__trust-check" d="m18 39 6 6 13-15"></path>
                        </svg>
                    </span>
                    <div>
                        <strong>Revisión documentada</strong>
                        <span>Estado, historial y preparación visibles desde el primer contacto.</span>
                    </div>
                </a>
                <a class="home-hero__trust-item" href="<?php echo esc_url($warranty_url); ?>">
                    <span class="home-hero__trust-icon" aria-hidden="true">
                        <svg viewBox="0 0 64 64">
                            <path d="M32 8 14 15v14c0 12 7.6 22.6 18 27 10.4-4.4 18-15 18-27V15z"></path>
                            <path class="home-hero__trust-check" d="m23 32 6 6 13-15"></path>
                        </svg>
                    </span>
                    <div>
                        <strong>Garantía clara</strong>
                        <span>Cobertura explicada antes de reservar, con la misma claridad que el precio.</span>
                    </div>
                </a>
                <a class="home-hero__trust-item" href="<?php echo esc_url($finance_url); ?>">
                    <span class="home-hero__trust-icon" aria-hidden="true">
                        <svg viewBox="0 0 64 64">
                            <path d="M10 20h44v28H10z"></path>
                            <path d="M16 28h14"></path>
                            <path d="M16 38h22"></path>
                            <path d="M44 29c4 0 7 3 7 7s-3 7-7 7-7-3-7-7 3-7 7-7z"></path>
                            <path class="home-hero__trust-check" d="m40 36 3 3 6-7"></path>
                        </svg>
                    </span>
                    <div>
                        <strong>Financiación transparente</strong>
                        <span>Cuotas y condiciones explicadas con números claros antes de firmar.</span>
                    </div>
                </a>
            </div>
        </div>
    </section>

    <!-- SERVICIOS DESTACADOS -->
    <section class="featured-services" aria-labelledby="services-title">
        <div class="featured-services__container">
            <div class="featured-services__header">
                <h2 id="services-title" class="featured-services__title">Servicios para comprar tu coche de segunda mano en Madrid</h2>
                <p class="featured-services__description">
                    En Escarpa Motor te acompañamos antes, durante y después de la compra con procesos pensados para que todo sea claro desde el primer momento.
                </p>
            </div>

            <div class="featured-services__grid">
                <article class="service-card">
                    <div class="service-card__icon">
                        <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="currentColor">
                            <path d="M480-80q-82 0-155-31.5t-127.5-86Q143-252 111.5-325T80-480q0-83 31.5-155.5t86-127Q252-817 325-848.5T480-880q83 0 155.5 31.5t127 86q54.5 54.5 86 127T880-480q0 82-31.5 155t-86 127.5q-54.5 54.5-127 86T480-80Zm0-240q60 0 117 17.5T704-252q46-46 71-104.5T800-480q0-134-93-227t-227-93q-134 0-227 93t-93 227q0 65 24.5 124T256-252q50-33 107-50.5T480-320Zm0 80q-41 0-80 10t-74 30q35 20 74 30t80 10q41 0 80-10t74-30q-35-20-74-30t-80-10ZM280-520q17 0 28.5-11.5T320-560q0-17-11.5-28.5T280-600q-17 0-28.5 11.5T240-560q0 17 11.5 28.5T280-520Zm120-120q17 0 28.5-11.5T440-680q0-17-11.5-28.5T400-720q-17 0-28.5 11.5T360-680q0 17 11.5 28.5T400-640Zm280 120q17 0 28.5-11.5T720-560q0-17-11.5-28.5T680-600q-17 0-28.5 11.5T640-560q0 17 11.5 28.5T680-520ZM480-400q33 0 56.5-23.5T560-480q0-13-4-25.5T544-528l54-136q7-16 .5-31.5T576-718q-15-7-30.5-.5T524-696l-54 136q-30 5-50 27.5T400-480q0 33 23.5 56.5T480-400Zm0 80Zm0-206Zm0 286Z"></path>
                        </svg>
                    </div>
                    <h3 class="service-card__title">Historial y kilómetros claros</h3>
                    <p class="service-card__description">
                        Revisamos la documentación disponible, el kilometraje y el estado real del vehículo para que puedas decidir con información fiable.
                    </p>
                </article>

                <article class="service-card">
                    <div class="service-card__icon">
                        <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#000000">
                            <path d="M440-80v-120H160v-80h640v80H520v120h-80Zm-51.5-431.5Q400-523 400-540t-11.5-28.5Q377-580 360-580t-28.5 11.5Q320-557 320-540t11.5 28.5Q343-500 360-500t28.5-11.5Zm240 0Q640-523 640-540t-11.5-28.5Q617-580 600-580t-28.5 11.5Q560-557 560-540t11.5 28.5Q583-500 600-500t28.5-11.5ZM200-616l66-192q5-14 16.5-23t25.5-9h344q14 0 25.5 9t16.5 23l66 192v264q0 14-9 23t-23 9h-16q-14 0-23-9t-9-23v-48H280v48q0 14-9 23t-23 9h-16q-14 0-23-9t-9-23v-264Zm106-64h348l-28-80H334l-28 80Zm-26 80v120-120Zm0 120h400v-120H280v120Z" />
                        </svg>
                    </div>
                    <h3 class="service-card__title">Revisión y preparación</h3>
                    <p class="service-card__description">
                        Cada coche pasa por una preparación cuidada de mecánica, seguridad, limpieza y entrega para que llegue a tus manos como debe.
                    </p>
                </article>

                <article class="service-card">
                    <div class="service-card__icon">
                        <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#000000">
                            <path d="m387-412 35-114-92-74h114l36-112 36 112h114l-93 74 35 114-92-71-93 71ZM240-40v-309q-38-42-59-96t-21-115q0-134 93-227t227-93q134 0 227 93t93 227q0 61-21 115t-59 96v309l-240-80-240 80Zm410-350q70-70 70-170t-70-170q-70-70-170-70t-170 70q-70 70-70 170t70 170q70 70 170 70t170-70ZM320-159l160-41 160 41v-124q-35 20-75.5 31.5T480-240q-44 0-84.5-11.5T320-283v124Zm160-62Z" />
                        </svg>
                    </div>
                    <h3 class="service-card__title">Garantía y financiación</h3>
                    <p class="service-card__description">
                        Te explicamos la garantía y las opciones de financiación con números claros, sin compromisos que no entiendas antes de firmar.
                    </p>
                </article>
            </div>

            <div class="featured-services__footer">
                <a href="<?php echo esc_url($stock_url); ?>" class="featured-services__link items-center justify-center button">
                    <span>Ver stock disponible</span>
                    <?php echo E360VO_Icon::get('chevron-right', array('class' => 'flex justify-center  items-center')); ?>
                </a>
            </div>
        </div>
    </section>



    <?php
    // Loop principal de la página
    if (have_posts()) :
        while (have_posts()) : the_post();
            the_content();
        endwhile;
    endif;
    ?>
</main>

<?php get_footer(); ?>
