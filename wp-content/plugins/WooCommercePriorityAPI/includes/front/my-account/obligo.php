<?php
/**
 * Created by PhpStorm.
 * User: רועי
 * Date: 05/07/2020
 * Time: 23:51
 */

class Obligo extends \PriorityAPI\API{
	private static $instance; // api instance

	public static function instance()
	{
		if (is_null(static::$instance)) {
			static::$instance = new static();
		}

		return static::$instance;
	}
	public function run()
	{
		//return is_admin() ? $this->backend(): $this->frontend();
	}

	private function __construct()
	{
		add_filter( 'woocommerce_get_item_data', [$this,'render_custom_data_on_cart_checkout'], 10, 2 );
		add_filter( 'woocommerce_add_cart_item_data',[$this,'split_product_individual_cart_items'], 10, 2 );
		add_action( 'woocommerce_before_calculate_totals', [$this,'add_custom_price']);
		add_action( 'p18a_request_front_obligo',[$this,'request_front_obligo']);
        // JS
		add_action( 'wp_ajax_my_action',[$this,'my_action']);
		add_action( 'wp_enqueue_scripts', [$this,'my_enqueue']);

		add_action('init', function() {
			add_rewrite_endpoint('obligo', EP_ROOT | EP_PAGES);
		});
		add_action( 'wp_enqueue_scripts', function(){
			wp_enqueue_script( 'my_custom_script',  P18AW_ASSET_URL . 'frontend.js',['jquery']);

			wp_localize_script( 'my_custom_script', 'ajax_object', array('ajaxurl' => admin_url( 'admin-ajax.php' )));
		});
		function my_custom_flush_rewrite_rules() {
			add_rewrite_endpoint( 'obligo', EP_ROOT | EP_PAGES );
			flush_rewrite_rules();
		}
		register_activation_hook( __FILE__, 'my_custom_flush_rewrite_rules' );
		register_deactivation_hook( __FILE__, 'my_custom_flush_rewrite_rules' );
		add_filter('woocommerce_account_menu_items', function($items) {
			$items['obligo'] = __('Obligo', 'p18w');
			return $items;
		});

		add_action('woocommerce_account_obligo_endpoint', function() {

			?>

			<div class="woocommerce-MyAccount-content-obligo">

				<p><?php _e('Obligo', 'p18w'); ?></p>
				<?php
				$foo = 2;
				do_action('p18a_request_front_obligo');?>

			</div>

			<?php

		});

		// menu manipulation
		add_filter('wp_nav_menu_items', [$this,'add_search_form'], 999, 999);
		add_action( 'woocommerce_check_cart_items', [$this,'skyverge_empty_cart_notice']);

		// update order item by cart item data
		add_action( 'woocommerce_checkout_create_order_line_item', [$this,'custom_field_update_order_item_meta'], 20, 4 );
	}
	/****** add same item with different price to cart *********/
	/*****************************************/

