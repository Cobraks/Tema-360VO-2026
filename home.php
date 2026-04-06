<?php

/**
 * @package 360vo-theme
 * Template Name: Noticias - Consejos para comprar coche de segunda mano (EdreamsCars)
 */

if (!defined('ABSPATH')) {
    exit;
}
get_header();

/**
 * Helpers
 */
if (!function_exists('th360_reading_time_label')) {
    function th360_reading_time_label($post_id): string
    {
        $p = get_post($post_id);
        if (!$p) return '';
        $words = str_word_count(wp_strip_all_tags((string) $p->post_content));
        $minutes = max(1, (int) ceil($words / 200));
        return $minutes . ' min';
    }
}

if (!function_exists('th360_first_cat_name')) {
    function th360_first_cat_name($post_id): string
    {
        $cats = get_the_category($post_id);
        return (!empty($cats) && !empty($cats[0]->name)) ? (string) $cats[0]->name : '';
    }
}

if (!function_exists('th360_get_image_caption')) {
    function th360_get_image_caption($post_id): string
    {
        $thumbnail_id = (int) get_post_thumbnail_id($post_id);
        if (!$thumbnail_id) return '';
        $caption = wp_get_attachment_caption($thumbnail_id);
        return $caption ? (string) $caption : '';
    }
}

/**
 * URLs "estratégicas"
 */
$inventory_url = home_url('/coches-segunda-mano/');
$search_q = get_search_query();

/**
 * Featured (editorial)
 */
$featured_posts = new WP_Query([
    'posts_per_page'         => 3,
    'post_status'            => 'publish',
    'meta_key'               => '_is_featured',
    'meta_value'             => '1',
    'no_found_rows'          => true,
    'ignore_sticky_posts'    => true,
    'update_post_term_cache' => true,
    'update_post_meta_cache' => true,
]);
$featured_ids = !empty($featured_posts->posts) ? wp_list_pluck($featured_posts->posts, 'ID') : [];

/**
 * Pagination and sorting
 */
$pp_allowed = [6, 9, 12];
$pp = isset($_GET['pp']) ? absint($_GET['pp']) : 6;
if (!in_array($pp, $pp_allowed, true)) $pp = 6;

$orderby_options = [
    'date'    => 'Más recientes',
    'popular' => 'Más vistos',
    'title'   => 'A-Z',
];
$orderby = isset($_GET['orderby']) ? sanitize_key((string) $_GET['orderby']) : 'date';
if (!array_key_exists($orderby, $orderby_options)) $orderby = 'date';

$paged = get_query_var('paged') ? (int) get_query_var('paged') : (get_query_var('page') ? (int) get_query_var('page') : 1);
if ($paged < 1) $paged = 1;

$posts_args = [
    'post_type'              => 'post',
    'posts_per_page'         => $pp,
    'paged'                  => $paged,
    'post_status'            => 'publish',
    'post__not_in'           => $featured_ids,
    'ignore_sticky_posts'    => true,
    'update_post_term_cache' => true,
    'update_post_meta_cache' => true,
];

switch ($orderby) {
    case 'popular':
        $posts_args['meta_key'] = 'post_views_count';
        $posts_args['orderby']  = 'meta_value_num';
        $posts_args['order']    = 'DESC';
        break;
    case 'title':
        $posts_args['orderby'] = 'title';
        $posts_args['order']   = 'ASC';
        break;
    default:
        $posts_args['orderby'] = 'date';
        $posts_args['order']   = 'DESC';
        break;
}

$posts_query = new WP_Query($posts_args);

$categories = get_categories([
    'hide_empty' => true,
    'orderby'    => 'count',
    'order'      => 'DESC',
    'number'     => 10,
]);

$base_url = get_permalink();
$pp_url_base = remove_query_arg(['pp', 'paged', 'page', 'orderby'], $base_url);

/**
 * Iconos por categoría (opcional)
 */
$category_icons = [
    'Consejos de compra' => 'M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z',
    'Financiación' => 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
    'Uncategorized' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
    'Mantenimiento' => 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z',
    'Guías' => 'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253',
    'Noticias' => 'M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z',
];

function th360_get_category_icon($category_name)
{
    global $category_icons;
    $default_icon = 'M7 20l4-16m2 16l4-16M6 9h14M4 15h14';

    if (isset($category_icons[$category_name])) {
        return $category_icons[$category_name];
    }

    foreach ($category_icons as $key => $icon) {
        if (stripos($category_name, $key) !== false || stripos($key, $category_name) !== false) {
            return $icon;
        }
    }

    return $default_icon;
}

?>

