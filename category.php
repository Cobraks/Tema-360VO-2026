<?php

/**
 * Category template
 * @package 360vo-theme
 */

if (!defined('ABSPATH')) {
    exit;
}

get_header();

/**
 * Helpers (blog)
 */
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

$category = get_queried_object();
$cat_id   = (isset($category->term_id) ? (int) $category->term_id : 0);
$cat_name = single_cat_title('', false);
$cat_desc = category_description();

$blog_url = home_url('/noticias/');
$search_q = get_search_query();

$categories = get_categories([
    'hide_empty' => true,
    'orderby'    => 'count',
    'order'      => 'DESC',
    'number'     => 10,
]);

$featured_args = [
    'post_type'              => 'post',
    'post_status'            => 'publish',
    'posts_per_page'         => 3,
    'meta_key'               => '_is_featured',
    'meta_value'             => '1',
    'no_found_rows'          => true,
    'ignore_sticky_posts'    => true,
    'update_post_term_cache' => true,
    'update_post_meta_cache' => true,
];
if ($cat_id) $featured_args['cat'] = $cat_id;

$featured_posts = new WP_Query($featured_args);

if (!$featured_posts->have_posts()) {
    unset($featured_args['cat']);
    $featured_posts = new WP_Query($featured_args);
}
?>

