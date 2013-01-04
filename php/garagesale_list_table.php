<?php
/*
Plugin Name: Garage Sale List Table
Plugin URI: http://www.eibler.at/garagesale
Description: The GarageSale List Table for Displaying elements (based on Custom List Table Example Version 1.1. from Matt Van Andel - see http://www.mattvanandel.com)
Version: 1.2
Author: Matthew Van Andel - modified by Leo Eibler
Author URI: http://www.mattvanandel.com - http://www.eibler.at
License: GPL2
Modified: Leo Eibler for GarageSale Plugin
Text Domain: garagesale

** @date 20120420 wordpress@sprossenwanne.at
**                bugfix bulk actions if using other language than english \n
**                bugfix administrator actions \n
**                only show img tag if an image is set \n
**                show user display_name instead of user_nicename \n
** @date 20130102 wordpress@sprossenwanne.at
**                bugfix remove prepare calls with only 1 argument to work with wordpress 3.5 \n
** @date 20130103 wordpress@sprossenwanne.at
**                bugfix use $_REQUEST instead of $_GET to work with wordpress 3.5 \n
**                add define GARAGESALE_ITEMS_PER_PAGE in garagesale.php as single point of configuration \n
** @date 20130104 wordpress@sprossenwanne.at
**                bugfix pagination total pages check \n
*/

/*  Copyright 2011  Matthew Van Andel  (email : matt@mattvanandel.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

/*************************** LOAD THE BASE CLASS *******************************
 *******************************************************************************
 * The WP_List_Table class isn't automatically available to plugins, so we need
 * to check if it's available and load it if necessary.
 */
if(!class_exists('WP_List_Table')){
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}




/************************** CREATE A PACKAGE CLASS *****************************
 *******************************************************************************
 * Create a new list table package that extends the core WP_List_Table class.
 * WP_List_Table contains most of the framework for generating the table, but we
 * need to define and override some methods so that our data can be displayed
 * exactly the way we need it to be.
 * 
 * To display this example on a page, you will first need to instantiate the class,
 * then call $yourInstance->prepare_items() to handle any data manipulation, then
 * finally call $yourInstance->display() to render the table to the page.
 * 
 * Our theme for this list table is going to be movies.
 */
class GarageSale_List_Table extends WP_List_Table {
	/*
	** @brief GarageSale class instance
	*/
	protected $garagesale;

	/*
	** @brief table name for the query
	*/
	protected $table_stuff;
	
	/*
	** @brief list type 'user' or 'administrator' to see only own items or all items
	*/
	protected $listType = 'user';
	
