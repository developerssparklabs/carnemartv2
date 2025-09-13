<?php /* Template Name: Pagina Legales */ get_header(); ?>


<?php if (have_posts()) : while (have_posts()) : the_post(); ?>
        <section id="post-<?php the_ID(); ?>" <?php post_class('site__content-page'); ?>>

            <header class="site__section-header-pagina-interna header-interna-legales" style="background-image: url('<?php echo get_the_post_thumbnail_url($post_id, 'full'); ?>');">
                <div class="row no-gutters row-header-page-interna">
                    <h1><?php the_title(); ?></h1>
                </div>
            </header>

            <div class="legales-marco-contenido">
                <?php the_content(); ?>
            </div>
            <div class="espaciox2"></div>




        <?php endwhile; ?>
        </section>

    <?php else : ?>

    <?php endif; ?>

    <?php get_footer(); ?>