<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
} ?>
<div class="eib2bpro-in-page">
    <div id="heading">
        <?php esc_html_e('Notifications', 'eib2bpro'); ?>
        <a class="float-right mr-5 pr-3 pt-0 text-secondary" href="<?php echo eib2bpro_admin('core', ['section' => 'notifications', 'do' => 'settings']) ?>"><i class="ri-equalizer-fill"></i>
        </a>
    </div>
    <?php if (0 < count($notifications)) {  ?>
        <div class="container">
            <div class="notification-area">
                <ul class="notification-bar">
                    <?php foreach ($notifications as $message) {  ?>
                        <li class="eib2bpro-Notifications_Type_<?php echo esc_attr($message['type']) ?> eib2bpro-Notifications_Status_<?php echo (isset($message['status']) ? esc_attr($message['status']) : 0) ?>">
                            <div>
                                <age><?php printf(esc_html__('%s ago', 'eib2bpro'), human_time_diff(strtotime($message["time"]), current_time('timestamp'))); ?></age>
                                <header><?php echo wp_kses_post($message["title"]) ?></header>

                                <?php if ("new_order" === $message["type"]) {  // Orders
                                ?>
                                    <?php if (isset($message['details'])) {  ?>
                                        <div class="eib2bpro-Details container">
                                            <div class="row">
                                                <div class="col-6 eib2bpro-Content eib2bpro-Notifications_OrderTotal">
                                                    <h6><?php echo esc_html($message['details']['customer']) ?></h6>
                                                    <?php echo esc_html($message['details']['city']) ?>
                                                    <br>
                                                    <?php echo esc_html($message['details']['payment_method_title']) ?>

                                                </div>
                                                <div class="col-5 text-right eib2bpro-Notifications_OrderTotal">
                                                    <h4><?php echo wp_kses_post($message['details']['total']); ?></h4>
                                                    <span class="text-uppercase badge badge-pill badge-secondary badge-<?php echo esc_html($message['details']['status']) ?>"><?php echo wc_get_order_status_name($message['details']['status']) ?></span>
                                                </div>

                                            </div>
                                            <div class="row eib2bpro-Action">

                                                <ul>
                                                    <li class="text-right">
                                                        <a href="<?php echo esc_url(admin_url('post.php?post=' . intval($message['details']['order_id']) . '&action=edit&eib2bpro_hide')); ?>" class="eib2bpro-panel2 eib2bpro-Close_Before_Trig"><?php esc_html_e('View Order', 'eib2bpro') ?></a>
                                                    </li>
                                            </div>
                                        </div>
                                    <?php } ?>
                                <?php } ?>

                                <?php if ("new_comment" === $message["type"]) {  // Comments
                                ?>
                                    <?php if (isset($message['details'])) {  ?>
                                        <div class="eib2bpro-Details container">
                                            <div class="row">
                                                <div class="col-1">
                                                    <img src="<?php echo get_the_post_thumbnail_url(intval($message['details']['post_id'])); ?>" class="eib2bpro-Product_Image eib2bpro-Product_Image_Not">

                                                </div>
                                                <div class="col-10 eib2bpro-Content">
                                                    <?php $stars = intval($message['details']['star']); ?>
                                                    <div class="eib2bpro-Stars">
                                                        <span class="eib2bpro-StarsUp"><?php echo str_repeat('★ ', $stars); ?></span>
                                                        <span class="eib2bpro-StarsDown"><?php echo str_repeat('★ ', 5 - $stars); ?></span>
                                                    </div>
                                                    <?php echo esc_html($message['details']['comment_content']) ?>
                                                    <br><br>
                                                </div>
                                            </div>
                                            <div class="row eib2bpro-Action">
                                                <ul>
                                                    <li class="text-right">
                                                        <a href="<?php echo esc_url(admin_url('comment.php?action=editcomment&c=' . intval($message['details']['comment_id']))) ?>" class="eib2bpro-panel2" data-width="900px" data-force="true"><?php esc_html_e('View Comment', 'eib2bpro') ?></a>
                                                    </li>

                                                </ul>
                                            </div>
                                        </div>
                                    <?php } ?>
                                <?php } ?>


                                <?php if ("user_needs_approval" === $message["type"]) {  // user needs approval
                                ?>
                                    <?php if (isset($message['details'])) {
                                        $user_id = $message['details']['user_id']; ?>
                                        <div class="eib2bpro-Details container">
                                            <div class="row pb-3">
                                                <div class="col-12 eib2bpro-Content">
                                                    <h6>
                                                        <?php $name = get_user_meta($user_id, 'first_name', true) . ' ' . get_user_meta($user_id, 'last_name', true);
                                                        if (empty(trim($name))) {
                                                            $name = get_userdata($user_id)->user_email;
                                                        }
                                                        eib2bpro_e($name) ?>
                                                    </h6>

                                                    <?php
                                                    $move = intval(get_user_meta($user_id, 'eib2bpro_user_move_to', true));
                                                    ?>
                                                    <table class="eib2bpro-b2b-table-reg-data table w-75 table-borderless mt-3 mb-0">
                                                        <tr class="item">
                                                            <td class="td">
                                                                <?php esc_html_e('Type', 'eib2bpro'); ?>:
                                                            </td>
                                                            <td class="td">
                                                                <?php
                                                                $role = intval(get_user_meta($user_id, 'eib2bpro_registration_role', true));
                                                                if (0 < $role) {
                                                                    eib2bpro_e(get_the_title(get_user_meta($user_id, 'eib2bpro_registration_role', true)));
                                                                } else {
                                                                    esc_html_e('B2C', 'eib2bpro');
                                                                } ?>
                                                            </td>
                                                        </tr>
                                                        <?php
                                                        $field_ids = wp_parse_id_list(get_user_meta($user_id, 'eib2bpro_customfield_ids', true));
                                                        foreach ($field_ids as $field_id) {
                                                            $field = get_post($field_id);
                                                            if ($field) {
                                                                $type = get_post_meta($field->ID, 'eib2bpro_field_type', true);
                                                        ?>
                                                                <tr class="item">
                                                                    <td class="td">
                                                                        <?php eib2bpro_e(get_post_meta($field->ID, 'eib2bpro_field_label', true)) ?>:
                                                                    </td>
                                                                    <td class="td">
                                                                        <?php
                                                                        $value = get_user_meta($user_id, 'eib2bpro_customfield_' . $field->ID, true);
                                                                        switch ($type) {
                                                                            case 'file':
                                                                                $file = wp_get_attachment_url($value);
                                                                                echo '<a href="' . esc_url($file) . '" target="_blank">' . esc_html__('View or download the file', 'eib2bpro') . '<a>';
                                                                                break;
                                                                            default:
                                                                                eib2bpro_e($value);
                                                                                break;
                                                                        } ?>
                                                                    </td>
                                                                </tr>
                                                            <?php } ?>
                                                        <?php } ?>
                                                    </table>
                                                </div>
                                            </div>
                                            <div class="row eib2bpro-Action eib2bpro-html-user-id-<?php eib2bpro_a($user_id) ?>">
                                                <ul>
                                                    <li class="text-left">
                                                        <a class="eib2bpro-panel2" href="<?php echo esc_url(admin_url('user-edit.php?user_id=' . $user_id)) ?>"><?php esc_html_e('View user', 'eib2bpro'); ?></a>
                                                    </li>
                                                    <li class="text-right">
                                                        <?php if ('no' !== get_user_meta($user_id, 'eib2bpro_user_approved', true)) { ?>
                                                            <span class="text-uppercase text-success"><?php esc_html_e('Approved', 'eib2bpro'); ?></span>
                                                        <?php } else { ?>
                                                            <?php eib2bpro_ui('ajax_button', 'user_approve', 1, ['title' => esc_html__('Approve', 'eib2bpro'), 'id' => $user_id, 'app' => 'b2b', 'do' => 'approve-user', 'status' => 'approve', 'move' => $move, 'class' => 'text-success', 'confirm' => esc_html__('Are you sure?', 'eib2bpro')]); ?>
                                                            &nbsp; &nbsp;&nbsp;
                                                            <?php eib2bpro_ui('ajax_button', 'user_approve', 1, ['title' => esc_html__('Decline', 'eib2bpro'), 'id' => $user_id, 'app' => 'b2b', 'do' => 'approve-user', 'status' => 'reject', 'move' => $move, 'class' => 'text-danger', 'confirm' => esc_html__('Are you sure?', 'eib2bpro')]); ?>

                                                        <?php } ?>
                                                    </li>

                                                </ul>
                                            </div>
                                        </div>
                                    <?php } ?>
                                <?php } ?>

                                <?php if ("new_b2b_user" === $message["type"]) {  // new b2b user
                                ?>
                                    <?php if (isset($message['details'])) {
                                        $user_id = $message['details']['user_id']; ?>
                                        <div class="eib2bpro-Details container">
                                            <div class="row pb-3">
                                                <div class="col-12 eib2bpro-Content">
                                                    <h6>
                                                        <?php $name = get_user_meta($user_id, 'first_name', true) . ' ' . get_user_meta($user_id, 'last_name', true);
                                                        if (empty(trim($name))) {
                                                            $name = get_userdata($user_id)->user_email;
                                                        }
                                                        eib2bpro_e($name) ?> &nbsp;
                                                    </h6>
                                                    <?php
                                                    eib2bpro_e(\EIB2BPRO\B2b\Site\Main::user('group_name', '', $user_id));
                                                    ?>

                                                </div>
                                            </div>
                                            <div class="row eib2bpro-Action eib2bpro-html-user-id-<?php eib2bpro_a($user_id) ?>">
                                                <ul>
                                                    <li class="text-right">
                                                        <a class="eib2bpro-panel2" href="<?php echo esc_url(admin_url('user-edit.php?user_id=' . $user_id)) ?>"><?php esc_html_e('View user', 'eib2bpro'); ?></a>
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
                                    <?php } ?>
                                <?php } ?>

                                <?php if ("new_quote_request" === $message["type"]) {  // Comments
                                ?>
                                    <?php if (isset($message['details'])) {
                                        $item = get_post($message['details']['post_id']);
                                        $offered = get_post_meta($item->ID, 'eib2bpro_offered', true);
                                        $offer_id = get_post_meta($item->ID, 'eib2bpro_offer_id', true); ?>
                                        <div class="eib2bpro-Details container">
                                            <div class="row">
                                                <div class="col-12 eib2bpro-Content">
                                                    <h6 class="mb-3 mt-3">
                                                        <?php
                                                        $user_mail = '';
                                                        if (0 < ($customer_id = get_post_meta($item->ID, 'eib2bpro_customer_id', true))) {
                                                            $customer = get_userdata($customer_id);
                                                            if ($customer) {
                                                                eib2bpro_e(printf('%s %s', $customer->first_name, $customer->last_name));
                                                                $user_mail = $customer->user_email;
                                                            }
                                                        } else {
                                                            eib2bpro_e(get_post_meta($item->ID, 'eib2bpro_customer_email', true) ?: esc_html_e('Visitor', 'eib2bpro'));
                                                            $user_mail = get_post_meta($item->ID, 'eib2bpro_customer_email', true) ?: '';
                                                        }
                                                        ?>
                                                    </h6>
                                                    <div class="mt-3 w-75">
                                                        <table class="eib2bpro-b2b-table-reg-data table table-borderless mb-0">

                                                            <?php
                                                            $field_ids = wp_parse_id_list(get_post_meta($item->ID, 'eib2bpro_field_ids', true));
                                                            foreach ($field_ids as $field_id) {
                                                            ?>
                                                                <tr class="item">
                                                                    <td class="td">
                                                                        <?php eib2bpro_e(get_post_meta($item->ID, 'eib2bpro_field_' . $field_id . '_title', true)); ?></td>
                                                                    <td class="td">
                                                                        <?php $values = get_post_meta($item->ID, 'eib2bpro_field_' . $field_id, true);
                                                                        if (is_array($values)) {
                                                                            eib2bpro_e(implode(', ', $values));
                                                                        } elseif (stripos($values, '://') !== false) {
                                                                            echo '<a href="' . esc_url($values) . '" class="pl-0" target="_blank">' . esc_html__('View file', 'eib2bpro') . '</a>';
                                                                        } else {
                                                                            eib2bpro_e(get_post_meta($item->ID, 'eib2bpro_field_' . $field_id, true) ?: '-');
                                                                        } ?>
                                                                    </td>
                                                                </tr>
                                                            <?php } ?>
                                                        </table>
                                                        <div class="mt-3 w-75 pb-3">
                                                            <?php
                                                            $products = get_post_meta($item->ID, 'eib2bpro_products', true);
                                                            foreach ($products as $product_id => $product_details) {
                                                                $product = wc_get_product($product_id);
                                                                if ($product) {
                                                                    echo eib2bpro_product_image($product_id, $product_details['qty'], 'width: 50px;margin:0px');
                                                                }
                                                            } ?>
                                                            <div class="clearfix"></div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row eib2bpro-Action">
                                                <ul>

                                                    <?php
                                                    if ($offered && $offer_id && get_post($offer_id)) { ?>
                                                        <li class="text-left text-uppercase text-success">
                                                            <div class="badge badge-pill badge-success ml-0 eib2bpro-font-14">
                                                                <?php echo wc_price(get_post_meta($offer_id, 'eib2bpro_total', true)) ?>
                                                            </div>
                                                        </li>
                                                        <li class="text-right">
                                                            <a href="<?php echo eib2bpro_admin('b2b', ['quote_id' => $item->ID, 'id' => $offer_id, 'section' => 'offers', 'action' => 'edit']) ?>" class="eib2bpro-panel2" data-width="700px"><?php esc_html_e('Edit the offer', 'eib2bpro') ?></a>
                                                        </li>
                                                    <?php } else { ?>
                                                        <li class="text-right">
                                                            <a href="<?php echo eib2bpro_admin('b2b', ['quote' => $item->ID, 'id' => 0, 'section' => 'offers', 'action' => 'edit']) ?>" class="eib2bpro-panel2" data-width="700px"><?php esc_html_e('Make an offer', 'eib2bpro') ?></a>
                                                        </li>
                                                    <?php } ?>
                                                </ul>
                                            </div>

                                        <?php } ?>
                                    <?php } ?>

                                    <?php if ("11" === $message["type"]) {  // Coupons
                                    ?>
                                        <?php if (isset($message['details'])) {  ?>
                                            <div class="eib2bpro-Details container">
                                                <div class="row">
                                                    <div class="col-12 eib2bpro-Content">
                                                        <?php printf(esc_html__('Coupon <span class="badge badge-pill badge-black text-uppercase">%s</span> usage limit (%s) has been reached', 'eib2bpro'), esc_attr($message['details']['coupon_code']), intval($message['details']['usage'])) ?> <br />&nbsp;<br />
                                                    </div>
                                                </div>
                                                <div class="row eib2bpro-Action">
                                                    <ul>
                                                        <li class="text-right">
                                                            <a href="<?php echo esc_url(admin_url('post.php?post=' . intval($message['details']['coupon_id']) . '&action=edit&eib2bpro_hide')) ?>" class="trig"><?php esc_html_e('View Coupon', 'eib2bpro') ?></a>
                                                        </li>
                                                    </ul>
                                                </div>
                                            </div>
                                        <?php } ?>
                                    <?php } ?>

                                    <?php if ("12" === $message["type"]) {  ?>
                                        <div class="eib2bpro-Details container">
                                            <div class="row">
                                                <div class="col-12 eib2bpro-Details_Info_Text">
                                                    <?php echo wp_kses_post($message['details']['message']); ?>
                                                </div>
                                            </div>
                                        </div>
                                    <?php } ?>

                                    <?php if ("stock" === $message["type"]) {  // Stock
                                    ?>
                                        <?php if (isset($message['details'])) {  ?>
                                            <div class="eib2bpro-Details container">
                                                <div class="row">

                                                    <div class="col-11 eib2bpro-Content text-center">
                                                        <h2 class="eib2bpro-Widget_onlineusers_Notice">
                                                            <img src="<?php echo get_the_post_thumbnail_url(intval($message['details']['product_id'])); ?>" class="eib2bpro-Product_Image eib2bpro-Product_Image_Not eib2bpro-Notifications_Type_14x">
                                                            →
                                                            <?php echo esc_html($message['details']['qty']) ?>
                                                        </h2>

                                                    </div>
                                                </div>
                                                <div class="row eib2bpro-Action">
                                                    <ul>
                                                        <li class="text-right">
                                                            <a href="<?php echo esc_url(admin_url('post.php?action=edit&post=' . intval($message['details']['product_id']))) ?>" class="trig"><?php esc_html_e('Edit Product', 'eib2bpro') ?></a>
                                                        </li>

                                                    </ul>
                                                </div>
                                            </div>
                                        <?php } ?>
                                    <?php } ?>

                                    <?php if ("15" === $message["type"]) {  ?>
                                        <?php if (isset($message['details'])) {  ?>
                                            <div class="eib2bpro-Details container">
                                                <div class="row">


                                                    <div class="col-11 eib2bpro-Content text-center pt-2">
                                                        <?php if ('empty' !== $message['details']['icon']) { ?>
                                                            <i class="<?php echo esc_attr($message['details']['icon']) ?> eib2bpro-Notifications_Type_15x"></i>
                                                            <br> <br>
                                                        <?php } ?>

                                                        <?php echo nl2br(wp_kses_post($message['details']['content'])) ?>
                                                        <br><br>
                                                    </div>
                                                </div>

                                                <div class="row eib2bpro-Action">
                                                    <ul>
                                                        <li class="text-left">
                                                            <a href="javascript:;"><?php $created_by = get_userdata($message['details']['created_by']);
                                                                                    echo esc_html($created_by->display_name) ?></a>
                                                        </li>
                                                        <li class="text-right">
                                                            <a href="javascript:;"><?php echo date_i18n('M d, H:i', strtotime($message['time'])) ?></a>
                                                        </li>

                                                    </ul>
                                                </div>
                                            </div>
                                        <?php } ?>
                                    <?php } ?>
                                        </div>
                        </li>
                    <?php } ?>

                </ul>
            </div>
        </div>

    <?php } else { ?>
        <div class="container">
            <div class="notification-area">
                <div class="eib2bpro-EmptyTable d-flex align-items-center justify-content-center text-center mt-5 pt-5">
                    <div><br><?php esc_html_e('No notification', 'eib2bpro'); ?></div>
                </div>
            </div>
        </div>
    <?php } ?>
</div>