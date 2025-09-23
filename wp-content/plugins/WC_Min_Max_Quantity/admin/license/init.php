<?php
namespace WC_MMQ_PRO\Admin\License;

if ( ! class_exists( 'CodeAstrology_Plugin_Updater' ) ) {
	// load our custom updater
	include dirname( __FILE__ ) . '/resource/updater.php';
}
include dirname( __FILE__ ) . '/settings.php';
// use WC_MMQ\Admin\Page_Loader;
class Init
{

    protected $status;

    /**
     * We have added new class 
     * in our main and free version.
     * 
     * which we will store to this property
     * 
     * and based on this Object,
     * we will do manything.
     * such: load topbar and new page style.
     * 
     * @author Saiful Islam <codersaiful@gmail.com>
     *
     * @var object
     */
    public $page_loader;

    /**
     * based on page_loader
     * we have to assign title, actually 
     * it's compolsury in topbar file.
     * 
     * jehetu pages/topbar.php file amra ekhan theke load korchi
     * ebong sei file a $this->topbar_sub_title use kora hoyeche.
     * tai ei khaneo ei object a dite hobe.
     * noile undefined error asbe.
     *
     * @var string|null
     */
    public $topbar_sub_title;

    /**
     * ei property tao lagbe topbar file.
     * r jeyetu ei file open/execute hocche, tar mane pro achei;
     * ei jonno eta amra by default define kore diyechi.
     *
     * @var boolean
     */
    public $is_pro = true;
    

    public function __construct()
    {
        
        
        $this->status = get_option( WCMMQ_EDD_LICENSE_STATUS );
        add_action('init', [$this, 'plugin_updater']);

        //Menu and Page Settings
        add_action('admin_init', [$this, 'register_option']);
        add_action( 'admin_menu', [$this, 'license_menu'] );

        //Activate and Deactivate License.
        add_action('admin_init', [$this, 'activate_license']);
        add_action('admin_init', [$this, 'deactivate_license']);

        //Notice Handle
        add_action('admin_notices', [$this, 'notice_activation_status']);
        if($this->status !== 'valid'){
            add_action('admin_notices', [$this, 'notice_to_activate']);
        }

        return 'licence_init';
    }

    /**
     * Registers the license key setting in the options table.
     *
     * @return void
     */
    function register_option()
    {
        register_setting(WCMMQ_EDD_FORM_REGISTER_SETTING, WCMMQ_EDD_LICENSE_KEY, [$this, 'sanitize_license']);
    }    

    /**
     * Adds the plugin license page to the admin menu.
     *
     * @return void
     */
    function license_menu() {
        if( ! class_exists('\WC_MMQ\Admin\Page_Loader')){
            add_submenu_page(
                WCMMQ_EDD_PARENT_MENU,
                __( WCMMQ_EDD_LICENSE_PAGE_TITLE ),
                __( WCMMQ_EDD_LICENSE_PAGE_TITLE ),
                'manage_options',
                WCMMQ_EDD_LICENSE_PAGE,
                [$this, 'license_page']
            );
        }
        

        
    }    
    function license_page() {

        if( class_exists('\WC_MMQ\Admin\Page_Loader')){
            $this->page_loader = new \WC_MMQ\Admin\Page_Loader();
        }

        if( $this->page_loader ){
            $this->topbar_sub_title = __( 'Manage license and update' );
            include $this->page_loader->topbar_file;
        }

        add_settings_section(
            WCMMQ_EDD_FORM_REGISTER_SETTING,
            __( WCMMQ_EDD_LICENSE_PAGE_TITLE ),
            [$this, 'license_key_settings_field'],
            WCMMQ_EDD_LICENSE_PAGE
        );
        
        ?>
        <div class="wrap wcmmq_wrap wcmmq-content">
            <h1 class="wp-heading "></h1>
            <div class="fieldwrap">
                <?php if( ! $this->page_loader ){  ?>
                    <h2><?php esc_html_e( WCMMQ_EDD_ITEM_NAME . ' ' . WCMMQ_EDD_LICENSE_PAGE_TITLE ); ?></h2>
                <?php } ?>
                
                <form method="post" action="options.php" class="wcmmq-licnse-form">
                
                    <?php
                    
                    do_settings_sections( WCMMQ_EDD_LICENSE_PAGE );
                    settings_fields( WCMMQ_EDD_FORM_REGISTER_SETTING );
                    
                    ?>
                    <div class="wcmmq-section-panel no-background wcmmq-full-form-submit-wrapper">
                        <?php 
                        /**
                         * ekhane submit_button() function ta chilo
                         * ami seta remove korechi ebong 
                         * manually amar markup onuzay button diyechi
                         * ebong perfectly kajoO koroche.
                         */
                        // submit_button(); 
                        ?>

                        
                        <button name="submit" type="submit"
                            class="wcmmq-btn wcmmq-has-icon configure_submit">
                            <span><i class="wcmmq_icon-floppy"></i></span>
                            <strong class="form-submit-text">
                            <?php echo esc_html__('Save Change','wcmmq');?>
                            </strong>
                        </button>
                        
                    </div>
                </form>

            </div>
            
        </div><!-- ./wrap wcmmq_wrap wcmmq-content -->
        <?php
    }


