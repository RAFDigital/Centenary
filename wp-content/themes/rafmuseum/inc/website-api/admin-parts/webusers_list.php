<?php  

	//pr($users_data); die();

	$order = '';
	$direction = 'asc';
	$page = 1;

	if(isset($_GET['paged'])){
		$page = $_GET['paged'];
	}

	if(isset($_GET['order'])){
		$order = $_GET['order'];
		$direction = $_GET['direction'];
	} else {
		$order = 'name';
	} 

	$direction = $direction == 'desc' ? 'desc' : 'asc';

	$users_data = $websiteUsers->getUsers($order, $direction, $page, 5);

	$users = $users_data->data;
	$users_current_page = $users_data->current_page;
	$users_last_page = $users_data->last_page;

	$users_from = $users_data->from;
	$users_to = $users_data->to;
	$users_total = $users_data->total;

	$users_path = $users_data->path;
	$users_per_page = $users_data->per_page;


	
	$users_current_page_url = '?' . http_build_query($_REQUEST);    

	// pagination
	$req = $_REQUEST;
	$req['wpage'] = 'list';
	$req['paged'] = $users_current_page - 1;     
	$users_prev_link = '?' . http_build_query($req);  

	$req['paged'] = $users_current_page + 1;     
	$users_next_link = '?' . http_build_query($req);    

	$req['paged'] = 1;     
	$users_first_link = '?' . http_build_query($req);    

	$req['paged'] = max(1 , $users_last_page );      
	$users_last_link = '?' . http_build_query($req);    
	

	 

	// form
	$select = $select2 = $select3 = array();
	$users_search = isset($_GET['users_search']) ? $_GET['users_search'] : '';
?>
	<h1 class="wp-heading-inline"><?php echo __('Website Users - list'); ?></h1>
	<!--
	<a href="<?php echo $webusers_admin_page_url;?>&amp;wpage=add-new" class="page-title-action">Add web user</a>
	--> 
	<hr class="wp-header-end">
 
	<form method="get" action="<?php echo $webusers_admin_page_url;?>&amp;wpage=list">
		<input type="hidden" name="page" value="raf-webusers" /> 
		<p class="search-box">
			<label class="screen-reader-text" for="post-search-input">Search users:</label>
			<input type="search" id="post-search-input" name="users_search" placeholder="<?php echo __('Search web users');?>" value="<?php echo $users_search; ?>">
			<input type="submit" id="search-submit" class="button" value="Search">
		</p> 
	</form>
	<form method="post" action="<?php echo $users_current_page_url;?>">  
	<?php if(count($users)){

		if(!function_exists('webusers_order')){ 

			function webusers_order ($key, $title, $order, $direction)
			{  	
				global $webusers_admin_page_url;

				if($order == $key){

				}
				// ?order=name&direction=desc
				?>
				<th <?php echo ($title== 'ID' ? 'style="width:50px;"' : ''); ?> class="manage-column column-name sortable <?php echo ($order == $key ? 'sorted':'' ); ?> <?php echo $direction; ?>">
					<a href="<?php echo $webusers_admin_page_url;?>&amp;wpage=list&amp;order=<?php echo $key;?>&direction=<?php echo ($direction == 'desc' ? 'asc': 'desc' ); ?>"><span><?php echo __($title); ?></span><span class="sorting-indicator"></span></a> 
				</th> 
				<?php
			} 

			function webusers_data($key, $user_data, $params = array())
			{
				global $webusers_admin_page_url;
				// date('d.m. H:i', strtotime($info->ordered));
				$val = '';
				switch ($key) {
					case 'id':
						$val = '<a href="' . $webusers_admin_page_url . '&amp;wpage=edit&amp;wuid=' . $user_data->$key . '">' . $user_data->$key . '</a>';
						break; 
					case 'name':
						$val = '<a href="' . $webusers_admin_page_url . '&amp;wpage=edit&amp;wuid=' . $user_data->id . '">' . $user_data->$key . '</a>';
						break;
					case 'email':
						$val = '<a href="mailto:' . $user_data->$key . '">' . $user_data->$key . '</a>';
						break;
					
					default:
						$val = $user_data->$key;
						break;
				}
				echo '<td>' . $val . '</td>';
			}
		}
		?>
		
		<div class="tablenav top">
			<input type="submit" name="change" class="button" value="<?php echo __('Submit');?>" />
			<input type="submit" name="export_ch_orders" class="button" value="<?php echo __('Export as CSV');?>" />
		</div>
			 
		<table class="wp-list-table widefat fixed">
		<tbody>
		<tr>
			<?php 
			foreach (WebsiteUsers::getUserFields() as $key => $title){
			 	webusers_order($key, $title, $order, $direction);
			}
			?>
			<th></th>
		</tr> 


		<?php
		$z = 0;
		foreach ($users as $user) {
			?>
			<tr class="<?php echo ($z%2==0 ? 'alternate':'');?> " >
				<?php 
				foreach (WebsiteUsers::getUserFields()  as $key => $title) {
				 	webusers_data($key, $user);
				}
				?>
				<td><small><a href="<?php echo $webusers_admin_page_url;?>&amp;wpage=list&amp;wudelete=<?php echo $user->id; ?>" onclick="return confirm('Really delete?');"><?php echo __('Delete');?></a></small></td>
			</tr>  
			<?php 
		}
		?>
		</tbody></table>
		<?php 
		}
		?> 

		<div class="tablenav bottom">

			<div class="alignleft actions bulkactions">
				<input type="submit" name="change" class="button" value="<?php echo __('Submit');?>" />
				<input type="submit" name="export_ch_orders" class="button" value="<?php echo __('Export as CSV');?>" />	
			</div>
			<div class="alignleft actions">
			</div>
			<div class="tablenav-pages">
				<span class="displaying-num"><?php echo $users_total; ?> <?php echo __('Items');?></span>
				<span class="pagination-links">
					<?php 
					if($users_current_page == 1){
						?>
						<span class="pagination-links"><span class="tablenav-pages-navspan" aria-hidden="true">&laquo;</span>
						<span class="tablenav-pages-navspan" aria-hidden="true">&lsaquo;</span>
						<?php
					} else {
						?>
						<a class="first-page" title="<?php echo __( 'First page' );?>" href="<?php echo $users_first_link; ?>">«</a>
						<a class="prev-page" title="<?php echo __( 'Previous page' );?>" href="<?php echo $users_prev_link; ?>">‹</a>
						<?php
					}
					?>
					<span class="paging-input"><?php echo $users_current_page; ?> <?php echo __('of');?> <span class="total-pages"><?php echo $users_last_page; ?></span></span>
					<?php 
					if($users_current_page >= $users_last_page){
						?>
						<span class="tablenav-pages-navspan" aria-hidden="true">&rsaquo;</span>
						<span class="tablenav-pages-navspan" aria-hidden="true">&raquo;</span>
						<?php
					} else {
						?>
						<a class="next-page" title="<?php echo __( 'Next page' );?>" href="<?php echo $users_next_link; ?>">&rsaquo;</a>
						<a class="last-page"  title="<?php echo __( 'Last page' );?>" href="<?php echo $users_last_link; ?>">&raquo;</a>
						<?php
					}
					?>

					
				</span>
			</div>
			<br class="clear">
		</div>  
	
	</form>