<!-- Button trigger modal -->
<button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#modalPermisos">
    Inicio
</button>

<button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#modalSeleccionTienda">
    Seleccion
</button>

<button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#modalTiendasCercanas">
    Tiendas
</button>

<button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#modalNoPermiso">
    No permiso
</button>



<!-- Modal permisos de ubicación -->
<div class="modal fade" id="modalPermisos" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="modalPermisoLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header header-icono">
                <p class="modal-title fs-4 has-principal-color" id="modalPermisoLabel"><i class="bi bi-geo-alt-fill has-verde-color"></i><b>¿Nos ayudas un momento?</b></p>
            </div>
            <div class="modal-body fs-7">
                <p>Para ofrecerte una mejor experiencia y mostrarte productos disponibles cerca de ti, <b class="has-verde-color">necesitamos saber tu ubicación.</b></p>
                <p class="pb-0 mb-0"><b class="has-principal-color">¿Te gustaría activarla ahora?</b></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary fs-7" data-bs-dismiss="modal">Si, compartir ubicación</button>
                <button type="button" class="btn btn-danger fs-7">No, prefiero no hacerlo.</button>
            </div>
        </div>
    </div>
</div>



<!-- Modal selección de tienda -->
<div class="modal fade" id="modalSeleccionTienda" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="modalSeleccionTiendaLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header header-icono">
                <p class="modal-title fs-4 has-verde-color" id="modalSeleccionTiendaLabel">
                    <i class="bi bi-geo-alt-fill has-verde-color"></i>
                    <b>Ubica tu tienda más cercana</b>
                </p>
                <button type="button" class="modal-cierre" data-bs-dismiss="modal" aria-label="Close">
                    <i class="bi bi-x-circle-fill"></i>
                </button>
            </div>
            <div class="modal-body fs-7">
                <p class="has-principal-color">
                    <b class="has-verde-color">Estimado cliente,</b> por favor seleccione la tienda de su interés o proporcione su dirección para localizar la más cercana.
                </p>
                <p class="has-principal-color mb-0 pb-0">
                    <a href="#" class="link-inline"><i class="bi bi-geo-alt"></i> Compartir mi ubicación</a> o agregar mi código postal
                </p>

                <form class="row no-gutters form-seleccion-sucursal custom-form" action="">
                    <div class="col-md-12">
                        <label for="seleccionEstado" class="form-label">Estado</label>
                        <select class="form-select form-select-sm" aria-label="Seleccion de estado">
                            <option selected>Selecciona</option>
                            <option value="1">One</option>
                            <option value="2">Two</option>
                            <option value="3">Three</option>
                        </select>
                    </div>

                    <div class="col-md-12">
                        <label for="seleccionSucursal" class="form-label">Sucursal</label>
                        <select class="form-select form-select-sm" aria-label="Seleccion de estado">
                            <option selected>Selecciona</option>
                            <option value="1">One</option>
                            <option value="2">Two</option>
                            <option value="3">Three</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
            </div>
        </div>
    </div>
</div>


<!-- Modal Tiendas Cercanas -->
<div class="modal fade" id="modalTiendasCercanas" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="modalTiendasCercanasLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header header-icono">
                <p class="modal-title fs-4 has-principal-color d-flex" id="modalTiendasCercanasLabel"><i class="bi bi-check-circle big-icon has-verde-color"></i><b>¡Encontramos una tienda<br> cerca de ti!</b></p>
                <button type="button" class="modal-cierre" data-bs-dismiss="modal" aria-label="Close"><i class="bi bi-x-circle-fill"></i></button>
            </div>
            <div class="modal-body fs-7">

                <ul class="has-principal-color listado-tienda-encontrada">
                    <li class="elemento-destacado"><b><i class="bi bi-shop-window bi-listado"></i> Tienda:</b> CMT Eje 3 - M310</li>
                    <li><b><i class="bi bi-geo-alt bi-listado"></i> Dirección:</b> 02810 Manuel Rivera Anaya CROC 1 473 Azcapotzalco Ciudad de México Ciudad de México 02510 México 19.487 -99.1859</li>
                    <li><b><i class="bi bi-pin-map bi-listado"></i> Coordenadas:</b> 19.487, -99.1859</li>
                    <li><b><i class="bi bi-arrows bi-listado"></i> Distancia:</b> 6.21 km</li>
                    <li><b><i class="bi bi-clock-history bi-listado"></i> Tiempo estimado:</b> 12.42 minutos</li>
                    <li><b><i class="bi bi-info-circle bi-listado"></i> Servicio:</b> Servicio a domicilio y recoger en tienda</li>
                </ul>
            </div>
            <div class="modal-footer d-flex flex-column p-relative p-0 m-0 overflow-hidden">
                <small class="text-muted">
                    Estamos redirigiéndote a tu tienda en … <span id="countdown">4</span> segundos…
                </small>

                <div class="progress w-100 custom-progress" style="height: 15px;">
                    <div id="autocloseBar" class="progress-bar has-principal-background-color autoclose-bar" role="progressbar"></div>
                </div>
            </div>
        </div>
    </div>
