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

$related_args = [
    'post_type'              => 'post',
    'post_status'            => 'publish',
    'posts_per_page'         => 4,
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
        <div class="post-hero__inner">
            <p class="post-hero__kicker">
                <a href="<?php echo esc_url($blog_url); ?>">Noticias</a>
                <?php if ($cat_name && $cat_url) : ?>
                    · <a href="<?php echo esc_url($cat_url); ?>"><?php echo esc_html($cat_name); ?></a>
                <?php endif; ?>
            </p>

            <h1 class="post-hero__title" id="post-title"><?php echo $title_safe; ?></h1>

            <?php if (trim(wp_strip_all_tags($intro)) !== '') : ?>
                <div class="post-hero__excerpt"><?php echo $intro_safe; ?></div>
            <?php endif; ?>

            <div class="post-hero__row">
                <div class="meta" aria-label="Metadatos del artículo">
                    <time class="meta__date" datetime="<?php echo esc_attr(get_the_date('c', $post_id)); ?>"><?php echo esc_html(get_the_date('j M, Y', $post_id)); ?></time>
                    <?php if ($reading) : ?><span class="meta__muted"><?php echo esc_html($reading); ?></span><?php endif; ?>

                    <?php
                    $modified_u = (int) get_the_modified_time('U', $post_id);
                    $published_u = (int) get_the_time('U', $post_id);
                    if ($modified_u > 0 && $published_u > 0 && ($modified_u - $published_u) > DAY_IN_SECONDS) :
                    ?>
                        <span class="meta__muted">Actualizado <?php echo esc_html(get_the_modified_date('j M, Y', $post_id)); ?></span>
                    <?php endif; ?>
                </div>

                <div class="post-tools" role="group" aria-label="Acciones">
                    <button class="icon-btn" type="button" data-action="save" data-id="<?php echo (int) $post_id; ?>" aria-pressed="false" aria-label="Guardar artículo" title="Guardar">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="M6 3h12a1 1 0 011 1v17l-7-4-7 4V4a1 1 0 011-1z" stroke="currentColor" stroke-width="2" stroke-linejoin="round" />
                        </svg>
                    </button>

                    <button class="icon-btn" type="button" data-action="copy" data-url="<?php echo esc_url($permalink); ?>" aria-label="Copiar enlace" title="Copiar enlace">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="M8 7h11a2 2 0 012 2v11a2 2 0 01-2 2H8a2 2 0 01-2-2V9a2 2 0 012-2z" stroke="currentColor" stroke-width="2" />
                            <path d="M16 3H6a2 2 0 00-2 2v10" stroke="currentColor" stroke-width="2" />
                        </svg>
                    </button>

                    <button class="icon-btn" type="button" data-action="share" data-url="<?php echo esc_url($permalink); ?>" aria-label="Compartir artículo" title="Compartir">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="M4 12v7a1 1 0 001 1h14a1 1 0 001-1v-7" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
                            <path d="M16 6l-4-4-4 4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                            <path d="M12 2v13" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
                        </svg>
                    </button>
                </div>
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
        <div class="layout">

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

                    $prev = get_previous_post();
                    $next = get_next_post();
                    if ($prev || $next) :
                        ?>
                        <div style="flex-basis:100%;height:0"></div>
                        <?php if ($prev) : ?><a class="btn btn--primary" href="<?php echo esc_url(get_permalink($prev)); ?>">← Anterior</a><?php endif; ?>
                        <?php if ($next) : ?><a class="btn btn--primary" href="<?php echo esc_url(get_permalink($next)); ?>">Siguiente →</a><?php endif; ?>
                    <?php endif; ?>
                </footer>
            </article>

            <aside class="aside" aria-label="Panel lateral">

                <section class="panel panel--subscribe" aria-label="Recibe novedades">
                    <h2 class="panel__title">Recibe novedades</h2>
                    <p class="panel__text">Un email cuando publiquemos una guía nueva. Sin spam.</p>

                    <?php
                    $newsletter_shortcode = (string) apply_filters('th360_newsletter_shortcode', '');
                    if ($newsletter_shortcode !== '' && function_exists('do_shortcode')) {
                        echo do_shortcode($newsletter_shortcode);
                    } else {
                    ?>
                        <form class="panel__form" action="#" method="post" novalidate>
                            <label class="sr-only" for="side-sub-email">Email</label>
                            <input id="side-sub-email" class="panel__input" type="email" placeholder="Tu email" autocomplete="email" inputmode="email" required>
                            <button class="btn btn--primary btn--full" type="submit">Suscribirme</button>
                        </form>
                        <p class="panel__note">
                            Para activarlo: crea un formulario en Contact Form 7 y conéctalo con el filtro <code>th360_newsletter_shortcode</code>.
                        </p>
                    <?php } ?>
                </section>

                <?php if ($related->have_posts()) : ?>
                    <section class="panel" aria-label="Más artículos">
                        <h2 class="panel__title">Más guías</h2>
                        <p class="panel__text">Lecturas relacionadas para seguir aprendiendo.</p>

                        <div class="grid" style="grid-template-columns:1fr">
                            <?php while ($related->have_posts()) : $related->the_post(); ?>
                                <article class="tile">
                                    <a class="tile__link" href="<?php the_permalink(); ?>">
                                        <div class="tile__media">
                                            <?php if (has_post_thumbnail()) : ?>
                                                <?php the_post_thumbnail('medium_large', [
                                                    'loading' => 'lazy',
                                                    'alt'     => esc_attr(get_the_title()),
                                                    'class'   => 'tile__img',
                                                ]); ?>
                                            <?php else : ?>
                                                <div class="tile__img tile__img--ph" aria-hidden="true"></div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="tile__body">
                                            <div class="meta">
                                                <time class="meta__date" datetime="<?php echo esc_attr(get_the_date('c')); ?>"><?php echo esc_html(get_the_date('j M')); ?></time>
                                                <span class="meta__muted"><?php echo esc_html(th360_reading_time_label(get_the_ID())); ?></span>
                                            </div>
                                            <h3 class="tile__title"><?php the_title(); ?></h3>
                                            <div class="tile__footer"><span class="tile__cta">Leer →</span></div>
                                        </div>
                                    </a>
                                </article>
                            <?php endwhile;
                            wp_reset_postdata(); ?>
                        </div>
                    </section>
                <?php endif; ?>

            </aside>

        </div>
    </div>

    <?php get_template_part('template-parts/blog/assets'); ?>
</main>

<?php get_footer(); ?>