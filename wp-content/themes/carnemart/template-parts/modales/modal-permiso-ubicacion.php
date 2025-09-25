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
<div class="modal fade" id="modalPermisos" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
    aria-labelledby="modalPermisoLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header header-icono">
                <p class="modal-title fs-4 has-principal-color" id="modalPermisoLabel"><i
                        class="bi bi-geo-alt-fill has-verde-color"></i><b>¿Nos ayudas un momento?</b></p>
            </div>
            <div class="modal-body fs-7">
                <p>Para ofrecerte una mejor experiencia y mostrarte productos disponibles cerca de ti, <b
                        class="has-verde-color">necesitamos saber tu ubicación.</b></p>
                <p class="pb-0 mb-0"><b class="has-principal-color">¿Te gustaría activarla ahora?</b></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary fs-7" data-bs-dismiss="modal">Si, compartir
                    ubicación</button>
                <button type="button" class="btn btn-danger fs-7">No, prefiero no hacerlo.</button>
            </div>
        </div>
    </div>
</div>



<!-- Modal selección de tienda -->
<div class="modal fade" id="modalSeleccionTienda" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
    aria-labelledby="modalSeleccionTiendaLabel" aria-hidden="true">
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
                    <b class="has-verde-color">Estimado cliente,</b> por favor seleccione la tienda de su interés o
                    proporcione su dirección para localizar la más cercana.
                </p>
                <p class="has-principal-color mb-0 pb-0">
                    <a href="#" class="link-inline" id="btnLocaliza"><i class="bi bi-geo-alt"></i> Compartir mi
                        ubicación</a>

                    <span id="togglePostcode" role="button" tabindex="0" aria-controls="contentPostalCode"
                        aria-expanded="false" style="cursor:pointer"
                        onclick="(el=>{const box=document.getElementById('contentPostalCode');const open=box.classList.toggle('is-open');el.setAttribute('aria-expanded',open)})(this)"
                        onkeydown="if(event.key==='Enter'||event.key===' '){this.click(); event.preventDefault();}">
                        o agregar mi código postal
                    </span>
                </p>
                <?php echo do_shortcode('[wcmlim_locations_switch]'); ?>
                <!-- <form class="row no-gutters form-seleccion-sucursal custom-form" action="">
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
                </form> -->
            </div>
            <div class="modal-footer">
            </div>
        </div>
    </div>
</div>

<style>
    #contentPostalCode {
        display: none;
    }

    #contentPostalCode.is-open {
        display: flex !important;
    }
</style>



<!-- Modal Tiendas Cercanas -->
<div class="modal fade" id="modalTiendasCercanas" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
    aria-labelledby="modalTiendasCercanasLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header header-icono">
                <p class="modal-title fs-4 has-principal-color d-flex" id="modalTiendasCercanasLabel"><i
                        class="bi bi-check-circle big-icon has-verde-color"></i><b>¡Encontramos una tienda<br> cerca de
                        ti!</b></p>
                <!-- <button type="button" class="modal-cierre" data-bs-dismiss="modal" aria-label="Close"><i
                        class="bi bi-x-circle-fill"></i></button> -->
            </div>
            <div class="modal-body fs-7">

                <ul class="has-principal-color listado-tienda-encontrada">
                    <li class="elemento-destacado"><b><i class="bi bi-shop-window bi-listado"></i> Tienda:</b> <span
                            id="modalTiendaNombre"></span> </li>
                    <li><b><i class="bi bi-geo-alt bi-listado"></i> Dirección:</b> <span
                            id="modalTiendaDireccion"></span> </li>
                    <li><b><i class="bi bi-pin-map bi-listado"></i> Coordenadas:</b> <span
                            id="modalTiendaCoordenadas"></span> </li>
                    <li><b><i class="bi bi-arrows bi-listado"></i> Distancia:</b> <span
                            id="modalTiendaDistancia"></span></li>
                    <li><b><i class="bi bi-clock-history bi-listado"></i> Tiempo estimado:</b> <span
                            id="modalTiendaTiempoEstimado"></span> </li>
                    <li><b><i class="bi bi-info-circle bi-listado"></i> Servicio:</b> <span
                            id="modalTiendaServicio"></span></li>
                </ul>
            </div>
            <div class="modal-footer d-flex flex-column p-relative p-0 m-0 overflow-hidden">
                <small class="text-muted">
                    Estamos redirigiéndote a tu tienda en … <span id="countdown">4</span> segundos…
                </small>

                <div class="progress w-100 custom-progress" style="height: 15px;">
                    <div id="autocloseBar" class="progress-bar has-principal-background-color autoclose-bar"
                        role="progressbar"></div>
                </div>
            </div>
        </div>
    </div>
</div>



