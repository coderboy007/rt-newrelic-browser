<?php

/**
 * Plugin Name: New Relic Browser
 * Plugin URI: http://www.rtcamp.com
 * Description: New Relic Browser Monitoring plugin.
 * Version: 1.0
 * Author: rtCamp
 * Author URI: http://www.rtcamp.com
 * License:  rt-newrelic-browser
 */
function rtp_relic_register_settings()
{
	register_setting( 'relic_options_settings', 'relic_options', 'rtp_relic_options_validate' );
}

/**
 * Add options page
 * @return null
 */
function rtp_relic_option_page()
{
	$rtp_new_relic_setting_page = add_options_page( 'New Relic Options', 'New Relic Browser', 'manage_options', 'new-relic-browser', 'new_relic_options' );
	add_action( 'load-' . $rtp_new_relic_setting_page, 'rtp_relic_page_help' );
}

/**
 * Add help tab
 * @return null
 */
function rtp_relic_page_help()
{
	$screen = get_current_screen();
	$screen->add_help_tab(
		array(
				'id' => 'rtp_relic_page_overview_tab',
				'title' => __( 'Overview' ),
				'content' => __(
					'<p>This page will allow you to integrate your New Relic Browser app with your website. 
					If do not have New Relic account, then just select "No" and provide required details. The New Relic script will be loaded automatically in &lt;head&gt; tag of your site without any manual effort.<p>'
				) , )
	);
	$screen->add_help_tab(
		array(
				'id' => 'rtp_relic_page_about_tab',
				'title' => __( 'New Relic Browser' ),
				'content' => __( '<p>New Relic Browser provides deep visibility and actionable insights into real users experiences on your website. With standard page load timing (sometimes referred to as real user monitoring or RUM), New Relic measures the overall time to load the entire webpage. However, New Relic Browser goes beyond RUM to also help you monitor the performance of individual sessions, AJAX requests, and JavaScript errors—extending the monitoring throughout the entire life cycle of the page.<p>' ),
			)
	);
}

/**
 * Add settings page
 * @return null
 */
function new_relic_options()
{
	include 'admin/new-relic-admin.php';
}

/**
 *  Enqueue scripts and styles
 * @return null
 */
function rtp_relic_load_js()
{
	wp_enqueue_script( 'rtp_relic_js', plugins_url( '/js/rtp-relic.js', __FILE__ ), array( 'jquery' ) );
	wp_enqueue_script( 'jquery-ui-dialog' );
	wp_enqueue_style( 'rtp_relic_css', plugins_url( '/css/rtp-new-relic.css', __FILE__ ) );
	wp_enqueue_style( 'rtp_relic_ui_css', plugins_url( '/css/jquery-ui.css', __FILE__ ) );
}

/**
 * Validate user form
 * @param array $relic_user_data
 * @return array
 */
function rtp_relic_validate_form( $relic_user_data )
{

	$relic_valid = true;
	$relic_error_message = '';
	if ( isset( $relic_user_data['rtp-relic-form-name'] ) ) {
		$form_name = sanitize_text_field( $relic_user_data['rtp-relic-form-name'] );
	}

	if ( 'rtp-get-browser' == $form_name ) {
		/* get browser list form */
		if ( ( '' == $relic_user_data['rtp-user-api-key'] ) ) {
			$relic_valid = false;
			$relic_error_message = __( 'All fields are required.' );
		}
	} else if ( 'rtp-select-browser' == $form_name ) {
		/* select browser application form */
		if ( '' == $relic_user_data['rtp-selected-browser-id'] ) {
			$relic_valid = false;
			$relic_error_message = __( 'Select atleast one application.' );
		}
	} else if ( 'rtp-add-account' == $form_name ) {
		/* add new user account form */
		if ( ( '' == $relic_user_data['relic-account-email']) || ( '' == $relic_user_data['relic-first-name']) || ( '' == $relic_user_data['relic-last-name']) || ( '' == $relic_user_data['relic-account-name']) ) {
			$relic_valid = false;
			$relic_error_message = __( 'All fields are required.' );
		} else if ( ! filter_var( $relic_user_data['relic-account-email'], FILTER_VALIDATE_EMAIL ) ) {
			$relic_valid = false;
			$relic_error_message = __( 'Not a valid email address' );
		} else if ( ( ! preg_match( '/^[a-zA-Z ]*$/', $relic_user_data['relic-first-name'] )) || ( ! preg_match( '/^[a-zA-Z ]*$/', $relic_user_data['relic-last-name'] )) ) {
			$relic_valid = false;
			$relic_error_message = __( 'Name should contain letters only' );
		}
	}

	$error_array = array(
		'valid' => $relic_valid,
		'message' => $relic_error_message,
	);

	return $error_array;
}

