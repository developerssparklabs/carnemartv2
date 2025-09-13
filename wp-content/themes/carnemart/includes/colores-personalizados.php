<?php
function agregar_paleta_colores_personalizada()
{
    add_theme_support('editor-color-palette', [
        [
            'name'  => __('Principal', 'text-domain'),
            'slug'  => 'principal',
            'color' => '#021B6D',
        ],
        [
            'name'  => __('Verde', 'text-domain'),
            'slug'  => 'verde',
            'color' => '#009A38',
        ],
        [
            'name'  => __('Rojo', 'text-domain'),
            'slug'  => 'rojo',
            'color' => '#ED2047',
        ],
        [
            'name'  => __('Azul Claro', 'text-domain'),
            'slug'  => 'azul-claro',
            'color' => '#0866FD',
        ],
        [
            'name'  => __('Negro', 'text-domain'),
            'slug'  => 'negro',
            'color' => '#343434',
        ],
        [
            'name'  => __('Gris', 'text-domain'),
            'slug'  => 'gris',
            'color' => '#F2F2F2',
        ],
        [
            'name'  => __('Extra', 'text-domain'),
            'slug'  => 'azulado',
            'color' => '#c8dbf8',
        ],
    ]);
}
add_action('after_setup_theme', 'agregar_paleta_colores_personalizada');


function agregar_paleta_colores_tinymce($init)
{
    $custom_colors = '
        "021B6D", "Principal",
        "009A38", "Verde",
        "ED2047", "Rojo",
        "0866FD", "Azul claro",
        "343434", "Negro",  
        "F2F2F2", "Gris",
        "c8dbf8", "Extra"

    ';
    // Si hay colores personalizados definidos
    $init['textcolor_map'] = '[' . $custom_colors . ']';

    // Habilitar más colores
    $init['textcolor_rows'] = 1; // Cantidad de filas en la paleta de colores
    return $init;
}
add_filter('tiny_mce_before_init', 'agregar_paleta_colores_tinymce');



function custom_acf_color_palette()
{
    wp_enqueue_script('acf-color-palette', get_template_directory_uri() . '/js/acf-color-palette.js', ['wp-color-picker'], null, true);
}
add_action('acf/input/admin_enqueue_scripts', 'custom_acf_color_palette');




function my_acf_collor_pallete_script()
{
?>
    <script type="text/javascript">
        (function($) {

            acf.add_filter('color_picker_args', function(args, $field) {

                // do something to args
                args.palettes = [
                    '#021B6D',
                    '#009A38',
                    '#ED2047',
                    '#0866FD',
                    '#343434',
                    '#F2F2F2',
                    '#c8dbf8'
                ]

                console.log(args);
                // return
                return args;
            });

        })(jQuery);
    </script>
<?php
}

add_action('acf/input/admin_footer', 'my_acf_collor_pallete_script');
