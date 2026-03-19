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

        $precio_num = is_numeric($precio) ? (float) $precio : 0.0;
        $precio_formateado = $precio_num ? number_format($precio_num, 0, ',', '.') . ' €' : '';

        // Portada
        $image_id = get_field('otros_datos_portada_coche', $car_id);
        if (!$image_id) {
            $image_id = get_field('portada_coche', $car_id);
        }
        if (!$image_id) {
            $image_id = get_post_thumbnail_id($car_id);
        }

        $image_url = $image_id ? wp_get_attachment_image_url($image_id, 'full') : '';
        $image_alt = $image_id ? get_post_meta($image_id, '_wp_attachment_image_alt', true) : '';

        if (empty($image_alt)) {
            $image_alt = trim(sprintf('Foto de %s %s %s', $nombre_marca, $nombre_modelo, (string) $version));
        }

        // Logo marca
        $logo_marca_white_id = $marca_term ? get_field('logo_marca_white', $marca_term) : null;
        $logo_marca_id       = $marca_term ? get_field('logo_marca', $marca_term) : null;

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
            'img'     => $image_url,
            'imgAlt'  => $image_alt,
            'logo'    => $logo_url,
        ];
    }
    wp_reset_postdata();
}

$hero_cars_json = wp_json_encode($hero_cars);
?>

