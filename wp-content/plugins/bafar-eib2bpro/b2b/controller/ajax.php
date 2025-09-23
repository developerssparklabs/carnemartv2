<?php

namespace EIB2BPRO\B2b;

defined('ABSPATH') || exit;

class Ajax
{
    public static function run()
    {
        $do = eib2bpro_post('do');

        switch ($do) {

                // Qoutes

            case "edit-quote-field":
                \EIB2BPRO\B2b\Admin\Quote::edit_field();
                break;
            case "delete-quote-field":
                \EIB2BPRO\B2b\Admin\Quote::delete();
                break;

                // Offers

            case "edit-offer":
                \EIB2BPRO\B2b\Admin\Offers::edit();
                break;

            case "offers-positions":
                \EIB2BPRO\B2b\Admin\Offers::edit_positions();
                break;

            case "change-offer-status":
                \EIB2BPRO\B2b\Admin\Registration::change_post_status('offers');
                break;

            case "mail-offer":
                \EIB2BPRO\B2b\Admin\Offers::mail_offer();
                break;


                // Registration

            case "registration-positions":
                \EIB2BPRO\B2b\Admin\Registration::edit_positions();
                break;

            case "edit-registration-regtype":
                \EIB2BPRO\B2b\Admin\Registration::edit_regtype();
                break;

            case "change-regtype-status":
                \EIB2BPRO\B2b\Admin\Registration::change_post_status('regtype');
                break;

            case "change-field-status":
                \EIB2BPRO\B2b\Admin\Registration::change_post_status('fields');
                break;

            case "edit-registration-field":
                \EIB2BPRO\B2b\Admin\Registration::edit_field();
                break;

            case 'approve-user':
                \EIB2BPRO\B2b\Admin\User::approve_user();
                break;


                // B2B Groups
            case "edit-group":
                \EIB2BPRO\B2b\Admin\Groups::edit();
                break;

            case "delete-group":
                \EIB2BPRO\B2b\Admin\Groups::delete();
                break;

            case "change-group-status":
                \EIB2BPRO\B2b\Admin\Groups::change_group_status();
                break;

            case "b2b-group-details":
                \EIB2BPRO\B2b\Admin\Groups::mini_group_details();
                break;



                // Settings

            case "save-settings":
                \EIB2BPRO\B2b\Admin\Settings::save();
                break;

            case "enable":
                \EIB2BPRO\B2b\Admin\Settings::enable_features();
                break;

                // Bulk
            case "edit-bulk-category":
                \EIB2BPRO\B2b\Admin\Bulk::save_category();
                break;

                // Bulk
            case "toolbox":
                \EIB2BPRO\B2b\Admin\Toolbox::actions();
                break;

                // Others
            case "search-user":
                \EIB2BPRO\B2b\Admin\User::search();
                break;

            case "search-product":
                \EIB2BPRO\B2b\Admin\Product::search();
                break;
        }
    }

    public static function public()
    {
        $do = eib2bpro_post('do');

        switch ($do) {

            case 'offer-add-to-cart':
                \EIB2BPRO\B2b\Site\Offers::add_to_cart();
                break;

            case 'quote-form':
                \EIB2BPRO\B2b\Site\Quote::show();
                break;

            case 'quote-send':
                \EIB2BPRO\B2b\Site\Quote::save();
                break;

                // Bulk order
            case 'bulkorder-category':
                \EIB2BPRO\B2b\Site\Bulkorder::products_by_category();
                break;


            case 'bulkorder-add-to-cart':
                \EIB2BPRO\B2b\Site\Bulkorder::add_to_cart();
                break;

                // Bulk order - Layout 2

            case "search-product":
                \EIB2BPRO\B2b\Site\Bulkorder::search();
                break;

            case "bulkorder-auto-save":
                \EIB2BPRO\B2b\Site\Bulkorder::auto_save();
                break;

                // Quick Orders
            case "quickorders-save":
                \EIB2BPRO\B2b\Site\Quickorders::save();
                break;

            case "quickorders-delete":
                \EIB2BPRO\B2b\Site\Quickorders::delete();
                break;
        }
    }
}
