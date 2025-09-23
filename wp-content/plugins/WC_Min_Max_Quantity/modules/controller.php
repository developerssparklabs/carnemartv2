<?php 
namespace WC_MMQ_PRO\Modules;

class Controller
{
    public static function modules_arr( $args )
    {

        $new_args = array(
            'cart-page-condition' => array(
                'key'   => 'cart-page-condition',
                'name'  =>  __( 'Cart Page Condition', 'wcmmq_pro' ),
                'desc'  =>  __( 'Extra control on cart page. Such: total price amout, total product count amount etc.', 'wcmmq_pro' ),
                'status'=>  'on',
                'dir'   =>  __DIR__,
            ),
            'variation-qty-count' => array(
                'key'   => 'variation-qty-count',
                'name'  =>  __( 'Variation Quanity and Count Control', 'wcmmq_pro' ),
                'desc'  =>  __( 'Mainly for Variation Product and control count of variation and total of variation cart items.', 'wcmmq_pro' ),
                'status'=>  'on',
                'dir'   =>  __DIR__,
            ),
            
            'variation-options' => array(
                'key'   => 'variation-options',
                'name'  =>  __( 'Min Max Control for Variation', 'wcmmq_pro' ),
                'desc'  =>  __( 'Control enable/disable for Variable product variation.', 'wcmmq_pro' ),
                'status'=>  'on',
                'dir'   =>  __DIR__,
            ),

        );

        return array_merge( $args, $new_args );
    }

    public static function run()
    {
        add_filter( 'wcmmq_module_item', [__CLASS__, 'modules_arr'] );
    }
}