<main class="main main--blog blog" id="main">

    <header class="blog-hero" aria-labelledby="blog-hero-title">
        <div class="blog-hero__inner">
            <div class="blog-hero__grid">
                <div class="blog-hero__content">
                    <p class="blog-hero__kicker">
                        <span class="blog-hero__dot" aria-hidden="true"></span>
                        Noticias · Categoría
                    </p>

                    <h1 class="blog-hero__title" id="blog-hero-title"><?php echo esc_html($cat_name); ?></h1>

                    <?php if (!empty($cat_desc)) : ?>
                        <div class="blog-hero__subtitle">
                            <?php echo wp_kses_post($cat_desc); ?>
                        </div>
                    <?php else : ?>
                        <p class="blog-hero__subtitle">Artículos relacionados con <strong><?php echo esc_html($cat_name); ?></strong>.</p>
                    <?php endif; ?>

                    <p class="blog-hero__badge">
                        <a class="inline-link" href="<?php echo esc_url($blog_url); ?>">← Volver a noticias</a>
                    </p>

                    <?php if (!empty($categories)) : ?>
                        <nav class="topics" aria-label="Temas principales">
                            <span class="topics__label">Explora por temas:</span>
                            <div class="topics__list">
                                <?php foreach ($categories as $cat) :
                                    $name = trim((string) $cat->name);
                                    if ($name === '' || preg_match('/^categor[ií]a\s*\d+$/i', $name)) continue;

                                    $link = get_category_link($cat);
                                    if (is_wp_error($link)) continue;

                                    $current = ($cat_id && (int) $cat->term_id === $cat_id);
                                ?>
                                    <a class="topics__link" href="<?php echo esc_url($link); ?>" <?php echo $current ? 'aria-current="page"' : ''; ?>>
                                        <span><?php echo esc_html($name); ?></span>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        </nav>
                    <?php endif; ?>
                </div>

                <aside class="blog-hero__side" aria-label="Buscar en noticias">
                    <form role="search" method="get" class="search" action="<?php echo esc_url(home_url('/')); ?>">
                        <label class="sr-only" for="blog-search">Buscar en noticias</label>
                        <div class="search__field">
                            <svg class="search__icon" width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <circle cx="11" cy="11" r="7" stroke="currentColor" stroke-width="2" />
                                <path d="M20 20l-3.5-3.5" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
                            </svg>
                            <input id="blog-search" class="search__input" type="search" name="s" placeholder="Buscar noticias y guías…" value="<?php echo esc_attr($search_q); ?>" />
                        </div>
                        <div class="search__hint" aria-hidden="true">Ej.: “garantía”, “financiación”, “mantenimiento”</div>
                    </form>
                </aside>
            </div>
        </div>
    </header>

    <div class="blog-shell">

        <?php if ($featured_posts->have_posts()) : ?>
            <section class="section" aria-label="Te puede interesar">
                <header class="section__header">
                    <h2 class="section__title">Te puede interesar</h2>
                    <p class="section__subtitle">Artículos destacados para complementar esta categoría.</p>
                </header>

                <div class="featured">
                    <?php
                    $i = 0;
                    while ($featured_posts->have_posts()) : $featured_posts->the_post();
                        $i++;
                        $is_big = ($i === 1);
                    ?>
                        <article class="card <?php echo $is_big ? 'card--big' : ''; ?>">
                            <a class="card__link" href="<?php the_permalink(); ?>">
                                <div class="card__media">
                                    <?php if (has_post_thumbnail()) : ?>
                                        <?php the_post_thumbnail($is_big ? 'large' : 'medium_large', [
                                            'loading'  => $is_big ? 'eager' : 'lazy',
                                            'decoding' => 'async',
                                            'alt'      => esc_attr(get_the_title()),
                                            'class'    => 'card__img',
                                        ]); ?>
                                    <?php else : ?>
                                        <div class="card__img card__img--ph" aria-hidden="true"></div>
                                    <?php endif; ?>
                                </div>

                                <div class="card__body">
                                    <div class="meta">
                                        <time class="meta__date" datetime="<?php echo esc_attr(get_the_date('c')); ?>"><?php echo esc_html(get_the_date('j M, Y')); ?></time>
                                        <span class="meta__muted"><?php echo esc_html(th360_reading_time_label((int) get_the_ID())); ?></span>
                                    </div>

                                    <h3 class="card__title"><?php the_title(); ?></h3>
                                    <p class="card__excerpt"><?php echo esc_html(wp_trim_words(get_the_excerpt(), $is_big ? 28 : 18, '…')); ?></p>
                                    <span class="card__cta" aria-hidden="true">Leer →</span>
                                </div>
                            </a>
                        </article>
                    <?php endwhile;
                    wp_reset_postdata(); ?>
                </div>
            </section>
        <?php endif; ?>

        <div class="layout">
            <section class="section section--posts" aria-label="Publicaciones en la categoría">
                <?php if (have_posts()) :
                    the_post();

                    $latest_id     = (int) get_the_ID();
                    $latest_url    = get_permalink($latest_id);
                    $latest_read   = th360_reading_time_label($latest_id);
                    $latest_views  = (int) (get_post_meta($latest_id, 'post_views_count', true) ?: 0);
                    $image_caption = th360_get_image_caption($latest_id);

                    $thumbnail_id = (int) get_post_thumbnail_id($latest_id);
                    $image_meta = $thumbnail_id ? wp_get_attachment_metadata($thumbnail_id) : null;
                    $is_vertical = $image_meta && !empty($image_meta['height']) && !empty($image_meta['width']) && ((int)$image_meta['height'] > (int)$image_meta['width'] * 1.2);
                ?>

                    <header class="section__header">
                        <h2 class="section__title">Últimas en <?php echo esc_html($cat_name); ?></h2>
                        <p class="section__subtitle">Ordenadas por fecha (más recientes primero).</p>
                    </header>

                    <article class="lead" data-post-id="<?php echo (int) $latest_id; ?>" data-date="<?php echo esc_attr(get_the_date('Y-m-d')); ?>" data-views="<?php echo (int) $latest_views; ?>">
                        <div class="lead__layout">
                            <a class="lead__media" href="<?php echo esc_url($latest_url); ?>" aria-label="<?php echo esc_attr(get_the_title($latest_id)); ?>">
                                <?php if (has_post_thumbnail()) : ?>
                                    <div class="lead__image-container">
                                        <?php the_post_thumbnail('large', [
                                            'loading'       => 'eager',
                                            'fetchpriority' => 'high',
                                            'decoding'      => 'async',
                                            'alt'           => esc_attr(get_the_title()),
                                            'class'         => 'lead__img' . ($is_vertical ? ' lead__img--vertical' : ''),
                                        ]); ?>
                                        <?php if ($image_caption) : ?>
                                            <div class="lead__caption"><?php echo esc_html($image_caption); ?></div>
                                        <?php endif; ?>
                                    </div>
                                <?php else : ?>
                                    <div class="lead__img lead__img--ph" aria-hidden="true"></div>
                                <?php endif; ?>
                            </a>

                            <div class="lead__body">
                                <p class="lead__kicker"><?php echo esc_html($cat_name); ?></p>

                                <div class="meta">
                                    <time class="meta__date" datetime="<?php echo esc_attr(get_the_date('c')); ?>"><?php echo esc_html(get_the_date('j M, Y')); ?></time>
                                    <?php if ($latest_read) : ?><span class="meta__muted"><?php echo esc_html($latest_read); ?></span><?php endif; ?>
                                </div>

                                <h3 class="lead__title">
                                    <a class="lead__title-link" href="<?php echo esc_url($latest_url); ?>"><?php the_title(); ?></a>
                                </h3>

                                <p class="lead__excerpt"><?php echo esc_html(wp_trim_words(get_the_excerpt(), 40, '…')); ?></p>

                                <div class="lead__actions" aria-label="Acciones del artículo">
                                    <a class="lead__read" href="<?php echo esc_url($latest_url); ?>">Leer →</a>

                                    <div class="lead__tools" role="group" aria-label="Herramientas">
                                        <button class="icon-btn" type="button" data-action="save" data-id="<?php echo (int) $latest_id; ?>" aria-pressed="false" aria-label="Guardar artículo" title="Guardar">
                                            <?php echo E360VO_Icon::get('bookmark_add', ['aria-hidden' => 'true', 'width' => 24, 'height' => 24]); ?>
                                        </button>
                                        <button class="icon-btn" type="button" data-action="share" data-url="<?php echo esc_url($latest_url); ?>" aria-label="Compartir artículo" title="Compartir">
                                            <?php echo E360VO_Icon::get('share', ['aria-hidden' => 'true', 'width' => 24, 'height' => 24]); ?>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </article>

                    <div class="grid">
                        <?php while (have_posts()) : the_post(); ?>
                            <article class="tile">
                                <a class="tile__link" href="<?php the_permalink(); ?>">
                                    <div class="tile__media">
                                        <?php if (has_post_thumbnail()) : ?>
                                            <?php the_post_thumbnail('medium_large', [
                                                'loading'  => 'lazy',
                                                'decoding' => 'async',
                                                'alt'      => esc_attr(get_the_title()),
                                                'class'    => 'tile__img',
                                            ]); ?>
                                        <?php else : ?>
                                            <div class="tile__img tile__img--ph" aria-hidden="true"></div>
                                        <?php endif; ?>
                                    </div>

                                    <div class="tile__body">
                                        <div class="meta">
                                            <time class="meta__date" datetime="<?php echo esc_attr(get_the_date('c')); ?>"><?php echo esc_html(get_the_date('j M')); ?></time>
                                            <span class="meta__muted"><?php echo esc_html(th360_reading_time_label((int) get_the_ID())); ?></span>
                                        </div>

                                        <h3 class="tile__title"><?php the_title(); ?></h3>
                                        <p class="tile__excerpt"><?php echo esc_html(wp_trim_words(get_the_excerpt(), 18, '…')); ?></p>
                                        <div class="tile__footer"><span class="tile__cta">Leer →</span></div>
                                    </div>
                                </a>
                            </article>
                        <?php endwhile; ?>
                    </div>

                    <?php
                    global $wp_query;
                    $pagination = paginate_links([
                        'base'      => str_replace(999999999, '%#%', esc_url(get_pagenum_link(999999999))),
                        'format'    => '',
                        'current'   => max(1, (int) get_query_var('paged')),
                        'total'     => (int) ($wp_query->max_num_pages ?? 1),
                        'type'      => 'array',
                        'prev_text' => '← Anterior',
                        'next_text' => 'Siguiente →',
                    ]);
                    if (!empty($pagination) && is_array($pagination)) :
                    ?>
                        <nav class="pagination" aria-label="Paginación">
                            <ul class="pagination__list">
                                <?php foreach ($pagination as $link) : ?>
                                    <li class="pagination__item"><?php echo $link; ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </nav>
                    <?php endif; ?>

                <?php else : ?>
                    <div class="empty">
                        <h3 class="empty__title">No hay artículos en esta categoría</h3>
                        <p class="empty__text">Prueba con otra categoría o vuelve a la página de noticias.</p>
                        <p class="u-mt-16"><a class="btn btn--primary" href="<?php echo esc_url($blog_url); ?>">Volver a noticias</a></p>
                    </div>
                <?php endif; ?>
            </section>

            <aside class="aside" aria-label="Panel lateral">
                <section class="panel panel--subscribe" aria-label="Recibe novedades">
                    <h2 class="panel__title">Recibe novedades</h2>
                    <p class="panel__text">Un email cuando publiquemos contenido nuevo. Sin spam.</p>

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