<main class="main main--blog blog" id="main">

    <header class="blog-hero" aria-labelledby="blog-hero-title">
        <div class="blog-hero__inner">
            <div class="blog-hero__grid">
                <div class="blog-hero__content">
                    <p class="blog-hero__kicker">
                        <span class="blog-hero__dot" aria-hidden="true"></span>
                        Noticias y guías · EdreamsCars
                    </p>

                    <h1 class="blog-hero__title" id="blog-hero-title">Consejos prácticos para comprar coche de segunda mano</h1>
                    <p class="blog-hero__subtitle">
                        Análisis, comparativas y guías para evaluar vehículos y evitar riesgos en tu compra.
                        <a class="inline-link" href="<?php echo esc_url($inventory_url); ?>">Visita nuestro catálogo de coches de ocasión</a> revisados con garantía.
                    </p>

                    <?php if (!empty($categories)) : ?>
                        <nav class="topics" aria-label="Temas principales">
                            <span class="topics__label">Explora por temas:</span>
                            <div class="topics__list">
                                <?php foreach ($categories as $cat) :
                                    $name = trim((string) $cat->name);
                                    if ($name === '' || preg_match('/^categor[ií]a\s*\d+$/i', $name)) continue;
                                    $icon_path = th360_get_category_icon($name);
                                ?>
                                    <a class="topics__link" href="<?php echo esc_url(get_category_link($cat)); ?>">
                                        <svg class="topics__icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                            <path d="<?php echo esc_attr($icon_path); ?>" />
                                        </svg>
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
                        <div class="search__hint" aria-hidden="true">Ej.: "garantía", "cambio automático", "diésel", "ITV"</div>
                    </form>
                </aside>
            </div>
        </div>
    </header>

    <div class="blog-shell">

        <?php if ($featured_posts->have_posts()) : ?>
            <section id="destacadas" class="section">
                <header class="section__header">
                    <h2 class="section__title">Guías destacadas</h2>
                    <p class="section__subtitle">Contenido esencial para empezar con buen criterio.</p>
                </header>

                <div class="featured">
                    <?php
                    $i = 0;
                    while ($featured_posts->have_posts()) : $featured_posts->the_post();
                        $i++;
                        $pid = (int) get_the_ID();
                        $cat_name = th360_first_cat_name($pid);
                        $reading  = th360_reading_time_label($pid);
                        $is_big   = ($i === 1);
                        $image_caption = th360_get_image_caption($pid);
                    ?>
                        <article class="card card--featured <?php echo $is_big ? 'card--big' : ''; ?>" data-post-id="<?php echo (int) $pid; ?>">
                            <a class="card__link" href="<?php the_permalink(); ?>">
                                <div class="card__media">
                                    <?php if (has_post_thumbnail()) : ?>
                                        <div class="card__image-container">
                                            <?php the_post_thumbnail($is_big ? 'large' : 'medium_large', [
                                                'loading'       => $is_big ? 'eager' : 'lazy',
                                                'fetchpriority' => $is_big ? 'high' : 'auto',
                                                'decoding'      => 'async',
                                                'alt'           => esc_attr(get_the_title()),
                                                'class'         => 'card__img',
                                            ]); ?>
                                            <?php if ($image_caption && $is_big) : ?>
                                                <div class="card__caption"><?php echo esc_html($image_caption); ?></div>
                                            <?php endif; ?>
                                        </div>
                                    <?php else : ?>
                                        <div class="card__img card__img--ph" aria-hidden="true"></div>
                                    <?php endif; ?>
                                </div>

                                <div class="card__body">
                                    <div class="meta">
                                        <time class="meta__date" datetime="<?php echo esc_attr(get_the_date('c')); ?>"><?php echo esc_html(get_the_date('j M, Y')); ?></time>
                                        <?php if ($reading) : ?>
                                            <span class="meta__muted"><?php echo esc_html($reading); ?></span>
                                        <?php endif; ?>
                                        <?php if ($cat_name) : ?>
                                            <span class="meta__chip"><?php echo esc_html($cat_name); ?></span>
                                        <?php endif; ?>
                                    </div>

                                    <h3 class="card__title"><?php the_title(); ?></h3>
                                    <p class="card__excerpt"><?php echo esc_html(wp_trim_words(get_the_excerpt(), $is_big ? 30 : 18, '…')); ?></p>
                                    <span class="card__cta" aria-hidden="true">Leer guía →</span>
                                </div>
                            </a>
                        </article>
                    <?php endwhile;
                    wp_reset_postdata(); ?>
                </div>
            </section>
        <?php endif; ?>

        <div class="layout">
            <section class="section section--posts" aria-label="Últimas publicaciones">
                <?php
                $total = (int) $posts_query->found_posts;
                $from  = ($paged - 1) * $pp + 1;
                $to    = min($total, $paged * $pp);
                ?>

                <header class="section__header section__header--row">
                    <div>
                        <h2 class="section__title">Últimas publicaciones</h2>
                        <p class="section__subtitle">Noticias, análisis y consejos prácticos para tu compra.</p>
                    </div>

                    <div class="toolbar" aria-label="Controles del listado">
                        <?php if ($total > 0) : ?>
                            <div class="results results--inline" aria-label="Estado del listado">
                                <span class="results__text"><?php echo (int) $from; ?>–<?php echo (int) $to; ?> de <?php echo (int) $total; ?></span>
                            </div>
                        <?php endif; ?>

                        <form class="toolbar__form" method="get" action="<?php echo esc_url($pp_url_base); ?>">
                            <?php if ($search_q) : ?>
                                <input type="hidden" name="s" value="<?php echo esc_attr($search_q); ?>">
                            <?php endif; ?>

                            <div class="toolbar__group">
                                <label class="toolbar__label" for="orderby">Ordenar por</label>
                                <select class="toolbar__select" id="orderby" name="orderby" onchange="this.form.submit()">
                                    <?php foreach ($orderby_options as $value => $label) : ?>
                                        <option value="<?php echo esc_attr($value); ?>" <?php selected($orderby, $value); ?>><?php echo esc_html($label); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="toolbar__group">
                                <label class="toolbar__label" for="pp">Por página</label>
                                <select class="toolbar__select" id="pp" name="pp" onchange="this.form.submit()">
                                    <?php foreach ($pp_allowed as $n) : ?>
                                        <option value="<?php echo (int) $n; ?>" <?php selected($pp, $n); ?>><?php echo (int) $n; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </form>
                    </div>
                </header>

                <?php if ($posts_query->have_posts()) :
                    $posts_query->the_post();
                    $latest_id   = (int) get_the_ID();
                    $latest_cat  = th360_first_cat_name($latest_id);
                    $latest_read = th360_reading_time_label($latest_id);
                    $latest_views = (int) (get_post_meta($latest_id, 'post_views_count', true) ?: 0);
                    $image_caption = th360_get_image_caption($latest_id);
                ?>

                    <article id="ultimo" class="lead" data-post-id="<?php echo (int) $latest_id; ?>" data-date="<?php echo esc_attr(get_the_date('Y-m-d')); ?>" data-views="<?php echo (int) $latest_views; ?>">
                        <div class="lead__layout">
                            <a class="lead__media" href="<?php the_permalink(); ?>" aria-label="<?php echo esc_attr(get_the_title($latest_id)); ?>">
                                <?php if (has_post_thumbnail()) : ?>
                                    <div class="lead__image-container">
                                        <?php
                                        $thumbnail_id = (int) get_post_thumbnail_id($latest_id);
                                        $image_meta = $thumbnail_id ? wp_get_attachment_metadata($thumbnail_id) : null;
                                        $is_vertical = $image_meta && !empty($image_meta['height']) && !empty($image_meta['width']) && ((int)$image_meta['height'] > (int)$image_meta['width'] * 1.2);

                                        the_post_thumbnail('large', [
                                            'loading'       => 'eager',
                                            'fetchpriority' => 'high',
                                            'decoding'      => 'async',
                                            'alt'           => esc_attr(get_the_title()),
                                            'class'         => 'lead__img' . ($is_vertical ? ' lead__img--vertical' : ''),
                                        ]);
                                        ?>
                                        <?php if ($image_caption) : ?>
                                            <div class="lead__caption"><?php echo esc_html($image_caption); ?></div>
                                        <?php endif; ?>
                                    </div>
                                <?php else : ?>
                                    <div class="lead__img lead__img--ph" aria-hidden="true"></div>
                                <?php endif; ?>
                            </a>

                            <div class="lead__body">
                                <p class="lead__kicker">Última publicación</p>

                                <div class="meta">
                                    <time class="meta__date" datetime="<?php echo esc_attr(get_the_date('c')); ?>"><?php echo esc_html(get_the_date('j M, Y')); ?></time>
                                    <?php if ($latest_read) : ?><span class="meta__muted"><?php echo esc_html($latest_read); ?></span><?php endif; ?>
                                    <?php if ($latest_cat) : ?>
                                        <span class="meta__chip"><?php echo esc_html($latest_cat); ?></span>
                                    <?php endif; ?>
                                </div>

                                <h3 class="lead__title">
                                    <a class="lead__title-link" href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                                </h3>

                                <p class="lead__excerpt"><?php echo esc_html(wp_trim_words(get_the_excerpt(), 40, '…')); ?></p>

                                <div class="lead__actions" aria-label="Acciones del artículo">
                                    <a class="lead__read" href="<?php the_permalink(); ?>">Leer →</a>

                                    <div class="lead__tools" role="group" aria-label="Herramientas">
                                        <button class="icon-btn" type="button" data-action="save" data-id="<?php echo (int) $latest_id; ?>" aria-pressed="false" aria-label="Guardar artículo" title="Guardar">
                                            <?php echo E360VO_Icon::get('bookmark_add', ['aria-hidden' => 'true', 'width' => 24, 'height' => 24]); ?>
                                        </button>
                                        <button class="icon-btn" type="button" data-action="share" data-url="<?php echo esc_url(get_permalink($latest_id)); ?>" aria-label="Compartir artículo" title="Compartir">
                                            <?php echo E360VO_Icon::get('share', ['aria-hidden' => 'true', 'width' => 24, 'height' => 24]); ?>
                                        </button>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </article>

                    <div class="grid" id="articles-container">
                        <?php while ($posts_query->have_posts()) : $posts_query->the_post();
                            $pid = (int) get_the_ID();
                            $views = (int) (get_post_meta($pid, 'post_views_count', true) ?: 0);
                            $cat  = th360_first_cat_name($pid);
                            $read = th360_reading_time_label($pid);
                            $tile_image_caption = th360_get_image_caption($pid);
                        ?>
                            <article class="tile" data-post-id="<?php echo (int) $pid; ?>" data-date="<?php echo esc_attr(get_the_date('Y-m-d')); ?>" data-views="<?php echo (int) $views; ?>">
                                <a class="tile__link" href="<?php the_permalink(); ?>">
                                    <div class="tile__media">
                                        <?php if (has_post_thumbnail()) : ?>
                                            <div class="tile__image-container">
                                                <?php the_post_thumbnail('medium_large', [
                                                    'loading'  => 'lazy',
                                                    'decoding' => 'async',
                                                    'alt'      => esc_attr(get_the_title()),
                                                    'class'    => 'tile__img',
                                                ]); ?>
                                                <?php if ($tile_image_caption) : ?>
                                                    <div class="tile__caption"><?php echo esc_html($tile_image_caption); ?></div>
                                                <?php endif; ?>
                                            </div>
                                        <?php else : ?>
                                            <div class="tile__img tile__img--ph" aria-hidden="true"></div>
                                        <?php endif; ?>
                                    </div>

                                    <div class="tile__body">
                                        <div class="meta">
                                            <time class="meta__date" datetime="<?php echo esc_attr(get_the_date('c')); ?>"><?php echo esc_html(get_the_date('j M')); ?></time>
                                            <?php if ($read) : ?><span class="meta__muted"><?php echo esc_html($read); ?></span><?php endif; ?>
                                            <?php if ($cat) : ?>
                                                <span class="meta__chip"><?php echo esc_html($cat); ?></span>
                                            <?php endif; ?>
                                        </div>

                                        <h3 class="tile__title"><?php the_title(); ?></h3>
                                        <p class="tile__excerpt"><?php echo esc_html(wp_trim_words(get_the_excerpt(), 18, '…')); ?></p>

                                        <div class="tile__footer">
                                            <span class="tile__cta">Leer →</span>
                                        </div>
                                    </div>
                                </a>
                            </article>
                        <?php endwhile;
                        wp_reset_postdata(); ?>
                    </div>

                    <?php
                    $pagination = paginate_links([
                        'base'      => str_replace(999999999, '%#%', esc_url(get_pagenum_link(999999999))),
                        'format'    => '',
                        'current'   => max(1, $paged),
                        'total'     => (int) $posts_query->max_num_pages,
                        'type'      => 'array',
                        'prev_text' => '← Anterior',
                        'next_text' => 'Siguiente →',
                        'add_args'  => array_filter([
                            'pp'      => $pp,
                            'orderby' => $orderby,
                            's'       => $search_q ?: null,
                        ]),
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
                        <h3 class="empty__title">Aún no hay publicaciones</h3>
                        <p class="empty__text">Próximamente publicaremos noticias y guías útiles sobre compra de coches de segunda mano.</p>
                    </div>
                <?php endif; ?>
            </section>

            <aside class="aside" aria-label="Panel lateral">
                <section class="panel panel--subscribe" aria-label="Recibe novedades">
                    <h2 class="panel__title">Recibe novedades</h2>
                    <p class="panel__text">Te avisamos cuando publiquemos contenido nuevo. Sin spam.</p>

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
