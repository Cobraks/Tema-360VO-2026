<?php

/**
 * Single post template (Blog)
 * @package 360vo-theme
 */

if (!defined('ABSPATH')) {
    exit;
}

get_header();

if (!function_exists('th360_reading_time_label')) {
    function th360_reading_time_label(int $post_id): string
    {
        $p = get_post($post_id);
        if (!$p) return '';
        $words   = str_word_count(wp_strip_all_tags((string) $p->post_content));
        $minutes = max(1, (int) ceil($words / 200));
        return $minutes . ' min';
    }
}

if (!function_exists('th360_get_image_caption')) {
    function th360_get_image_caption(int $post_id): string
    {
        $thumbnail_id = (int) get_post_thumbnail_id($post_id);
        if (!$thumbnail_id) return '';
        $caption = wp_get_attachment_caption($thumbnail_id);
        return $caption ? (string) $caption : '';
    }
}

$post_id = (int) get_the_ID();

$blog_url = function_exists('th360_get_blog_home_url')
    ? th360_get_blog_home_url()
    : home_url('/noticias/');
$permalink = get_permalink($post_id);

$blog_context = function_exists('th360_get_post_blog_context')
    ? th360_get_post_blog_context($post_id)
    : [];

$primary_cat = $blog_context['category'] ?? null;
$cat_name    = (string) ($blog_context['category_name'] ?? '');
$cat_url     = (string) ($blog_context['category_url'] ?? '');

$title_override = '';
$intro_override = '';
if (function_exists('get_field')) {
    $title_override = (string) get_field('titulo_h1');
    $intro_override = (string) get_field('parrafo_introduccion');
}

$title = trim($title_override) !== '' ? $title_override : get_the_title($post_id);
$intro = trim($intro_override) !== '' ? $intro_override : get_the_excerpt($post_id);

$title_allowed = [
    'span'   => ['class' => true],
    'br'     => true,
    'em'     => true,
    'strong' => true,
];
$title_safe = wp_kses($title, $title_allowed);
$intro_safe = wp_kses_post(wpautop($intro));

$reading = th360_reading_time_label($post_id);
$caption = th360_get_image_caption($post_id);
$activar_toc = true;
$is_brand_mode = !empty($blog_context['is_brand_mode']);
$selected_brand = $blog_context['brand'] ?? null;
$brand_name = ($selected_brand instanceof WP_Term && !empty($selected_brand->name))
    ? (string) $selected_brand->name
    : '';
$brand_slug = ($selected_brand instanceof WP_Term && !empty($selected_brand->slug))
    ? (string) $selected_brand->slug
    : '';
$brand_archive_url = (string) ($blog_context['brand_url'] ?? '');
$brand_stock_url = $brand_slug !== ''
    ? home_url('/stock/' . $brand_slug . '/')
    : '#stock-marca';
$brand_visual = function_exists('th360_get_brand_visual_data')
    ? th360_get_brand_visual_data($selected_brand instanceof WP_Term ? $selected_brand : null, true)
    : ['logo_id' => 0, 'shape' => 'circular', 'alt' => $brand_name, 'title' => $brand_name];
$brand_logo_id = (int) ($brand_visual['logo_id'] ?? 0);
$brand_logo_shape = (string) ($brand_visual['shape'] ?? 'circular');
$brand_logo_alt = (string) ($brand_visual['alt'] ?? $brand_name);
$brand_logo_title = (string) ($brand_visual['title'] ?? $brand_name);
$stock_complement = function_exists('th360_get_stock_complement')
    ? th360_get_stock_complement()
    : '';
$site_name = trim((string) get_bloginfo('name'));
$brand_summary = trim(implode(' ', array_filter([$brand_name, $stock_complement])));

if ($brand_summary !== '' && $site_name !== '') {
    $brand_summary .= ' en ' . $site_name;
} elseif ($brand_summary === '') {
    $brand_summary = $brand_name;
}

$excerpt_links = [];
if ($cat_name !== '' && $cat_url !== '') {
    $excerpt_links[] = sprintf(
        '<a class="meta__chip meta__chip--inline" href="%1$s">%2$s</a>',
        esc_url($cat_url),
        esc_html($cat_name)
    );
}

if ($is_brand_mode && $brand_name !== '' && $brand_archive_url !== '') {
    $excerpt_links[] = sprintf(
        '<a class="meta__chip meta__chip--inline" href="%1$s">%2$s</a>',
        esc_url($brand_archive_url),
        esc_html($brand_name)
    );
}

