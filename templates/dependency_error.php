<div class="error">
	<p><strong>Error: <?php echo OIMWC_NAME; ?> requires the below stated plugins. Please install and activate the below plugins for <?php echo OIMWC_NAME; ?> to work.</strong></p>
	<ol>
		<?php
			
			if ( !in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
				?>
				<li>Woocommerce</li>
				<?php
			}
		
		?>
	</ol>
</div>