<?php
if (!defined('ABSPATH'))
    die('No direct access allowed');

class WCMLIM_Widget extends WP_Widget
{

    public function __construct()
    {

        parent::__construct(
            __CLASS__,
            __('WCMLIM - Filter Products By Locations', 'wcmlim'),
            array(
                'classname' => __CLASS__,
                'description' => __('Display a list of locations to filter products in your store.', 'wcmlim')
            )
        );
    }

    public function widget($args, $instance)
    {
        if (is_shop()) {
            
            $backendMode = get_option("wcmlim_allow_only_backend");
            if ($backendMode == "on") {
                return;
            }
            
            echo $args['before_widget'];
            if (!empty($instance['title'])) {
                echo "<span class='gamma widget-title'>{$instance['title']}</span>";
            } else {
                echo $args['before_title'] . apply_filters('widget_title', 'WCMLIM - Filter Products By Locations') . $args['after_title'];
            }
            $excludeLocations = get_option("wcmlim_exclude_locations_from_frontend");
            $wst = get_option("wcmlim_widget_select_mode");
            if (!empty($excludeLocations)) {
                $terms = get_terms(array('taxonomy' => 'locations', 'hide_empty' => false, 'parent' => 0, 'exclude' => $excludeLocations));
            } else {
                $terms = get_terms(array('taxonomy' => 'locations', 'hide_empty' => false, 'parent' => 0));
            }
            if (!empty($terms)) {
               

                
?>
                <form action="" name="wcmlimWidFrom" method="POST">
                    <?php if ($wst == "multi") { ?>
                        <select class="wcmlim_locwid_dd" name="wcmlim_locations_wid[]" multiple="multiple">
                        <?php } else { ?>
                            <select class="wcmlim_locwid_dd" name="wcmlim_locations_wid">
                            <?php } ?>
                            <option value="-1"><?php _e(' - Select - ', 'wcmlim'); ?></option>
                            <?php
                            global $wp;
                            $activeLocation =  isset($_COOKIE['wcmlim_widget_chosenlc']) ? $_COOKIE['wcmlim_widget_chosenlc'] : "";
                            $exploded_activeLocation = explode(',', $activeLocation);
                            foreach ($terms as $k => $term) {
                                $term_meta = get_option("taxonomy_$term->term_id");
                                $term_meta = array_map(function ($term) {
                                    if (!is_array($term)) {
                                        return $term;
                                    }
                                }, $term_meta);
                                $term_meta = array_filter($term_meta);
                                $rl = implode(" ", $term_meta);
                            ?>
                                <option <?php if ($wst == "multi") {
                                            if (in_array($term->term_id, $exploded_activeLocation)) {
                                                echo "selected='selected'";
                                            }
                                        } else {
                                            if ($activeLocation == $term->term_id) {
                                                echo "selected='selected'";
                                            }
                                        } ?> value="<?php echo $term->term_id; ?>"><?php echo $term->name . '(' . $term->count . ')'; ?></option>
                            <?php
                            } ?>
                            </select>
                            <?php if ($wst == "multi") { ?>
                                <br>
                                <div class="wcmlim_location_search_container" style="margin-top:20px;">
                                    <a class="button wcmlim_reset_location_form">Reset</a>
                                    <a style="float: left; margin-right:10px;" class="button wcmlim_submit_location_form">Filter</a>
                                </div>
                            <?php } ?>
                </form>
        <?php
                // Keep this line
                echo $args['after_widget'];
            }
        }
    }

    public function form($instance)
    {
        $defaults = array(
            'title' => __('WooCommerce Products Filter', 'wcmlim'),
        );
        $instance = wp_parse_args((array) $instance, $defaults);
        $args = array();
        $args['instance'] = $instance;
        $args['widget'] = $this;
        ?>
        <p>
            <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title', 'wcmlim') ?>:</label>
            <input class="widefat" type="text" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" value="<?php echo $instance['title']; ?>" />
        </p>
<?php
    }

    public function update($new_instance, $old_instance)
    {
        $instance = $old_instance;
        $instance['title'] = $new_instance['title'];
        return $instance;
    }
}
