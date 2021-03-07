<?php
class BeRocket_Labels_compat_jet_woo_widgets {
    function __construct() {
        add_action( 'init', array( $this, 'init' ) );
    }

    public function init() {
        add_filter( 'jet-woo-widgets/template-functions/product-thumbnail', array( $this, 'set_all_label'), 20);
    }
    public function set_all_label( $html = '' ) {
        ob_start();
        $BeRocket_products_label = BeRocket_products_label::getInstance();
        $BeRocket_products_label->set_all_label();
        $html_extra = ob_get_contents();
        ob_end_clean();
        return $html . $html_extra;
    }
}
new BeRocket_Labels_compat_jet_woo_widgets();