</div>



<!-- Modal NO permiso -->
<div class="modal fade" id="modalNoPermiso" data-bs-keyboard="false" tabindex="-1" aria-labelledby="modalNoPermisoLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header header-icono">
                <p class="modal-title fs-4 has-principal-color d-flex" id="modalNoPermisoLabel"><i class="bi bi-x-circle big-icon has-rojo-color"></i><b>¡Entendemos<br> tu decisión.</b></p>
                <button type="button" class="modal-cierre" data-bs-dismiss="modal" aria-label="Close"><i class="bi bi-x-circle-fill"></i></button>
            </div>
            <div class="modal-body fs-7">

                <p class="has-principal-color">
                    No activaremos la ubicación, pero si cambias de opinión podrás hacerlo más adelante.
                </p>
            </div>
            <div class="modal-footer d-flex justify-content-end">
                <button type="button" class="btn btn-secondary fs-7" data-bs-dismiss="modal">Cerrar ventana y continuar</button>
            </div>
        </div>
    </div>
</div>



<!-- Precarga de la primera modal -->

<style>
    .progress .autoclose-bar {
        width: 100%;
        transition: width var(--autoCloseMs, 4000ms) linear !important;
    }
</style>
<script>
    jQuery(document).ready(function($) {

        Modal de permisos de ubicación(Modal Permisos)
        setTimeout(function() {
            var modalPermisos = new bootstrap.Modal(document.getElementById('modalPermisos'));
            modalPermisos.show();
        }, 3000);


        // Modal de tienda cercana con auto cierre 
        var modalSeleccionTienda = new bootstrap.Modal(document.getElementById('modalSeleccionTienda'));
        // Abrir modal al dar click en el botón
        $('#btnAbrirTienda').on('click', function() {
            modalSeleccionTienda.show();
            // Este cierra la banda azul de buscar tienda
            $('.buscador-contenido').slideToggle(300);
        });


        // Animacion de autocierre TIENDA CERCANA ------------------------------------------------
        var duracionMs = 8000; // 

        var $modal = $('#modalTiendasCercanas');
        var $bar = $('#autocloseBar');
        var $count = $('#countdown');
        var tickId = null;


        if ($bar[0]) $bar[0].style.setProperty('--autoCloseMs', duracionMs + 'ms');

        // Evita duplicar handlers si pegas este bloque más de una vez
        $modal.off('.autoclose');

        $modal.on('shown.bs.modal.autoclose', function() {
            // Reset visual
            $bar.css('width', '100%');
            var segundos = Math.ceil(duracionMs / 1000);
            $count.text(segundos);

            // Forzar reflow para que la transición 100% -> 0% sí ocurra
            void $bar[0].offsetWidth;

            // Dispara la animación
            $bar.css('width', '0%');

            // Cuenta regresiva cada 1s
            tickId = setInterval(function() {
                segundos--;
                $count.text(Math.max(segundos, 0));
                if (segundos <= 0) {
                    clearInterval(tickId);
                    tickId = null;
                }
            }, 1000);

            // Cierra exactamente cuando termine la transición de la barra
            $bar.one('transitionend', function() {
                var inst = bootstrap.Modal.getOrCreateInstance($modal[0]);
                inst.hide();
            });
        });

        $modal.on('hide.bs.modal.autoclose', function() {
            if (tickId) {
                clearInterval(tickId);
                tickId = null;
            }
            // Deja listo por si se vuelve a abrir
            $bar.css('width', '100%');
            $count.text(Math.ceil(duracionMs / 1000));
        });




    });
</script>