    /**
     * Outputs the license key settings field.
     *
     * @return void
     */
    function license_key_settings_field() {
        include dirname( __FILE__ ) . '/view/html-page.php';
    }



    /**
     * Activates the license key.
     *
     * @return void
     */
    function activate_license()
    {


        // listen for our activate button to be clicked
        if (!isset($_POST[WCMMQ_EDD_LICENSE_BTN_ACTIVATE_NAME])) {
            return;
        }

        // run a quick security check
        if (!check_admin_referer(WCMMQ_EDD_LICENSE_NONCE, WCMMQ_EDD_LICENSE_NONCE)) {
            return; // get out if we didn't click the Activate button
        }

        // retrieve the license from the database
        $license = trim(get_option(WCMMQ_EDD_LICENSE_KEY));
        if (!$license) {
            $license = !empty($_POST[WCMMQ_EDD_LICENSE_KEY]) ? sanitize_text_field($_POST[WCMMQ_EDD_LICENSE_KEY]) : '';
        }
        if (!$license) {
            return;
        }

        // data to send in our API request
        $api_params = array(
            'edd_action'  => 'activate_license',
            'license'     => $license,
            'item_id'     => WCMMQ_EDD_ITEM_ID,
            'item_name'   => rawurlencode(WCMMQ_EDD_ITEM_NAME), // the name of our product in EDD
            'url'         => home_url(),
            'environment' => function_exists('wp_get_environment_type') ? wp_get_environment_type() : 'production',
        );

        // Call the custom API.
        $response = wp_remote_post(
            WCMMQ_EDD_STORE_URL,
            array(
                'timeout'   => 15,
                'sslverify' => false,
                'body'      => $api_params,
            )
        );


        // make sure the response came back okay
        if (is_wp_error($response) || 200 !== wp_remote_retrieve_response_code($response)) {

            if (is_wp_error($response)) {
                $message = $response->get_error_message();
            } else {
                $message = __('An error occurred, please try again.');
            }
        } else {




            $license_data = json_decode(wp_remote_retrieve_body($response));

            if (false === $license_data->success) {

                switch ($license_data->error) {

                    case 'expired':
                        $message = sprintf(
                            /* translators: the license key expiration date */
                            __('Your license key expired on %s.', 'bbbaa'),
                            date_i18n(get_option('date_format'), strtotime($license_data->expires, current_time('timestamp')))
                        );
                        break;

                    case 'disabled':
                    case 'revoked':
                        $message = __('Your license key has been disabled.', 'bbbaa');
                        break;

                    case 'missing':
                        $message = __('Invalid license.', 'bbbaa');
                        break;

                    case 'invalid':
                    case 'site_inactive':
                        $message = __('Your license is not active for this URL.', 'bbbaa');
                        break;

                    case 'item_name_mismatch':
                        /* translators: the plugin name */
                        $message = sprintf(__('This appears to be an invalid license key for %s.', 'bbbaa'), WCMMQ_EDD_ITEM_NAME);
                        break;

                    case 'no_activations_left':
                        $message = __('Your license key has reached its activation limit.', 'bbbaa');
                        break;

                    default:
                        $message = __('An error occurred, please try again.', 'bbbaa');
                        break;
                }
            }
        }

        // Check if anything passed on a message constituting a failure
        if (! empty($message)) {
            $redirect = add_query_arg(
                array(
                    'page'          => WCMMQ_EDD_LICENSE_PAGE,
                    'sl_activation' => 'false',
                    'message'       => rawurlencode($message),
                ),
                WCMMQ_EDD_LICENSE_PAGE_LINK//admin_url('plugins.php')
            );

            wp_safe_redirect($redirect);
            exit();
        }

        // $license_data->license will be either "valid" or "invalid"
        if ('valid' === $license_data->license) {
            update_option(WCMMQ_EDD_LICENSE_KEY, $license);
        }
        update_option(WCMMQ_EDD_LICENSE_STATUS, $license_data->license);
        update_option(WCMMQ_EDD_PLUGIN_LICENSE_DATA, $license_data);

        // $redirect = add_query_arg(
        //     array(
        //         'page'          => WCMMQ_EDD_LICENSE_PAGE,
        //         'sl_activation' => 'true',
        //         'barta'       => rawurlencode( $message ),
        //     ),
        //     WCMMQ_EDD_LICENSE_PAGE_LINK
        // );

        // wp_safe_redirect( $redirect );
        // exit();
        // // wp_safe_redirect(admin_url('plugins.php?page=' . WCMMQ_EDD_LICENSE_PAGE));
        // exit();
    }

