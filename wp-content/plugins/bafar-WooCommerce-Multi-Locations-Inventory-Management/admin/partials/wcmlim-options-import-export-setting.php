<?php

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       http://www.techspawn.com
 * @since      1.0.0
 *
 * @package    Wcmlim
 * @subpackage Wcmlim/admin/partials
 */
if (isset($_POST['wcmlim_export_submit'])) {
    global $wpdb;
    $wcmlim_options = $wpdb->get_results("SELECT option_name, option_value FROM $wpdb->options WHERE option_name LIKE 'wcmlim%'");

    // Check if there are options to export
    if (!empty($wcmlim_options)) {
       
        // Create CSV file in the uploads folder
        $upload_dir = wp_upload_dir();
        $filename = $upload_dir['basedir'] . '/wcmlim_options.csv';
        
        // Open the file in write mode
        $fp = fopen($filename, 'w');

        // Write CSV header
        fputcsv($fp, array('Option Name', 'Option Value'));

        // Write data to CSV file (only 'option_name' and 'option_value')
        foreach ($wcmlim_options as $option) {
            fputcsv($fp, array($option->option_name, $option->option_value));
        }

        // Close the file
        fclose($fp);

        $upload_dir = wp_upload_dir();
        $filename = $upload_dir['basedir'] . '/wcmlim_options.csv';
        //get url of csv file
        $filenameurl = $upload_dir['baseurl'] . '/wcmlim_options.csv';
        //redirect to csv file url
        header("Location: " . $filenameurl);
        // Exit to prevent any further output
        exit;
    } else {
        // No options to export, handle accordingly (e.g., display a message)
        echo 'No options to export.';
        // exit;
    }
}
?>
<div class="wrap">
    <?php
    if (get_option('wcmlim_license') == '' || get_option('wcmlim_license') == 'invalid') {
    ?>
        <script>
            window.location.href = "?page=multi-location-inventory-management";
        </script>
    <?php
    }
    ?>
    <h1><?php esc_html_e('WooCommerce Multi Locations Inventory Management', 'wcmlim'); ?></h1>
    <?php settings_errors(); ?>
    <form method="POST">
    <ul class="nav nav-tabs">
        <li class="wcmlim-admin-menu-tab-li active"><a href="#tab-1"><?php esc_html_e('List All Options', 'wcmlim'); ?></a></li>
        <li class="wcmlim-admin-menu-tab-li wcmlim-admin-menu-tab-li-right"><button type="submit" name="wcmlim_export_submit" class="button button-primary"><?php esc_html_e('Export', 'wcmlim'); ?></button></li>
        <li class="wcmlim-admin-menu-tab-li wcmlim-admin-menu-tab-li-right"><button type="button" name="wcmlim_import_submit" class="button button-primary wcmlim_import_modal"><?php esc_html_e('Import', 'wcmlim'); ?></button></li>
    </ul>
</form>
    <div class="tab-content" id="import_export">
        <div id="tab-1" class="tab-pane active">
            <div class="wcmlim_flex_container">
                <div class="wcmlim_flex_box">
                   <table>
                        <tr>
                            <th><?php esc_html_e('Option Name', 'wcmlim'); ?></th>
                            <th><?php esc_html_e('Option Value', 'wcmlim'); ?></th>
                        </tr>
                        <?php
                        //get all option from wp_options table where option_name like wcmlim%
                        global $wpdb;
                        $wcmlim_options = $wpdb->get_results("SELECT option_name, option_value FROM $wpdb->options WHERE option_name LIKE 'wcmlim%'");
                        foreach ($wcmlim_options as $key => $value) {
                        ?>
                            <tr>
                                <td class="wcmlim_option_name"><?php echo $value->option_name; ?></td>
                                <td><?php echo $value->option_value; ?></td>
                            </tr>
                        <?php
                        }
                        //when click on export button then export all option from wp_options table where option_name like wcmlim%
                        
                        
                        ?>
                   </table>
                </div>
                <!-- Design Preview @since 1.1.5 -->
            </div>
        </div>
    </div>
