<?php
function register_acf_block_types()
{



   acf_register_block_type(array(
      'name'              => 'slider-ancho-completo',
      'title'             => __('Slider destacado'),
      'description'       => __('Slider de banners para desktop y mobile'),
      'render_template'   => 'template-parts/blocks/contenido/bloque-slider-ancho-completo.php',
      'category' => 'Bloques de sitio',
      'mode' => 'edit',
      'icon' => array(
         'background' => '#fff',
         'foreground' => '#2881d7',
         'src' => 'block-default',
      ),
      'keywords'          => array('Bloques', 'quote'),
   ));


   acf_register_block_type(array(
      'name'              => 'section-call-to-action-slider-cards',
      'title'             => __('Call to Action y slider de cards'),
      'description'       => __('Muestra una sección con call to action y slider de contenido'),
      'render_template'   => 'template-parts/blocks/contenido/bloque-call-to-action-slider-cards.php',
      'category' => 'Bloques de sitio',
      'mode' => 'edit',
      'icon' => array(
         'background' => '#fff',
         'foreground' => '#2881d7',
         'src' => 'block-default',
      ),
      'keywords'          => array('Bloques', 'quote'),
   ));


   acf_register_block_type(array(
      'name'              => 'section-listado-categorias-principales',
      'title'             => __('Listado de categorias principales'),
      'description'       => __('Muestra una sección con listado de categorias'),
      'render_template'   => 'template-parts/blocks/contenido/bloque-listado-categorias-principales.php',
      'category' => 'Bloques de sitio',
      'mode' => 'edit',
      'icon' => array(
         'background' => '#fff',
         'foreground' => '#2881d7',
         'src' => 'block-default',
      ),
      'keywords'          => array('Bloques', 'quote'),
   ));

   acf_register_block_type(array(
      'name'              => 'section-botonera-etiquetas',
      'title'             => __('Bloque de botonera destacada para etiquetas'),
      'description'       => __('Muestra un bloque con una botonera para etiquetas'),
      'render_template'   => 'template-parts/blocks/contenido/bloque-botonera-etiquetas.php',
      'category' => 'Bloques de sitio',
      'mode' => 'edit',
      'icon' => array(
         'background' => '#fff',
         'foreground' => '#2881d7',
         'src' => 'block-default',
      ),
      'keywords'          => array('Bloques', 'quote'),
   ));


   acf_register_block_type(array(
      'name'              => 'section-titulo-descripcion',
      'title'             => __('Sección para título y descripción'),
      'description'       => __('Muestra una sección con un título simple y descripción'),
      'render_template'   => 'template-parts/blocks/contenido/bloque-titulo-descripcion.php',
      'category' => 'Bloques de sitio',
      'mode' => 'edit',
      'icon' => array(
         'background' => '#fff',
         'foreground' => '#2881d7',
         'src' => 'block-default',
      ),
      'keywords'          => array('Bloques', 'quote'),
   ));

   acf_register_block_type(array(
      'name'              => 'section-banner-cinturon',
      'title'             => __('Bloque de banner de ancho completo'),
      'description'       => __('Muestra un bloque con banner de ancho completo con enlace'),
      'render_template'   => 'template-parts/blocks/contenido/bloque-banner-cinturon.php',
      'category' => 'Bloques de sitio',
      'mode' => 'edit',
      'icon' => array(
         'background' => '#fff',
         'foreground' => '#2881d7',
         'src' => 'block-default',
      ),
      'keywords'          => array('Bloques', 'quote'),
   ));

   acf_register_block_type(array(
      'name'              => 'section-botonera-destacada',
      'title'             => __('Bloque de botonera destacada'),
      'description'       => __('Muestra un bloque con una botonera en forma de grid'),
      'render_template'   => 'template-parts/blocks/contenido/bloque-botonera-destacada.php',
      'category' => 'Bloques de sitio',
      'mode' => 'edit',
      'icon' => array(
         'background' => '#fff',
         'foreground' => '#2881d7',
         'src' => 'block-default',
      ),
      'keywords'          => array('Bloques', 'quote'),
   ));


   acf_register_block_type(array(
      'name'              => 'section-bloque-columnas',
      'title'             => __('Bloque de columnas de contenido'),
      'description'       => __('Muestra un bloque para mostrar columnas de contenido'),
      'render_template'   => 'template-parts/blocks/contenido/bloque-columnas.php',
      'category' => 'Bloques de sitio',
      'mode' => 'edit',
      'icon' => array(
         'background' => '#fff',
         'foreground' => '#2881d7',
         'src' => 'block-default',
      ),
      'keywords'          => array('Bloques', 'quote'),
   ));

   acf_register_block_type(array(
      'name'              => 'section-bloque-bullets',
      'title'             => __('Bloque de bullets'),
      'description'       => __('Muestra un bloque con bullets de imagen y texto'),
      'render_template'   => 'template-parts/blocks/contenido/bloque-bullets.php',
      'category' => 'Bloques de sitio',
      'mode' => 'edit',
      'icon' => array(
         'background' => '#fff',
         'foreground' => '#2881d7',
         'src' => 'block-default',
      ),
      'keywords'          => array('Bloques', 'quote'),
   ));


   acf_register_block_type(array(
      'name'              => 'section-bloque-contenido-lateral',
      'title'             => __('Bloque de contenido lateral'),
      'description'       => __('Muestra un bloque con contenido, imagen y o video'),
      'render_template'   => 'template-parts/blocks/contenido/bloque-seccion-contenido-lateral.php',
      'category' => 'Bloques de sitio',
      'mode' => 'edit',
      'icon' => array(
         'background' => '#fff',
         'foreground' => '#2881d7',
         'src' => 'block-default',
      ),
      'keywords'          => array('Bloques', 'quote'),
   ));


   acf_register_block_type(array(
      'name'              => 'section-bloque-sucursales',
      'title'             => __('Bloque de sucursales'),
      'description'       => __('Muestra un bloque con las sucursales'),
      'render_template'   => 'template-parts/blocks/contenido/bloque-sucursales.php',
      'category' => 'Bloques de sitio',
      'mode' => 'edit',
      'icon' => array(
         'background' => '#fff',
         'foreground' => '#2881d7',
         'src' => 'block-default',
      ),
      'keywords'          => array('Bloques', 'quote'),
   ));

   acf_register_block_type(array(
      'name'              => 'bloque-codigo',
      'title'             => __('Bloque contenido y shortcode'),
      'description'       => __('Bloque para mostrar contenido y shortcode'),
      'render_template'   => 'template-parts/blocks/contenido/bloque-seccion-shortcode.php',
      'category' => 'Bloques de sitio',
      'mode' => 'edit',
      'icon' => array(
         'background' => '#fff',
         'foreground' => '#2881d7',
         'src' => 'block-default',
      ),
      'keywords'          => array('Bloques', 'quote'),
   ));


   acf_register_block_type(array(
      'name'              => 'bloque-tabs',
      'title'             => __('Bloque de tabs con shortcode o contenido'),
      'description'       => __('Bloque para mostrar tabs con contenido y o shortcode'),
      'render_template'   => 'template-parts/blocks/contenido/bloque-tabs-shortcode.php',
      'category' => 'Bloques de sitio',
      'mode' => 'edit',
      'icon' => array(
         'background' => '#fff',
         'foreground' => '#2881d7',
         'src' => 'block-default',
      ),
      'keywords'          => array('Bloques', 'quote'),
   ));

   acf_register_block_type(array(
      'name'              => 'section-bloque-galeria',
      'title'             => __('Bloque de galería'),
      'description'       => __('Muestra un bloque de galería'),
      'render_template'   => 'template-parts/blocks/contenido/bloque-galeria.php',
      'category' => 'Bloques de sitio',
      'mode' => 'edit',
      'icon' => array(
         'background' => '#fff',
         'foreground' => '#2881d7',
         'src' => 'block-default',
      ),
      'keywords'          => array('Bloques', 'quote'),
   ));
}

// Check if function exists and hook into setup.
if (function_exists('acf_register_block_type')) {
   add_action('acf/init', 'register_acf_block_types');
}
