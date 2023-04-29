<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if(!class_exists('SCORG_license')){
	class SCORG_license {
		public function __construct(){
			add_action( 'admin_init', array($this, 'SCORG_register_option') );
			add_action( 'admin_menu', array($this, 'SCORG_activate_license') );
			add_action( 'admin_menu', array($this, 'SCORG_deactivate_license') );
			add_action( 'admin_notices', array($this, 'SCORG_admin_notices') );
		}

		public function SCORG_register_option() {
			// creates our settings in the options table
			register_setting('SCORG_license', 'scorg_license_key', array($this, 'SCORG_edd_sanitize_license') );
		}

		public function SCORG_edd_sanitize_license( $new ) {
			$old = get_option( 'scorg_license_key' );
			if( $old && $old != $new ) {
				delete_option( 'scorg_license_key' ); // new license has been entered, so must reactivate
				delete_option( 'scorg_license_status' ); // new license has been entered, so must reactivate
			}
			return $new;
		}

		/************************************
		* Activate a license key
		*************************************/

		public function SCORG_activate_license() {

			// listen for our activate button to be clicked
			if( isset( $_POST['scorg_license_activate'] ) ) {
				ob_start();

				// run a quick security check
			 	if( ! check_admin_referer( 'scorg_nonce', 'scorg_nonce' ) )
					return; // get out if we didn't click the Activate button

				// retrieve the license from the database
				$license = trim( $_POST['scorg_license_key'] );
				update_option( 'scorg_license_key', $license );

				// data to send in our API request
				$api_params = array(
					'edd_action' => 'activate_license',
					'license'    => $license,
					'item_id'  => SCORG_SAMPLE_ITEM_ID, // the name of our product in EDD
					'item_name'  => urlencode( SCORG_SAMPLE_ITEM_NAME ), // the name of our product in EDD
					'url'        => home_url()
				);

				// Call the custom API.
				$response = wp_remote_post( SCORG_SAMPLE_STORE_URL, array( 'timeout' => 15, 'sslverify' => false, 'body' => $api_params ) );

				// make sure the response came back okay
				if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {

					if ( is_wp_error( $response ) ) {
						$message = $response->get_error_message();
					} else {
						$message = __( 'An error occurred, please try again.' );
					}

				} else {

					$license_data = json_decode( wp_remote_retrieve_body( $response ) );
					//echo "<pre>"; print_r($license_data); "</pre>"; exit;

					if ( false === $license_data->success ) {

						switch( $license_data->error ) {

							case 'expired' :

								$message = sprintf(
									__( 'Your license key expired on %s.' ),
									date_i18n( get_option( 'date_format' ), strtotime( $license_data->expires, current_time( 'timestamp' ) ) )
								);
								break;

							case 'disabled' :
							case 'revoked' :

								$message = __( 'Your license key has been disabled.' );
								break;

							case 'missing' :

								$message = __( 'Invalid license.' );
								break;

							case 'invalid' :
							case 'site_inactive' :

								$message = __( 'Your license is not active for this URL.' );
								break;

							case 'item_name_mismatch' :

								$message = sprintf( __( 'This appears to be an invalid license key for %s.' ), SCORG_SAMPLE_ITEM_ID );
								break;

							case 'no_activations_left':

								$message = __( 'Your license key has reached its activation limit.' );
								break;

							default :

								$message = __( 'An error occurred, please try again.' );
								break;
						}

					}

				}

				// Check if anything passed on a message constituting a failure
				if ( ! empty( $message ) ) {
					$base_url = admin_url( 'plugins.php?page=' . SCORG_SAMPLE_PLUGIN_LICENSE_PAGE );
					$redirect = add_query_arg( array( 'sl_activation' => 'false', 'message' => urlencode( $message ) ), $base_url );

					wp_redirect( $redirect );
					exit();
				}

				// $license_data->license will be either "valid" or "invalid"

				update_option( 'scorg_license_status', $license_data->license );
				wp_redirect( admin_url( 'edit.php?post_type=scorg' ) );
				exit();
			}
		}

		/***********************************************
		* Illustrates how to deactivate a license key.
		***********************************************/

		public function SCORG_deactivate_license() {

			// listen for our activate button to be clicked
			if( isset( $_POST['scorg_license_deactivate'] ) ) {
				ob_start();
				// run a quick security check
			 	if( ! check_admin_referer( 'scorg_nonce', 'scorg_nonce' ) )
					return; // get out if we didn't click the Activate button

				// retrieve the license from the database
				$license = trim( get_option( 'scorg_license_key' ) );


				// data to send in our API request
				$api_params = array(
					'edd_action' => 'deactivate_license',
					'license'    => $license,
					'item_id'  => SCORG_SAMPLE_ITEM_ID, // the name of our product in EDD
					'item_name'  => urlencode( SCORG_SAMPLE_ITEM_NAME ), // the name of our product in EDD
					'url'        => home_url()
				);

				// Call the custom API.
				$response = wp_remote_post( SCORG_SAMPLE_STORE_URL, array( 'timeout' => 15, 'sslverify' => false, 'body' => $api_params ) );

				// make sure the response came back okay
				if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {

					if ( is_wp_error( $response ) ) {
						$message = $response->get_error_message();
					} else {
						$message = __( 'An error occurred, please try again.' );
					}

					$base_url = admin_url( 'plugins.php?page=' . SCORG_SAMPLE_PLUGIN_LICENSE_PAGE );
					$redirect = add_query_arg( array( 'sl_activation' => 'false', 'message' => urlencode( $message ) ), $base_url );

					wp_redirect( $redirect );
					exit();
				}

				// decode the license data
				$license_data = json_decode( wp_remote_retrieve_body( $response ) );

				// $license_data->license will be either "deactivated" or "failed"
				if( $license_data->license == 'deactivated' ) {
					delete_option( 'scorg_license_key' );
					delete_option( 'scorg_license_status' );
				}

				wp_redirect( admin_url( 'plugins.php?page=' . SCORG_SAMPLE_PLUGIN_LICENSE_PAGE ) );
				exit();

			}
		}

		/************************************
		* Check if a license key is still valid
		*************************************/

		public function SCORG_check_license() {

			global $wp_version;

			$license = trim( get_option( 'scorg_license_key' ) );

			$api_params = array(
				'edd_action' => 'check_license',
				'license' => $license,
				'item_id'  => SCORG_SAMPLE_ITEM_ID, // the name of our product in EDD
				'item_name' => urlencode( SCORG_SAMPLE_ITEM_NAME ),
				'url'       => home_url()
			);

			// Call the custom API.
			$response = wp_remote_post( SCORG_SAMPLE_STORE_URL, array( 'timeout' => 15, 'sslverify' => false, 'body' => $api_params ) );

			if ( is_wp_error( $response ) )
				return false;

			$license_data = json_decode( wp_remote_retrieve_body( $response ) );

			if( $license_data->license == 'valid' ) {
				echo 'valid'; exit;
				// this license is still valid
			} else {
				echo 'invalid'; exit;
				// this license is no longer valid
			}
		}

		/**
		 * This is a means of catching errors from the activation method above and displaying it to the customer
		 */
		public function SCORG_admin_notices() {
			if ( isset( $_GET['sl_activation'] ) && ! empty( $_GET['message'] ) ) {

				switch( $_GET['sl_activation'] ) {

					case 'false':
						$message = urldecode( $_GET['message'] );
						?>
						<div class="error">
							<p><?php echo $message; ?></p>
						</div>
						<?php
						break;

					case 'true':
					default:
						// Developers can put a custom success message here for when activation is successful if they way.
						break;

				}
			}
		}
	}
	new SCORG_license();
}