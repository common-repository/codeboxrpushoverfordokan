<?php

include_once( ABSPATH . 'wp-admin/includes/plugin.php' );

define('CBPD_WC_PUSHOVER_DIR', plugin_dir_path(__FILE__));

class Codeboxrpushoverfordokan{

    const VERSION               = '1.0.8';
    protected $plugin_slug      = 'codeboxrpushoverfordokan';
    protected static $instance  = null;
    /**
     * Initialize the plugin by setting localization and loading public scripts
     * and styles.
     *
     * @since     1.0.0
     */
    private function __construct(){

	    //require_once( plugin_dir_path( __FILE__ ) . '../admin/class-wc-codeboxrpushoverfordokan.php' );

        if (!class_exists('CBPDPushover_Api'))
            include_once('class-cbpdpushover-api.php');

        add_action('init', array($this, 'load_plugin_textdomain'));
        add_action('wpmu_new_blog', array($this, 'activate_new_site'));



	    //for dokan plugin
        //add_action('dokan_settings_form_bottom', array($this, 'dokan_settings_form_bottom'), '', 2);
        //add_action('dokan_store_profile_saved', array($this, 'dokan_store_profile_saved'), '', 2);

	    //adding setting menu as per plugin version 2.3
        if ( defined( 'DOKAN_PLUGIN_VERSION' ) && DOKAN_PLUGIN_VERSION > '2.3'  ) {
            add_filter( 'dokan_get_dashboard_settings_nav', array( $this, 'register_pushover_settings_menu' ) );
            add_filter( 'dokan_dashboard_settings_heading_title', array( $this, 'load_settings_pushover_header' ), 10, 2 );
            add_filter( 'dokan_dashboard_settings_helper_text', array( $this, 'load_settings_pushover_helper_text' ), 10, 2 );
            add_action( 'dokan_render_settings_content', array( $this, 'load_settings_pushover_content' ), 10 );
        } else {
            add_filter( 'dokan_get_dashboard_settings_nav', array( $this, 'register_pushover_menu' ) );
            add_action( 'dokan_settings_template', array( $this, 'register_pushover_menu_set_templates' ), 10, 2 );
        }

	    //for push notification
        add_action('woocommerce_thankyou', array($this, 'codeboxrpushoverfordokan_notify_new_order'));
        add_action('woocommerce_product_on_backorder', array($this, 'codeboxrpushoverfordokan_notify_backorder'));
        add_action('woocommerce_notify_no_stock', array($this, 'codeboxrpushoverfordokan_notify_no_stock'));
        add_action('woocommerce_notify_low_stock', array($this, 'codeboxrpushoverfordokan_notify_low_stock'));



	    // ajax for pushover form setting save
	    add_action("wp_ajax_pushover_settings", array( $this,"cb_ajax_pushover_settings"));
	    add_action("wp_ajax_nopriv_pushover_settings", array( $this,"cb_ajax_pushover_settings"));

        // ajax for test notification
        add_action("wp_ajax_cbpushovertest", array( $this,"cb_ajax_cbpushovertest"));
        add_action("wp_ajax_nopriv_cbpushovertest", array( $this,"cb_ajax_cbpushovertest"));



    }



	public function cb_ajax_pushover_settings(){
		$dokan_pushover_settings = Dokan_Pushover_Settings::init();
		$dokan_pushover_settings->ajax_settings();
		/*
		if ( ! dokan_is_user_seller( get_current_user_id() ) ) {
			wp_send_json_error( __( 'Are you cheating?', 'dokan' ) );
		}

		//$_POST['dokan_update_profile'] = '';

		switch( $_POST['form_id'] ) {
			case 'pushover-form':
				if ( !wp_verify_nonce( $_POST['_wpnonce'], 'dokan_pushover_settings_nonce' ) ) {
					wp_send_json_error( __( 'Are you cheating?', 'dokan' ) );
				}
				//$ajax_validate =  $this->profile_validate();
				break;

		}

		//if ( is_wp_error( $ajax_validate ) ) {
		//	wp_send_json_error( $ajax_validate->errors );
		//}

		// we are good to go
		$save_data = $this->cb_ajax_pushover_settings_process();

		$progress_bar = dokan_get_profile_progressbar();
		$success_msg = __( 'Your information has been saved successfully', 'dokan' ) ;

		$data = array(
			'progress' => $progress_bar,
			'msg'      => $success_msg,
		);

		wp_send_json_success( $data );
		*/
	}

