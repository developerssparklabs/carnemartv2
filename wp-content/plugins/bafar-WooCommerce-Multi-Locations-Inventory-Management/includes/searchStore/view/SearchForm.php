<?php

// Evita el acceso directo al archivo
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Clase SearchStore
 *
 * Esta clase define un shortcode [tiendas_section] que renderiza
 * un buscador de tiendas con opción para compartir la ubicación del usuario.
 */
class SearchForm
{
    /**
     * Callback del shortcode 'tiendas_section'
     *
     * Renderiza el HTML del buscador de tiendas, incluyendo el input de búsqueda
     * y un botón para compartir la ubicación del usuario.
     *
     * @return string HTML generado.
     */
    public function formulario_search_store()
    {
        ob_start();
        ?>
        <!-- Buscador de tiendas -->
        <section class="buscador-tienda">
            <div class="buscador-tienda__header">
                <div class="buscador-tienda__buscador">
                    <input type="text" id="buscador" class="buscador-tienda__input-buscar"
                        placeholder="Buscar por estado">
                </div>
                <div class="buscador-tienda__cta">
                    <a href="#" class="buscador-tienda__btn">
                        <i class="geo-alt"></i>
                        <span>Compartir mi ubicación</span>
                    </a>
                </div>
            </div>
            <div class="buscador-container mb-4"></div>
        </section>
        <?php
        return ob_get_clean();
    }
}
