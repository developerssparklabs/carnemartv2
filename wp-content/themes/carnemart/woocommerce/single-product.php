<?php

/**
 * The Template for displaying all single products
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/single-product.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see         https://woocommerce.com/document/template-structure/
 * @package     WooCommerce\Templates
 * @version     1.6.4
 */

if (! defined('ABSPATH')) {
	exit; // Exit if accessed directly
}

get_header('shop'); ?>

<?php
/**
 * woocommerce_before_main_content hook.
 *
 * @hooked woocommerce_output_content_wrapper - 10 (outputs opening divs for the content)
 * @hooked woocommerce_breadcrumb - 20
 */
do_action('woocommerce_before_main_content');
?>



<!-- AOG -->

<section id="post-<?php the_ID(); ?>" <?php post_class('site__content-page'); ?>>

	<section class="site__section bloque-contenido-shortcode  section-shortcode-contenido">
		<div class="row-shortcode-section">
			<?php echo do_shortcode('[barra_promociones]'); ?>
		</div>
	</section>

	<section class="site__section">
		<div class="row-shortcode-section">
			<img src="/wp-content/uploads/2025/09/proceso-de-compra-cinta.webp" alt="Proceso de compra" loading="lazy" decoding="async" fetchpriority="low" class="img-responsiva" style="border-radius:15px;" />
		</div>
	</section>



	<div class="custom-row row-encabezado-single">
		<div class="area-encabezado-single">
			<span class="area-encabezado-single__intro">
				Tienda
			</span>
			<h1 class="area-encabezado-single__titulo"><?php the_title(); ?></h1>
		</div>

		<div class="area-breadcomps pt-0">
			<?php
			if (function_exists('woocommerce_breadcrumb')) {
				woocommerce_breadcrumb(array(
					'delimiter'   => ' &#47; ',
					'wrap_before' => '<nav class="woocommerce-breadcrumb" aria-label="breadcrumb">',
					'wrap_after'  => '</nav>',
					'before'      => '<span>',
					'after'       => '</span>',
				));
			}
			?>
		</div>
	</div>

	<div class="custom-row bloque-producto">

		<div class="custom-4 img-galeria">

			<div class="single-product-image">

				<div class="producto-onsale">
					<?php
					// Asegúrate de que $product está definido correctamente.
					global $product;
					if (! is_a($product, 'WC_Product')) {
						$product = wc_get_product(get_the_ID());
					}

					// Verifica si el producto está en oferta antes de mostrar el flash.
					if ($product && $product->is_on_sale()) {
						woocommerce_show_product_sale_flash();
					}
					?>
				</div>




				<div class="woocommerce-product-gallery woocommerce-product-gallery--with-images" data-columns="4" style="opacity: 1; transition: opacity 0.25s ease-in-out;">
					<?php
					if (function_exists('woocommerce_show_product_images')) {
						woocommerce_show_product_images();
					}
					?>
				</div>
			</div>

		</div>

		<div class="custom-6 area-info-product">
			<div class="single-product-info">
				<?php echo do_shortcode('[listado_categorias]') ?>
				<?php echo do_shortcode('[listado_etiquetas]') ?>
				<div class="nombre-producto">
					<h2><?php the_title(); ?></h2>
					<div class="sku-section">
						<p class="product-sku"><strong>SKU:</strong> <?php echo esc_html($product->get_sku()); ?></p>
					</div>
				</div>

				<div class="mensajes-disponibilidad">
					<?php
					global $product;

					if ($product->managing_stock() && $product->is_in_stock()) {
						$stock_quantity = $product->get_stock_quantity(); // Obtén la cantidad de stock

						if ($stock_quantity > 0 && $stock_quantity <= get_option('woocommerce_notify_low_stock_amount')) {
							echo '<div class="msg-alerta-stock stock-alert">';
							echo '<i class="bi bi-info-circle-fill"></i> <span>¡Últimas piezas!</span>';
							echo '</div>';
							// echo '¡Quedan solo ' . esc_html($stock_quantity) . ' en stock! ';				
						}
					}
					?>

					<?php
					if (! $product->is_in_stock()) {
						echo '<div class="msg-alerta-stock producto-agotado">';
						echo '<i class="bi bi-ban-fill"></i></i> <span>¡Producto agotado!</span>';
						echo '</div>';
					}
					?>
				</div>

				<div class="stock-section">
					<?php
					$stock_quantity = $product->get_stock_quantity();
					$stock_status = $product->get_stock_status();

					if ($stock_status === 'instock') {
						echo '<p class="product-stock in-stock"><strong>Stock:</strong> Disponible (' . esc_html($stock_quantity) . ' unidades)</p>';
					} elseif ($stock_status === 'onbackorder') {
						echo '<p class="product-stock backorder"><strong>Stock:</strong> Disponible bajo pedido</p>';
					} else {
						echo '<p class="product-stock out-of-stock" style="display:none!important;"><strong>Stock:</strong> Agotado</p>';
					}
					?>
				</div>

				<div class="custom-row bloque-precio-detalle">
					<div class="custom-100">
						<div class="area-info-compra">
							<?php
							// Si el producto es variable
							if ($product->is_type('variable')) {
								$prices = $product->get_variation_prices();
								$min_price = current($prices['price']);
								$max_price = end($prices['price']);
								$min_regular_price = current($prices['regular_price']);
								$max_regular_price = end($prices['regular_price']);
							?>
								<?php if ($min_regular_price != $min_price): ?>
									<p class="product-price-original">
										<del><?php echo wc_price($min_regular_price); ?> - <?php echo wc_price($max_regular_price); ?></del>
									</p>
								<?php endif; ?>
								<p class="product-price-sale">
									<?php echo wc_price($min_price); ?> - <?php echo wc_price($max_price); ?>
								</p>
							<?php
							} else {
								// Si el producto no es variable
								$regular_price = $product->get_regular_price();
								$sale_price = $product->get_sale_price();
							?>
								<?php if ($product->is_on_sale()) : ?>
									<p class="product-price-original">
										<del><?php echo wc_price($regular_price); ?></del>
									</p>
								<?php endif; ?>

								<p class="product-price-sale">
									<?php echo $product->is_on_sale() ? wc_price($sale_price) : wc_price($regular_price); ?>
									<?php if ($product->is_on_sale()) : ?>
										<?php
										$percentage_saved = round((($regular_price - $sale_price) / $regular_price) * 100);
										?>
										<span class="product-discount-tag"><span class="mini-texto">Ahorre</span> <span class="mini-descuento">-<?php echo $percentage_saved; ?>%</span></span>
									<?php endif; ?>
								</p>
							<?php
							}
							?>
							<p class="product-iva-text">IVA INCLUIDO</p>



							<?php if ($product->is_in_stock()) : ?>
								<form class="cart" action="<?php echo esc_url($product->add_to_cart_url()); ?>" method="post" enctype="multipart/form-data">
									<div class="product-quantity">
										<button type="button" class="quantity-btn quantity-minus">-</button>
										<input type="number" id="quantity-input" class="quantity-input" name="quantity" value="1" min="1" aria-label="Cantidad" max="<?php echo esc_attr($product->get_stock_quantity()); ?>">
										<button type="button" class="quantity-btn quantity-plus">+</button>
									</div>

									<button type="submit" class="add-to-cart-button single_add_to_cart_button button">
										Añadir al carrito <i class="bi bi-cart2"></i>
									</button>
								</form>
							<?php endif; ?>
						</div>

					</div>

				</div>


				<div class="custom-row bloque-informacion">


					<?php
					global $product;

					$terms = get_the_terms($product->get_id(), 'pais');

					if (! empty($terms) && ! is_wp_error($terms)) {

						$term_names = array_map(function ($term) { ?>

							<div class="custom-3">
								<div class="site__imagen-texto-lateral">
									<figure class="site__imagen-texto-figure">
										<img decoding="async" src="<?php echo get_template_directory_uri(); ?>/img/flag.webp" alt="La Naval" class="site__imagen-texto-img">
									</figure>
									<div class="site__imagen-texto-contenido">
										<h3 class="titulo">País</h3>
										<div class="descripcion">

											<span>
												<?php echo esc_html($term->name); ?>
											</span>

										</div>
									</div>
								</div>

							</div>

					<?php }, $terms);

						echo implode('', $term_names);
					} else {
						echo '<span class="visually-hidden">No disponible</span>';
					}
					?>

					<?php
					global $product;

					$terms = get_the_terms($product->get_id(), 'region');

					if (! empty($terms) && ! is_wp_error($terms)) {

						$term_names = array_map(function ($term) { ?>

							<div class="custom-3">
								<div class="site__imagen-texto-lateral">
									<figure class="site__imagen-texto-figure">
										<img decoding="async" src="<?php echo get_template_directory_uri(); ?>/img/uvas.webp" alt="La Naval" class="site__imagen-texto-img">
									</figure>
									<div class="site__imagen-texto-contenido">
										<h3 class="titulo">Región</h3>
										<div class="descripcion">

											<span>
												<?php echo esc_html($term->name); ?>
											</span>

										</div>
									</div>
								</div>
							</div>

					<?php }, $terms);
						echo implode('', $term_names);
					} else {
						echo '<span class="visually-hidden">No disponible</span>';
					}
					?>


					<?php
					global $product;

					$terms = get_the_terms($product->get_id(), 'bodega');

					if (! empty($terms) && ! is_wp_error($terms)) {

						$term_names = array_map(function ($term) { ?>

							<div class="custom-3">
								<div class="site__imagen-texto-lateral">
									<figure class="site__imagen-texto-figure">
										<img decoding="async" src="<?php echo get_template_directory_uri(); ?>/img/botellas.webp" alt="La Naval" class="site__imagen-texto-img">
									</figure>
									<div class="site__imagen-texto-contenido">
										<h3 class="titulo">Bodega</h3>
										<div class="descripcion">

											<span>
												<?php echo esc_html($term->name); ?>
											</span>

										</div>
									</div>
								</div>
							</div>


					<?php }, $terms);

						echo implode('', $term_names);
					} else {
						echo '<span class="visually-hidden">No disponible</span>';
					}
					?>


				</div><!--custom-row bloque-informacion-->

				<div class="espacio"></div>


				<!-- <div class="custom-row bloque-informacion-promociones">
					<div class="custom-100">
						Espacio para otra información
					</div>
				</div> -->

				<div class="custom-row bloque-informacion-promociones flex-column">

					<div class="width-max p-relative">
						<p class="has-principal-color pb-1 mb-0 d-flex align-items-center"> <span>Peso variable</span> <span id="showInfoPeso"><i class="bi bi-info-circle-fill icon-con-tooltip has-azul-claro-color"></i></span></p>
						<div class="cm-peso-tooltip">El peso de productos frescos, perecederos o porcionados puede legítimamente variar por la naturaleza del producto (corte, humedad, hueso, congelación, etc.) y esto podría impactar en el precio final.</div>
					</div>


					<?php
					$medios_pago_img = get_field('medios_pago_img', 'option'); // ajusta 'option' si no es en página de opciones

					if (!empty($medios_pago_img) && isset($medios_pago_img['url'])) : ?>
						<div class="custom-100">
							<p class="has-principal-color pb-1 mb-0">Medios de pago</p>
							<img
								src="<?php echo esc_url($medios_pago_img['url']); ?>"
								alt="<?php echo esc_attr($medios_pago_img['alt']); ?>"
								class="single-product-medios-pago" loading="lazy" decoding="async" fetchpriority="low">
						</div>
					<?php endif; ?>

				</div>

			</div>
		</div>
	</div>

	<div class="espacio"></div>



	<div class="custom-row tabs-woocommerce">
		<div class="custom-100">

			<?php
			/**
			 * Muestra todas las tabs de producto
			 * (Descripción, Información adicional y Valoraciones)
			 */
			woocommerce_output_product_data_tabs();
			?>



		</div>
	</div>

	<div class="espaciox2"></div>


	<!-- Productos relacionados -->
	<section class="site__section bloque-contenido-shortcode bloque-full" style="background-color:#FAFAFA!important;">

		<div class="custom-shape-divider-top">
			<svg data-name="Layer 1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1200 120" preserveAspectRatio="none">
				<path d="M1200 120L0 16.48 0 0 1200 0 1200 120z" class="shape-fill"></path>
			</svg>
		</div>

		<div class="site__content-page">
			<div class="row-shortcode-section espacio-separadores AOFFF remove-products-grid">
				<?php echo do_shortcode('[productos_relacionados posts_per_page="4" columns="4"]'); ?>
			</div>
		</div>

		<div class="custom-shape-divider-bottom">
			<svg data-name="Layer 1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1200 120" preserveAspectRatio="none">
				<path d="M1200 120L0 16.48 0 0 1200 0 1200 120z" class="shape-fill"></path>
			</svg>
		</div>

	</section>

	<div class="espaciox2"></div>

