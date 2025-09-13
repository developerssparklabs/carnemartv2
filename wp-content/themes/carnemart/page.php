<?php get_header(); ?>


<?php if (have_posts()) : while (have_posts()) : the_post(); ?>
		<section id="post-<?php the_ID(); ?>" <?php post_class('site__content-page'); ?>>

			<header class="site__section-header-pagina-interna" style="background-image: url('<?php $image_url = get_the_post_thumbnail_url(get_the_ID(), 'full');
																								echo esc_url($image_url); ?>');">
				<div class="row no-gutters row-header-page-interna">
					<h1><?php the_title(); ?></h1>
				</div>
				<div class="mascara"></div>
			</header>


			<?php the_content(); ?>

		<?php endwhile; ?>
		</section>

	<?php else : ?>

	<?php endif; ?>

	<?php get_footer(); ?>