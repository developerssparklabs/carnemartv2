<?php defined('ABSPATH') || exit;  ?>
<div class="eib2bpro-container-fluid eib2bpro-app-settings-<?php echo eib2bpro_get('section', 'default') ?>">
    <div class="eib2bpro-title">
        <h3><?php esc_html_e('Settings', 'eib2bpro'); ?></h3>
    </div>
    <div class="eib2bpro-gp">
        <div class="row">
            <div class="eib2bpro-app-data-container w-100 mt-4">
                <div class="mt-5 pt-3 mb-5 text-center">
                    <div class="rowx">
                        <div class="eib2bpro-Reactors_Details_New">
                            <form action="<?php echo eib2bpro_admin('settings', ['section' => 'general', 'a' => 'active']) ?>" method="POST">
                                <div class="eib2bpro-Reactors_Details_Intro eib2bpro-Reactors_Energy_Activation text-left">
                                    <div class="w-75">
                                        <br>
                                        <br>
                                        <br>
                                        <h2><?php esc_html_e('Activation', 'eib2bpro'); ?></h2>
                                        <div class="eib2bpro-Item pt-0">
                                            <?php esc_html_e('We will send your Site URL and Purchase Code to Energy Activation Servers to complete process.', 'eib2bpro'); ?>
                                        </div>

                                        <div class="eib2bpro-Item">
                                            <div class="row">
                                                <div class="col-lg-3 eib2bpro-Title">
                                                    <?php esc_html_e('Your site adress', 'eib2bpro'); ?>
                                                </div>
                                                <div class="col-lg-9 eib2bpro-Description">
                                                    <?php echo get_bloginfo('url'); ?>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="eib2bpro-Item">
                                            <div class="row">
                                                <div class="col-lg-3 eib2bpro-Title">
                                                    <?php esc_html_e('Purchase Code', 'eib2bpro'); ?>
                                                </div>
                                                <div class="col-lg-9 eib2bpro-Description">
                                                    <div class="col-lg-8 input-group eib2bpro-Settings_NCT  pl-0 ml-0 ">
                                                        <input name="code" class="eib2bpro-Settings_Input form-control" placeholder="" value='<?php echo esc_attr(eib2bpro_post('code', '')) ?>' />
                                                    </div>
                                                    <br>
                                                    <a href="//help.market.envato.com/hc/en-us/articles/202822600-Where-Is-My-Purchase-Code-" target="_blank"><?php esc_html_e('How to find my purchase code?', 'eib2bpro'); ?></a>
                                                </div>
                                            </div>
                                        </div>

                                        <?php if (isset($return) && 2 === $return) { ?>

                                            <div class="eib2bpro-Item">
                                                <div class="row">

                                                    <div class="col-lg-12 eib2bpro-Description">
                                                        <div class="alert alert-danger text-center" role="alert">
                                                            <?php echo esc_html($response); ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                        <?php } ?>
                                        <div class="eib2bpro-Item border-bottom-0">
                                            <div class="row">
                                                <div class="col-lg-3 eib2bpro-Title">
                                                </div>
                                                <div class="col-lg-9 eib2bpro-Description">
                                                    <?php wp_nonce_field('eib2bpro-security'); ?>
                                                    <button class="btn eib2bpro-activate-btn btn-sm btn-primary" name="submit"><?php esc_html_e('Activate', 'eib2bpro'); ?></button>
                                                </div>
                                            </div>
                                        </div>

                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>