<!-- Modal NO permiso -->
<div class="modal fade" id="modalNoPermiso" data-bs-keyboard="false" tabindex="-1" aria-labelledby="modalNoPermisoLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header header-icono">
                <p class="modal-title fs-4 has-principal-color d-flex" id="modalNoPermisoLabel"><i
                        class="bi bi-x-circle big-icon has-rojo-color"></i><b>¡Entendemos<br> tu decisión.</b></p>
            </div>
            <div class="modal-body fs-7">

                <p class="has-principal-color">
                    No activaremos la ubicación, pero si cambias de opinión podrás hacerlo más adelante.
                </p>
            </div>
            <div class="modal-footer d-flex justify-content-end">
                <button type="button" class="btn btn-secondary fs-7" data-bs-dismiss="modal">Cerrar ventana y
                    continuar</button>
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
    jQuery(document).ready(function ($) {


        const isSafari = (() => {
            const ua = navigator.userAgent;
            const vendor = navigator.vendor || '';
            const isChromium =
                'userAgentData' in navigator || /Chrome|Chromium|CriOS|Edg|OPR/i.test(ua);
            const isSafariToken = /Safari/i.test(ua) && /Version\/\d+/i.test(ua);
            const isApple = /Apple/i.test(vendor);
            return isApple && isSafariToken && !isChromium;
        })();
        console.log("Safari:: ", isSafari);
        const isSafariTest = (() => {
            const ua = navigator.userAgent;
            const isSafariUA = /^((?!chrome|chromium|android).)*safari/i.test(ua);
            const isAppleVendor = navigator.vendor && navigator.vendor.includes('Apple');
            return isSafariUA && isAppleVendor;
        })();
        console.log("Safari2:: ", isSafari);

        // //Modal de permisos de ubicación(Modal Permisos)



        // Modal de tienda cercana con auto cierre 
        var modalSeleccionTienda = new bootstrap.Modal(document.getElementById('modalSeleccionTienda'));
        // Abrir modal al dar click en el botón
        $('#btnAbrirTienda').on('click', function () {
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

        $modal.on('shown.bs.modal.autoclose', function () {
            // Reset visual
            $bar.css('width', '100%');
            var segundos = Math.ceil(duracionMs / 1000);
            $count.text(segundos);

            // Forzar reflow para que la transición 100% -> 0% sí ocurra
            void $bar[0].offsetWidth;

            // Dispara la animación
            $bar.css('width', '0%');

            // Cuenta regresiva cada 1s
            tickId = setInterval(function () {
                segundos--;
                $count.text(Math.max(segundos, 0));
                if (segundos <= 0) {
                    clearInterval(tickId);
                    tickId = null;
                }
            }, 1000);

            // Cierra exactamente cuando termine la transición de la barra
            $bar.one('transitionend', function () {
                var inst = bootstrap.Modal.getOrCreateInstance($modal[0]);
                inst.hide();
            });
        });

        $modal.on('hide.bs.modal.autoclose', function () {
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

<!-- Modal cuando hay productos en el carrito -->

<!-- Modal productos en carrito -->
<div class="modal fade" id="modalCarrito" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
    aria-labelledby="modalCarritoLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header header-icono">
                <p class="modal-title fs-4 has-principal-color d-flex" id="modalCarritoLabel">
                    <i class="bi bi-exclamation-triangle-fill big-icon has-amarillo-color"></i>
                    <b>¡Atención!<br> Tienes productos en tu carrito</b>
                </p>
            </div>
            <div class="modal-body fs-7">
                <p class="has-principal-color">
                    <b class="has-rojo-color">Si cambias de tienda ahora, perderás todos los productos que tienes en tu
                        carrito de compras.</b>
                </p>
                <p class="has-principal-color mb-0">
                    ¿Qué prefieres hacer?
                </p>
            </div>
            <div class="modal-footer d-flex flex-column gap-2">
                <div class="d-flex gap-2 w-100">
                    <button type="button" class="btn btn-danger fs-7 flex-fill" data-bs-dismiss="modal"
                        id="btnCambiarTienda">
                        Cambiar tienda (perderé mis productos)
                    </button>
                    <button type="button" class="btn btn-primary fs-7 flex-fill"
                        onclick="window.location.href='/checkout'">
                        Ir a pagar mis productos
                    </button>
                </div>
                <button type="button" class="btn btn-secondary fs-7 w-100" data-bs-dismiss="modal"
                    id="btnSeguirComprando">
                    <i class="bi bi-cart-plus"></i> Continuar comprando en esta tienda
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal no se encontraron tiendas cercanas -->
<div class="modal fade" id="modalSinTiendas" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
    aria-labelledby="modalSinTiendasLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header header-icono">
                <p class="modal-title fs-4 has-principal-color d-flex" id="modalSinTiendasLabel">
                    <i class="bi bi-exclamation-circle big-icon has-amarillo-color"></i>
                    <b>¡Lo sentimos!<br> No encontramos una tienda cerca</b>
                </p>
            </div>
            <div class="modal-body fs-7">
                <p class="has-principal-color">
                    <b class="has-rojo-color">No hemos encontrado ninguna tienda disponible cerca de tu ubicación
                        actual.</b>
                </p>
                <p class="has-principal-color">
                    Te sugerimos las siguientes opciones:
                </p>
                <ul class="has-principal-color">
                    <li>Selecciona manualmente una tienda de nuestra lista</li>
                    <li>Verifica que tu ubicación sea correcta</li>
                </ul>
            </div>
            <div class="modal-footer d-flex flex-column gap-2">
                <div class="d-flex flex-column gap-2 w-100"></div>
                <button type="button" class="btn btn-primary fs-7 w-100" data-bs-dismiss="modal" data-bs-toggle="modal"
                    data-bs-target="#modalSeleccionTienda">
                    <i class="bi bi-shop"></i> Seleccionar tienda manualmente
                </button>
            </div>
        </div>
    </div>
</div>