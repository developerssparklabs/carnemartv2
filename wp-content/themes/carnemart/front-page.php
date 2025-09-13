<?php get_header(); ?>


<?php if (have_posts()) : while (have_posts()) : the_post(); ?>
        <section id="post-<?php the_ID(); ?>" <?php post_class('site__content-page'); ?>>


            <?php the_content(); ?>

        <?php endwhile; ?>
        </section>

    <?php else : ?>

    <?php endif; ?>

    <?php get_footer(); ?>