    /**
     * Deactivates the license key.
     * This will decrease the site count.
     *
     * @return void
     */
    function deactivate_license()
    {

        // var_dump('bbbbbwpt-edd',$_POST);
        // listen for our activate button to be clicked
        if (isset($_POST[WCMMQ_EDD_LICENSE_BTN_DEACTIVATE_NAME])) {

            // run a quick security check
            if (!check_admin_referer(WCMMQ_EDD_LICENSE_NONCE, WCMMQ_EDD_LICENSE_NONCE)) {
                return; // get out if we didn't click the Activate button
            }

            // retrieve the license from the database
            $license = trim(get_option(WCMMQ_EDD_LICENSE_KEY));

            // data to send in our API request
            $api_params = array(
                'edd_action'  => 'deactivate_license',
                'license'     => $license,
                'item_id'     => WCMMQ_EDD_ITEM_ID,
                'item_name'   => rawurlencode(WCMMQ_EDD_ITEM_NAME), // the name of our product in EDD
                'url'         => home_url(),
                'environment' => function_exists('wp_get_environment_type') ? wp_get_environment_type() : 'production',
            );

            // Call the custom API.
            $response = wp_remote_post(
                WCMMQ_EDD_STORE_URL,
                array(
                    'timeout'   => 15,
                    'sslverify' => false,
                    'body'      => $api_params,
                )
            );
            // var_dump($response);

            // make sure the response came back okay
            if (is_wp_error($response) || 200 !== wp_remote_retrieve_response_code($response)) {

                if (is_wp_error($response)) {
                    $message = $response->get_error_message();
                } else {
                    $message = __('An error occurred, please try again.');
                }

                $redirect = add_query_arg(
                    array(
                        'page'          => WCMMQ_EDD_LICENSE_PAGE,
                        'sl_activation' => 'false',
                        'message'       => rawurlencode($message),
                    ),
                    WCMMQ_EDD_LICENSE_PAGE_LINK
                );

                wp_safe_redirect($redirect);
                exit();
            }

            // decode the license data
            $license_data = json_decode(wp_remote_retrieve_body($response));

            // $license_data->license will be either "deactivated" or "failed"
            if ('deactivated' === $license_data->license) {
                delete_option(WCMMQ_EDD_LICENSE_STATUS);
            }

            delete_option(WCMMQ_EDD_PLUGIN_LICENSE_DATA);
            return;
            
            exit();
        }
    }



    /**
     * Sanitizes the license key.
     *
     * @param string  $new The license key.
     * @return string
     */
    function sanitize_license($new)
    {
        $old = get_option(WCMMQ_EDD_LICENSE_KEY);
        if ($old && $old !== $new) {
            delete_option(WCMMQ_EDD_LICENSE_STATUS); // new license has been entered, so must reactivate
        }

        return sanitize_text_field($new);
    }


