<!-- search -->
<form class="search__form search inlinesearch" method="get" action="<?php echo home_url(); ?>" role="search">
    <input class="search-input" type="search" name="s" placeholder="<?php _e('Buscar en el blog', 'html5blank'); ?>">
    <input type="hidden" name="post_type" value="post"> <!-- Este campo oculta filtra solo por posts -->
    <button class="custom-search-submit" type="submit" role="button" aria-label="Buscar contenido">
        <?php _e('<i class="bi bi-search"></i>', 'html5blank'); ?>
    </button>
</form>
<!-- /search -->