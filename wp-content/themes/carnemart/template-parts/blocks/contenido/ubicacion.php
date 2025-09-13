<?php

$className = 'section-ubiacion';

if (!empty($block['className'])) {

    $className .= ' ' . $block['className'];
}

if (!empty($block['align'])) {
    $className .= 'align' . $block['align'];
}


$id_seccion = get_field('id_seccion');



$seccion_esp_sup = get_field('seccion_esp_sup');
$seccion_esp_inf = get_field('seccion_esp_inf');
$seccion_fondo_color = get_field('seccion_fondo_color');
?>



<section class="section-sitew <?php echo esc_attr($className); ?> <?php echo $seccion_esp_sup; ?> <?php echo $seccion_esp_inf; ?>" id="<?php echo $id_seccion; ?>" style="background-color:<?php echo $seccion_fondo_color; ?>!important;">
    <div class="custom-container">
        <div class="row justify-content-between align-items-center">

            <div class="col-md-6 load-hidden intro-bottom p-5">
                <div class="inner-box-content-info">



                    <?php if (have_rows('listado_contenido')) : ?>
                        <ul class="listado-icono-contenido <?php echo $tipo_lista; ?>">
                            <?php while (have_rows('listado_contenido')) : the_row();
                                $icono = get_sub_field('icono');
                                $titulo = get_sub_field('titulo');
                                $contenido = get_sub_field('contenido');
                            ?>
                                <li class="listado-icono-contenido">
                                    <?php if ($icono) { ?>
                                        <span class="icon-lateral <?php echo $icono; ?>"></span>
                                    <?php } ?>
                                    <div class="box-info">

                                        <?php if ($titulo) { ?>
                                            <h3 class="bloque-titulo">
                                                <?php echo $titulo; ?>
                                            </h3>
                                        <?php } ?>

                                        <?php if ($contenido) { ?>
                                            <div class="bloque-contenido">
                                                <?php echo $contenido; ?>
                                                <div>
                                                <?php } ?>
                                                </div>
                                </li>
                            <?php endwhile; ?>
                        </ul>
                    <?php endif; ?>

                </div>
            </div>

            <div class="col-md-6 load-hidden intro-bottom p-2">
                <div class="inner-box-content-info">

                    <div class="espacio-titulo-contenido">


                        <?php
                        $location = get_field('ubicacion_mapa');
                        if ($location) : ?>
                            <div class="acf-map" data-zoom="16">
                                <div class="marker" data-lat="<?php echo esc_attr($location['lat']); ?>" data-lng="<?php echo esc_attr($location['lng']); ?>"></div>
                            </div>
                        <?php endif; ?>
                    </div>

                </div>
            </div>

        </div><!--row-->
    </div>
</section>

<style type="text/css">
    .acf-map {
        width: 100%;
        height: 650px;
        margin: 20px 0;
    }

    @media screen and (max-width:680px) {
        .acf-map {
            width: 100%;
            height: 400px;
        }
    }

    // Fixes potential theme css conflict.
    .acf-map img {
        max-width: inherit !important;
    }
