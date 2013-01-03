<?php
/**
 * @package GarageSale
 * @version 1.2.1
 * @date 20120401 wordpress@sprossenwanne.at
 *                finalize plugin \n
 * @date 20120420 wordpress@sprossenwanne.at
 *                version change from 1.0 -> 1.1 \n
 *                modify type of price from float to varchar \n
 *                add license \n
 * @date 20130102 wordpress@sprossenwanne.at
 *                version change from 1.1 -> 1.2 \n
 *                bugfix remove prepare calls with only 1 argument to work with wordpress 3.5 \n
 * @date 20130103 wordpress@sprossenwanne.at
 *                version change from 1.2 -> 1.2.1 \n
 *                bugfix use $_REQUEST instead of $_GET to work with wordpress 3.5 \n
 *                add define GARAGESALE_ITEMS_PER_PAGE in garagesale.php as single point of configuration \n
 *                bugfix use wp_get_image_editor() instead of wp_create_thumbnail() in wordpress version greater or equal than 3.5 \n
 *                improve error handling (use trigger_error) if something was wrong while uploading image \n
 */
/*
Plugin Name: Garage Sale
Plugin URI: http://www.eibler.at/garagesale
Description: This plugin is a lightweight solution to put a kind of garage sale on your wordpress page. Users can put their stuff with a picture, description, price and contact on a wordpress site. The users are wordpress users with access right Subscriber (so every registered user can use the garage sale). Put the string "[GarageSaleList]" on any page or article post where you want to display the list of sale items.
This Plugin creates an own subfolder within the upload folder for the pictures.
Author: Leo Eibler
Version: 1.2.1
Author URI: http://www.eibler.at
Text Domain: garagesale
*/

/*
  Copyright 2012-2013 Leo Eibler (http://www.eibler.at)

   Licensed under the Apache License, Version 2.0 (the "License");
   you may not use this file except in compliance with the License.
   You may obtain a copy of the License at

       http://www.apache.org/licenses/LICENSE-2.0

   Unless required by applicable law or agreed to in writing, software
   distributed under the License is distributed on an "AS IS" BASIS,
   WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
   See the License for the specific language governing permissions and
   limitations under the License.
*/

// thanx goes to: 
//   http://www.devlounge.net/publik/Devlounge-How-to-Write-a-Wordpress-Plugin.pdf
//   http://wordpress.org/extend/plugins/custom-list-table-example/
//   http://www.webmaster-source.com/2010/01/08/using-the-wordpress-uploader-in-your-plugin-or-theme/

/*
** @brief GARAGESALE_VERSION: version number of current plugin (can be used for plugin upgrade procedure in future)
*/
define( 'GARAGESALE_VERSION', '1.2.1' );

/*
** @brief GARAGESALE_ITEMS_PER_PAGE: the number of items per page displayed in the tables
*/
define( 'GARAGESALE_ITEMS_PER_PAGE', 5 );

/*
** @brief GARAGESALE_TABLE_PREFIX: the prefix for all garagesale tables
*/
define( 'GARAGESALE_TABLE_PREFIX', 'garagesale' );

/*
** @brief GARAGESALE_TEMPLATE_DIR: the directory relative to plugin path where all html templates located
** see function GarageSalePlugin::inc()
*/
define( 'GARAGESALE_TEMPLATE_DIR', 'templates' );

if( !function_exists('wp_upload_dir') ) {
	require_once( ABSPATH.'wp-includes/functions.php' );
}
$WPUploadDir = wp_upload_dir();
if( isset($WPUploadDir) ) {
	$tmpGARAGESALE_UPLOAD_DIR = $WPUploadDir['basedir'];
	$tmpGARAGESALE_UPLOAD_URL = $WPUploadDir['baseurl'];
}

/*
** @brief GARAGESALE_UPLOAD_DIR: use the default wp_upload as base and create subdirectory with plugin name
*/
define( 'GARAGESALE_UPLOAD_DIR', $tmpGARAGESALE_UPLOAD_DIR.'/garagesale' );