	public function my_enqueue() {

		wp_enqueue_script( 'ajax-script',  plugin_dir_url(__FILE__).'/my-account.js', array('jquery') );

		wp_localize_script( 'ajax-script', 'my_ajax_object',
			array( 'ajax_url' => admin_url( 'admin-ajax.php' ),
			       'woo_cart_url' => get_permalink( wc_get_page_id( 'cart' ) )
                  )
        );
	}
	public function my_action() {

		$data = $_POST['data'];
		array_shift($data);
		$response = true;
		foreach ( $data as $key => $value ) {
		        $arr= explode('#',$value['name']);
				$cart_item_data = [];
				$cart_item_data['_other_options']['product-price'] = $arr[0] ;
				$cart_item_data['_other_options']['product-ivnum'] = $arr[1] ;
				$product_id = wc_get_product_id_by_sku('PAYMENT');
				$cart           = WC()->cart->add_to_cart( $product_id, 1, null, null, $cart_item_data );
                if(!$cart){
                    $response = false;
                }
		}
		if($response){
			WC()->session->set(
				'session_vars',
				array(
					'ordertype' => 'Recipe'));
        }
		$data = [$response];
		wp_send_json_success($data);

		wp_die(); // this is required to terminate immediately and return a proper response
	}
	function split_product_individual_cart_items( $cart_item_data, $product_id ){
		if(isset($_POST['obligoSubmit'])){
	    $unique_cart_item_key = uniqid();
		$cart_item_data['unique_key'] = $unique_cart_item_key;
		//$cart_item_data['_other_options']['product-price'] = rand(1,22) ;
		}
		return $cart_item_data;
	}
	function add_custom_price( $cart_object ) {
		foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
			if(isset($cart_item['_other_options'])){
				$custom_price = $cart_item['_other_options']['product-price']; // This will be your custom price
				$cart_item['data']->set_price($custom_price);
            }
		}
	}
	function render_custom_data_on_cart_checkout( $cart_data, $cart_item = null ) {
		$custom_items = array();
		/* Woo 2.4.2 updates */
		if( !empty( $cart_data ) ) {
			$custom_items = $cart_data;
		}
		if( isset( $cart_item['_other_options']['product-ivnum'] ) ) {
			$custom_items[] = array( "name" => "IVNUM", "value" => $cart_item['_other_options']['product-ivnum'] );
		}
		return $custom_items;
	}
	function request_front_obligo() {
	$current_user             = wp_get_current_user();
	$priority_customer_number = get_user_meta( $current_user->ID, 'priority_customer_number', true );
	$additionalurl = 'OBLIGO?&$expand=OBLIGO_FNCITEMS_SUBFORM&$filter=CUSTNAME eq \'' . $priority_customer_number . '\'';
	$args= [];
	$response = $this->makeRequest( "GET", $additionalurl, $args, true );
	$data     = json_decode( $response['body'] );	
	if ( ! empty( $data->value ) ) {
		echo "<table>";
		foreach ( $data->value[0] as $key => $value ) {
			if ( $key == 'OBLIGO_FNCITEMS_SUBFORM' || $key == 'PROJ_DEBIT' || $key == 'CUST' ) {
				continue;
			}
			echo "<tr>";
			echo "<td>" .__($key,'p18w')."</td><td>".__($value,'p18w')."</td>";
			echo "</tr>";
		}
		echo "</table>";
       // if(isset($data->value[0]->OBLIGO_FNCITEMS_SUBFORM)) {
		echo "<form id='simply-obligo' action='' method='post'>";

		echo '<input type="hidden" name="action" value="my_action_obligo" />';
		echo "<button type='submit' name='obligoSubmit' id='obligoSubmit' style='float: right;' disabled>".__('Open Payment','p18w')."</button>";

		echo "<table> <tr>";
		echo "<th></th><th>".esc_html__('BALDATE','p18w')."</th> <th>".esc_html__('FNCNUM','p18w')."</th> <th>".esc_html__('IVNUM','p18w')."</th> <th>".esc_html__('DETAILS','p18w')."</th> <th>".esc_html__('SUM1','p18w')."</th>";
		echo "</tr>";
		global $woocommerce;
		$items     = $woocommerce->cart->get_cart();
		$cartcheck = empty( $items ) ? '' : 'disabled';
		$i         = 1;
		foreach ( $data->value[0]->OBLIGO_FNCITEMS_SUBFORM as $key => $value ) {
			echo "<tr>";
			$arr = array( 'sum' => $value->SUM1, 'ivnum' => $value->IVNUM );
			echo '<td><input type="checkbox" name="'.$value->SUM1 .'#'.$value->IVNUM.'" class="obligo_checkbox" data-sum=' . $value->SUM1 . ' data-IVNUM=' . $value->IVNUM . ' $cartcheck value="obligo_chk_sum' . $i . '"></td>';
			//echo "<input type='hidden'name='obligo_chk_sum" . $i . "' value='" . $value->SUM1 . "'>";
			//echo "<input type='hidden'name='obligo_chk_ivnum" . $i . "' value='" . $value->IVNUM . "'>";

			//echo "<input type='hidden' name='obligo_chk_sum[]' value='" . $value->SUM1 . "'>";
			//echo "<input type='hidden' name='obligo_chk_ivnum[]' value='" . $value->IVNUM . "'>";

			foreach ( $value as $Fkey => $Fvalue ) {
				if ( $Fkey == 'BALDATE' || $Fkey == 'FNCNUM' || $Fkey == 'IVNUM' || $Fkey == 'DETAILS' || $Fkey == 'SUM1' ) {
					if ( $Fkey == 'BALDATE' ) {
						$timestamp = strtotime( $Fvalue );
						echo "<td>" . date( 'd/m/y', $timestamp ) . "</td>";
					} else {
						echo "<td>" . $Fvalue . "</td>";
					}
				}
			}
			echo "</tr>";
			$i ++;
		}
		echo "</table>";
		echo "</form>";
//}
	}
	return 'Recipet opened...';
}

	function add_search_form($items, $args) {
		$session = WC()->session->get('session_vars');
        if($session['ordertype']=='Recipe'){
	        $items .= '<li class="menu-item">'
	                  . '<p><span style="color: #0000ff;"><em>Recipe</em></span></p>'

	                  . '</li>';
        }
		return $items;
	}

	function skyverge_empty_cart_notice() {

		if ( WC()->cart->get_cart_contents_count() == 0 ) {
			WC()->session->set(
				'session_vars',
				array(
					'ordertype' => ''));
		}

	}
	function custom_field_update_order_item_meta( $item, $cart_item_key, $values, $order ) {
		if ( ! isset( $values['_other_options'] ) )
			return;

		$custom_data = $values['_other_options'];
		$ivnum = $custom_data['product-ivnum'];
		if ( $ivnum )
			$item->update_meta_data( __('product-ivnum'), $ivnum );

		//return $cart_item_data;
	}
}