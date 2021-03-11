jQuery(document).ready(function($){

    //$("select.orderby").select2();
    if( /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent) ) {
        if($( "li.menu-item-has-children" ).find('button.toggle').length == 0){
            $( "li.menu-item-has-children" ).append( "<button class='toggle'></button>" );
        }
        $(document).on("click","li.menu-item-has-children .toggle",function(e){
            e.preventDefault();
            e.stopPropagation();
            $(this).closest('.menu-item-has-children').toggleClass('show-sub-menu');
            //var scroll = jQuery("#mega-menu-top_bar_nav").offset().top;
            $("ul.nav-sidebar").scrollTop(0);
            $(".mfp-content").scrollTop(0);
        });

        $(document).on("click","li.menu-item > a",function(e){
            menu_item = $(this).attr('target-id');
            document.location.href=$(this).attr('href');
            sessionStorage.setItem('visited_cat_'+ menu_item, menu_item);

        });

        for (var i = 0; i < sessionStorage.length; i++){
            session_cat = (sessionStorage.getItem(sessionStorage.key(i)));
            if (session_cat.indexOf("menu-item") >= 0){
                $('li.menu-item a[target-id='+session_cat+']').closest('.menu-item').addClass('visited_cat');
            }
      
        }





		

        // $(document).on("click",".nav-icon > a",function(e){
        //     if ($('html').hasClass('has-off-canvas')) {
        //         $('body').addClass("fixed-position");
        //      } else {
        //         $('body').removeClass("fixed-position");
        //     }
        // });


    }

    
    if( !(/Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent)) ) {
        $('select.orderby, .wpfCheckboxHier select').select2();
    }

    $('input#check-user-details').change(function(){
        if($(this).is(":checked")){
            $( ".woocommerce-shipping-fields > .shipping_address" ).css('display', 'none');
            $( "input#ship-to-different-address-checkbox" ).prop( "checked", false );
        }
    });
    $('input#ship-to-different-address-checkbox').change(function(){
        if($(this).is(":checked")){
            $( "input#check-user-details" ).prop( "checked", false );
        }
    });

    $( ".woocommerce-billing-fields__field-wrapper .woocommerce-input-wrapper > input" ).each(function( index ) {
        if ($(this).val().length ==0){
            $(this).css('display', 'none');
        }
    });

    //prevent zoom in iphone
    if(/(iPhone|iPad|iPod)\sOS\s6/.test(navigator.userAgent)) {
        document.addEventListener("touchstart", event => {
            if(event.touches.length > 1) {
                console.log("zoom plz stop");
                event.preventDefault();
                event.stopPropagation(); // maybe useless
            }
        }, {passive: false});
    }


    /**single product page js */
    $('.single-product input.qty').attr('readonly',true);
    var $singleqty = $('.single-product input.qty').val();
    if($singleqty == 0) {
        $('.single_add_to_cart_button').attr('disabled',true);
    }else{
        $('.single_add_to_cart_button').attr('disabled',false);
    }

    $(document).on("change",".pri-packs",function(){

        var $sform = $('form.cart');
        var $singleqty = $sform.find('input[name=quantity]').val();
        
        if($singleqty == 0) {
            $sform.find(".single_add_to_cart_button").attr('disabled',true);
        }else{
            $sform.find(".single_add_to_cart_button").attr('disabled',false);
        }
    });

    $(document).on("change", "input[name=quantity]", function() {
        var $sform = $(this).closest("form.cart");
       
		if($sform.has("p.confirm_add").length) {
			//$packwrap.find(".add_to_cart_button").attr("data-quantity", this.value);  //used attr instead of data, for WC 4.0 compatibility
			$sform.children("p.confirm_add").hide();
			$sform.find(".single_add_to_cart_button").addClass("blue_update_cart_winn").text(scriptParams.updatecart);
			$sform.find(".single_add_to_cart_button").show();
		}else{
			//$sform.find(".single_add_to_cart_button").attr("data-quantity", this.value);  //used attr instead of data, for WC 4.0 compatibility
        }
        
        var $singleqtyval = $(this).val();
        if($singleqtyval == 0) {
            $sform.find(".single_add_to_cart_button").attr('disabled',true);
        }else{
            $sform.find(".single_add_to_cart_button").attr('disabled',false);
        }
    });

    $(document).on("click",".single_add_to_cart_button",function(e){
        e.preventDefault();
        //$(this).attr('href','javascript:void(0)');
        console.log("here updating cart");
        var $thisbutton = $(this),
		$form = $thisbutton.closest("form.cart"),
		id = $thisbutton.val(),
		product_qty = $form.find('input[name=quantity]').val(),
		product_id = $form.find('input[name=product_id]').val() || id;
        
        $thisbutton.removeClass('added').addClass('loading');
		var data = {
			action: "nisl_woocommerce_ajax_remove_from_cart",
			product_id: product_id,
			product_sku: '',
			quantity: product_qty,
		};
       
				
        $.ajax({
            type: "post",
            url: wc_add_to_cart_params.ajax_url, //wc_add_to_cart_params.wc_ajax_url.toString().replace( '%%endpoint%%', 'remove_from_cart' )
            data: data,
            success:function(response){
                jQuery(document.body).trigger('wc_fragment_refresh');
                $(document.body).trigger("adding_to_cart", [$thisbutton, data]);

                  var data2 = {
                    action: "nisl_woocommerce_ajax_add_to_cart",
                    product_id: product_id,
                    product_sku: '',
                    quantity: product_qty,
                };
                $.ajax({
                    type: "post",
                    url: wc_add_to_cart_params.ajax_url, //wc_add_to_cart_params.wc_ajax_url.toString().replace( '%%endpoint%%', 'add_to_cart' ),
                    data: data2,
                    success:function(response){
                        $( document.body ).trigger( 'added_to_cart');
                        $thisbutton.removeClass('loading').addClass('added');
                        var qty = $form.find('input[name=quantity]').val();
                        $thisbutton.after("<p class=\'confirm_add\'>"+scriptParams.added + " " + qty + " " + scriptParams.units + "</p>");
                        $thisbutton.css("display","none");
                        jQuery(document.body).trigger('wc_fragment_refresh');
        
                    }
                });  
            }
        }); 
	});
    /*single product page js over */

    /*shop page  js*/
    $('.pack-wrapper input.qty').attr('readonly',true);
    var $packwrap = $('.pack-wrapper').siblings(".add-to-cart-button");
    console.log('starting qty '+$(this).find('input.qty').val());
    $packwrap.find(".add_to_cart_button").attr("data-quantity", $(this).find('input.qty').val() );

    var $qtyval = $packwrap.find(".add_to_cart_button").attr("data-quantity");
    if($qtyval == 0) {
        $packwrap.find(".add_to_cart_button").attr('disabled',true).prop('disabled', true);
    }else{
        $packwrap.find(".add_to_cart_button").attr('disabled',false).prop('disabled', false);
    }

    $(document).on("change",".pack-wrapper",function(){

        var $packwrap = $(this).siblings(".add-to-cart-button");
        console.log('qty '+$(this).find('input.qty').val());
        $packwrap.find(".add_to_cart_button").attr("data-quantity", $(this).find('input.qty').val() );

        var $qtyval = $packwrap.find(".add_to_cart_button").attr("data-quantity");
        if($qtyval == 0) {
            $packwrap.find(".add_to_cart_button").attr('disabled',true).prop('disabled', true);
        }else{
            $packwrap.find(".add_to_cart_button").attr('disabled',false).prop('disabled', false);
        }
    });

    $(document).on("change", "input.qty", function() {
        var $packwrap = $(this).closest(".pack-wrapper").siblings(".add-to-cart-button");
       
		if($packwrap.has("p.confirm_add").length) {
			$packwrap.find(".add_to_cart_button").show();
			$packwrap.find(".add_to_cart_button").attr("data-quantity", this.value);  //used attr instead of data, for WC 4.0 compatibility
			$packwrap.children("p.confirm_add").hide();
			$packwrap.find(".add_to_cart_button").addClass("blue_update_cart").text(scriptParams.updatecart);
		}else{
			$packwrap.find(".add_to_cart_button").attr("data-quantity", this.value);  //used attr instead of data, for WC 4.0 compatibility
        }
        
        var $qtyval = $packwrap.find(".add_to_cart_button").attr("data-quantity");
        if($qtyval == 0) {
            $packwrap.find(".add_to_cart_button").attr('disabled',true).prop('disabled', true);
        }else{
            $packwrap.find(".add_to_cart_button").attr('disabled',false).prop('disabled', false);
        }
    });
    

    $(document).on("click",".add_to_cart_button, .blue_update_cart",function(e){
        e.preventDefault();
        //$(this).attr('href','javascript:void(0)');
        console.log("here updating cart");
        var $thisbutton = $(this),
		$form = $thisbutton.closest(".price-wrapper"),
        product_pack_step = $(this).attr("data-product_pack_step"),
        product_pack_code = $(this).attr("data-product_pack_code"),
		id = $thisbutton.val(),
		product_qty = $(this).attr("data-quantity"),
		product_id = $(this).attr("data-product_id") || id;
        
        $thisbutton.removeClass('added').addClass('loading');
		var data = {
			action: "nisl_woocommerce_ajax_remove_from_cart",
			product_id: product_id,
			product_sku: '',
			quantity: product_qty,
		};
       
				
        $.ajax({
            type: "post",
            url: wc_add_to_cart_params.ajax_url, //wc_add_to_cart_params.wc_ajax_url.toString().replace( '%%endpoint%%', 'remove_from_cart' )
            data: data,
            success:function(response){
                jQuery(document.body).trigger('wc_fragment_refresh');
                $(document.body).trigger("adding_to_cart", [$thisbutton, data]);

                  var data2 = {
                    action: "nisl_woocommerce_ajax_add_to_cart",
                    product_id: product_id,
                    product_sku: '',
                    quantity: product_qty,
                    product_pack_step: product_pack_step,
                    product_pack_code: product_pack_code,
                };
                $.ajax({
                    type: "post",
                    url: wc_add_to_cart_params.ajax_url, //wc_add_to_cart_params.wc_ajax_url.toString().replace( '%%endpoint%%', 'add_to_cart' ),
                    data: data2,
                    success:function(response){
                        console.log(response);
                        $( document.body ).trigger( 'added_to_cart');
                        $thisbutton.removeClass('loading').addClass('added');
                        var qty = $thisbutton.attr("data-quantity");
                        $thisbutton.after("<p class=\'confirm_add\'>"+scriptParams.added + " " + qty + " " + scriptParams.units +" </p>");
                        $thisbutton.css("display","none");
                        jQuery(document.body).trigger('wc_fragment_refresh');
        
                    }
                });  
            }
        }); 
    });
    
    /*Shop page js over */
});
