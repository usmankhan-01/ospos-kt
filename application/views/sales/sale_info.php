<div id="sale-info-container">
	<?php 
	if (isset($error_message))
	{
		echo "<div class='alert alert-dismissible alert-danger'>".$error_message."</div>";
	}
	if (isset($warning))
	{
		echo "<div class='alert alert-dismissible alert-warning'>".$warning."</div>";
	}
	?>
	<div class="row">
		<div class="col-md-6">
			<div class="panel panel-default sale_info_panel">
				<div class="panel-body">
					<h5>Sale Details</h5>
					<fieldset class="form-horizontal">
					<div class="form-group form-group-sm m-b-none">
						<?php echo form_label($this->lang->line('sales_info_customer_name'), 'sales_info_customer_name', array('class'=>'control-label col-xs-6')); ?>
						<?php echo form_label($sale_info['customer_name'], 'customer_name', array('class'=>'control-label col-xs-6', "style"=>"text-align:left")); ?>
					</div>
					<div class="form-group form-group-sm m-b-none">
						<?php echo form_label($this->lang->line('sales_info_customer_phone_number'), 'sales_info_customer_phone_number', array('class'=>'control-label col-xs-6')); ?>
						<?php echo form_label($sale_info['customer_phone_number'], 'customer_phone_number', array('class'=>'control-label col-xs-6', "style"=>"text-align:left")); ?>
					</div>
					<div class="form-group form-group-sm m-b-none">
						<?php echo form_label($this->lang->line('sales_info_sale_time'), 'sales_info_sale_time', array('class'=>'control-label col-xs-6')); ?>
						<?php echo form_label($sale_info['sale_time'], 'sale_time', array('class'=>'control-label col-xs-6', "style"=>"text-align:left")); ?>
					</div>
					<div class="form-group form-group-sm m-b-none">
						<?php echo form_label($this->lang->line('sales_info_sale_comment'), 'sales_info_sale_comment', array('class'=>'control-label col-xs-6')); ?>
						<?php echo form_label($sale_info['sale_comment'], 'sale_comment', array('class'=>'control-label col-xs-6', "style"=>"text-align:left")); ?>
					</div>
					<div class="form-group form-group-sm m-b-none">
						<?php echo form_label($this->lang->line('sales_info_total_retail_price'), 'sales_info_total_retail_price', array('class'=>'control-label col-xs-6')); ?>
						<?php echo form_label(to_currency($sale_info['total_retail_price']), 'total_retail_price', array('class'=>'control-label col-xs-6', "style"=>"text-align:left")); ?>
					</div>
					<div class="form-group form-group-sm m-b-none">
						<?php echo form_label($this->lang->line('sales_info_sale_total'), 'sales_info_sale_total', array('class'=>'control-label col-xs-6')); ?>
						<?php echo form_label(to_currency($sale_info['sale_total']), 'sale_total', array('class'=>'control-label col-xs-6', "style"=>"text-align:left")); ?>
					</div>
					<div class="form-group form-group-sm m-b-none">
						<?php echo form_label($this->lang->line('sales_info_total_discount'), 'sales_info_total_discount', array('class'=>'control-label col-xs-6')); ?>
						<?php echo form_label(to_currency($sale_info['total_discount']), 'total_discount', array('class'=>'control-label col-xs-6', "style"=>"text-align:left")); ?>
					</div>
					<div class="form-group form-group-sm m-b-none">
						<?php echo form_label($this->lang->line('sales_info_remaining_amount'), 'sales_info_remaining_amount', array('class'=>'control-label col-xs-6')); ?>
						<?php echo form_label(to_currency($sale_info['remaining_amount']), 'remaining_amount', array('class'=>'control-label col-xs-6', "style"=>"text-align:left")); ?>
					</div>
				</fieldset>
				</div>
			</div>
		</div>	
		<div class="col-md-6">
			<div class="panel panel-default sale_info_panel">
				<div class="panel-body">
					<h5>Sale Payments</h5>
					<?php 
					// Only show this part if there is at least one payment entered.
					if(count($sale_payments) > 0)
					{
					?>
						<table class="sales_table_100" id="register">
							<thead>
								<tr>
									<th style="width: 30%;"><?php echo $this->lang->line('sales_payment_date'); ?></th>
									<th style="width: 30%;"><?php echo $this->lang->line('sales_payment_type'); ?></th>
									<th style="width: 30%;"><?php echo $this->lang->line('sales_payment_amount'); ?></th>
									<th style="width: 10%;"><?php echo $this->lang->line('common_delete'); ?></th>
								</tr>
							</thead>

							<tbody id="payment_contents">
								<?php
								$index = 0;
								foreach($sale_payments as $key=>$value)
								{
									if (!$value->isDelete)
									{
								?>
									<tr id='payment_<?php echo $key ?>'>
										<td><?php echo date($this->config->item('dateformat'), strtotime($value->payment_date)); ?></td>
										<td><?php echo $value->payment_type; ?></td>
										<td><?php echo to_currency($value->payment_amount); ?></td>
										<td>
											<a class='delete_payment print_hide'  data-paymentid= <?php echo $key ?> ><span class="glyphicon glyphicon-trash"></span></a>
										</td>
									</tr>
								<?php
									}
								$index++;
								}
								?>
							</tbody>
						</table>
					<?php
					}
					else
					{
					?>
						<p>There are no payments for this sale.</p>
					<?php
					}
					?>	
				</div>	
			</div>	
		</div>	
	</div>
	<br />
	<div class="row">
		<div class="col-md-12">
		<div class="panel panel-default sale_info_panel">
				<div class="panel-body">
				<h5>Sale Items</h5>
				<?php 
			// Only show this part if there is at least one payment entered.
						if(count($sales_items) > 0)
						{
						?>
							<table class="sales_table_100" id="register">
								<thead>
									<tr>
										<th style="width: 15%;"><?php echo $this->lang->line('sale_info_item_name'); ?></th>
										<th style="width: 15%;"><?php echo $this->lang->line('sale_info_item_category'); ?></th>
										<th style="width: 10%;"><?php echo $this->lang->line('sale_info_item_quantity'); ?></th>
										<th style="width: 15%;"><?php echo $this->lang->line('sale_info_item_sale_price'); ?></th>
										<th style="width: 15%;"><?php echo $this->lang->line('sale_info_item_retail_price'); ?></th>
										<th style="width: 15%;"><?php echo $this->lang->line('sale_info_item_discount'); ?></th>
										<th style="width: 15%;"><?php echo $this->lang->line('sale_info_item_price'); ?></th>
										<!-- <th style="width: 15%;"><?php //echo $this->lang->line('sales_update'); ?></th>
										<th style="width: 10%;"><?php //echo $this->lang->line('common_delete'); ?></th> -->
									</tr>
								</thead>

								<tbody id="sales_contents">
									<?php
									foreach($sales_items as $line=>$item)
									{
										$id = "quantity_" . $line;
									?>
										<tr id='saleitem_<?php echo $line ?>'>
											<td><?php echo $item->item_name; ?></td>
											<td><?php echo $item->item_category; ?></td>
											<!-- <td><?php //echo form_input(array('name'=>'quantity', 'id'=>$id, 'class'=>'form-control input-sm', 'value'=>to_quantity_decimals($item->item_quantity), 'tabindex'=>++$tabindex, 'onClick'=>'this.select();'));?></td> -->
											<td><?php echo to_quantity_decimals($item->item_quantity) ?></td>
											<td><?php echo to_currency($item->item_sale_price) ?></td>
											<td><?php echo to_currency($item->item_retail_price); ?></td>
											<td><?php echo $item->item_discount_percent; ?></td>
											<td><?php echo to_currency($item->item_subtotal); ?></td>
											<!-- <td><a class="sale-update" data-existingquantity="<?php //echo $item->item_quantity ?>" data-location="<?php //echo $item->item_location ?>" data-line="<?php //echo $line ?>"  data-itemid="<?php //echo $item->item_id ?>" data-itemline="<?php //echo $item->item_line ?>" data-saleid="<?php //echo $item->sale_id ?>" title=<?php //echo $this->lang->line('sales_update')?> ><span class="glyphicon glyphicon-refresh"></span></a></td>
											<td>
												<a class='delete_saleitem print_hide' data-line="<?php //echo $line ?>"  data-itemid="<?php //echo $item->item_id ?>" data-itemline="<?php //echo $item->item_line ?>" data-saleid="<?php //echo $item->sale_id ?>" ><span class="glyphicon glyphicon-trash"></span></a>
											</td> -->
										</tr>
									<?php
									}
									?>
								</tbody>
							</table>
						<?php
						}
						?>
				</div>	
			</div>
	</div>