/**
 * Create a browser app
 * @param string $app_name name of the app
 * @param string $account_api_key api key of the account
 * @return boolean
 */
function rtp_create_browser_app( $app_name, $account_api_key )
{

	/* send curl request */

	$app_data = array(
		browser_application => array(
			'name' => $app_name,
		)
	);
	$app_dataString = json_encode( $app_data );
	$app_curl = curl_init();
	curl_setopt_array(
		$app_curl, array(
		CURLOPT_URL => 'https://api.newrelic.com/v2/browser_applications.json',
		CURLOPT_POST => 1,
		CURLOPT_RETURNTRANSFER => 1,
		CURLOPT_HTTPHEADER => array( 'x-api-key:' . $account_api_key, 'Content-Type:application/json' ),
		CURLOPT_POSTFIELDS => $app_dataString,
			)
	);

	$app_response = curl_exec( $app_curl );
	curl_close( $app_curl );
	$app_json_data = json_decode( $app_response );

	if ( empty( $app_json_data->browser_application->loader_script ) ) {
		add_settings_error( 'relic_options', 'relic_options_error', $app_json_data->error->title, 'error' );
		return false;
	} else {
		// stored the received browser application data

		/* insert browser monitoring key and browser app id before script ends */
		$index = strpos( $app_json_data->browser_application->loader_script, '</script>' );
		$newscript = substr_replace( $app_json_data->browser_application->loader_script, 'NREUM.info.applicationID=' . $app_json_data->browser_application->id . '; NREUM.info.licenseKey=' . $app_json_data->browser_application->browser_monitoring_key, $index, 0 );
		$browser_details_array = array(
			'relic_app_name' => $app_json_data->browser_application->name,
			'relic_app_key' => $app_json_data->browser_application->browser_monitoring_key,
			'relic_app_id' => $app_json_data->browser_application->id,
			'relic_app_script' => $newscript,
		);
		add_option( 'rtp_relic_browser_details', $browser_details_array );
		return true;
	}
}

