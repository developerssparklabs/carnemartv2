<!-- Button trigger modal -->
<!-- <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#modalPermisos">
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
</button> -->


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
                    <a href="#" class="link-inline"><i class="bi bi-geo-alt"></i> <span id="shareGeoModal"
                            class="eti-pointer">Compartir mi ubicación</span></a> <span id="showPostalCode"
                        class="eti-pointer">o agregar mi código
                        postal</span>
                </p>
                <div class="field cold-md-12 custom-form">
                    <input id="inputCodePostal" class="input" type="text" inputmode="numeric" autocomplete="postal-code"
                        maxlength="5" pattern="\d{5}" required placeholder="Ingresa tu código postal" />
                    <small class="error-msg">Por favor ingresa un código postal válido</small>
                </div>
                <form class="row no-gutters form-seleccion-sucursal custom-form" action="">
                    <div class="col-md-12">
                        <label for="seleccionEstado" class="form-label">Estado</label>
                        <select id="selectState" class="form-select form-select-sm" aria-label="Seleccion de estado">
                        </select>
                    </div>
                    <div class="col-md-12">
                        <label for="seleccionSucursal" class="form-label">Sucursal</label>
                        <select id="selectStore" class="form-select form-select-sm" aria-label="Seleccion de sucursal">
                            <option selected>Selecciona sucursal</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <div id="content-search-stores">
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Tiendas Cercanas -->
<div class="modal fade" id="modalTiendasCercanas" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
    aria-labelledby="modalTiendasCercanasLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header header-icono">
                <p class="modal-title fs-4 has-principal-color d-flex" id="modalTiendasCercanasLabel"><i
                        class="bi bi-check-circle big-icon has-verde-color"></i><b>¡Encontramos una tienda<br> cerca de
                        ti!</b></p>
                <button type="button" class="modal-cierre" data-bs-dismiss="modal" aria-label="Close"><i
                        class="bi bi-x-circle-fill"></i></button>
            </div>
            <div class="modal-body fs-7">
                <ul class="has-principal-color listado-tienda-encontrada">
                    <li class="elemento-destacado"><b><i class="bi bi-shop-window bi-listado"></i> Tienda:</b> CMT Eje 3
                        - M310</li>
                    <li><b><i class="bi bi-geo-alt bi-listado"></i> Dirección:</b> 02810 Manuel Rivera Anaya CROC 1 473
                        Azcapotzalco Ciudad de México Ciudad de México 02510 México 19.487 -99.1859</li>
                    <li><b><i class="bi bi-pin-map bi-listado"></i> Coordenadas:</b> 19.487, -99.1859</li>
                    <li><b><i class="bi bi-arrows bi-listado"></i> Distancia:</b> 6.21 km</li>
                    <li><b><i class="bi bi-clock-history bi-listado"></i> Tiempo estimado:</b> 12.42 minutos</li>
                    <li><b><i class="bi bi-info-circle bi-listado"></i> Servicio:</b> Servicio a domicilio y recoger en
                        tienda</li>
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
                <button type="button" class="modal-cierre" data-bs-dismiss="modal" aria-label="Close"><i
                        class="bi bi-x-circle-fill"></i></button>
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
<style>
    .progress .autoclose-bar {
        width: 100%;
        transition: width var(--autoCloseMs, 4000ms) linear !important;
    }

    #content-search-stores {
        width: 100%;
    }

    /* Contenedor scrollable dentro del modal */
    #content-search-stores.stores-list {
        margin-top: 10px;
        border-radius: 10px;
        overflow: auto;
    }

    #content-search-stores .stores-status {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        padding: 16px;
        color: #374151;
        font-size: 14px;
        background-color: #021b6d;
        color: #fff;
        border-radius: 12px;
    }

    #content-search-stores .spinner {
        width: 18px;
        height: 18px;
        border-radius: 50%;
        border: 2px solid #e5e7eb;
        border-top-color: #111827;
        animation: spin .6s linear infinite;
    }

    @keyframes spin {
        to {
            transform: rotate(360deg);
        }
    }

    /* Ítem */
    .store-item {
        display: flex;
        align-items: flex-start;
        gap: 10px;
        padding: 10px 12px;
        margin-bottom: 8px;
        border: 1px solid #e5e7eb;
        border-radius: 10px;
        background: #fff;
        box-shadow: 0 1px 1px rgba(16, 24, 40, .04);
        cursor: pointer;
    }

    .store-item:hover {
        box-shadow: 0 2px 8px #021b6d;
        border-color: #d1d5db;
    }

    .store-item__left {
        flex: 1 1 auto;
        min-width: 0;
    }

    .store-item__state {
        font-size: 11px;
        font-weight: 700;
        color: #0ea5e9;
        text-transform: uppercase;
        letter-spacing: .04em;
    }

    .store-item__head {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-top: 2px;
    }

    .store-item__name {
        font-size: 15px;
        font-weight: 700;
        color: #111827;
        line-height: 1.2;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .store-item__dist {
        flex: 0 0 auto;
        padding: 2px 8px;
        border-radius: 9999px;
        background: green;
        color: white;
        font-weight: 600;
        font-size: 11px;
        white-space: nowrap;
    }

    .store-item__meta {
        font-size: 12px;
        color: #4b5563;
        line-height: 1.35;
        margin-top: 4px;
    }

    .store-item__meta-line {
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .store-item__right {
        flex: 0 0 auto;
        display: flex;
        flex-direction: column;
        gap: 6px;
        align-items: flex-end;
    }

    .chip {
        padding: 4px 8px;
        border-radius: 7px;
        background: #ecfdf5;
        color: #047857;
        font-weight: 700;
        font-size: 11px;
        white-space: nowrap;
    }

    /**
    * Error codigo postal
    */
    .error-msg {
        display: none;
        color: #dc2626;
        font-weight: 500;
        font-style: italic;
        font-size: 11px;
    }
</style>