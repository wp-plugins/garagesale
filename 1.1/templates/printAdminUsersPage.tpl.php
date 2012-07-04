<?php
/*
** The printAdminUsersPage content template
*/
?>
<div class=wrap>
	<h2><?php echo __('Garage Sale List','garagesale'); ?></h2>
	<h3><?php echo __('List Active Items','garagesale'); ?></h3>
</div>
<?php $this->incOnce( 'garagesale_list_table.php', GARAGESALE_PHP_DIR ); ?>

<?php

//Create an instance of our package class...
$gsListTable = new GarageSale_List_Table();

$gsListTable->setGarageSale( $this );
if( GarageSalePlugin_get_current_user_role() == 'administrator' ) {
	$gsListTable->setList( 'administrator' );
} else {
	$gsListTable->setList( 'active' );
}


$gsListTable->setTableStuff( $this->table_stuff );
//Fetch, prepare, sort, and filter our data...
$gsListTable->prepare_items();
    
?>
<div class="wrap">

	<!-- Forms are NOT created automatically, so you need to wrap the table in one to use features like bulk actions -->
	<form id="movies-filter" method="get">
		<!-- For plugins, we also need to ensure that the form posts back to our current page -->
		<input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" />
		<!-- Now we can render the completed list table -->
		<?php $gsListTable->display() ?>
	</form>
	
</div>
