<?php /* Template Name: Página interna con título */ get_header(); ?>


<?php if (have_posts()) : while (have_posts()) : the_post(); ?>
        <section id="post-<?php the_ID(); ?>" <?php post_class('site__content-page'); ?>>

            <header class="site__section-header-pagina-interna">
                <div class="row no-gutters row-header-page-interna">
                    <h1 class="has-verde-color"><?php the_title(); ?></h1>
                </div>
                <?php
                if (function_exists('yoast_breadcrumb')) {
                    yoast_breadcrumb(
                        '<nav class="breadcrumbs migas-centradas" aria-label="breadcrumb">',
                        '</nav>'
                    );
                }
                ?>

            </header>

            <div class="marco-contenido-internas">
                <?php the_content(); ?>
            </div>
            <div class="espaciox2"></div>




        <?php endwhile; ?>
        </section>

    <?php else : ?>

    <?php endif; ?>

    <?php get_footer(); ?>