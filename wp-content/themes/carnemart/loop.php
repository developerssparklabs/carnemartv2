<?php if (have_posts()): while (have_posts()) : the_post(); ?>

	<!-- article -->
	<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

		<div class="card custom-card">

			<?php if (has_post_thumbnail()) :  ?>
				<a href="<?php the_permalink(); ?>" title="<?php the_title(); ?>">
					<img src="<?php the_post_thumbnail_url( 'full' ); ?>" class="card-img-top" alt="<?php the_title_attribute(); ?>">
				</a>
			<?php else: ?>

			<?php endif; ?>

		<div class="card-body">
      <h5 class="card-title"><a href="<?php the_permalink(); ?>" title="<?php the_title(); ?>"><?php the_title(); ?></a></h5>
      <span class="date" style="font-size:12px;"><?php the_time('F.j.Y'); ?></span>
    </div>
		<div class="espacio-1"></div>
		</div>



	</article>
	<!-- /article -->

<?php endwhile; ?>

<?php else: ?>

	<!-- article -->
	<article>
		<h2><?php _e( 'Sorry, nothing to display.', 'html5blank' ); ?></h2>
	</article>
	<!-- /article -->

<?php endif; ?>
