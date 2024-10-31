<script type="text/html" id="tmpl-supplier_table">
    <tr id="post-{{{data.supplier_id}}}" class="iedit author-self level-0 post-{{data.supplier_id}} type-supplier status-publish hentry entry">
		<td class="supplier_id column-supplier_id" data-colname="ID">{{data.supplier_id}}
		<style> .bulkactions{ display: none;}#cb,.check-column{display: none;}</style>
		</td>
		<td class="title column-title has-row-actions column-primary page-title" data-colname="Supplier Name">
			<div class="locked-info">
				<span class="locked-avatar"></span>
				<span class="locked-text"></span>
			</div>
			<strong>
				<a class="row-title" href="<?php echo admin_url();?>post.php?post={{data.supplier_id}}&action=edit" aria-label="{{data.supplier_name}} (Edit)">{{data.supplier_name}}
				</a>
			</strong>

			<div class="hidden" id="inline_{{data.supplier_id}}">
				<div class="post_title">{{data.supplier_name}}</div>
				<div class="post_name">{{data.post_name}}</div>
				<div class="post_author">{{data.post_author}}</div>
				<div class="comment_status">{{data.comment_status}}</div>
				<div class="ping_status">{{data.ping_status}}</div>
				<div class="_status">{{data.post_status}}</div>
				<div class="jj">{{data.date}}</div>
				<div class="mm">{{data.mon}}</div>
				<div class="aa">{{data.yr}}</div>
				<div class="hh">{{data.hh}}</div>
				<div class="mn">{{data.mm}}</div>
				<div class="ss">{{data.ss}}</div>
				<div class="post_password"></div>
				<div class="page_template">default</div>
				<div class="sticky"></div>
			</div>
			<div class="row-actions">
				<span class="edit">
					<a href="<?php echo admin_url();?>post.php?post={{data.supplier_id}}&action=edit" aria-label="Edit '{{data.supplier_name}}'">Edit</a>	| 
				</span>
				<span class="trash">
					<a href="<?php echo admin_url();?>post.php?post={{data.supplier_id}}&action=trash&_wpnonce=199030cdcb" class="submitdelete" aria-label="Move '{{data.supplier_name}}' to the Trash">Trash</a>
				</span>
			</div>
			<button type="button" class="toggle-row">
				<span class="screen-reader-text">Show more details</span>
			</button>
		</td>
		<td class="custom_name column-custom_name" data-colname="Custom Name">{{data.short_name}}
		<style> .bulkactions{ display: none;}#cb,.check-column{display: none;}</style>
		</td>
	</tr>
</script>