<?php
// Evitar acceso directo
if (!defined('ABSPATH')) {
    exit;
}

// Mostrar campo de imagen al editar un término
function agregar_campo_imagen_a_taxonomias_edit($term)
{
    $imagen_id = get_term_meta($term->term_id, 'imagen_destacada', true); // Obtener ID de la imagen
?>
    <tr class="form-field">
        <th scope="row" valign="top">
            <label for="imagen_destacada"><?php _e('Imagen destacada'); ?></label>
        </th>
        <td>
            <input type="hidden" id="imagen_destacada" name="imagen_destacada" value="<?php echo esc_attr($imagen_id); ?>" />
            <div id="imagen_destacada_preview" style="max-width: 150px; max-height: 150px;">
                <?php if ($imagen_id) : ?>
                    <img src="<?php echo esc_url(wp_get_attachment_url($imagen_id)); ?>" style="width: 100%; height: auto;" />
                <?php endif; ?>
            </div>
            <button type="button" class="button" id="subir_imagen"><?php _e('Seleccionar imagen'); ?></button>
            <button type="button" class="button" id="eliminar_imagen"><?php _e('Eliminar imagen'); ?></button>
        </td>
    </tr>
<?php
    agregar_script_imagen();
}

// Mostrar campo de imagen al crear un término
function agregar_campo_imagen_a_taxonomias_add($taxonomy)
{
?>
    <div class="form-field">
        <label for="imagen_destacada"><?php _e('Imagen destacada'); ?></label>
        <input type="hidden" id="imagen_destacada" name="imagen_destacada" />
        <div id="imagen_destacada_preview" style="max-width: 150px; max-height: 150px;"></div>
        <button type="button" class="button" id="subir_imagen"><?php _e('Seleccionar imagen'); ?></button>
        <button type="button" class="button" id="eliminar_imagen"><?php _e('Eliminar imagen'); ?></button>
    </div>
<?php
    agregar_script_imagen();
}

// Script de subida de imagen
function agregar_script_imagen()
{
?>
    <script>
        jQuery(document).ready(function($) {
            var frame;

            $('#subir_imagen').on('click', function(e) {
                e.preventDefault();
                if (frame) {
                    frame.open();
                    return;
                }
                frame = wp.media({
                    title: '<?php _e("Seleccionar imagen destacada"); ?>',
                    button: {
                        text: '<?php _e("Usar esta imagen"); ?>'
                    },
                    multiple: false
                });
                frame.on('select', function() {
                    var attachment = frame.state().get('selection').first().toJSON();
                    $('#imagen_destacada').val(attachment.id);
                    $('#imagen_destacada_preview').html('<img src="' + attachment.url + '" style="width: 100%; height: auto;" />');
                });
                frame.open();
            });

            $('#eliminar_imagen').on('click', function(e) {
                e.preventDefault();
                $('#imagen_destacada').val('');
                $('#imagen_destacada_preview').html('');
            });
        });
    </script>
<?php
}

// Guardar el valor del campo de imagen al crear o editar un término
function guardar_imagen_para_taxonomia($term_id)
{
    if (isset($_POST['imagen_destacada']) && $_POST['imagen_destacada'] !== '') {
        update_term_meta($term_id, 'imagen_destacada', sanitize_text_field($_POST['imagen_destacada']));
    } else {
        delete_term_meta($term_id, 'imagen_destacada');
    }
}

// Mostrar y guardar el campo de imagen en todas las taxonomías
function aplicar_campo_imagen_a_todas_taxonomias()
{
    $taxonomias = get_taxonomies(['public' => true], 'names');
    foreach ($taxonomias as $taxonomy) {
        // Mostrar el campo en formularios de creación y edición
        add_action("{$taxonomy}_edit_form_fields", 'agregar_campo_imagen_a_taxonomias_edit');
        add_action("{$taxonomy}_add_form_fields", 'agregar_campo_imagen_a_taxonomias_add');

        // Guardar el valor del campo
        add_action("create_{$taxonomy}", 'guardar_imagen_para_taxonomia', 10, 2);
        add_action("edited_{$taxonomy}", 'guardar_imagen_para_taxonomia', 10, 2);
    }
}
add_action('admin_init', 'aplicar_campo_imagen_a_todas_taxonomias');
