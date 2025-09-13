<!doctype html>
<html <?php language_attributes(); ?> class="no-js sr">

<head>

	<meta charset="<?php bloginfo('charset'); ?>">
	<link rel="profile" href="https://gmpg.org/xfn/11">
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<script src="https://cdn.jsdelivr.net/modernizr/3.3.1/modernizr.min.js"></script>
	<?php wp_head(); ?>

</head>

<body <?php body_class('master-class-site'); ?>>

	<!-- Modal de alerta y promos -->
	<?php //echo do_shortcode('[mcp_modal]'); 
	?>

	<!-- Se muestra el bllque de sidebar de carito -->
	<?php echo do_shortcode('[carrito_lateral]'); ?>

	<div class="site__wrapper" id="isTop">

		<!-- Header -->
		<header class="site__header fixed-header">

			<?php
			if (get_field('mostrar_marquesina', 'option')) {
				$texto_marquesina = get_field('texto_marquesina', 'option');
				$fondo_marquesina = get_field('fondo_marquesina', 'option');
				$color_marquesina = get_field('color_marquesina', 'option');
				echo '<div class="banner-marquesina" style="background-color:' . esc_html($fondo_marquesina) . '!important; color:' . esc_html($color_marquesina) . ' !important;"><marquee behavior="scroll" direction="left">' . esc_html($texto_marquesina) . '</marquee></div>';
			}
			?>

			<div class="site__header-box1">

				<div class="site__header-informacion-lateral">
					<?php
					$img_banner = get_field('banner_bloque_superior', 'option');
					if ($img_banner) {
					?>
						<div class="site__header-banner-superior">
							<figure class="site__header-banner-figure">
								<img src="<?php echo $img_banner['url']; ?>" alt="<?php echo $img_banner['alt']; ?>" class="site__header-banner-img" loading="eager" fetchpriority="high">
							</figure>
							<div class="site__header-banner-contenido">
								<?php
								$link = get_field('enlace_bloque_superior', 'option');
								if ($link):
									$link_url = $link['url'];
									$link_title = $link['title'];
									$link_target = $link['target'] ? $link['target'] : '_self';
								?>
									<a class="site__header-banner-link" href="<?php echo esc_url($link_url); ?>" title="Inicio" target="<?php echo esc_attr($link_target); ?>"><?php echo esc_html($link_title); ?></a>
								<?php endif; ?>
							</div>
						</div>
					<?php
					}
					?>


				</div>



				<div class="site__header-sidebar">

					<button class="btn-top-simple-icon btn-azul btnOpenMenuGiros" aria-label="Menú de giros de negocio"><i class="bi bi-bag"></i></button>

					<?php echo do_shortcode('[iniciar_sesion_o_mostrar_menu]'); ?>

					<?php get_template_part('template-parts/bloques/redes'); ?>

					<?php echo do_shortcode('[icono_carrito]'); ?>
				</div>

			</div><!--site__header-box1-->


			<div class="site__header-box2">

				<!-- Logo Site -->
				<div class="site__logo">
					<a href="<?php echo home_url(); ?>/" title="Inicio"
						class="site__logo-link" aria-label="<?php bloginfo('name'); ?>">

						<?php
						$custom_logo_id = get_theme_mod('custom_logo');

						if ($custom_logo_id && has_custom_logo()) {
							// Tamaños base (1x y 2x)
							$src_190 = wp_get_attachment_image_src($custom_logo_id, array(190, 0)); // ~190w
							$src_380 = wp_get_attachment_image_src($custom_logo_id, array(380, 0)); // ~380w (retina)
							$full    = wp_get_attachment_image_src($custom_logo_id, 'full');

							// Fallbacks
							$src      = $src_190 ? $src_190[0] : ($full ? $full[0] : '');
							$full_w   = $full ? (int)$full[1] : 0;
							$full_h   = $full ? (int)$full[2] : 0;

							// Altura numérica calculada para 190px de ancho (evita CLS)
							$target_w = 190;
							$target_h = ($full_w > 0 && $full_h > 0) ? (int) round($full_h * ($target_w / $full_w)) : null;

							// Construir srcset
							$srcset_parts = array();
							if ($src_190) $srcset_parts[] = esc_url($src_190[0]) . ' 190w';
							if ($src_380) $srcset_parts[] = esc_url($src_380[0]) . ' 380w';
							if ($full)    $srcset_parts[] = esc_url($full[0])    . ' ' . (int)$full[1] . 'w';
							$srcset = implode(', ', $srcset_parts);

							// El logo mide ~150px en móvil y 190px en desktop
							$sizes = '(max-width: 768px) 150px, 190px';

							echo '<img class="site__logo-img"'
								. ' src="' . esc_url($src) . '"'
								. ($srcset ? ' srcset="' . esc_attr($srcset) . '"' : '')
								. ' sizes="' . esc_attr($sizes) . '"'
								. ' alt="' . esc_attr(get_bloginfo('name')) . '"'
								. ' loading="eager" fetchpriority="high" decoding="async"'
								. ' width="' . $target_w . '"'
								. ($target_h ? ' height="' . $target_h . '"' : '')
								. '>';
						} else {
							echo '<p>' . esc_html(get_bloginfo('name')) . '</p>';
						}
						?>

					</a>
				</div>


				<div class="site__header-ctas-and-search">

					<button class="btn-top-menu btn-azul btnOpenMegaMenuProductos" aria-label="Menú de productos"><span>Productos</span> <i class="bi bi-list"></i></button>

					<button class="btn-top-menu btn-blanco btnOpenMenuGiros" aria-label="Menú de giros"><i class="bi bi-bag"></i> <span>Giro de Negocio</span> <i class="bi bi-caret-down-fill" style="font-size: 12px;"></i></button>

					<!-- Buscador -->
					<div class="site__header-search-form">
						<?php get_template_part('searchform'); ?>
					</div>
				</div>

				<!-- Contenedor de menu principal Desktop-->

			</div><!--site__header-box2-->


			<div class="site__header-box3">
				<!-- Buscador -->
				<div class="site__header-search-form full-width">
					<?php get_template_part('searchform'); ?>
				</div>
			</div><!--site__header-box3-->



			<!-- Buscador de Sucursales -->
			<?php get_template_part('template-parts/bloques/barra-de-seleccion-tienda'); ?>


			<!-- Boton de Menu mobile -->
			<button class="ctaMenuMobile" id="ctaMenuMobile" aria-label="Menú">
				<i class="bi bi-three-dots-vertical"></i>
			</button>

		</header><!--site__header-->





		<!-- Menu Mobile -->
		<section id="boxMenuPrincipal" class="site__header-menu-mobile">

			<!-- Boton para cerrar el menu mobile -->
			<div class="ctaCloseMenu" id="ctaCloseMenu">
				<i class="bi bi-x"></i>
			</div>

			<div class="site__menu-mobile-servicio">
				<?php
				$link = get_field('enlace_bloque_superior', 'option');
				if ($link):
					$link_url = $link['url'];
					$link_title = $link['title'];
					$link_target = $link['target'] ? $link['target'] : '_self';
				?>
					<a class="enlace-servicio" href="<?php echo esc_url($link_url); ?>" target="<?php echo esc_attr($link_target); ?>"> <i class="bi bi-info-circle"></i> <?php echo esc_html($link_title); ?></a>
				<?php endif; ?>
			</div>

			<div class="sitew-container">

				<!-- Buscador en mobile -->
				<div class="site__menu-mobile-buscador">
					<?php get_template_part('searchform'); ?>
				</div>



				<!-- Menu Principal en mobile -->
				<div class="site__menu-mobile-principal">
					<?php get_template_part('template-parts/menus/menu-principal'); ?>
				</div>

			</div>
		</section>
		<!-- Menu Mobile -->



		<!-- Megamenu de Productos -->
		<div class="box-megamenu-productos">

			<div class="header-menu-productos">

				<div class="site__logo">
					<a href="<?php echo home_url(); ?>/" title="Inicio" class="site__logo-link">
						<?php
						$custom_logo_id = get_theme_mod('custom_logo');
						$logo = wp_get_attachment_image_src($custom_logo_id, 'full');
						if (has_custom_logo()) {
							echo '<img class="site__logo-img" src="' . esc_url($logo[0]) . '" alt="' . get_bloginfo('name') . '">';
						} else {
							echo '<p>' . get_bloginfo('name') . '</p>';
						}
						?>
					</a>
				</div>

				<button class="btn-simple-icon btn-azul btnCloseMegaMenuProductos" aria-label="Cerrar menú productos">
					<i class="bi bi-x-circle-fill"></i>
				</button>

			</div>
			<?php echo do_shortcode('[wc_menu_categorias columns="6" hide_empty="1" breakpoint="992"]'); ?>

		</div>

		<div class="box-megamenu-giros">
			<div class="caja-menu-giros">
				<div class="espacio-titulo">Giro<br> de negocio</div>
				<div class="espacio-menu">
					<div class="menu-giro-negocio">
						<?php echo do_shortcode('[menus_giro_negocio_header]'); ?>
					</div>
				</div>
			</div>
			<button class="btn-simple-icon btn-azul btnCloseMegaMenuGiros menu-giro-cerrar" aria-label="Cerrar menú Giros">
				<i class="bi bi-x-circle-fill"></i>
			</button>
		</div>