/*
** @brief GARAGESALE_UPLOAD_URL: use the default wp_upload as base and use own plugin subdirectory 
*/
define( 'GARAGESALE_UPLOAD_URL', $tmpGARAGESALE_UPLOAD_URL.'/garagesale' );

/*
** @brief GARAGESALE_IMAGE_SIZE: image size for uploaded pictures (used in table and detail window)
*/
define( 'GARAGESALE_IMAGE_SIZE', 220 );

/*
** @brief GARAGESALE_SCRIPT_DIR: the directory relative to plugin path where all javascripts located
*/
define( 'GARAGESALE_SCRIPT_DIR', 'js' );

/*
** @brief GARAGESALE_SCRIPT_DIR: the directory relative to plugin path where all javascripts located
*/
define( 'GARAGESALE_STYLE_DIR', 'css' );


/*
** @brief GARAGESALE_PHP_DIR: the directory relative to plugin path where all php methods and files are located used for this plugin
** see function GarageSalePlugin::inc()
*/
define( 'GARAGESALE_PHP_DIR', 'php' );

/*
** @brief GARAGESALE_ELEMENT_ACTIVE: statuscode for element active
*/
define( 'GARAGESALE_ELEMENT_ACTIVE', 0 );

/*
** @brief GARAGESALE_ELEMENT_SOLD: statuscode for element sold
*/
define( 'GARAGESALE_ELEMENT_SOLD', 2 );

if( !function_exists('wp_create_thumbnail') ) {
	require_once(ABSPATH . 'wp-admin/includes/image.php');
	require_once(ABSPATH . 'wp-includes/media.php');
}

