<style>
    @import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700;800;900&display=swap');
    *{
        margin: 0;
        padding: 0;
    }
    body{
        font-family: 'Montserrat', sans-serif;
    }
    .clearfix{
        clear: both;
        float: none;
    } 
    .w-100{
        width: 100%;
    }
    .w-50{
        width: 50%;
    }
    h1{
        padding-bottom: -15px;
        font-size: 16px;
        font-weight: bold;
    }
    table td.total-btn, thead{
        color: #fff;
    }
    tr{
        background-color : #f6f7f9; 
    }
    .product_info tr:nth-child(odd) {background-color: #f6f7f9;}
    .product_info tr:nth-child(even) {background-color: #ffffff;}
    th { 
        <?php if($language_type == 'sv_SE'){ ?>
            font-size: 11px;
        <?php }else{ ?>
            font-size: 12px;
        <?php } ?>
        color: <?php echo $pdf_font_color; ?>;
        font-weight: bold; 
        padding: 10px 20px;
        background-color : <?php echo $pdf_color; ?>; 
        <?php if($pdf_color =='#ffffff'){ ?>
            border: 1px solid black;
        <?php } ?>
    }
    .product_info th{
        <?php if($count == 3 && $language_type == 'sv_SE') { ?>
            font-size: 12px;
        <?php }else if($language_type == 'sv_SE' && $count == 8){ ?>
            font-size: 15px;
        <?php }else{ ?>
            font-size: 12px;
        <?php } ?>
        padding: 8px 10px;
        line-height: 1.4;
        overflow: auto;
    }
    .product_info td{
        padding: 8px 10px;
        overflow: wrap;
    }
    td, th { 
        text-align:left;
    }
    .product_info td,.product_info th { 
        text-align:center;
    }
    p {
        font-size: 14px;
        line-height: 1.4;
        font-weight: 500;
    }
    table tr td, table p {
        font-size: 13px;
        line-height: 1.4;
        font-weight: 500;
    }
    .supplier_info tr td {
        vertical-align: top;
        text-align: left;
        padding-top: 20px !important;
        padding-bottom: 20px !important;
    }
    table th{
        text-transform: uppercase;
        letter-spacing: 1px;
    }
    .supplier_info th:first-child,
    .supplier_info td:first-child{
        padding-left: 80px;
    }
    .supplier_info th:last-child,
    .supplier_info td:last-child{
        padding-right: 80px;
        padding-left: 0px;
    }
    .supplier_info th,
    .supplier_info td{
       width: 50%;
    }
    .supplier_info {
        padding-top: -40px;
    }
    .mb-30{
        margin-bottom: 20px;
    }
    .table_two td{
        padding: 10px 20px;
    }
    .text-right{
        text-align: right;
    }
    td.total-price{
        font-weight: bold;
        font-size: 13px;
        padding: 10px;
    }
    .product_info th {
        text-align: left;
    }
    strong {
        font-size: 12px;
    }
    a {
        text-decoration: none;
    }
</style>
<?php 

    $sql = "SELECT * FROM `{$wpdb->prefix}order_inventory` WHERE supplier_id = {$supplier_id} AND order_date = '{$order_date}' order by order_number DESC";
    $result = $wpdb->get_results($sql);
    foreach ($result as $row) {
        $order_id = ($row->order_number != '') ? $row->order_number : 0;
        $private_notes = $row->additional_information;
    }

$delivery_date = (!empty($_POST['delivery_date'])) ? $_POST['delivery_date'] : $delivery_date;
$image_id = get_option('oimwc_pdf_logo');
if( $image = wp_get_attachment_image_src( $image_id, 'thumbnail' ) ) {
    echo '<div style="padding:0 0 5px 20px;"><img src="'.wp_get_attachment_url($image_id).'" width="auto" height="'.$image[2].'" alt="pdf_logo" /></div>';
} ?>
<div class="w-100 mb-30" style="padding:20px 20px;">
    <div class="col w-50" style="float: left;">
        <h1><?php echo (!empty($im_company)) ? $im_company.'<br/>' : ''; ?></h1>
        <p><?php echo (!empty($address1)) ? $address1.'<br />' : '';
        echo (!empty($address2)) ? $address2.'<br />' : ''; ?>
        <?php if($city != '' || $zip_code != '' || $state_office != '') { echo (!empty($city)) ? $city.' ' : ''; echo (!empty($state_office)) ? $state_office.' ' : ''; echo (!empty($zip_code)) ? $zip_code.' ' : ''; } ?>
        <?php if($city != '' || $zip_code != '' || $state_office != '') { echo '<br />'; } ?>
        <?php echo (!empty($country)) ? $country : ''; ?></p>
        <?php  if($phone != '' || $email != '' || $po_attn != '' || $fax_number != '' || $url != '' || $tax != '') { ?><p><?php } if($phone != ''){ ?><?php _e('Phone No','order-and-inventory-manager-for-woocommerce'); echo ': '; ?><?php echo $phone.'<br />'; ?><?php } ?>
        <?php if($email != ''){ ?><?php _e('Email','order-and-inventory-manager-for-woocommerce'); echo ': '; ?><?php echo $email.'<br />'; ?><?php } ?>
        <?php if($fax_number != ''){ ?><?php _e('Fax Number','order-and-inventory-manager-for-woocommerce'); echo ': '; ?><?php echo $fax_number.'<br />'; ?><?php } ?>
        <?php if($url != ''){ ?><?php _e('Website','order-and-inventory-manager-for-woocommerce'); echo ': '; ?><a href="<?php echo $url; ?>" target="_blank"><?php echo $url.'<br />'; ?></a><?php } ?>
        <?php if($tax != ''){ ?><?php _e('Tax registration nr. / VAT','order-and-inventory-manager-for-woocommerce'); echo ': '; ?><?php echo $tax.'<br />'; ?><?php } ?>
        <?php if($po_attn != ''){ ?><?php _e('Attn','order-and-inventory-manager-for-woocommerce'); echo '.: '; ?><?php echo $po_attn; ?><?php } if($phone != '' || $email != '' || $po_attn != '' || $fax_number != '' || $url != '' || $tax != '') { ?></p><?php } ?>
    </div>
    <div class="col w-50" style="float: right; margin-top: 2px; margin-right: -50px;">
        <h1><?php _e('Purchase Order','order-and-inventory-manager-for-woocommerce'); ?></h1>
        <p><?php _e('PO No','order-and-inventory-manager-for-woocommerce'); echo '.: '.$order_id; ?><br />
        <?php echo $order_date_pdf; ?></p>
    </div>
    <div class="clearfix"></div>
</div>
<table class="supplier_info w-100 mb-30" autosize="1" style="border-spacing:0;border: 0;overflow: wrap;">
    <thead>  
        <tr>
            <th><?php _e('SUPPLIER','order-and-inventory-manager-for-woocommerce'); ?></th>
            <?php if($shipping_address_list != '') { ?><th style="padding-left: 50px;"><?php _e('DELIVERY ADDRESS','order-and-inventory-manager-for-woocommerce'); ?></th><?php } ?>
        </tr>
    </thead>
    <tr>
        <td style="padding-left: 20px;">
            <strong><?php echo $supplier_name; ?></strong>
            <?php if($supplier_address != '') { ?><p style="font-size: 13px;"><?php echo $supplier_address; ?></p><?php } ?>
            <br />
            <?php if($supplier_phone != '') { ?><p><strong style="font-size: 12px; font-weight: bold;"><?php _e('Phone No','order-and-inventory-manager-for-woocommerce'); echo '.: '; ?></strong><?php echo $supplier_phone; ?></p><?php } ?>
            <?php if($supplier_email != ''){ ?><p><strong style="font-size: 12px; font-weight: bold;"><?php _e('Email','order-and-inventory-manager-for-woocommerce'); echo ': '; ?></strong><?php echo $supplier_email; ?></p><?php } ?>
        </td>
        <?php if($shipping_address_list != '') { ?>
            <td style="padding-left: 50px;">
                <p><?php echo (!empty($im_receiver)) ? $im_receiver : ''; ?></p>
                <p><?php echo (!empty($im_address1)) ? $im_address1 : ''; ?></p>
                <p><?php echo (!empty($im_state)) ? $im_state : ''; if($im_state != '' && $im_zip_code != '') { echo ', '; } echo (!empty($im_zip_code)) ? $im_zip_code : ''; ?></p>
                <p><?php echo (!empty($im_country)) ? $im_country : ''; ?></p>
                <br />
                <?php if($im_phone != ''){ ?><p><strong style="font-size: 12px; font-weight: bold;"><?php _e('Phone No','order-and-inventory-manager-for-woocommerce'); echo '.: '; ?></strong><?php echo (!empty($im_phone)) ? $im_phone : '-'; ?></p><?php } ?>
                <?php if($po_attn != ''){ ?><p><strong style="font-size: 12px; font-weight: bold;"><?php _e('Attn','order-and-inventory-manager-for-woocommerce'); echo ': '; ?></strong><?php echo $po_attn; ?></p><?php } ?>
            </td>
        <?php } ?>
    </tr>
</table>
<table class="w-100 mb-30 table_two" style="border-spacing:0;border: 0;">
  <tr>
    <th><?php _e('DELIVERY DATE','order-and-inventory-manager-for-woocommerce'); ?></th>
    <th><?php _e('REQUESTED BY','order-and-inventory-manager-for-woocommerce'); ?></th>
    <th><?php _e('SHIPPING TERMS','order-and-inventory-manager-for-woocommerce'); ?></th>
    <th><?php _e('SHIPPING METHOD','order-and-inventory-manager-for-woocommerce'); ?></th>
  </tr>
  <tr>
    <td><?php echo (!empty($delivery_date)) ? $delivery_date : '-'; ?></td>
    <td><?php echo (!empty($im_contact)) ? $im_contact : '-'; ?></td>
    <td><?php echo (!empty($shipping_terms)) ? $shipping_terms : '-'; ?></td>
    <td><?php echo (!empty($shipping_method)) ? $shipping_method : '-'; ?></td>
  </tr>
</table>
<table class="w-100 mb-30 table_two" autosize="1" style="border-spacing:0;border: 0;overflow: wrap;">
    <tr>
        <th><?php _e('NOTES','order-and-inventory-manager-for-woocommerce'); ?></th>
    </tr>
    <tr>
        <td><?php echo (!empty($private_notes)) ? $private_notes : '-'; ?></td>
    </tr>
</table>
<table class="w-100 product_info" style="border-spacing:3.5px;" autosize="0">
    <thead>
    <tr>
        <?php if(in_array('product_image', $column_names))
        { ?>
            <th class="item-name"><?php _e('ITEM IMAGE','order-and-inventory-manager-for-woocommerce'); ?></th>
        <?php } ?>
        <?php if(in_array('shop_product_name', $column_names) || in_array('shop_variant_name', $column_names))
        { ?>
            <th class="item-name"><?php _e('ITEM NAME','order-and-inventory-manager-for-woocommerce'); ?></th>
        <?php } ?>
        <?php if(in_array('product_url', $column_names)) 
        { ?>
            <th class="item-url"><?php _e('ITEM URL','order-and-inventory-manager-for-woocommerce'); ?></th>
        <?php } ?>
        <?php if(in_array('product_id',$column_names)) 
        { ?>
            <th class="item-code"><?php _e('ITEM CODE','order-and-inventory-manager-for-woocommerce'); ?></th>
        <?php } ?>
        <?php if(in_array('notes', $column_names)) 
        { ?>
            <th style="text-align: left;"><?php _e('NOTES','order-and-inventory-manager-for-woocommerce'); ?></th>
        <?php } ?>
        <?php if(in_array('qty', $column_names))
        { ?>
            <th style="text-align: right;"><?php _e('QTY','order-and-inventory-manager-for-woocommerce'); ?></th>
        <?php } ?>
        <?php if(in_array('product_price', $column_names))
        { ?>
           <th style="text-align: right;"><?php _e('ITEM PRICE','order-and-inventory-manager-for-woocommerce'); ?></th>
           <th style="text-align: right;"><?php _e('TOTAL','order-and-inventory-manager-for-woocommerce'); ?></th>
        <?php } ?>
    </tr>
  </thead>
<?php foreach ($result as $row) { 
    $total_price = 0;
    if($row->temp_product == 1){
        $temporary_product = $wpdb->get_results('SELECT * FROM '.$wpdb->prefix . 'oimwc_temp_product WHERE id='.$row->product_id,ARRAY_A);
        foreach ($temporary_product as $value) {
            $product_title = $value['product_name'];
            $product_variant = $value['variation_name'];
            $product_image = wp_upload_dir()['basedir'].'/woocommerce-placeholder-100x100.png';
            $product_url = $value['product_url'];
            $product_code = $value['supplier_product_id'];
            $product_notes = $value['supplier_notes'];
            $product_qty = $value['product_qty'];
            $product_price = $value['product_price'];
            if ($product_price) {
                $total_price += ( $value['product_price'] * $product_qty );
            }
            $product_url = ($product_url != '') ? $product_url : '';
            $product_code = ($product_code != '') ? $product_code : '';
            $product_notes = ($product_notes != '') ? $product_notes : '';
        }
    }else{
        $product = get_post($row->product_id);
        $product_title = html_entity_decode($product->post_parent ? get_the_title($product->post_parent) : get_the_title($product->ID));
        $product_type = $product->post_type;
        $product_variant = '';
        if ($product_type == 'product_variation') {
            $variable_product = new WC_Product_Variation($product->ID);
            $product_variant = $variable_product->get_variation_attributes();
                if (is_array($product_variant) && count($product_variant)) {
                    $variation_names = array();
                    foreach ($product_variant as $key=>$value) {
                        if (strpos($key, 'pa_') !== false) {
                            $product_attribute = wc_attribute_label(str_replace("attribute_","", $key));
                        }else{
                            $product_attribute = wc_attribute_label('pa_' . str_replace("attribute_","", $key));
                            if(strpos($product_attribute, 'pa_') !== false){
                                $product_attribute = ucfirst(wc_attribute_label(str_replace(array("attribute_","-"),array(""," "), $key)));
                            }else{
                                $product_attribute = wc_attribute_label('pa_' . str_replace("attribute_","", $key));
                            }
                        }
                        $term = get_term_by('slug', $value, str_replace("attribute_","", $key) );
                        if(!$term){
                            $variation_names[] = ['name'=>$value,'attribute'=>$product_attribute];
                        }else{
                            $variation_names[] = ['name'=>$term->name,'attribute'=>$product_attribute];
                        }
                    }
                    $product_variant = $variation_names;
                } else {
                    $product_variant = '';
                }
                $product_image = wp_get_attachment_image_src( get_post_thumbnail_id( $product->post_parent ), array('100','100') );
                $variation_image = wp_get_attachment_image_src( get_post_thumbnail_id( $row->product_id ), array('100','100') );
                $product_image = (!empty($variation_image)) ? $variation_image[0] : $product_image[0];
        }else{
            $product_image = wp_get_attachment_image_src( get_post_thumbnail_id( $row->product_id ), array('100','100') );
            $product_image = $product_image[0];
        }
        $product_image = (empty($product_image)) ? wp_upload_dir()['basedir'].'/woocommerce-placeholder-100x100.png' : $product_image;
        if (preg_match('/[ëäöåÄÖÅ]+/', end(explode('/', $product_image)), $matches))
        {
            $product_image = wp_upload_dir()['basedir'].'/woocommerce-placeholder-100x100.png';
        }
        else
        {
            $product_image = $product_image;
        }
        $product_url = get_post_meta($row->product_id,'oimwc_supplier_product_url',true);
        $product_url = ($product_url != '') ? $product_url : '';
        $product_code = get_post_meta($row->product_id,'oimwc_supplier_product_id',true);
        $product_code = ($product_code != '') ? $product_code : '';
        $product_notes = get_post_meta($row->product_id,'oimwc_supplier_note',true);
        $product_notes = ($product_notes != '') ? $product_notes : '';
        $product_qty = $row->requested_stock;
        $product_price = get_post_meta($row->product_id,'oimwc_supplier_purchase_price',true);
        if(strpos($product_price,',') !== false){
            $product_price = str_replace(',', '.', $product_price);
        }else{
            $product_price = $product_price;
        }
        $additional_supplier_info = oimwc_additional_supplier_details_from_product( $product->ID, $supplier_id );
        if( is_array( $additional_supplier_info ) && count( $additional_supplier_info ) ){
            $product_url = $additional_supplier_info['supplier_product_url'];
            $product_code = $additional_supplier_info['supplier_product_id'];
            $product_notes = $additional_supplier_info['product_notes'];
            $product_price = $additional_supplier_info['purchase_price'];
            if( !$product_code ){
                $product_code    = get_post_meta( $row->product_id, '_sku', true ); 
            }
        }
        if ($product_price) {
            $total_price += ( $product_price * $product_qty );
        } }?>
    <tr style="font-size: 20px !important;">
        <?php if(in_array('product_image', $column_names)) 
        { ?>
            <td><img src="<?php echo $product_image; ?>" alt="product_image" /></td>
        <?php } ?>
        <?php if(in_array('shop_product_name', $column_names))
        { 
            if($row->temp_product == 1){ ?>
                <td style="text-align: left;"><?php echo $product_title; 
                    if(in_array('shop_variant_name', $column_names)) 
                    { 
                        echo '<br />'; echo (!empty($product_variant)) ? $product_variant : ''; 
                    } ?>
                </td> 
            <?php }else{ ?>
                <td style="text-align: left;"><?php echo $product_title; 
                    if(in_array('shop_variant_name', $column_names)) 
                    { 
                        foreach ($product_variant as $key => $value) {
                            echo '<br />'; echo (!empty($value)) ? $value['attribute'].' : '.$value['name'] : ''; 
                        }
                    } ?>
                </td> 
            <?php }
        }else if(in_array('shop_variant_name', $column_names)) 
        { 
            if($row->temp_product == 1){ ?>
                <td style="text-align: left;"><?php 
                    if(!empty($product_variant)){
                        echo '<br />'; echo (!empty($product_variant)) ? $product_variant : ''; 
                    }else{
                        echo '';
                    } ?>
                </td>
            <?php }else{ ?>
                <td style="text-align: left;"><?php 
                    if(!empty($product_variant)){
                        foreach ($product_variant as $key => $value) {
                            echo '<br />'; echo (!empty($value)) ? $value['attribute'].' : '.$value['name'] : ''; 
                        }
                    }else{
                        echo '-';
                    } ?>
                </td>
            <?php }
        } 
        if(in_array('product_url', $column_names)) 
        { ?>
            <td><?php if(!empty($product_url)) { ?>
                    <a href="<?php echo $product_url; ?>" target="_blank" style="text-decoration: none;text-align: left !important;"><?php _e('Item Url','order-and-inventory-manager-for-woocommerce'); ?></a>
                <?php }else{
                    echo '-';
                } ?>
            </td>
        <?php } ?>
        <?php if(in_array('product_id',$column_names)) 
        { 
            if(!empty($product_code)){ ?>
                <td style="text-align: left;"><?php echo $product_code; ?></td>
            <?php }else{ ?>
                <td style="text-align: center;"><?php echo '-'; ?></td>
            <?php }
        } ?>
        <?php if(in_array('notes', $column_names)) 
        {   
            if(!empty($product_notes)){ ?>
                <td style="text-align: left;"><?php echo $product_notes; ?></td>
            <?php }else{ ?>
                <td style="text-align: center;"><?php echo '-'; ?></td>
            <?php } 
        } ?>
        <?php if(in_array('qty', $column_names))
        { ?>
          <td style="text-align: right;"><?php echo $product_qty; ?></td>
        <?php } ?>
        <?php if(in_array('product_price', $column_names))
        { ?>
          <td style="text-align: right;"><?php echo (!empty($product_price)) ? wc_price($product_price,array('currency' => $supplier_currency,'decimal_separator' => '.' )) : wc_price(0,array('currency' => $supplier_currency,'decimal_separator' => '.' )); ?></td>
          <td style="text-align: right;"><?php echo (!empty($total_price)) ? wc_price($total_price,array('currency' => $supplier_currency,'decimal_separator' => '.' )) : wc_price(0,array('currency' => $supplier_currency,'decimal_separator' => '.' )); ?></td>
        <?php } ?>
    </tr>
    <?php } ?>
    <?php if(in_array('product_price', $column_names))
    { 
        $header_span = 2;
        if(count($column_names) == 8){
            $col_span = 5;
        }
        else if(count($column_names) == 1){
            $header_span = 1;
        }
        else if(count($column_names) <= 7 && !in_array('shop_product_name', $column_names) && in_array('shop_variant_name', $column_names)){
            if(count($column_names) == 4){
                $col_span = 2;
            }
            if(count($column_names) == 5){
                $col_span = 3;
            }
            if(count($column_names) == 6){
                $col_span = 4;
            }
            if(count($column_names) == 7){
                $col_span = 5;
            }
        }
        else if(count($column_names) <= 7 && (in_array('shop_product_name', $column_names)) && !in_array('shop_variant_name', $column_names)){
            if(count($column_names) == 4){
                $col_span = 2;
            }
            if(count($column_names) == 5){
                $col_span = 3;
            }
            if(count($column_names) == 6){
                $col_span = 4;
            }
            if(count($column_names) == 7){
                $col_span = 5;
            }
        } 
        else if(count($column_names) <= 7 && (in_array('shop_product_name', $column_names) && in_array('shop_variant_name', $column_names))){
            if(count($column_names) == 4){
                $col_span = 1;
            }
            if(count($column_names) == 5){
                $col_span = 2;
            }
            if(count($column_names) == 6){
                $col_span = 3;
            }
            if(count($column_names) == 7){
                $col_span = 4;
            }
        }   
        else if(count($column_names) <= 7 && (in_array('shop_product_name', $column_names) || in_array('shop_variant_name', $column_names))){
            if(count($column_names) == 4){
                $col_span = 1;
            }
            if(count($column_names) == 6){
                $col_span = 3;
            }
            if(count($column_names) == 5){
                $col_span = 3;
            }
            if(count($column_names) == 7){
                $col_span = 5;
            }
        }
        else if(count($column_names) <= 7 && (!in_array('shop_product_name', $column_names) || !in_array('shop_variant_name', $column_names))){
            if(count($column_names) == 3){
                $col_span = 1;
            }else{
                $col_span = count($column_names) - 2;
            }
        } ?>
    <tr style="background-color: #ffffff !important;">
            <?php if(count($column_names) > 3){ ?>
                <td colspan="<?php echo $col_span; ?>"></td>
            <?php }else if(count($column_names) == 3 && (!in_array('shop_product_name', $column_names) || !in_array('shop_variant_name', $column_names))){ ?>
                <td colspan="1"></td>
            <?php } ?>
            <th class="text-right total-btn" colspan="<?php echo $header_span; ?>" style="text-align: center;background-color : <?php echo $pdf_color; ?>"><?php _e('ORDER TOTAL','order-and-inventory-manager-for-woocommerce'); ?></th>
            <td class="text-right total-price" style="background-color: #F6F7F9;"><strong><?php echo $total_purchase; ?></strong></td>
    </tr>
<?php } ?>
</table>