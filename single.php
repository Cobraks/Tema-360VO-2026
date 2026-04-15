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

$blog_url = home_url('/noticias/');
$permalink = get_permalink($post_id);

$cats = get_the_category($post_id);
$primary_cat = (!empty($cats) && !empty($cats[0])) ? $cats[0] : null;

$cat_name = ($primary_cat && !empty($primary_cat->name)) ? (string) $primary_cat->name : '';
$cat_url  = ($primary_cat) ? get_category_link($primary_cat) : '';
if (is_wp_error($cat_url)) $cat_url = '';

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
                        <input id="blog-search-single" class="search__input" type="search" name="s" placeholder="Buscar en el blog…" value="" />
                    </div>
                </form>
            </div>
        </div>

        <div class="post-hero__inner">
            <h1 class="post-hero__title" id="post-title"><?php echo $title_safe; ?></h1>

            <div class="post-hero__summary">
                <div class="post-hero__meta" aria-label="Metadatos del artículo">
                    <time class="post-hero__date" datetime="<?php echo esc_attr(get_the_date('c', $post_id)); ?>"><?php echo esc_html(get_the_date('j M, Y', $post_id)); ?></time>
                    <?php if ($reading) : ?><span class="post-hero__reading"><?php echo esc_html($reading); ?> de lectura</span><?php endif; ?>
                </div>

                <?php if (trim(wp_strip_all_tags($intro)) !== '') : ?>
                    <div class="post-hero__excerpt"><?php echo $intro_safe; ?></div>
                <?php endif; ?>
            </div>

            <div class="post-tools" role="group" aria-label="Acciones">
                <button class="icon-btn icon-btn--text" type="button" data-action="save" data-id="<?php echo (int) $post_id; ?>" aria-pressed="false" aria-label="Guardar artículo" title="Guardar">
                    <span class="icon-btn__icon icon-btn__icon--off" aria-hidden="true"><?php echo E360VO_Icon::get('blog_save', ['width' => 22, 'height' => 22]); ?></span>
                    <span class="icon-btn__icon icon-btn__icon--on" aria-hidden="true"><?php echo E360VO_Icon::get('blog_saved', ['width' => 22, 'height' => 22]); ?></span>
                    <span class="icon-btn__label">Guardar</span>
                </button>

                <button class="icon-btn icon-btn--text" type="button" data-action="copy" data-url="<?php echo esc_url($permalink); ?>" aria-label="Copiar enlace" title="Copiar enlace">
                    <span class="icon-btn__icon" aria-hidden="true"><?php echo E360VO_Icon::get('blog_copy', ['width' => 22, 'height' => 22]); ?></span>
                    <span class="icon-btn__label">Copiar enlace</span>
                </button>

                <button class="icon-btn icon-btn--text" type="button" data-action="share" data-url="<?php echo esc_url($permalink); ?>" aria-label="Compartir artículo" title="Compartir">
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

            <?php if ($activar_toc) : ?>
                <?php th360_render_table_of_contents([
                    'classes' => ['toc-container--single'],
                    'content_id' => 'toc-content-post-' . $post_id,
                    'label' => 'Navegacion del articulo',
                    'toggle_aria_label' => 'Abrir tabla de contenidos',
                ]); ?>
            <?php endif; ?>

            <article class="post-card" aria-label="Contenido del artículo">

                <div class="entry-content entry-content--start">
                    <?php
                    while (have_posts()) : the_post();
                        the_content();
                    endwhile;

                    wp_link_pages([
                        'before' => '<nav class="pagination" aria-label="Páginas del artículo"><ul class="pagination__list">',
                        'after'  => '</ul></nav>',
                        'link_before' => '<li class="pagination__item">',
                        'link_after'  => '</li>',
                    ]);
                    ?>
                </div>

                <footer class="post-footer" aria-label="Enlaces del artículo">
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

                <section class="post-related-inline" aria-label="Artículos relacionados">
                    <div class="post-related-inline__header">
                        <h2 class="post-related-inline__title">Sigue leyendo</h2>
                        <div class="post-related-inline__links">
                            <?php if ($cat_name && $cat_url) : ?>
                                <a class="inline-link" href="<?php echo esc_url($cat_url); ?>">Ver todas las noticias de <?php echo esc_html($cat_name); ?></a>
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
                        <p class="post-related-inline__empty">Todavía no hay más artículos relacionados en esta categoría.</p>
                    <?php endif; ?>
                </section>
            </article>

            <aside class="aside aside--single" aria-label="Panel lateral">

                <section class="panel panel--subscribe" aria-label="Recibe novedades">
                    <div class="panel__title-row">
                        <h2 class="panel__title">Recibe novedades</h2>
                        <button type="button" class="ayuda_garantia__button ayuda_garantia__button--mantenimiento panel__help-btn" aria-label="Más información sobre la newsletter" data-tip-toggle aria-expanded="false" aria-controls="newsletter-tip-single">
                            <?php echo E360VO_Icon::get('icon-help_outline', ['class' => 'ayuda_garantia__icon', 'aria-hidden' => 'true']); ?>
                        </button>
                    </div>
                    <p class="panel__tip" id="newsletter-tip-single" hidden>Te enviaremos un correo cuando publiquemos contenido relevante para ti. Sin spam.</p>

                    <?php
                    $newsletter_shortcode = (string) apply_filters('th360_newsletter_shortcode', '[contact-form-7 id="04d14f1" title="Newsletter"]');
                    if (
                        $newsletter_shortcode !== ''
                        && function_exists('do_shortcode')
                        && function_exists('shortcode_exists')
                        && shortcode_exists('contact-form-7')
                    ) {
                        echo do_shortcode($newsletter_shortcode);
                    } else {
                    ?>
                        <p class="panel__note">
                            Activa Contact Form 7 para mostrar el formulario de suscripción.
                        </p>
                    <?php } ?>
                </section>

            </aside>

        </div>
    </div>

    <?php get_template_part('template-parts/blog/assets'); ?>
</main>

<?php get_footer(); ?>
