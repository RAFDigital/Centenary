<?php  
 

?>
	<h1><?php echo __('Website Users - New user'); ?></h1>
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

		 <table class="form-table">
			<tbody>
				<tr class="user-user-login-wrap">
					<th>Email</th>
					<td>
						<input type="email" name="user[email]" required value="" class="regular-text">
					</td>
				</tr>
				<tr class="user-user-login-wrap">
					<th>Name</th>
					<td>
						<input type="text" name="user[name]" required value="" class="regular-text">
					</td>
				</tr>
				<tr class="user-user-login-wrap">
					<th>Valid</th>
					<td>
						<label><input type="radio" name="user[valid]" value="1" checked="checked"> Yes</label>
						<label><input type="radio" name="user[valid]" value="0"> No</label>

					</td>
				</tr>
			</tbody>
		</table>
		<p class="submit"><input type="submit" name="create_webuser" id="submit" class="button button-primary" value="Create Webuser"><span class="acf-spinner"></span></p>
	</form>

	