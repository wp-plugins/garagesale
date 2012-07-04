<?php
/**
 * @package GarageSale
 * @version 1.1
 * @brief uninstaller for wordpress garagesale plugin
 * @date 20120420 wordpress@sprossenwanne.at
*                add license \n
 */

/*
  Copyright 2012 Leo Eibler (http://www.eibler.at)

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

if( !defined( 'ABSPATH') && !defined('WP_UNINSTALL_PLUGIN') ) {
	die( "invalid command" );
}

// suppress the startup of all hooks
define( 'GARAGESALE_UNINSTALL', 1 );

// we need this to get all paths for deleting user content
require_once( plugin_dir_path( __FILE__ ).'garagesale.php' );

if( isset($garageSale) ) {
	$garageSale->uninstall();
}

// done
