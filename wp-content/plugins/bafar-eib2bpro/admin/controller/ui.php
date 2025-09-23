<?php

/**
 * Admin UI: UI elements for panel
 */

namespace EIB2BPRO\Admin;

defined('ABSPATH') || exit;

class UI
{
    /**
     * Input: Textbox
     *
     * @param string $name
     * @param string $value
     * @param array $args
     * @return void
     */

    public function input($name, $value, $args = [])
    {
?>
        <input name="<?php echo esc_attr($name) ?>" value="<?php echo esc_attr($value); ?>" class="form-control eib2bpro-ui-input eib2bpro-input-<?php echo esc_attr(str_replace('[]', '', $name)) . ' ' . eib2bpro_clean($args['class'], '') ?>" <?php echo (isset($args['attr']) ? ' ' . $args['attr'] : '') ?>>
    <?php
    }

    /**
     * Input: Checkbox
     *
     * @param string $name
     * @param string $value
     * @param array $args
     * @return void
     */

    public function checkbox($name, $value, $args = [])
    {
    ?>
        <input type="checkbox" <?php if (1 === (int)$args['checked']) {
                                    echo ' checked';
                                } ?> name="<?php echo esc_attr($name) ?>" value="<?php echo esc_attr($value); ?>" class="form-control eib2bpro-ui-input eib2bpro-input-<?php echo esc_attr(str_replace('[]', '', $name)) . ' ' . eib2bpro_clean($args['class'], '') ?>" <?php echo (isset($args['attr']) ? ' ' . eib2bpro_r($args['attr']) : '') ?>>
        <?php eib2bpro_e($args['title']) ?>
    <?php
    }

    /**
     * Input: Select
     *
     * @param string $name
     * @param string $value
     * @param array $args
     * @return void
     */

    public function select($name, $value, $args = [])
    {
        $args['class'] = $args['class'] ?? '';
        $args['attr'] = $args['attr'] ?? '';

        if (isset($args['ajax'])) {
            $args['class'] .= ' eib2bpro-input-x-select-ajax ';
        }

        foreach ($args as $arg_key => $arg_val) {
            if (in_array($arg_key, ['class', 'attr', 'options', 'ajax'])) {
                continue;
            }
            $args['attr'] .= ' data-' . esc_attr($arg_key) . '="' . esc_attr($arg_val) . '" ';
        } ?>
        <select name="<?php eib2bpro_a($name) ?>" class="eib2bpro-input-select form-control
            eib2bpro-input-<?php echo esc_attr($name) . ' ' . esc_attr($args['class']) ?>" <?php echo wp_kses_post($args['attr']) ?>>
            <?php foreach ($args['options'] as $item_k => $item_v) { ?>
                <?php if (stripos($item_k, '_optgroup_end') !== false) {
                    echo '</optgroup>';
                    continue;
                } ?>
                <?php if (stripos($item_k, '_optgroup') !== false) {
                    echo '<optgroup label="' . esc_attr($item_v) . '">';
                    continue;
                }  ?>

                <option value="<?php eib2bpro_a($item_k) ?>" <?php
                                                        if (is_array($value)) {
                                                            if (in_array($item_k, $value)) {
                                                                echo ' selected';
                                                            }
                                                        } else {
                                                            if ((string)$value === (string)$item_k) {
                                                                echo ' selected';
                                                            }
                                                        } ?>><?php eib2bpro_e($item_v) ?></option>
            <?php } ?>
        </select>
    <?php
    }

    /**
     * Ajax button
     *
     * @param string $name
     * @param string $value
     * @param array $args
     * @return void
     */
    public function ajax_button($name, $value, $args = [])
    {
    ?><a href="javascript:;" class="eib2bpro-app-ajax<?php if (isset($args['class'])) {
                                                    echo ' ' . esc_attr($args['class']);
                                                } ?>" data-app="<?php echo esc_attr(eib2bpro_clean($args['app'], eib2bpro_get('app', false))) ?>" data-action="eib2bpro" data-asnonce="<?php echo wp_create_nonce('eib2bpro-security') ?>" <?php
                                                                                                                                                                                                                    unset($args['class']);
                                                                                                                                                                                                                    foreach ($args as $k => $v) { ?> data-<?php echo esc_attr($k); ?>="<?php echo esc_attr($v) ?>" <?php } ?>>
            <?php echo esc_html($args['title']) ?>
            <?php if (isset($args['html'])) {
                echo wp_kses_post($args['html']);
            } ?>
        </a>

