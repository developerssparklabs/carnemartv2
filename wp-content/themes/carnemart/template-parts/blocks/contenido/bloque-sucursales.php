<?php

$className = 'section-listado-sucursales';

if (!empty($block['className'])) {
    $className .= ' ' . $block['className'];
}

if (!empty($block['align'])) {
    $className .= 'align' . $block['align'];
}

$id_seccion = get_field('id_seccion');
$seccion_fondo_color = get_field('seccion_fondo_color');
?>

<section class="site__section site__section-listado-sucursales <?php echo esc_attr($className); ?>" id="<?php echo esc_attr($id_seccion); ?>" style="background-color:<?php echo esc_attr($seccion_fondo_color); ?>!important;">

    <div class="bloque-botonera">
        <button id="btn-ubicacion" class="btn-ubicacion"><i class="bi bi-geo-alt-fill"></i> <span>Compartir mi ubicación</span></button>
    </div>
    <div class="localizador-container">

        <div class="listado-sucursales">
            <?php
            $sucursales = new WP_Query([
                'post_type'      => 'sucursal',
                'posts_per_page' => -1,
                'orderby'        => 'menu_order',
                'order'          => 'ASC',
            ]);

            if ($sucursales->have_posts()) :
                while ($sucursales->have_posts()) : $sucursales->the_post();
                    $coordenadas = get_post_meta(get_the_ID(), 'coordenadas', true);
                    $direccion = get_post_meta(get_the_ID(), 'direccion', true);
                    $telefono = get_post_meta(get_the_ID(), 'telefono', true);
                    $horario = get_post_meta(get_the_ID(), 'horario', true);

                    if (is_array($coordenadas) && isset($coordenadas['lat'], $coordenadas['lng'])) : ?>
                        <div class="sucursal-item"
                            data-lat="<?php echo esc_attr($coordenadas['lat']); ?>"
                            data-lng="<?php echo esc_attr($coordenadas['lng']); ?>"
                            data-title="<?php echo esc_attr(get_the_title()); ?>"
                            data-direccion="<?php echo esc_attr($direccion); ?>"
                            data-telefono="<?php echo esc_attr($telefono); ?>"
                            data-horario="<?php echo esc_attr($horario); ?>"
                            data-id="<?php echo esc_attr(get_the_ID()); ?>"
                            data-shortcode='<?php echo esc_attr(do_shortcode('[sucursal_slider id="' . get_the_ID() . '"]')); ?>'> 

                            <h3><?php the_title(); ?></h3>
                            <?php if ($direccion) : ?>
                                <p class="suc_direccion"><i class="bi bi-geo-alt-fill"></i> <?php echo esc_html($direccion); ?></p>
                            <?php endif; ?>
                            <?php if ($telefono) : ?>
                                <p class="suc_telefono"><strong>Teléfono:</strong> <?php echo esc_html($telefono); ?></p>
                            <?php endif; ?>
                            <?php if ($horario) : ?>
                                <p class="suc_horario"><strong>Horario:</strong> <?php echo esc_html($horario); ?></p>
                            <?php endif; ?>
                        </div>
                    <?php else : ?>
                        <p class="sucursal-error">Coordenadas no disponibles para: <?php the_title(); ?></p>
                    <?php endif; ?>
            <?php
                endwhile;
                wp_reset_postdata();
            else :
                echo '<p>No hay sucursales disponibles.</p>';
            endif;
            ?>
        </div>

        <div id="mapa"></div>

    </div>

    <script>
        var map;

        function initMap() {
            map = new google.maps.Map(document.getElementById('mapa'), {
                center: { lat: 19.432608, lng: -99.133209 },
                zoom: 14,
            });

            var bounds = new google.maps.LatLngBounds();
            var infoWindow = new google.maps.InfoWindow();

            document.querySelectorAll('.sucursal-item').forEach(function(elemento) {
                var lat = parseFloat(elemento.dataset.lat);
                var lng = parseFloat(elemento.dataset.lng);
                var title = elemento.dataset.title;
                var direccion = elemento.dataset.direccion;
                var telefono = elemento.dataset.telefono;
                var horario = elemento.dataset.horario;
                var sucursalId = elemento.dataset.id;
                var sliderHTML = elemento.dataset.shortcode; // 🔹 Tomamos el slider ya generado en PHP

                if (!isNaN(lat) && !isNaN(lng)) {
                    var marker = new google.maps.Marker({
                        position: { lat: lat, lng: lng },
                        map: map,
                        title: title,
                    });

                    bounds.extend(marker.getPosition());

                    function mostrarInfoWindow() {
                        let content = `
                            <h3>${title}</h3>
                            <p><strong>Dirección:</strong> ${direccion}</p>
                            ${telefono ? `<p><strong>Teléfono:</strong> ${telefono}</p>` : ''}
                            ${horario ? `<p><strong>Horario:</strong> ${horario}</p>` : ''}
                            <div class="slider-sucursal">${sliderHTML}</div>
                        `;

                        infoWindow.setContent(content);
                        infoWindow.open(map, marker);

                        // Esperar y reinicializar Slick Slider
                        setTimeout(function () {
                            jQuery('.slick-slider-sucursal').not('.slick-initialized').slick({
                                autoplay: true,
                                autoplaySpeed: 4000,
                                arrows: false,
                                dots: false,
                                infinite: true,
                                speed: 500,
                                slidesToShow: 1,
                                slidesToScroll: 1
                            });
                        }, 500);
                    }

                    marker.addListener('click', function() {
                        map.setCenter(marker.getPosition());
                        mostrarInfoWindow();
                    });

                    elemento.addEventListener('click', function() {
                        map.setCenter(marker.getPosition());
                        mostrarInfoWindow();
                    });

                } else {
                    console.error(`Coordenadas inválidas para: ${title}`);
                }
            });

            map.fitBounds(bounds);
        }

        function loadGoogleMapsAPI() {
            var script = document.createElement('script');
            script.src = `https://maps.googleapis.com/maps/api/js?key=AIzaSyDMDBDU9aMaoHI2ov7Ywa6_Jo9gDMhjGOc&callback=initMap`;
            script.async = true;
            script.defer = true;
            document.body.appendChild(script);
        }

        document.addEventListener('DOMContentLoaded', loadGoogleMapsAPI);
    </script>
</section>
