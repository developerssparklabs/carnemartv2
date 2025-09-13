<div class="share-redes">

	<?php
	$current_url = home_url($_SERVER['REQUEST_URI'])
	?>

	<a href="#" onclick="share_fb('<?php echo $current_url ?>');return false;" rel="nofollow" share_url="<?php echo $current_url ?>" target="_blank" class="share-link link-s-facebook">
		<i class="bi bi-facebook"></i>
	</a>

	<a href="https://www.linkedin.com/shareArticle?mini=true&url=<?php echo $current_url ?>&title=<?php echo get_bloginfo('name'); ?>&summary=<?php the_title(); ?>&source=<?php echo get_bloginfo('name'); ?>" target="_blank" class="share-link link-s-linkedin">
		<i class="bi bi-linkedin"></i>
	</a>

	<a href="https://twitter.com/share?url=<?php echo $current_url ?>&amp;text=<?php echo get_bloginfo('name'); ?> <?php the_title(); ?>" target="_blank" class="share-link link-s-twitter">
		<i class="bi bi-twitter-x"></i>
	</a>

	<a href="https://api.whatsapp.com/send?text=<?php echo get_bloginfo('name'); ?> <?php the_title(); ?>%20<?php echo $current_url ?>" target=" _blank" class="share-link link-s-whatsapp">
		<i class="bi bi-whatsapp"></i>
	</a>

	<a href="mailto:?Subject=<?php echo get_bloginfo('name'); ?> <?php the_title(); ?>&amp;Body=<?php the_title(); ?> | <?php echo $current_url ?>" class="share-link link-s-mail">
		<i class="bi bi-envelope-fill"></i>
	</a>




</div>

<script type="text/javascript">
	function share_fb(url) {
		window.open('https://www.facebook.com/sharer/sharer.php?u=' + url, 'facebook-share-dialog', "width=626, height=436")
	}
</script>