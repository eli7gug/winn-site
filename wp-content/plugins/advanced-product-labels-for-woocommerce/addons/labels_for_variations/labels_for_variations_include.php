<?php
class BeRocket_products_label_labels_for_variations_class {
    function __construct() {
        add_filter( 'wp_head', array( $this, 'load_scripts' ), 15 );

        add_action( "wp_ajax_variation_label", array ( $this, 'variation_label' ) );
        add_action( "wp_ajax_nopriv_variation_label", array ( $this, 'variation_label' ) );
        add_filter( "berocket_apl_condition_check_data", array( $this, 'add_variation' ) );
        add_filter( "berocket_advanced_label_editor_check_type_product", array( $this, 'check_cond_variation' ), 100, 3 );
    }

    public function load_scripts() {
        if ( !is_product() ) return;

        global $product;
        if ( !is_object( $product) ) $product = wc_get_product( get_the_ID() );
        if ( !$product->is_type( 'variable' ) ) return;

        wp_enqueue_script( 'berocket_label_scripts', plugins_url( 'js/frontend.js', __FILE__ ), array( 'jquery', 'berocket_tippy' ), BeRocket_products_label_version );

        wp_localize_script( 'berocket_label_scripts', 'brlabelsHelper',
            array(
                'ajax_url' => admin_url( 'admin-ajax.php' ),
            )
        );
    }

    public function variation_label() {
        $options = apply_filters( 'berocket_labels_get_base_options', false );

        $variation_id = intVal( sanitize_text_field( $_REQUEST['variation_id'] ) );
        do_action( 'berocket_apl_set_label', true, $variation_id );
        do_action( 'bd_show_timer_hook_for_variation', $options['timer_product_hook'], $variation_id );
        wp_die();
    }
    public function add_variation($args = array()) {
        if( ! empty($args['product']) && is_a($args['product'], 'WC_Product_Variation' ) ) {
            $parent = wp_get_post_parent_id($args['product_id']);
            $args['var_product_id'] = $args['product_id'];
            $args['var_product'] = $args['product'];
            $args['product_id'] = $parent;
            $args['product'] = get_product($parent);
        }
        return $args;
    }
    public function check_cond_variation($show_in, $condition, $additional) {
        $show = false;
        if( ! empty($additional['var_product_id']) ) {
            if( isset($condition['product']) && is_array($condition['product']) ) {
                $show = in_array($additional['var_product_id'], $condition['product']);
                if( ! empty($condition['additional_product']) && is_array($condition['additional_product']) ) {
                    $show = $show || in_array($additional['var_product_id'], $condition['additional_product']);
                }
                if( $condition['equal'] == 'not_equal' ) {
                    $show = ! $show;
                }
            }
        }
        return $show || $show_in;
    }
}
new BeRocket_products_label_labels_for_variations_class(); 
