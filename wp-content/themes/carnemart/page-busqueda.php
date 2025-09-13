<?php
/*
Template Name: Página de Búsqueda
*/
get_header();
?>


<h1><?php the_title(); ?></h1>

<p>&nbsp;</p>
<p>&nbsp;</p>
<p>&nbsp;</p>
<p>&nbsp;</p>


<form role="search" method="get" id="searchform" action="<?php echo home_url('/busqueda/'); ?>">
    <label class="screen-reader-text" for="s">Buscar:</label>
    <input type="text" value="<?php echo get_search_query(); ?>" name="s" id="s" />
    <input type="submit" id="searchsubmit" value="Buscar" />
</form>

<?php if (have_posts()) : ?>
    <h2>Resultados de búsqueda:</h2>
    <?php while (have_posts()) : the_post(); ?>
        <div>
            <h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
            <p><?php the_excerpt(); ?></p>
        </div>
    <?php endwhile; ?>
<?php else : ?>
    <p>No se encontraron resultados.</p>
<?php endif; ?>


<?php get_footer(); ?>