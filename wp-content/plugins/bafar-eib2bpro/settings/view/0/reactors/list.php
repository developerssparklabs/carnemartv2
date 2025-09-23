<?php defined('ABSPATH') || exit; ?>
<div id="eib2bpro-reactors">
    <div id="eib2bpro-widgets--list">
        <div class="row">
            <?php foreach ($items as $widget) {
            ?>
                <div class="col-md-4 col-sm-12 mb-4 mt-0">
                    <div class="card h-100 eib2bpro-shadow text-white bg-<?php if (1 === $widget['active']) {
                                                                        echo "primary";
                                                                    } else {
                                                                        echo "dark";
                                                                    } ?>">
                        <div class="card-body">
                            <?php if (false !== $widget['badge'] && 0 === $widget['active']) { ?>
                                <span class="text-warning"><?php echo esc_html($widget['badge']); ?></span>
                            <?php } ?>
                            <h3 class="text-white"><?php echo esc_html($widget['title']); ?></h3>
                        </div>
                        <div class="card-body">
                            <p class="card-text"><?php echo esc_html($widget['description']) ?></p>
                            <br>
                            <?php if (false !== $widget['url']) {
                                $url = explode('|', $widget['url']); ?>
                                <a href="<?php echo esc_url_raw($url[1]); ?>" class="btn btn-sm btn-outline-light" target="_blank"><?php echo esc_html($url[0]); ?></a>
                            <?php
                            } elseif (1 === $widget['active']) { ?>
                                <a href="<?php echo eib2bpro_admin('reactors', array('action' => 'detail', 'id' => $widget['id']));  ?>" class="btn btn-sm btn-outline-light eib2bpro-panel trig-close" data-width="600px" data-id="<?php echo esc_attr($widget['id']) ?>"><?php esc_html_e('Settings', 'eib2bpro'); ?></a>
                            <?php } else { ?>
                                <a href="<?php echo eib2bpro_admin('reactors', array('action' => 'detail', 'id' => $widget['id']));  ?>" class="btn btn-sm btn-outline-light eib2bpro-panel trig-close" data-width="600px"><?php esc_html_e('Activate', 'eib2bpro'); ?></a>
                            <?php } ?>
                        </div>
                        <ul class="list-group list-group-flush d-none">
                            <li class="list-group-item">
                                <?php if (isset($installed[$widget['id']])) { ?>
                                    <a href="<?php echo eib2bpro_admin('reactors', array('action' => 'detail', 'id' => $widget['id']));  ?>" data-width="600px" class="eib2bpro-panel trig-close" data-id="<?php echo esc_attr($widget['id']) ?>"><?php esc_html_e('Settings', 'eib2bpro'); ?></a>
                                <?php } else { ?>
                                    <a href="<?php echo eib2bpro_admin('reactors', array('action' => 'detail', 'id' => $widget['id']));  ?>" data-width="600px" class="eib2bpro-panel trig-close"><?php esc_html_e('Activate', 'eib2bpro'); ?></a>
                                <?php } ?>
                            </li>
                        </ul>
                    </div>
                </div>
            <?php
            } ?>
        </div>

    </div>
</div>