$excerpt_links_markup = '';
if (!empty($excerpt_links)) {
    $excerpt_links_markup = '<span class="meta__chip-group">' . implode(
        '<span class="meta__chip-separator" aria-hidden="true">|</span>',
        $excerpt_links
    ) . '</span>';
}

$excerpt_html = '';
if (trim(wp_strip_all_tags($intro)) !== '') {
    $excerpt_html = $intro_safe;

    if ($excerpt_links_markup !== '') {
        $last_paragraph_pos = strripos($excerpt_html, '</p>');
        if ($last_paragraph_pos !== false) {
            $excerpt_html = substr_replace($excerpt_html, ' ' . $excerpt_links_markup . '</p>', $last_paragraph_pos, 4);
        } else {
            $excerpt_html .= '<p>' . $excerpt_links_markup . '</p>';
        }
    }
} elseif ($excerpt_links_markup !== '') {
    $excerpt_html = '<p>' . $excerpt_links_markup . '</p>';
}

$related_args = [
    'post_type'              => 'post',
    'post_status'            => 'publish',
    'posts_per_page'         => 3,
    'no_found_rows'          => true,
    'ignore_sticky_posts'    => true,
    'post__not_in'           => [$post_id],
    'update_post_term_cache' => false,
    'update_post_meta_cache' => false,
];
if ($primary_cat && !empty($primary_cat->term_id)) {
    $related_args['cat'] = (int) $primary_cat->term_id;
}
$related = new WP_Query($related_args);
?>

