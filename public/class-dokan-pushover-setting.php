<?php
/**
 * Dokan Pushover settings Class
 *
 * @author wpboxr
 */
class Dokan_Pushover_Settings {

	public static function init() {
		static $instance = false;

		if ( !$instance ) {
			$instance = new Dokan_Pushover_Settings();
		}

		return $instance;
	}

	/**
	 * Save settings via ajax
	 *
	 * @return void
	 */
	function ajax_settings() {

		if ( ! dokan_is_user_seller( get_current_user_id() ) ) {
			wp_send_json_error( __( 'Are you cheating?', 'dokan' ) );
		}

		//$_POST['dokan_update_profile'] = '';

		switch( $_POST['form_id'] ) {
			case 'pushover-form':
				if ( !wp_verify_nonce( $_POST['_wpnonce'], 'dokan_pushover_settings_nonce' ) ) {
					wp_send_json_error( __( 'Are you cheating?', 'dokan' ) );
				}
				$ajax_validate =  $this->profile_validate();
				break;

		}

		if ( is_wp_error( $ajax_validate ) ) {
			wp_send_json_error( $ajax_validate->errors );
		}

		// we are good to go
		$save_data = $this->insert_settings_info();

		$progress_bar = dokan_get_profile_progressbar();
		$success_msg = __( 'Pushover setting has been saved successfully', 'codeboxrpushoverfordokan' ) ;

		$data = array(
			'progress' => $progress_bar,
			'msg'      => $success_msg,
		);

		wp_send_json_success( $data );
	}



	/**
	 * Validate profile settings
	 *
	 * @return bool|WP_Error
	 */
	function profile_validate() {

		if ( !isset( $_POST['dokan_update_pushover_settings'] ) ) {
			return false;
		}

		if ( !wp_verify_nonce( $_POST['_wpnonce'], 'dokan_pushover_settings_nonce' ) ) {
			wp_die( __( 'Are you cheating?', 'dokan' ) );
		}

		$error = new WP_Error();
		/*
		User and group identifiers are 30 characters long, case-sensitive, and may contain the character set [A-Za-z0-9].
		Device names are optional, may be up to 25 characters long, and will contain the character set [A-Za-z0-9_-].
		*/
		if(isset($_POST['pushovernotifiction'])){
			$cbpd_info         = $_POST['pushovernotifiction'];
			$enable             = isset($cbpd_info['enable']) ? $cbpd_info['enable'] : '0';
			$userapitoken       = isset($cbpd_info['userapitoken']) ? $cbpd_info['userapitoken'] : '';
			$device             = isset($cbpd_info['device']) ? $cbpd_info['device'] : '';
			$neworder           = isset($cbpd_info['neworder']) ? $cbpd_info['neworder']: '0';
			$freeorder          = isset($cbpd_info['freeorder'])? $cbpd_info['freeorder']: '0';
			$backorder          = isset($cbpd_info['backorder'])? $cbpd_info['backorder']: '0';
			$nostock            = isset($cbpd_info['nostock']) ? $cbpd_info['nostock']: '0';
			$lowstock           = isset($cbpd_info['lowstock']) ? $cbpd_info['lowstock']: '0';

			if(intval($enable)){
				if($userapitoken == '' || strlen($userapitoken) != 30){
					$error->add( 'dokanpushover_userapi', __( 'Userapi Token needs to be 30 characters long and should not any char but [A-Za-z0-9].', 'codeboxrpushoverfordokan' ) );
				}

			}

			if($device != '' && strlen($device) > 25){
				$error->add( 'dokanpushover_device', __( 'Device name can not be more than 25 character and may not contain but [A-Za-z0-9_-]', 'codeboxrpushoverfordokan' ) );
			}

		}
		/*if ( isset( $_POST['setting_category'] ) ) {

			if ( !is_array( $_POST['setting_category'] ) || !count( $_POST['setting_category'] ) ) {
				$error->add( 'dokan_type', __( 'Dokan type required', 'dokan' ) );
			}
		}

		if ( !empty( $_POST['setting_paypal_email'] ) ) {
			$email = filter_var( $_POST['setting_paypal_email'], FILTER_VALIDATE_EMAIL );

			if ( empty( $email ) ) {
				$error->add( 'dokan_email', __( 'Invalid email', 'dokan' ) );
			}
		}*/

		if ( $error->get_error_codes() ) {
			return $error;
		}

		return true;
	}

	/**
	 * Save store settings
	 *
	 * @return void
	 */
	function insert_settings_info() {

		$store_id            = get_current_user_id();
		$prev_dokan_settings = get_user_meta( $store_id, 'dokan_profile_settings', true );
		$dokan_pushover_settings = array( 'pushovernotifiction' => array() );

		if ( wp_verify_nonce( $_POST['_wpnonce'], 'dokan_pushover_settings_nonce' ) ) {

			// update profile settings info
			$cbpd_info         = $_POST['pushovernotifiction'];

			/*echo '<pre>';
			print_r($cbpd_info);
			echo '</pre>';*/

			//$social_fields  = dokan_get_social_profile_fields();

			$dokan_pushover_settings['pushovernotifiction']['enable']             = isset($cbpd_info['enable']) ? $cbpd_info['enable'] : '0';
			$dokan_pushover_settings['pushovernotifiction']['userapitoken']       = isset($cbpd_info['userapitoken']) ? $cbpd_info['userapitoken'] : '';
			$dokan_pushover_settings['pushovernotifiction']['device']             = isset($cbpd_info['device']) ? $cbpd_info['device'] : '';
			$dokan_pushover_settings['pushovernotifiction']['neworder']           = isset($cbpd_info['neworder']) ? $cbpd_info['neworder']: '0';
			$dokan_pushover_settings['pushovernotifiction']['freeorder']          = isset($cbpd_info['freeorder'])? $cbpd_info['freeorder']: '0';
			$dokan_pushover_settings['pushovernotifiction']['backorder']          = isset($cbpd_info['backorder'])? $cbpd_info['backorder']: '0';
			$dokan_pushover_settings['pushovernotifiction']['nostock']            = isset($cbpd_info['nostock']) ? $cbpd_info['nostock']: '0';
			$dokan_pushover_settings['pushovernotifiction']['lowstock']           = isset($cbpd_info['lowstock']) ? $cbpd_info['lowstock']: '0';



		}

		$dokan_settings = array_merge($prev_dokan_settings, $dokan_pushover_settings);

		//$profile_completeness = $this->calculate_profile_completeness_value( $dokan_settings );
		//$dokan_settings['profile_completion'] = $profile_completeness;

		update_user_meta( $store_id, 'dokan_profile_settings', $dokan_settings );

		do_action( 'dokan_store_profile_saved', $store_id, $dokan_settings );

		if ( ! defined( 'DOING_AJAX' ) ) {
			$_GET['message'] = 'profile_saved';
		}
	}




}
