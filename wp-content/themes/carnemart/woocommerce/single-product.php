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

if (!defined('ABSPATH')) {
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
			<img src="/wp-content/uploads/2025/09/proceso-de-compra-cinta.webp" alt="Proceso de compra" loading="lazy"
				decoding="async" fetchpriority="low" class="img-responsiva" style="border-radius:15px;" />
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
					'delimiter' => ' &#47; ',
					'wrap_before' => '<nav class="woocommerce-breadcrumb" aria-label="breadcrumb">',
					'wrap_after' => '</nav>',
					'before' => '<span>',
					'after' => '</span>',
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
					if (!is_a($product, 'WC_Product')) {
						$product = wc_get_product(get_the_ID());
					}

					// Verifica si el producto está en oferta antes de mostrar el flash.
					if ($product && $product->is_on_sale()) {
						woocommerce_show_product_sale_flash();
					}
					?>
				</div>




				<div class="woocommerce-product-gallery woocommerce-product-gallery--with-images" data-columns="4"
					style="opacity: 1; transition: opacity 0.25s ease-in-out;">
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
					if (!$product->is_in_stock()) {
						echo '<div class="msg-alerta-stock producto-agotado">';
						echo '<i class="bi bi-ban-fill"></i></i> <span>¡Producto agotado!</span>';
						echo '</div>';
					}
					?>
				</div>

				<div class="stock-section">
					<?php
					// Verificar cookie de tienda
					$term_id = sb_get_current_store_term_id();
					if ($term_id) {
						$stock_quantity = get_post_meta($product->get_id(), "wcmlim_stock_at_{$term_id}", true);
						$stock_status = $product->get_stock_status();
						if ($stock_status === 'instock') {
							echo '<p class="product-stock in-stock"><strong>Stock:</strong> Disponible (' . esc_html($stock_quantity) . ' unidades)</p>';
						} elseif ($stock_status === 'onbackorder') {
							echo '<p class="product-stock backorder"><strong>Stock:</strong> Disponible bajo pedido</p>';
						} else {
							echo '<p class="product-stock out-of-stock" style="display:none!important;"><strong>Stock:</strong> Agotado</p>';
						}
					}
					?>
				</div>

				<?php
				if (!function_exists('sb_get_current_store_term_id')) {
					function sb_get_current_store_term_id(): int
					{
						if (!empty($_COOKIE['wcmlim_selected_location_termid']) && $_COOKIE['wcmlim_selected_location_termid'] !== 'undefined') {
							return (int) $_COOKIE['wcmlim_selected_location_termid'];
						}
						return 0;
					}
				}

				if (!function_exists('sb_get_store_prices_for_product')) {
					/**
					 * Devuelve precios para un producto/variación, priorizando la tienda actual.
					 * return ['regular'=>float|null, 'sale'=>float|null, 'price'=>float]  (price = efectivo)
					 */
					function sb_get_store_prices_for_product($product): array
					{
						$pid = $product instanceof WC_Product ? $product->get_id() : (int) $product;
						$term_id = sb_get_current_store_term_id();

						$reg = $sale = null;
						if ($term_id > 0 && $pid > 0) {
							$reg = get_post_meta($pid, "wcmlim_regular_price_at_{$term_id}", true);
							$sale = get_post_meta($pid, "wcmlim_sale_price_at_{$term_id}", true);

							// si es variación y no tiene meta, caer al padre
							if ($product instanceof WC_Product_Variation) {
								$parent_id = $product->get_parent_id();
								if (($reg === '' || $reg === null) && $parent_id)
									$reg = get_post_meta($parent_id, "wcmlim_regular_price_at_{$term_id}", true);
								if (($sale === '' || $sale === null) && $parent_id)
									$sale = get_post_meta($parent_id, "wcmlim_sale_price_at_{$term_id}", true);
							}
						}

						// normaliza
						$reg = is_numeric($reg) ? (float) $reg : null;
						$sale = is_numeric($sale) ? (float) $sale : null;
						if ($sale !== null && $reg !== null && $sale >= $reg)
							$sale = null;

						// fallback a precios nativos si no hay por tienda
						$fallback_reg = (float) $product->get_regular_price();
						$fallback_sale = $product->get_sale_price() !== '' ? (float) $product->get_sale_price() : null;

						$effective_reg = $reg !== null ? $reg : $fallback_reg;
						$effective_sale = $sale !== null ? $sale : $fallback_sale;

						$price = ($effective_sale !== null) ? $effective_sale : $effective_reg;

						return ['regular' => $effective_reg, 'sale' => $effective_sale, 'price' => (float) $price];
					}
				}
				?>
				<div class="custom-row bloque-precio-detalle">
					<div class="custom-100">
						<div class="area-info-compra">
							<?php
							    if (!function_exists('sb_get_first_step'))  {
									// obtenemos el primer step
									function sb_get_first_step($tiers) {
										if (empty($tiers)) {
											return null;
										}
										
										$first_key = array_key_first($tiers);
										if ($first_key === null) {
											return null;
										}
										
										// Extract the first numeric value from the key
										if (preg_match('/(\d+(?:\.\d+)?)/', $first_key, $matches)) {
											return (float) $matches[1];
										}
										
										return null;
									}
								}
								
							// Si el producto es variable
							/* ==== PINTAR PRECIO ==== */
							if ($product->is_type('variable')) {

								$child_ids = $product->get_children(); // ids de variaciones
								$prices = $regs = $sales = [];

								foreach ($child_ids as $vid) {
									$v = wc_get_product($vid);
									if (!$v)
										continue;
									$p = sb_get_store_prices_for_product($v);
									// ignora variaciones sin precio real
									if ($p['price'] > 0) {
										$prices[] = $p['price'];
										$regs[] = $p['regular'];
										if ($p['sale'] !== null)
											$sales[] = $p['sale'];
									}
								}

								if (empty($prices)) {
									// Fallback total a comportamiento nativo si no encontramos nada
									echo '<p class="product-price-sale">' . wp_kses_post($product->get_price_html()) . '</p>';
								} else {
									$min_price = min($prices);
									$max_price = max($prices);
									$min_reg = !empty($regs) ? min($regs) : $min_price;
									$max_reg = !empty($regs) ? max($regs) : $max_price;

									// Mostrar tachado si hay alguna variación en oferta (por tienda o nativo)
									$hay_oferta = !empty($sales);

									if ($hay_oferta && ($min_reg > $min_price || $max_reg > $max_price)): ?>
										<p class="product-price-original">
											<del><?php echo wc_price($min_reg); ?> - <?php echo wc_price($max_reg); ?></del>
										</p>
									<?php endif; ?>

									<p class="product-price-sale">
										<?php echo wc_price($min_price); ?> - <?php echo wc_price($max_price); ?>
									</p>
								<?php }

							} else {
								    $pid = $product->get_id();
									$unit = get_post_meta($pid, 'ri_quantity_step_label', true);
									$unit_txt = $unit ? ' <span class="por-label" style="color:#6c757d;font-size:15px;">por <b>' . esc_html($unit) . '</b></span>' : '';

									$term_id = sb_get_current_store_term_id();

									// Precio base por tienda o nativo
									$price_base_tienda = $term_id ? get_post_meta($pid, "wcmlim_regular_price_at_{$term_id}", true) : '';
									$price_base_tienda = is_numeric($price_base_tienda) ? (float) $price_base_tienda : null;
									$price_base_nativo = (float) $product->get_regular_price();

									// Tiers (array asociativo "key" => price)
									$tiers = $term_id ? sb_get_tiers_meta($pid, $term_id) : [];

									$show_del = false;
									$regular = null;
									$sale = null;

									$showOfert = false;
									if (!empty($tiers)) {
										// Tomar PRIMER tier (clave + valor)
										[$first_key, $first_val] = sb_array_first_kv($tiers);
										$first_val = is_numeric($first_val) ? (float) $first_val : null;

										// Precio entre paréntesis en la clave => regular del tier
										$paren_reg = sb_parse_paren_price_from_key((string) $first_key);

										$first_step = sb_get_first_step($tiers);

										// echo '<pre>';
										// print_r($first_val);
										// echo '</pre>';

										// echo '<pre>';
										// print_r($paren_reg);
										// echo '</pre>';

										// echo '<pre>';
										// print_r($first_step);
										// echo '</pre>';

										$step_meta = get_post_meta($pid, 'product_step', true); // p.ej. "0.5"

										if ($first_step <= $step_meta) {
											$showOfert = true;
										}
										// 1) Regular
										if ($paren_reg !== null && $paren_reg > 0) {
											$regular = $paren_reg;
										} else {
											// si no hay numérico entre paréntesis, usamos el base de tienda si existe, si no nativo
											$regular = $price_base_tienda !== null ? $price_base_tienda : $price_base_nativo;
										}

										// 2) Sale (el valor del primer tier, solo si es menor al regular)
										if ($first_val !== null && $regular !== null && $first_val < $regular) {
											$sale = $first_val;
											$show_del = true;
										}

									} else {
										// Sin tiers: usa base tienda o nativo
										$regular = $price_base_tienda !== null ? $price_base_tienda : $price_base_nativo;
									}

									// Pintar precio
									if ($show_del && $regular && $showOfert) {
										// echo '<span class="product-discount-tag">';
										// echo '<span class="mini-texto">Ahorre</span>';
										// echo '<span class="mini-descuento">-' . esc_html($regular) . '%</span>';
										// echo '</span>';
										
										echo '<div>';
										echo '<p class="product-price-original" style="color: red; font-size: 20px">';
										echo '<del>' . wc_price($regular) . '</del>';
										echo '</p>';
										echo '<p class="product-price-sale">' . wc_price($sale) . $unit_txt . '</p>';
										echo '</div>';
									} else {
										echo '<p class="product-price-sale">' . wc_price($regular) . $unit_txt . '</p>';
									}		
								?>
							<?php } ?>
							<?php if ($product->is_in_stock()):

								$term_id = sb_get_current_store_term_id();
								if (!$term_id) {
									echo '<div class="cm-store-required" style="
										display: flex;
										margin-top:10px;
										background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
										color: white;
										padding: 15px;
										border-radius: 8px;
										text-align: left;
										font-weight: bold;
										margin-bottom: 10px;
										box-shadow: 0 2px 10px rgba(0,0,0,0.1);
									">
										<i class="bi bi-geo-alt-fill" style="margin-right: 8px; font-size: 1.1em;"></i> 
										<div>
											Selecciona una tienda para ver el precio, disponibilidad y opciones de compra de este producto.
										</div>
									</div>';
								} else {
									// === Paso y mínimo desde metas ===
									$pid = $product->get_id();
									$step_meta = get_post_meta($pid, 'product_step', true); // p.ej. "0.5"
									$min_meta = get_post_meta($pid, 'product_min', true); // opcional. Si no hay, usamos el step
							
									$step = is_numeric($step_meta) ? (float) $step_meta : 1;
									$min = is_numeric($min_meta) ? (float) $min_meta : $step;

									if ($step <= 0)
										$step = 1;
									if ($min <= 0)
										$min = $step;

									// Cantidad por defecto = mínimo
									$default_qty = $min;

									// Máximo recomendado (si manejas stock; quítalo si no lo necesitas)
									$max_qty = $product->backorders_allowed() ? '' : $product->get_stock_quantity();

									// Decimales para formatear el valor mostrado
									$decimals = strpos((string) $step, '.') !== false ? strlen(substr(strrchr((string) $step, '.'), 1)) : 0;

									?>
										<form class="cart"
											action="<?php echo esc_url(apply_filters('woocommerce_add_to_cart_form_action', $product->get_permalink())); ?>"
											method="post" enctype="multipart/form-data">

											<div class="product-quantity" style="margin-top: 12px;">
												<button type="button" class="quantity-btn quantity-minus"
													aria-label="Disminuir">-</button>

												<input type="number" 
													id="quantity-input" 
													class="quantity-input" 
													name="quantity"
													value="<?php echo esc_attr(wc_format_decimal($default_qty, $decimals)); ?>" 
													step="<?php echo esc_attr($step); ?>" 
													min="<?php echo esc_attr($min); ?>"
													<?php if ($max_qty): ?>max="<?php echo esc_attr($max_qty); ?>"<?php endif; ?>
													inputmode="decimal" 
													pattern="[0-9]*[.,]?[0-9]*"
													aria-label="<?php esc_attr_e('Cantidad'); ?>">

												<button type="button" class="quantity-btn quantity-plus"
													aria-label="Aumentar">+</button>
											</div>

											<!-- Necesario para que el POST agregue este producto -->
											<input type="hidden" name="add-to-cart"
												value="<?php echo esc_attr($product->get_id()); ?>">

											<button type="submit" class="add-to-cart-button single_add_to_cart_button button">
												Añadir al carrito <i class="bi bi-cart2"></i>
											</button>
										</form>
									<?php
								}
								?>
							<?php endif; ?>

							<?php
								// Mostrar los tiers (escalados)
								$term_id = sb_get_current_store_term_id();

								$pid = $product->get_id();

								// Tiers (array asociativo "key" => price)
								$tiers = $term_id ? sb_get_tiers_meta($pid, $term_id) : [];

								// ===== Tabla "Precios por volumen" SIEMPRE visible (del segundo tier en adelante) =====
								if (!empty($tiers)) {
									
									$first_step = sb_get_first_step($tiers);
									$step_meta = get_post_meta($pid, 'product_step', true); // p.ej. "0.5"

									$saltar_first_tier = ($first_step <= $step_meta);

									// Saltamos el primer tier (ya mostrado como precio actual)
									$rest = $saltar_first_tier ? array_slice($tiers, 1, null, true) : $tiers;
									if (!empty($rest)) {
										// Base para calcular “Ahorro”
										if (!$saltar_first_tier)  {
											$base_price = $regular;
										} else {
											$base_price = (isset($sale) && $sale > 0 && $sale < $regular) ? (float)$sale : (float)$regular;
										}

										static $css_done_vol = false;
										if (!$css_done_vol) {
											echo '<style>
											.bafar-volume-open{margin:.5rem 0 1rem; margin-top: 20px}
											.bafar-volume-title{
												color:#0866FD; font-weight:700; margin-bottom:.35rem; display:flex; align-items:center;
											}

											.bafar-table-wrap{margin-top:.35rem}
											.bafar-table{width:100%; border-collapse:collapse; font-size:.95rem; background:#fff; border:1px solid #e6e8ef; border-radius:8px; overflow:hidden; table-layout:fixed}
											/* Columnas: 55% / 25% / 20% para alinear números */
											.bafar-table col.col-desde{width:55%}
											.bafar-table col.col-precio{width:25%}
											.bafar-table col.col-ahorro{width:20%}

											.bafar-table thead th{
												text-align:left; background:#f7f9fc; color:#334155; font-weight:700; padding:.6rem .75rem; border-bottom:1px solid #e6e8ef;
											}
											.bafar-table thead th.price,
											.bafar-table thead th.save{ text-align:right; }

											.bafar-table tbody td{
												padding:.6rem .75rem; border-bottom:1px solid #f0f2f6; vertical-align:middle;
											}
											.bafar-table tbody tr:last-child td{border-bottom:none}

											.bafar-table td.price{ text-align:right; font-weight:700; color:#0b2a6b }
											.bafar-table td.save{  text-align:right; font-weight:700; color:#0a7a2a }
											.bafar-dot{display:inline-block; width:.45rem; height:.45rem; border-radius:50%; background:#0866FD; margin-right:.45rem; vertical-align:middle}

											/* Responsive: tabla apilada */
											@media (max-width: 640px){
												.bafar-table thead{display:none}
												.bafar-table, .bafar-table tbody, .bafar-table tr, .bafar-table td {display:block; width:100%}
												.bafar-table tr{border-bottom:1px solid #eef1f6; padding:.35rem 0}
												.bafar-table td{padding:.45rem .75rem}
												.bafar-table td::before{
													content:attr(data-label); display:block; font-size:.8rem; color:#64748b; margin-bottom:.15rem; font-weight:600;
												}
												.bafar-table td.price, .bafar-table td.save{ text-align:left }
											}
											</style>';
											$css_done_vol = true;
										}

										echo '<div class="bafar-volume-open">';
										echo   '<div class="bafar-table-wrap">';
										echo     '<table class="bafar-table">';
										echo       '<colgroup>
														<col class="col-desde">
														<col class="col-precio">
														<col class="col-ahorro">
													</colgroup>';
										echo       '<thead><tr>';
										echo         '<th>Desde</th><th class="price">Precio</th><th class="save">Ahorro</th>';
										echo       '</tr></thead><tbody>';

										foreach ($rest as $k => $v) {
											if (!is_numeric($v)) continue;
											$v   = (float) $v;
											$qty = sb_parse_qty_from_key((string) $k); // tu helper
											$qty_txt = sb_format_qty((float) $qty) . ($unit ? ' ' . esc_html($unit) : '');

											// Ahorro vs base
											$save_txt = '—';
											if (!empty($base_price) && $base_price > 0 && $v < $base_price) {
												$pct = round((($base_price - $v) / $base_price) * 100);
												$save_txt = '-' . $pct . '%';
											}

											echo '<tr>';
											echo   '<td data-label="Desde"><span class="bafar-dot"></span>desde ' . esc_html($qty_txt) . '</td>';
											echo   '<td data-label="Precio" class="price">' . wc_price($v) . '</td>';
											echo   '<td data-label="Ahorro" class="save">' . esc_html($save_txt) . '</td>';
											echo '</tr>';
										}

										echo       '</tbody></table>';
										echo   '</div>'; // .bafar-table-wrap
										echo '</div>';   // .bafar-volume-open
									} else {
										if (count($tiers) === 1) {
											$only_tier = reset($tiers);
											echo '<div style="color: #0a7a2a; font-weight: 700; margin-top: 10px; padding: 10px; background: #f0f9f0; border-radius: 5px; border-left: 4px solid #0a7a2a;">';
											$tier_key = array_key_first($tiers);
											$tier_text = '';

											// Buscar el texto después de los paréntesis con decimales
											if (preg_match('/\(\d+\.\d+\)\s*(.+)$/', $tier_key, $matches)) {
												$tier_text = trim($matches[1]);
											}

											if ($tier_text) {
												echo '<i class="bi bi-info-circle-fill" style="margin-right: 8px;"></i>';
												echo esc_html($tier_text);
											} else {
												echo '<i class="bi bi-tag-fill" style="margin-right: 8px;"></i>';
												echo 'Precio especial disponible';
											}
											echo '</div>';
										}
									}
								}
							?>
						</div>

					</div>

				</div>


				<div class="custom-row bloque-informacion">


					<?php
					global $product;

					$terms = get_the_terms($product->get_id(), 'pais');

					if (!empty($terms) && !is_wp_error($terms)) {

						$term_names = array_map(function ($term) { ?>

							<div class="custom-3">
								<div class="site__imagen-texto-lateral">
									<figure class="site__imagen-texto-figure">
										<img decoding="async" src="<?php echo get_template_directory_uri(); ?>/img/flag.webp"
											alt="La Naval" class="site__imagen-texto-img">
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

					if (!empty($terms) && !is_wp_error($terms)) {

						$term_names = array_map(function ($term) { ?>

							<div class="custom-3">
								<div class="site__imagen-texto-lateral">
									<figure class="site__imagen-texto-figure">
										<img decoding="async" src="<?php echo get_template_directory_uri(); ?>/img/uvas.webp"
											alt="La Naval" class="site__imagen-texto-img">
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

					if (!empty($terms) && !is_wp_error($terms)) {

						$term_names = array_map(function ($term) { ?>

							<div class="custom-3">
								<div class="site__imagen-texto-lateral">
									<figure class="site__imagen-texto-figure">
										<img decoding="async"
											src="<?php echo get_template_directory_uri(); ?>/img/botellas.webp" alt="La Naval"
											class="site__imagen-texto-img">
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
						<p class="has-principal-color pb-1 mb-0 d-flex align-items-center"> <span>Peso variable</span>
							<span id="showInfoPeso"><i
									class="bi bi-info-circle-fill icon-con-tooltip has-azul-claro-color"></i></span>
						</p>
						<div class="cm-peso-tooltip">El peso de productos frescos, perecederos o porcionados puede
							legítimamente variar por la naturaleza del producto (corte, humedad, hueso, congelación,
							etc.) y esto podría impactar en el precio final.</div>
					</div>


					<?php
					$medios_pago_img = get_field('medios_pago_img', 'option'); // ajusta 'option' si no es en página de opciones
					
					if (!empty($medios_pago_img) && isset($medios_pago_img['url'])): ?>
						<div class="custom-100">
							<p class="has-principal-color pb-1 mb-0">Medios de pago</p>
							<img src="<?php echo esc_url($medios_pago_img['url']); ?>"
								alt="<?php echo esc_attr($medios_pago_img['alt']); ?>" class="single-product-medios-pago"
								loading="lazy" decoding="async" fetchpriority="low">
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
			<svg data-name="Layer 1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1200 120"
				preserveAspectRatio="none">
				<path d="M1200 120L0 16.48 0 0 1200 0 1200 120z" class="shape-fill"></path>
			</svg>
		</div>

		<div class="site__content-page">
			<div class="row-shortcode-section espacio-separadores AOFFF remove-products-grid">
				<?php echo do_shortcode('[productos_relacionados posts_per_page="4" columns="4"]'); ?>
			</div>
		</div>

		<div class="custom-shape-divider-bottom">
			<svg data-name="Layer 1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1200 120"
				preserveAspectRatio="none">
				<path d="M1200 120L0 16.48 0 0 1200 0 1200 120z" class="shape-fill"></path>
			</svg>
		</div>

	</section>

	<div class="espaciox2"></div>

</section>

<script>
	document.addEventListener("DOMContentLoaded", function () {
		// Detectar el evento de cierre del lightbox
		document.body.addEventListener("click", function (event) {
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
			lightboxContainer.addEventListener("pswpClosed", function () {
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
	document.addEventListener('DOMContentLoaded', function () {
		const minusButton = document.querySelector('.quantity-minus');
		const plusButton = document.querySelector('.quantity-plus');
		const quantityInput = document.querySelector('#quantity-input');

		if (!minusButton || !plusButton || !quantityInput) {
			return;
		}

		// Obtenemos el paso, el mínimo y el máximo desde los atributos del input
		const step = parseFloat(quantityInput.getAttribute('step')) || 1;
		const min = parseFloat(quantityInput.getAttribute('min')) || step;
		const max = quantityInput.hasAttribute('max') ? parseFloat(quantityInput.getAttribute('max')) : null;
		const decimals = (step.toString().split('.')[1] || '').length;

		function formatValue(val) {
			return decimals > 0 ? parseFloat(val).toFixed(decimals) : parseInt(val, 10);
		}

		minusButton.addEventListener('click', function () {
			let currentValue = parseFloat(quantityInput.value) || min;
			let newValue = currentValue - step;
			if (newValue < min) newValue = min;
			quantityInput.value = formatValue(newValue);
			quantityInput.dispatchEvent(new Event('change', { bubbles: true }));
		});

		plusButton.addEventListener('click', function () {
			let currentValue = parseFloat(quantityInput.value) || min;
			let newValue = currentValue + step;
			if (max !== null && newValue > max) newValue = max;
			quantityInput.value = formatValue(newValue);
			quantityInput.dispatchEvent(new Event('change', { bubbles: true }));
		});

		quantityInput.addEventListener('change', function () {
			let value = parseFloat(quantityInput.value) || min;
			if (value < min) value = min;
			if (max !== null && value > max) value = max;
			quantityInput.value = formatValue(value);
		});
	});
</script>

<?php get_footer('shop'); ?>