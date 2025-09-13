<?php

get_header(); ?>

<div class="localizador-container">
    <div class="listado-sucursales">
        <?php
        // Query para obtener las sucursales
        $sucursales = new WP_Query([
            'post_type'      => 'sucursal',
            'posts_per_page' => -1,
        ]);

        if ($sucursales->have_posts()) :
            while ($sucursales->have_posts()) : $sucursales->the_post();
                $coordenadas = get_field('coordenadas'); // Coordenadas del campo ACF
                $direccion = get_field('direccion');
                $telefono = get_field('telefono');
                $horario = get_field('horario');
        ?>
                <div class="sucursal-item"
                    data-lat="<?php echo esc_attr($coordenadas['lat']); ?>"
                    data-lng="<?php echo esc_attr($coordenadas['lng']); ?>"
                    data-title="<?php echo esc_attr(get_the_title()); ?>">
                    <h3><?php the_title(); ?></h3>
                    <p><strong>Dirección:</strong> <?php echo esc_html($direccion); ?></p>
                    <?php if ($telefono) : ?>
                        <p><strong>Teléfono:</strong> <?php echo esc_html($telefono); ?></p>
                    <?php endif; ?>
                    <?php if ($horario) : ?>
                        <p><strong>Horario:</strong> <?php echo esc_html($horario); ?></p>
                    <?php endif; ?>
                </div>
        <?php
            endwhile;
            wp_reset_postdata();
        else :
            echo '<p>No hay sucursales disponibles.</p>';
        endif;
        ?>
    </div>

    <button id="btn-ubicacion" class="btn-ubicacion">Compartir mi ubicación</button>
    <div id="mapa" style="width: 100%; height: 500px;"></div>
</div>


<script>
    function initMap() {
        var map = new google.maps.Map(document.getElementById('mapa'), {
            center: {
                lat: -34.397,
                lng: 150.644
            }, // Coordenadas iniciales (puedes ajustarlas)
            zoom: 14,
        });

        var bounds = new google.maps.LatLngBounds(); // Para ajustar automáticamente el zoom al contenido
        var markers = [];

        // Crea una instancia global de InfoWindow (una sola)
        var infoWindow = new google.maps.InfoWindow();

        // Recorrer los elementos de sucursales en el DOM
        document.querySelectorAll('.sucursal-item').forEach(function(elemento) {
            var lat = parseFloat(elemento.dataset.lat);
            var lng = parseFloat(elemento.dataset.lng);
            var title = elemento.dataset.title;

            // Validar si las coordenadas son números válidos
            if (!isNaN(lat) && !isNaN(lng)) {
                var marker = new google.maps.Marker({
                    position: {
                        lat: lat,
                        lng: lng
                    },
                    map: map,
                    title: title,
                });

                bounds.extend(marker.getPosition()); // Agregar el marcador al ajuste automático del mapa

                // Evento para mostrar InfoWindow en el marcador
                marker.addListener('click', function() {
                    infoWindow.setContent(`
                <h3>${title}</h3>
                <p><strong>Dirección:</strong> ${elemento.querySelector('p').innerHTML}</p>
                ${elemento.innerHTML.replace(`<h3>${title}</h3>`, '')} <!-- Evita duplicar el título -->
            `);
                    infoWindow.open(map, marker);
                });

                // Evento para centrar el mapa y abrir InfoWindow al hacer clic en el listado
                elemento.addEventListener('click', function() {
                    map.setCenter(marker.getPosition());
                    infoWindow.setContent(`
                <h3>${title}</h3>
                <p><strong>Dirección:</strong> ${elemento.querySelector('p').innerHTML}</p>
                ${elemento.innerHTML.replace(`<h3>${title}</h3>`, '')} <!-- Evita duplicar el título -->
            `);
                    infoWindow.open(map, marker);
                });

                markers.push(marker);
            } else {
                console.error(`Coordenadas inválidas para: ${title}`);
            }
        });


        // Ajustar el zoom y el centro del mapa automáticamente
        map.fitBounds(bounds);
    }

    // Cargar el script de Google Maps con el callback
    function loadGoogleMapsAPI() {
        var script = document.createElement('script');
        script.src = `https://maps.googleapis.com/maps/api/js?key=AIzaSyDMDBDU9aMaoHI2ov7Ywa6_Jo9gDMhjGOc&callback=initMap`;
        script.async = true;
        script.defer = true;
        document.body.appendChild(script);
    }

    document.addEventListener('DOMContentLoaded', loadGoogleMapsAPI);
</script>


<script>
    document.addEventListener('DOMContentLoaded', function() {
        const botonUbicacion = document.getElementById('btn-ubicacion');

        botonUbicacion.addEventListener('click', function() {
            if (navigator.geolocation) {
                // Solicita la ubicación del usuario
                navigator.geolocation.getCurrentPosition(
                    function(position) {
                        const userLat = position.coords.latitude;
                        const userLng = position.coords.longitude;

                        // Centra el mapa en la ubicación del usuario
                        const userLocation = new google.maps.LatLng(userLat, userLng);
                        map.setCenter(userLocation);
                        map.setZoom(12);

                        // Agrega un marcador en la ubicación del usuario
                        const userMarker = new google.maps.Marker({
                            position: userLocation,
                            map: map,
                            title: "Tu ubicación",
                            icon: {
                                url: "https://maps.google.com/mapfiles/ms/icons/blue-dot.png", // Marcador azul
                            },
                        });

                        // Calcula y resalta las sucursales cercanas (opcional)
                        calcularSucursalesCercanas(userLat, userLng);
                    },
                    function(error) {
                        alert("No pudimos obtener tu ubicación. Por favor, verifica los permisos.");
                    }
                );
            } else {
                alert("La geolocalización no está disponible en tu navegador.");
            }
        });
    });

    // Función para calcular sucursales cercanas (opcional)
    function calcularSucursalesCercanas(lat, lng) {
        const maxDistancia = 10; // En kilómetros
        const radianes = (grados) => grados * (Math.PI / 180);

        document.querySelectorAll('.sucursal-item').forEach(function(elemento) {
            const sucursalLat = parseFloat(elemento.dataset.lat);
            const sucursalLng = parseFloat(elemento.dataset.lng);

            // Calcula la distancia entre la ubicación del usuario y la sucursal
            const distancia = 6371 * Math.acos(
                Math.cos(radianes(lat)) *
                Math.cos(radianes(sucursalLat)) *
                Math.cos(radianes(sucursalLng) - radianes(lng)) +
                Math.sin(radianes(lat)) * Math.sin(radianes(sucursalLat))
            );

            // Resalta sucursales cercanas
            if (distancia <= maxDistancia) {
                elemento.style.backgroundColor = "#d4edda"; // Verde claro
            } else {
                elemento.style.backgroundColor = ""; // Sin color
            }
        });
    }
</script>





<?php get_footer(); ?>