	/*

	public function cb_ajax_pushover_settings_process(){
		$store_id            = get_current_user_id();
		$prev_dokan_settings = get_user_meta( $store_id, 'dokan_profile_settings', true );

		if ( wp_verify_nonce( $_POST['_wpnonce'], 'dokan_pushover_settings_nonce' ) ) {

			// update profile settings info
			$cbpd_info         = $_POST['pushovernotifiction'];



			//$social_fields  = dokan_get_social_profile_fields();

			$dokan_settings['pushovernotifiction']['enable']             = isset($cbpd_info['enable']) ? $cbpd_info['enable'] : '0';
			$dokan_settings['pushovernotifiction']['userapitoken']       = isset($cbpd_info['userapitoken']) ? $cbpd_info['userapitoken'] : '';
			$dokan_settings['pushovernotifiction']['device']             = isset($cbpd_info['device']) ? $cbpd_info['device'] : '';
			$dokan_settings['pushovernotifiction']['neworder']           = isset($cbpd_info['neworder']) ? $cbpd_info['neworder']: '0';
			$dokan_settings['pushovernotifiction']['freeorder']          = isset($cbpd_info['freeorder'])? $cbpd_info['freeorder']: '0';
			$dokan_settings['pushovernotifiction']['backorder']          = isset($cbpd_info['backorder'])? $cbpd_info['backorder']: '0';
			$dokan_settings['pushovernotifiction']['nostock']            = isset($cbpd_info['nostock']) ? $cbpd_info['nostock']: '0';
			$dokan_settings['pushovernotifiction']['lowstock']           = isset($cbpd_info['lowstock']) ? $cbpd_info['lowstock']: '0';



		}

		$dokan_settings = array_merge($prev_dokan_settings, $dokan_settings);

		//$profile_completeness = $this->calculate_profile_completeness_value( $dokan_settings );
		//$dokan_settings['profile_completion'] = $profile_completeness;

		update_user_meta( $store_id, 'dokan_profile_settings', $dokan_settings );

		do_action( 'dokan_store_profile_saved', $store_id, $dokan_settings );

		if ( ! defined( 'DOING_AJAX' ) ) {
			$_GET['message'] = 'profile_saved';
		}
	}
	*/

    /**
     * function cb_ajax_cbpushovertest
     * called from user test pushover
     */
    public function cb_ajax_cbpushovertest(){

	    $nonce = $_REQUEST['nonce'];
	    if ( ! wp_verify_nonce( $nonce, 'codeboxrpushoverfordokan' ) ) {
		    // This nonce is not valid.
		    echo __('Security check, Please refresh browser.');
		    die( );
	    }

	    //$dokanpush_admin_setting = new WC_Codeboxrpushoverfordokan();
	    $siteapi = dokan_get_option( 'codeboxrpushoverfordokan_site_api', 'dokan_pushover', '' );
	    $debug = dokan_get_option( 'codeboxrpushoverfordokan_debug', 'dokan_pushover', 'no' );

	    //$siteapi = $dokanpush_admin_setting->codeboxrpushoverfordokan_site_api;
	    //$debug   = $dokanpush_admin_setting->codeboxrpushoverfordokan_debug;

	    //var_dump('debug='.$debug);

        //if( isset( $_POST['cbsiteapi']) && $_POST['cbsiteapi'] != '' && isset( $_POST['cbuserapi']) && $_POST['cbuserapi'] != '' ) {
        if( isset( $_POST['cbuserapi']) && $_POST['cbuserapi'] != '' && $siteapi != '') {



            //$cburl         = get_admin_url();
	        //set the test url to the user's setting's pushovernotification page
            $cburl         = dokan_get_navigation_url( 'settings/pushovernotifiction' );


            $cbtitle       = __('Test Pushover for Dokan ','codeboxrpushoverfordokan');
            $cbmsg         = __('This is a test pushover notification for Dokan plugin','codeboxrpushoverfordokan');

            self::cbdp_send_notification( $siteapi , $_POST['cbuserapi'] ,  $_POST['cbdevice']  , $cbtitle , $cbmsg , $cburl ,$debug );
            echo '1';
        }
        else{
            echo __('Enter Api to Test ','codeboxrpushoverfordokan');
        }

        die();
    }


