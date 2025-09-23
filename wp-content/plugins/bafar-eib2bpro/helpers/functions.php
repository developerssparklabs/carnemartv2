<?php

/**
 * Options for EIB2BPRO
 *
 * @since  1.0.0
 */

function eib2bpro_option($key, $default = '', $action = 'get', $user = false)
{
    if ('get' === $action) {
        if ($user) {
            $value = get_user_meta(get_current_user_id(), $key, true);
        } else {
            $value = get_option('eib2bpro_' . $key, $default);
        }
        if (is_int($default)) {
            return intval($value);
        }
        return $value;
    } else {
        if ($user) {
            update_user_meta(get_current_user_id(), $key, $default);
        } else {
            update_option('eib2bpro_' . $key, $default);
        }
    }
}

function eib2bpro_option_translate($key, $default = '')
{
    return $default;

    if (function_exists('icl_object_id')) {
        return $default;
    } else {
        return eib2bpro_option($key, $default);
    }
}



/**
 * Sanitize GET
 *
 * @since  1.0.0
 */

function eib2bpro_get($key = '', $default = '', $type = false)
{
    if (!isset($_GET[$key]) or empty($_GET[$key])) {
        return $default;
    }

    $value = sanitize_text_field($_GET[$key]);

    if ($type) {
        switch ($type) {
            case 'int':
                $value = intval($value);
                break;
            case 'float':
                $value = floatval($value);
                break;
        }
    }

    return $value;
}

/**
 * Sanitize POST
 *
 * @since  1.0.0
 */

function eib2bpro_post($key = '', $default = '', $type = false)
{
    if (!isset($_POST[$key])) {
        return $default;
    }

    $value = wc_clean($_POST[$key]);

    if ($type) {
        switch ($type) {
            case 'int':
                $value = intval($value);
                break;
            case 'float':
                $value = floatval($value);
                break;
        }
    }

    return $value;
}

function eib2bpro_view($app, $mood, $file_original, $data = array())
{

    $theme = sanitize_key(\EIB2BPRO\Admin::$theme);
    $dir = EIB2BPRO_DIR;

    $file_original_c = explode('.', $file_original);

    $view_file = "";

    foreach ($file_original_c as $file) {
        $sanitize_file_name = sanitize_file_name($file);
        if ($sanitize_file_name !== '') {
            $view_file .= '/' . $sanitize_file_name;
        }
    }

    if ('core' === $app || '*' === $mood) {
        $file = $view_file . '.php';
    } else {
        $file = '/view/' . $mood . '' . $view_file . '.php';
    }

    if (!file_exists($dir . 'core/themes/' . $theme . $file)) {
        $file = $dir . $app . $file;
    } else {
        $file = $dir . 'core/themes/' . $theme . $file;
    }

    extract($data, EXTR_REFS);
    ob_start();
    include(apply_filters('eib2bpro_views', $file));
    return ob_get_clean();
}

/**
 * Generate admin link for EnergyPlus panels
 *
 * @since  1.0.0
 */

function eib2bpro_admin($app = '', $others = array())
{
    return admin_url("admin.php?page=eib2bpro&app=" . $app . "&" . implode('&', array_map(
        function ($v, $k) {
            return "$k=$v";
        },
        $others,
        array_keys($others)
    )));
}


/**
 * Generate an url for sorting
 *
 * @since  1.0.0
 */

function eib2bpro_thead_sort($sort)
{
    $class = '';
    $url = remove_query_arg(array('orderby', 'order'), filter_input(INPUT_SERVER, 'REQUEST_URI'));
    $order = (isset($_GET['order']) && $_GET['order'] === "ASC") ? "DESC" : "ASC";

    if (isset($_GET['orderby']) && ($sort === $_GET['orderby'])) {
        $class = '" class="__A__Order_' . esc_attr($order);
    }

    return esc_url(add_query_arg(
        array('orderby' => $sort, 'order' => $order)
    ), $url) . $class;
}

/**
 * Send query results to $api variable for using global
 *
 * @since  1.0.0
 */