function rtp_relic_options_validate( $input )
{

	/* validate form if js not worked */
	$rtp_relic_form_validated = rtp_relic_validate_form( $_POST );

	if ( ! $rtp_relic_form_validated['valid'] ) {
		/* form not validated throw an error */
		add_settings_error( 'relic_options', 'relic_options_error', $rtp_relic_form_validated['message'], 'error' );
	} else {
		/* form validated go ahead */
		$option_name = 'rtp_relic_account_details';
		$app_option_name = 'rtp_relic_browser_details';
		$browser_app_list_option = 'rtp_relic_browser_list';

		if ( 'rtp-select-browser' == $_POST['rtp-relic-form-name'] ) {
			/* user has selected the application so fetch the browser script */
			$rtp_account_details = get_option( $option_name );
			if ( isset( $_POST['rtp-selected-browser-id'] ) ) {
				/* In this curl we are filtering browser applications by using browser id */
				$get_browser_curl = curl_init();
				curl_setopt_array( $get_browser_curl, array(
					CURLOPT_URL => 'https://api.newrelic.com/v2/browser_applications.json?filter[ids]=' . sanitize_text_field( $_POST['rtp-selected-browser-id'] ),
					CURLOPT_RETURNTRANSFER => 1,
					CURLOPT_CUSTOMREQUEST => 'GET',
					CURLOPT_HTTPHEADER => array( 'x-api-key:' . $rtp_account_details['relic_api_key'], 'Content-Type:application/json' ),
				) );
				$browser_list_response = curl_exec( $get_browser_curl );
				$decoded_data = json_decode( $browser_list_response );
				$browser_list = $decoded_data->browser_applications;

				/* we have filtered the applications so go ahead and select the first application */

				$browser_script = $browser_list[0]->loader_script;
				curl_close( $get_browser_curl );

				/* insert browser monitoring key and browser app id before script ends */

				$index = strpos( $browser_script, '</script>' );
				$newscript = substr_replace( $browser_script, 'NREUM.info.applicationID=' . $browser_list[0]->id . '; NREUM.info.licenseKey=' . $browser_list[0]->browser_monitoring_key, $index, 0 );

				/* store the browser application details */

				$browser_array = array(
					'relic_app_name' => $browser_list[0]->name,
					'relic_app_key' => $browser_list[0]->browser_monitoring_key,
					'relic_app_id' => $browser_list[0]->id,
					'relic_app_script' => $newscript,
				);
				add_option( $app_option_name, $browser_array );
				add_settings_error( 'relic_options', 'relic_options_error', 'New Relic Browser App integrated successfully', 'updated' );

				/* browser details saved so delete the browsers list from meta */

				delete_option( $browser_app_list_option );
			}
		} else if ( 'rtp-create-browser' == $_POST['rtp-relic-form-name'] ) {
			$rtp_account_details = get_option( $option_name );
			$account_api_key = $rtp_account_details['relic_api_key'];
			if ( isset( $_POST['rtp-relic-browser-name'] ) ) {
				$browser_created = rtp_create_browser_app( sanitize_text_field( $_POST['rtp-relic-browser-name'] ), $account_api_key );
			}
			/* store the account details */
			if ( $browser_created ) {
				$account_details_array = array(
					'relic_api_key' => $account_api_key,
				);
				add_option( $option_name, $account_details_array );
				add_settings_error( 'relic_options', 'relic_options_error', 'New Relic Browser App integrated successfully', 'updated' );
			}
		} else if ( 'rtp-get-browser' == $_POST['rtp-relic-form-name'] ) {
			/* get the list of browser apps */
			if ( isset( $_POST['rtp-user-api-key'] ) ) {
				$account_api_key = sanitize_text_field( $_POST['rtp-user-api-key'] );
			}
			$get_app_list_curl = curl_init();
			curl_setopt_array(
				$get_app_list_curl, array(
				CURLOPT_URL => 'https://api.newrelic.com/v2/browser_applications.json',
				CURLOPT_RETURNTRANSFER => 1,
				CURLOPT_HTTPHEADER => array( 'x-api-key:' . $account_api_key, 'Content-Type:application/json' ),
					)
			);
			$list_response = curl_exec( $get_app_list_curl );
			curl_close( $get_app_list_curl );
			$browser_app_list = json_decode( $list_response );
			if ( ! empty( $browser_app_list->browser_applications ) ) {
				$browser_app_array = array();
				foreach ( $browser_app_list->browser_applications as $key => $app_data ) {
					$app_data_array = array(
						'browser_id' => $app_data->id,
						'browser_name' => $app_data->name,
					);
					array_push( $browser_app_array, $app_data_array );
				}

				/* store the browsers list */

				add_option( 'rtp_relic_browser_list', $browser_app_array );

				/* store the account details */

				$main_array = array(
					'relic_api_key' => $account_api_key,
				);
				add_option( $option_name, $main_array );
				add_settings_error( 'relic_options', 'relic_options_error', 'Select Browser Application', 'updated' );
			} else {
				/* create a browser app as the account doesn't contain any app */
				if ( isset( $_SERVER['SERVER_NAME'] ) ) {
					$browser_created = rtp_create_browser_app( sanitize_text_field( $_SERVER['SERVER_NAME'] ), $account_api_key );
				}
				/* store the account details */
				if ( $browser_created ) {
					$account_details_array = array(
						'relic_api_key' => $account_api_key,
					);
					add_option( $option_name, $account_details_array );
					add_settings_error( 'relic_options', 'relic_options_error', 'New Relic Browser App integrated successfully', 'updated' );
				}
			}
		} else {
			/* set password to new account */
			$relic_password = wp_generate_password( 8 );

			/* if the account data is already stored then delete the account */
			if ( 'rtp-remove-account' == $_POST['rtp-relic-form-name'] ) {
				/* curl request to remove account */
				if ( '' != $_POST['rtp-relic-account-id'] ) {
					if ( isset( $_POST['rtp-relic-account-id'] ) ) {
						$account_id = sanitize_text_field( $_POST['rtp-relic-account-id'] );
					}
					$delete_curl = curl_init();
					curl_setopt_array(
						$delete_curl, array(
						CURLOPT_URL => 'https://rpm.newrelic.com/api/v2/partners/857/accounts/' . $account_id,
						CURLOPT_CUSTOMREQUEST => 'DELETE',
						CURLOPT_RETURNTRANSFER => 1,
						CURLOPT_HTTPHEADER => array( 'x-api-key:6155aee398970036405f017b9f788801ed32f23e208f2d4', 'Content-Type:application/json' ),
							)
					);
					$delete_response = curl_exec( $delete_curl );
					curl_close( $delete_curl );

					/* delete the stored data */

					delete_option( $option_name );
					delete_option( $app_option_name );
					delete_option( $browser_app_list_option );
				} else {
					/* delete the stored meta */
					delete_option( $option_name );
					delete_option( $app_option_name );
					delete_option( $browser_app_list_option );
				}
				add_settings_error( 'relic_options', 'relic_options_error', 'New Relic Browser App removed successfully', 'updated' );
			} else {
				/* always set allow_api_access to true while creating account
				  start of API 1 */
					$relic_account_name = sanitize_text_field( $_POST['relic-account-name'] );
					$relic_account_email = sanitize_email( $_POST['relic-account-email'] );
					$relic_first_name = sanitize_text_field( $_POST['relic-first-name'] );
					$relic_last_name = sanitize_text_field( $_POST['relic-last-name'] );
					$data = array(
					'account' => array(
						'name' => $relic_account_name,
						'allow_api_access' => true,
						'testing' => true,
						'users' => array(
							array(
								'email' => $relic_account_email,
								'password' => $relic_password,
								'first_name' => $relic_first_name,
								'last_name' => $relic_last_name,
								'role' => 'admin',
								'owner' => 'true',
							),
						),
						'subscriptions' => array(),
					)
					);

					/* for this api data is to be pass in json */

					$dataString = json_encode( $data );
					$curl = curl_init();
					curl_setopt_array(
						$curl, array(
						CURLOPT_URL => 'https://rpm.newrelic.com/api/v2/partners/857/accounts',
						CURLOPT_POST => 1,
						CURLOPT_RETURNTRANSFER => 1,
						CURLOPT_HTTPHEADER => array( 'x-api-key:6155aee398970036405f017b9f788801ed32f23e208f2d4', 'Content-Type:application/json' ),
						CURLOPT_POSTFIELDS => $dataString,
						)
					);

					$response = curl_exec( $curl );
					curl_close( $curl );
					$json_data = json_decode( $response );
					if ( $json_data->error != '' ) {
						add_settings_error( 'relic_options', 'relic_options_error', $json_data->error, 'error' );
					} else if ( isset( $json_data->api_key ) ) {
						/* store the received data */
							$main_array = array(
							'relic_account_name' => $json_data->name,
							'relic_api_key' => $json_data->api_key,
							'relic_id' => $json_data->id,
							'relic_password' => $relic_password,
							);
						add_option( $option_name, $main_array );
							/* end of API 1
						  Now create the browser app */
						if ( isset( $_POST['relic-account-name'] ) ){
							$relic_account_name = sanitize_text_field( $_POST['relic-account-name'] );
						}
						$browser_created = rtp_create_browser_app( $relic_account_name, $json_data->api_key );
						if ( $browser_created ) {
							/* mail the account details to user */
							if ( isset( $_POST['relic-account-email'] ) ){
								$relic_user_mail = sanitize_email( $_POST['relic-account-email'] );
								$browser_app_details = get_option( 'rtp_relic_browser_details' );
								$relic_subject = 'Welcome to New Relic Browser monitoring of '.$relic_account_name;
								$relic_headers = "MIME-Version: 1.0\r\n";
								$relic_headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";
								$relic_email_message = '<div style="font-size:15px;margin-top:20px;border:1px solid #666;padding:20px 50px;">
													<p><h1 style="color:#666;font-weight: 300;margin-top: 10px;">Welcome to New Relic Browser</h1></p>
													<p>Thanks for adding New Relic Browser monitoring of '.$relic_account_name.'. Please <a href="https://rpm.newrelic.com/accounts/'.$json_data->id.'/browser/'.$browser_app_details['relic_app_id'].'">login</a> to your New Relic account and change your temporary password below :</p>
														<div style="font-size:15px;margin:20px 0;display: inline-block;border:1px solid #666;padding: 15px;">
															<p style="margin:2px">
																<span>Email : </span>
																<span><a href="mailto:' . $relic_user_mail . '" target="_blank">' . $relic_user_mail . '</a></span>
															</p>
													<p style="margin:2px">
														<span>Password : </span>
														<span>' . $relic_password . '</span>
													</p>
												</div>
												<p style="margin-top:20px">
												For help getting started with New Relic Browser, please visit <a href="https://docs.newrelic.com/docs/browser/new-relic-browser">https://docs.newrelic.com/docs/browser/new-relic-browser</a> and <a href="https://discuss.newrelic.com/c/browser">https://discuss.newrelic.com/c/browser</a>
												</p>
												<p style="margin-top:20px">
												Be sure to start your 15-day free trial of New Relic Browser Pro by clicking the "Activate" button on <a href="https://rpm.newrelic.com/accounts/'.$json_data->id.'/browser/'.$browser_app_details['relic_app_id'].'">https://rpm.newrelic.com/accounts/'.$json_data->id.'/browser/'.$browser_app_details['relic_app_id'].'</a> After 15 days, if you choose to not upgrade to New Relic Browser Pro, your account will switch to Browser Lite, which you can use for free, forever!
												</p>
												</div>';
								wp_mail( $relic_user_mail, $relic_subject, $relic_email_message, $relic_headers );
							}
							add_settings_error( 'relic_options', 'relic_options_error', 'New Relic Browser App integrated successfully', 'updated' );
						}
					} else {
						add_settings_error( 'relic_options', 'relic_options_error', __( 'Error while creating account' ), 'error' );
					}
			}
		}
	}
	return $input;
}

/**
 * Add script tag in head
 * @return null
 */
function insert_relic_script()
{

	/* fetched the script stored in metadata and insert in head */

	$app_option_name = 'rtp_relic_browser_details';
	if ( false !== get_option( $app_option_name ) ) {
		$relic_browser_options_data = get_option( $app_option_name );
		$output = $relic_browser_options_data['relic_app_script'];
		echo $output;
	}
}

add_action( 'wp_head', 'insert_relic_script', 1 );
add_action( 'admin_enqueue_scripts', 'rtp_relic_load_js' );
add_action( 'admin_menu', 'rtp_relic_option_page' );
add_action( 'admin_init', 'rtp_relic_register_settings' );
