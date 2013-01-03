<?php
/*
** The printAdminUsersPage content template
*/
$this->incOnce( 'garagesale_list_table4dashboard.php', GARAGESALE_PHP_DIR ); 
?>
<div class=wrap>
	<h4 class="garagesale"><?php echo sprintf( __('The last %d active items','garagesale'), GARAGESALE_LIST_TABLE4DASHBOARD_NROFITEMS ); ?></h4>
</div>
 <?php 
$gsListTable = new GarageSale_List_Table4Dashboard();
$gsListTable->setGarageSale( $this );
$gsListTable->setTableStuff( $this->table_stuff );
$gsListTable->prepare_items();
echo $gsListTable->display()
?>
<div class=wrap>
	<h4 class="garagesale"><?php echo __('Add a new item','garagesale'); ?></h4>
</div>
<form method="post" action="<?php echo sprintf('?page=%s&action=%s&id=%s','printAdminUsersNewItemPage','new',0 ); ?>" enctype="multipart/form-data">
	<p><?php echo __('Title','garagesale'); ?>: <br />
		<label for="garagesale_name"><input type="text" size="40" name="name" value="" /></label>
	</p>
	<p><label for="garagesale_Btn_Submit"><input type="submit" size="40" name="Btn_Submit" value="<?php _e('Save','garagesale'); ?>" /></label></p>
	<input type="hidden" name="id" value="<?php if( isset($this->data['id']) ) { echo $this->data['id']; } else { echo '0'; }  ?>" />
</form>
