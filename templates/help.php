<?php
$url = 'admin.php?page=order-inventory-management&subpage=help';
$initial_page = get_option('oimwc_initial_page');
include( OIMWC_TEMPLATE . 'top_area.php' );
?>
<div class="wrap">
	<h2></h2>
	<div class="help_main_panel">
		<p class="p_font"><?php _e('Help','order-and-inventory-manager-for-woocommerce');?></p>
		<div class="how_to_help">
			<div id="accordion" class="accordion">
				<?php 
				$response = wp_remote_get( 'https://www.oimwc.com/wp-json/wp/v2/avada_faq?per_page=25&orderby=menu_order' );
				$faqs = json_decode( wp_remote_retrieve_body( $response ) );
				$class = 'open';
				if(is_array($faqs) && count($faqs) > 0){
					foreach ($faqs as $faq) {
					    ?>
						<div class="accordion-inner <?php echo $class; ?>">
						    <div class="link"><i class="fa fa-chevron-right"></i><?php echo $faq->title->rendered;?></div>
						    <div class="submenu" style="display:<?php echo $class ? 'block' : 'none' ; ?>">
						      	<?php 
						      	$class = '';
									if ( $faq->slug == 'how-do-i-contact-you' ){
										include_once(OIMWC_TEMPLATE.'contact.php');
									}
									else{
										echo $faq->content->rendered;	
									}
								?>
						    </div>
						</div>
					    <?php
					}
				}
				?>
			</div>
		</div>
	</div>
</div>