</style>
<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyDMDBDU9aMaoHI2ov7Ywa6_Jo9gDMhjGOc&callback=Function.prototype"></script>
<script type="text/javascript">
    (function($) {

        /**
         * initMap
         *
         * Renders a Google Map onto the selected jQuery element
         *
         * @date    22/10/19
         * @since   5.8.6
         *
         * @param   jQuery $el The jQuery element.
         * @return  object The map instance.
         */
        function initMap($el) {

            // Find marker elements within map.
            var $markers = $el.find('.marker');

            // Create gerenic map.
            var mapArgs = {
                zoom: $el.data('zoom') || 17,
                // mapTypeId: google.maps.MapTypeId.ROADMAP
                styles: [{
                        "elementType": "geometry",
                        "stylers": [{
                            "color": "#f5f5f5"
                        }]
                    },
                    {
                        "elementType": "labels.icon",
                        "stylers": [{
                            "visibility": "off"
                        }]
                    },
                    {
                        "elementType": "labels.text.fill",
                        "stylers": [{
                            "color": "#616161"
                        }]
                    },
                    {
                        "elementType": "labels.text.stroke",
                        "stylers": [{
                            "color": "#f5f5f5"
                        }]
                    },
                    {
                        "featureType": "administrative.land_parcel",
                        "elementType": "labels.text.fill",
                        "stylers": [{
                            "color": "#bdbdbd"
                        }]
                    },
                    {
                        "featureType": "poi",
                        "elementType": "geometry",
                        "stylers": [{
                            "color": "#eeeeee"
                        }]
                    },
                    {
                        "featureType": "poi",
                        "elementType": "labels.text.fill",
                        "stylers": [{
                            "color": "#757575"
                        }]
                    },
                    {
                        "featureType": "poi.park",
                        "elementType": "geometry",
                        "stylers": [{
                            "color": "#e5e5e5"
                        }]
                    },
                    {
                        "featureType": "poi.park",
                        "elementType": "labels.text.fill",
                        "stylers": [{
                            "color": "#9e9e9e"
                        }]
                    },
                    {
                        "featureType": "road",
                        "elementType": "geometry",
                        "stylers": [{
                            "color": "#ffffff"
                        }]
                    },
                    {
                        "featureType": "road.arterial",
                        "elementType": "labels.text.fill",
                        "stylers": [{
                            "color": "#757575"
                        }]
                    },
                    {
                        "featureType": "road.highway",
                        "elementType": "geometry",
                        "stylers": [{
                            "color": "#dadada"
                        }]
                    },
                    {
                        "featureType": "road.highway",
                        "elementType": "labels.text.fill",
                        "stylers": [{
                            "color": "#616161"
                        }]
                    },
                    {
                        "featureType": "road.local",
                        "elementType": "labels.text.fill",
                        "stylers": [{
                            "color": "#9e9e9e"
                        }]
                    },
                    {
                        "featureType": "transit.line",
                        "elementType": "geometry",
                        "stylers": [{
                            "color": "#e5e5e5"
                        }]
                    },
                    {
                        "featureType": "transit.station",
                        "elementType": "geometry",
                        "stylers": [{
                            "color": "#eeeeee"
                        }]
                    },
                    {
                        "featureType": "water",
                        "elementType": "geometry",
                        "stylers": [{
                            "color": "#c9c9c9"
                        }]
                    },
                    {
                        "featureType": "water",
                        "elementType": "labels.text.fill",
                        "stylers": [{
                            "color": "#9e9e9e"
                        }]
                    }
                ]

            };
            var map = new google.maps.Map($el[0], mapArgs);

            // Add markers.
            map.markers = [];
            $markers.each(function() {
                initMarker($(this), map);
            });

            // Center map based on markers.
            centerMap(map);

            // Return map instance.
            return map;
        }

        /**
         * initMarker
         *
         * Creates a marker for the given jQuery element and map.
         *
         * @date    22/10/19
         * @since   5.8.6
         *
         * @param   jQuery $el The jQuery element.
         * @param   object The map instance.
         * @return  object The marker instance.
         */
        function initMarker($marker, map) {

            // Get position from marker.
            var lat = $marker.data('lat');
            var lng = $marker.data('lng');
            var latLng = {
                lat: parseFloat(lat),
                lng: parseFloat(lng)
            };

            // Create marker instance.
            var marker = new google.maps.Marker({
                position: latLng,
                map: map
            });

            // Append to reference for later use.
            map.markers.push(marker);

            // If marker contains HTML, add it to an infoWindow.
            if ($marker.html()) {

                // Create info window.
                var infowindow = new google.maps.InfoWindow({
                    content: $marker.html()
                });

                // Show info window when marker is clicked.
                google.maps.event.addListener(marker, 'click', function() {
                    infowindow.open(map, marker);
                });
            }
        }

        /**
         * centerMap
         *
         * Centers the map showing all markers in view.
         *
         * @date    22/10/19
         * @since   5.8.6
         *
         * @param   object The map instance.
         * @return  void
         */
        function centerMap(map) {

            // Create map boundaries from all map markers.
            var bounds = new google.maps.LatLngBounds();
            map.markers.forEach(function(marker) {
                bounds.extend({
                    lat: marker.position.lat(),
                    lng: marker.position.lng()
                });
            });

            // Case: Single marker.
            if (map.markers.length == 1) {
                map.setCenter(bounds.getCenter());

                // Case: Multiple markers.
            } else {
                map.fitBounds(bounds);
            }
        }

        // Render maps on page load.
        $(document).ready(function() {
            $('.acf-map').each(function() {
                var map = initMap($(this));
            });
        });

    })(jQuery);
</script>