<main class="main main--blog blog" id="main">

    <div
        class="reading-progress"
        data-reading-progress
        role="progressbar"
        aria-label="Progreso de lectura"
        aria-valuemin="0"
        aria-valuemax="100"
        aria-valuenow="0">
        <span class="reading-progress__track" aria-hidden="true">
            <span class="reading-progress__bar" data-reading-progress-bar></span>
        </span>
    </div>

    <header class="post-hero" aria-labelledby="post-title">
        <div class="post-hero__top">
            <div class="single-search" data-single-search>
                <button
                    class="single-search__toggle"
                    type="button"
                    aria-expanded="false"
                    aria-controls="blog-search-panel"
                    aria-label="Abrir búsqueda en noticias">
                    <span class="single-search__toggle-icon single-search__toggle-icon--search" aria-hidden="true"><?php echo E360VO_Icon::get('buscar', ['width' => 20, 'height' => 20]); ?></span>
                    <span class="single-search__toggle-icon single-search__toggle-icon--close" aria-hidden="true"><?php echo E360VO_Icon::get('close', ['width' => 20, 'height' => 20]); ?></span>
                </button>

                <form role="search" method="get" class="search search--single" id="blog-search-panel" action="<?php echo esc_url(home_url('/')); ?>">
                    <label class="sr-only" for="blog-search-single">Buscar en noticias</label>
                    <div class="search__field">
                        <span class="search__icon" aria-hidden="true"><?php echo E360VO_Icon::get('buscar', ['width' => 20, 'height' => 20]); ?></span>
                        <input id="blog-search-single" class="search__input" type="search" name="s" placeholder="Buscar en el blog..." value="" />
                    </div>
                </form>
            </div>
        </div>

        <div class="post-hero__inner">
            <h1 class="post-hero__title" id="post-title"><?php echo $title_safe; ?></h1>

            <div class="post-hero__summary">
                <div class="post-hero__meta" aria-label="Metadatos del articulo">
                    <time class="post-hero__date" datetime="<?php echo esc_attr(get_the_date('c', $post_id)); ?>"><?php echo esc_html(get_the_date('j M, Y', $post_id)); ?></time>
                    <?php if ($reading) : ?><span class="post-hero__reading"><?php echo esc_html($reading); ?> de lectura</span><?php endif; ?>
                </div>

                <?php if ($excerpt_html !== '') : ?>
                    <div class="post-hero__excerpt"><?php echo wp_kses_post($excerpt_html); ?></div>
                <?php endif; ?>
            </div>

            <div class="post-tools" role="group" aria-label="Acciones">
                <button class="icon-btn icon-btn--text" type="button" data-action="save" data-id="<?php echo (int) $post_id; ?>" aria-pressed="false" aria-label="Guardar articulo" title="Guardar">
                    <span class="icon-btn__icon icon-btn__icon--off" aria-hidden="true"><?php echo E360VO_Icon::get('blog_save', ['width' => 22, 'height' => 22]); ?></span>
                    <span class="icon-btn__icon icon-btn__icon--on" aria-hidden="true"><?php echo E360VO_Icon::get('blog_saved', ['width' => 22, 'height' => 22]); ?></span>
                    <span class="icon-btn__label">Guardar</span>
                </button>

                <button class="icon-btn icon-btn--text" type="button" data-action="copy" data-url="<?php echo esc_url($permalink); ?>" aria-label="Copiar enlace" title="Copiar enlace">
                    <span class="icon-btn__icon" aria-hidden="true"><?php echo E360VO_Icon::get('blog_copy', ['width' => 22, 'height' => 22]); ?></span>
                    <span class="icon-btn__label">Copiar enlace</span>
                </button>

                <button class="icon-btn icon-btn--text" type="button" data-action="share" data-url="<?php echo esc_url($permalink); ?>" aria-label="Compartir articulo" title="Compartir">
                    <span class="icon-btn__icon" aria-hidden="true"><?php echo E360VO_Icon::get('blog_share', ['width' => 22, 'height' => 22]); ?></span>
                    <span class="icon-btn__label">Compartir</span>
                </button>
            </div>

        </div>
    </header>

    <?php if (has_post_thumbnail($post_id)) : ?>
        <section class="post-media" aria-label="Imagen destacada">
            <div class="post-media__inner">
                <?php the_post_thumbnail('large', [
                    'loading'       => 'eager',
                    'decoding'      => 'async',
                    'fetchpriority' => 'high',
                    'class'         => 'post-media__img',
                    'alt'           => esc_attr(get_the_title($post_id)),
                ]); ?>

                <?php if ($caption) : ?>
                    <div class="post-media__caption"><?php echo esc_html($caption); ?></div>
                <?php endif; ?>
            </div>
        </section>
    <?php endif; ?>

    <div class="blog-shell">
        <div class="layout layout--single" <?php echo $activar_toc ? 'data-toc-enabled="1"' : ''; ?>>

            <?php if ($is_brand_mode && $brand_name !== '') : ?>
                <section class="brand-highlight brand-highlight--single" data-brand-highlight-single aria-label="<?php echo esc_attr(sprintf('Marca destacada: %s', $brand_name)); ?>">
                    <div class="brand-highlight__main">
                        <div class="brand-highlight__media brand-highlight__media--<?php echo esc_attr($brand_logo_shape); ?>">
                            <?php if ($brand_logo_id > 0) : ?>
                                <?php echo wp_get_attachment_image($brand_logo_id, 'full', false, [
                                    'alt'      => $brand_logo_alt,
                                    'title'    => $brand_logo_title,
                                    'class'    => 'brand-highlight__logo brand-highlight__logo--' . sanitize_html_class($brand_logo_shape),
                                    'loading'  => 'lazy',
                                    'decoding' => 'async',
                                ]); ?>
                            <?php else : ?>
                                <span class="brand-highlight__logo-fallback"><?php echo esc_html(mb_substr($brand_name, 0, 1)); ?></span>
                            <?php endif; ?>
                        </div>

                        <div class="brand-highlight__copy">
                            <h2 class="brand-highlight__title"><?php echo esc_html($brand_name); ?></h2>
                            <p class="brand-highlight__text"><?php echo esc_html($brand_summary); ?></p>
                        </div>
                    </div>

                    <a class="brand-highlight__cta" href="<?php echo esc_url($brand_stock_url); ?>">Ver stock</a>
                </section>
            <?php endif; ?>

            <?php if ($activar_toc) : ?>
                <?php th360_render_table_of_contents([
                    'classes' => ['toc-container--single'],
                    'content_id' => 'toc-content-post-' . $post_id,
                    'label' => 'Navegacion del articulo',
                    'toggle_aria_label' => 'Abrir tabla de contenidos',
                ]); ?>
            <?php endif; ?>

            <article class="post-card" aria-label="Contenido del articulo">

                <div class="entry-content entry-content--start" data-reading-progress-target>
                    <?php
                    while (have_posts()) : the_post();
                        the_content();
                    endwhile;

                    wp_link_pages([
                        'before' => '<nav class="pagination" aria-label="Paginas del articulo"><ul class="pagination__list">',
                        'after'  => '</ul></nav>',
                        'link_before' => '<li class="pagination__item">',
                        'link_after'  => '</li>',
                    ]);
                    ?>
                </div>

                <footer class="post-footer" aria-label="Enlaces del articulo">
                    <?php
                    $tags = get_the_tags($post_id);
                    if (!empty($tags)) :
                        foreach ($tags as $tag) :
                            $tag_link = get_tag_link($tag);
                            if (is_wp_error($tag_link)) continue;
                    ?>
                            <a class="tag-link" href="<?php echo esc_url($tag_link); ?>">#<?php echo esc_html($tag->name); ?></a>
                        <?php
                        endforeach;
                    endif;
                    ?>

                </footer>

                <section class="post-related-inline" aria-label="Articulos relacionados">
                    <div class="post-related-inline__header">
                        <h2 class="post-related-inline__title">Sigue leyendo</h2>
                        <div class="post-related-inline__links">
                            <?php if ($cat_name && $cat_url) : ?>
                                <a class="inline-link" href="<?php echo esc_url($cat_url); ?>">Ver todas las noticias de <?php echo esc_html($cat_name); ?></a>
                            <?php endif; ?>
                            <?php if ($is_brand_mode && $brand_name && $brand_archive_url) : ?>
                                <a class="inline-link" href="<?php echo esc_url($brand_archive_url); ?>">Ver todas las noticias sobre <?php echo esc_html($brand_name); ?></a>
                            <?php endif; ?>
                            <a class="inline-link" href="<?php echo esc_url($blog_url); ?>">Volver a todas las noticias</a>
                        </div>
                    </div>

                    <?php if ($related->have_posts()) : ?>
                        <div class="post-nav-cards post-nav-cards--related">
                            <?php while ($related->have_posts()) : $related->the_post(); ?>
                                <a class="post-nav-card" href="<?php the_permalink(); ?>">
                                    <div class="post-nav-card__content">
                                        <div class="post-nav-card__thumb" aria-hidden="true">
                                            <?php if (has_post_thumbnail()) : ?>
                                                <?php the_post_thumbnail('thumbnail', ['loading' => 'lazy', 'decoding' => 'async']); ?>
                                            <?php else : ?>
                                                <span class="post-nav-card__ph"></span>
                                            <?php endif; ?>
                                        </div>
                                        <div>
                                            <span class="post-nav-card__label"><?php echo esc_html(get_the_date('j M, Y')); ?></span>
                                            <span class="post-nav-card__title"><?php the_title(); ?></span>
                                        </div>
                                    </div>
                                </a>
                            <?php endwhile;
                            wp_reset_postdata(); ?>
                        </div>
                    <?php else : ?>
                        <p class="post-related-inline__empty">Todavia no hay mas articulos relacionados en esta categoria.</p>
                    <?php endif; ?>
                </section>
            </article>

            <aside class="aside aside--single" aria-label="Panel lateral">

                <section class="panel panel--subscribe newsletter-panel" data-newsletter-panel aria-label="Recibe novedades">
                    <div class="panel__title-row newsletter-panel__header">
                        <h2 class="panel__title">Recibe novedades</h2>
                        <button type="button" class="ayuda_garantia__button ayuda_garantia__button--mantenimiento panel__help-btn" aria-label="Mas informacion sobre la newsletter" data-tip-toggle aria-expanded="false" aria-controls="newsletter-tip-single">
                            <?php echo E360VO_Icon::get('icon-help_outline', ['class' => 'ayuda_garantia__icon', 'aria-hidden' => 'true']); ?>
                        </button>
                        <button type="button" class="newsletter-panel__trigger" data-newsletter-toggle aria-expanded="false" aria-controls="newsletter-form-shell-single">
                            Suscribete
                        </button>
                    </div>
                    <p class="panel__tip" id="newsletter-tip-single" hidden>Te enviaremos un correo cuando publiquemos contenido relevante para ti. Sin spam.</p>

                    <div class="newsletter-panel__form-shell" id="newsletter-form-shell-single" data-newsletter-form hidden>
                        <?php
                        $newsletter_shortcode = (string) apply_filters('th360_newsletter_shortcode', '[contact-form-7 id="04d14f1" title="Newsletter"]');
                        if (
                            $newsletter_shortcode !== ''
                            && function_exists('do_shortcode')
                            && function_exists('shortcode_exists')
                            && shortcode_exists('contact-form-7')
                        ) {
                            echo '<div class="newsletter-panel__form">';
                            echo do_shortcode($newsletter_shortcode);
                            echo '</div>';
                            echo '<div class="newsletter-panel__actions">';
                            echo '<button type="button" class="newsletter-panel__cancel" data-newsletter-cancel>Cancelar</button>';
                            echo '</div>';
                        } else {
                        ?>
                            <p class="panel__note">
                                Activa Contact Form 7 para mostrar el formulario de suscripcion.
                            </p>
                        <?php } ?>
                    </div>
                </section>

            </aside>

        </div>
    </div>

    <?php get_template_part('template-parts/blog/assets'); ?>
</main>

<?php get_footer(); ?>