    /**
     * notify_backorder
     *
     * Send notification when new order is received
     *
     * @access public
     *
     * @param $args
     *
     * @return void
     */
    function codeboxrpushoverfordokan_notify_backorder($args){

        $product         = $args['product'];
        $order_id        = $args['order_id'];
        $title           = sprintf(__('Product Backorder', 'codeboxrpushoverfordokan'), $order_id);
        $message         = sprintf(__('Product (#%d %s) is on backorder.', 'codeboxrpushoverfordokan'), $product->id, $product->get_title());
        $seller_id       = get_post_field('post_author', $product->id);
        $cbpdseller_info = get_user_meta((int)$seller_id, 'dokan_profile_settings', true);

        if (!empty($cbpdseller_info) && isset($cbpdseller_info['pushovernotifiction'])) {
            $cbpdseller_pushoverinfo = $cbpdseller_info['pushovernotifiction'];

            if (is_array($cbpdseller_pushoverinfo) && !empty($cbpdseller_pushoverinfo)) {

                if (isset($cbpdseller_pushoverinfo['enable']) && $cbpdseller_pushoverinfo['enable'] == '1' && isset($cbpdseller_pushoverinfo['backorder']) && $cbpdseller_pushoverinfo['backorder'] == '1') {

	                /*
	                $dokanpush_admin_setting = new WC_Codeboxrpushoverfordokan();

	                $siteapi = $dokanpush_admin_setting->codeboxrpushoverfordokan_site_api;
	                $debug   = $dokanpush_admin_setting->codeboxrpushoverfordokan_debug;
	                */

	                $siteapi = dokan_get_option( 'codeboxrpushoverfordokan_site_api', 'dokan_pushover', '' );
	                $debug = dokan_get_option( 'codeboxrpushoverfordokan_debug', 'dokan_pushover', 'no' );

                    //$url     = get_admin_url();
	                //$url         = wp_nonce_url( add_query_arg( array( 'order_id' => $order_id ), dokan_get_navigation_url( 'orders' ) ), 'dokan_view_order' ) ;
	                $url    = dokan_edit_product_url($product->id);

                    //$siteapi = isset($cbpdseller_pushoverinfo['siteapitoken']) ? $cbpdseller_pushoverinfo['siteapitoken'] : '';
                    $userapi = isset($cbpdseller_pushoverinfo['userapitoken']) ? $cbpdseller_pushoverinfo['userapitoken'] : '';
                    $device  = isset($cbpdseller_pushoverinfo['device']) ? $cbpdseller_pushoverinfo['device'] : '';
                    //$debug   = isset($cbpdseller_pushoverinfo['debug']) ? $cbpdseller_pushoverinfo['debug'] : '';
                    self::cbdp_send_notification($siteapi, $userapi, $device, $title, $message, $url , $debug);
                }
            }
        }

    }

    /**
     * notify_no_stock
     *
     * Send notification when new order is received
     *
     * @access public
     *
     * @param WC_Product $product
     *
     * @return void
     */
    function codeboxrpushoverfordokan_notify_no_stock(WC_Product $product){

        // get order details

        $title           = __('Product Low Stock', 'codeboxrpushoverfordokan');
        $message         = sprintf(__('Product %s %s now out of stock.', 'codeboxrpushoverfordokan'), $product->id, $product->get_title());
        $seller_id       = get_post_field('post_author', $product->id);
        $cbpdseller_info = get_user_meta((int)$seller_id, 'dokan_profile_settings', true);

        //if (!empty($cbpdseller_info) && array_key_exists('setting_cbpdpushup', $cbpdseller_info)) {
	    if (!empty($cbpdseller_info) && isset($cbpdseller_info['pushovernotifiction'])) {
            $cbpdseller_pushoverinfo = $cbpdseller_info['pushovernotifiction'];

            if (is_array($cbpdseller_pushoverinfo) && !empty($cbpdseller_pushoverinfo)) {

                if (isset($cbpdseller_pushoverinfo['enable']) && $cbpdseller_pushoverinfo['enable'] == '1' && isset($cbpdseller_pushoverinfo['nostock']) && $cbpdseller_pushoverinfo['nostock'] == '1') {

	                /*

	                $dokanpush_admin_setting = new WC_Codeboxrpushoverfordokan();

	                $siteapi = $dokanpush_admin_setting->codeboxrpushoverfordokan_site_api;
	                $debug   = $dokanpush_admin_setting->codeboxrpushoverfordokan_debug;
					*/

	                $siteapi = dokan_get_option( 'codeboxrpushoverfordokan_site_api', 'dokan_pushover', '' );
	                $debug = dokan_get_option( 'codeboxrpushoverfordokan_debug', 'dokan_pushover', 'no' );

                    //$url     = get_admin_url();
	                //$url     = dokan_get_page_url( 'my_orders' );
	                $url    = dokan_edit_product_url($product->id);
	                //$url         = wp_nonce_url( add_query_arg( array( 'order_id' => $order_id ), dokan_get_navigation_url( 'orders' ) ), 'dokan_view_order' ) ;
                    //$siteapi = isset($cbpdseller_pushoverinfo['siteapitoken']) ? $cbpdseller_pushoverinfo['siteapitoken'] : '';
                    $userapi = isset($cbpdseller_pushoverinfo['userapitoken']) ? $cbpdseller_pushoverinfo['userapitoken'] : '';
                    $device  = isset($cbpdseller_pushoverinfo['device']) ? $cbpdseller_pushoverinfo['device'] : '';
                    //$debug   = isset($cbpdseller_pushoverinfo['debug']) ? $cbpdseller_pushoverinfo['debug'] : '';

	                self::cbdp_send_notification($siteapi, $userapi, $device, $title, $message, $url , $debug);
                }
            }
        }

    }