function eib2bpro_api_pagination($_this, $query)
{
    \EIB2BPRO\Admin::$api = array('query' => $query);
}


/**
 * Groups array by key
 *
 * @since  1.0.0
 */

function eib2bpro_group_by($key, $data)
{
    $result = array();
    foreach ($data as $val) {
        if (array_key_exists($key, $val)) {
            $result[$val[$key]][] = $val;
        } else {
            $result[""][] = $val;
        }
    }

    return $result;
}


/**
 * Generate product image div by id
 *
 * @since  1.0.0
 */
function eib2bpro_product_image($product_id, $quantity = 0, $style = '', $title = false)
{
    if ($title) {
        echo '<div class="eib2bpro-Product_Image_Container">';
        $image = get_the_post_thumbnail_url($product_id, array(150, 150));
        if ($image) {
            echo '<img src="' . esc_url_raw($image) . '" title="' . esc_attr($title) . '" class="eib2bpro-Product_Image" style"' . "=" . esc_attr($style) . '" >';
            if (1 <> $quantity) {
                echo '<span class="badge badge-pill badge-danger eib2bpro-Product_Image_Qny">' . esc_html($quantity) . '</span>';
            }
        }
        echo '</div>';
    } else {
        if (0 < absint($product_id)) {
            $product = wc_get_product($product_id);

            if (!$product) {
                return;
            }

            echo '<div class="eib2bpro-Product_Image_Container">';
            $image = get_the_post_thumbnail_url($product_id, array(150, 150));
            if ($image) {
                echo '<img src="' . esc_url_raw($image) . '" title="' . esc_attr($product->get_title()) . '" class="eib2bpro-Product_Image" style="' . esc_attr($style) . '" >';
                if (1 <> $quantity) {
                    echo '<span class="badge badge-pill badge-danger eib2bpro-Product_Image_Qny">' . esc_html($quantity) . '</span>';
                }
            }
            echo '</div>';
        }
    }
}

/**
 * Sanitize a string
 *
 * @param string $string
 * @param string $secondary
 * @return string
 * @since  1.0.0
 */

function eib2bpro_clean(&$string, $secondary = '')
{
    if (!isset($string) or '' === trim($string)) {
        $string = $secondary;
    }

    return sanitize_text_field($string);
}

function eib2bpro_clean2($string, $secondary = '')
{
    if (!isset($string) or '' === trim($string) or null === $string) {
        $string = $secondary;
    }

    return sanitize_text_field($string);
}

function eib2bpro_option_color($args = array())
{
    echo '<div class="__A__Helpers_Row row">
    <div class="float-left"><input name="' . esc_attr($args['name']) . '" type="text" value="' . esc_attr($args['value']) . '" class="__A__Reactors_Color_' . esc_attr($args['name']) . ' ' . esc_attr($args['css']) . ' energyplus-color-field energyplus-color-field-tmpID" data-default-color="' . esc_attr($args['value']) . '"
    /></div><div class="float-left pt-2"> &nbsp; ' . esc_html($args['label']) . '</div></div>';
    if (!isset($args['no-js']) || (isset($args['no-js']) && $args['no-js'] === false)) {
        echo '<script>
      jQuery(document).ready(function() {
        "use strict";
        jQuery(".__A__Reactors_Color_' . esc_attr($args['name']) . '").wpColorPicker({ width:160 });
      });
      </script>';
    }
}

function eib2bpro_style($values)
{
    echo eib2bpro_r('style' . '="' . esc_attr($values) . '"');
}

function eib2bpro_form($args = array())
{
    echo '<form action="" method="post">
            <input name="app" type="hidden" value="' . esc_attr((isset($args['app']) ? $args['app'] : eib2bpro_get('app', 'default'))) . '">
            <input name="action" type="hidden" value="' . esc_attr((isset($args['gateway']) ? $args['gateway'] : 'eib2bpro')) . '">
            <input name="eib2bpro-app-current-tab" type="hidden" class="eib2bpro-app-current-tab" value="0">
            <input name="asnonce" type="hidden" value="' . esc_attr(wp_create_nonce('eib2bpro-security')) . '">';
    foreach ($args as $key => $value) {
        echo '<input name="' . esc_attr($key) . '" type="hidden" value="' . esc_attr($value) . '">';
    }
}

