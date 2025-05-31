<div id="item-list-container">
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
	<?php echo form_open("sales/add", array('id'=>'add_item_modal_form', 'class'=>'form-horizontal')); ?>
	<?php echo form_hidden('item', ''); ?>
	<div class="row">
		<div class="col-md-12">
			<div >
				<div >
					<?php 
					// Only show this part if there is at least one item entered.
					if(count($items_list) > 0)
					{
					?>
						<table class="table table-striped table-bordered items_list" id="register">
							<thead>
								<tr class="clickable-row">
									<th style="width: 15%;"><?php echo $this->lang->line('items_name'); ?></th>
									<th style="width: 15%;"><?php echo $this->lang->line('items_unit_price'); ?></th>
									<th style="width: 15%;"><?php echo $this->lang->line('items_cost_price'); ?></th>
								</tr>
							</thead>

							<tbody id="sales_contents">
								<?php
								foreach($items_list as $item)
								{
								?>
									<tr class="clickable-row" data-id="<?php echo $item->barcode ?>" >
										<td><?php echo $item->name; ?></td>
										<td><?php echo $item->retail_price; ?></td>
										<td><?php echo $item->wholesale_price; ?></td>
									</tr>
								<?php
								}
								?>
							</tbody>
						</table>
					<?php
					}
					else
					{
						echo "No items to show";
					}
					?>
				</div>	
			</div>
		</div>
	</div>
	<?php echo form_close(); ?>
</div>


<script type="text/javascript">
$(document).ready(function()
{
	$(document).on('click', '.clickable-row', function(e) {
		$(this).addClass('active').siblings().removeClass('active');
	});

	$(document).on('dblclick', '.clickable-row', function(e) {
		$(this).addClass('active').siblings().removeClass('active');
		var item_id = $(".clickable-row.active").data('id');
		$('#add_item_modal_form input[type="hidden"]').val(item_id);
		$("#add_item_modal_form").submit();
	});
	
	
	$('#ok').on('click', function(e) {
		var item_id = $(".clickable-row.active").data('id');
		$('#add_item_modal_form input[type="hidden"]').val(item_id);
		$("#add_item_modal_form").submit();
	});

	$('#cancel').on('click', function(e) {
		$(".modal-dlg").modal('hide');
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
	
});
</script>