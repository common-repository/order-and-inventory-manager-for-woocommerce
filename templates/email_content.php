<table border="0" cellpadding="0" cellspacing="0" height="100%" width="100%">
	<tbody><tr>
	<td align="center" valign="top">
		<div id="template_header_image">
		</div>
		<table border="0" cellpadding="0" cellspacing="0" width="600" id="template_container" style="background-color: #ffffff; border: 1px solid #dedede; box-shadow: 0 1px 4px rgba(0, 0, 0, 0.1); border-radius: 3px">
			<tbody><tr>
				<td align="center" valign="top">
					<table border="0" cellpadding="0" cellspacing="0" width="100%" id="template_header" style="background-color: #96588a; color: #ffffff; border-bottom: 0; font-weight: bold; line-height: 100%; vertical-align: middle; font-family: &quot;Helvetica Neue&quot;, Helvetica, Roboto, Arial, sans-serif; border-radius: 3px 3px 0 0">
						<tbody><tr>
							<td id="header_wrapper" style="padding: 36px 48px; display: block">
								<h1 style="font-family: &quot;Helvetica Neue&quot;, Helvetica, Roboto, Arial, sans-serif; font-size: 30px; font-weight: 300; line-height: 150%; margin: 0; text-align: left; text-shadow: 0 1px 0 #ab79a1; color: #ffffff">New Inquiry</h1>
							</td>
						</tr>
						</tbody></table>
				</td>
				</tr>
				<tr>
					<td align="center" valign="top">
						<table border="0" cellpadding="0" cellspacing="0" width="600" id="template_body">
							<tbody><tr>
								<td valign="top" id="body_content" style="background-color: #ffffff">
									<table border="0" cellpadding="20" cellspacing="0" width="100%">
										<tbody><tr>
											<td valign="top" style="padding: 48px 48px 32px">
												<div id="body_content_inner" style="color: #636363; font-family: &quot;Helvetica Neue&quot;, Helvetica, Roboto, Arial, sans-serif; font-size: 14px; line-height: 150%; text-align: left">

<div class="oimwc_mail_content" style="font-size: 15px;">
	<p>Hello!</p>
	<?php 
	echo sprintf(__('<p>New inquiry for <strong>%s</strong> from <strong>%s</strong>.</p>','order-and-inventory-manager-for-woocommerce'),sanitize_text_field($data['subject']),sanitize_text_field($data['name']));
	echo '<p><strong>User Email</strong>: '.sanitize_email($data['email']).'</p>';
	echo '<p><strong>Product License Key</strong>: '.htmlentities($data['license_key']).'</p>';
	if(isset($data['domain']) && !empty($data['domain'])){
	    echo '<p><strong>Site Address</strong>: '.sanitize_text_field($data['domain']).'</p>';    
	}
	if(isset($data['summary']) && !empty($data['summary'])){
	    echo '<p><strong>Summary</strong>: '.sanitize_text_field($data['summary']).'</p>';    
	}
	if(isset($data['message']) && !empty($data['message'])){
	    echo '<p><strong>Description</strong>: '.sanitize_text_field($data['message']).'</p>';    
	}
	if(isset($data['link']) && !empty($data['link'])){
	    echo '<p><strong>Site relevant link</strong>: '.sanitize_text_field($data['link']).'</p>';    
	}
	if(isset($data['admin_login_page']) && !empty($data['admin_login_page'])){
	    echo '<p><strong>Admin Page Login URL</strong>: '.sanitize_text_field($data['admin_login_page']).'</p>';    
	}
	if(isset($data['wp_admin_user']) && !empty($data['wp_admin_user'])){
	    echo '<p><strong>WP Admin username</strong>: '.sanitize_text_field($data['wp_admin_user']).'</p>';    
	}
	if(isset($data['wp_admin_password']) && !empty($data['wp_admin_password'])){
	    echo '<p><strong>WP Admin Password</strong>: '.sanitize_text_field($data['wp_admin_password']).'</p>';    
	}
?>
</div>
</div>
											</td>
										</tr>
									</tbody></table>
									
								</td>
							</tr>
						</tbody></table>
						
					</td>
				</tr>
			</tbody></table>
		</td>
	</tr>
	<tr>
		<td align="center" valign="top">
			
			<table border="0" cellpadding="10" cellspacing="0" width="600" id="template_footer">
				<tbody><tr>
					<td valign="top" style="padding: 0; border-radius: 6px">
						<table border="0" cellpadding="10" cellspacing="0" width="100%">
							<tbody><tr>
								<td colspan="2" valign="middle" id="credit" style="border-radius: 6px; border: 0; color: #8a8a8a; font-family: &quot;Helvetica Neue&quot;, Helvetica, Roboto, Arial, sans-serif; font-size: 12px; line-height: 150%; text-align: center; padding: 24px 0">
									<p style="margin: 0 0 16px">Order & Inventory Manager</p>
								</td>
							</tr>
						</tbody></table>
					</td>
				</tr>
			</tbody></table>
			
		</td>
	</tr>
</tbody></table>