    <?php
    }

    /**
     * Dropdown menu
     *
     * @param string $name
     * @param string $value
     * @param array $args
     * @return void
     */

    public function dd_menu($name = '', $value = '', $args = [])
    {
    ?>
        <div class="eib2bpro-ui-ddmenu btn-group">
            <button class="btn dropdown-toggle border-0 bg-white" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <i class="text-dark fas fa-ellipsis-h font-21"></i>
            </button>
            <div class="dropdown-menu dropdown-menu-tip-n">
                <?php echo wp_kses_post($name) ?>
            </div>
        </div>
    <?php
    }

    /**
     * Input: on/off checkbox with ajax
     *
     * @param string $name
     * @param string $value
     * @param array $args
     * @return void
     */

    public function onoff_ajax($name = '', $value = '', $args = [])
    {
        echo '<input type="checkbox" name="' . esc_attr($name) . '" value="1" data-value="1" data-name="' . esc_attr($name) . '" class="eib2bpro-app-ajax switch_1 ' . ((isset($args['class'])) ? esc_attr($args['class']) : '') . ' eib2bpro-StopPropagation" ' . ((1 === intval($value)) ? ' checked ' : '');
        foreach ($args as $k => $v) {
            echo sprintf('data-%s="%s"', esc_attr($k), esc_attr($v));
        }
        echo 'data-action="' . (isset($args['gateway']) ? esc_attr($args['gateway']) : 'eib2bpro') . '"
             data-asnonce="' . wp_create_nonce('eib2bpro-security') . '"
             >';
    }

    /**
     * Input: on/off checkbox
     *
     * @param string $name
     * @param string $value
     * @param array $args
     * @return void
     */

    public function onoff($name = '', $value = '', $args = [])
    {
        if (isset($args['csv'])) {
            if (is_array($args['csv'])) {
                $checked = in_array($value, $args['csv']) ? ' checked' : '';
            } else {
                $checked = in_array($value, (array)array_map('trim', (array)explode(',', $args['csv']))) ? ' checked' : '';
            }
        } else {
            $checked = ((1 === intval($value)) ? ' checked ' : '');
            $value = 1;
        }
        echo '<input type="hidden" name="' . esc_attr($name) . '" value="0"><input type="checkbox" name="' . esc_attr($name) . '" value="' . $value . '" data-value="' . $value . '" data-name="' . esc_attr($name) . '" class="switch_1 ' . ((isset($args['class'])) ? esc_attr($args['class']) : '') . '" ' . $checked . '>';
    }

    /**
     * Avatar
     *
     * @param integer $id
     * @param array $args
     * @param string $value
     * @return void
     */
    public function avatar($id, $args = array(), $value = '')
    {
        $output = '';
        if (!isset($args['w'])) {
            $args['w'] = 55;
            $args['h'] = 55;
        }

        if (!isset($args['border'])) {
            $args['border'] = '1px solid #f5f5f5';
            $font = ($args['w'] - 1) / 2;
        } else {
            $font = ($args['w'] - 1) / 2;
        }

        if (!isset($args['style'])) {
            $style = '';
        } else {
            $style = $args['style'];
        }

        if (is_array($id)) {
            $output .= "<div class='eib2bpro-Avatars'>";

            foreach ($id as $i) {
                if (!empty($i)) {
                    $output .= $this->avatar($i, $args);
                }
            }
            $output .= "</div>";
        } else {

            $style .= 'width:' . esc_attr($args['w']) . 'px; height:' . esc_attr($args['h']) . 'px; border: ' . eib2bpro_r($args['border']) . '; ';

            $class = "" . (isset($args['class']) ? $args['class'] : '');

            if (isset($args['ml'])) {
                $class .= ' eib2bpro-avatar-ml ';
            }

            $user = get_userdata($id);
            $avatar = get_user_meta($user->ID, 'eib2bpro_avatar', true);
            $name = $user->display_name;

            if ($avatar) {
                $image = wp_get_attachment_image_src(intval($avatar), 'full');
                if (is_array($image) && isset($image[1])) {
                    $display = "<img class='eib2bpro-Avatar-Image' data-tooltip='" . esc_attr($name) . "' src='" . esc_url($image[0]) . "'>";
                }
            } else {
                $class .= 'eib2bpro-Avatar-Empty';

                $colors = array('#aaa', 'red', '#ccc', '#ddd', 'red', 'orange', 'orange', 'orange', 'orange');

                $style .= 'background: ' . eib2bpro_r($colors[$id % 10]) . ';';

                $display = substr($user->display_name, 0, 1);
            }

            if (isset($args['style'])) {
                $style = 'style="' . eib2bpro_r($style) . '"';
            } else {
                $style = '';
            }

            $output .=  '<div class="eib2bpro-Avatar ' . eib2bpro_r($class) . '" ' . eib2bpro_r($style) . '  data-toggle=" ' . esc_attr($args['toggle']) . '" data-title="' . esc_attr($name) . '">' . eib2bpro_r($display) . '</div>';
        }
        return $output;
    }

    /**
     * B2B Users selector for selectize
     *
     * @param string $name
     * @param string $value
     * @param array $args
     * @return void
     */

    public function b2b_users_select($name = '', $value = '', $args = [])
    {
        if (is_array($value)) {
            $users = array_map('trim', $value);
        } else {
            $users = array_map('trim', (array)explode(',', $value));
        } ?>
        <select placeholder="<?php isset($args['placeholder']) ? eib2bpro_a($args['placeholder']) : '' ?>" name="<?php eib2bpro_a($name) ?>[]" class="eib2bpro-app-user-select hidden" multiple>
            <?php if (is_array($users) && 0 < count($users)) {
                foreach ($users as $user) {
                    if (intval($user) === 0) {
                        continue;
                    } ?>
                    <option value="<?php eib2bpro_a($user) ?>" selected>
                        <?php printf('%s %s (%s)', get_user_meta($user, 'first_name', true), get_user_meta($user, 'last_name', true), get_userdata($user)->user_login) ?>
                    </option>
            <?php
                }
            } ?>
        </select>
    <?php
    }

    /**
     * B2B Products selector for selectize
     *
     * @param string $name
     * @param string $value
     * @param array $args
     * @return void
     */

    public function b2b_product_select($name = '', $value = '', $args = [])
    {
        if (is_array($value)) {
            $products = array_map('trim', $value);
        } else {
            $products = array_map('trim', (array)explode(',', $value));
        } ?>
        <select placeholder="<?php isset($args['placeholder']) ? eib2bpro_a($args['placeholder']) : '' ?>" name="<?php eib2bpro_a($name) ?>[]" class="eib2bpro-app-product-select hidden<?php echo (isset($args['class']) ? ' ' . $args['class'] : '') ?>">
            <?php if (is_array($products) && 0 < count($products)) {
                foreach ($products as $product) {
                    if (intval($product) === 0) {
                        continue;
                    } ?>
                    <option value="<?php eib2bpro_a($product) ?>" selected data-data='<?php echo eib2bpro_r(json_encode(['name' => esc_html(get_the_title($product)), 'price_currency' => get_post_meta($product, '_price', true)])) ?>'>
                        <?php echo esc_html(get_the_title($product)) ?></option>
            <?php
                }
            } ?>
        </select>
    <?php
    }

    /**
     * Input: Media
     *
     * @param string $name
     * @param string $value
     * @param array $args
     * @return void
     */

    public static function media($name = '', $value = '', $args = [])
    {
        $uniqid = md5($name);
        if (0 < intval($value) && $image = wp_get_attachment_image_src($value, 'full')) {
            echo '<a href="#" class="eib2bpro-media-upl eib2bpro-media-upl-' . esc_attr($uniqid) . '"><img src="' . esc_attr($image[0]) . '" /></a>
                  <a href="#" class="eib2bpro-media-rmv eib2bpro-media-rmv-' . esc_attr($uniqid) . '">' . esc_html__('Remove image', 'eib2bpro') . '</a>
                  <input type="hidden"  class="eib2bpro-media-id-' . esc_attr($uniqid) . '" name="' . esc_attr($name) . '" value="' . esc_attr($value) . '">';
        } else {
            echo '<a href="#" class="eib2bpro-media-upl eib2bpro-media-upl-' . esc_attr($uniqid) . '">' . esc_html__('Upload image', 'eib2bpro') . '</a>
                  <a href="#" class="eib2bpro-media-rmv eib2bpro-media-rmv-' . esc_attr($uniqid) . ' d-none hidden">' . esc_html__('Remove image', 'eib2bpro') . '</a>
                  <input type="hidden" class="eib2bpro-media-id-' . esc_attr($uniqid) . '" name="' . esc_attr($name) . '" value="">';
        } ?>
        <script>
            jQuery(function($) {
                "use strict";

                // on upload button click
                $('body').on('click', '.eib2bpro-media-upl-<?php eib2bpro_a($uniqid) ?>', function(e) {

                    e.preventDefault();

                    var button = $(this),
                        custom_uploader = wp.media({
                            library: {
                                type: 'image'
                            },
                            multiple: false
                        }).on('select', function() {
                            var attachment = custom_uploader.state().get('selection').first().toJSON();
                            button.html('<img src="' + attachment.url + '">').show()
                            $('.eib2bpro-media-id-<?php eib2bpro_a($uniqid) ?>').val(attachment.id);
                        }).open();

                });

                //  remove button click
                $('body').on('click', '.eib2bpro-media-rmv-<?php eib2bpro_a($uniqid) ?>', function(e) {

                    e.preventDefault();

                    var button = $(this);
                    button.next().val('');
                    button.hide().prev().html('<?php esc_html_e('Select image', 'eib2bpro') ?>');
                });

            });
        </script>
<?php
    }

    /**
     * WPML Available languages
     *
     * @param string $name
     * @param string $value
     * @param array $args
     * @return array
     */

    public function langs($name = '', $value = '', $args = [])
    {
        $langs['en'] = 'en';

        $icl_langs = function_exists('icl_get_languages') ? icl_get_languages() : [];
        if (!empty($icl_langs)) {
            foreach ($icl_langs as $icl => $ic) {
                $langs[$icl] = $icl;
            }
        }
        return array_reverse($langs);
    }

    /**
     * Show WPML language selector
     *
     * @param string $name
     * @param string $value
     * @param array $args
     * @return void
     */
    public function show_langs($name = '', $value = '', $args = [])
    {
        $langs = array_reverse($this->langs());

        if (!empty($langs)) {
            echo "<div class='eib2bpro_multi_langs float-right clearfix'>";
            foreach ($langs as $lang) {
                echo "<a class='eib2bpro_multi_lang' href='javascript:;' data-lang='" . esc_attr($lang) . "'>" . esc_html($lang) . "</a>";
            }
            echo "</div>";
        }
    }

    /**
     * Main WPML language selector
     *
     * @param string $name
     * @param string $value
     * @param array $args
     * @return void
     */

    public static function wpml_selector($name = '', $value = '', $args = [])
    {

        if (!function_exists('icl_object_id')) {
            return false;
        }

        global $wpdb, $wp_admin_bar, $pagenow, $mode, $sitepress;

        $all_languages_enabled = true;
        $current_page      = basename($_SERVER['SCRIPT_NAME']);
        $post_type         = false;
        $trid              = false;
        $translations      = false;
        $languages_links   = array();

        // individual translations
        $is_post = false;
        $current_language = $sitepress->get_current_language();
        $current_language = $current_language ? $current_language : $sitepress->get_default_language();

        if (0 === eib2bpro_get('id', 0, 'int')) {
        } else {

            $is_post           = true;
            $post_id           = eib2bpro_get('id', 0);
            $post              = get_post($post_id);

            $post_language = $sitepress->get_language_for_element($post_id, 'post_' . get_post_type($post_id));
            if ($post_language && $post_language !== $current_language) {
                $sitepress->switch_lang($post_language);
                $current_language = $sitepress->get_current_language();
            }
            $trid         = $sitepress->get_element_trid($post_id, 'post_' . $post->post_type);
            $translations = $sitepress->get_element_translations($trid, 'post_' . $post->post_type, true);
        }


        $active_languages = $sitepress->get_active_languages();
        if ('all' !== $current_language) {
            $current_active_language = isset($active_languages[$current_language]) ? $active_languages[$current_language] : null;
        }
        $active_languages = apply_filters('wpml_admin_language_switcher_active_languages', $active_languages);
        if ('all' !== $current_language && !isset($active_languages[$current_language])) {
            array_unshift($active_languages, $current_active_language);
        }

        foreach ($active_languages as $lang) {
            $current_page_lang = $current_page;

            if (isset($_SERVER['QUERY_STRING'])) {
                parse_str($_SERVER['QUERY_STRING'], $query_vars);
                unset($query_vars['lang'], $query_vars['admin_bar']);
            } else {
                $query_vars = array();
            }
            // individual translations
            if ($is_post) {
                if (isset($translations[$lang['code']]) && isset($translations[$lang['code']]->element_id)) {
                    $query_vars['post'] = $translations[$lang['code']]->element_id;
                    $query_vars['id'] = $translations[$lang['code']]->element_id;
                    unset($query_vars['source_lang']);
                    $current_page_lang      = 'admin.php';
                } else {
                    $current_page_lang = 'admin.php';
                    if (isset($post)) {
                        $query_vars['original'] = eib2bpro_get('id', 0);
                        $query_vars['post_type']   = $post->post_type;
                        $query_vars['source_lang'] = $current_language;
                    } else {
                        $query_vars['post_type'] = $post_type;
                    }
                    $query_vars['trid'] = $trid;
                    unset($query_vars['post'], $query_vars['id']);
                }
            }

            $query_string = http_build_query($query_vars);

            $query = '?';
            if (!empty($query_string)) {
                $query .= $query_string . '&';
            }
            $query .= 'lang=' . $lang['code']; // the default language need to specified explicitly yoo in order to set the lang cookie

            $link_url = admin_url($current_page_lang . $query);

            $flag = $sitepress->get_flag($lang['code']);

            if ($flag) {
                if ($flag->from_template) {
                    $wp_upload_dir = wp_upload_dir();
                    $flag_url      = $wp_upload_dir['baseurl'] . '/flags/' . $flag->flag;
                } else {
                    $flag_url = ICL_PLUGIN_URL . '/res/flags/' . $flag->flag;
                }
            } else {
                $flag_url = ICL_PLUGIN_URL . '/res/flags/';
            }

            $languages_links[$lang['code']] = array(
                'url'     => $link_url . '&admin_bar=1',
                'current' => $lang['code'] === $current_language,
                'anchor'  => $lang['display_name'],
                'flag'    => '<img class="icl_als_iclflag" src="' . esc_url($flag_url) . '" alt="' . esc_attr($lang['code']) . '" width="18" height="12" />'
            );
        }

        if ($all_languages_enabled) {
            $query = '?';
            if (!empty($query_string)) {
                $query .= $query_string . '&';
            }
            $query .= 'lang=all';
            $link_url = admin_url(basename($_SERVER['SCRIPT_NAME']) . $query);
        } else {
            // set the default language as current
            if ('all' === $current_language) {
                $current_language = $sitepress->get_default_language();
                $languages_links[$current_language]['current'] = true;
            }
        }

        $current_language_item = isset($languages_links[$current_language]) ? $languages_links[$current_language] : null;
        $languages_links       = apply_filters('wpml_admin_language_switcher_items', $languages_links);
        if (!isset($languages_links[$current_language])) {
            $languages_links = array_merge(array($current_language => $current_language_item), $languages_links);
        }

        if ($languages_links) {
            echo eib2bpro_r("<span class='eib2bpro-wpml-selector'>");
            foreach ($languages_links as $code => $lang) {
                echo eib2bpro_r("<a href='" . esc_url($lang['url'])  . "'" . (($code === $current_language) ? "class='eib2bpro-selected'" : '') . ">" . eib2bpro_r($lang['flag']) . "</a>");
            }
            echo eib2bpro_r("</span>");
        }
    }
}
