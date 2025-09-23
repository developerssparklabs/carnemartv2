<?php
/**
 * There will stay HTML Field or input filed of
 * active deactive button. 
 * 
 * @author Saiful Islam <codersaiful@gmail.com>
 */

$license = get_option( WCMMQ_EDD_LICENSE_KEY );
$status  = get_option( WCMMQ_EDD_LICENSE_STATUS );
$license_data  = get_option( WCMMQ_EDD_PLUGIN_LICENSE_DATA );

if($status === 'valid' && is_object($license_data) ){
    $customer_name = $license_data->customer_name;
    ?>
<div class="license-module-parent">
    <div class="license-form-result">
    <p class="attr-alert attr-alert-success">
        <?php 
        echo sprintf( esc_html__( "Congratulations %s%s%s! Your product is activated for '%s'.", 'wcmmq_pro' ), '<b>',$customer_name,'</b>', parse_url( home_url(), PHP_URL_HOST ) );
        ?>
    </p>
    <?php
    ?>

        <div class="user-info-edd">
            <h3 class="sec-title">Your Details</h3>
            <div class="user-details">

                <div class="edd-single-user-ifno item_name">
                    <p class="field-name">Product</p>
                    <p class="field-value"><?php echo esc_html( $license_data->item_name ); ?></p>
                </div>
                
                <div class="edd-single-user-ifno customer_name">
                    <p class="field-name">Name</p>
                    <p class="field-value"><?php echo esc_html( $license_data->customer_name ); ?></p>
                </div>
                <div class="edd-single-user-ifno customer_email">
                    <p class="field-name">Email</p>
                    <p class="field-value"><?php echo esc_html( $license_data->customer_email ); ?></p>
                </div>
                <div class="edd-single-user-ifno license">
                    <p class="field-name">license</p>
                    <p class="field-value"><?php echo esc_html( $license_data->license ); ?></p>
                </div>
                <div class="edd-single-user-ifno license_limit">
                    <p class="field-name">license_limit</p>
                    <p class="field-value"><?php echo esc_html( $license_data->license_limit ); ?></p>
                </div>
                <div class="edd-single-user-ifno site_count">
                    <p class="field-name">site_count</p>
                    <p class="field-value"><?php echo esc_html( $license_data->site_count ); ?></p>
                </div>
                <div class="edd-single-user-ifno activations_left">
                    <p class="field-name">activations_left</p>
                    <p class="field-value"><?php echo esc_html( $license_data->activations_left ); ?></p>
                </div>
                <div class="edd-single-user-ifno expires">
                    <p class="field-name">expires</p>
                    <p class="field-value">
                        <?php 
                        try{
                            $dateObj = new DateTime($license_data->expires);
                            $formattedDate = $dateObj->format('j F, Y');
                            echo esc_html( $formattedDate ); 
                        }catch( Exception $eee ){
                            echo esc_html( $license_data->expires ); 
                        }
                        ?>
                    </p>
                </div>
                
                <div class="edd-single-user-ifno my-account">
                    <p class="field-name">Login</p>
                    <p class="field-value">
                        <?php
                        $payment_id = $license_data->payment_id ?? '';
                        ?>
                        <a href="https://codeastrology.com/my-account/?target_tab=purches_history&action=manage_licenses&payment_id=<?= $payment_id ?>&utm=User Details Page" target="_blank">
                            My Account (Check license/Upgrade/Manage Site)  
                        </a>
                    </p>
                </div>

            </div>
        </div>
    </div>
</div>
<?php 
}
 ?>

<div class="wcmmq-section-panel wcmmq-licnet-area" id="wcmmq-licnet-area">
    <table class="wcmmq-table universal-setting">
        <thead>
            <tr>
                <th class="wcmmq-inside">
                    <div class="wcmmq-table-header-inside">
                        <h3><?php echo esc_html__( 'License Submit', 'wcmmq' ); ?></h3>
                    </div>
                    
                </th>
                <th>
                <div class="wcmmq-table-header-right-side"></div>
                </th>
            </tr>
        </thead>

        <tbody>
            <tr>
                <td>
                    <div class="wcmmq-form-control">
                        <div class="form-label col-lg-3">
                            <?php esc_html_e( 'Enter your license key.' ); ?>
                        </div>
                        <div class="form-field col-lg-9">
                            <div class="wcmmq-license-activate-deactive">
                                <div class="col-md-7 wcmmq-license-input-box">
                                    <?php
                                    printf(
                                        '<input type="password" class="regular-text" id="' . WCMMQ_EDD_LICENSE_KEY . '" name="' . WCMMQ_EDD_LICENSE_KEY . '" value="%s" />',
                                        esc_attr( $license )
                                    );
    
                                    ?>
                                    <br>
                                </div>
                                <div class="col-md-5">
                                    <?php
                                    
                                    $button = array(
                                        'name'  => WCMMQ_EDD_LICENSE_BTN_DEACTIVATE_NAME,
                                        'label' => __( 'Deactivate License' ),
                                    );
                                    if ( 'valid' !== $status ) {
                                        $button = array(
                                            'name'  => WCMMQ_EDD_LICENSE_BTN_ACTIVATE_NAME,
                                            'label' => __( 'Activate License' ),
                                        );
                                    }
                                    wp_nonce_field( WCMMQ_EDD_LICENSE_NONCE, WCMMQ_EDD_LICENSE_NONCE );
                                    ?>
                                    <button name="<?php echo esc_attr( $button['name'] ); ?>" type="submit"
                                        class="wcmmq-btn wcmmq-btn-small wcmmq-has-icon licnse-change-button">
                                        <span><i class="wcmmq_icon-gift"></i></span>
                                        <strong class="form-submit-text">
                                        <?php echo esc_attr( $button['label'] ); ?>
                                        </strong>
                                    </button>
                                </div>

                            </div>
                        </div>
                    </div>
                </td>
                <td>
                    <div class="wcmmq-form-info">
                    <p>First, Go your <a href="https://codeastrology.com/my-account/" target="_blank"><strong>My Account</strong></a>, Login Then you will get your all plugins. Find your license key.</p>
                        <p class="license-key"><a href="<?php echo esc_url( WCMMQ_EDD_LICENCE_HELP_URL ); ?>" target="_black"><i class="wcmmq_icon-thumbs-up"></i><?php echo esc_html__( 'More Details', 'wcmmq_pro' ); ?></a></p>
                    </div> 
                </td>
            </tr>
        </tbody>
    </table>
</div>



 
