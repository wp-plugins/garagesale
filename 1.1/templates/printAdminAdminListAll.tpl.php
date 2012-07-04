<?php
/*
** The printAdminUsersPage content template
*/
?>
<div class=wrap>
	<h2><?php echo __('Garage Sale List','garagesale'); ?></h2>
	<h3><?php echo __('List All Items From All Users','garagesale'); ?></h3>
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
$gsListTable->prepare_items();
    
?>
<?php include( $this->incRet( 'printAdminUsersList_Footer.tpl.php' ) ); ?>