    /**
     * notify_low_stock
     *
     * Send notification when new order is received
     *
     * @access public
     *
     * @param WC_Product $product
     *
     * @return void
     */
    function codeboxrpushoverfordokan_notify_low_stock(WC_Product $product){

        // get order details
        $title           = __('Product Low Stock', 'codeboxrpushoverfordokan');
        $message         = sprintf(__('Product %s %s now has low stock.', 'codeboxrpushoverfordokan'), $product->id, $product->get_title());
        $seller_id       = get_post_field('post_author', $product->id);
        $cbpdseller_info = get_user_meta((int)$seller_id, 'dokan_profile_settings', true);

        if (!empty($cbpdseller_info) && array_key_exists('pushovernotifiction', $cbpdseller_info)) {
            $cbpdseller_pushoverinfo = $cbpdseller_info['pushovernotifiction'];

            if (is_array($cbpdseller_pushoverinfo) && !empty($cbpdseller_pushoverinfo)) {

                if (isset($cbpdseller_pushoverinfo['enable']) && $cbpdseller_pushoverinfo['enable'] == '1' && isset($cbpdseller_pushoverinfo['lowstock']) && $cbpdseller_pushoverinfo['lowstock'] == '1') {
	                /*

	                $dokanpush_admin_setting = new WC_Codeboxrpushoverfordokan();

	                $siteapi = $dokanpush_admin_setting->codeboxrpushoverfordokan_site_api;
	                $debug   = $dokanpush_admin_setting->codeboxrpushoverfordokan_debug;
	                */

	                $siteapi = dokan_get_option( 'codeboxrpushoverfordokan_site_api', 'dokan_pushover', '' );
	                $debug = dokan_get_option( 'codeboxrpushoverfordokan_debug', 'dokan_pushover', 'no' );

                    //$url        = get_admin_url();
	                //$url     = dokan_get_page_url( 'my_orders' );
	                $url    = dokan_edit_product_url($product->id);
                    //$siteapi    = isset($cbpdseller_pushoverinfo['siteapitoken']) ? $cbpdseller_pushoverinfo['siteapitoken'] : '';
                    $userapi    = isset($cbpdseller_pushoverinfo['userapitoken']) ? $cbpdseller_pushoverinfo['userapitoken'] : '';
                    $device     = isset($cbpdseller_pushoverinfo['device']) ? $cbpdseller_pushoverinfo['device'] : '';
                    //$debug      = isset($cbpdseller_pushoverinfo['debug']) ? $cbpdseller_pushoverinfo['debug'] : '';

	                self::cbdp_send_notification($siteapi, $userapi, $device, $title, $message, $url , $debug);
                }
            }
        }

    }//end codeboxrpushoverfordokan_notify_low_stock

