<?php
/*
** @brief lightweight table layout class to display users item list in dashboard widget for garagesale wordpress plugin
** @author Leo Eibler - wordpress@sprossenwanne.at
** @date 20120331 wordpress@sprossenwanne.at
**                create class \n
** @date 20120420 wordpress@sprossenwanne.at
**                modify type of price from float to varchar \n
**                add license \n
** @date 20130102 wordpress@sprossenwanne.at
**                bugfix remove prepare calls with only 1 argument to work with wordpress 3.5 \n
** @date 20140123 wordpress@sprossenwanne.at
**                add support for multisite (use $wpdb->users for users table) \n
*/

/*
  Copyright 2012-2014 Leo Eibler (http://www.eibler.at)

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

/*
** @brief display first GARAGESALE_LIST_TABLE4DASHBOARD_NROFITEMS elements of the items for the current logged in user
*/
define( 'GARAGESALE_LIST_TABLE4DASHBOARD_NROFITEMS', 5 );

class GarageSale_List_Table4Dashboard {
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
	
	/*
	** @brief pagination args
	*/
	protected $pagination = array();
	
	protected $order = '';
	protected $orderby = '';
	
    
    function __construct(){
    }
 
    function column_default($item, $column_name){
        switch($column_name){
            case 'name':
				$out = '<span class="garagesale-field garagesale-field-name">';
				$out .= sprintf('<a class="garagesale-dashboard" href="?page=%s&action=%s&id=%s">'.format_to_edit($item[$column_name]).'</a>','printAdminUsersNewItemPage','edit',$item['id'] );
				//$out .= format_to_edit($item[$column_name]);
				$out .= '</span><br />';
				if( isset($item['description']) && !empty($item['description']) ) {
					$out .= '<span class="garagesale-field garagesale-field-description">'.format_to_edit($item['description']).'</span>';
				}
				return $out;
			case 'picture':
				$out = '';
				if( isset( $item['picture_original'] ) && !empty( $item['picture_original'] ) ) {
					$out .= '<a href="'.$item['picture_original'].'" target="_blank">';
				}
				if( !empty($item[$column_name]) ) {
					$out .= '<img class="garagesale-small" src="'.$item[$column_name].'" />';
				} else {
					if( isset( $item['picture_original'] ) && !empty( $item['picture_original'] ) ) {
						$out .= 'link';
					}
				}
				if( isset( $item['picture_original'] ) && !empty( $item['picture_original'] ) ) {
					$out .= '</a>';
				}
				return  $out;
			case 'price':
				return  format_to_edit( $item[$column_name] );
			default:
                return print_r($item,true); //Show the whole array for troubleshooting purposes
        }
    }
    
    function get_columns(){
        $columns = array(
			'picture'    => __('Picture','garagesale'),
			'name'     => __('Title','garagesale'),
			'price'    => __('Price','garagesale'),
        );
        return $columns;
    }
    
    function get_sortable_columns() {
        $sortable_columns = array(
			'post_date'     => array('post_date',true),     //true means its already sorted
            'status'     => array('status',false),     //true means its already sorted
            'name'     => array('name',false),     //true means its already sorted
            'display_name'    => array('display_name',false)
        );
        return $sortable_columns;
    }
    
    function prepare_items() {
		global $wpdb;
        
		$columns = $this->get_columns();
		$select = 'SELECT '.
			's.id, s.id, s.post_author, s.post_date, s.category, s.name, '.
			's.description, s.picture, s.picture_original, s.price, s.contact, s.status, '.
			'u.display_name, u.user_login '.
			'FROM '.$this->table_stuff.' AS s '.
			'LEFT JOIN '.$wpdb->users.' AS u '.
			'ON s.post_author = u.ID ';
		$wc = 'WHERE s.status = '.GARAGESALE_ELEMENT_ACTIVE;
		$wc .= ' AND s.post_author = '.get_current_user_id();
		$order = ' ORDER BY s.post_date DESC ';
		$limit = ' LIMIT '.GARAGESALE_LIST_TABLE4DASHBOARD_NROFITEMS.' OFFSET 0 ';

		$data = $wpdb->get_results( $select.' '.$wc.$order.$limit, ARRAY_A );
		$this->items = $data;
    }
	
	public function getLink( $page=false, $orderby=false, $order=false ) {
		$link = '';
		if( $page !== false ) {
			if( !empty($link) ) { $link .= '&'; }
			$link .= sprintf( 'paged=%d', $page );
		}
		if( $orderby !== false ) {
			if( !empty($link) ) { $link .= '&'; }
			$link .= sprintf( 'orderby=%s', $orderby );
		}
		if( $order !== false ) {
			if( !empty($link) ) { $link .= '&'; }
			$link .= sprintf( 'order=%s', $order );
		}
		$permalink = get_permalink();
		if( !empty($link) ) {
			if( strpos($permalink,'?') === false ) {
				$link = '?'.$link;
			} else {
				$link = '&'.$link;
			}
		}
		return $permalink.$link;
	}
	
	public function displayTable() {
		$out = '';
		$out .= '<table class="garagesale">';
		$out .= '<tr class="garagesale garagesale-tablehead">';
		$columns = $this->get_columns();
		foreach( $columns as $column => $name ) {
			$out .= '<th class="garagesale garagesale-tablehead garagesale-field-'.$column.'-small">'.$name.'</th>';
		}
		$out .= '</tr>';
		foreach( $this->items as $i => $item ) {
			$out .= '<tr class="garagesale garagesale-'.($i%2 == 0 ? 'even' : 'odd').'">';
			foreach( $columns as $column => $name ) {
				if( isset( $item[$column] ) ) {
					$out .= '<td class="garagesale garagesale-'.($i%2 == 0 ? 'even' : 'odd').' garagesale-field-'.$column.'-small">'.$this->column_default($item, $column).'</td>';
				}
			}
			$out .= '</tr>';
		}
		$out .= '</table>';
		return $out;
	}
	
	public function display() {
		$out = '';
		$out .= $this->displayTable();
		return $out;
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
}

