<?php

$className = 'section-listado-categorias-principales';

if (!empty($block['className'])) {

    $className .= ' ' . $block['className'];
}

if (!empty($block['align'])) {
    $className .= 'align' . $block['align'];
}

$id_seccion = get_field('id_seccion');
$seccion_fondo_color = get_field('seccion_fondo_color');
?>





<section class="site__section site__section-grid-categorias <?php echo esc_attr($className); ?>" id="<?php echo $id_seccion; ?>" style="background-color:<?php echo $seccion_fondo_color; ?>!important;">



    <div class="site__section-grid-categorias-wrapper">

        <a href="//localhost:3000/lanaval/categoria-producto/sin-categorizar/" class="site__section-grid-categorias-caja caja-con-contenido es-Animado" style="background-image: url();">
            <div class="item-contenido">
                <h2>Sin categorizar</h2>
            </div>
        </a>

        <div class="site__section-grid-categorias-caja">

            <a href="//localhost:3000/lanaval/categoria-producto/aog-2/" class="site__section-grid-categorias-caja-internax2 caja-con-contenido es-Animado" style="background-image: url(//localhost:3000/lanaval/wp-content/uploads/2024/09/bg-cinto-la-naval.webp);">
                <div class="item-contenido">
                    <h2>AOG 2</h2>
                </div>
            </a>

            <a href="//localhost:3000/lanaval/categoria-producto/categoria-4/" class="site__section-grid-categorias-caja-internax2 caja-con-contenido es-Animado" style="background-image: url(//localhost:3000/lanaval/wp-content/uploads/woocommerce-placeholder.png);">
                <div class="item-contenido">
                    <h2>Categoria 4</h2>
                </div>
            </a>

        </div>

        <a href="//localhost:3000/lanaval/categoria-producto/categoria-aog-1/" class="site__section-grid-categorias-caja caja-con-contenido es-Animado" style="background-image: url(//localhost:3000/lanaval/wp-content/uploads/2024/12/Captura-de-pantalla-2024-12-11-a-las-11.57.40 a.m.png);">
            <div class="item-contenido">
                <h2>Categoria AOG 1</h2>
            </div>
        </a>

        <div class="site__section-grid-categorias-caja">

            <a href="//localhost:3000/lanaval/categoria-producto/categorua-5/" class="site__section-grid-categorias-caja-internax2 caja-con-contenido es-Animado" style="background-image: url(//localhost:3000/lanaval/wp-content/uploads/2024/12/banner-web-1400x600-1.webp);">
                <div class="item-contenido">
                    <h2>Categorua 5</h2>
                </div>
            </a>

            <a href="//localhost:3000/lanaval/categoria-producto/sexta-categoria/" class="site__section-grid-categorias-caja-internax2 caja-con-contenido es-Animado" style="background-image: url(//localhost:3000/lanaval/wp-content/uploads/2024/12/Captura-de-pantalla-2024-12-11-a-las-11.57.40 a.m.png);">
                <div class="item-contenido">
                    <h2>Sexta categoria</h2>
                </div>
            </a>
        </div>

    </div>


</section>