    /**
     * Initialize the updater. Hooked into `init` to work with the
     * wp_version_check cron job, which allows auto-updates.
     */
    public function plugin_updater()
    {

        // To support auto-updates, this needs to run during the wp_version_check cron job for privileged users.
        $doing_cron = defined('DOING_CRON') && DOING_CRON;
        if (!current_user_can('manage_options') && !$doing_cron) {
            return;
        }

        // retrieve our license key from the DB
        $license_key = trim(get_option(WCMMQ_EDD_LICENSE_KEY));

        // setup the updater
        $edd_updater = new \CodeAstrology_Plugin_Updater(
            WCMMQ_EDD_STORE_URL,
            WCMMQ_EDD_PLUGIN_ROOT__FILE__, //__FILE__,
            array(
                'version' => WCMMQ_EDD_CURRENT_VERSION,                    // current version number
                'license' => $license_key,             // license key (used get_option above to retrieve from DB)
                'item_id' => WCMMQ_EDD_ITEM_ID,       // ID of the product
                'author'  => WCMMQ_EDD_AUTHOR_NAME,
                'beta'    => true,
            )
        );
        // var_dump($edd_updater);
    }


    /**
     * this function added by saiful.
     * 
     * asole eta kothao use hoyni apatot.
     * zodi kono karone status ba license data dekhar proyojon hoy, tokhon
     * eta use kora jabe. multo age thekei chilo seta theke ami eta custom abniyechi.
     * 
     * @author Saiful Islam <codersaiful@gmail.com>
     * 
     * //Previous comment:
     * Checks if a license key is still valid.
     * The updater does this for you, so this is only needed if you want
     * to do somemthing custom.
     *
     * @return object|mixed
     */
    function get_license_data()
    {
        $license = trim(get_option(WCMMQ_EDD_LICENSE_KEY));

        // var_dump($license);
        $api_params = array(
            'edd_action'  => 'check_license',
            'license'     => $license,
            'item_id'     => WCMMQ_EDD_ITEM_ID,
            'item_name'   => rawurlencode(WCMMQ_EDD_ITEM_NAME),
            'url'         => home_url(),
            'environment' => function_exists('wp_get_environment_type') ? wp_get_environment_type() : 'production',
        );

        // Call the custom API.
        $response = wp_remote_post(
            WCMMQ_EDD_STORE_URL,
            array(
                'timeout'   => 15,
                'sslverify' => false,
                'body'      => $api_params,
            )
        );

        if (is_wp_error($response)) {
            return false;
        }

        $license_data = json_decode(wp_remote_retrieve_body($response));

        return $license_data;
    }

    /**
     * It's Live status, If you call this method,
     * this method will check again manually
     * 
     * @author Saiful Islam <codersaiful@gmail.com>
     * 
     * This method customised by Saiful
     * 
     * **************************
     * Checks if a license key is still valid.
     * The updater does this for you, so this is only needed if you want
     * to do somemthing custom.
     *
     * @return string Example: valid or invalid
     */
    function get_live_license_status()
    {


        $license_data = $this->get_license_data();
        if ('valid' === $license_data->license) {
            return 'valid';
        } else {
            return 'invalid';
        }
    }

    /**
     * This is a means of catching errors from the activation method above and displaying it to the customer
     */
    function notice_activation_status()
    {
        if (isset($_GET['sl_activation']) && !empty($_GET['message'])) {
            $message = urldecode($_GET['message'] ?? '');
            switch ($_GET['sl_activation']) {

                case 'false':
                    
                ?>
                    <div class="error">
                        <p><?php echo wp_kses_post($message); ?></p>
                    </div>
                <?php
                    break;

                case 'true':
                default:

                ?>
                    <div id="message" class="updated notice notice-success">
                        <p><?php echo wp_kses_post($message); ?></p>
                        <p>Submitted.</p>
                    </div>
                <?php
                    // Developers can put a custom success message here for when activation is successful if they way.
                    break;
            }
        }
    }

    public function notice_to_activate()
    {

        $link_label = __( 'Activate License', 'wcmmq_pro' );
        $link = WCMMQ_EDD_LICENSE_PAGE_LINK;
		$message = esc_html__( 'Please activate ', 'wcmmq_pro' ) . '<strong>' . esc_html__( WCMMQ_EDD_ITEM_NAME ) . '</strong>' . esc_html__( ' license to get automatic updates.', 'wcmmq_pro' ) . '</strong>';
        printf( '<div class="error error-warning is-dismissible"><p>%1$s <a href="%2$s">%3$s</a></p></div>', $message, $link, $link_label );
    }
}
