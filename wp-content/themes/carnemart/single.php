<?php
get_header(); ?>

<?php
// Inicializar la variable del background
$background_style = '';

if (has_post_thumbnail()) {
	$thumbnail_url = get_the_post_thumbnail_url(get_the_ID(), 'full');
	$background_style = 'background-image: url(' . esc_url($thumbnail_url) . ');';
} else {
	$default_image_url = get_template_directory_uri() . '/img/img-horizontal.webp';
	$background_style = 'background-image: url(' . esc_url($default_image_url) . ');';
}
?>


<section id="post-<?php the_ID(); ?>" <?php post_class('site__content-page'); ?>>

	<header class="site__section-header-pagina-interna" style="background-image: url('<?php echo get_the_post_thumbnail_url($post_id, 'full'); ?>');">
		<div class="row no-gutters row-header-page-interna">
			<h4>Blog</h4>
			<h1><?php the_title(); ?></h1>
		</div>
		<div class="mascara"></div>
	</header>

	<!--  -->
	<div class="espaciox2"></div>

	<div class="custom-row">

		<div class="contenido-entrada">

			<?php if (have_posts()): while (have_posts()) : the_post(); ?>

					<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

						<div class="custom-row justify-content-between align-items-end pb-4 gap-5">
							<div class="custom-7">
								<h2>
									<?php the_title(); ?>
								</h2>
								<div class="espacio-1"></div>
								<span class="tipo-categoria">
									<?php
									// Mostrar la primera categoría asignada al post
									$categories = get_the_category();
									if (!empty($categories)) {
										echo esc_html($categories[0]->name);
									}
									?>
								</span>
								<div class="espaciox2"></div>
							</div>
							<div class="custom-width">

							</div>
						</div>


						<div class="col-md-12">
							<p>Fecha: <?php echo get_the_date(); ?></p>
						</div>

						<?php the_content(); ?>

					</article>

				<?php endwhile; ?>

			<?php else: ?>
				<article>
					<h2><?php _e('Sin contenido para mostrar', 'html5blank'); ?></h2>
				</article>
			<?php endif; ?>

			<div class="espacio"></div>

			<?php
			$prev_post = get_previous_post(); // Obtener entrada anterior
			$next_post = get_next_post(); // Obtener entrada siguiente
			?>

			<div class="navigation-posts">
				<?php if ($prev_post): ?>
					<div class="previous-post">
						<a href="<?php echo get_permalink($prev_post->ID); ?>" title="<?php echo esc_attr($prev_post->post_title); ?>">
							<?php
							// Obtener la URL de la imagen destacada
							$prev_thumbnail_url = get_the_post_thumbnail_url($prev_post->ID, 'thumbnail');
							if ($prev_thumbnail_url): ?>
								<img src="<?php echo esc_url($prev_thumbnail_url); ?>" alt="<?php echo esc_attr($prev_post->post_title); ?>" class="custom-thumbnail">
							<?php endif; ?>
							<h3><?php echo esc_html($prev_post->post_title); ?></h3>
						</a>
					</div>
				<?php endif; ?>

				<?php if ($next_post): ?>
					<div class="next-post">
						<a href="<?php echo get_permalink($next_post->ID); ?>" title="<?php echo esc_attr($next_post->post_title); ?>">
							<?php
							// Obtener la URL de la imagen destacada
							$next_thumbnail_url = get_the_post_thumbnail_url($next_post->ID, 'thumbnail');
							if ($next_thumbnail_url): ?>
								<img src="<?php echo esc_url($next_thumbnail_url); ?>" alt="<?php echo esc_attr($next_post->post_title); ?>" class="custom-thumbnail">
							<?php endif; ?>
							<h3><?php echo esc_html($next_post->post_title); ?></h3>
						</a>
					</div>
				<?php endif; ?>
			</div>

			<div class="espaciox2"></div>



		</div>

		<div class="contenido-sidebar side-bar-blog">
			<div class="share">
				<span class="txt-compartir">Compartir</span>
				<?php get_template_part('template-parts/bloques/share-page'); ?>
			</div>
			<div class="espacio"></div>

			<div style="padding:0 15px;">
				<?php get_template_part('searchform-entradas'); ?>
			</div>

			<div class="espacio"></div>

			<?php

			$args = array(
				'post_type' => 'post',
				'posts_per_page' => 3,
				'orderby' => 'date',
				'order' => 'DESC',
				'post__not_in' => array(get_the_ID()),
			);

			$query = new WP_Query($args);

			if ($query->have_posts()) :
				while ($query->have_posts()) : $query->the_post();
			?>
					<div class="slide-item">
						<div class="entradas-blog__card">
							<a href="<?php the_permalink(); ?>" class="entradas-blog__card-header" style="background-image: url('<?php echo get_the_post_thumbnail_url(get_the_ID(), 'full'); ?>');">
								<span class="tipo-categoria">
									<?php
									// Mostrar la primera categoría asignada al post
									$categories = get_the_category();
									if (!empty($categories)) {
										echo esc_html($categories[0]->name);
									}
									?>
								</span>
							</a>
							<div class="entradas-blog__card-body">
								<h2><?php the_title(); ?></h2>
								<span class="extracto"><?php the_excerpt(); ?></span>
							</div>
							<div class="entradas-blog__card-footer">
								<a href="<?php the_permalink(); ?>" class="btn btn-more"><i class="bi bi-plus-circle-fill"></i></a>
							</div>
						</div>
					</div>
			<?php
				endwhile;
				wp_reset_postdata();
			else :
				echo '<p>No se encontraron entradas.</p>';
			endif;
			?>

			<!-- sidebar -->
		</div>

	</div>

	<div class="espaciox2"></div>

</section>

<!-- body-single-page -->

<?php get_template_part('template-parts/bloques/footer-contenido'); ?>
<?php get_footer(); ?>