<?php
/*
** The printAdminUsersNewItemPage content template
*/
// _e( 'Add Item', 'garagesale' ); 
?>
<div class=wrap>
	<h2><?php if( isset($this->data['id']) && $this->data['id'] > 0 ) { echo __('Edit Item','garagesale'); } else { echo __('Add Item','garagesale'); } ?></h2>
	<form method="post" action="<?php echo esc_url($_SERVER["REQUEST_URI"]); ?>" enctype="multipart/form-data">
		<p><?php echo __('ID','garagesale'); ?>: <?php if( isset($this->data['id']) && $this->data['id'] > 0 ) { echo $this->data['id'].' ('.date_i18n(get_option('date_format') ,strtotime($this->data['post_date'])).')'; } else { _e( 'NEW (NOT SAVED!)','garagesale'); }  ?> </p>
		<!-- p>Author: <?php echo get_current_user_id(); ?> </p -->
		<p><?php echo __('Title','garagesale'); ?>: <br />
			<label for="garagesale_name"><input type="text" size="40" name="name" value="<?php if( isset($this->data['name']) ) { echo format_to_edit($this->data['name']); } ?>" /></label>
		</p>
		<div style="float:left; margin-left:10px;">
			<p><?php echo __('Price','garagesale'); ?>: <br />
				<label for="garagesale_price"><input type="text" size="40" name="price" value="<?php if( isset($this->data['price']) ) { echo format_to_edit($this->data['price']); } ?>" /></label>
			</p>
			<p><?php echo __('Description','garagesale'); ?>: <br />
				<label for="garagesale_description"><textarea cols="40" rows="8" name="description"><?php if( isset($this->data['description']) ) { echo format_to_edit($this->data['description']); } ?></textarea></label>
			</p>
			<p><?php echo __('Contact','garagesale'); ?> (<?php echo __('Example: your telephone number','garagesale'); ?>): <br />
				<label for="garagesale_contact"><input type="text" size="40" name="contact" value="<?php if( isset($this->data['contact']) ) { echo format_to_edit($this->data['contact']); } ?>" /></label>
			</p>
			<p><?php echo __('Status','garagesale'); ?>: <br />
				<label for="garagesale_status">
					<select name="status">
						<option value="<?php echo GARAGESALE_ELEMENT_ACTIVE; ?>"<?php if( isset($this->data['status']) && $this->data['status'] == GARAGESALE_ELEMENT_ACTIVE ): ?> selected="selected"<?php endif; ?>><?php echo __('Active','garagesale'); ?></option>
						<option value="<?php echo GARAGESALE_ELEMENT_SOLD; ?>"<?php if( isset($this->data['status']) && $this->data['status'] == GARAGESALE_ELEMENT_SOLD ): ?> selected="selected"<?php endif; ?>><?php echo __('Sold','garagesale'); ?></option>
					</select>
				</label>
			</p>
			
		</div>
		<div style="float:left; margin-left:10px;">
			<p><?php echo __('Picture (File Upload)','garagesale'); ?>: <br />
				<label for="uploadfile">
				<input type="file" name="uploadfile" />
				</label> 
			</p>
			<p><?php echo __('Picture (Link)','garagesale'); ?>: <br />	
				<label for="picture">
					<input id="picture" type="text" size="36" name="picture" value="<?php if( isset($this->data['picture']) ) { echo format_to_edit($this->data['picture']); } ?>" />
					<br /><?php echo __('Enter an URL or upload an image for the banner.','garagesale'); ?>
				</label> 
				<br />
				<?php if( !empty($this->data['picture_original']) ): ?><a href="<?php echo format_to_edit($this->data['picture_original']); ?>" target="_blank"><?php endif; ?>
				<?php if( isset($this->data['picture']) ): ?>
				<img id="pictureDisplay" src="<?php echo format_to_edit($this->data['picture']); ?>" />
				<?php else: ?>
				<?php if( !empty($this->data['picture_original']) ) { echo "link"; } ?>
				<?php endif; ?>
				<?php if( !empty($this->data['picture_original']) ): ?></a><?php endif; ?>
			</p>
		</div>
		<br clear="all" />
		<p><label for="garagesale_Btn_Submit"><input type="submit" size="40" name="Btn_Submit" value="<?php _e('Save','garagesale'); ?>" /></label></p>
		<input type="hidden" name="id" value="<?php if( isset($this->data['id']) ) { echo $this->data['id']; } else { echo '0'; }  ?>" />
	</form>
</div>