if( !class_exists('GarageSalePlugin') ) {
class GarageSalePlugin {
	
	protected $table_stuff = null;
	protected $table_categories = null;
	
	protected $adminOptionsName = 'GarageSalePluginAdminOptions';
	
	/*
	** @brief current record (e.g. post data set or retrieved data from table)
	*/
	protected $data = array();

	public function GarageSalePlugin() {
		global $wpdb;
		$this->table_stuff = $wpdb->prefix.GARAGESALE_TABLE_PREFIX.'_stuff';
		$this->table_categories = $wpdb->prefix.GARAGESALE_TABLE_PREFIX.'_categories';
	}
	
	/*
	** @brief called when plugin is activated
	*/
	public function init() {
		$sql = "CREATE TABLE ".$this->table_stuff." (
		  id bigint(20) NOT NULL AUTO_INCREMENT,
		  post_author bigint(20) DEFAULT 0 NOT NULL,
		  post_date datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
		  category int DEFAULT 0 NOT NULL,
		  name varchar(120) DEFAULT '' NOT NULL,
		  description text DEFAULT '' NOT NULL,
		  picture text DEFAULT '' NOT NULL,
		  picture_original text DEFAULT '' NOT NULL,
		  price varchar(120) DEFAULT '' NOT NULL,
		  contact text DEFAULT '' NOT NULL,
		  status int DEFAULT ".GARAGESALE_ELEMENT_ACTIVE." NOT NULL,
		  UNIQUE KEY id (id),
		  KEY category (category),
		  KEY price (price),
		  KEY status (status),
		  KEY post_author (post_author),
		  KEY post_date (post_date)
		);";
		dbDelta($sql);
		add_option( 'garagesale_version', GARAGESALE_VERSION );
		if( !is_dir(GARAGESALE_UPLOAD_DIR) ) {
			@mkdir( GARAGESALE_UPLOAD_DIR );
		}
		if( !file_exists(GARAGESALE_UPLOAD_DIR.'/index.php') ) {
			@file_put_contents( GARAGESALE_UPLOAD_DIR.'/index.php', "<?php // silence ?>" );
		}
	}
	
	/*
	** @brief on uninstalling plugin delete all uploaded files, custom upload folder and database records
	*/
	public function uninstall() {
		global $wpdb;
		
		// remove uploaded files
		$garageSaleUploadFiles = scandir( GARAGESALE_UPLOAD_DIR );
		if( is_array($garageSaleUploadFiles) ) {
			foreach( $garageSaleUploadFiles as $gsFile ) {
				@unlink( GARAGESALE_UPLOAD_DIR.'/'.$gsFile );
			}
		}
		@rmdir( GARAGESALE_UPLOAD_DIR );
		
		// remove table
		$wpdb->query( $wpdb->prepare( "DROP TABLE ".$this->table_stuff ) );
	}
	
	public function theContent($content = '') {
		if( stripos( $content, '[GarageSaleList]' ) !== false ) {
			$this->incOnce( 'garagesale_list_table4user.php', GARAGESALE_PHP_DIR );
			$gsListTable = new GarageSale_List_Table4User();
			$gsListTable->setGarageSale( $this );
			$gsListTable->setList( 'active' );
			$gsListTable->setTableStuff( $this->table_stuff );
			$gsListTable->prepare_items();
			$GarageSaleList = $gsListTable->display();
			$content = str_replace( '[GarageSaleList]', $GarageSaleList, $content );
		}
		return $content;
	}
	
	public function addScripts() {
		wp_register_style('garagesale-css', plugin_dir_url( __FILE__ ).GARAGESALE_STYLE_DIR.'/garagesale.css');
		wp_enqueue_style('garagesale-css');
	}
	
	/*
	** @brief displays special content only for administrators
	*/
	public function printAdminAdminListAll() {
		$this->inc( 'printAdminAdminListAll.tpl.php' );
	}
	
	/*
	** @brief displays the content for all user to administer their stuff
	*/
	public function printAdminUsersPage() {
		$this->inc( 'printAdminUsersPage.tpl.php' );
	}
	
	/*
	** @brief displays the content for list with filter active
	*/
	public function printAdminUsersListActive() {
		$this->inc( 'printAdminUsersListActive.tpl.php' );
	}
	
	/*
	** @brief displays the content for list with filter sold
	*/
	public function printAdminUsersListSold() {
		$this->inc( 'printAdminUsersListSold.tpl.php' );
	}
	
	/*
	** @brief displays the content for list with filter all
	*/
	public function printAdminUsersListAll() {
		$this->inc( 'printAdminUsersListAll.tpl.php' );
	}
	
	/*
	** @brief displays the content for list with filter all
	*/
	public function getList4User() {
		$this->inc( 'getList4User.tpl.php' );
	}
	
	/*
	** @brief displays the content for all user to add new item
	*/
	public function printAdminUsersNewItemPage() {
		global $wpdb;
		$this->data = array();
		$this->data['id'] = 0;
		$this->data['post_author'] = get_current_user_id();
		$requestType = 0;
		if( isset( $_POST['Btn_Submit'] ) ) {
			if( isset( $_POST['id'] ) ) {
				$this->data['id'] = sprintf( '%d', $_POST['id'] );
			}
			if( isset( $_POST['name'] ) ) {
				$this->data['name'] = $_POST['name'];
			}
			if( isset( $_POST['description'] ) ) {
				$this->data['description'] = $_POST['description'];
			}
			if( isset( $_POST['picture'] ) ) {
				$this->data['picture'] = $_POST['picture'];
			}
			if( isset( $_POST['contact'] ) ) {
				$this->data['contact'] = $_POST['contact'];
			}
			if( isset( $_POST['price'] ) ) {
				$testprice = str_replace( ',', '.', $_POST['price'] );
				if( is_numeric( $testprice ) ) {
					$this->data['price'] = sprintf( '%.2f', $testprice );
				} else {
					$this->data['price'] = $_POST['price'];
				}
			}
			if( isset( $_POST['status'] ) && is_numeric($_POST['status']) ) {
				$this->data['status'] = $_POST['status'];
			}
			// POST Request
			$requestType = 1;
			if( !empty($_FILES['uploadfile']['name']) && ( $_FILES['uploadfile']['size'] != 0 ) ) {
				$tempname = $_FILES['uploadfile']['tmp_name'];
				$origfilename = $_FILES['uploadfile']['name'];
				$i=0;
				do {
					$newUploadFile = $i.'_'.$this->data['post_author'].'_'.$origfilename;
					$i++;
				} while( file_exists(GARAGESALE_UPLOAD_DIR.'/'.$newUploadFile) );
				$ret = @move_uploaded_file( $tempname, GARAGESALE_UPLOAD_DIR.'/'.$newUploadFile );
				if( !$ret ) {
					trigger_error( "GARAGESALE ERROR: something was wrong while uploading file '".$origfilename."'", E_USER_ERROR );
				} else {
					$this->data['picture_original'] = GARAGESALE_UPLOAD_URL.'/'.$newUploadFile;
					global $wp_version;
					$newfile = '';
					if( version_compare( $wp_version, '3.5', '>=' ) ) {
						$gs_image = wp_get_image_editor( GARAGESALE_UPLOAD_DIR.'/'.$newUploadFile );
						if ( ! is_wp_error( $gs_image ) ) {
							$gs_image->resize( GARAGESALE_IMAGE_SIZE, GARAGESALE_IMAGE_SIZE, false );
							$tempName = GARAGESALE_UPLOAD_DIR.'/resizetemp_'.time().'_'.$newUploadFile;
							$gs_image_res = $gs_image->save( $tempName );
							if( is_array($gs_image_res) && isset($gs_image_res['path']) && isset($gs_image_res['file']) && isset($gs_image_res['width']) && isset($gs_image_res['height']) ) {
								$gs_file_extpos = strrpos( $newUploadFile, '.' );
								$newUploadFile2 = substr( $newUploadFile, 0, $gs_file_extpos ).'-'.$gs_image_res['width'].'x'.$gs_image_res['height'].substr( $newUploadFile, $gs_file_extpos );
								@rename( $tempName, GARAGESALE_UPLOAD_DIR.'/'.$newUploadFile2 );
								$newfile = GARAGESALE_UPLOAD_DIR.'/'.$newUploadFile2;
							} else {
								trigger_error( "GARAGESALE ERROR: cannot resize or save uploaded file '".GARAGESALE_UPLOAD_DIR.'/'.$newUploadFile."' using wp_get_image_editor", E_USER_ERROR );
							}
						}
					} else {
						$newfile = wp_create_thumbnail( GARAGESALE_UPLOAD_DIR.'/'.$newUploadFile, GARAGESALE_IMAGE_SIZE );
					}
					if( file_exists( $newfile ) ) {
						$this->data['picture'] = GARAGESALE_UPLOAD_URL.'/'.basename($newfile);
					} else {
						trigger_error( "GARAGESALE ERROR: resized file cannot be found at '".GARAGESALE_UPLOAD_DIR.'/'.$newUploadFile."'", E_USER_ERROR );
					}
				}
			}
		} else {
			if( isset($_REQUEST['id']) ) {
				$this->data['id'] = sprintf( '%d', $_REQUEST['id'] );
			}
		}
		if( $this->data['id'] > 0 ) {
			$rawData = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM ".$this->table_stuff." WHERE id = %d", $this->data['id'] ), ARRAY_A, 0 );
			// only the user itself or an administrator can change this record
			if( GarageSalePlugin_get_current_user_role() != 'administrator' && 
				( isset($rawData['post_author']) && $this->data['post_author'] != $rawData['post_author'] ) ) {
				$this->error( "you are not valid to edit this record!" );
				return false;
			} else {
				if( $requestType == 0 && isset($rawData['id']) ) {
					// if GET request and record found use this (it's an update request)
					$this->data = $rawData;
				} else 
				if( $requestType == 1 && isset($rawData['id']) ) {
					// on update load the post_date from record to display it on the page
					$this->data['post_date'] = $rawData['post_date'];
					// save post_author (if admin edits the record)
					$this->data['post_author'] = $rawData['post_author'];
				}
			}
		}
		if( empty($this->data['name']) ) {
			echo '<div class="updated"><p>'.__('Please fill in a title','garagesale').'</p></div>';
		} else
		if( empty($this->data['contact']) ) {
			echo '<div class="updated"><p>'.__('Please fill in a contact (example: telephone number)','garagesale').'</p></div>';
		} else {
			if( isset($this->data['id']) && $this->data['id'] > 0 ) {
				// update
				$wpdb->update( $this->table_stuff, $this->data, array( 'id' => $this->data['id'] ) ); 
			} else {
				// create
				$this->data['post_date'] = date('Y-m-d H:i:s');
				$wpdb->insert( $this->table_stuff, $this->data ); 
				$this->data['id'] = $wpdb->insert_id;
			}
		}
		$this->inc( 'printAdminUsersNewItemPage.tpl.php' );
	}
	
	/*
	** @brief deletes element with $id from table and checks if current user is owner of the element or has role administrator
	*/
	public function deleteElement( $id ) {
		global $wpdb;
		$current_user = get_current_user_id();
		$rawData = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM ".$this->table_stuff." WHERE id = %d", $id ), ARRAY_A, 0 );
		if( GarageSalePlugin_get_current_user_role() != 'administrator' &&
			( isset($rawData['post_author']) && $current_user != $rawData['post_author'] ) ) {
			$this->error( "you are not valid to delete this record!" );
			return false;
		} else {
			$wpdb->query( $wpdb->prepare( "DELETE FROM ".$this->table_stuff." WHERE id = %d", $id ) );
		}
		return true;
	}
	
	/*
	** @brief sets element with $id to sold and checks if current user is owner of the element or has role administrator
	*/
	public function soldElement( $id ) {
		global $wpdb;
		$current_user = get_current_user_id();
		$rawData = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM ".$this->table_stuff." WHERE id = %d", $id ), ARRAY_A, 0 );
		if( GarageSalePlugin_get_current_user_role() != 'administrator' && 
			( isset($rawData['post_author']) && $current_user != $rawData['post_author'] ) ) {
			$this->error( "you are not valid to set this record to sold!" );
			return false;
		} else {
			$wpdb->query( $wpdb->prepare( "UPDATE ".$this->table_stuff." SET status = ".GARAGESALE_ELEMENT_SOLD." WHERE id = %d", $id ) );
		}
		return true;
	}
	
	public function dashboardWidget() {
		$this->inc( 'printAdminDashboardOverview.tpl.php' );
	}
	
	/*
	** @brief is called from templates via $this->inc( file ) to include other files from plugin path
	** @param $file	filename to include
	** @param $relativeDir	relative directory where the file to include; default=GARAGESALE_TEMPLATE_DIR; alternative: GARAGESALE_PHP_DIR
	*/
	protected function inc( $file, $relativeDir = GARAGESALE_TEMPLATE_DIR ) {
		if( file_exists( plugin_dir_path( __FILE__ ).$relativeDir.'/'.$file ) ) {
			include( plugin_dir_path( __FILE__ ).$relativeDir.'/'.$file );
		} else {
			$this->error( "cannot access include file '".$file."' from directory '".$relativeDir."'" );
		}
	}
	
	/*
	** @brief is called from templates via $this->incRet( file ) to include other files from plugin path
	** @param $file	filename to include
	** @param $relativeDir	relative directory where the file to include; default=GARAGESALE_TEMPLATE_DIR; alternative: GARAGESALE_PHP_DIR
	*/
	protected function incRet( $file, $relativeDir = GARAGESALE_TEMPLATE_DIR ) {
		if( file_exists( plugin_dir_path( __FILE__ ).$relativeDir.'/'.$file ) ) {
			return plugin_dir_path( __FILE__ ).$relativeDir.'/'.$file;
		} else {
			$this->error( "cannot access include file '".$file."' from directory '".$relativeDir."'" );
		}
	}
	
	/*
	** @brief is called from templates via $this->inc( file ) to include other files from plugin path but only once
	** @param $file	filename to include
	** @param $relativeDir	relative directory where the file to include; default=GARAGESALE_TEMPLATE_DIR; alternative: GARAGESALE_PHP_DIR
	*/
	protected function incOnce( $file, $relativeDir = GARAGESALE_TEMPLATE_DIR ) {
		if( file_exists( plugin_dir_path( __FILE__ ).$relativeDir.'/'.$file ) ) {
			include_once( plugin_dir_path( __FILE__ ).$relativeDir.'/'.$file );
		} else {
			$this->error( "cannot access include file '".$file."' from directory '".$relativeDir."'" );
		}
	}
	
	/*
	** @brief error message function
	*/
	protected function error( $msg ) {
		echo $msg;
	}
}
} // end if( !class_exists('GarageSalePlugin') ) {


