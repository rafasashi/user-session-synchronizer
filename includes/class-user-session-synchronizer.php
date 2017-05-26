<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class User_Session_Synchronizer {

	/**
	 * The single instance of User_Session_Synchronizer.
	 * @var 	object
	 * @access  private
	 * @since 	1.0.0
	 */
	private static $_instance = null;

	/**
	 * Settings class object
	 * @var     object
	 * @access  public
	 * @since   1.0.0
	 */
	public $emailVerification = null;
	public $settings = null;
	public $sessionControl = null;

	/**
	 * The version number.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $_version;

	/**
	 * The token.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $_token;

	/**
	 * The main plugin file.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $file;

	/**
	 * The main plugin directory.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $dir;

	/**
	 * The plugin assets directory.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $assets_dir;

	/**
	 * The plugin assets URL.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $assets_url;

	/**
	 * Suffix for Javascripts.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $script_suffix;

	/**
	 * Constructor function.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	 
	public $key_num;
	public $secret_key;
	public $proto;
	 
	public function __construct ( $file = '', $version = '1.0.0' ) {

		$this->_version = $version;
		$this->_token = 'user-session-synchronizer';

		// Load plugin environment variables
		$this->file = $file;
		$this->dir = dirname( $this->file );
		$this->assets_dir = trailingslashit( $this->dir ) . 'assets';
		$this->assets_url = esc_url( trailingslashit( plugins_url( '/assets/', $this->file ) ) );

		$this->script_suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
		
		// set user ip
		
		$this->user_ip = $this->get_user_ip();
		
		// set user agent
		
		$this->user_agent = $_SERVER['HTTP_USER_AGENT'];
		
		// set secret key number
		
		$this -> key_num=1;
		
		if(isset($_GET['ussync-key'])){
			
			$this -> key_num=(int)trim($_GET['ussync-key']);
		}
		
		//get secret_key
		
		$this -> secret_key = get_option('ussync_secret_key_'.$this -> key_num);
		
		// get proto
		
		if( (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $_SERVER['SERVER_PORT'] == 443 ){
			  
			$this -> proto = 'https://';
		}
		else{
			
			$this -> proto = 'http://';
		}

		// register plugin activation hook
		
		register_activation_hook( $this->file, array( $this, 'install' ) );

		// Load frontend JS & CSS
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_styles' ), 10 );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ), 10 );

		// Load admin JS & CSS
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ), 10, 1 );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_styles' ), 10, 1 );

		// Load API for generic admin functions
		if ( is_admin() ) {
			
			$this->admin = new User_Session_Synchronizer_Admin_API();
		}

		// Handle login synchronization
		add_action( 'init', array( $this, 'synchronize_session' ), 0 );		
		
		// Handle localisation
		$this->load_plugin_textdomain();
		add_action( 'init', array( $this, 'load_localisation' ), 0 );
		
		// Handle profile updates
		add_action( 'user_profile_update_errors', array( $this, 'prevent_email_change'), 10, 3 );
		add_action( 'admin_init', array( $this, 'disable_user_profile_fields'));

	} // End __construct ()

	public function get_user_ip() {
		
		foreach (array('HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR') as $key){
			
			if (array_key_exists($key, $_SERVER) === true){
				
				foreach (array_map('trim', explode(',', $_SERVER[$key])) as $ip){
					
					if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false){
						
						return $ip;
					}
				}
			}
		}
	}	
	
	public function prevent_email_change( $errors, $update, $user ) {
	
		if( !empty($user->ID) ){
	
			$old = get_user_by('id', $user->ID);

			if( $user->user_email != $old->user_email   && (!current_user_can('create_users')) ){
				
				$user->user_email = $old->user_email;
			}
		}
	}
	
	public function disable_user_profile_fields() {
	 
		global $pagenow;
	 
		// apply only to user profile or user edit pages
		if ($pagenow!=='profile.php' && $pagenow!=='user-edit.php') {
			
			return;
		}
	 
		// do not change anything for the administrator
		if (current_user_can('administrator')) {
			
			return;
		}
	 
		add_action( 'admin_footer', array( $this,'disable_user_profile_fields_js' ));
	}
	 
	 
	/**
	 * Disables selected fields in WP Admin user profile (profile.php, user-edit.php)
	 */
	public function disable_user_profile_fields_js() {
		
		?>
			<script>
				jQuery(document).ready( function($) {
					var fields_to_disable = ['email', 'username'];
					for(i=0; i<fields_to_disable.length; i++) {
						if ( $('#'+ fields_to_disable[i]).length ) {
							$('#'+ fields_to_disable[i]).attr("disabled", "disabled");
							$('#'+ fields_to_disable[i]).after("<span class=\"description\"> " + fields_to_disable[i] + " cannot be changed.</span>");
						}
					}
				});
			</script>
		<?php
	}	
	
	public function synchronize_session(){
		
		// set user information
		
		$this->user_id = get_current_user_id();
		
		// check user verified
		
		if( current_user_can('administrator') ) {

			$this->user_verified = 'true';
		}
		else{
			
			$this->user_verified = get_user_meta( $this->user_id, "ussync_email_verified", TRUE);
		}
		
		// add cors header
		if(is_user_logged_in()){
			
			add_action( 'send_headers', array($this, 'add_cors_header') );
			add_action( 'send_headers', array($this, 'add_content_security_policy') );
		}		
		
		// synchronize sessions

		if( isset($_GET['action']) && $_GET['action']=='logout' ){
			
			$this->get_domains(true);
		}
		elseif( isset($_GET['ussync-status']) && $_GET['ussync-status']=='loggedin' ){
			
			echo 'User logged in!';
			exit;
		}
		elseif( is_user_logged_in() && isset($_GET['redirect_to']) ){
			
			if( !empty($_GET['reauth']) && $_GET['reauth'] == '1' ){
				
				echo 'Error accessing the current session...';			
			}
			else{

				wp_safe_redirect( trim( $_GET['redirect_to'] ) );
			}
			
			exit;
		}
		elseif( isset($_GET['ussync-token']) && isset($_GET['ussync-id']) && isset($_GET['ussync-ref']) ){

			//decrypted user_name
			
			$user_name = trim($_GET['ussync-id']);
			$user_name = $this->decrypt_uri($user_name);

			//decrypted user_name
			
			$user_ref = ($_GET['ussync-ref']);
			
			$user_ref = $this->decrypt_uri($user_ref);
			
			//decrypted user_email
			
			$user_email = trim($_GET['ussync-token']);
			$user_email = $this->decrypt_uri($user_email);
			
			//set user ID
			
			$user_email = sanitize_email($user_email);
			
			//get domain list
			
			$domain_list = get_option('ussync_domain_list_'.$this -> key_num);
			$domain_list = explode(PHP_EOL,$domain_list);
			
			//get valid domains

			$domains=[];
			
			foreach($domain_list as $domain){
				
				$domain = trim($domain);
				$domain = rtrim($domain,'/');
				$domain = preg_replace("(^https?://)", "", $domain);
				
				$domains[$domain]='';
			}
			
			//check referer
			
			$valid_referer=false;
			
			if(isset($domains[$user_ref])){
				
				$valid_referer=true;
			}			
			
			if($valid_referer===true){
				
				if(isset($_GET['ussync-status']) && $_GET['ussync-status']=='loggingout'){
					
					// Logout user
					
					if( $user = get_user_by('email', $user_email ) ){
						
						// get all sessions for user with ID
						$sessions = WP_Session_Tokens::get_instance($user->ID);

						// we have got the sessions, destroy them all!
						$sessions->destroy_all();	

						echo 'User logged out...';
						exit;					
					} 
					else{
						
						$this->decrypt_uri($_GET['ussync-token']);
						
						echo 'Error logging out...';
						exit;					
					}
				}			
				else{
					
					$current_user = wp_get_current_user();
					
					if(!is_user_logged_in()){			
						
						// check if the user exists
						
						if( !email_exists( $user_email ) ){
						
							$ussync_no_user = get_option('ussync_no_user_'.$this -> key_num);
						
							if($ussync_no_user=='register_suscriber'){
								
								// register new suscriber
								
								$user_data = array(
									'user_login'  =>  $user_name,
									'user_email'   =>  $user_email,
								);
														
								if( get_userdatabylogin($user_name) ){
									
									echo 'User name already exists!';
									exit;							
								}
								elseif( $user_id = wp_insert_user( $user_data ) ) {
									
									// update email status
									
									add_user_meta( $user_id, 'ussync_email_verified', 'true');
								}
								else{
									
									echo 'Error creating a new user!';
									exit;								
								}
							}
							else{
								
								echo 'This user doesn\'t exist...';
								exit;							
							}
						}
						
						// destroy current user session

						$sessions = WP_Session_Tokens::get_instance($current_user->ID);
						$sessions->destroy_all();	

						// get new user
						
						$user = get_user_by('email',$user_email);
						
						if( isset($user->ID) && intval($user->ID) > 0 ){
							
							//do the authentication
							
							clean_user_cache($user->ID);
							
							wp_clear_auth_cookie();
							wp_set_current_user( $user->ID );
							wp_set_auth_cookie( $user->ID , true, is_ssl());

							update_user_caches($user);
							
							if(is_user_logged_in()){
								
								//redirect after authentication
								
								//wp_safe_redirect( rtrim( get_site_url(), '/' ) . '/?ussync-status=loggedin');

								echo 'User '.$user->ID . ' logged in...';
								exit;
							}
						}
						else{
							
							echo 'Error logging in...';
							exit;						
						}					
					}
					elseif($current_user->user_email != $user_email){
						
						//wp_mail($dev_email, 'Debug user sync id ' . $current_user->ID . ' - ip ' . $this->user_ip . ' user_email: '. $current_user->user_email .' request email: '. $user_email.' $_SERVER: ' . print_r($_SERVER,true));
					
						echo 'Another user already logged in...';
						exit;
					}
					else{
						
						echo 'User already logged in...';
						exit;
					}
				}
			}
			else{

				echo 'Host not allowed to synchronize...';
				exit;				
			}
		}
		elseif(is_user_logged_in() && !isset($_GET['ussync-token']) && $this->user_verified === 'true'){
			
			//add footers
			
			if( is_admin() ) {
				
				add_action( 'admin_footer_text', array( $this, 'get_domains' ));
			}
			else{
				
				add_action( 'wp_footer', array( $this, 'get_domains' ));
			}			
		}
	}
	
	public function add_cors_header() {
		
		// Allow from valid origin
		/*
		if(isset($_SERVER['HTTP_ORIGIN'])) {
			
			//header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
			header("Access-Control-Allow-Origin: *");
			header('Access-Control-Allow-Credentials: true');
			header('Access-Control-Max-Age: 86400');    // cache for 1 day
		}

		// Access-Control headers are received during OPTIONS requests

		if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {

			if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD']))
				header("Access-Control-Allow-Methods: GET, POST, OPTIONS");         

			if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']))
				header("Access-Control-Allow-Headers:        {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");

			exit(0);
		}
		*/
	}
	
	public function add_content_security_policy() {
		
		if( (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $_SERVER['SERVER_PORT'] == 443 ){
			
			header("Content-Security-Policy: upgrade-insecure-requests");
		}
	}
	
	public function get_domains($loggingout=false){
		
		if($user = wp_get_current_user()){

			//get list of domains
			
			$domains = get_option('ussync_domain_list_'.$this -> key_num);
			$domains = explode(PHP_EOL,$domains);
			
			//get encrypted user name
			
			$user_name = $user->user_login;
			$user_name = $this->encrypt_uri($user_name);
			
			//get encrypted user email
			
			$user_email = $user->user_email;
			$user_email = $this->encrypt_uri($user_email);
			
			//get current domain
			
			$current_domain = get_site_url();
			$current_domain = rtrim($current_domain,'/');
			$current_domain = preg_replace("(^https?://)", "", $current_domain);
			
			//get encrypted user referer
			
			//$user_ref = $_SERVER['HTTP_HOST'];
			$user_ref = $current_domain;
			$user_ref = $this->encrypt_uri($user_ref);
			
			if(!empty($domains)){
				
				foreach($domains as $domain){
					
					$domain = trim($domain);
					$domain = rtrim($domain,'/');
					$domain = preg_replace("(^https?://)", "", $domain);

					if( $loggingout===true ){

						$url = $this -> proto . $domain . '/?ussync-token='.$user_email.'&ussync-key='.$this -> key_num.'&ussync-id='.$user_name.'&ussync-ref='.$user_ref.'&ussync-status=loggingout'.'&_' . time();

						$response = wp_remote_get( $url, array(
						
							'timeout'     => 5,
							'user-agent'  => $this -> user_agent,
							'headers'     => array(
							
								'X-Forwarded-For' => $this->user_ip
							),
						)); 						
					}
					elseif($current_domain != $domain){
						
						//output html
					
						//echo '<img class="ussync" src="' . $this -> proto . $domain . '/?ussync-token='.$user_email.'&ussync-key='.$this -> key_num.'&ussync-id='.$user_name.'&ussync-ref='.$user_ref.'&_' . time() . '" height="1" width="1" style="border-style:none;" >';								
						
						echo'<iframe class="ussync" src="' . $this -> proto . $domain . '/?ussync-token='.$user_email.'&ussync-key='.$this -> key_num.'&ussync-id='.$user_name.'&ussync-ref='.$user_ref.'&_' . time() . '" style="width:1px;height:1px;border-style:none;position:absolute;display:block;"></iframe>';
					}
				}
				
				if( $loggingout === true ){
					
					wp_logout();
					
					if(!empty($_GET['redirect_to'])){
						
						wp_safe_redirect( trim( $_GET['redirect_to'] ) );
					}
					else{
						
						wp_safe_redirect( wp_login_url() );
					}
					
					exit;
				}				
			}
		}
	}
	
	private function get_secret_iv(){
		
		//$secret_iv = md5( $this->user_agent . $this->user_ip );
		//$secret_iv = md5( $this->user_ip );
		$secret_iv = md5( 'another-secret' );
		
		return $secret_iv;
	}
	
	private function encrypt_str($string){
		
		$output = false;

		$encrypt_method = "AES-256-CBC";
		
		$secret_key = md5( $this -> secret_key );
		
		$secret_iv = $this->get_secret_iv();
		
		// hash
		$key = hash('sha256', $secret_key);
		
		// iv - encrypt method AES-256-CBC expects 16 bytes - else you will get a warning
		$iv = substr(hash('sha256', $secret_iv), 0, 16);

		$output = openssl_encrypt($string, $encrypt_method, $key, 0, $iv);
		$output = $this->base64_urlencode($output);

		return $output;
	}
	
	private function decrypt_str($string){
		
		$output = false;

		$encrypt_method = "AES-256-CBC";
		
		$secret_key = md5( $this->secret_key );
		
		$secret_iv = $this->get_secret_iv();

		// hash
		$key = hash( 'sha256', $secret_key);
		
		// iv - encrypt method AES-256-CBC expects 16 bytes - else you will get a warning
		$iv = substr( hash( 'sha256', $secret_iv ), 0, 16);

		$output = openssl_decrypt($this->base64_urldecode($string), $encrypt_method, $key, 0, $iv);

		return $output;
	}
	
	private function encrypt_uri($uri,$len=250,$separator='/'){
		
		$uri = wordwrap($this->encrypt_str($uri),$len,$separator,true);
		
		return $uri;
	}
	
	private function decrypt_uri($uri,$separator='/'){
		
		$uri = $this->decrypt_str(str_replace($separator,'',$uri));
		
		return $uri;
	}
	
	private function base64_urlencode($inputStr=''){

		return strtr(base64_encode($inputStr), '+/=', '-_,');
	}

	private function base64_urldecode($inputStr=''){

		return base64_decode(strtr($inputStr, '-_,', '+/='));
	}

	/**
	 * Wrapper function to register a new post type
	 * @param  string $post_type   Post type name
	 * @param  string $plural      Post type item plural name
	 * @param  string $single      Post type item single name
	 * @param  string $description Description of post type
	 * @return object              Post type class object
	 */
	public function register_post_type ( $post_type = '', $plural = '', $single = '', $description = '', $options = array() ) {

		if ( ! $post_type || ! $plural || ! $single ) return;

		$post_type = new User_Session_Synchronizer_Post_Type( $post_type, $plural, $single, $description, $options );

		return $post_type;
	}

	/**
	 * Wrapper function to register a new taxonomy
	 * @param  string $taxonomy   Taxonomy name
	 * @param  string $plural     Taxonomy single name
	 * @param  string $single     Taxonomy plural name
	 * @param  array  $post_types Post types to which this taxonomy applies
	 * @return object             Taxonomy class object
	 */
	public function register_taxonomy ( $taxonomy = '', $plural = '', $single = '', $post_types = array(), $taxonomy_args = array() ) {

		if ( ! $taxonomy || ! $plural || ! $single ) return;

		$taxonomy = new User_Session_Synchronizer_Taxonomy( $taxonomy, $plural, $single, $post_types, $taxonomy_args );

		return $taxonomy;
	}

	/**
	 * Load frontend CSS.
	 * @access  public
	 * @since   1.0.0
	 * @return void
	 */
	public function enqueue_styles () {
		
		wp_register_style( $this->_token . '-frontend', esc_url( $this->assets_url ) . 'css/frontend.css', array(), $this->_version );
		wp_enqueue_style( $this->_token . '-frontend' );
	} // End enqueue_styles ()

	/**
	 * Load frontend Javascript.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function enqueue_scripts () {
		wp_register_script( $this->_token . '-frontend', esc_url( $this->assets_url ) . 'js/frontend' . $this->script_suffix . '.js', array( 'jquery' ), $this->_version );
		wp_enqueue_script( $this->_token . '-frontend' );
	} // End enqueue_scripts ()

	/**
	 * Load admin CSS.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function admin_enqueue_styles ( $hook = '' ) {
		wp_register_style( $this->_token . '-admin', esc_url( $this->assets_url ) . 'css/admin.css', array(), $this->_version );
		wp_enqueue_style( $this->_token . '-admin' );
	} // End admin_enqueue_styles ()

	/**
	 * Load admin Javascript.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function admin_enqueue_scripts ( $hook = '' ) {
		wp_register_script( $this->_token . '-admin', esc_url( $this->assets_url ) . 'js/admin' . $this->script_suffix . '.js', array( 'jquery' ), $this->_version );
		wp_enqueue_script( $this->_token . '-admin' );
	} // End admin_enqueue_scripts ()

	/**
	 * Load plugin localisation
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function load_localisation () {
		load_plugin_textdomain( 'user-session-synchronizer', false, dirname( plugin_basename( $this->file ) ) . '/lang/' );
	} // End load_localisation ()

	/**
	 * Load plugin textdomain
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function load_plugin_textdomain () {
	    $domain = 'user-session-synchronizer';

	    $locale = apply_filters( 'plugin_locale', get_locale(), $domain );

	    load_textdomain( $domain, WP_LANG_DIR . '/' . $domain . '/' . $domain . '-' . $locale . '.mo' );
	    load_plugin_textdomain( $domain, false, dirname( plugin_basename( $this->file ) ) . '/lang/' );
	} // End load_plugin_textdomain ()

	/**
	 * Main User_Session_Synchronizer Instance
	 *
	 * Ensures only one instance of User_Session_Synchronizer is loaded or can be loaded.
	 *
	 * @since 1.0.0
	 * @static
	 * @see User_Session_Synchronizer()
	 * @return Main User_Session_Synchronizer instance
	 */
	public static function instance ( $file = '', $version = '1.0.0' ) {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self( $file, $version );
		}
		return self::$_instance;
	} // End instance ()

	/**
	 * Cloning is forbidden.
	 *
	 * @since 1.0.0
	 */
	public function __clone () {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?' ), $this->_version );
	} // End __clone ()

	/**
	 * Unserializing instances of this class is forbidden.
	 *
	 * @since 1.0.0
	 */
	public function __wakeup () {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?' ), $this->_version );
	} // End __wakeup ()

	/**
	 * Installation. Runs on activation.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function install () {
		$this->_log_version_number();
	} // End install ()

	/**
	 * Log the plugin version number.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	private function _log_version_number () {
		update_option( $this->_token . '_version', $this->_version );
	} // End _log_version_number ()
}