function eib2bpro_is_ajax()
{
    if (!empty(filter_input(INPUT_SERVER, 'HTTP_X_REQUESTED_WITH')) && strtolower(filter_input(INPUT_SERVER, 'HTTP_X_REQUESTED_WITH')) === 'xmlhttprequest') {
        return true;
    } else {
        return false;
    }
}

function eib2bpro_frame($page)
{
    echo eib2bpro_view('core', 0, 'shared.index.frame', array('page' => esc_url($page)));
    return;
}


function eib2bpro_meta_by_lang($post_id, $meta_key, $default = '', $lang = false)
{
    if (defined('ICL_LANGUAGE_CODE')) {
        $current_lang = ICL_LANGUAGE_CODE;
    } else {
        $current_lang = 'en';
    }
    return get_post_meta($post_id, $meta_key . '_' . $current_lang, true);
}

/**
 * Generate and verify nonce
 *
 * @param boolean $get_nonce
 * @param string $action
 * @since  1.0.0
 */

function eib2bpro_ajax_nonce($get_nonce = false, $action = 'eib2bpro-general')
{
    if (true === $get_nonce) {
        $nonce = sanitize_key($_REQUEST['_wpnonce']);

        if (!wp_verify_nonce($nonce, $action)) {
            exit;
        }
    }

    return "_wpnonce: jQuery('input[name=_wpnonce]').val(), _wp_http_referer: jQuery('input[name=_wp_http_referer]').val()";
}


/**
 * Group arrays by date
 *
 * @param string $date
 * @since  1.0.0
 */

function eib2bpro_grouped_time($date)
{
    $date = wc_format_datetime($date, 'Y-m-d H:i:s');

    if (intval(eib2bpro_strtotime('now', 'Ymd')) === intval(eib2bpro_strtotime($date, 'Ymd'))) {
        return array('key' => 'd' . eib2bpro_strtotime($date, 'z'), 'title' => esc_html__('Today', 'eib2bpro'));
    }

    if ((intval(eib2bpro_strtotime('now', 'Y')) === intval(eib2bpro_strtotime($date, 'Y'))) && (intval((eib2bpro_strtotime('now', 'z') - 1)) === intval(eib2bpro_strtotime($date, 'z')))) {
        return array('key' => 'd' . eib2bpro_strtotime($date, 'z'), 'title' => esc_html__('Yesterday', 'eib2bpro'));
    }

    if (eib2bpro_strtotime($date, 'Ymd') >= eib2bpro_strtotime('monday this week', 'Ymd')) {
        return array('key' => 'w' . eib2bpro_strtotime($date, 'mW'), 'title' => esc_html__('This Week', 'eib2bpro'));
    }

    if ((eib2bpro_strtotime($date, 'Ymd') <= eib2bpro_strtotime('monday this week', 'Ymd')) and (eib2bpro_strtotime($date, 'Ymd') >= eib2bpro_strtotime('monday last week', 'Ymd'))) {
        return array('key' => 'w' . eib2bpro_strtotime($date, 'mW'), 'title' => esc_html__('Last Week', 'eib2bpro'));
    }

    if ((eib2bpro_strtotime($date, 'Ymd') <= eib2bpro_strtotime('monday last week', 'Ymd')) and (eib2bpro_strtotime($date, 'Ymd') >= eib2bpro_strtotime('first day of this month', 'Ymd'))) {
        return array('key' => 'm' . eib2bpro_strtotime($date, 'm'), 'title' => esc_html__('This Month', 'eib2bpro'));
    }

    if ((eib2bpro_strtotime($date, 'Ymd') < eib2bpro_strtotime('first day of this month', 'Ymd')) and (eib2bpro_strtotime($date, 'Ymd') >= eib2bpro_strtotime('first day of January', 'Ymd'))) {
        return array('key' => 'm' . eib2bpro_strtotime($date, 'm'), 'title' => date_i18n('F', strtotime($date)));
    }

    return array('key' => 'y' . eib2bpro_strtotime($date, 'Y'), 'title' => esc_html__('Year ', 'eib2bpro') . eib2bpro_strtotime($date, 'Y'));
}


