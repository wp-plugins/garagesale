<?php
/*
** @brief lightweight table layout class to display in user area for garagesale wordpress plugin
** (we cannot use an instance of WP_List_Table here, because this is only for admin area)
** @author Leo Eibler - wordpress@sprossenwanne.at
** @date 20120330 wordpress@sprossenwanne.at
**                create class \n
** @date 20120420 wordpress@sprossenwanne.at
**                modify type of price from float to varchar \n
**                show user display_name instead of user_nicename \n
**                add license \n
** @date 20130102 wordpress@sprossenwanne.at
**                bugfix remove prepare calls with only 1 argument to work with wordpress 3.5 \n
** @date 20130103 wordpress@sprossenwanne.at
**                bugfix use $_REQUEST instead of $_GET to work with some specials wordpress 3.5 \n
**                add define GARAGESALE_ITEMS_PER_PAGE in garagesale.php as single point of configuration \n
**                bugfix pagination \n
**                improve get_pagenum() method \n
** @date 20130104 wordpress@sprossenwanne.at
**                bugfix get_pagenum() method for use with permanent links enabled \n
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

class GarageSale_List_Table4User {
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
				$out = '<span class="garagesale-field garagesale-field-name">'.format_to_edit($item[$column_name]).'</span><br />';
				if( isset($item['description']) && !empty($item['description']) ) {
					$out .= '<span class="garagesale-field garagesale-field-description">'.format_to_edit($item['description']).'</span>';
				}
				return $out;
			case 'post_author':
			case 'display_name':
				$post_date = date_i18n(get_option('date_format') ,strtotime($item['post_date']));
				$out = '<span class="garagesale-field garagesale-field-post_date">'.$post_date.'</span><br />';
				$out .= '<span class="garagesale-field garagesale-field-author">'.format_to_edit($item[$column_name]).'</span><br />';
				if( isset($item['contact']) && !empty($item['contact']) ) {
					$out .= '<span class="garagesale-field garagesale-field-contact">'.format_to_edit($item['contact']).'</span>';
				}
				return $out;
			case 'contact':
				return format_to_edit($item[$column_name]);
			case 'description':
				if( mb_strlen($item[$column_name]) > 60 ) {
					return  format_to_edit( mb_substr( $item[$column_name], 0, 60 ) ).'...';
				}
				return  format_to_edit( mb_substr( $item[$column_name], 0, 60 ) );
			case 'picture':
				$out = '';
				if( isset( $item['picture_original'] ) && !empty( $item['picture_original'] ) ) {
					$out .= '<a href="'.$item['picture_original'].'" target="_blank">';
				}
				if( !empty($item[$column_name]) ) {
					$out .= '<img class="garagesale-middle" src="'.$item[$column_name].'" />';
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
	
	function get_columns(){
		$columns = array(
			'picture'    => __('Picture','garagesale'),
			'name'     => __('Title','garagesale'),
			'display_name'    => __('Author','garagesale'),
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
	
	function process_bulk_action() {
	}
	
	function prepare_items() {
		global $wpdb;
		
		$per_page = GARAGESALE_ITEMS_PER_PAGE;
		
		$columns = $this->get_columns();
		$hidden = array();
		$sortable = $this->get_sortable_columns();
		
		$this->_column_headers = array($columns, $hidden, $sortable);

		$this->process_bulk_action();

		$select = 'SELECT '.
			's.id, s.id, s.post_author, s.post_date, s.category, s.name, '.
			's.description, s.picture, s.picture_original, s.price, s.contact, s.status, '.
			'u.display_name, u.user_login '.
			'FROM '.$this->table_stuff.' AS s '.
			'LEFT JOIN '.$wpdb->prefix.'users AS u '.
			'ON s.post_author = u.ID ';
		$wc = 'WHERE status = '.GARAGESALE_ELEMENT_ACTIVE;

		$order = '';
		if( !empty($_REQUEST['orderby']) ) {
			if( array_key_exists( $_REQUEST['orderby'], $sortable ) ) {
				$order = ' ORDER BY '.$_REQUEST['orderby'];
				$this->orderby = $_REQUEST['orderby'];
			}
		} else {
			$order = ' ORDER BY post_date';
			$this->orderby = 'post_date';
		}
		if( !empty($order) ) {
			if( !empty($_REQUEST['order']) && $_REQUEST['order'] == 'asc' ) {
				$this->order = 'ASC';
			} else {
				$this->order = 'DESC';
			}
		}
		$order .= ' '.$this->order.' ';

		// first setup pagination
		$selectTotalItems = 'SELECT COUNT(id) AS total_items FROM '.$this->table_stuff.' '.$wc;
		$total_items = 0;
		$dataTotalItems = $wpdb->get_row( $selectTotalItems, ARRAY_A, 0 );
		if( isset( $dataTotalItems['total_items'] ) ) {
			$total_items = $dataTotalItems['total_items'];
		}
		$this->set_pagination_args( array(
			'total_items' => $total_items,                  //WE have to calculate the total number of items
			'per_page'    => $per_page,                     //WE have to determine how many items to show on a page
			'total_pages' => ceil($total_items/$per_page)   //WE have to calculate the total number of pages
		) );

		// now select the items for given page
		$current_page = $this->get_pagenum();
		$limit = ' LIMIT '.$per_page.' OFFSET '.($current_page-1)*$per_page;
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
	
	public function getPageNavLink( $page=0 ) {
		if( $page < 1 ) {
			$page = 1;
		}
		if( $page > $this->getTotalPages() ) {
			$page = $this->getTotalPages();
		}
		return $this->getLink( $page, $this->orderby, $this->order );
	}
	
	public function displayPageNav() {
		$out = '';
		$out .= $this->getTotalItems().' '.__('Elements','garagesale').' &nbsp; ';
		$out .= '<a class="garagesale-pagenav garagesale-pagefirst" href="'.$this->getPageNavLink(0).'">&laquo;</a>';
		$out .= '<a class="garagesale-pagenav garagesale-pageprevious" href="'.$this->getPageNavLink($this->get_pagenum()-1).'">&lsaquo;</a>';
		for( $i=1; $i<=$this->getTotalPages(); $i++ ) {
			$out .= '<a class="garagesale-pagenav garagesale-pagenumber';
			if( $i == $this->get_pagenum() ) {
				$out .= ' garagesale-pagecurrent';
			}
			$out .= '" href="'.$this->getPageNavLink($i).'">'.$i.'</a>';
		}
		$out .= '<a class="garagesale-pagenav garagesale-pagenext" href="'.$this->getPageNavLink($this->get_pagenum()+1).'">&rsaquo;</a>';
		$out .= '<a class="garagesale-pagenav garagesale-pagelast" href="'.$this->getPageNavLink($this->getTotalPages()).'">&raquo;</a>';
		/*
		«
		‹
		›
		»
		*/
		return $out;
	}
	
	public function displayTable() {
		$out = '';
		$out .= '<table class="garagesale">';
		$out .= '<tr class="garagesale garagesale-tablehead">';
		$columns = $this->get_columns();
		foreach( $columns as $column => $name ) {
			$out .= '<th class="garagesale garagesale-tablehead garagesale-field-'.$column.'">'.$name.'</th>';
		}
		$out .= '</tr>';
		foreach( $this->items as $i => $item ) {
			$out .= '<tr class="garagesale garagesale-'.($i%2 == 0 ? 'even' : 'odd').'">';
			foreach( $columns as $column => $name ) {
				if( isset( $item[$column] ) ) {
					$out .= '<td class="garagesale garagesale-'.($i%2 == 0 ? 'even' : 'odd').' garagesale-field-'.$column.'">'.$this->column_default($item, $column).'</td>';
				}
			}
			$out .= '</tr>';
		}
		$out .= '</table>';
		return $out;
	}
	
	public function display() {
		$out = '';
		$out .= $this->displayPageNav();
		$out .= $this->displayTable();
		$out .= $this->displayPageNav();
		return $out;
	}
	
	public function get_pagenum() {
		$paged = 0;
		if( isset($_REQUEST['paged']) && is_numeric($_REQUEST['paged']) ) {
			// try to get the page from REQUEST
			$paged = $_REQUEST['paged'];
		}
		// see http://codex.wordpress.org/Function_Reference/get_query_var
		if( !$paged ) {
			// try to get the page from variable 'paged'
			$paged = (get_query_var('paged')) ? get_query_var('paged') : 0;
		}
		if( !$paged ) {
			// try to get the page from variable 'page'
			$paged = (get_query_var('page')) ? get_query_var('page') : 0;
		}
		if( !is_numeric( $paged ) ) {
			$paged = 0;
		}
		if( $paged > $this->getTotalPages() ) {
			return $this->getTotalPages();
		}
		if( $paged > 0 ) {
			return $paged;
		}
		return 1;
	}
	
	public function getTotalItems() {
		if( isset($this->pagination['total_items']) ) {
			return $this->pagination['total_items'];
		}
		return 0;
	}
	
	public function getPerPage() {
		if( isset($this->pagination['per_page']) ) {
			return $this->pagination['per_page'];
		}
		return GARAGESALE_ITEMS_PER_PAGE;
	}
	
	public function getTotalPages() {
		if( isset($this->pagination['total_pages']) ) {
			if( $this->pagination['total_pages'] < 1 ) {
				return 1;
			}
			return $this->pagination['total_pages'];
		}
		return 1;
	}
	
	protected function set_pagination_args( $pagination ) {
		$this->pagination = $pagination;
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

