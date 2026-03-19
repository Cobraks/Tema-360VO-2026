Resultados de búsqueda<?php
                        /**
                         * Search results (Blog)
                         * @package 360vo-theme
                         */

                        if (!defined('ABSPATH')) exit;

                        get_header();

                        $blog_url = home_url('/noticias/');
                        $q = get_search_query(false);
                        $term = trim((string) $q);

                        global $wp_query;
                        $total = (int) ($wp_query->found_posts ?? 0);
                        ?>

<main class="main main--blog blog" id="main">

    <header class="blog-hero" aria-labelledby="blog-hero-title">
        <div class="blog-hero__inner">
            <div class="blog-hero__grid">
                <div class="blog-hero__content">
                    <p class="blog-hero__kicker">
                        <span class="blog-hero__dot" aria-hidden="true"></span>
                        Noticias · Búsqueda
                    </p>

                    <h1 class="blog-hero__title" id="blog-hero-title">
                        <?php if ($term !== '') : ?>
                            Resultados para “<?php echo esc_html($term); ?>”
                        <?php else : ?>
                            Buscar en noticias
                        <?php endif; ?>
                    </h1>

                    <p class="blog-hero__subtitle">
                        <?php if ($term !== '') : ?>
                            <?php echo (int) $total; ?> resultado<?php echo ($total === 1 ? '' : 's'); ?> en noticias y guías.
                        <?php else : ?>
                            Introduce un término para encontrar artículos del blog.
                        <?php endif; ?>
                    </p>

                    <p class="blog-hero__badge">
                        <a class="inline-link" href="<?php echo esc_url($blog_url); ?>">← Volver a noticias</a>
                    </p>
                </div>

                <aside class="blog-hero__side" aria-label="Buscar en noticias">
                    <form role="search" method="get" class="search" action="<?php echo esc_url(home_url('/')); ?>">
                        <input type="hidden" name="post_type" value="post">
                        <input type="hidden" name="th360_blog_search" value="1">

                        <label class="sr-only" for="blog-search">Buscar en noticias</label>
                        <div class="search__field">
                            <svg class="search__icon" width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <circle cx="11" cy="11" r="7" stroke="currentColor" stroke-width="2" />
                                <path d="M20 20l-3.5-3.5" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
                            </svg>
                            <input
                                id="blog-search"
                                class="search__input"
                                type="search"
                                name="s"
                                placeholder="Buscar noticias y guías…"
                                value="<?php echo esc_attr(get_search_query()); ?>" />
                        </div>
                        <div class="search__hint" aria-hidden="true">Ej.: “garantía”, “diésel”, “cambio automático”, “ITV”</div>
                    </form>
                </aside>
            </div>
        </div>
    </header>

    <div class="blog-shell">
        <section class="section section--posts" aria-label="Resultados de búsqueda">

            <?php if (have_posts()) : ?>
                <div class="grid">
                    <?php while (have_posts()) : the_post(); ?>
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
                                        <time class="meta__date" datetime="<?php echo esc_attr(get_the_date('c')); ?>">
                                            <?php echo esc_html(get_the_date('j M, Y')); ?>
                                        </time>
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
                $pagination = paginate_links([
                    'base'      => str_replace(999999999, '%#%', esc_url(get_pagenum_link(999999999))),
                    'format'    => '',
                    'current'   => max(1, (int) get_query_var('paged')),
                    'total'     => (int) ($wp_query->max_num_pages ?? 1),
                    'type'      => 'array',
                    'prev_text' => '← Anterior',
                    'next_text' => 'Siguiente →',
                    'add_args'  => [
                        'post_type' => 'post',
                        'th360_blog_search' => '1',
                    ],
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
                    <h3 class="empty__title">No hay resultados</h3>
                    <p class="empty__text">Prueba con otro término o revisa la ortografía.</p>
                    <p style="margin-top:1rem">
                        <a class="btn btn--primary" href="<?php echo esc_url($blog_url); ?>">Volver a noticias</a>
                    </p>
                </div>
            <?php endif; ?>

        </section>
    </div>

    <?php get_template_part('template-parts/blog/assets'); ?>
</main>

<?php get_footer(); ?>