/**
 * Get strtotime and parse it to date
 *
 * @since  1.0.0
 */

function eib2bpro_strtotime($time, $format = 'Y-m-d H:i:s', $tz = true)
{
    $tz_string = get_option('timezone_string');
    $tz_offset = get_option('gmt_offset', 0);

    if (!empty($tz_string)) {
        // If site timezone option string exists, use it
        $timezone = $tz_string;
    } elseif ($tz_offset === 0) {
        // get UTC offset, if it isn’t set then return UTC
        $timezone = 'UTC';
    } else {
        $timezone = $tz_offset;

        if (substr($tz_offset, 0, 1) !== "-" && substr($tz_offset, 0, 1) !== "+" && substr($tz_offset, 0, 1) !== "U") {
            $timezone = "+" . $tz_offset;
        }
    }

    $datetime = new DateTime($time, new DateTimeZone($timezone));
    return $datetime->format($format);

    $diff = 0;

    if ('today' === $time) {
        $time = 'now';
        $format = str_replace('H:i:s', '00:00:00', $format);
    }

    if ($tz) {
        $diff = get_option('gmt_offset') * HOUR_IN_SECONDS;
    }

    return gmdate($format, strtotime($time) + $diff);
}

function eib2bpro_sanitize_array($array_or_string = array())
{
    if (is_string($array_or_string)) {
        $array_or_string = sanitize_text_field($array_or_string);
    } elseif (is_array($array_or_string)) {
        foreach ($array_or_string as $key => &$value) {
            if (is_array($value)) {
                $value = eib2bpro_sanitize_array($value);
            } else {
                $value = sanitize_text_field($value);
            }
        }
    }

    return $array_or_string;
}

function eib2bpro_checked($value)
{
    if (1 === $value) {
        echo ' checked';
    }
}

function eib2bpro_exists($class = '')
{
    return class_exists($class);
}

function eib2bpro_class($app, $controller, $func)
{
    $class = '\EIB2BPRO\\' . sanitize_key($app) . '\\' . sanitize_key($controller);

    if ('public' === $func) {
        if (
            class_exists($class)
        ) {
            $class::public();
        }
    }

    if ('run' === $func) {
        if (
            class_exists($class)
            && current_user_can(
                apply_filters(
                    'eib2bpro_required_cap',
                    'manage_woocommerce'
                )
            )
        ) {
            $class::run();
        }
    }
}

function eib2bpro_title_link($link)
{
    echo eib2bpro_r($link);
}

function eib2bpro_change_url($from, $to, $old_classes, $new_classes, $is_default = false)
{
    $class = '" class="' . esc_attr($old_classes) . '"';
    $url =         remove_query_arg($from, filter_input(INPUT_SERVER, 'REQUEST_URI'));

    if ((isset($_GET[$from]) && ($to  === $_GET[$from])) or (!isset($_GET[$from]) && $is_default === true)) {
        $class = '" class="' . esc_attr($old_classes) . ' ' . esc_attr($new_classes) . ' eib2bpro-Selected"';
    }

    return esc_url(add_query_arg(
        array($from => $to)
    ), $url) . $class;
}


/**
 * Determine selected items by GET
 *
 * @since  1.0.0
 */

function eib2bpro_selected($key = '', $selected = false, $class = ' eib2bpro-Selected')
{
    if (!isset($_GET[$key])) {
        $_GET[$key] = false;
    }

    if ($_GET[$key] === $selected) {
        echo esc_attr($class);
    }
}

/**
 * Generate an url with nonce
 *
 * @param string $segment
 * @param string $action
 * @param array $others
 * @return string
 * @since  1.0.0
 */

function eib2bpro_secure_url($segment, $action = 'asterik-u', $others = array())
{
    if ('frame' === $segment or isset($others['go'])) {
        return eib2bpro_admin($segment, $others);
    }

    return wp_nonce_url(eib2bpro_admin($segment, $others), $action);
}

