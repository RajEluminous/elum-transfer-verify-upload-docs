<?php 
/**
 * Plugin Name: Elum Verify Upload Docs
 * Plugin URI: https://eluminoustechnologies.com/
 * Description: This plugin shows the list of documents uploaded by user for verification purpose..
 * Version: 1.0.0
 * Text Domain: elum-transfer-verify-upload-docs
 * Author: Rajendra Mahajan
 * Author URI: https://eluminoustechnologies.com/
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 */
  
 // Plugin directory url.
 define('ETVUDS_URL', WP_PLUGIN_URL."/".dirname( plugin_basename( __FILE__ ) ) );
 
    /**
     * For development: To display error, set WP_DEBUG to true.
     * In production: set it to false
    */
 define('WP_DEBUG',true);
 
 // Get absolute path 
 if ( !defined('ETVUDS_ABSPATH'))
    define('ETVUDS_ABSPATH', dirname(__FILE__) . '/');

 // Get absolute path 
if ( !defined('ABSPATH'))
    define('ABSPATH', dirname(__FILE__) . '/');

/**
 *  Current plugin version.
 */
 if ( ! defined( 'ETVUDS_VER' ) ) {
	define( 'ETVUDS_VER', '1.0.0' );
 }
  
 define('ETVUDS_TEMPLATES',ETVUDS_ABSPATH.'templates');
 define('ETVUDS_PAGE_TITLE','Verify User Uploaded Documents');
 


add_action( 'template_redirect', 'wpse_inspect_page_id' );
function wpse_inspect_page_id() {
    $page_object = get_queried_object();
   // var_dump($page_object);

    $page_id = get_queried_object_id();
    //echo $page_id;
	
}

 // Main Class
 class ElumTransferVerifyUploadDocs {
	
	var $etvuds_page_menu;
	
	// Obj
	var $fbauth;
	
	// facebook app id
	var $etvuds_fbappid;
	
	// Flag to maintain, while roaming in admin area
	var $etvuds_is_allowed_to_skip;
	
	// for user info
	var $etvuds_uid;
	var $etvuds_user_login;
	var $etvuds_display_name;
	var $etvuds_user_email;
	 
		
	function __construct() {	
		global $wpdb;
		global $wp;
       		 
		//Initial loading				 		 
		add_action('admin_init',array($this,'init'),0);	 
		add_action('admin_menu', array($this, 'etvuds_admin_menu')); 		  
	}	
		 
	// initial processing	
	function init() {
		// if session is not start, then start it.
		if(!session_id()) {
			session_start();
		} 
		$this->load(); 		
	} 
	
	function load() {
		
		// check for document approval request
		if($_GET['dusrid']>0 && $_GET['dsts']>=0)
		  $this->etvuds_processDocStatus(); 
	}
	// processing document approval request
	function etvuds_processDocStatus(){
	
	 if($_GET['dsts']==1)
	   update_user_meta($_GET['dusrid'],'usr_vdoc_isapproved',0);
     else
	   update_user_meta($_GET['dusrid'],'usr_vdoc_isapproved',1);     
	}
	 
	// add menu to admin
	function etvuds_admin_menu() {
		add_menu_page('Verify Uploaded Documents','Verify Uploaded Documents','administrator', __FILE__,array($this,'etvuds_admin_uploaded_docs_page'),'',100);   		 
    }
	
	// Display uploaded document listing, include template file
	function etvuds_admin_uploaded_docs_page() {	
		global $wpdb;
						
		// get all users meta information	
		$fivesdrafts = $wpdb->get_results("SELECT * FROM $wpdb->usermeta");
		$rowcount = $wpdb->num_rows;
			
		$arrUserMeta = array();
		/*foreach ($fivesdrafts as $fivesdraft) { 
			if(!$arrUserMeta[$fivesdraft->user_id]['id']) {
				$arrUserMeta[$fivesdraft->user_id]['id'] = $fivesdraft->user_id;
			}
			else { 	
				$arrUserMeta[$fivesdraft->user_id][$fivesdraft->meta_key] = $fivesdraft->meta_value;				 
			}	
		}
		*/ 
		$users_count = get_users( array('fields' => array('ID'), 
								  'meta_key' => 'usr_vdoc_isapproved' 
								  ));
			//----- for pagination ---------//
		$pagenum = isset( $_GET['pagenum'] ) ? absint( $_GET['pagenum'] ) : 1;      

        $limit = 10; // number of rows in page
        $offset = ( $pagenum - 1 ) * $limit;
        $total = count($users_count);
        $num_of_pages = ceil( $total / $limit );
		//---------- end pagination -------//
		
		$users = get_users( array('fields' => array('ID'), 
								  'meta_key' => 'usr_vdoc_isapproved', 
								  'offset' => $offset,
								  'number' => $limit
								  ));
		 	
		foreach($users as $user_id){
			$usr_meta = get_user_meta ( $user_id->ID);
			//print_r($usr_meta);
			$arrUserMeta[$user_id->ID]['id'] = $user_id->ID; 		
			$arrUserMeta[$user_id->ID]['first_name'] = current($usr_meta['first_name']);
			$arrUserMeta[$user_id->ID]['last_name'] = current($usr_meta['last_name']);
			$arrUserMeta[$user_id->ID]['usr_vdoc_img_passport'] = current($usr_meta['usr_vdoc_img_passport']);
			$arrUserMeta[$user_id->ID]['usr_vdoc_img_drivinglicense'] = current($usr_meta['usr_vdoc_img_drivinglicense']);
			$arrUserMeta[$user_id->ID]['usr_vdoc_img_idcard'] = current($usr_meta['usr_vdoc_img_idcard']);
			$arrUserMeta[$user_id->ID]['usr_vdoc_img_bill'] = current($usr_meta['usr_vdoc_img_bill']);
			$arrUserMeta[$user_id->ID]['usr_vdoc_isapproved'] = current($usr_meta['usr_vdoc_isapproved']);			
		} 
		 
		
		 $page_links = paginate_links( array(
            'base' => add_query_arg( 'pagenum', '%#%' ),
            'format' => '',
            'prev_text' => __( '&laquo;', 'text-domain' ),
            'next_text' => __( '&raquo;', 'text-domain' ),
            'total' => $num_of_pages,
            'current' => $pagenum
        ) );
		$page_pagination_nav = "";
        if ( $page_links ) {
            $page_pagination_nav = '<div class="tablenav" style="width: 99%; float:right"><div class="tablenav-pages" style="margin: 1em 0">' . $page_links . '</div></div>';
        }
	
		
		require_once(ETVUDS_TEMPLATES . '/listing_template.php');
	}			

	// To check and return valid img path
	function eluTransGetImgPath($edocimgpath) {
		 
		if(file_exists(str_replace(site_url(),'..',$edocimgpath))) {
			return $edocimgpath;
		} else {
			return ETVUDS_URL.'/assets/noimg.png';
		}
	}
	
	
 } // Classe
 
 // Call class
 new ElumTransferVerifyUploadDocs();
 
?>