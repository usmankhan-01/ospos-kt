<?php $current_method = $this->router->fetch_method() ?>

<?php $this->load->view("partial/header"); ?>

<link rel="stylesheet" type="text/css" href="css/card.css"/>

<script type="text/javascript">
	dialog_support.init("a.modal-dlg");
</script>

<div id="page_title"><?php echo $title ?></div>
<br/>
<!-- <div id="page_subtitle"><?php echo $subtitle ?></div> -->

<?php echo form_open('#', array('id'=>'add_item_form', 'enctype'=>'multipart/form-data', 'class'=>'form-horizontal panel panel-default')); ?>
<!-- <?php echo form_open('#', array('id'=>'reports_table', 'enctype'=>'multipart/form-data', 'class'=>'form-horizontal')); ?> -->
	<div id="panel-body form-group" style="margin:15px 10px 0px 10px">
		<div class="row">
			<div class="col-md-4">
				<!-- <div class="form-inline" role="toolbar"> -->
				<div class="form-group form-inline form-group-sm">
					<?php echo form_label($this->lang->line('reports_date_range'), 'reports_date_range_label', array('class'=>'control-label col-xs-4 pull-left')); ?>
					<?php echo form_input(array('name'=>'daterangepicker', 'class'=>'form-control input-sm', 'id'=>'daterangepicker')); ?>
				</div>
			</div>
			<?php	
				if(!empty($mode))
				{
				?>
				<!-- <div class="form-inline" role="toolbar"> -->
				<div class="col-md-4">
					<div class="form-group form-inline form-group-sm">
						<?php echo form_label($this->lang->line('reports_sale_type'), 'reports_sale_type_label', array('class'=>'control-label col-xs-4')); ?>
						<?php echo form_dropdown('sale_type', $sale_type_options, $mode, array('id'=>'input_type', 'class'=>'form-control input-sm')); ?>
					</div>
				</div>		
			<?php
			}
			?>
			<?php	
			if (!empty($stock_locations) && count($stock_locations) > 1)
			{
			?>
				<div class="col-md-3">	
					<!-- <div class="form-inline" role="toolbar"> -->
					<div class="form-group form-inline form-group-sm">
						<?php echo form_label($this->lang->line('reports_stock_location'), 'reports_stock_location_label', array('class'=>'control-label col-xs-5')); ?>
						<?php echo form_dropdown('stock_location', $stock_locations, 'all', array('id'=>'location_id', 'class'=>'form-control input-sm')); ?>					
					</div>	
				</div>
			<?php
			}
			?>
			<div class="col-md-1">	
				<?php
				echo form_button(array(
					'name'=>'generate_report',
					'id'=>'generate_report',
					'content'=>$this->lang->line('common_apply'),
					'class'=>'btn btn-primary btn-sm pull-right')
				);
				?>
			</div>
		</div>
	</div>
<?php echo form_close(); ?>

<div id="table_holder">
	<table id="table"></table>
</div>

<div class="row" style="margin-top:15px">
	<?php
		$column_margin_left = (12 - count($summary_data)*2) / 2;
	?>
	<div class=<?php echo '"col-lg-'.$column_margin_left.'"' ?> > </div>
	
	<?php
	$bgColors = array('#117a8b', '#1e7e34', '#17a2b8', '#545b62', '#007bff', '#343a40');
	$colorIndex = 0;
	
	foreach($summary_data as $name => $value)
	{ 
		if($name == "total_quantity")
		{
			echo info_card('col-lg-2','#fff',$bgColors[$colorIndex],'fa-calculator',$value,'#fff',$this->lang->line('reports_'.$name)); 
		}
		else
		{
			echo info_card('col-lg-2','#fff',$bgColors[$colorIndex],'fa-dollar-sign',to_currency($value),'#fff',$this->lang->line('reports_'.$name));
		}
		$colorIndex++;
	}
	?>
</div>
<script type="text/javascript">
	$(document).ready(function()
	{
		// load the preset datarange picker
		<?php $this->load->view('partial/daterangepicker'); ?>
/*
		$("#daterangepicker").on('apply.daterangepicker', function(ev, picker) {
			var initial_url = '<?php echo site_url($controller_name).'/'.$current_method;?>';
			window.location = [initial_url, picker.startDate.format('YYYY-MM-DD'), picker.endDate.format('YYYY-MM-DD'), $("#input_type").val() || 0, $("#location_id").val()].join("/");
		});

		$("#input_type, #location_id").on('onchange', function(ev, picker) {
			var initial_url = '<?php echo site_url($controller_name).'/'.$current_method;?>';
			window.location = [initial_url, picker.startDate.format('YYYY-MM-DD'), picker.endDate.format('YYYY-MM-DD'), $("#input_type").val() || 0, $("#location_id").val()].join("/");
		});
*/

		$("#generate_report").click(function()
		{		
			var initial_url = '<?php echo site_url($controller_name).'/'.$current_method;?>';
			window.location = [initial_url, start_date, end_date, $("#input_type").val() || 0, $("#location_id").val()].join("/");
		});

		<?php $this->load->view('partial/bootstrap_tables_locale'); ?>

		var init_dialog = function() {
			dialog_support.init("a.modal-dlg");
		};

		$('#table').bootstrapTable({
			columns: <?php echo transform_headers($headers, TRUE, FALSE); ?>,
			stickyHeader: true,
			pageSize: <?php echo $this->config->item('lines_per_page'); ?>,
			striped: true,
			sortable: true,
			showExport: true,
			exportDataType: 'all',
			exportTypes: ['json', 'xml', 'csv', 'txt', 'sql', 'excel', 'pdf'],
			pagination: true,
			showColumns: true,
			data: <?php echo json_encode($data); ?>,
			iconSize: 'sm',
			paginationVAlign: 'bottom',
			escape: false,
			onPageChange: init_dialog,
			onPostBody: function() {
				dialog_support.init("a.modal-dlg");
			},
		});

	});
</script>

<?php $this->load->view("partial/footer"); ?>