</div>
</div>


<script type="text/javascript">
$(document).ready(function()
{
	$('.delete_payment').on('click', function(e) {
		
		var payment_id = $(this).data('paymentid');
		var url = "<?php echo base_url(); ?>receivables/delete_payment/" + payment_id;
		$.ajax({
			type: "GET",
			url: url,
			async: false,
			success: function(data) {
				$('#payment_' + payment_id).hide();
				if (!$("#payment_contents tr").length)
				{
					$("#payment_contents").html("<p>There are no items for this sale.</p>");
				}
			}
		});
	});

	$('#save').on('click', function(e) {
		
		var url = "<?php echo base_url(); ?>receivables/save_payments";
		$.ajax({
			type: "GET",
			url: url,
			async: false,
			success: function(data) {
				if( data.status === true )
					location.reload();
            		//document.location.href = data.redirect;
			}
		});
	});

	$('.sale-update').on('click', function(e) {
		var line = $(this).data('line');
		var quantity = $("#quantity_" + line).val();
		var existing_quantity = $(this).data('existingquantity');
		var saleid = $(this).data('saleid');
		var itemid = $(this).data('itemid');
		var itemline = $(this).data('itemline');
		var itemlocation = $(this).data('itemlocation');
		var url = "<?php echo base_url(); ?>receivables/edit_sale_item";
		$.ajax({
			type: "GET",
			url: url,
			data: { item_id : itemid, quantity : quantity, sale_id : saleid, itemline : itemline, itemlocation : itemlocation, existing_quantity : existing_quantity },
			async: false,
			success: function(data) {
				$("#sale-info-container").replaceWith(data.html);
			}
		});
	});

	$('.delete_saleitem').on('click', function(e) {
		
		var line = $(this).data('line');
		var saleid = $(this).data('saleid');
		var itemid = $(this).data('itemid');
		var itemline = $(this).data('itemline');
		var url = "<?php echo base_url(); ?>receivables/delete_sale_item";
		$.ajax({
			type: "GET",
			url: url,
			data: { item_id : itemid, sale_id : saleid, itemline : itemline },
			async: false,
			success: function(data) {
				$('#saleitem_' + line).hide();
			}
		});
	});
	
});
</script>

<style>
	.delete_payment{
		cursor:pointer;
	}
</style>
