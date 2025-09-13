<!-- search -->
<form class="search__form search inlinesearch" method="get" action="<?php echo home_url(); ?>" role="search">
	<input class="search-input" type="search" name="s" placeholder="<?php _e('¿Qué productos estas buscando?', 'html5blank'); ?>">
	<button class="custom-search-submit" type="submit" role="button" aria-label="Buscar contenido"><?php _e('<i class="bi bi-search"></i>', 'html5blank'); ?></button>
	<!-- <button class="custom-search-submit" type="submit" role="button"><?php _e('<span class="search__form-texto">Buscar</span>', 'html5blank'); ?></button> -->
</form>
<!-- /search -->