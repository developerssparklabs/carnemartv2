<?php

get_header(); // Carga el archivo header.php del tema
?>

<main id="main" class="site-main">
	<?php if (have_posts()) : ?>
		<?php while (have_posts()) : the_post(); ?>
			<h1><?php the_title(); ?></h1>
			dadadadadad
			<p><?php the_excerpt(); ?></p>
		<?php endwhile; ?>
	<?php else : ?>
		<p><?php _e('No posts found', 'your-text-domain'); ?></p>
	<?php endif; ?>
</main>

<?php
get_footer(); ?>