<?php

	if ( ! defined( 'ABSPATH' ) ) exit;

	class User_Session_Synchronizer_Email_Verification {

		/**
		 * The single instance of User_Session_Synchronizer_Email_Verification.
		 * @var 	object
		 * @access  private
		 * @since 	1.0.0
		 */
		private static $_instance = null;

		/**
		 * The main plugin object.
		 * @var 	object
		 * @access  public
		 * @since 	1.0.0
		 */
		public $parent = null;

		/**
		 * Prefix for plugin settings.
		 * @var     string
		 * @access  public
		 * @since   1.0.0
		 */

		public $emailVerification = array();
		
		private $user_id;
		private $plugin_slug;

		public function __construct ( $parent ) {
			
			$this->parent = $parent;
			
			$this->user_id = get_current_user_id();

			register_activation_hook(__FILE__, array($this, 'activate_plugins_email'));

			//add_action('wp_login', array( $this, 'ussync_after_user_loggedin'),10);
			
			add_shortcode('ussyncemailverificationcode', array($this, 'get_email_verification_link'));

			add_filter('manage_users_columns', array($this, 'update_user_table'), 10, 1);
			add_filter('manage_users_custom_column', array($this, 'modify_user_table_row'), 10, 3);
			
			add_action('user_register', array( $this, 'after_user_register'), 10, 1);
			add_action('admin_head', array($this, 'verify_user'));
			
			add_action('init', array($this, 'verify_registered_user'));
		}
		
		public function after_user_register($user_id){

			// the new user just registered but never logged in yet
			add_user_meta($user_id, 'ussync_has_not_logged_in_yet', 'true');
		}

		public function verify_registered_user(){
			
			if(isset($_GET["ussync_confirmation_verify"])){
				
				$user_meta = explode("@", base64_decode($_GET["ussync_confirmation_verify"]));
				
				if (get_user_meta((int) $user_meta[1], "ussync_email_verifiedcode", TRUE) == $user_meta[0]) {
					
					update_user_meta((int) $user_meta[1], "ussync_email_verified", "true");
					
					delete_user_meta((int) $user_meta[1], "ussync_email_verifiedcode");
					
					echo '<div class="updated fade"><p><b>Congratulations</b> your account has been successfully verified!</p></b></div>';
				}
				elseif(get_user_meta((int) $user_meta[1], "ussync_email_verified", TRUE) == 'true'){
					
					echo '<div class="updated fade"><p>Your account has already been verified...</p></b></div>';
				}
				else{
					
					echo '<div class="updated fade"><p><b>Oops</b> something went wrong during your account validation...</p></b></div>';
				}
			}			
			elseif(is_user_logged_in()){
				
				$user_id = get_current_user_id();
				
				$user_meta = get_user_meta($user_id);

				if(isset($user_meta['ussync_has_not_logged_in_yet'])){
					
					delete_user_meta($user_id, 'ussync_has_not_logged_in_yet');
					
					update_user_meta($user_id, 'ussync_email_verified', 'true');
				}					
			}
		}		

		public function activate_plugins_email() {
			
			ob_start();
			include plugin_dir_path(__FILE__) . "views/demo_email.html";
			$demo_email_content = ob_get_clean();
			
			update_option("ussync-email-header", $demo_email_content);
			update_option("ussync_email_confemail", get_option("admin_email"));
			update_option("ussync_email_conf_title", "Please Verify Your email Account");
		}

		public function view_email_setting() {
			
			include plugin_dir_path(__FILE__) . "views/email-setting.php";
		}

		public function view_email_verification() {

			include plugin_dir_path(__FILE__) . "views/email-verification.php";
		}

		public function codeMailSender($email) {
					
			$urlparts = parse_url(site_url());
			$domain = $urlparts ['host'];						
					
			$Email_title = get_option("ussync_email_conf_title");
			$sender_email = get_option("ussync_email_confemail");
			$message = get_option("ussync-email-header");
			
			$headers   = [];
			$headers[] = 'From: ' . get_bloginfo('name') . ' <noreply@'.$domain.'>';
			$headers[] = 'MIME-Version: 1.0';
			$headers[] = 'Content-type: text/html';
			
			$preMesaage = "<html><body><div style='width:700px;padding:5px;margin:auto;font-size:14px;line-height:18px'>" . apply_filters('the_content', $message) . "<div style='clear:both'></div></div></body></html>";
			
			if(!wp_mail($email, $Email_title, $preMesaage, $headers)){
				
				global $phpmailer;
				
				var_dump($phpmailer->ErrorInfo);exit;				
			}
		}		
		
		public function get_email_verification_link(){
			
			$link='';
			
			if(isset($_GET["user_id"]) && wp_verify_nonce($_GET["wp_nonce"], "ussync_email")){
				
				$user_id = $_GET['user_id'];
				
				$secret = get_user_meta( (int) $user_id, "ussync_email_verifiedcode", true);
				
				$createLink = $secret . "@" . $user_id;
				
				$hyperlink = get_admin_url() . "profile.php?ussync_confirmation_verify=" . base64_encode($createLink);
				
				$link .= "<a href='" . $hyperlink . "'> Click here to verify</a>";
			}
			
			return $link;
		}

		public function update_user_table($column) {
			
			$column['ussync_verified'] = 'Verified user';
			return $column;
		}

		public function modify_user_table_row($val, $column_name, $user_id) {
			
			$user_role = get_userdata($user_id);
			
			$row='';
			
			if ($column_name == "ussync_verified") {

				if ($user_role->roles[0] != "administrator") {
					
					if (get_user_meta($user_id, "ussync_email_verified", true) != "true") {
						
						if (get_user_meta($user_id, "ussync_has_not_logged_in_yet", true) == "true") {
							
							$text = "<img src='" . plugin_dir_url(__FILE__) . "images/time.png' width=25 height=25>";
							$row .= "<a title=\"Validate User\" href=\"" . add_query_arg(array("user_id" => $user_id, "wp_nonce" => wp_create_nonce("ussync_email"), "ussync_confirm" => "true"), get_admin_url() . "users.php") . "\">" . apply_filters("ussync_email_confirmation_manual_verify", $text) . "</a>";							
						}
						else{
							
							$text = "<img src='" . plugin_dir_url(__FILE__) . "images/wrong_arrow.png' width=25 height=25>";
							$row .= "<a title=\"Validate User\" href=\"" . add_query_arg(array("user_id" => $user_id, "wp_nonce" => wp_create_nonce("ussync_email"), "ussync_confirm" => "true"), get_admin_url() . "users.php") . "\">" . apply_filters("ussync_email_confirmation_manual_verify", $text) . "</a>";
						}
						

						$text = "<img src='" . plugin_dir_url(__FILE__) . "images/send.png' width=25 height=25>";
						$row .= "<a title=\"Resend Validation Email\" href=\"" . add_query_arg(array("user_id" => $user_id, "wp_nonce" => wp_create_nonce("ussync_email"), "ussync_confirm" => "resend"), get_admin_url() . "users.php") . "\">" . apply_filters("ussync_email_confirmation_manual_verify", $text) . "</a>";						
					}
					else{
						
						$text = "<img src='" . plugin_dir_url(__FILE__) . "images/right_arrow.png' width=25 height=25>";
						$row .= "<a title=\"Unvalidate User\" href=\"" . add_query_arg(array("user_id" => $user_id, "wp_nonce" => wp_create_nonce("ussync_email"), "ussync_confirm" => "false"), get_admin_url() . "users.php") . "\">" . apply_filters("ussync_email_confirmation_manual_verify", $text) . "</a>";						
					}
					
				} 
				else {
					
					$row .= "Admin";
				}
			}
			
			return $row;
		}

		public function verify_user() {
			
			//var_dump(wp_verify_nonce($_GET["wp_nonce"], "ussync_email"));
			
			if(isset($_GET["user_id"]) && isset($_GET["wp_nonce"]) && wp_verify_nonce($_GET["wp_nonce"], "ussync_email") && isset($_GET["ussync_confirm"])) {
				
				if($_GET["ussync_confirm"] === 'true' || $_GET["ussync_confirm"] === 'false'){
					
					update_user_meta($_GET["user_id"], "ussync_email_verified", $_GET["ussync_confirm"]);
				}
				elseif($_GET["ussync_confirm"] === 'resend'){

					$user_id = intval($_GET['user_id']);
					
					$email_verified = get_user_meta(($user_id), "ussync_email_verified", TRUE);
					
					if( $email_verified !== 'true' ){
						
						$user = get_user_by("id", $user_id);
						
						$scret_code = md5( $user->user_email . time() );
						
						update_user_meta($user_id, "ussync_email_verifiedcode", $scret_code);
						
						$this->codeMailSender($user->user_email);
						
						echo '<div class="updated fade"><p>Email sent to '.$user->user_email.'</p></b></div>';						
					}
				}
			}
		}

		/**
		 * Main User_Session_Synchronizer_Email_Verification Instance
		 *
		 * Ensures only one instance of User_Session_Synchronizer_Email_Verification is loaded or can be loaded.
		 *
		 * @since 1.0.0
		 * @static
		 * @see User_Session_Synchronizer()
		 * @return Main User_Session_Synchronizer_Email_Verification instance
		 */
		public static function instance ( $parent ) {
			
			if ( is_null( self::$_instance ) ) {
				
				self::$_instance = new self( $parent );
			}
			return self::$_instance;
		} // End instance()

		/**
		 * Cloning is forbidden.
		 *
		 * @since 1.0.0
		 */
		public function __clone () {
			_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?' ), $this->parent->_version );
		} // End __clone()

		/**
		 * Unserializing instances of this class is forbidden.
		 *
		 * @since 1.0.0
		 */
		public function __wakeup () {
			_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?' ), $this->parent->_version );
		} // End __wakeup()

	}