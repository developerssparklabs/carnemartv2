<?php
function slider_entradas_blog_shortcode($atts)
{
    // Atributos del shortcode con valores por defecto
    $atts = shortcode_atts(
        array(
            'button_link' => '#', // Enlace del botón
            'button_title' => 'Ver más entradas', // Título del botón
        ),
        $atts,
        'slider_entradas_blog'
    );

    // Inicio del buffer para capturar la salida
    ob_start();

?>
    <div class="slider-entradas-blog">
        <?php
        // Query personalizado para obtener las entradas del CPT 'post'
        $args = array(
            'post_type' => 'post', // Tipo de post
            'posts_per_page' => 8, // Número de entradas a mostrar
            'orderby' => 'date', // Ordenar por fecha
            'order' => 'DESC', // Orden descendente
        );

        $query = new WP_Query($args);

        if ($query->have_posts()) :
            while ($query->have_posts()) : $query->the_post();
        ?>
                <div class="slide-item">
                    <div class="entradas-blog__card">
                        <a href="<?php the_permalink(); ?>" class="entradas-blog__card-header" style="background-image: url('<?php echo get_the_post_thumbnail_url(get_the_ID(), 'full'); ?>');">
                            <span class="tipo-categoria">
                                <?php
                                // Mostrar la primera categoría asignada al post
                                $categories = get_the_category();
                                if (!empty($categories)) {
                                    echo esc_html($categories[0]->name);
                                }
                                ?>
                            </span>
                        </a>
                        <div class="entradas-blog__card-body">
                            <h2><?php the_title(); ?></h2>
                            <span class="extracto"><?php the_excerpt(); ?></span>
                        </div>
                        <div class="entradas-blog__card-footer">
                            <a href="<?php the_permalink(); ?>" class="btn btn-more"><i class="bi bi-plus-circle-fill"></i></a>
                        </div>
                    </div>
                </div>
        <?php
            endwhile;
            wp_reset_postdata();
        else :
            echo '<p>No se encontraron entradas.</p>';
        endif;
        ?>
    </div>
    <div class="espacio"></div>

    <!-- Botón personalizado con los atributos -->
    <a href="<?php echo esc_url($atts['button_link']); ?>" class="btn btn-big cta-dark">
        <?php echo esc_html($atts['button_title']); ?>
    </a>

    <div class="espacio-1"></div>
<?php

    // Final del buffer y devolver el contenido generado
    return ob_get_clean();
}

// Registrar el shortcode
add_shortcode('slider_entradas_blog', 'slider_entradas_blog_shortcode');










function grid_entradas_blog_shortcode($atts)
{
    // Atributos del shortcode con valores por defecto
    $atts = shortcode_atts(
        array(
            'button_link' => '#', // Enlace del botón
            'button_title' => 'Ver más entradas', // Título del botón
        ),
        $atts,
        'slider_entradas_blog'
    );

    // Inicio del buffer para capturar la salida
    ob_start();

?>
    <div class="grid-entradas-blog">
        <?php
        // Query personalizado para obtener las entradas del CPT 'post'
        $args = array(
            'post_type' => 'post', // Tipo de post
            'posts_per_page' => -1, // Número de entradas a mostrar
            'orderby' => 'date', // Ordenar por fecha
            'order' => 'DESC', // Orden descendente
        );

        $query = new WP_Query($args);

        if ($query->have_posts()) :
            while ($query->have_posts()) : $query->the_post();
        ?>
                <div class="grid-item">
                    <div class="entradas-blog__card">
                        <a href="<?php the_permalink(); ?>" class="entradas-blog__card-header" style="background-image: url('<?php echo get_the_post_thumbnail_url(get_the_ID(), 'full'); ?>');">
                            <span class="tipo-categoria">
                                <?php
                                // Mostrar la primera categoría asignada al post
                                $categories = get_the_category();
                                if (!empty($categories)) {
                                    echo esc_html($categories[0]->name);
                                }
                                ?>
                            </span>
                        </a>
                        <div class="entradas-blog__card-body">
                            <h2><?php the_title(); ?></h2>
                            <span class="extracto"><?php the_excerpt(); ?></span>
                        </div>
                        <div class="entradas-blog__card-footer">
                            <a href="<?php the_permalink(); ?>" class="btn btn-more"><i class="bi bi-plus-circle-fill"></i></a>
                        </div>
                    </div>
                </div>
        <?php
            endwhile;
            wp_reset_postdata();
        else :
            echo '<p>No se encontraron entradas.</p>';
        endif;
        ?>
    </div>
    <div class="espacio"></div>

<?php

    // Final del buffer y devolver el contenido generado
    return ob_get_clean();
}

// Registrar el shortcode
add_shortcode('grid_entradas_blog', 'grid_entradas_blog_shortcode');
