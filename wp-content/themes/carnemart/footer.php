<?php
$imgLogoFooter = get_field('logotipo_footer_desktop', 'option');
$bloque_1_footer = get_field('contenido_footer_1', 'option');

$banner_footer_img = get_field('banner_footer_img', 'option');
$banner_footer_txt = get_field('banner_footer_txt', 'option');
$banner_footer_descripcion = get_field('banner_footer_descripcion', 'option');
$banner_footer_color_txt = get_field('banner_footer_color_txt', 'option');
$banner_footer_color_fondo = get_field('banner_footer_color_fondo', 'option');

$medios_pago_img = get_field('medios_pago_img', 'option');

?>


<?php
$linkD = get_field('numero_whatsapp', 'option');
if ($linkD):
  $link_url = $linkD['url'];
  $link_title = $linkD['title'];
  $link_target = $linkD['target'] ? $linkD['target'] : '_self';
  ?>
  <a class="flotante-cta-whats" href="<?php echo esc_url($link_url); ?>" target="<?php echo esc_attr($link_target); ?>"><i
      class="bi bi-whatsapp"></i></a>
<?php endif; ?>


<?php if (get_field('show_barra_footer', 'option')) { ?>
  <!-- Footer Seccion 1 -->
  <div class="site__footer footer__seccion-01"
    style="background-color:<?php echo $banner_footer_color_fondo; ?>!important; color:<?php echo $banner_footer_color_txt; ?>;  background-image: url('<?php echo $banner_footer_img; ?>');">
    <div class="site__footer-box1">

      <div class="banner_footer__box-contenido">

        <div class="banner_footer__box-titulo-descripcion">

          <?php if ($banner_footer_txt) { ?>
            <h2><b><?php echo $banner_footer_txt; ?></b></h2>
          <?php } ?>

          <?php if ($banner_footer_descripcion) { ?>
            <p><?php echo $banner_footer_descripcion; ?></p>
          <?php } ?>

        </div>
      </div>

      <div class="banner_footer-box-form">
        <?php echo do_shortcode(get_field('banner_footer_shortcode', 'option'));
        ?>
      </div>

    </div>
  </div>
<?php } ?>






<!-- Footer Seccion 2 -->
<div class="site__footer footer__seccion-02">
  <div class="site__footer-box2">


    <div class="site__footer-columna-01">

      <?php


      if (!empty($imgLogoFooter) && isset($imgLogoFooter['url'])): ?>
        <div class="site__footer-logo">
          <img src="<?php echo esc_url($imgLogoFooter['url']); ?>" alt="<?php echo esc_attr($imgLogoFooter['alt']); ?>"
            class="site_footer-logo-img img-responsiva" loading="lazy" decoding="async" fetchpriority="low">
        </div>
      <?php endif; ?>


      <!-- Redes -->
      <?php get_template_part('template-parts/bloques/redes'); ?>

    </div>

    <div class="site__footer-columna-02">

      <!-- Menus footer -->
      <?php get_template_part('template-parts/menus/menu-footer'); ?>

      <?php get_template_part('template-parts/menus/menu-footer-2'); ?>

      <?php get_template_part('template-parts/menus/menu-footer-3'); ?>

      <?php get_template_part('template-parts/menus/menu-footer-4'); ?>

    </div>

  </div>
</div>


<!-- Footer Seccion 3 -->
<div class="site__footer footer__seccion-03">
  <div class="site__footer-box3">

    <?php
    $medios_pago_img = get_field('medios_pago_img', 'option'); // ajusta 'option' si no es en página de opciones
    
    if (!empty($medios_pago_img) && isset($medios_pago_img['url'])): ?>
      <img src="<?php echo esc_url($medios_pago_img['url']); ?>" alt="<?php echo esc_attr($medios_pago_img['alt']); ?>"
        class="site__footer-medios-pago" loading="lazy" decoding="async" fetchpriority="low">
    <?php endif; ?>

    <hr class="separador-footer">
    <!-- Caja de legales -->
    <?php if ($bloque_1_footer): ?>
      <div class="site__footer-contenido-legales legales-box01">
        <?php echo $bloque_1_footer; ?>
      </div>
    <?php endif ?>

  </div>
</div>

</div>

<!-- Modales de consulta general -->
<?php get_template_part('template-parts/modales/modal-permiso-ubicacion'); ?>

</body>


<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
  integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
<link rel="stylesheet" type="text/css" href="//cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.css" />
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"
  integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js"
  integrity="sha384-0pUGZvbkm6XF6gxjEnlmuGrJXVbNuzT9qBBavbLwCsOGabYfZo0T0to5eqruptLy" crossorigin="anonymous"></script>
<script src="<?php echo get_template_directory_uri(); ?>/js/script-megamenu.js"></script>


<?php wp_footer(); ?>
<script type="text/javascript" src="//cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.min.js"></script>

</html>