    /**
     * @param $order_id
     * main function to send notification to all seller of order products when new order
     */
    public function codeboxrpushoverfordokan_notify_new_order($order_id)
    {

        global $wpdb;

	    /*
	    $dokanpush_admin_setting = new WC_Codeboxrpushoverfordokan();

	    $siteapi = $dokanpush_admin_setting->codeboxrpushoverfordokan_site_api;
	    $debug   = $dokanpush_admin_setting->codeboxrpushoverfordokan_debug;
	    */

	    $siteapi    = dokan_get_option( 'codeboxrpushoverfordokan_site_api', 'dokan_pushover', '' );
	    $debug      = dokan_get_option( 'codeboxrpushoverfordokan_debug', 'dokan_pushover', 'no' );


        $cbpdorder    = new WC_Order($order_id);
        $sub_orders = get_children( array(
            'post_parent' => $cbpdorder->id,
            'post_type'   => 'shop_order',
            'post_status' => array( 'wc-pending', 'wc-completed', 'wc-processing', 'wc-on-hold' )
        ) );

        if ( is_array($sub_orders) && count($sub_orders) > 0 ) {

            foreach($sub_orders as $sub_order){

                $cbpdorder          = new WC_Order($sub_order->ID);
                $cbpd_items         = $cbpdorder->get_items();
                $cbpdseller_id      =(int) get_post_field('post_author', $sub_order->ID);
                $cbpdseller_info    = get_user_meta($cbpdseller_id, 'dokan_profile_settings', true);
                $cbpd_product_list  = '';
                $cbxcount = 0;
                //create item list
                foreach ($cbpd_items as $seller_product) {

                    $cbxcount++;
                    $cbxcomma = ',';
                    if($cbxcount == (int)count($cbpd_items)){
                        $cbxcomma = '';
                     }
                    $productobj         = new WC_Product($seller_product['product_id']);
                    $cbpd_product_list  .= $seller_product['name'] . '(' . $seller_product['qty'] . ') -' . self::cbpd_get_currency_symbol() . $productobj->price .$cbxcomma;

                }
                if (!empty($cbpdseller_info) && isset($cbpdseller_info['pushovernotifiction'])) {

                    $cbpdseller_pushoverinfo = $cbpdseller_info['pushovernotifiction'];

                    if (is_array($cbpdseller_pushoverinfo) && !empty($cbpdseller_pushoverinfo)) {

                        if (isset($cbpdseller_pushoverinfo['enable']) && $cbpdseller_pushoverinfo['enable'] == '1' && isset($cbpdseller_pushoverinfo['neworder']) && $cbpdseller_pushoverinfo['neworder'] == '1') {

                            if (0 < absint($cbpdorder->order_total) || (isset($cbpdseller_pushoverinfo['freeorder']) && $cbpdseller_pushoverinfo['freeorder'] == '1')) {


                                $cbpdtitle   = sprintf(__('New Order #%d (%s%d) ', 'codeboxrpushoverfordokan'), $sub_order->ID , self::cbpd_get_currency_symbol() , $cbpdorder->order_total);

                                $cbpdmessage = sprintf(
                                    __('%1$s ordered %2$s %3$s', 'codeboxrpushoverfordokan'),
                                    $cbpdorder->billing_first_name . " " . $cbpdorder->billing_last_name, '(' .self::cbpd_get_currency_symbol() .$cbpdorder->order_total .')',
                                    $cbpd_product_list

                                );



                                //$url         = get_admin_url();
	                            $url         = wp_nonce_url( add_query_arg( array( 'order_id' => $order_id ), dokan_get_navigation_url( 'orders' ) ), 'dokan_view_order' ) ;

                                //$siteapi     = isset($cbpdseller_pushoverinfo['siteapitoken']) ? $cbpdseller_pushoverinfo['siteapitoken'] : '';
                                $userapi     = isset($cbpdseller_pushoverinfo['userapitoken']) ? $cbpdseller_pushoverinfo['userapitoken'] : '';
                                $device      = isset($cbpdseller_pushoverinfo['device']) ? $cbpdseller_pushoverinfo['device'] : '';
                                //$debug       = isset($cbpdseller_pushoverinfo['debug']) ? $cbpdseller_pushoverinfo['debug'] : '';

	                            self::cbdp_send_notification($siteapi, $userapi, $device, $cbpdtitle, $cbpdmessage, $url , $debug);
                            }
                            // if total amount match
                        }
                        // if enable
                    }
                    // if info pushover is a array
                }

            }// end of foreach
        }// end of is suborders
        else{

                $cbpd_items   = $cbpdorder->get_items();
                $cbpd_sellers = array();

               foreach ($cbpd_items as $item) {
                   $seller_id                  = get_post_field('post_author', $item['product_id']);
                   $cbpd_sellers[$seller_id][] = $item;
               }

               foreach ($cbpd_sellers as $index => $seller_products) {

                   $cbpdseller_id      = (int)$index;
                   $cbpdseller_info    = get_user_meta($cbpdseller_id, 'dokan_profile_settings', true);
                   $cbpd_product_list  = '';
                   $cbxcount           = 0;

                   foreach ($seller_products as $seller_product) {

                       $cbxcount++;
                       $cbxcomma = ',';
                       if($cbxcount == (int)count($cbpd_items)){
                           $cbxcomma = '';
                       }
                       $productobj         = new WC_Product($seller_product['product_id']);
                       $cbpd_product_list  .= $seller_product['name'] . '(' . $seller_product['qty'] . ') -' . self::cbpd_get_currency_symbol() . $productobj->price .$cbxcomma;

                   }

                   if (!empty($cbpdseller_info) && isset($cbpdseller_info['pushovernotifiction']) ) {

                       $cbpdseller_pushoverinfo = $cbpdseller_info['pushovernotifiction'];

                       if (is_array($cbpdseller_pushoverinfo) && !empty($cbpdseller_pushoverinfo)) {

                           if (isset($cbpdseller_pushoverinfo['enable']) && $cbpdseller_pushoverinfo['enable'] == '1' && isset($cbpdseller_pushoverinfo['neworder']) && $cbpdseller_pushoverinfo['neworder'] == '1') {

                               if (0 < absint($cbpdorder->order_total) || (isset($cbpdseller_pushoverinfo['freeorder']) && $cbpdseller_pushoverinfo['freeorder'] == '1')) {


                                   $cbpdtitle   = sprintf(__('New Order #%d (%s%d) ', 'codeboxrpushoverfordokan'), $cbpdorder->id , self::cbpd_get_currency_symbol() , $cbpdorder->order_total);

                                   $cbpdmessage = sprintf(
                                       __('%1$s ordered %2$s %3$s', 'codeboxrpushoverfordokan'),
                                       $cbpdorder->billing_first_name . " " . $cbpdorder->billing_last_name, '(' .self::cbpd_get_currency_symbol() .$cbpdorder->order_total .')',
                                       $cbpd_product_list

                                   );

	                               //$url         = get_admin_url();
	                               $url         = wp_nonce_url( add_query_arg( array( 'order_id' => $order_id ), dokan_get_navigation_url( 'orders' ) ), 'dokan_view_order' ) ;
                                   //$siteapi     = isset($cbpdseller_pushoverinfo['siteapitoken']) ? $cbpdseller_pushoverinfo['siteapitoken'] : '';
                                   $userapi     = isset($cbpdseller_pushoverinfo['userapitoken']) ? $cbpdseller_pushoverinfo['userapitoken'] : '';
                                   $device      = isset($cbpdseller_pushoverinfo['device']) ? $cbpdseller_pushoverinfo['device'] : '';
                                   //$debug       = isset($cbpdseller_pushoverinfo['debug']) ? $cbpdseller_pushoverinfo['debug'] : '';

                                   self::cbdp_send_notification($siteapi, $userapi, $device, $cbpdtitle, $cbpdmessage, $url , $debug);
                               }
                               // if total amount match
                           }
                           // if enable
                       }
                       // if info pushover is a array
                   }
                   // end is not empty settings of user
               }
               // end foreach seller
        }


    }// end of function


