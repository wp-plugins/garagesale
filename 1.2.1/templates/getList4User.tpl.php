<?php
/*
** The user content
*/
?>
<?php $this->incOnce( 'garagesale_list_table4user.php', GARAGESALE_PHP_DIR ); ?>

<?php

//Create an instance of our package class...
$gsListTable = new GarageSale_List_Table4User();

$gsListTable->setGarageSale( $this );
$gsListTable->setList( 'active' );
$gsListTable->setTableStuff( $this->table_stuff );
$gsListTable->prepare_items();
    
?>
<div class="wrap">
	<?php $gsListTable->display() ?>
</div>
