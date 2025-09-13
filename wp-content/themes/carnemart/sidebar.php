<!-- sidebar -->
<aside class="sidebar" role="complementary">
	<div class="espacio"></div>
	<h4>Buscar</h4>
	<?php get_template_part('searchform'); ?>
	<div class="espacio"></div>
		<?php if(!function_exists('dynamic_sidebar') || !dynamic_sidebar('widget-area-blog')) ?>
</aside>
<!-- /sidebar -->