</section>

<script>
	document.addEventListener("DOMContentLoaded", function() {
		// Detectar el evento de cierre del lightbox
		document.body.addEventListener("click", function(event) {
			// Detectar el botón de cerrar del lightbox
			const closeButton = event.target.closest(".pswp__button--close");

			if (closeButton) {
				// Forzar la limpieza del lightbox
				const lightbox = document.querySelector(".pswp");
				if (lightbox) {
					lightbox.classList.remove("pswp--open", "pswp--visible", "pswp--animated-in");
					lightbox.setAttribute("aria-hidden", "true");
					lightbox.style.display = "none"; // Asegurarse de ocultarlo completamente
				}

				// Restaurar visibilidad de la galería principal
				const productImage = document.querySelector(".woocommerce-product-gallery__image img");
				if (productImage) {
					productImage.style.visibility = "visible";
				}
			}
		});

		// Detectar cuando el lightbox se abre para evitar conflictos
		const lightboxContainer = document.querySelector(".pswp");
		if (lightboxContainer) {
			lightboxContainer.addEventListener("pswpClosed", function() {
				// Asegurar que el lightbox esté completamente cerrado
				lightboxContainer.style.display = "none";
			});
		}
	});
</script>

<style>
	.pswp[aria-hidden="true"] {
		display: none !important;
		visibility: hidden !important;
		opacity: 0 !important;
		pointer-events: none !important;
	}

	.area-info-compra {
		padding-top: 5px;
	}
</style>

<script>
	document.addEventListener('DOMContentLoaded', function() {
		const minusButton = document.querySelector('.quantity-minus');
		const plusButton = document.querySelector('.quantity-plus');
		const quantityInput = document.querySelector('#quantity-input');

		minusButton.addEventListener('click', function() {
			let currentValue = parseInt(quantityInput.value) || 1;
			if (currentValue > 1) {
				quantityInput.value = currentValue - 1;
			}
		});

		plusButton.addEventListener('click', function() {
			let currentValue = parseInt(quantityInput.value) || 1;
			let maxValue = parseInt(quantityInput.max) || 999;
			if (currentValue < maxValue) {
				quantityInput.value = currentValue + 1;
			}
		});
	});
</script>



<?php get_footer('shop'); ?>