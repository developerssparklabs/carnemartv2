<?php get_header(); ?>


<style type="text/css">
	.header-box {
		display: flex;
		background-image: url();
		flex-direction: column;
		width: 100%;
		min-height: 80vh;
		justify-content: center;
		align-items: flex-start;
	}

	h1 {
		display: block;
		font-size: 150px;
		color: var(--color-secundario);
		margin: 0;
		padding: 0;
	}

	h5 {
		font-size: 30px;
		color: var(--color-secundario);
	}

	a.link-base {
		display: inline-block;
		padding: 10px;
		background-color: #ededed;
		margin: 0 6px;
		font-size: 17px;
		color: var(--color-principal);
	}

	.box-404-content {
		padding: 5%;
	}

	@media screen and (max-width:680px) {
		h1 {
			font-size: 70px !important;
		}

		h5 {
			font-size: 20px;
		}
	}
</style>

<section class="header-box" style="background-image: url(<?php the_field('img_fondo_404', 'option'); ?>);">


	<div class="box-404-content">
		<article id="post-404">
			<h1>404</h1>
			<h5>Lo sentimos, la página no esta disponible</h5>
			<div style="display:block; height: 20px;"></div>
			<a class=" btn-ubicacion" href="javascript:history.back()"><span>Regresar a la página anterior</span></a>
			<a class=" btn-ubicacion" href="<?php echo home_url(); ?>"><span>Ir a la página de inicio</span></a>
			<div class="espacio"></div>
		</article>

		<div class="espaciox2"></div>


		<h3>Quizas puedan interesarte estos productos</h3>
		<div class="espacio"></div>

		<?php echo do_shortcode('[products limit="4"]'); ?>
	</div>


</section>

<?php get_footer(); ?>