<main id="main" class="main">
    <!-- Hero Section -->

    <!-- BOTÓN DE SCROLL MEJORADO -->
    <button class="hero-section__scroll-indicator" aria-label="Desplazar hacia abajo para ver más contenido">
        <svg viewBox="0 0 24 24" aria-hidden="true">
            <path d="M7.41 8.59L12 13.17l4.59-4.58L18 10l-6 6-6-6 1.41-1.41z" />
        </svg>
    </button>
    <section class="hero-section" aria-labelledby="hero-title">
        <div class="hero-section__background" aria-hidden="true">
            <div class="hero-section__gradient"></div>
            <div class="hero-section__pattern"></div>
        </div>

        <div class="hero-section__container">
            <!-- Visual: slider de últimos coches -->
            <div class="hero-section__visual">
                <div class="hero-section__image-wrapper" data-hero-cars='<?php echo esc_attr($hero_cars_json); ?>'>
                    <div class="hero-section__slider" aria-label="Galería de vehículos recientes" role="group" tabindex="0">
                        <?php if (!empty($hero_cars)) : ?>
                            <?php foreach ($hero_cars as $index => $car) : ?>
                                <div class="hero-section__slide <?php echo $index === 0 ? 'is-active' : ''; ?>" data-slide-index="<?php echo esc_attr((string)$index); ?>">
                                    <?php if (!empty($car['img'])) : ?>
                                        <img
                                            src="<?php echo esc_url($car['img']); ?>"
                                            alt="<?php echo esc_attr($car['imgAlt']); ?>"
                                            loading="<?php echo $index === 0 ? 'eager' : 'lazy'; ?>"
                                            decoding="async"
                                            fetchpriority="<?php echo $index === 0 ? 'high' : 'low'; ?>" />
                                    <?php else : ?>
                                        <img
                                            src="<?php echo esc_url(get_template_directory_uri() . '/public/assets/images/defaults/presentacion_azul.png'); ?>"
                                            alt="Vehículo de segunda mano en exposición"
                                            loading="<?php echo $index === 0 ? 'eager' : 'lazy'; ?>"
                                            decoding="async" />
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        <?php else : ?>
                            <div class="hero-section__slide is-active" data-slide-index="0">
                                <img
                                    src="https://www.stoic-archimedes.31-170-100-104.plesk.page/wp-content/uploads/2026/02/WhatsApp-Image-2025-07-02-at-09.16.05.jpeg"
                                    alt="Vehículo premium en exposición - Concesionario EdreamsCars Alicante"
                                    loading="eager"
                                    decoding="async" />
                            </div>
                        <?php endif; ?>
                        <div class="hero-section__image-overlay"></div>

                        <!-- Controles -->
                        <div class="hero-section__slider-controls" aria-hidden="false">
                            <button class="hero-section__slider-btn hero-section__slider-btn--prev" type="button" aria-label="Coche anterior">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" aria-hidden="true">
                                    <path d="M15.41 7.41 14 6l-6 6 6 6 1.41-1.41L10.83 12z"></path>
                                </svg>
                            </button>
                            <button class="hero-section__slider-btn hero-section__slider-btn--next" type="button" aria-label="Coche siguiente">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" aria-hidden="true">
                                    <path d="m8.59 16.59 1.41 1.41 6-6-6-6-1.41 1.41L13.17 12z"></path>
                                </svg>
                            </button>

                            <button class="hero-section__slider-toggle" type="button" aria-label="Pausar reproducción" aria-pressed="false">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" aria-hidden="true">
                                    <path d="M6 19h4V5H6v14zm8-14v14h4V5h-4z"></path>
                                </svg>
                            </button>
                        </div>

                        <!-- Badge dinámico dividido -->
                        <div class="hero-section__car-badge" aria-live="polite" aria-atomic="true">
                            <?php if (!empty($hero_cars) && count($hero_cars) > 1) : ?>
                                <div class="hero-section__dots" aria-label="Selector de coche">
                                    <?php foreach ($hero_cars as $i => $_car) : ?>
                                        <button
                                            type="button"
                                            class="hero-section__dot"
                                            data-dot-index="<?php echo esc_attr((string)$i); ?>"
                                            aria-label="<?php echo esc_attr('Ir al coche ' . ($i + 1)); ?>"
                                            aria-current="<?php echo $i === 0 ? 'true' : 'false'; ?>"></button>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>

                            <div class="hero-section__car-badge-content">
                                <!-- Parte izquierda: Logo e información del coche -->
                                <div class="hero-section__car-info">
                                    <div class="hero-section__car-logo" aria-hidden="true">
                                        <img src="" alt="" />
                                    </div>
                                    <div class="hero-section__car-text">
                                        <span class="hero-section__car-title"></span>
                                        <span class="hero-section__car-subtitle"></span>
                                    </div>
                                </div>

                                <!-- Parte derecha: Precio y CTA -->
                                <div class="hero-section__car-actions">
                                    <div class="hero-section__car-price-wrap">
                                        <div class="hero-section__car-price"></div>
                                        <div class="hero-section__car-price-note">Financiado</div>
                                    </div>

                                    <a class="hero-section__car-cta" href="#" aria-label="Ver ficha del vehículo">
                                        <span>Ver ficha</span>
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" aria-hidden="true">
                                            <path d="M14 3h7v7h-2V6.41l-9.29 9.3-1.42-1.42 9.3-9.29H14V3zM5 5h6v2H7v10h10v-4h2v6H5V5z" />
                                        </svg>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Features debajo de la imagen en desktop -->
                <div class="hero-section__features">
                    <div class="hero-section__feature">
                        <svg class="hero-section__feature-icon" xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#000000">
                            <path d="M824-80 716-188q-22 13-46 20.5t-50 7.5q-75 0-127.5-52.5T440-340q0-75 52.5-127.5T620-520q75 0 127.5 52.5T800-340q0 26-7.5 50T772-244l108 108-56 56ZM691-269q29-29 29-71t-29-71q-29-29-71-29t-71 29q-29 29-29 71t29 71q29 29 71 29t71-29Zm149-291h-80v-200h-80v120H280v-120h-80v560h200v80H200q-33 0-56.5-23.5T120-200v-560q0-33 23.5-56.5T200-840h167q11-35 43-57.5t70-22.5q40 0 71.5 22.5T594-840h166q33 0 56.5 23.5T840-760v200ZM508.5-771.5Q520-783 520-800t-11.5-28.5Q497-840 480-840t-28.5 11.5Q440-817 440-800t11.5 28.5Q463-760 480-760t28.5-11.5Z" />
                        </svg>

                        <div>
                            <div class="hero-section__feature-title">Revisión completa</div>
                            <div class="hero-section__feature-desc">Proceso profesional</div>
                        </div>
                    </div>

                    <div class="hero-section__feature">
                        <svg class="hero-section__feature-icon" xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="currentColor">
                            <path d="M480-80q-139-35-229.5-159.5T160-516v-244l320-120 320 120v244q0 152-90.5 276.5T480-80Zm0-84q104-33 172-132t68-220v-189l-240-90-240 90v189q0 121 68 220t172 132Zm0-316Z" />
                        </svg>
                        <div>
                            <div class="hero-section__feature-title">Garantía</div>
                            <div class="hero-section__feature-desc">Hasta 3 años</div>
                        </div>
                    </div>

                    <div class="hero-section__feature">
                        <svg class="hero-section__feature-icon" xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="currentColor">
                            <path d="M480-80q-82 0-155-31.5t-127.5-86Q143-252 111.5-325T80-480q0-83 31.5-155.5t86-127Q252-817 325-848.5T480-880q83 0 155.5 31.5t127 86q54.5 54.5 86 127T880-480q0 82-31.5 155t-86 127.5q-54.5 54.5-127 86T480-80Zm0-240q60 0 117 17.5T704-252q46-46 71-104.5T800-480q0-134-93-227t-227-93q-134 0-227 93t-93 227q0 65 24.5 124T256-252q50-33 107-50.5T480-320Zm0 80q-41 0-80 10t-74 30q35 20 74 30t80 10q41 0 80-10t74-30q-35-20-74-30t-80-10ZM280-520q17 0 28.5-11.5T320-560q0-17-11.5-28.5T280-600q-17 0-28.5 11.5T240-560q0 17 11.5 28.5T280-520Zm120-120q17 0 28.5-11.5T440-680q0-17-11.5-28.5T400-720q-17 0-28.5 11.5T360-680q0 17 11.5 28.5T400-640Zm280 120q17 0 28.5-11.5T720-560q0-17-11.5-28.5T680-600q-17 0-28.5 11.5T640-560q0 17 11.5 28.5T680-520ZM480-400q33 0 56.5-23.5T560-480q0-13-4-25.5T544-528l54-136q7-16 .5-31.5T576-718q-15-7-30.5-.5T524-696l-54 136q-30 5-50 27.5T400-480q0 33 23.5 56.5T480-400Zm0 80Zm0-206Zm0 286Z"></path>
                        </svg>
                        <div>
                            <div class="hero-section__feature-title">Historial verificado</div>
                            <div class="hero-section__feature-desc">Kilómetros certificados</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Contenido - Segundo en móvil -->
            <div class="hero-section__content">
                <div class="hero-section__badge">
                    <svg class="hero-section__badge-icon" xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#000000">
                        <path d="M160-160v-640 292-12 360Zm40-356v264q0 14 9 23t23 9h16q14 0 23-9t9-23v-48h167q5-22 12.5-41.5T478-380H280v-120h336q34-14 71-18t73 1l-66-191q-5-14-16.5-23t-25.5-9H308q-14 0-25.5 9T266-708l-66 192Zm106-64 28-80h292l28 80H306Zm82.5 168.5Q400-423 400-440t-11.5-28.5Q377-480 360-480t-28.5 11.5Q320-457 320-440t11.5 28.5Q343-400 360-400t28.5-11.5ZM692-150l142-142-30-30-112 112-56-56-30 30 86 86Zm169.5-231.5Q920-323 920-240T861.5-98.5Q803-40 720-40T578.5-98.5Q520-157 520-240t58.5-141.5Q637-440 720-440t141.5 58.5ZM160-80q-33 0-56.5-23.5T80-160v-640q0-33 23.5-56.5T160-880h640q33 0 56.5 23.5T880-800v331q-18-13-38-22.5T800-508v-292H160v640h292q7 22 16.5 42T491-80H160Z" />
                    </svg>
                    <span>Concesionario certificado</span>
                </div>

                <h1 id="hero-title" class="hero-section__title">
                    <span class="hero-section__title-highlight">Concesionario</span> de confianza en Alicante
                </h1>

                <p class="hero-section__description">
                    En EdreamsCars te ayudamos a encontrar el coche que encaja contigo.
                    Vehículos de segunda mano revisados, con <strong>kilómetros certificados</strong>
                    y <strong>garantía de hasta 3 años</strong>.
                    <em>Lujo accesible, calidad entera.</em>
                </p>

                <div class="hero-section__stats">
                    <div class="hero-section__stat">
                        <div class="hero-section__stat-number">1er año</div>
                        <div class="hero-section__stat-label">Mantenimiento gratis</div>
                    </div>
                    <div class="hero-section__stat">
                        <div class="hero-section__stat-number">100%</div>
                        <div class="hero-section__stat-label">Kilómetros certificados</div>
                    </div>
                    <div class="hero-section__stat">
                        <div class="hero-section__stat-number">3 años</div>
                        <div class="hero-section__stat-label">Garantía incluida</div>
                    </div>
                </div>

                <div class="hero-section__cta-group">
                    <a href="https://www.stoic-archimedes.31-170-100-104.plesk.page/coches-baratos-segunda-mano/"
                        class="hero-section__cta hero-section__cta--secondary"
                        aria-label="Vender mi coche a EdreamsCars">
                        <span>Compramos tu coche</span>
                        <svg class="hero-section__cta-icon" xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="currentColor">
                            <path d="M200-120q-33 0-56.5-23.5T120-200v-560q0-33 23.5-56.5T200-840h280v80H200v560h560v-280h80v280q0 33-23.5 56.5T760-120H200Zm188-212-56-56 372-372H560v-80h280v280h-80v-144L388-332Z"></path>
                        </svg>
                    </a>
                    <a href="https://www.stoic-archimedes.31-170-100-104.plesk.page/coches-baratos-segunda-mano/"
                        class="hero-section__cta hero-section__cta--primary"
                        aria-label="Ver todos nuestros vehículos disponibles">
                        <span>Ver vehículos</span>
                        <svg class="hero-section__cta-icon" xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="currentColor">
                            <path d="M200-120q-33 0-56.5-23.5T120-200v-560q0-33 23.5-56.5T200-840h280v80H200v560h560v-280h80v280q0 33-23.5 56.5T760-120H200Zm188-212-56-56 372-372H560v-80h280v280h-80v-144L388-332Z"></path>
                        </svg>
                    </a>
                </div>
            </div>
        </div>


    </section>

    <!-- SERVICIOS DESTACADOS -->
    <section class="featured-services" aria-labelledby="services-title">
        <div class="featured-services__container">
            <div class="featured-services__header">
                <h2 id="services-title" class="featured-services__title">Coches segunda mano Alicante: nuestros servicios</h2>
                <p class="featured-services__description">
                    En EdreamsCars no solo vendemos coches, te acompañamos durante todo el proceso de compra
                    con servicios diseñados para tu tranquilidad y satisfacción.
                </p>
            </div>

            <div class="featured-services__grid">
                <article class="service-card">
                    <div class="service-card__icon">
                        <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="currentColor">
                            <path d="M480-80q-82 0-155-31.5t-127.5-86Q143-252 111.5-325T80-480q0-83 31.5-155.5t86-127Q252-817 325-848.5T480-880q83 0 155.5 31.5t127 86q54.5 54.5 86 127T880-480q0 82-31.5 155t-86 127.5q-54.5 54.5-127 86T480-80Zm0-240q60 0 117 17.5T704-252q46-46 71-104.5T800-480q0-134-93-227t-227-93q-134 0-227 93t-93 227q0 65 24.5 124T256-252q50-33 107-50.5T480-320Zm0 80q-41 0-80 10t-74 30q35 20 74 30t80 10q41 0 80-10t74-30q-35-20-74-30t-80-10ZM280-520q17 0 28.5-11.5T320-560q0-17-11.5-28.5T280-600q-17 0-28.5 11.5T240-560q0 17 11.5 28.5T280-520Zm120-120q17 0 28.5-11.5T440-680q0-17-11.5-28.5T400-720q-17 0-28.5 11.5T360-680q0 17 11.5 28.5T400-640Zm280 120q17 0 28.5-11.5T720-560q0-17-11.5-28.5T680-600q-17 0-28.5 11.5T640-560q0 17 11.5 28.5T680-520ZM480-400q33 0 56.5-23.5T560-480q0-13-4-25.5T544-528l54-136q7-16 .5-31.5T576-718q-15-7-30.5-.5T524-696l-54 136q-30 5-50 27.5T400-480q0 33 23.5 56.5T480-400Zm0 80Zm0-206Zm0 286Z"></path>
                        </svg>
                    </div>
                    <h3 class="service-card__title">Kilómetros certificados</h3>
                    <p class="service-card__description">
                        Transparencia total con historial verificado y kilómetros certificados mediante
                        sistemas oficiales. Tu tranquilidad es nuestra prioridad.
                    </p>
                </article>

                <article class="service-card">
                    <div class="service-card__icon">
                        <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#000000">
                            <path d="M440-80v-120H160v-80h640v80H520v120h-80Zm-51.5-431.5Q400-523 400-540t-11.5-28.5Q377-580 360-580t-28.5 11.5Q320-557 320-540t11.5 28.5Q343-500 360-500t28.5-11.5Zm240 0Q640-523 640-540t-11.5-28.5Q617-580 600-580t-28.5 11.5Q560-557 560-540t11.5 28.5Q583-500 600-500t28.5-11.5ZM200-616l66-192q5-14 16.5-23t25.5-9h344q14 0 25.5 9t16.5 23l66 192v264q0 14-9 23t-23 9h-16q-14 0-23-9t-9-23v-48H280v48q0 14-9 23t-23 9h-16q-14 0-23-9t-9-23v-264Zm106-64h348l-28-80H334l-28 80Zm-26 80v120-120Zm0 120h400v-120H280v120Z" />
                        </svg>
                    </div>
                    <h3 class="service-card__title">Revisiones completas</h3>
                    <p class="service-card__description">
                        142 puntos de revisión en motor, suspensión, frenos, electrónica y seguridad.
                        Taller propio con certificación oficial.
                    </p>
                </article>

                <article class="service-card">
                    <div class="service-card__icon">
                        <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#000000">
                            <path d="m387-412 35-114-92-74h114l36-112 36 112h114l-93 74 35 114-92-71-93 71ZM240-40v-309q-38-42-59-96t-21-115q0-134 93-227t227-93q134 0 227 93t93 227q0 61-21 115t-59 96v309l-240-80-240 80Zm410-350q70-70 70-170t-70-170q-70-70-170-70t-170 70q-70 70-70 170t70 170q70 70 170 70t170-70ZM320-159l160-41 160 41v-124q-35 20-75.5 31.5T480-240q-44 0-84.5-11.5T320-283v124Zm160-62Z" />
                        </svg>
                    </div>
                    <h3 class="service-card__title">Garantía 3 años</h3>
                    <p class="service-card__description">
                        Cobertura extendida que protege tu inversión. Disfruta de tu coche con la seguridad
                        de estar respaldado por profesionales.
                    </p>
                </article>
            </div>

            <div class="featured-services__footer">
                <a href="https://www.stoic-archimedes.31-170-100-104.plesk.page/importar-coches/" class="featured-services__link items-center justify-center button">
                    <span>Descubre todos nuestros servicios</span>
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