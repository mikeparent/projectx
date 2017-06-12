<?php

	class Downloads {

		function update_privacypolicy(){

			$privacypolicy = $_POST['privacypolicy'];
			$response['success'] = '0';

			if ( get_option( 'px_privacypolicy' ) !== false ) {

			    // The option already exists, so we just update it.
			    if(update_option( 'px_privacypolicy', $privacypolicy )){
			    	$response['success'] = '1';
			    }

			} else {

			    $deprecated = null;
			    $autoload = 'no';
			    if(add_option( 'px_privacypolicy', $privacypolicy, $deprecated, $autoload )){
			    	$response['success'] = '1';
			    }

			}

		    echo json_encode($response);
		    die();

		}

		function get_privacy_policy(){

			$response['success'] = 0;

			$response['privacypolicy'] = get_option( 'px_privacypolicy' );

			echo json_encode($response);
		    die();

		}

		function addDownload(){

			$response = '';

			if(isset($_FILES['thefile'])){

				$tmp_name = $_FILES["thefile"]["tmp_name"];
				$uploadfilename = $_FILES["thefile"]["name"];
				$saveddate=date("mdy-Hms");

				$newfilename = '../wp-content/plugins/projectx/downloads/' . $uploadfilename;

				if(file_exists($newfilename)){
					$response['error'] = 'File exists with the same name';
					return $response;
				}
				
				if(move_uploaded_file($tmp_name, $newfilename)){
					$response = "File Uploaded";
					
					// add download to database
					$campaignId = '';
					$campaignTitle = '';
					$outcomeId = '';
					$outcomeTitle = '';

					if(isset($_POST['campaign'])){
						if($_POST['campaign'] != ''){
							$campaignObj = explode('###', $_POST['campaign']);
							$campaignId = $campaignObj[0];
							$campaignTitle = $campaignObj[1];
						}
					}

					if(isset($_POST['outcome'])){
						if($_POST['outcome'] != ''){
							$outcomeObj = explode('###', $_POST['outcome']);
							$outcomeId = $outcomeObj[0];
							$outcomeTitle = $outcomeObj[1];
						}
					}

					global $wpdb;
					$wpdb->insert($wpdb->prefix . 'px_downloads', array(
					    'filename' => $uploadfilename,
					    'funnel_position' => $_POST['funnelpos'],
					    'campaign_id' => $campaignId,
					    'outcome_id' => $outcomeId,
					    'campaign' => $campaignTitle,
					    'outcome' => $outcomeTitle
					));

					$lastid = $wpdb->insert_id;

				}else{
					$response['error'] = "No file selected";
				}

			}

			return $response;

		}

		function remove_file(){

			$response['success'] = '0';
			$response['downloads'] = '';

			$path = '../wp-content/plugins/projectx/downloads/' . $_POST['filename'];

			if(unlink($path)){

				$response['success'] = '1';

				// remove from database
				global $wpdb; 
   				$wpdb->delete( $wpdb->prefix . 'px_downloads', array( 'id' => $_POST['downloadId'] ) );

				$response['downloads'] = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "px_downloads;");

			}

			echo json_encode($response);
		    die();

		}

		function get_campaigns_outcomes(){

			$response['success'] = '0';
			$response['outcomes'] = [];

			global $wpdb;
			$response['outcomes'] = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "outcomes WHERE campaign_id=" . $_POST['campaign'] . ";");

			$response['success'] = '1';

			echo json_encode($response);
		    die();

		}

		function get_filtered_downloads(){

			$response['success'] = '0';
			$response['downloads'] = [];

			global $wpdb;
			$response['downloads'] = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "px_downloads WHERE outcome_id=" . $_POST['outcome_id'] . " AND funnel_position='" . $_POST['funnel_position'] . "'");

			$response['theposts'] = $_POST['outcome_id'] . ' ' . $_POST['funnel_position'];

			$response['success'] = '1';

			echo json_encode($response);
		    die();

		}

		function __construct(){

			add_action( 'wp_ajax_update_privacypolicy', array($this, 'update_privacypolicy') );
			add_action( 'wp_ajax_nopriv_update_privacypolicy', array($this, 'update_privacypolicy') );

			add_action( 'wp_ajax_get_privacy_policy', array($this, 'get_privacy_policy') );
			add_action( 'wp_ajax_nopriv_get_privacy_policy', array($this, 'get_privacy_policy') );

			add_action( 'wp_ajax_remove_file', array($this, 'remove_file') );
			add_action( 'wp_ajax_nopriv_remove_file', array($this, 'remove_file') );

			add_action( 'wp_ajax_get_campaigns_outcomes', array($this, 'get_campaigns_outcomes') );
			add_action( 'wp_ajax_nopriv_get_campaigns_outcomes', array($this, 'get_campaigns_outcomes') );

			add_action( 'wp_ajax_get_filtered_downloads', array($this, 'get_filtered_downloads') );
			add_action( 'wp_ajax_nopriv_get_filtered_downloads', array($this, 'get_filtered_downloads') );

		}

	}

?>