/**
 * Print error message on failure
 *
 * @param string $error
 * @since  1.0.0
 */

function eib2bpro_error($error)
{
    if (eib2bpro_is_ajax()) {
        echo eib2bpro_r(json_encode(array('status' => 0, 'message' => esc_html($error))));
    } else {
        echo esc_html($error);
    }
    wp_die();
}

/**
 * Groups an array by a given key. Any additional keys will be used for grouping
 * the next set of sub-arrays.
 *
 * @param array $arr The array to be grouped.
 * @param mixed $key,... A set of keys to group by.
 *
 * @return array
 * @author Jake Zatecky
 *
 */
function eib2bpro_array_group_by(array $arr, $key)
{
    $grouped = [];
    foreach ($arr as $value) {
        if (isset($value[$key]) && !empty($value[$key])) {
            $groupKey = null;
            $groupKey = $value[$key];
            $grouped[$groupKey][] = $value;
        }
    }

    return $grouped;
}

/**
 * Print success message
 *
 * @param string $message
 * @param array $details
 * @since  1.0.0
 */

function eib2bpro_success($message = '', $details = array(), $raw = false)
{
    if ('' === $message) {
        $message = esc_html__('Done', 'eib2bpro');
    }

    echo eib2bpro_r(json_encode(array_merge(array('status' => 1, 'message' => $message), $details)));
    wp_die();
}


function eib2bpro_select($name, $value = null, $options = [], $args = [], $class = '', $style = '')
{
    echo "<select name='" . esc_attr($name) . "' class='form-control os-input-" . esc_attr($name) . " " . esc_attr($class) . "'";

    if ($style) {
        echo " style='" . esc_attr($style) . "'";
    }

    foreach ($args as $key => $val) {
        echo ' ' . esc_attr($key) . '="' . esc_attr($val) . '"';
    }

    echo '>';

    if (0 < count($options)) {
        foreach ($options as $key => $val) {
            echo "<option value='" . esc_attr($key) . "'" . (($key === $value) ? ' selected' : '') . ">" . esc_html($val) . "</option>";
        }
    }
    echo "</select>";
}

function eib2bpro_save($title = '', $class = '', $args = [])
{
    echo '<button type="button" class="btn-save eib2bpro-app-save-button eib2bpro-os-stop-propagation ' . esc_attr($class) . '"';

    if (!empty($args) && is_array($args)) {
        foreach ($args as $key => $value) {
            echo esc_attr($key) . '="' . esc_attr($value) . '"';
        }
    }

    echo '>' . (!empty($title) ? esc_html($title) : esc_html__('Save', 'eib2bpro')) . '</button>';
}


function eib2bpro_select_avatars($name, $value = [], $args = [])
{
    $users = EIB2BPRO\Admin::users();

    if ('all' === $value) {
        $value = array_keys($users);
    }

    echo '<div class="row">';

    foreach ($users as $user) {
        echo '<div class="pl-2">
        <div class="custom-control custom-checkbox image-checkbox">
            <input name="' . esc_attr($name) . '" value="' . esc_attr($user['id']) . '" type="checkbox" class="custom-control-input" id="eib2bpro-user-' . esc_attr($user['id']) . '"' . (in_array($user['id'], $value) ? ' checked' : '') . '>
            <label class="custom-control-label" for="eib2bpro-user-' . esc_attr($user['id']) . '">
                ' . wp_kses_post($user['avatar']) . '
            </label>
        </div>
    </div>';
    }

    echo "</div>";
}

function eib2bpro_app($value)
{
    return 0;
}
function eib2bpro_e($value)
{
    echo esc_html($value);
}

function eib2bpro_a($value)
{
    echo esc_attr($value);
}

function eib2bpro_r($value)
{
    return $value;
}

function eib2bpro_ui($type, $name = false, $value = false, $args = [])
{
    $ui = new \EIB2BPRO\Admin\UI;
    return $ui->$type($name, $value, $args);
}
