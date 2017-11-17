<?php  

	$user_data = $websiteUsers->getUser($_GET['wuid']);
	if(!count($user_data)){
		wp_redirect($webusers_admin_page_url);
		exit;
	}

	//pr($user_data); 

?>
	<h1><?php echo __('Website Users'); ?> - <?php echo $user_data->name;?></h1>
	<form method="get" action="/wp-admin/admin.php">
		<input type="hidden" name="page" value="raf-webusers" /> 
		<p class="search-box">
			<label class="screen-reader-text" for="post-search-input">Search users:</label>
			<input type="search" id="post-search-input" name="users_search" placeholder="<?php echo __('Search web users');?>" value="">
			<input type="submit" id="search-submit" class="button" value="Search">
		</p>
	</form>
	<form method="post" action="<?php echo $webusers_admin_page_url;?>">
		<input type="hidden" name="page" value="raf-webusers" />
		<input type="hidden" name="user[id]" value="<?php echo $user_data->id;?>" />

		 <table class="form-table">
			<tbody>
				<tr class="user-user-login-wrap">
					<th>Email</th>
					<td>
						<input type="email" name="user[email]" required value="<?php echo $user_data->email;?>" class="regular-text">
					</td>
				</tr>
				<tr class="user-user-login-wrap">
					<th>Name</th>
					<td>
						<input type="text" name="user[name]" required value="<?php echo $user_data->name;?>" class="regular-text">
					</td>
				</tr>
				<tr class="user-user-login-wrap">
					<th>Valid</th>
					<td>
						<label><input type="radio" name="user[valid]" value="1" <?php echo ($user_data->valid > 0 ? 'checked="checked"' : '');?>> Yes</label>
						<label><input type="radio" name="user[valid]" value="0" <?php echo ($user_data->valid > 0 ? '' : 'checked="checked"');?>> No</label>

					</td>
				</tr>
			</tbody>
		</table>
		<p class="submit"><input type="submit" name="edit_webuser" id="submit" class="button button-primary" value="Update Webuser"><span class="acf-spinner"></span></p>
	</form>

	