if( !function_exists('dbDelta') ) {
	require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
}
if( !function_exists('get_current_user_id') ) {
	require_once(ABSPATH . 'wp-includes/user.php');
}

if( class_exists('GarageSalePlugin') ) {
	$garageSale = new GarageSalePlugin();
}

if( !function_exists("GarageSalePlugin_get_current_user_role") ) {
	function GarageSalePlugin_get_current_user_role() {
		global $wp_roles;
		$current_user = wp_get_current_user();
		$roles = $current_user->roles;
		$role = array_shift($roles);
		return $role;
	}
}

if( !function_exists("GarageSaleList") ) {
	function GarageSaleList() {
		global $garageSale;
		$garageSale->getList4User();
	}
}

if( !function_exists("GarageSaleDashboardWidget") ) {
	function GarageSaleDashboardWidget() {
		global $garageSale;
		wp_add_dashboard_widget('garagesale_dashboard_widget', __('GarageSale Overview','garagesale'), array(&$garageSale, 'dashboardWidget'));
	}
}


if( !function_exists("GarageSalePlugin_ap") ) {
	function GarageSalePlugin_ap() {
		global $garageSale;
		
		if( !isset($garageSale) ) {
			return;
		}
		if( function_exists('add_options_page') && GarageSalePlugin_get_current_user_role() == 'administrator' ) {
			add_options_page( __('Garage Sale Admin','garagesale'), __('Garage Sale Admin','garagesale'), 'read', basename(__FILE__), array(&$garageSale, 'printAdminAdminListAll') );
		}
		if( function_exists('add_submenu_page') ) {
			add_submenu_page( 'index.php', __('GarageSale List Active','garagesale'), __('GarageSale List Active','garagesale'), 
					'read', 'printAdminUsersListActive', array(&$garageSale, 'printAdminUsersListActive'));
			add_submenu_page( 'index.php', __('GarageSale List Sold','garagesale'), __('GarageSale List Sold','garagesale'), 
					'read', 'printAdminUsersListSold', array(&$garageSale, 'printAdminUsersListSold'));
			add_submenu_page( 'index.php', __('GarageSale List All','garagesale'), __('GarageSale List All','garagesale'), 
					'read', 'printAdminUsersListAll', array(&$garageSale, 'printAdminUsersListAll'));
			add_submenu_page( 'index.php', __('GarageSale Add Item','garagesale'), __('GarageSale Add Item','garagesale'), 'read', 'printAdminUsersNewItemPage', array(&$garageSale, 'printAdminUsersNewItemPage'));
		}
	}
}

if( isset($garageSale) && !defined('GARAGESALE_UNINSTALL') ) {
	add_action( 'activate_garagesale/garagesale.php', array(&$garageSale, 'init'));
	add_action( 'admin_menu', 'GarageSalePlugin_ap' );
	add_filter( 'the_content', array(&$garageSale, 'theContent'));
    add_action( 'wp_enqueue_scripts', array(&$garageSale, 'addScripts') );
    add_action( 'admin_print_scripts', array(&$garageSale, 'addScripts') );
	add_action( 'wp_dashboard_setup', 'GarageSaleDashboardWidget' );
	$plugin_dir = basename(dirname(__FILE__));
	load_plugin_textdomain( 'garagesale', false, $plugin_dir );
}
