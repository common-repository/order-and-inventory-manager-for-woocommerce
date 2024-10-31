<?php
	global $post;
	$oimwcSiteUrl = admin_url().'admin.php?page=order-inventory-management';
?>
<div class="top_logo_area">
	<a href="<?php echo $oimwcSiteUrl; ?>">
        <img alt="" src="<?php echo OIMWC_PLUGIN_URL; ?>images/new_logo.png" />
    </a>
</div>
<?php
if( !isset($_GET['subpage']) && ( (isset($post->post_type) && $post->post_type == 'supplier') || 
	(isset($_GET['post_type']) && sanitize_text_field($_GET['post_type']) == 'supplier') ) )
{
	echo '<div class="sticky_header_supplier">
	<div class="wrap">
	<form id="posts-filter">
		<p class="search-box">
			<label class="screen-reader-text" for="post-search-input">Search supplier:</label>
			<input type="search" id="post-search-input" name="s" value="" placeholder="Search supplier...">
			<input type="submit" id="search-submit" class="button" value="Search supplier">
		</p>
	</form>
	</div></div>';
}
?>