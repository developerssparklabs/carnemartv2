<?php if (have_rows('enlaces_sociales', 'option')): ?>
    <ul class="site__redes-listado">
        <?php while (have_rows('enlaces_sociales', 'option')): the_row();
            $icono = get_sub_field('icono_social', 'option');
            $showNombre = get_sub_field('mostrar_nombre', 'option');
            $mostrarBloque = get_sub_field('ubicacion_social', 'option');
        ?>
            <li class="site__redes-elemento <?php if ($mostrarBloque): ?><?php foreach ($mostrarBloque as $color): ?><?php echo $color; ?> <?php endforeach; ?><?php endif; ?>">

                <?php
                $enlaceRed = get_sub_field('enlace_social', 'option');
                if ($enlaceRed) :
                    $link_url = $enlaceRed['url'];
                    $link_title = $enlaceRed['title'];
                    $link_target = $enlaceRed['target'] ? $enlaceRed['target'] : '_self';
                ?>
                    <a class="site__redes-elemento-enlace" href="<?php echo esc_url($link_url); ?>" target="<?php echo esc_attr($link_target); ?>" aria-label="<?php echo esc_html($link_title); ?>">

                        <span class=" site__redes-elemento-texto <?php if ($showNombre): ?><?php foreach ($showNombre as $color): ?><?php echo $color; ?> <?php endforeach; ?><?php endif; ?>">
                            <?php echo esc_html($link_title); ?>
                        </span>

                        <span class="site__redes-elemento-icono">
                            <i class="bi <?php echo $icono; ?>"></i>
                        </span>

                    </a>
                <?php endif; ?>

            </li>
        <?php endwhile; ?>
    </ul>
<?php endif; ?>