<?php
/*
Template Name: Página de ancho completo
*/
get_header();
?>

<style>
	.page-full-content{
		display:block;
		width:100%!important;
		padding:25px 0 50px 0;
	}
	body{
		background:#ffffff;
	}
</style>

<?php if (have_posts()) : while (have_posts()) : the_post(); ?>
    <section id="post-<?php the_ID(); ?>" <?php post_class('site__content-page pagina-tipo-full'); ?>>

    

        <div class="page-full-content">
            <?php the_content(); ?>
        </div>


    <?php endwhile; ?>
    </section>

<?php else : ?>

    <?php endif; ?>



<?php get_footer(); ?>