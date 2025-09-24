<div class="site__header-box4 barra-seleccion-tienda">
    <div class="buscador-tienda">
        <div class="buscador-tienda__btn">
            <button class="btn__buscador" id="btnBuscadorTienda" aria-label="Buscar tienda"><span>Buscar tienda</span>
                <i class="bi bi-chevron-down icon-down"></i></button>
        </div>
        <?php echo do_shortcode('[wcmlim_locations_popup]'); ?>
        <div class="buscador-contenido">
            <div class="resultados__ubicacion">
                <div class="resultados__ubicacion-caja caja__entrega">
                    <p class="caja__entrega-titulo">
                        Entrega en:
                    </p>
                    <ul class="resultados__ubicacion-lista">
                        <li><span class="resultados__ubicacion-lista-titulo txtentrega"></span></li>
                        <li><span class="resultados__ubicacion-lista-titulo txthorarios"></span></li>
                    </ul>
                </div>

                <div class="resultados__ubicacion-caja caja__recoleccion">
                    <p class="caja__entrega-titulo">
                        Recolección
                    </p>
                    <ul class="resultados__ubicacion-lista">
                        <li><span class="resultados__ubicacion-lista-titulo txtubicacion "></span></li>
                    </ul>
                </div>
                <div class=" resultados__ubicacion-caja caja__botonera">
                    <!-- <a href="#" id="btnAbrirTienda" onclick="" class="resultados__ubicacion-boton"
                        title="Cambiar dirección">Cambiar dirección</a> -->
                    <button class="resultados__ubicacion-boton">
                        <a id="set-def-store-popup-btn" class="set-def-store-popup-btn" href="#set-def-store"
                            title="Cambiar dirección"></a>
                        <p>Cambiar dirección</p>
                    </button>
                    <a href="#" id="ctaOpenBox" class="resultados__ubicacion-inline-link" title=Continua con tus
                        compras">Continua con tus compras</a>
                </div>

            </div>
            <p class="text-center mb-3 mt-2 small">*Los plazos de entrega están sujetos a disponibilidad</p>
        </div>
    </div>
</div><!--site__header-box4-->