	/** ************************************************************************
	 * REQUIRED. Set up a constructor that references the parent constructor. We 
	 * use the parent reference to set some default configs.
	 ***************************************************************************/
	function __construct(){
		global $status, $page;
				
		//Set parent defaults
		parent::__construct( array(
			'singular'  => __('item','garagesale'),     //singular name of the listed records
			'plural'    => __('items','garagesale'),    //plural name of the listed records
			'ajax'      => false        //does this table support ajax?
		) );
		
	}
	
	
	/** ************************************************************************
	 * Recommended. This method is called when the parent class can't find a method
	 * specifically build for a given column. Generally, it's recommended to include
	 * one method for each column you want to render, keeping your package class
	 * neat and organized. For example, if the class needs to process a column
	 * named 'title', it would first see if a method named $this->column_title() 
	 * exists - if it does, that method will be used. If it doesn't, this one will
	 * be used. Generally, you should try to use custom column methods as much as 
	 * possible. 
	 * 
	 * Since we have defined a column_title() method later on, this method doesn't
	 * need to concern itself with any column with a name of 'title'. Instead, it
	 * needs to handle everything else.
	 * 
	 * For more detailed insight into how columns are handled, take a look at 
	 * WP_List_Table::single_row_columns()
	 * 
	 * @param array $item A singular item (one full row's worth of data)
	 * @param array $column_name The name/slug of the column to be processed
	 * @return string Text or HTML to be placed inside the column <td>
	 **************************************************************************/
	function column_default($item, $column_name){
		switch($column_name){
			case 'display_name':
			case 'post_author':
				$out = format_to_edit($item[$column_name]).'<br />';
				if( isset($item['contact']) && !empty($item['contact']) ) {
					$out .= '<span class="garagesale-field garagesale-field-contact">'.format_to_edit($item['contact']).'</span>';
				}
				return $out;
			case 'name':
				return format_to_edit($item[$column_name]);
			case 'description':
				if( mb_strlen($item[$column_name]) > 60 ) {
					return format_to_edit( mb_substr( $item[$column_name], 0, 60 ) ).'...';
				}
				return format_to_edit( mb_substr( $item[$column_name], 0, 60 ) );
			case 'picture':
				if( !empty($item[$column_name]) ) {
					return '<img class="garagesale-admin" src="'.$item[$column_name].'" />';
				}
				return '';
			case 'price':
				return format_to_edit($item[$column_name]);
			case 'post_date':
				return date_i18n(get_option('date_format') ,strtotime($item['post_date']));
			case 'status':
				if( $item[$column_name] == GARAGESALE_ELEMENT_SOLD ) {
					return __('Sold','garagesale');
				} else {
					return __('Active','garagesale');
				}
			default:
				return print_r($item,true); //Show the whole array for troubleshooting purposes
		}
	}
	
		
	/** ************************************************************************
	 * Recommended. This is a custom column method and is responsible for what
	 * is rendered in any column with a name/slug of 'title'. Every time the class
	 * needs to render a column, it first looks for a method named 
	 * column_{$column_title} - if it exists, that method is run. If it doesn't
	 * exist, column_default() is called instead.
	 * 
	 * This example also illustrates how to implement rollover actions. Actions
	 * should be an associative array formatted as 'slug'=>'link html' - and you
	 * will need to generate the URLs yourself. You could even ensure the links
	 * 
	 * 
	 * @see WP_List_Table::::single_row_columns()
	 * @param array $item A singular item (one full row's worth of data)
	 * @return string Text to be placed inside the column <td> (movie title only)
	 **************************************************************************/
	function column_name($item){
		
		//Build row actions
		$actions = array(
			'edit'      => sprintf('<a href="'.$this->getLink('main').'?page=%s&action=%s&id=%s">'.__('Edit').'</a>','printAdminUsersNewItemPage','edit',$item['id'] ),
			'sold'    => sprintf('<a href="'.$this->getLink().'?page=%s&action=%s&id=%s">'.__('Sold','garagesale').'</a>',$_REQUEST['page'],'sold',$item['id'] ),
			'delete'    => sprintf('<a href="'.$this->getLink().'?page=%s&action=%s&id=%s">'.__('Delete').'</a>',$_REQUEST['page'],'delete',$item['id'] ),
		);
		
		$nameOut = sprintf('<a class="garagesale" href="'.$this->getLink('main').'?page=%s&action=%s&id=%s">'.format_to_edit($item['name']).'</a>','printAdminUsersNewItemPage','edit',$item['id'] );

		//Return the title contents
		return sprintf('%1$s <span style="color:silver">(id:%2$s)</span>%3$s',
			/*$1%s*/ $nameOut,
			/*$2%s*/ $item['id'],
			/*$3%s*/ $this->row_actions($actions)
		);
	}
	
	/** ************************************************************************
	 * REQUIRED if displaying checkboxes or using bulk actions! The 'cb' column
	 * is given special treatment when columns are processed. It ALWAYS needs to
	 * have it's own method.
	 * 
	 * @see WP_List_Table::::single_row_columns()
	 * @param array $item A singular item (one full row's worth of data)
	 * @return string Text to be placed inside the column <td> (movie title only)
	 **************************************************************************/
	function column_cb($item){
		return sprintf(
			'<input type="checkbox" name="%1$s[]" value="%2$s" />',
			/*$1%s*/ 'item',  // item is the variable used in processing bulk actions
			/*$2%s*/ $item['id']                //The value of the checkbox should be the record's id
		);
	}
	
	
	/** ************************************************************************
	 * REQUIRED! This method dictates the table's columns and titles. This should
	 * return an array where the key is the column slug (and class) and the value 
	 * is the column's title text. If you need a checkbox for bulk actions, refer
	 * to the $columns array below.
	 * 
	 * The 'cb' column is treated differently than the rest. If including a checkbox
	 * column in your table you must create a column_cb() method. If you don't need
	 * bulk actions or checkboxes, simply leave the 'cb' entry out of your array.
	 * 
	 * @see WP_List_Table::::single_row_columns()
	 * @return array An associative array containing column information: 'slugs'=>'Visible Titles'
	 **************************************************************************/
	function get_columns(){
		$columns = array(
			'cb'        => '<input type="checkbox" />', //Render a checkbox instead of text
			'name'     => __('Title','garagesale'),
			'display_name'    => __('Author','garagesale'),
			'description'    => __('Description','garagesale'),
			'price'    => __('Price','garagesale'),
			'picture'    => __('Picture','garagesale'),
			'status'    => __('Status','garagesale'),
			'post_date'    => __('Date','garagesale'),
		);
		return $columns;
	}
	