	/**
	 * Register Pushover notification menu to dokan setting
	 *
	 * @param $urls
	 *
	 * @return mixed array
	 */
	public function register_pushover_menu($urls){
        if ( dokan_is_seller_enabled(get_current_user_id()) ) {
    		$urls['pushovernotifiction'] = array(
    			'title' => __( 'Push Notification', 'codeboxrpushoverfordokan' ),
    			'icon'  => '<i class="fa fa-wifi"></i>',
    			'url'   => dokan_get_navigation_url( 'settings/pushovernotifiction' ),
    		);
        }
		return $urls;
	}

    /**
     * Register Pushover notification menu to dokan setting
     *
     * @param $urls
     *
     * @return mixed array
     */
    public function register_pushover_settings_menu($urls){

        if ( dokan_is_seller_enabled(get_current_user_id()) ) {
            $urls['pushovernotifiction'] = array(
                'title' => __( 'Push Notification', 'codeboxrpushoverfordokan' ),
                'icon'  => '<i class="fa fa-wifi"></i>',
                'url'   => dokan_get_navigation_url( 'settings/pushovernotifiction' ),
                'pos'   => 130,
            );
        }

        return $urls;
    }

    /**
     * Load Settings Pushover notification Header
     *
     * @param  string $header
     * @param  string $query_vars
     *
     * @return string
     */
    public function load_settings_pushover_header( $header, $query_vars ) {

        if ( $query_vars == 'pushovernotifiction' ) {
            $header = __( 'Pushover Settings', 'codeboxrpushoverfordokan' );
        }

        return $header;
    }

    /**
     * Load Settings page helper
     *
     * @param  string $help_text
     * @param  array $query_vars
     *
     * @return string
     */
    public function load_settings_pushover_helper_text( $help_text, $query_vars ) {

        if ( $query_vars == 'pushovernotifiction' ) {
            $help_text = __( 'Pushover makes it easy to send real-time notifications to your Android and iOS devices.', 'codeboxrpushoverfordokan' );
        }

        return $help_text;
    }

