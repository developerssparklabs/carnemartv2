<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
?>

<?php if (0 < count($products)) {   ?>
    <h2 class="badge badge-black badge-pill eib2bpro-Badge_Big_Title"><?php esc_html_e('Products', 'eib2bpro'); ?></h2>
    <div class="eib2bpro-List_M1" id="eib2bpro-products-1">
        <?php foreach ($products as $product) {   ?>
            <?php if ('variant' !== $product['type']) {   ?>
                <div class="btnA eib2bpro-Item collapsed" id="item_<?php echo  esc_attr($product['id']) ?>" data-toggle="collapse" data-target="#item_d_<?php echo esc_attr($product['id']) ?>" aria-expanded="false" aria-controls="item_d_<?php echo  esc_attr($product['id']) ?>">
                    <div class="liste  row d-flex align-items-center">
                        <div class="eib2bpro-Col_Image col-3 col-sm-1 align-middle">
                            <img src="<?php echo esc_url(get_the_post_thumbnail_url(esc_attr($product['id']), array(150, 150))); ?>" class="eib2bpro-Product_Image">
                        </div>
                        <div class="eib2bpro-Col_Title col-6 col-sm-5 align-middle">
                            <?php echo esc_html($product['title']) ?>
                        </div>
                        <div class="align-middle col-2">
                            <div class="eib2bpro-Price1" id="eib2bpro-Price_<?php echo esc_attr($product['id'])  ?>">
                                <?php echo str_replace("&ndash;", "", $product['price_html']); ?>
                            </div>
                        </div>
                        <div class="eib2bpro-Col_3 col-2 align-middle text-center" data-colname="Stock">
                            <div class="eib2bpro-Stocks1" id="eib2bpro-Stock_<?php echo esc_attr($product['id'])  ?>">
                                <?php if (true === $product['managing_stock']) {   ?>
                                    <?php if (0 <  intval($product['stock_quantity'])) {
                                        echo esc_html($product['stock_quantity']);
                                    } else {
                                        echo '<span class="badge badge-danger">' . esc_html__('Out of stock', 'eib2bpro') . '</span>';
                                    } ?>
                                <?php } else {  ?>
                                    <?php if (true ===  $product['in_stock']) {
                                        echo '<span class="text-mute">∞</span>';
                                    } else {
                                        echo '<span class="badge badge-danger">' . esc_html__('Out of stock', 'eib2bpro') . '</span>';
                                    } ?>
                                <?php } ?>
                            </div>
                            <div class="eib2bpro-Stocks text-left eib2bpro-Display_None">
                                <input type="text" name="qnty" data-id="<?php echo  esc_attr($product['id']) ?>" value="<?php echo esc_attr($product['stock_quantity'])  ?>" class="eib2bpro-StockAjax">
                                <br>
                                <input type="checkbox" name="unlimited" data-id="<?php echo  esc_attr($product['id']) ?>" class="eib2bpro-StockAjax" <?php if ((true !== $product['managing_stock'] && true === $product['in_stock']) or (true === $product['managing_stock'] && 9999 === $product['stock_quantity'])) echo ' checked'; ?>> <?php esc_html_e('Unlimited', 'eib2bpro'); ?><br>
                                <input type="checkbox" name="outofstock" data-id="<?php echo  esc_attr($product['id']) ?>" class="eib2bpro-StockAjax" <?php if ('1' <> $product['in_stock']) echo ' checked'; ?>> <?php esc_html_e('Out of stock', 'eib2bpro'); ?><br>
                            </div>
                        </div>
                        <div class="eib2bpro-Col_Categories eib2bpro-Col_3 col-2 align-middle" data-colname="Categories">
                            <?php
                            foreach ($product['categories'] as $category) {  ?>
                                <a href="<?php echo eib2bpro_admin('products', array('category' => $category->slug));  ?>"><?php echo esc_html($category->name) ?></a><br />
                            <?php }
                            ?> &nbsp;
                        </div>
                    </div>
                    <div class="collapse col-xs-12 col-sm-12 col-md-12 text-right" id="item_d_<?php echo  esc_attr($product['id']) ?>">
                        <div class="eib2bpro-Item_Details ">
                            <div class="containerx">
                                <div class="row">
                                    <div class="col-12 col-sm-12 text-right eib2bpro-Product_Actions">
                                        <a href="<?php echo esc_url(admin_url('post.php?post=' . esc_attr($product['id']) . '&action=edit&eib2bpro_hide')); ?>" class="eib2bpro-StopPropagation eib2bpro-panel"><?php esc_html_e('Edit product', 'eib2bpro'); ?></a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php } ?>
        <?php } ?>
    </div>

    <p>&nbsp;</p>
    <p>&nbsp;</p>

<?php } ?>