	/** ************************************************************************
	 * Optional. If you want one or more columns to be sortable (ASC/DESC toggle), 
	 * you will need to register it here. This should return an array where the 
	 * key is the column that needs to be sortable, and the value is db column to 
	 * sort by. Often, the key and value will be the same, but this is not always
	 * the case (as the value is a column name from the database, not the list table).
	 * 
	 * This method merely defines which columns should be sortable and makes them
	 * clickable - it does not handle the actual sorting. You still need to detect
	 * the ORDERBY and ORDER querystring variables within prepare_items() and sort
	 * your data accordingly (usually by modifying your query).
	 * 
	 * @return array An associative array containing all the columns that should be sortable: 'slugs'=>array('data_values',bool)
	 **************************************************************************/
	function get_sortable_columns() {
		$sortable_columns = array(
			'name'     => array('name',true),     //true means its already sorted
			'status'     => array('status',false),     //true means its already sorted
			'display_name'    => array('display_name',false),
			'post_date'    => array('display_name',false)
		);
		return $sortable_columns;
	}
	
	
	/** ************************************************************************
	 * Optional. If you need to include bulk actions in your list table, this is
	 * the place to define them. Bulk actions are an associative array in the format
	 * 'slug'=>'Visible Title'
	 * 
	 * If this method returns an empty value, no bulk action will be rendered. If
	 * you specify any bulk actions, the bulk actions box will be rendered with
	 * the table automatically on display().
	 * 
	 * Also note that list tables are not automatically wrapped in <form> elements,
	 * so you will need to create those manually in order for bulk actions to function.
	 * 
	 * @return array An associative array containing all the bulk actions: 'slugs'=>'Visible Titles'
	 **************************************************************************/
	function get_bulk_actions() {
		$actions = array(
			'sold'    => __('Sold','garagesale'),
			'delete'    => __('Delete')
		);
		return $actions;
	}
	
	
	/** ************************************************************************
	 * Optional. You can handle your bulk actions anywhere or anyhow you prefer.
	 * For this example package, we will handle it in the class to keep things
	 * clean and organized.
	 * 
	 * @see $this->prepare_items()
	 **************************************************************************/
	function process_bulk_action() {
		//Detect when a bulk action is being triggered...
		if( 'delete' === $this->current_action() ) {
			if( isset($_REQUEST['item']) && is_array($_REQUEST['item']) ) {
				foreach( $_REQUEST['item'] as $itemId ) {
					$this->garagesale->deleteElement( $itemId );
				}
			} else 
			if( isset($_REQUEST['id']) && $_REQUEST['id'] ) { 
				$this->garagesale->deleteElement( $_REQUEST['id'] );
			}
		}
		if( 'sold' === $this->current_action() ) {
			if( isset($_REQUEST['item']) && is_array($_REQUEST['item']) ) {
				foreach( $_REQUEST['item'] as $itemId ) {
					$this->garagesale->soldElement( $itemId );
				}
			} else 
			if( isset($_REQUEST['id']) && $_REQUEST['id'] ) { 
				$this->garagesale->soldElement( $_REQUEST['id'] );
			}
		}
	}
	
	
	/** ************************************************************************
	 * REQUIRED! This is where you prepare your data for display. This method will
	 * usually be used to query the database, sort and filter the data, and generally
	 * get it ready to be displayed. At a minimum, we should set $this->items and
	 * $this->set_pagination_args(), although the following properties and methods
	 * are frequently interacted with here...
	 * 
	 * @uses $this->_column_headers
	 * @uses $this->items
	 * @uses $this->get_columns()
	 * @uses $this->get_sortable_columns()
	 * @uses $this->get_pagenum()
	 * @uses $this->set_pagination_args()
	 **************************************************************************/
	function prepare_items() {
		global $wpdb;
		
		/**
		 * First, lets decide how many records per page to show
		 */
		$per_page = GARAGESALE_ITEMS_PER_PAGE;
		
		
		/**
		 * REQUIRED. Now we need to define our column headers. This includes a complete
		 * array of columns to be displayed (slugs & titles), a list of columns
		 * to keep hidden, and a list of columns that are sortable. Each of these
		 * can be defined in another method (as we've done here) before being
		 * used to build the value for our _column_headers property.
		 */
		$columns = $this->get_columns();
		$hidden = array();
		$sortable = $this->get_sortable_columns();
		
		
		/**
		 * REQUIRED. Finally, we build an array to be used by the class for column 
		 * headers. The $this->_column_headers property takes an array which contains
		 * 3 other arrays. One for all columns, one for hidden columns, and one
		 * for sortable columns.
		 */
		$this->_column_headers = array($columns, $hidden, $sortable);
		
		
		/**
		 * Optional. You can handle your bulk actions however you see fit. In this
		 * case, we'll handle them within our package just to keep things clean.
		 */
		$this->process_bulk_action();
		
		/**
		 * Instead of querying a database, we're going to fetch the example data
		 * property we created for use in this plugin. This makes this example 
		 * package slightly different than one you might build on your own. In 
		 * this example, we'll be using array manipulation to sort and paginate 
		 * our data. In a real-world implementation, you will probably want to 
		 * use sort and pagination data to build a custom query instead, as you'll
		 * be able to use your precisely-queried data immediately.
		 */
		$select = 'SELECT '.
			's.id, s.id, s.post_author, s.post_date, s.category, s.name, '.
			's.description, s.picture, s.price, s.contact, s.status, '.
			'u.display_name, u.user_login '.
			'FROM '.$this->table_stuff.' AS s '.
			'LEFT JOIN '.$wpdb->prefix.'users AS u '.
			'ON s.post_author = u.ID ';
		$wc = '';
		switch( $this->listType ) {
			case 'administrator':
				$wc = '';
				break;
			case 'all':
				$wc .= ' WHERE post_author = '.get_current_user_id();
				break;
			case 'sold':
				$wc .= ' WHERE post_author = '.get_current_user_id();
				$wc .= ' AND status = '.GARAGESALE_ELEMENT_SOLD;
				break;
			default:
				$wc .= ' WHERE post_author = '.get_current_user_id();
				$wc .= ' AND status = '.GARAGESALE_ELEMENT_ACTIVE;
				break;
		}
		$order = '';
		if( !empty($_REQUEST['orderby']) ) {
			if( array_key_exists( $_REQUEST['orderby'], $sortable ) ) {
				$order = ' ORDER BY '.$_REQUEST['orderby'];
			}
		} else {
			$order = ' ORDER BY name';
		}
		if( !empty($order) ) {
			if( !empty($_REQUEST['order']) && $_REQUEST['order'] == 'desc' ) {
				$order .= ' DESC ';
			} else {
				$order .= ' ASC ';
			}
		}
		
		/**
		 * REQUIRED for pagination. Let's check how many items are in our data array. 
		 * In real-world use, this would be the total number of items in your database, 
		 * without filtering. We'll need this later, so you should always include it 
		 * in your own package classes.
		 */
		// first setup pagination
		$selectTotalItems = 'SELECT COUNT(id) AS total_items FROM '.$this->table_stuff.' '.$wc;
		$total_items = 0;
		$dataTotalItems = $wpdb->get_row( $selectTotalItems, ARRAY_A, 0 );
		if( isset( $dataTotalItems['total_items'] ) ) {
			$total_items = $dataTotalItems['total_items'];
		}
		/**
		 * REQUIRED. We also have to register our pagination options & calculations.
		 */
		$this->set_pagination_args( array(
			'total_items' => $total_items,                  //WE have to calculate the total number of items
			'per_page'    => $per_page,                     //WE have to determine how many items to show on a page
			'total_pages' => ceil($total_items/$per_page)   //WE have to calculate the total number of pages
		) );
		
		$current_page = $this->get_pagenum();
		
		$limit = ' LIMIT '.$per_page.' OFFSET '.($current_page-1)*$per_page;
		$data = $wpdb->get_results( $select.' '.$wc.$order.$limit, ARRAY_A );

		/**
		 * REQUIRED. Now we can add our *sorted* data to the items property, where 
		 * it can be used by the rest of the class.
		 */
		$this->items = $data;
		
	}
	
	
	/*
	** @brief gets the link to plugin pages to use it on different sites
	** @param $what	string what script to return ("main"=main page to use user details, other=current script name)
	** @return string script name to use in link
	*/
	public function getLink( $what='' ) {
		if( $what == 'main' ) {
			return 'index.php';
		} else {
			return $_SERVER['SCRIPT_NAME'];
		}
	}
	
	/*
	** @brief sets the garagesale class to be able to call methods
	*/
	public function setGarageSale( $garagesale ) {
		$this->garagesale = $garagesale;
	}

	/*
	** @brief sets the table name for the query
	*/
	public function setTableStuff( $table_stuff ) {
		$this->table_stuff = $table_stuff;
	}
	
	/*
	** @brief sets the list type 'user' or 'administrator' to see only own items or all items
	*/
	public function setList( $listType ) {
		$this->listType = $listType;
	}
}