    public function load_settings_pushover_content( $query_vars ) {
        if ( dokan_is_seller_enabled( get_current_user_id() ) ) {
            if ( isset( $query_vars['settings'] ) && $query_vars['settings'] == 'pushovernotifiction' ) {
                wp_enqueue_style($this->plugin_slug . '-plugin-styles', plugins_url('assets/css/codeboxrpushoverfordokan_public.css', __FILE__), array(), self::VERSION);
                $nonce = wp_create_nonce( 'codeboxrpushoverfordokan' );
                wp_enqueue_script($this->plugin_slug . '-plugin-script', plugins_url('assets/js/codeboxrpushoverfordokan_public.js', __FILE__), array('jquery'), self::VERSION);
                wp_localize_script( $this->plugin_slug . '-plugin-script', 'cbpushovertest', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ), 'nonce' => $nonce));

                include_once CODEBOXR_PUSHOVER_DIR . '/public/pushover-form.php';

            }
        }
    }



	public  function register_pushover_menu_set_templates($path, $part ){
		if ( $part == 'pushovernotifiction' ) {

			//var_dump($this);
			//add_action('wp_enqueue_scripts', array($this, 'enqueue_styles'));
			//add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));

			wp_enqueue_style($this->plugin_slug . '-plugin-styles', plugins_url('assets/css/codeboxrpushoverfordokan_public.css', __FILE__), array(), self::VERSION);
			$nonce = wp_create_nonce( 'codeboxrpushoverfordokan' );
			wp_enqueue_script($this->plugin_slug . '-plugin-script', plugins_url('assets/js/codeboxrpushoverfordokan_public.js', __FILE__), array('jquery'), self::VERSION);
			wp_localize_script( $this->plugin_slug . '-plugin-script', 'cbpushovertest', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ), 'nonce' => $nonce));

			return CODEBOXR_PUSHOVER_DIR . '/public/form.php';
		}

		return $path;
	}






    /**
     * Return the plugin slug.
     *
     * @since    1.0.0
     *
     * @return    Plugin slug variable.
     */
    public function get_plugin_slug()
    {
        return $this->plugin_slug;
    }

    /**
     * Return an instance of this class.
     *
     * @since     1.0.0
     *
     * @return    object    A single instance of this class.
     */
    public static function get_instance()
    {

        // If the single instance hasn't been set, set it now.
        if (null == self::$instance) {
            self::$instance = new self;
        }

        return self::$instance;
    }

    /**
     * Fired when the plugin is activated.
     *
     * @param    boolean $network_wide       True if WPMU superadmin uses
     *                                       "Network Activate" action, false if
     *                                       WPMU is disabled or plugin is
     *                                       activated on an individual blog.
     */
    public static function activate($network_wide)
    {

        if (function_exists('is_multisite') && is_multisite()) {

            if ($network_wide) {

                // Get all blog ids
                $blog_ids = self::get_blog_ids();

                foreach ($blog_ids as $blog_id) {

                    switch_to_blog($blog_id);
                    self::single_activate();
                }

                restore_current_blog();

            } else {
                self::single_activate();
            }

        } else {
            self::single_activate();
        }
        // Verify WooCommerce is installed and active
        $active_plugins = (array)get_option('active_plugins', array());

        if (is_multisite())
            $active_plugins = array_merge($active_plugins, get_site_option('active_sitewide_plugins', array()));

        //if (!(in_array('woocommerce/woocommerce.php', $active_plugins) || array_key_exists('woocommerce/woocommerce.php', $active_plugins))) {
	    if ( !class_exists('WooCommerce') ) {
            deactivate_plugins(basename(__FILE__));
            wp_die("This plugin requires WooCommerce to be installed and active.");
        }

	    //if ( !is_plugin_active( 'dokan/dokan.php' ) ) {
	    if ( !class_exists('WeDevs_Dokan') ) {  //change is made suggested by Tareq wedevs (main developer of dokan plugin)
		    //plugin is activated
		    deactivate_plugins(basename(__FILE__));
		    wp_die("This plugin requires Dokan to be installed and active.");
	    }

	    //check if dokan plugin is installed

        // verify that SimpleXML library is available
        if (!function_exists('simplexml_load_string')) {
            deactivate_plugins(basename(__FILE__));
            wp_die("Sorry, but you can't run this plugin, it requires the SimpleXML library installed on your server/hosting to function.");
        }


    }

    /**
     * Fired when the plugin is deactivated.
     *
     * @since    1.0.0
     *
     * @param    boolean $network_wide       True if WPMU superadmin uses
     *                                       "Network Deactivate" action, false if
     *                                       WPMU is disabled or plugin is
     *                                       deactivated on an individual blog.
     */
    public static function deactivate($network_wide)
    {

        if (function_exists('is_multisite') && is_multisite()) {

            if ($network_wide) {

                // Get all blog ids
                $blog_ids = self::get_blog_ids();

                foreach ($blog_ids as $blog_id) {

                    switch_to_blog($blog_id);
                    self::single_deactivate();

                }

                restore_current_blog();

            } else {
                self::single_deactivate();
            }

        } else {
            self::single_deactivate();
        }

    }

    /**
     * Fired when a new site is activated with a WPMU environment.
     *
     * @since    1.0.0
     *
     * @param    int $blog_id ID of the new blog.
     */
    public function activate_new_site($blog_id)
    {

        if (1 !== did_action('wpmu_new_blog')) {
            return;
        }

        switch_to_blog($blog_id);
        self::single_activate();
        restore_current_blog();

    }

    /**
     * function single activate fired when plugin is deactivated
     */
    public static function single_deactivate()
    {

    }

    /**
     * function single activate fired when plugin is activated
     */
    public static function single_activate()
    {

    }

    /**
     * Get all blog ids of blogs in the current network that are:
     * - not archived
     * - not spam
     * - not deleted
     *
     * @since    1.0.0
     *
     * @return   array|false    The blog ids, false if no matches.
     */
    private static function get_blog_ids()
    {

        global $wpdb;

        // get an array of blog ids
        $sql = "SELECT blog_id FROM $wpdb->blogs
			    WHERE archived = '0' AND spam = '0'
			    AND deleted = '0'";

        return $wpdb->get_col($sql);

    }


    /**
     * Load the plugin text domain for translation.
     *
     * @since    1.0.0
     */
    public function load_plugin_textdomain()
    {
        $domain = $this->plugin_slug;

        load_plugin_textdomain( $domain, false, dirname( plugin_basename( __FILE__ ) ) . '/../languages' );

    }





    /**
     * @param        $site_api
     * @param        $user_api
     * @param        $device
     * @param        $title
     * @param        $message
     * @param string $url
     * @param        $debug
     *  * send_notification
     *
     * Send notification when new order is received
     *
     * @access public
     */
    public static function cbdp_send_notification($site_api, $user_api, $device, $title, $message, $url = '' , $debug = ''){

        $pushover = new CBPDPushover_Api();
        // check settings, if not return
        if (('' == $site_api) || ('' == $user_api)) {
            self::cbpd_add_log(__('Site API or User API setting is missing.  Notification is not sent.', 'codeboxrpushoverfordokan') , $debug);

            return;
        }

        // Setup settings
        $pushover->setSiteApi($site_api);
        $pushover->setUserApi($user_api);
        if ('' != $device) {
            $pushover->setDevice($device);
        }

        // Setup message
        $pushover->setTitle($title);
        $pushover->setMessage($message);
        $pushover->setUrl($url);
        $response = '';

        self::cbpd_add_log(__('Sending: ', 'codeboxrpushoverfordokan') .
            "\nTitle: " . $title .
            "\nMessage: " . $message .
            "\nURL: " . $url , $debug);

        try {
            $response = $pushover->send();
            self::cbpd_add_log(__('Response: ', 'codeboxrpushoverfordokan') . "\n" . print_r($response, true) , $debug);

        } catch (Exception $e) {
            self::cbpd_add_log(sprintf(__('Error: Caught exception from send method: %s', 'codeboxrpushoverfordokan'), $e->getMessage()) , $debug);
        }

        self::cbpd_add_log(__('Pushover response', 'codeboxrpushoverfordokan') . "\n" . print_r($response, true) , $debug);

    }

    /**
     * add_log
     *
     * @access public
     *
     * @param $message string
     *
     * @return void
     */
    public static function cbpd_add_log($message , $debug)
    {
        //if($debug == 'on'){
        if($debug == 'yes'){
            $time   = date_i18n('m-d-Y @ H:i:s -');
            $handle = fopen(CBPD_WC_PUSHOVER_DIR . 'codeboxrpushoverfordokan_debug_pushover.log', 'a');
            if ($handle) {
                fwrite($handle, $time . " " . $message . "\n");
                fclose($handle);
            }

        }

    }

	/**
	 * pushover_get_currency_symbol
     *
     * @access public
     * @return string
     * @since 1.0.0
     */
    public static function cbpd_get_currency_symbol(){

        $currency = get_woocommerce_currency();

        switch ($currency) {
            case 'BRL' :
                $currency_symbol = '&#82;&#36;';
                break;
            case 'AUD' :
            case 'CAD' :
            case 'MXN' :
            case 'NZD' :
            case 'HKD' :
            case 'SGD' :
            case 'USD' :
                $currency_symbol = '$';
                break;
            case 'EUR' :
                $currency_symbol = '€';
                break;
            case 'CNY' :
            case 'RMB' :
            case 'JPY' :
                $currency_symbol = '¥‎';
                break;
            case 'RUB' :
                $currency_symbol = 'руб.';
                break;
            case 'KRW' :
                $currency_symbol = '₩';
                break;
            case 'TRY' :
                $currency_symbol = 'TL';
                break;
            case 'NOK' :
                $currency_symbol = 'kr';
                break;
            case 'ZAR' :
                $currency_symbol = 'R';
                break;
            case 'CZK' :
                $currency_symbol = 'Kč';
                break;
            case 'MYR' :
                $currency_symbol = 'RM';
                break;
            case 'DKK' :
                $currency_symbol = 'kr';
                break;
            case 'HUF' :
                $currency_symbol = 'Ft';
                break;
            case 'IDR' :
                $currency_symbol = 'Rp';
                break;
            case 'INR' :
                $currency_symbol = '₹';
                break;
            case 'ILS' :
                $currency_symbol = '₪';
                break;
            case 'PHP' :
                $currency_symbol = '₱';
                break;
            case 'PLN' :
                $currency_symbol = 'zł';
                break;
            case 'SEK' :
                $currency_symbol = 'kr';
                break;
            case 'CHF' :
                $currency_symbol = 'CHF';
                break;
            case 'TWD' :
                $currency_symbol = 'NT$';
                break;
            case 'THB' :
                $currency_symbol = '฿';
                break;
            case 'GBP' :
                $currency_symbol = '£';
                break;
            case 'RON' :
                $currency_symbol = 'lei';
                break;
            default    :
                $currency_symbol = '';
                break;
        }

        return apply_filters('dokanpushover_currency_symbol', $currency_symbol, $currency);
    }

}// end of class
