<?php

/**
 * Data Class for validation
 */

namespace EIB2BPRO\Admin;

defined('ABSPATH') || exit;

class Data
{
    /**
     * Check if the current user can edit the data
     *
     * @param integer $owner
     * @param integer $check
     * @return void
     */

    public static function checkPermission($owner = 0, $check = 0)
    {
        if (0 === $check) {
            $check = get_current_user_id();
        }

        if (intval($owner) !== intval($check) && !is_admin()) {
            eib2bpro_error(esc_html__('Permission error', 'eib2bpro'));
        }
    }

    /**
     * Validate and sanitize input data
     *
     * @param array $forms
     * @param array $post
     * @return array
     */

    public static function validate($forms = [], $post = [])
    {
        $data = [];
        $error = [];

        foreach ($forms as $key => $attr) {
            $value = null;

            if (isset($post[$key])) {
                $data[$key] = null;

                if (is_array($post[$key])) {
                    $data[$key] = $post[$key];
                } else {
                    if ($post[$key]) {
                        $value = isset($post[$key]) ? $post[$key] : (isset($attr['default']) ? $attr['default'] : '');
                        $data[$key] = $value;
                    }
                }

                if (isset($attr['required']) && true === $attr['required']) {
                    if (!isset($post[$key]) || empty($post[$key])) {
                        $error[] = sprintf(esc_html__('%s is required', 'eib2bpro'), $key);
                    }
                }

                if (isset($attr['sanitize'])) {
                    switch ($attr['sanitize']) {
                        case 'int':
                            $data[$key] = intval($value);
                            break;
                        case 'htmlentities':
                            $data[$key] = htmlentities(html_entity_decode($value));
                            break;
                    }
                } else {
                    $data[$key] = eib2bpro_clean($value);
                }
            } else {
                if (isset($attr['required']) && true === $attr['required']) {
                    if (!isset($post[$key]) || empty($post[$key])) {
                        $error[] = sprintf(esc_html__('%s is required', 'eib2bpro'), $key);
                    }
                }
            }
        }

        if (0 < count($error)) {
            echo eib2bpro_r(json_encode([
                'status' => 0,
                'message' => join('<br>', $error)
            ]));
            wp_die();
        }

        return $data;
    }
}
