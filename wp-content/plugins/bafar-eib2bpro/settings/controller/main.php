<?php

namespace EIB2BPRO\Settings;

defined('ABSPATH') || exit;

class Main extends \EIB2BPRO\Settings
{
    public static function index()
    {
        wp_redirect(eib2bpro_admin('settings', ['section' => 'general']));
    }

    public static function need()
    {
        $active = eib2bpro_option('active', false);
        if (false === $active) {
            echo eib2bpro_view('settings', 0, 'need', array('step' => 0));
            return false;
        } else {
            $parts = explode(':', $active);
            $control = md5($parts[0] . str_replace(['http://', 'https://', 'www.'], '', get_bloginfo('url')));
            if ($active !== $parts[0] . ":" . $control . ":" . md5($control)) {
                echo eib2bpro_view('settings', 0, 'need', array('step' => 0));
                return false;
            }
        }
        return true;
    }

    public static function active()
    {

        $step = 1;
        $response = "";

        if ($_POST) {

            if (!wp_verify_nonce($_REQUEST['_wpnonce'], 'eib2bpro-security')) {
                exit;
            }

            $data = array(
                'code' => sanitize_key(trim(eib2bpro_post('code', 0))),
                'url' => str_replace(['http://', 'https://', 'www.'], '', get_bloginfo('url'))
            );
            //API URL QUITE https://en.er.gy/api/v2/activate
            $response = wp_remote_post(
                'https://gy/api/v2/activate',
                array(
                    'method' => 'POST',
                    'timeout'     => 45,
                    'body' => $data
                )
            );

            if (is_wp_error($response)) {

                $step = 2;
                $response = esc_html__('Can not connect to activation server, please try again later');
            } else {

                $result = json_decode(sanitize_text_field($response['body']), true);

                if (!is_array($result)) {
                    $step = 2;
                    $response = esc_html__('Can not retrive a valid response from activation server, please try again later');
                } else {
                    if (0 === $result['status']) {
                        $step = 2;
                        $response = sanitize_text_field($result['response']);
                    } elseif (1 === $result['status']) {
                        $step = 3;
                        $response = sanitize_text_field($result['response']);

                        if (strlen($response) === 32) {
                            $key =  md5($data['code']) . ':' . md5(md5($data['code']) . $data['url']) . ':' . sanitize_key($response);
                            eib2bpro_option('active', $key, 'set');
                            eib2bpro_option('active_code', $data['code'], 'set');
                            wp_redirect(eib2bpro_admin('settings', ['section' => 'general']));
                        }
                    }
                }
            }
        }
        echo eib2bpro_view('settings', 0, 'need', array('step' => 1, 'return' => $step, 'response' => $response));
    }
}