</div>
<div id="wcmlim_import_modal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <h2><?php esc_html_e('Import Options', 'wcmlim'); ?></h2>
        <form method="post" action="" enctype="multipart/form-data">
            <input type="file" name="wcmlim_import_file" id="wcmlim_import_file" />
            <input type="submit" name="wcmlim_import_submit" id="wcmlim_import_submit" class="button button-primary" value="<?php esc_html_e('Import', 'wcmlim'); ?>" />
        </form>
        <?php
        //when click on import button then import all option from csv file
        if (isset($_POST['wcmlim_import_submit'])) {
            // Check if a file was selected for upload
            if (isset($_FILES['wcmlim_import_file']) && $_FILES['wcmlim_import_file']['error'] == 0) {
                $file = $_FILES['wcmlim_import_file']['tmp_name'];
                
                // Check if the file was opened successfully
                $handle = fopen($file, "r");
                if ($handle !== false) {
                    $c = 0;
        
                    while (($filesop = fgetcsv($handle, 1000, ",")) !== false) {
                        // Assuming the CSV contains 'option_name' and 'option_value' columns
                        if (isset($filesop[0]) && isset($filesop[1])) {
                            $option_name = sanitize_text_field($filesop[0]);
                            $option_value = sanitize_text_field($filesop[1]);
        
                            // Perform your database update (example: insert or update)
                            // Example using $wpdb:
                            $wpdb->replace(
                                $wpdb->options,
                                array(
                                    'option_name' => $option_name,
                                    'option_value' => $option_value,
                                ),
                                array('%s', '%s')
                            );
        
                            $c = $c + 1;
                        }
                    }
        
                    // Close the CSV file
                    fclose($handle);
        
                    // Provide a success or failure message
                    if ($c > 0) {
                        echo '<span style="color:green;">' . $c . ' options imported successfully</span>';
                        echo '<script>alert("'.$c.' options imported successfully");</script>';
                        echo '<script>location.reload();</script>';
                    } else {
                        echo '<span style="color:red;">No valid options found in the CSV file</span>';
                    }
                } else {
                    // Handle file open error
                    echo '<span style="color:red;">Error opening the CSV file</span>';
                }
            } else {
                // Handle file upload error
                echo '<span style="color:red;">Error uploading the file</span>';
            }
        }
        
        ?>
    </div>
</div>
<script>
    //remove _ from the .wcmlim_flex_box table tr td
    jQuery(document).ready(function() {
        //add disable attribute to submit button
        jQuery('#wcmlim_import_submit').attr('disabled', 'disabled');
        jQuery('#wcmlim_import_submit').css('cursor', 'not-allowed');
        
        jQuery('.wcmlim_option_name').each(function() {
            var option_name = jQuery(this).text();
            option_name = option_name.replace(/_/g, ' ');
            jQuery(this).text(option_name);
            var option_name = jQuery(this).text();
            option_name = option_name.charAt(0).toUpperCase() + option_name.slice(1);
            jQuery(this).text(option_name);
        });
        // import modal code start
        jQuery('.wcmlim_import_modal').click(function(e) {
            e.preventDefault();
            jQuery('#wcmlim_import_modal').css('display', 'block');
        });
        //close modal
        jQuery('.close').click(function() {
            jQuery('#wcmlim_import_modal').css('display', 'none');
        });
        jQuery(window).click(function(e) {
            if (e.target == document.getElementById('wcmlim_import_modal')) {
                jQuery('#wcmlim_import_modal').css('display', 'none');
            }
        });
        //check if file is uploaded or not uptill then disable submit button
        jQuery('#wcmlim_import_file').change(function() {
            jQuery('#wcmlim_import_submit').removeAttr('disabled');
            jQuery('#wcmlim_import_submit').css('cursor', 'pointer');
        });
        jQuery('#wcmlim_import_modal').css('background', 'rgba(0,0,0,0.5)');
        // import modal code end

        //accepct only csv file
        jQuery('#wcmlim_import_file').change(function() {
            var ext = jQuery(this).val().split('.').pop().toLowerCase();
            if (jQuery.inArray(ext, ['csv']) == -1) {
                jQuery('#wcmlim_import_submit').attr('disabled', 'disabled');
                jQuery('#wcmlim_import_submit').css('cursor', 'not-allowed');
                jQuery('#wcmlim_import_submit').after('<span style="color:#000000;">Please select csv file only</span>');
            } else {
                jQuery('#wcmlim_import_submit').removeAttr('disabled');
                jQuery('#wcmlim_import_submit').css('cursor', 'pointer');
                jQuery('#wcmlim_import_submit').next('span').remove();
            }
        });
    });
</script>